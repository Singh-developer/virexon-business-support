<?php
namespace App\Http\Controllers;
use App\Models\AssertionLetter;
use App\Models\User;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\AssertionLetterMail;

class AssertionLetterController extends Controller
{
    public function index(Request $request)
    {
        $letters = AssertionLetter::with(["user", "business"])->latest()->paginate(15)->withQueryString();
        return view("assertions.index", compact("letters"));
    }

    public function create(Request $request)
    {
        $agents = User::whereHas("role", fn($q) => $q->where("slug", "agent"))->orderBy("name")->get();
        $selectedAgent = null;
        if ($request->filled("agent_id")) { $selectedAgent = User::with("business", "detail")->find($request->agent_id); }
        elseif ($request->filled("user_id")) { $selectedAgent = User::with("business", "detail")->find($request->user_id); }
        
        $existingSignature = \App\Models\SystemSetting::getValue('last_signature_image');
        
        return view("assertions.create", ["agents" => $agents, "selectedAgent" => $selectedAgent, "existingSignature" => $existingSignature]);
    }

    public function preview(Request $request)
    {
        $data = $this->validateForm($request);
        $agent = User::with("business", "detail")->findOrFail($data["user_id"]);
        $signatureImageUrl = null;
        if ($request->hasFile("signature_image")) {
            $path = $request->file("signature_image")->store("signature-images", "public");
            $signatureImageUrl = asset("storage/" . $path);
        } elseif ($request->filled("existing_signature_image")) {
            $signatureImageUrl = asset("storage/" . $request->input("existing_signature_image"));
        } else {
            $lastSignature = \App\Models\SystemSetting::getValue('last_signature_image');
            if ($lastSignature) {
                $signatureImageUrl = asset("storage/" . $lastSignature);
            }
        }
        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl);
        $pdf = Pdf::loadView("assertions.pdf", $pdfData)->setPaper("letter", "portrait");
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
        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl);
        $pdf = Pdf::loadView("assertions.pdf", $pdfData)->setPaper("letter", "portrait");
        return $pdf->stream("assertion-letter-" . now()->format("Ymd-His") . ".pdf");
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
            \App\Models\SystemSetting::setValue('last_signature_image', $signatureImagePath);
        } else {
            $lastSignature = \App\Models\SystemSetting::getValue('last_signature_image');
            if ($lastSignature) {
                $signatureImageUrl = asset("storage/" . $lastSignature);
                $signatureImagePath = $lastSignature;
            }
        }
        $pdfData = $this->buildPdfData($data, $agent, $signatureImageUrl);
        $pdf = Pdf::loadView("assertions.pdf", $pdfData)->setPaper("letter", "portrait");
        $filename = "assertion-letter-" . $agent->id . "-" . now()->format("Ymd-His") . ".pdf";
        $relativePath = "assertion-letters/" . $filename;
        Storage::disk("public")->put($relativePath, $pdf->output());
        $dynamicFields = [];
        if ($request->has("dynamic_fields")) {
            $raw = $request->input("dynamic_fields");
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (!empty($item["key"]) && isset($item["value"])) {
                            $dynamicFields[$item["key"]] = $item["value"];
                        }
                    }
                }
            }
        }
        $letter = AssertionLetter::create([
            "user_id" => $agent->id, "business_id" => $agent->business_id,
            "title" => $data["title"], "subject" => $data["subject"],
            "greeting" => $data["greeting"], "body" => $data["body"],
            "dynamic_fields" => !empty($dynamicFields) ? $dynamicFields : null,
            "closing" => $data["closing"],
            "notes" => $data["notes"] ?? null,
            "signature_name" => $data["signature_name"],
            "signature_designation" => $data["signature_designation"] ?? null,
            "signature_company" => $data["signature_company"] ?? null,
            "signature_image" => $signatureImagePath,
            "pdf_path" => $relativePath, "status" => "sent", "sent_at" => now(),
        ]);
        $recipientEmail = $agent->detail?->personal_email ?: $agent->email;
        try {
            Mail::to($recipientEmail)->send(new AssertionLetterMail($letter, $agent, $pdf->output(), $filename));
        } catch (\Throwable $e) {
            $audit->record($request, "assertion_letter.email_failed", $letter, ["error" => $e->getMessage()]);
            return back()->withInput()->with("error", "PDF saved but email failed: " . $e->getMessage());
        }
        $audit->record($request, "assertion_letter.sent", $letter, ["to" => $recipientEmail]);
        return redirect()->route("assertions.index")->with("success", "Letter sent to " . $agent->name . " (" . $recipientEmail . ")");
    }

    public function show(AssertionLetter $assertion)
    {
        $assertion->load(["user.business", "user.detail", "business"]);
        $agent = $assertion->user;

        $letterNo = 'VIREXON/AF/' . date('Y') . '/' . str_pad($agent->id ?? 0, 4, '0', STR_PAD_LEFT);
        $agentAddress = $agent->detail?->current_address ?? '';
        if ($agent->detail?->current_city) $agentAddress .= ', ' . $agent->detail->current_city;
        if ($agent->detail?->current_state) $agentAddress .= ', ' . $agent->detail->current_state;
        if ($agent->detail?->current_pincode) $agentAddress .= ' - ' . $agent->detail->current_pincode;

        $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
        if ($advanceAmount == 0 && isset($assertion->dynamic_fields['approved_amount'])) {
            $advanceAmount = $assertion->dynamic_fields['approved_amount'];
        }

        $availableKeys = [
            '[[agent_name]]'          => $agent->name ?? '',
            '[[agent_email]]'         => $agent->email ?? '',
            '[[agent_id]]'            => $agent->detail?->agent_id_number ?? '',
            '[[agent_address]]'       => $agentAddress,
            '[[business_name]]'       => $agent->business?->name ?? '',
            '[[letter_no]]'           => $letterNo,
            '[[approved_amount]]'     => is_numeric($advanceAmount) ? number_format($advanceAmount, 2) : $advanceAmount,
            '[[sanction_date]]'       => now()->format('d/m/Y'),
            '[[document_title]]'      => $assertion->title ?? '',
            '[[email_subject]]'       => $assertion->subject ?? '',
            '[[signature_name]]'      => $assertion->signature_name ?? '',
            '[[signature_designation]]' => $assertion->signature_designation ?? '',
            '[[signature_company]]'   => $assertion->signature_company ?? '',
        ];

        $replaceTokens = function ($text) use ($availableKeys, $assertion) {
            $allKeys = array_merge($availableKeys, $assertion->dynamic_fields ?? []);
            $text = str_replace(array_keys($allKeys), array_values($allKeys), $text ?? '');
            $text = preg_replace('/\[\[bold:(.*?)\]\]/', '<strong>$1</strong>', $text);
            $text = str_replace('\n', "\n", $text);
            return $text;
        };

        $resolvedTitle = $replaceTokens($assertion->title);
        $resolvedBody = $replaceTokens($assertion->body);
        $resolvedGreeting = $replaceTokens($assertion->greeting);
        $resolvedClosing = $replaceTokens($assertion->closing);

        return view("assertions.show", ["letter" => $assertion, "resolvedTitle" => $resolvedTitle, "resolvedBody" => $resolvedBody, "resolvedGreeting" => $resolvedGreeting, "resolvedClosing" => $resolvedClosing]);
    }

    public function download(AssertionLetter $assertion)
    {
        if ($assertion->pdf_path && Storage::disk("public")->exists($assertion->pdf_path)) {
            return Storage::disk("public")->download($assertion->pdf_path);
        }
        $assertion->load(["user.business", "user.detail"]);
        $sigUrl = $assertion->signature_image ? asset("storage/" . $assertion->signature_image) : null;
        $pdf = Pdf::loadView("assertions.pdf", [
            "title" => $assertion->title, "subject" => $assertion->subject,
            "greeting" => $assertion->greeting, "body" => $assertion->body,
            "dynamic_fields" => $assertion->dynamic_fields ?? [],
            "notes" => $assertion->notes,
            "closing" => $assertion->closing,
            "signature_name" => $assertion->signature_name,
            "signature_designation" => $assertion->signature_designation,
            "signature_company" => $assertion->signature_company,
            "signature_image_url" => $sigUrl, "agent" => $assertion->user,
        ])->setPaper("letter", "portrait");
        return $pdf->stream("assertion-letter-" . $assertion->id . ".pdf");
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
            "signature_name" => ["required", "string", "max:180"],
            "signature_designation" => ["nullable", "string", "max:180"],
            "signature_company" => ["nullable", "string", "max:180"],
            "signature_image" => ["nullable", "file", "image", "mimes:png,jpg,jpeg,svg", "max:2048"],
            "existing_signature_image" => ["nullable", "string"],
            "dynamic_fields" => ["nullable"],
        ]);
    }

    private function buildPdfData(array $data, User $agent, ?string $signatureImageUrl): array
    {
        $dynamicFields = [];
        if (!empty($data["dynamic_fields"])) {
            $raw = $data["dynamic_fields"];
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (!empty($item["key"]) && isset($item["value"])) {
                            $dynamicFields[$item["key"]] = $item["value"];
                        }
                    }
                }
            }
        }

        $letterNo = 'VIREXON/AF/' . date('Y') . '/' . str_pad($agent->id ?? 0, 4, '0', STR_PAD_LEFT);
        $agentAddress = $agent->detail?->current_address ?? '';
        if ($agent->detail?->current_city) $agentAddress .= ', ' . $agent->detail->current_city;
        if ($agent->detail?->current_state) $agentAddress .= ', ' . $agent->detail->current_state;
        if ($agent->detail?->current_pincode) $agentAddress .= ' - ' . $agent->detail->current_pincode;

        $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
        if ($advanceAmount == 0 && isset($dynamicFields['approved_amount'])) {
            $advanceAmount = $dynamicFields['approved_amount'];
        }

        $availableKeys = [
            '[[agent_name]]'          => $agent->name ?? '',
            '[[agent_email]]'         => $agent->email ?? '',
            '[[agent_id]]'            => $agent->detail?->agent_id_number ?? '',
            '[[agent_address]]'       => $agentAddress,
            '[[business_name]]'       => $agent->business?->name ?? '',
            '[[letter_no]]'           => $letterNo,
            '[[approved_amount]]'     => is_numeric($advanceAmount) ? number_format($advanceAmount, 2) : $advanceAmount,
            '[[sanction_date]]'       => now()->format('d/m/Y'),
            '[[document_title]]'      => $data["title"] ?? '',
            '[[email_subject]]'       => $data["subject"] ?? '',
            '[[signature_name]]'      => $data["signature_name"] ?? '',
            '[[signature_designation]]' => $data["signature_designation"] ?? '',
            '[[signature_company]]'   => $data["signature_company"] ?? '',
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
            "signature_name" => $data["signature_name"],
            "signature_designation" => $data["signature_designation"] ?? null,
            "signature_company" => $data["signature_company"] ?? null,
            "signature_image_url" => $signatureImageUrl, "agent" => $agent,
        ];
    }
}
