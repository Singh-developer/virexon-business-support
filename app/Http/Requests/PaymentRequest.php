<?php

namespace App\Http\Requests;

use App\Models\Business;
use App\Models\PaymentGateway;
use App\Models\VirtualCard;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $activeGatewaySlugs = PaymentGateway::where('status', true)->pluck('slug')->toArray();

        return [
            'business_id' => [
                'required',
                'exists:businesses,id',
            ],

            'card_id' => [
                'required',
                'exists:virtual_cards,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'gateway' => [
                'required',
                'in:mock,' . implode(',', $activeGatewaySlugs),
            ],

            'payment_type' => [
                'nullable',
                'in:spending,repayment',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {

                $user = $this->user();

                $business = Business::find(
                    $this->business_id
                );

                $card = VirtualCard::find(
                    $this->card_id
                );

                if (! $user) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Agent business restriction
                |--------------------------------------------------------------------------
                */

                if (
                    $user->isAgent() &&
                    (
                        ! $business ||
                        $business->id !== $user->business_id
                    )
                ) {
                    $validator->errors()->add(
                        'business_id',
                        'You can only use your assigned business.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Agent card restriction
                |--------------------------------------------------------------------------
                */

                if (
                    $user->isAgent() &&
                    (
                        ! $card ||
                        $card->agent_id !== $user->id
                    )
                ) {
                    $validator->errors()->add(
                        'card_id',
                        'You can only use your own virtual card.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Card/business consistency
                |--------------------------------------------------------------------------
                */

                if (
                    $card &&
                    $business &&
                    $card->business_id !== $business->id
                ) {
                    $validator->errors()->add(
                        'card_id',
                        'Selected card does not belong to the selected business.'
                    );
                }
            },
        ];
    }
}
