<?php
namespace App\Http\Controllers;
use App\Models\SanctionLetter;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SanctionLetterMail;

class SanctionLetterController extends Controller
{
    private const SEQUENCE_KEY = 'sanction_letter_sequence';
    private const SEQUENCE_START = 1000;

    public function index(Request $request)
    {
        $query = SanctionLetter::with(["user", "user.detail", "business"]);

        $status = in_array($request->get('status'), ['pending', 'under_review', 'reupload_required', 'approved'], true)
            ? $request->get('status')
            : 'all';

        if ($status !== 'all') {
            $query->where('review_status', $status);
        }

        // Filter by agent (used by "Review Sanction Letters" button on agent edit page).
        $agentId = $request->get('agent_id') ?: $request->get('user_id');
        $filterAgent = null;
        if ($agentId) {
            $query->where('user_id', $agentId);
            $filterAgent = User::find($agentId);
        }

        // Load all matching letters for client-side DataTables filtering/search
        // (same UX as Agents page: Start/End Date, dropdowns, Quick Search, export).
        $letters = $query->latest()->get();

        return view("sanctions.index", compact("letters", "status", "filterAgent"));
    }

    public function create(Request $request)
    {
        $agents = User::with(['business', 'detail'])->whereHas("role", fn($q) => $q->where("slug", "agent"))->orderBy("name")->get();
        $selectedAgent = null;
        if ($request->filled("agent_id")) { $selectedAgent = User::with("business", "detail")->find($request->agent_id); }
        elseif ($request->filled("user_id")) { $selectedAgent = User::with("business", "detail")->find($request->user_id); }

        $existingSignature = SystemSetting::getValue('last_signature_image');

        $sequence = $this->peekSanctionNumber();

        return view("sanctions.create", [
            "agents" => $agents,
            "selectedAgent" => $selectedAgent,
            "existingSignature" => $existingSignature,
            "nextSanctionNumber" => SanctionLetter::formatSanctionLetterNo($sequence),
            "nextReferenceNumber" => SanctionLetter::formatReferenceNo($sequence),
            "disbursementModes" => $this->disbursementModes(),
        ]);
    }

    public function preview(Request $request)
    {
        $data = $this->validateForm($request);
        $agent = User::with("business", "detail")->findOrFail($data["user_id"]);
        $signatureImageUrl = $this->resolveSignatureUrl($request);
        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl, (string) $this->peekSanctionNumber());
        $pdf = Pdf::loadView("sanctions.pdf", $pdfData)->setPaper("letter", "portrait");
        $binary = $pdf->output();
        return response()->json(["pdf" => "data:application/pdf;base64," . base64_encode($binary)]);
    }

    public function downloadPdf(Request $request)
    {
        $data = $this->validateForm($request);
        $agent = User::with("business", "detail")->findOrFail($data["user_id"]);
        $signatureImageUrl = null;
        if ($request->hasFile("signature_image")) {
            $path = $request->file("signature_image")->store("signature-images", "public");
            $signatureImageUrl = asset("storage/" . $path);
        }
        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl, (string) $this->peekSanctionNumber());
        $pdf = Pdf::loadView("sanctions.pdf", $pdfData)->setPaper("letter", "portrait");
        return $pdf->stream("sanction-letter-" . now()->format("Ymd-His") . ".pdf");
    }

    public function store(Request $request, AuditService $audit)
    {
        $data = $this->validateForm($request);
        $agent = User::with("business", "detail")->findOrFail($data["user_id"]);
        $signatureImagePath = null;
        $signatureImageUrl = null;
        if ($request->hasFile("signature_image")) {
            $signatureImagePath = $request->file("signature_image")->store("signature-images", "public");
            $signatureImageUrl = asset("storage/" . $signatureImagePath);
            SystemSetting::setValue('last_signature_image', $signatureImagePath);
        } else {
            $lastSignature = SystemSetting::getValue('last_signature_image');
            if ($lastSignature) {
                $signatureImageUrl = asset("storage/" . $lastSignature);
                $signatureImagePath = $lastSignature;
            }
        }

        $sanctionNumber = (string) $this->allocateSanctionNumber();

        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl, $sanctionNumber);
        $pdf = Pdf::loadView("sanctions.pdf", $pdfData)->setPaper("letter", "portrait");
        $filename = "sanction-letter-" . $sanctionNumber . "-" . now()->format("Ymd-His") . ".pdf";
        $relativePath = "sanction-letters/" . $filename;
        Storage::disk("public")->put($relativePath, $pdf->output());

        $letter = SanctionLetter::create([
            "sanction_number" => (int) $sanctionNumber,
            "user_id" => $agent->id, "business_id" => $agent->business_id,
            "title" => $data["title"], "subject" => $data["subject"],
            "greeting" => $data["greeting"], "body" => $data["body"],
            "dynamic_fields" => $pdfData["dynamic_fields"],
            "closing" => $data["closing"],
            "notes" => $data["notes"] ?? null,
            "signature_name" => $data["signature_name"] ?? null,
            "signature_designation" => $data["signature_designation"] ?? null,
            "signature_company" => $data["signature_company"] ?? null,
            "signature_image" => $signatureImagePath,
            "upload_deadline_at" => now()->addHours(SanctionLetter::UPLOAD_WINDOW_HOURS),
            "pdf_path" => $relativePath, "status" => "sent", "sent_at" => now(),
            "process_fee" => (float) ($data["process_fee"] ?? 0),
        ]);
        $recipientEmail = $agent->detail?->personal_email ?: $agent->email     ?: $agent->email;
        $sendEmail = $request->boolean("send_email");
        if ($sendEmail) {
            try {
                Mail::to($recipientEmail)->send(new SanctionLetterMail($letter, $agent, $pdf->output(), $filename));
            } catch (\Throwable $e) {
                $audit->record($request, "sanction_letter.email_failed", $letter, ["error" => $e->getMessage()]);
                return back()->withInput()->with("error", "PDF saved but email failed: " . $e->getMessage());
            }
            $audit->record($request, "sanction_letter.sent", $letter, ["to" => $recipientEmail]);
        } else {
            $audit->record($request, "sanction_letter.created_without_email", $letter, ["recipient" => $recipientEmail, "dash_only" => true]);
        }
        return redirect()->route("sanctions.index")->with("success",  $sendEmail
            ? "Letter sent to " . $agent->name . " (" . $recipientEmail . ")"
            : "Letter created — PDF saved and available to " . $agent->name . " (" . $recipientEmail . ") in the dashboard (email not sent).");
    }

    public function show(SanctionLetter $sanction)
    {
        $sanction->load(["user.business", "user.detail", "business"]);
        $agent = $sanction->user;

        $df = is_array($sanction->dynamic_fields) ? $sanction->dynamic_fields : [];

        $letterNo = $sanction->sanction_letter_no;

        $agentAddress = $this->buildAgentAddress($agent);
        $letterAddress = trim($df['letter_address'] ?? '') !== '' ? $df['letter_address'] : $agentAddress;

        $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
        $approvedAmount = $df['approved_amount'] ?? ($advanceAmount > 0 ? $advanceAmount : 0);

        $availableKeys = [
            '[[agent_name]]'          => $agent->name ?? '',
            '[[agent_email]]'         => $agent->email ?? '',
            '[[agent_id]]'            => $agent->detail?->agent_id_number ?? '',
            '[[agent_address]]'       => $letterAddress,
            '[[business_name]]'       => $agent->business?->name ?? '',
            '[[letter_no]]'           => $letterNo,
            '[[reference_no]]'        => $sanction->reference_no,
            '[[approved_amount]]'     => is_numeric($approvedAmount) ? '₹ ' . number_format((float) $approvedAmount, 2) : $approvedAmount,
            '[[sanction_date]]'       => $df['sanction_date'] ?? now()->format('d/m/Y'),
            '[[document_title]]'      => $sanction->title ?? '',
            '[[email_subject]]'       => $sanction->subject ?? '',
            '[[signature_name]]'      => $sanction->signature_name ?? '',
            '[[signature_designation]]' => $sanction->signature_designation ?? '',
            '[[signature_company]]'   => $sanction->signature_company ?? '',
        ];

        $replaceTokens = function ($text) use ($availableKeys, $df) {
            $allKeys = array_merge($availableKeys, $df);
            $text = str_replace(array_keys($allKeys), array_values($allKeys), $text ?? '');
            $text = preg_replace('/\[\[bold:(.*?)\]\]/', '<strong>$1</strong>', $text);
            $text = str_replace('\n', "\n", $text);
            return $text;
        };

        $resolvedTitle = $replaceTokens($sanction->title);
        $resolvedBody = $replaceTokens($sanction->body);
        $resolvedGreeting = $replaceTokens($sanction->greeting);
        $resolvedClosing = $replaceTokens($sanction->closing);

        return view("sanctions.show", ["letter" => $sanction, "resolvedTitle" => $resolvedTitle, "resolvedBody" => $resolvedBody, "resolvedGreeting" => $resolvedGreeting, "resolvedClosing" => $resolvedClosing]);
    }

    /**
     * Soft delete (trash) the sanction letter. The generated PDF and any
     * signed uploads remain on disk until the record is permanently deleted
     * from the trash.
     */
    public function destroy(Request $request, SanctionLetter $sanction, AuditService $audit)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($sanction->trashed()) {
            return back()->with('error', 'This sanction letter is already in the trash.');
        }

        $sanction->delete();

        $audit->record($request, 'sanction_letter.trashed', $sanction, [
            'sanction_letter_id' => $sanction->id,
        ]);

        return redirect()
            ->route('sanctions.index')
            ->with('success', "Sanction letter {$sanction->sanction_letter_no} moved to trash.");
    }

    public function download(SanctionLetter $sanction)
    {
        if ($sanction->pdf_path && Storage::disk("public")->exists($sanction->pdf_path)) {
            return Storage::disk("public")->download($sanction->pdf_path);
        }
        $sanction->load(["user.business", "user.detail"]);
        $df = is_array($sanction->dynamic_fields) ? $sanction->dynamic_fields : [];
        if (!isset($df['sanction_number']) && $sanction->sanction_number) {
            $df['sanction_number'] = (string) $sanction->sanction_number;
        }
        $sigUrl = $sanction->signature_image ? asset("storage/" . $sanction->signature_image) : null;
        $pdf = Pdf::loadView("sanctions.pdf", [
            "title" => $sanction->title, "subject" => $sanction->subject,
            "greeting" => $sanction->greeting, "body" => $sanction->body,
            "dynamic_fields" => $df,
            "notes" => $sanction->notes,
            "closing" => $sanction->closing,
            "signature_name" => $sanction->signature_name,
            "signature_designation" => $sanction->signature_designation,
            "signature_company" => $sanction->signature_company,
            "signature_image_url" => $sigUrl, "agent" => $sanction->user,
        ])->setPaper("letter", "portrait");
        return $pdf->stream("sanction-letter-" . ($sanction->sanction_number ?? $sanction->id) . ".pdf");
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            "user_id" => ["required", "exists:users,id"],
            "title" => ["required", "string", "max:200"],
            "subject" => ["required", "string", "max:255"],
            "greeting" => ["required", "string"],
            "body" => ["required", "string"],
            "notes" => ["nullable", "string"],
            "closing" => ["required", "string"],
            "signature_name" => ["nullable", "string", "max:180"],
            "signature_designation" => ["nullable", "string", "max:180"],
            "signature_company" => ["nullable", "string", "max:180"],
            "guardian_relation" => ["nullable", "string", "in:S/o,D/o,W/o"],
            "signature_image" => ["nullable", "file", "image", "mimes:png,jpg,jpeg,webp,svg", "max:2048"],
            "existing_signature_image" => ["nullable", "string"],
            "dynamic_fields" => ["nullable"],
            "approved_amount" => ["required", "numeric", "min:0"],
            "disbursement_mode" => ["required", "string", "max:120"],
            "process_fee" => ["nullable", "numeric", "min:0"],
            "tenure" => ["required", "integer", "min:1"],
            "monthly_principal_settlement" => ["required", "numeric", "min:0"],
            "monthly_portal_service_charges" => ["required", "numeric", "min:0"],
            "sanction_date" => ["required", "date_format:Y-m-d"],
            "letter_address" => ["required", "string", "max:2000"],
        ]);
    }

    private function buildPdfData(array $data, User $agent, ?string $signatureImageUrl, string $sanctionNumber): array
    {
        $monthlyPrincipal = (float) ($data["monthly_principal_settlement"] ?? 0);
        $monthlyCharges   = (float) ($data["monthly_portal_service_charges"] ?? 0);
        $totalMonthly     = $monthlyPrincipal + $monthlyCharges;

        $agentAddress   = $this->buildAgentAddress($agent);
        $letterAddress  = trim($data["letter_address"] ?? "") !== "" ? $data["letter_address"] : $agentAddress;
        $sanctionDate   = !empty($data["sanction_date"]) ? date("d/m/Y", strtotime($data["sanction_date"])) : now()->format("d/m/Y");

        $dynamicFields = [
            'agent_name'                    => $agent->name ?? '',
            'agent_email'                   => $agent->email ?? '',
            'agent_id'                      => $agent->detail?->agent_id_number ?? '',
            'agent_address'                 => $agentAddress,
            'letter_address'                => $letterAddress,
            'business_name'                 => $agent->business?->name ?? '',
            'letter_no'                     => SanctionLetter::formatSanctionLetterNo($sanctionNumber),
            'reference_no'                  => SanctionLetter::formatReferenceNo($sanctionNumber),
            'sanction_number'               => $sanctionNumber,
            'document_title'                => $data["title"] ?? '',
            'email_subject'                 => $data["subject"] ?? '',
            'approved_amount'               => (float) ($data["approved_amount"] ?? 0),
            'disbursement_mode'             => $data["disbursement_mode"] ?? 'Bank Account Transfer',
            'process_fee'                   => (float) ($data["process_fee"] ?? 0),
            'tenure'                        => (int) ($data["tenure"] ?? 0) . ' Months',
            'monthly_principal_settlement'  => $monthlyPrincipal,
            'monthly_portal_service_charges'=> $monthlyCharges,
            'total_monthly_settlement'      => $totalMonthly,
            'sanction_date'                 => $sanctionDate,
            'signature_name'                => $data["signature_name"] ?? '',
            'signature_designation'         => $data["signature_designation"] ?? '',
            'signature_company'             => $data["signature_company"] ?? '',
            'guardian_relation'             => $data["guardian_relation"] ?? 'S/o',
        ];

        if (!empty($data["dynamic_fields"])) {
            $custom = $this->decodeDynamicFields($data["dynamic_fields"]);
            $dynamicFields = array_merge($dynamicFields, $custom);
        }

        $availableKeys = [
            '[[agent_name]]'          => $dynamicFields['agent_name'],
            '[[agent_email]]'         => $dynamicFields['agent_email'],
            '[[agent_id]]'            => $dynamicFields['agent_id'],
            '[[agent_address]]'       => $letterAddress,
            '[[business_name]]'       => $dynamicFields['business_name'],
            '[[letter_no]]'           => SanctionLetter::formatSanctionLetterNo($sanctionNumber),
            '[[reference_no]]'        => SanctionLetter::formatReferenceNo($sanctionNumber),
            '[[approved_amount]]'     => '₹ ' . number_format($dynamicFields['approved_amount'], 2),
            '[[sanction_date]]'       => $sanctionDate,
            '[[document_title]]'      => $dynamicFields['document_title'],
            '[[email_subject]]'       => $dynamicFields['email_subject'],
            '[[signature_name]]'      => $dynamicFields['signature_name'],
            '[[signature_designation]]' => $dynamicFields['signature_designation'],
            '[[signature_company]]'   => $dynamicFields['signature_company'],
        ];

        $replaceTokens = function ($text) use ($availableKeys, $dynamicFields) {
            $allKeys = array_merge($availableKeys, $dynamicFields);
            $text = str_replace(array_keys($allKeys), array_values($allKeys), $text ?? '');
            $text = preg_replace('/\[\[bold:(.*?)\]\]/', '<strong>$1</strong>', $text);
            $text = str_replace('\n', "\n", $text);
            return $text;
        };

        return [
            "title" => $replaceTokens($data["title"]),
            "subject" => $replaceTokens($data["subject"]),
            "greeting" => $replaceTokens($data["greeting"]),
            "body" => $replaceTokens($data["body"]),
            "notes" => $replaceTokens($data["notes"] ?? null),
            "dynamic_fields" => $dynamicFields,
            "closing" => $replaceTokens($data["closing"]),
            "signature_name" => $data["signature_name"] ?? null,
            "guardian_relation" => $data["guardian_relation"] ?? "S/o",
            "signature_designation" => $data["signature_designation"] ?? null,
            "signature_company" => $data["signature_company"] ?? null,
            "signature_image_url" => $signatureImageUrl, "agent" => $agent,
        ];
    }

    private function resolveSignatureUrl(Request $request): ?string
    {
        if ($request->hasFile("signature_image")) {
            $path = $request->file("signature_image")->store("signature-images", "public");
            return asset("storage/" . $path);
        }
        if ($request->filled("existing_signature_image")) {
            return asset("storage/" . $request->input("existing_signature_image"));
        }
        $lastSignature = SystemSetting::getValue('last_signature_image');
        if ($lastSignature) {
            return asset("storage/" . $lastSignature);
        }
        return null;
    }

    private function decodeDynamicFields(mixed $raw): array
    {
        $fields = [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (!empty($item["key"]) && isset($item["value"])) {
                        $fields[$item["key"]] = $item["value"];
                    }
                }
            }
        } elseif (is_array($raw)) {
            foreach ($raw as $item) {
                if (!empty($item["key"]) && isset($item["value"])) {
                    $fields[$item["key"]] = $item["value"];
                }
            }
        }
        return $fields;
    }

    private function buildAgentAddress(User $agent): string
    {
        $address = $agent->detail?->current_address ?? '';
        if ($agent->detail?->current_city) $address .= ', ' . $agent->detail->current_city;
        if ($agent->detail?->current_state) $address .= ', ' . $agent->detail->current_state;
        if ($agent->detail?->current_pincode) $address .= ' - ' . $agent->detail->current_pincode;
        return trim($address);
    }

    private function peekSanctionNumber(): int
    {
        return (int) SystemSetting::getValue(self::SEQUENCE_KEY, self::SEQUENCE_START);
    }

    private function allocateSanctionNumber(): int
    {
        return DB::transaction(function () {
            $row = DB::table('system_settings')->where('key', self::SEQUENCE_KEY)->lockForUpdate()->first();
            if (!$row) {
                DB::table('system_settings')->insert([
                    'key' => self::SEQUENCE_KEY,
                    'value' => (string) (self::SEQUENCE_START + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return self::SEQUENCE_START;
            }
            $next = (int) $row->value;
            DB::table('system_settings')->where('key', self::SEQUENCE_KEY)->update([
                'value' => (string) ($next + 1),
                'updated_at' => now(),
            ]);
            return $next;
        });
    }

    private function disbursementModes(): array
    {
        return [
            'Bank Account Transfer',
            'UPI Transfer',
            'Digital Wallet',
            'Cheque',
            'Demand Draft',
        ];
    }
}