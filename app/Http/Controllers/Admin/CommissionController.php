<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Store a new commission for the agent.
     * If gross_amount is not provided, auto-calculate based on the agent's
     * configured commission settings (rate/fixed) and the transaction_amount.
     */
    public function store(Request $request, User $agent)
    {
        $request->validate([
            'gross_amount' => 'nullable|numeric|min:0.01',
            'transaction_amount' => 'nullable|numeric|min:0.01',
            'description'  => 'nullable|string|max:255',
        ]);

        // Load agent details (may be null)
        $agent->loadMissing('detail');
        $detail = $agent->detail;

        $gross = 0;
        $description = $request->description ?? 'Commission Payout';

        // If gross amount not provided, calculate from agent's commission config
        if (!$request->filled('gross_amount') && $request->filled('transaction_amount')) {
            $transactionAmount = (float) $request->transaction_amount;
            $commissionRate = (float) ($detail->commission_rate ?? 0);
            $commissionFixed = (float) ($detail->commission_fixed ?? 0);
            $commissionType = $detail->commission_type ?? 'percentage';

            if ($commissionType === 'fixed') {
                $gross = $commissionFixed;
                $description = $description . ' (Rs.' . number_format($transactionAmount, 2) . ' @ Rs.' . number_format($commissionFixed, 2) . ' fixed)';
            } else {
                $gross = round($transactionAmount * ($commissionRate / 100), 2);
                $description = $description . ' (Rs.' . number_format($transactionAmount, 2) . ' @ ' . number_format($commissionRate, 4) . '%)';
            }
        } else {
            $gross = (float) $request->gross_amount;
        }

        if ($gross <= 0) {
            return back()->with('error', 'Invalid commission amount. Please provide a valid gross amount or transaction amount with commission rate configured.');
        }

        DB::transaction(function () use ($agent, $gross, $description) {
            $deduction = 0;

            // Check for active advance
            $advance = $agent->advances()->where('status', 'active')->first();
            if ($advance) {
                if ($advance->repayment_type === 'unselected') {
                    // Don't deduct if unselected.
                } elseif ($advance->repayment_type === 'one_time') {
                    // Deduct up to the gross amount
                    $deduction = min($gross, $advance->outstanding_amount);
                } elseif ($advance->repayment_type === 'emi') {
                    // Deduct EMI amount, capped by gross amount and outstanding amount
                    $deduction = min($advance->emi_amount, $gross, $advance->outstanding_amount);
                }

                if ($deduction > 0) {
                    $advance->outstanding_amount -= $deduction;
                    if ($advance->outstanding_amount <= 0) {
                        $advance->status = 'completed';
                    }
                    $advance->save();
                }
            }

            Commission::create([
                'user_id' => $agent->id,
                'gross_amount' => $gross,
                'advance_deduction' => $deduction,
                'net_amount' => $gross - $deduction,
                'description' => $description,
            ]);
        });

        return back()->with('success', 'Commission added successfully.');
    }
}
