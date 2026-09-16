<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = User::query()
            ->with([
                'business',
                'virtualCard',
                'detail',
                'documents',
            ])
            ->whereHas(
                'role',
                fn($query) => $query->where('slug', 'agent')
            )
            ->latest()
            ->get();

        return view(
            'agents.index',
            compact('agents')
        );
    }

    public function downloadSample()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=agents_import_sample.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];
        
        $columns = [
            'name', 'email', 'phone', 'status', 'application_status', 'agent_id_number', 
            'father_name', 'mother_name', 'guardian_name', 'date_of_birth', 'gender', 
            'mobile', 'personal_email', 'whatsapp_number', 'is_married', 'spouse_name', 
            'spouse_mobile', 'current_address', 'address_line_2', 'current_city', 
            'current_state', 'current_pincode', 'permanent_address', 'permanent_city', 
            'permanent_state', 'permanent_pincode', 'loan_amount', 'loan_tenure', 
            'purpose_of_advance', 'account_name', 'bank_name', 'account_number', 
            'routing_number', 'account_type', 'branch_name', 'pan_number', 'aadhar_number'
        ];

        $sampleData = [
            'John Doe', 'john.doe@example.com', '9876543210', 'active', 'pending', 'AG-1001', 
            'Robert Doe', 'Jane Doe', 'Robert Doe', '1990-01-01', 'male', 
            '9876543210', 'john.personal@example.com', '9876543210', '0', '', 
            '', '123 Main St', 'Apt 4B', 'New York', 
            'NY', '10001', '123 Main St', 'New York', 
            'NY', '10001', '50000', '60', 
            'Business Expansion', 'John Doe', 'Chase Bank', '1234567890', 
            'IFSC1234', 'Savings', 'Downtown Branch', 'ABCDE1234F', '123456789012'
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sampleData);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data); // Get headers
        
        // Convert headers to lowercase for easier mapping
        $header = array_map('strtolower', $header);
        $header = array_map('trim', $header);

        $role = Role::where('slug', 'agent')->first();
        if (!$role) {
            return back()->with('error', 'Agent role not found.');
        }

        $imported = 0;
        $skipped = 0;
        $rowErrors = [];
        $seenPhones = [];
        $seenPans = [];

        DB::beginTransaction();
        try {
            foreach ($data as $line => $row) {
                $lineNo = $line + 2; // 1-based, header is line 1

                // Skip empty rows
                if (count($row) !== count($header)) {
                    $skipped++;
                    continue;
                }

                $row = array_combine($header, $row);

                // Minimum required fields: name, email
                if (empty($row['name']) || empty($row['email'])) {
                    $skipped++;
                    continue;
                }

                $email = trim($row['email']);
                $phone = trim($row['phone'] ?? '');
                $pan = strtoupper(trim($row['pan_number'] ?? ''));

                $phoneDigits = preg_replace('/\D+/', '', $phone);
                if (strlen($phoneDigits) === 12 && str_starts_with($phoneDigits, '91')) {
                    $phoneDigits = substr($phoneDigits, 2);
                }

                // Check if user exists
                $user = User::where('email', $email)->first();
                if (!$user) {
                    // PAN number validation (Indian PAN: ABCDE1234F)
                    if (empty($pan)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): PAN number is required.";
                        $skipped++;
                        continue;
                    }
                    if (!preg_match('/^[A-Z]{5}\d{4}[A-Z]$/', $pan)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): Invalid PAN '{$row['pan_number']}' — expected format ABCDE1234F.";
                        $skipped++;
                        continue;
                    }

                    // Phone number validation (10-digit Indian mobile)
                    if (empty($phone)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): Phone number is required.";
                        $skipped++;
                        continue;
                    }
                    if (!preg_match('/^[6-9]\d{9}$/', $phoneDigits)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): Invalid phone '{$row['phone']}' — expected 10-digit Indian mobile (e.g. 9876543210).";
                        $skipped++;
                        continue;
                    }

                    if (in_array($phoneDigits, $seenPhones, true)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): Phone {$phoneDigits} is duplicated in this file.";
                        $skipped++;
                        continue;
                    }
                    if (in_array($pan, $seenPans, true)) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): PAN {$pan} is duplicated in this file.";
                        $skipped++;
                        continue;
                    }
                    if (User::where('phone', $phoneDigits)->exists()) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): Phone {$phoneDigits} already belongs to another agent.";
                        $skipped++;
                        continue;
                    }
                    if (\App\Models\UserDetail::where('pan_number', $pan)->exists()) {
                        $rowErrors[] = "Row {$lineNo} ({$email}): PAN {$pan} already belongs to another agent.";
                        $skipped++;
                        continue;
                    }

                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $email,
                        'phone' => $phoneDigits,
                        'password' => \Illuminate\Support\Facades\Hash::make("{$pan}@" . substr($phoneDigits, -4)), // Default password: {pan}@{last 4 of phone}
                        'role_id' => $role->id,
                        'status' => strtolower($row['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
                    ]);

                    $seenPhones[] = $phoneDigits;
                    $seenPans[] = $pan;
                    $imported++;
                } else {
                    $skipped++;
                }

                // Update detail if provided
                $detailData = [];
                $detailColumns = [
                    'application_status', 'agent_id_number', 'father_name', 'mother_name', 
                    'guardian_name', 'date_of_birth', 'gender', 'mobile', 'personal_email', 
                    'whatsapp_number', 'is_married', 'spouse_name', 'spouse_mobile', 
                    'current_address', 'address_line_2', 'current_city', 'current_state', 
                    'current_pincode', 'permanent_address', 'permanent_city', 'permanent_state', 
                    'permanent_pincode', 'loan_amount', 'loan_tenure', 'purpose_of_advance', 
                    'account_name', 'bank_name', 'account_number', 'routing_number', 'account_type', 
                    'branch_name', 'pan_number', 'aadhar_number'
                ];

                foreach ($detailColumns as $col) {
                    if (isset($row[$col]) && $row[$col] !== '') {
                        $detailData[$col] = $row[$col];
                    }
                }

                // Fallback for mobile if not provided but phone is
                if (!isset($detailData['mobile']) && !empty($phoneDigits)) {
                    $detailData['mobile'] = $phoneDigits;
                }

                // Always store the PAN in uppercase so it matches the login password.
                if (!empty($pan)) {
                    $detailData['pan_number'] = $pan;
                }

                if (!empty($detailData)) {
                    $user->detail()->updateOrCreate(
                        ['user_id' => $user->id],
                        $detailData
                    );
                }
            }
            DB::commit();
            $response = back()->with('success', "Import complete! $imported imported, $skipped skipped.");

            if (! empty($rowErrors)) {
                $response->with('import_errors', $rowErrors);
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('agents.form', [
            'agent' => new User([
                'status' => 'active',
            ]),

            'businesses' => Business::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::where(
            'slug',
            'agent'
        )->firstOrFail();

        DB::transaction(function () use (
            $data,
            $role
        ) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role_id' => $role->id,
                'business_id' => $data['business_id'],
                'status' => $data['status'],
            ]);

            if (isset($data['personal_email'])) {
                $user->detail()->create([
                    'personal_email' => $data['personal_email']
                ]);
            }
        });

        return redirect()
            ->route('agents.index')
            ->with(
                'success',
                'Agent created successfully. Virtual card must be created separately by Admin.'
            );
    }

    public function edit(User $agent)
    {
        abort_unless(
            $agent->isAgent(),
            404
        );

        $agent->load(['detail', 'referencePersons', 'business', 'virtualCard']);

        return view('agents.form', [
            'agent'      => $agent,
            'businesses' => Business::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(
        Request $request,
        User $agent
    ) {
        abort_unless(
            $agent->isAgent(),
            404
        );

        $data = $this->validated(
            $request,
            $agent
        );

        // Additional validation for limit and commission fields
        $request->validate([
            'max_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'commission_type' => 'nullable|in:percentage,fixed',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_fixed' => 'nullable|numeric|min:0|max:999999999999.99',
        ]);

        DB::transaction(function () use (
            $agent,
            $data,
            $request
        ) {
            $agent->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'business_id' => $data['business_id'],
                'status' => $data['status'],

                ...(
                    filled($data['password'] ?? null)
                    ? ['password' => $data['password']]
                    : []
                ),
            ]);

            // Update user details including limit and commission settings
            $agent->detail()->updateOrCreate(
                ['user_id' => $agent->id],
                [
                    'personal_email' => $data['personal_email'] ?? null,
                    'max_limit' => $request->max_limit ?? 500000.00,
                    'commission_type' => $request->commission_type ?? 'percentage',
                    'commission_rate' => $request->commission_rate ?? 0,
                    'commission_fixed' => $request->commission_fixed ?? 0,
                ]
            );

            /*
             * DO NOT CREATE A CARD HERE.
             *
             * If an existing card belongs to the Agent,
             * synchronize business/cardholder information.
             */
            if ($agent->virtualCard) {
                $agent->virtualCard->update([
                    'business_id' => $agent->business_id,
                    'cardholder_name' => $agent->name,
                ]);
            }
        });

        return redirect()
            ->route('agents.index')
            ->with(
                'success',
                'Agent details updated.'
            );
    }

    public function toggleStatus(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $desired = $request->input('status');

        if ($desired !== null) {
            $request->validate(['status' => ['required', 'in:active,inactive']]);
            $agent->update(['status' => $desired]);
        } else {
            $agent->update([
                'status' => $agent->status === 'active' ? 'inactive' : 'active',
            ]);
        }

        $statusText = $agent->status === 'active' ? 'enabled' : 'disabled';

        return back()->with(
            'success',
            "Agent login access has been {$statusText}."
        );
    }

    /**
     * Bulk actions for selected agents:
     *  - trash        Move whole selection to trash (cascades to related records)
     *  - activate     Re-enable login access for the whole selection
     *  - deactivate   Disable login access for the whole selection
     */
    public function bulk(Request $request, AuditService $audit)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'action' => 'required|in:trash,activate,deactivate',
            'ids'    => 'required|string',
        ]);

        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $data['ids'])))));

        if (empty($ids)) {
            return back()->with('error', 'No agents were selected.');
        }

        $agents = User::query()
            ->with(['role'])
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn ($user) => $user->isAgent());

        if ($agents->isEmpty()) {
            return back()->with('error', 'No agents were found. The selection may already be trashed.');
        }

        $count = 0;

        foreach ($agents as $agent) {
            switch ($data['action']) {
                case 'trash':
                    if (! $agent->trashed()) {
                        $agent->delete();
                        $audit->record($request, 'agent.trashed', $agent, [
                            'agent_id' => $agent->id,
                            'bulk'     => true,
                        ]);
                        $count++;
                    }
                    break;

                case 'activate':
                    if ($agent->status !== 'active') {
                        $agent->update(['status' => 'active']);
                        $count++;
                    }
                    break;

                case 'deactivate':
                    if ($agent->status !== 'inactive') {
                        $agent->update(['status' => 'inactive']);
                        $count++;
                    }
                    break;
            }
        }

        $label = match ($data['action']) {
            'trash'      => 'moved to trash',
            'activate'   => 'enabled',
            'deactivate' => 'disabled',
        };

        return back()->with('success', "{$count} agent(s) {$label}.");
    }

    /**
     * Soft delete the agent. Related records (virtual card, sanction letters,
     * documents, advances, commissions, payments, details, references) are
     * trashed together. Uploaded files stay in place until the agent is
     * permanently removed from the trash.
     */
    public function destroy(Request $request, User $agent, AuditService $audit)
    {
        abort_unless($agent->isAgent(), 404);
        abort_unless($request->user()->isAdmin(), 403);

        if ($agent->trashed()) {
            return back()->with('error', 'This agent is already in the trash.');
        }

        $agent->delete();

        $audit->record($request, 'agent.trashed', $agent, [
            'agent_id' => $agent->id,
        ]);

        return redirect()
            ->route('agents.index')
            ->with('success', "Agent {$agent->name} moved to trash. Virtual card, sanction letters, documents and related records were trashed too.");
    }

    public function show(User $agent)
    {
        abort_unless(
            $agent->isAgent(),
            404
        );

        $agent->load([
            'business',
            'virtualCard',
            'detail',
            'referencePersons',
            'role',
        ]);

        return view(
            'agents.show',
            compact('agent')
        );
    }

    public function updateApplicationStatus(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $data = $request->validate([
            'application_status' => 'required|in:pending,form_received,approved,rejected',
        ]);

        $agent->detail()->updateOrCreate(
            ['user_id' => $agent->id],
            ['application_status' => $data['application_status']]
        );

        // Notify the agent when admin marks form as received
        if ($data['application_status'] === 'form_received') {
            $agent->notify(new \App\Notifications\DocumentReviewedNotification(
                new \App\Models\AgentDocument([
                    'user_id'       => $agent->id,
                    'document_type' => 'form',
                    'status'        => 'form_received',
                    'admin_note'    => 'Your Fund Application form has been received! Please log in and upload your documents to proceed.',
                ])
            ));
        }

        $statusLabel = ucfirst(str_replace('_', ' ', $data['application_status']));

        return back()->with('success', "Agent application has been marked as {$statusLabel}.");
    }

    public function updateLimitCommission(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $data = $request->validate([
            'max_limit' => 'nullable|numeric|min:0|max:999999999999.99',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_fixed' => 'nullable|numeric|min:0|max:999999999999.99',
            'action' => 'required|in:save,save_and_card',
        ]);

        $maxLimit = $data['max_limit'] ?? 500000.00;

        $agent->detail()->updateOrCreate(
            ['user_id' => $agent->id],
            [
                'max_limit' => $maxLimit,
                'commission_type' => $data['commission_type'] ?? 'percentage',
                'commission_rate' => $data['commission_rate'] ?? 0,
                'commission_fixed' => $data['commission_fixed'] ?? 0,
            ]
        );

        // Sync virtual card limits if card exists
        if ($agent->virtualCard) {
            $agent->virtualCard->update([
                'card_limit' => $maxLimit,
                'daily_limit' => $maxLimit,
                'monthly_limit' => $maxLimit * 10,
                'per_transaction_limit' => $maxLimit,
            ]);
        }

        $message = 'Limit & Commission settings updated successfully.';

        // Create virtual card if requested and agent doesn't have one
        if ($data['action'] === 'save_and_card') {
            if ($agent->virtualCard) {
                $message .= ' Agent already has a virtual card.';
            } else {
                $provider = app(\App\Services\MockVirtualCardProvider::class);
                $providerCard = $provider->createCard($agent->name);
                $reference = 'VC-' . now()->format('ymd') . '-' . strtoupper(str()->random(8));

                \App\Models\VirtualCard::create([
                    'business_id' => $agent->business_id,
                    'agent_id' => $agent->id,
                    'reference' => $reference,
                    'encrypted_pan' => encrypt($providerCard['pan']),
                    'last4' => $providerCard['last4'],
                    'provider_card_id' => $providerCard['provider_card_id'],
                    'cardholder_name' => $providerCard['cardholder_name'],
                    'status' => 'active',
                    'card_limit' => $maxLimit,
                    'daily_limit' => $maxLimit,
                    'monthly_limit' => $maxLimit * 10,
                    'current_usage' => 0,
                ]);

                $message .= ' Virtual card created successfully.';
            }
        }

        return back()->with('success', $message);
    }

    public function updateDetails(Request $request, User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $detailId = optional($agent->detail)->id;

        $data = $request->validate([
            'personal_email'     => 'nullable|email|max:255',
            'mobile'             => 'nullable|string|max:20',
            'whatsapp_number'    => 'nullable|string|max:20',
            'guardian_name'      => 'nullable|string|max:255',
            'father_name'        => 'nullable|string|max:255',
            'mother_name'        => 'nullable|string|max:255',
            'agent_id_number'    => ['nullable', 'string', 'max:255', Rule::unique('user_details', 'agent_id_number')->ignore($detailId)],
            'date_of_birth'      => 'nullable|date',
            'gender'             => 'nullable|in:male,female,other',
            'is_married'         => 'nullable|boolean',
            'spouse_name'        => 'nullable|string|max:255',
            'spouse_mobile'      => 'nullable|string|max:20',
            'current_address'    => 'nullable|string|max:500',
            'address_line_2'     => 'nullable|string|max:255',
            'current_city'       => 'nullable|string|max:255',
            'current_state'      => 'nullable|string|max:255',
            'current_pincode'    => 'nullable|string|max:20',
            'permanent_address'  => 'nullable|string|max:500',
            'permanent_city'     => 'nullable|string|max:255',
            'permanent_state'    => 'nullable|string|max:255',
            'permanent_pincode'  => 'nullable|string|max:20',
            'loan_amount'        => 'nullable|numeric|min:0|max:999999999999.99',
            'loan_tenure'        => 'nullable|integer|min:1|max:360',
            'purpose_of_advance' => 'nullable|string|max:1000',
            'account_name'       => 'nullable|string|max:255',
            'bank_name'          => 'nullable|string|max:255',
            'account_number'     => 'nullable|string|max:255',
            'routing_number'     => 'nullable|string|max:255',
            'account_type'       => 'nullable|string|max:255',
            'branch_name'        => 'nullable|string|max:255',
            'pan_number'         => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'pan_number')->ignore($detailId)],
            'aadhar_number'      => ['nullable', 'string', 'max:20', Rule::unique('user_details', 'aadhar_number')->ignore($detailId)],

            'references'                        => 'nullable|array|max:5',
            'references.*.id'                   => 'nullable|exists:reference_people,id',
            'references.*.person_name'          => 'required_with:references|string|max:255',
            'references.*.mobile'               => 'required_with:references|string|max:20',
            'references.*.company_agent_id'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($agent, $data, $request) {
            $detailData = collect($data)->except(['references'])->toArray();

            // Keep father_name in sync when only guardian_name is supplied (legacy column).
            if (array_key_exists('guardian_name', $detailData) && empty($detailData['father_name'])) {
                $detailData['father_name'] = $detailData['guardian_name'];
            }

            // Normalise empty strings to null so unique columns don't clash on "".
            foreach (['agent_id_number', 'pan_number', 'aadhar_number'] as $uniqueKey) {
                if (array_key_exists($uniqueKey, $detailData) && $detailData[$uniqueKey] === '') {
                    $detailData[$uniqueKey] = null;
                }
            }

            if (! empty($detailData) || $agent->detail === null) {
                $agent->detail()->updateOrCreate(
                    ['user_id' => $agent->id],
                    $detailData
                );
            }

            // Sync references (edit existing, add new, drop removed).
            if ($request->has('references')) {
                $keepIds = [];
                foreach ($data['references'] ?? [] as $ref) {
                    // Skip fully empty rows.
                    if (empty($ref['person_name']) && empty($ref['mobile']) && empty($ref['company_agent_id'])) {
                        continue;
                    }

                    // Company agent ID must stay unique — skip rows that would collide.
                    if (! empty($ref['company_agent_id'])) {
                        $collision = \App\Models\ReferencePerson::where('company_agent_id', $ref['company_agent_id'])
                            ->when(! empty($ref['id']), fn ($q) => $q->where('id', '!=', $ref['id']))
                            ->exists();
                        if ($collision) {
                            continue;
                        }
                    }

                    if (! empty($ref['id'])) {
                        $existing = $agent->referencePersons()->where('id', $ref['id'])->first();
                        if ($existing) {
                            $existing->update([
                                'person_name'      => $ref['person_name'],
                                'mobile'           => $ref['mobile'],
                                'company_agent_id' => $ref['company_agent_id'] ?? $existing->company_agent_id,
                            ]);
                            $keepIds[] = $existing->id;
                        }
                    } else {
                        $created = $agent->referencePersons()->create([
                            'person_name'      => $ref['person_name'],
                            'mobile'           => $ref['mobile'],
                            'company_agent_id' => $ref['company_agent_id'] ?? ('REF-' . strtoupper(str()->random(8))),
                        ]);
                        $keepIds[] = $created->id;
                    }
                }

                // Delete references removed by the admin.
                $agent->referencePersons()->whereNotIn('id', $keepIds)->delete();
            }
        });

        return back()->with('success', 'Agent details updated.');
    }

    private function validated(
        Request $request,
        ?User $agent = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
            ],

            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($agent?->id),
            ],

            'personal_email' => [
                'nullable',
                'email',
                'max:180',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:40',
            ],

            'business_id' => [
                'required',
                'exists:businesses,id',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'password' => [
                $agent ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);
    }
}
