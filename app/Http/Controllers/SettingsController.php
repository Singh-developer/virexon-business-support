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
        $user = Auth::user();

        return view(
            'settings.profile',
            compact('user')
        );
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:180',
            ],

            'email' => [
                'required',
                'email',
                'max:180',
                'unique:users,email,' . $user->id,
            ],

            'phone' => [
                'nullable',
                'string',
                'max:40',
            ],
        ]);

        $user->update($data);

        return back()->with(
            'success',
            'Your profile has been updated.'
        );
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
            'environment' => 'test',
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
            'environment' => 'required|in:test,staging,production',
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
}