<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    //
    public function store(Request $request, User $agent)
    {
        $request->validate([
            'gross_amount' => 'required|numeric|min:0.01',
            'description'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $agent) {
            $gross = $request->gross_amount;
            $deduction = 0;

            // Check for active advance
            $advance = $agent->advances()->where('status', 'active')->first();
            if ($advance) {
                if ($advance->repayment_type === 'unselected') {
                    // Force the agent to select repayment before admin can pay commission? 
                    // Or don't deduct? Let's just don't deduct, or better, prevent commission payment until they select.
                    // For now, let's just bypass deduction if unselected.
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
                'description' => $request->description ?? 'Commission Payout',
            ]);
        });

        return back()->with('success', 'Commission added successfully.');
    }
}
