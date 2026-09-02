<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'business_id' => [
                'required',
                'exists:businesses,id',
            ],

            'agent_id' => [
                'required',
                'exists:users,id',
                Rule::unique('virtual_cards', 'agent_id')
                    ->ignore($this->route('card')?->id),
            ],

            'cardholder_name' => [
                'required',
                'string',
                'max:180',
            ],

            'card_limit' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'daily_limit' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'monthly_limit' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'per_transaction_limit' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'expiry_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'in:active,blocked,suspended,cancelled',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $data = $this->all();

                $agentId = $data['agent_id'] ?? null;

                if ($agentId) {
                    $agent = User::with('role')->find($agentId);

                    if (! $agent || ! $agent->isAgent()) {
                        $validator->errors()->add(
                            'agent_id',
                            'Only users with the Agent role can own a virtual card.'
                        );
                    }

                    if (
                        $agent &&
                        $agent->business_id !== (int) ($data['business_id'] ?? 0)
                    ) {
                        $validator->errors()->add(
                            'agent_id',
                            'Agent must belong to the selected business.'
                        );
                    }
                }

                if (
                    isset($data['daily_limit'], $data['monthly_limit']) &&
                    (float) $data['daily_limit'] > (float) $data['monthly_limit']
                ) {
                    $validator->errors()->add(
                        'daily_limit',
                        'Daily limit cannot exceed monthly limit.'
                    );
                }

                if (
                    isset($data['monthly_limit'], $data['card_limit']) &&
                    (float) $data['monthly_limit'] > (float) $data['card_limit']
                ) {
                    $validator->errors()->add(
                        'monthly_limit',
                        'Monthly limit cannot exceed overall card limit.'
                    );
                }

                if (
                    isset(
                        $data['per_transaction_limit'],
                        $data['daily_limit']
                    ) &&
                    (float) $data['per_transaction_limit'] >
                    (float) $data['daily_limit']
                ) {
                    $validator->errors()->add(
                        'per_transaction_limit',
                        'Per-transaction limit cannot exceed daily limit.'
                    );
                }
            },
        ];
    }
}