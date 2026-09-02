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

    public function updateGateway(
        Request $request,
        PaymentGateway $gateway
    ) {
        abort_unless(
            auth()->user()->isAdmin(),
            403
        );

        $data = $request->validate([
            'status' => [
                'nullable',
                'boolean',
            ],

            'environment' => [
                'required',
                'in:test,staging,production',
            ],
        ]);

        $gateway->update([
            'status' =>
                $request->boolean('status'),

            'environment' =>
                $data['environment'],
        ]);

        return back()->with(
            'success',
            'Gateway settings updated.'
        );
    }
}