<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Validation\ValidationException;

class PaymentModeService
{
    public const SANDBOX = 'sandbox';

    public const LIVE = 'live';

    public function current(): string
    {
        return SystemSetting::getValue(
            'payment_mode',
            self::SANDBOX
        );
    }

    public function isSandbox(): bool
    {
        return $this->current() === self::SANDBOX;
    }

    public function isLive(): bool
    {
        return $this->current() === self::LIVE;
    }

    public function set(string $mode): void
    {
        if (! in_array($mode, [
            self::SANDBOX,
            self::LIVE,
        ], true)) {
            throw ValidationException::withMessages([
                'payment_mode' => 'Invalid payment mode.',
            ]);
        }

        SystemSetting::setValue(
            'payment_mode',
            $mode
        );
    }
}