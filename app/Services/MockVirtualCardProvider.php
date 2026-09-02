<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class MockVirtualCardProvider
{
    /**
     * Create a sandbox card.
     *
     * IMPORTANT:
     * CVV is returned to the caller but is NOT persisted.
     */
    public function createCard(string $cardholderName): array
    {
        $pan = $this->generatePan();

        $expiry = Carbon::now()
            ->addYears(3)
            ->endOfMonth();

        return [
            'provider_card_id' => 'mock_' . Str::lower(Str::random(24)),
            'pan' => $pan,
            'last4' => substr($pan, -4),
            'cvv' => $this->generateCvv(),
            'expiry_date' => $expiry,
            'cardholder_name' => strtoupper(trim($cardholderName)),
        ];
    }

    private function generateCvv(): string
    {
        return (string) random_int(100, 999);
    }

    /**
     * Generate a sandbox Visa-style 16-digit PAN
     * with a valid Luhn checksum.
     */
    private function generatePan(): string
    {
        $prefix = '400000';

        $body = '';

        for ($i = 0; $i < 9; $i++) {
            $body .= random_int(0, 9);
        }

        $partial = $prefix . $body;

        $checkDigit = $this->calculateLuhnCheckDigit($partial);

        return $partial . $checkDigit;
    }

    private function calculateLuhnCheckDigit(string $number): int
    {
        $sum = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$length - 1 - $i];

            if ($i % 2 === 0) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }
}