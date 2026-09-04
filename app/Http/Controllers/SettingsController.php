<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Services\PaymentModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Agent + Admin: own profile
    |--------------------------------------------------------------------------
    */

    public function profile()
    {
        $user = Auth::user()->load('detail', 'role');
        $isAgent = $user->isAgent();
        $detail = $isAgent ? $user->detail : null;
        $isApproved = $isAgent && $detail && $detail->application_status === 'approved';

        return view(
            'settings.profile',
            compact('user', 'isAgent', 'detail', 'isApproved')
        );
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $isAgent = $user->isAgent();
        $detail = $user->detail;
        $isApproved = $isAgent && $detail && $detail->application_status === 'approved';

        $rules = [
            'name' => 'required|string|max:180',
            'email' => 'required|email|max:180|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:40',
        ];

        if ($isAgent) {
            // Always editable fields (even after approval)
            $rules['personal_email'] = 'nullable|email|max:255';
            $rules['mobile'] = 'nullable|string|max:20';
            $rules['current_address'] = 'nullable|string|max:500';
            $rules['address_line_2'] = 'nullable|string|max:255';
            $rules['current_city'] = 'nullable|string|max:255';
            $rules['current_state'] = 'nullable|string|max:255';
            $rules['current_pincode'] = 'nullable|string|max:20';
            $rules['account_name'] = 'nullable|string|max:255';
            $rules['bank_name'] = 'nullable|string|max:255';
            $rules['account_number'] = 'nullable|string|max:255';
            $rules['routing_number'] = 'nullable|string|max:255';
            $rules['account_type'] = 'nullable|string|max:255';
            $rules['branch_name'] = 'nullable|string|max:255';

            if (!$isApproved) {
                // Fields locked after approval
                $rules['guardian_name'] = 'nullable|string|max:255';
                $rules['agent_id_number'] = 'nullable|string|max:255|unique:user_details,agent_id_number,' . optional($detail)->id;
                $rules['date_of_birth'] = 'nullable|date';
                $rules['gender'] = 'nullable|in:male,female,other';
                $rules['is_married'] = 'nullable|boolean';
                $rules['pan_number'] = 'nullable|string|max:20|unique:user_details,pan_number,' . optional($detail)->id;
                $rules['purpose_of_advance'] = 'nullable|string|max:300';
            }
        }

        $data = $request->validate($rules);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($isAgent) {
            // Always save these fields
            $detailData = [
                'personal_email' => $data['personal_email'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'current_address' => $data['current_address'] ?? null,
                'address_line_2' => $data['address_line_2'] ?? null,
                'current_city' => $data['current_city'] ?? null,
                'current_state' => $data['current_state'] ?? null,
                'current_pincode' => $data['current_pincode'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'routing_number' => $data['routing_number'] ?? null,
                'account_type' => $data['account_type'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
            ];

            if (!$isApproved) {
                // Only save these if not yet approved
                $detailData = array_merge($detailData, [
                    'guardian_name' => $data['guardian_name'] ?? null,
                    'father_name' => $data['guardian_name'] ?? null,
                    'agent_id_number' => $data['agent_id_number'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'is_married' => $data['is_married'] ?? null,
                    'pan_number' => $data['pan_number'] ?? null,
                    'purpose_of_advance' => $data['purpose_of_advance'] ?? null,
                ]);
            }

            $user->detail()->updateOrCreate(
                ['user_id' => $user->id],
                $detailData
            );
        }

        return back()->with('success', 'Your profile has been updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin only: gateway settings
    |--------------------------------------------------------------------------
    */

    public function gateways()
    {
        abort_unless(
            auth()->user()->isAdmin(),
            403
        );

        $gateways = PaymentGateway::orderBy(
            'id'
        )->get();

        $paymentMode =
            app(PaymentModeService::class)
                ->current();

        return view(
            'settings.gateways',
            compact(
                'gateways',
                'paymentMode'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin only: Sandbox / Live switch
    |--------------------------------------------------------------------------
    */

    public function updatePaymentMode(
        Request $request,
        PaymentModeService $paymentModeService
    ) {
        abort_unless(
            auth()->user()->isAdmin(),
            403
        );

        $data = $request->validate([
            'payment_mode' => [
                'required',
                'in:sandbox,live',
            ],
        ]);

        $paymentModeService->set(
            $data['payment_mode']
        );

        return back()->with(
            'success',
            'Payment mode changed to ' .
            strtoupper($data['payment_mode']) .
            '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin only: individual gateway
    |--------------------------------------------------------------------------
    */

    public function storeGateway(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug',
        ]);

        PaymentGateway::create([
            'name' => $data['name'],
            'slug' => \Illuminate\Support\Str::slug($data['slug']),
            'status' => false,
            'environment' => 'sandbox',
            'credentials' => [],
        ]);

        return back()->with('success', 'Payment gateway added successfully.');
    }

    public function updateGateway(
        Request $request,
        PaymentGateway $gateway
    ) {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => 'nullable|boolean',
            'environment' => 'required|in:sandbox,production',
            'credentials' => 'nullable|array',
            'credentials.*' => 'nullable|string',
        ]);

        $credentials = $gateway->credentials ?? [];
        if (!empty($data['credentials'])) {
            foreach ($data['credentials'] as $key => $val) {
                if (!empty($val)) {
                    $credentials[$key] = $val;
                }
            }
        }

        $gateway->update([
            'status' => $request->boolean('status'),
            'environment' => $data['environment'],
            'credentials' => $credentials,
        ]);

        return back()->with('success', 'Gateway settings updated.');
    }

    public function destroyGateway(PaymentGateway $gateway)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $gateway->delete();
        return back()->with('success', 'Gateway removed successfully.');
    }
}