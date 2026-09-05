<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\User;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    //
    public function store(Request $request, User $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // Check if agent already has an active advance
        $activeAdvance = $agent->advances()->where('status', 'active')->first();
        if ($activeAdvance) {
            return back()->with('error', 'Agent already has an active advance. Please clear it first before issuing a new one.');
        }

        Advance::create([
            'user_id' => $agent->id,
            'total_amount' => $request->amount,
            'outstanding_amount' => $request->amount,
            'repayment_type' => 'unselected',
            'status' => 'active',
        ]);

        return back()->with('success', 'Advance issued successfully. Agent will be prompted to choose a repayment method.');
    }
}
