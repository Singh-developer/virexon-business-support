<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    //
    public function index()
    {
        $user = auth()->user();
        $activeAdvance = $user->advances()->where('status', 'active')->first();
        $commissions = $user->commissions()->orderBy('created_at', 'desc')->get();
        $totalCommission = $commissions->sum('net_amount');
        $totalDeductions = $commissions->sum('advance_deduction');

        // Load agent settings (max_limit, commission)
        $user->loadMissing('detail');
        $detail = $user->detail;
        $maxLimit = $detail->max_limit ?? 500000;
        $commissionType = $detail->commission_type ?? 'percentage';
        $commissionRate = $detail->commission_rate ?? 0;
        $commissionFixed = $detail->commission_fixed ?? 0;

        return view('advances.index', compact(
            'activeAdvance',
            'commissions',
            'totalCommission',
            'totalDeductions',
            'maxLimit',
            'commissionType',
            'commissionRate',
            'commissionFixed'
        ));
    }

    public function updateRepaymentMethod(Request $request)
    {
        $user = auth()->user();
        $activeAdvance = $user->advances()->where('status', 'active')->first();

        if (!$activeAdvance) {
            return back()->with('error', 'You have no active advance.');
        }

        if ($activeAdvance->repayment_type !== 'unselected') {
            return back()->with('error', 'Repayment method is already selected.');
        }

        $request->validate([
            'repayment_type' => 'required|in:one_time,emi',
            'emi_amount'     => 'required_if:repayment_type,emi|numeric|min:1',
        ]);

        $activeAdvance->update([
            'repayment_type' => $request->repayment_type,
            'emi_amount'     => $request->repayment_type === 'emi' ? $request->emi_amount : null,
        ]);

        return back()->with('success', 'Repayment method selected successfully.');
    }
}
