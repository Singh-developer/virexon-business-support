<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register-extended');
    }

    public function create_new()
    {
        return view('auth.register-new');
    }

    public function store_new(Request $request)
    {
        $userDetailId = auth()->check() ? optional(auth()->user()->detail)->id : null;
        $userId = auth()->check() ? auth()->id() : null;

        $validated = $request->validate([
            // Personal Info
            'name' => 'required|string|max:255',
            'guardian_name' => 'required|string|max:255',
            'agent_id_number' => 'required|string|max:255|unique:user_details,agent_id_number,' . $userDetailId,
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'is_married' => 'required|boolean',
            'mobile' => 'required|string|max:20',
            'personal_email' => 'required|string|email|max:255',
            'pan_number' => 'required|string|max:20|unique:user_details,pan_number,' . $userDetailId,
            
            // Address Info
            'current_address' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'current_city' => 'required|string|max:255',
            'current_state' => 'required|string|max:255',
            'current_pincode' => 'required|string|max:20',
            
            // Bank Details
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'routing_number' => 'required|string|max:255', // IFSC Code
            'account_type' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            
            // Advance Details
            'loan_amount' => 'required|numeric|max:500000',
            'purpose_of_advance' => 'required|string|max:300',
            
            // References
            'references' => 'required|array|min:1',
            'references.*.person_name' => 'required|string|max:255',
            'references.*.mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'references.*.company_agent_id' => 'required|string|max:255|unique:reference_people,company_agent_id',
            
            // OTP
            'otp' => 'required|digits:6',
        ]);

        if ($request->otp != session('loan_form_otp')) {
            return back()->withErrors(['otp' => 'The provided OTP is incorrect or has expired.'])->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $user->update([
                'name' => $validated['name'],
            ]);

            $user->detail()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'agent_id_number' => $validated['agent_id_number'],
                    'father_name' => $validated['guardian_name'], // Save guardian_name into father_name for backward compatibility
                    'guardian_name' => $validated['guardian_name'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'is_married' => $validated['is_married'],
                    'mobile' => $validated['mobile'],
                    'personal_email' => $validated['personal_email'],
                    'pan_number' => $validated['pan_number'],
                    
                    'current_address' => $validated['current_address'],
                    'address_line_2' => $validated['address_line_2'],
                    'current_city' => $validated['current_city'],
                    'current_state' => $validated['current_state'],
                    'current_pincode' => $validated['current_pincode'],
                    
                    'account_name' => $validated['account_name'],
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'routing_number' => $validated['routing_number'],
                    'account_type' => $validated['account_type'],
                    'branch_name' => $validated['branch_name'],
                    
                    'loan_amount' => $validated['loan_amount'],
                    'purpose_of_advance' => $validated['purpose_of_advance'],
                    
                    'loan_tenure' => 60, // Fixed 60 months tenure as per UI
                ]
            );

            // Store References
            $user->referencePersons()->delete(); // Clear old references if any
            foreach ($validated['references'] as $ref) {
                $user->referencePersons()->create([
                    'person_name' => $ref['person_name'],
                    'mobile' => $ref['mobile'],
                    'company_agent_id' => $ref['company_agent_id'],
                ]);
            }

            session()->forget('loan_form_otp');

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Application submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Submission failed: ' . $e->getMessage())->withInput();
        }
    }

    public function store(Request $request)
    {
        $step = (int) $request->input('current_step', 1);

        // ---------------------------------------------------------
        // 1. VALIDATION (Only validate the current step's fields)
        // ---------------------------------------------------------
        if ($step === 1) {
            $rules = [
                'name' => 'required|string|max:255',
                // Ignore unique check if the user clicks "Back" and resubmits their own email
                'email' => 'required|string|email|max:255|unique:users,email,' . optional(auth()->user())->id,
                'role_id' => 3,
            ];

            if (!auth()->check()) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            $request->validate($rules);
        } elseif ($step === 2) {
            $request->validate([
                'agent_id_number' => 'required|string|unique:user_details,agent_id_number,' . optional(auth()->user()->detail)->id,
                'father_name' => 'required|string',
                'mother_name' => 'required|string',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
            ]);
        } elseif ($step === 3) {
            $request->validate([
                'mobile' => 'required|string',
                'whatsapp_number' => 'nullable|string',
                'is_married' => 'required|boolean',
                'spouse_name' => 'required_if:is_married,1|nullable|string',
                'spouse_mobile' => 'required_if:is_married,1|nullable|string',
            ]);
        } elseif ($step === 4) {
            $request->validate([
                'current_address' => 'required|string',
                'current_city' => 'required|string',
                'current_state' => 'required|string',
                'current_pincode' => 'required|string',
                'permanent_address' => 'required|string',
                'permanent_city' => 'required|string',
                'permanent_state' => 'required|string',
                'permanent_pincode' => 'required|string',
            ]);
        } elseif ($step === 5) {
            $request->validate([
                'loan_amount' => 'required|numeric',
                'loan_tenure' => 'required|numeric',
            ]);
        } elseif ($step === 6) {
            $request->validate([
                'account_name' => 'required|string',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'routing_number' => 'required|string',
            ]);
        } elseif ($step === 7) {
            $request->validate([
                'pan_number' => 'required|string|unique:user_details,pan_number,' . optional(auth()->user()->detail)->id,
                'aadhar_number' => 'required|string|unique:user_details,aadhar_number,' . optional(auth()->user()->detail)->id,
                'pan_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'aadhar_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'terms' => 'accepted',
                'otp' => 'required|digits:6', // Added OTP validation rule
            ]);

            // Verify the OTP before allowing database updates for Step 7
            if ($request->otp != session('loan_form_otp')) {
                return back()->withErrors(['otp' => 'The provided OTP is incorrect or has expired.'])->withInput();
            }
        }

        // ---------------------------------------------------------
        // 2. DATABASE SAVE (Commit the step data immediately)
        // ---------------------------------------------------------
        try {
            DB::beginTransaction();

            // 1. Handle User Account (Step 1)
            if ($step === 1) {
                if (!auth()->check()) {
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => 'agent'
                    ]);
                    auth()->login($user);
                } else {
                    $user = auth()->user();
                    $user->update(['name' => $request->name, 'email' => $request->email]);
                }
            }

            $user = auth()->user();
            if (!$user) {
                throw new \Exception('Session expired. Please restart your application.');
            }

            if ($request->filled('loan_amount') && $request->filled('loan_tenure')) {
                $user->detail()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'loan_amount' => $request->loan_amount,
                        'loan_tenure' => $request->loan_tenure
                    ]
                );
            }

            // 2. Save details based on the current step
            if ($step === 2) {
                $user->detail()->updateOrCreate(['user_id' => $user->id], $request->only('agent_id_number', 'father_name', 'mother_name', 'date_of_birth', 'gender'));
            } elseif ($step === 3) {
                $user->detail()->updateOrCreate(['user_id' => $user->id], $request->only('mobile', 'whatsapp_number', 'is_married', 'spouse_name', 'spouse_mobile'));
            } elseif ($step === 4) {
                $user->detail()->updateOrCreate(['user_id' => $user->id], $request->only('current_address', 'current_city', 'current_state', 'current_pincode', 'permanent_address', 'permanent_city', 'permanent_state', 'permanent_pincode'));
            } elseif ($step === 5) {
                // Step 5 is technically already saved by the block above, but keeping this is fine
                $user->detail()->updateOrCreate(['user_id' => $user->id], $request->only('loan_amount', 'loan_tenure'));
            } elseif ($step === 6) {
                $user->detail()->updateOrCreate(['user_id' => $user->id], $request->only('account_name', 'bank_name', 'account_number', 'routing_number'));
            } elseif ($step === 7) {
                $detail = $user->detail()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['pan_number' => strtoupper($request->pan_number), 'aadhar_number' => $request->aadhar_number]
                );

                if ($request->hasFile('pan_file')) {
                    $detail->update(['pan_file_path' => $request->file('pan_file')->store('documents/pan', 'public')]);
                }
                if ($request->hasFile('aadhar_file')) {
                    $detail->update(['aadhar_file_path' => $request->file('aadhar_file')->store('documents/aadhar', 'public')]);
                }

                // OTP is valid and data is saved; clear it from session to prevent reuse
                session()->forget('loan_form_otp');
            }

            DB::commit();

            if ($step === 7) {
                return redirect()->route('dashboard')->with('success', 'Application completed and submitted for admin approval!');
            }

            return back()->with('step', $step + 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registration failed on step ' . $step . ': ' . $e->getMessage())->withInput();
        }
    }
}
