<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
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

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
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

                // Check if user exists
                $user = User::where('email', $row['email'])->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'phone' => $row['phone'] ?? null,
                        'password' => \Illuminate\Support\Facades\Hash::make('Agent@12345'), // Default password
                        'role_id' => $role->id,
                        'status' => strtolower($row['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
                    ]);
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
                if (!isset($detailData['mobile']) && !empty($row['phone'])) {
                    $detailData['mobile'] = $row['phone'];
                }

                if (!empty($detailData)) {
                    $user->detail()->updateOrCreate(
                        ['user_id' => $user->id],
                        $detailData
                    );
                }
            }
            DB::commit();
            return back()->with('success', "Import complete! $imported imported, $skipped skipped.");
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

        DB::transaction(function () use (
            $agent,
            $data
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

            $agent->detail()->updateOrCreate(
                ['user_id' => $agent->id],
                ['personal_email' => $data['personal_email'] ?? null]
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

    public function toggleStatus(User $agent)
    {
        abort_unless($agent->isAgent(), 404);

        $agent->update([
            'status' => $agent->status === 'active' ? 'inactive' : 'active'
        ]);

        $statusText = $agent->status === 'active' ? 'enabled' : 'disabled';

        return back()->with(
            'success',
            "Agent login access has been {$statusText}."
        );
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
