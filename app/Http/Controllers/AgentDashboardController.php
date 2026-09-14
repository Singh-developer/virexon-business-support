<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Transaction;

class AgentDashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        $card = $user->virtualCard;

        $detail = $user->detail;

        // Total Approved = the amount sanctioned in the admin-approved loan letter.
        // Falls back to the agent's stored max_limit for agents approved
        // through the older flow (and 0 when nothing is approved yet).
        $approvedLetter = $user->sanctionLetters()
            ->where('review_status', 'approved')
            ->latest('reviewed_at')
            ->first();

        $approvedAmount = $approvedLetter
            ? (float) ($approvedLetter->getDynamicValue('approved_amount') ?? 0)
            : 0;

        $totalApproved = $approvedAmount > 0
            ? $approvedAmount
            : (float) ($detail->max_limit ?? 0);

        $currentUsage = (float) ($card->current_usage ?? 0);

        // Remaining Limit stays consistent with the approved amount,
        // not with the card's static limit.
        $remainingLimit = max(0, $totalApproved - $currentUsage);

        $totalTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'successful')
            ->count();

        $totalSpent = Transaction::where('user_id', $user->id)
            ->where('transaction_type', TransactionType::PAYMENT)
            ->where('status', 'successful')
            ->sum('amount');

        $totalFees = Transaction::where('user_id', $user->id)
            ->where('status', 'successful')
            ->sum('fee');

        $monthlySpent = Transaction::where('user_id', $user->id)
            ->where('transaction_type', TransactionType::PAYMENT)
            ->where('status', 'successful')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $monthlyFees = Transaction::where('user_id', $user->id)
            ->where('status', 'successful')
            ->whereMonth('created_at', now()->month)
            ->sum('fee');

        $repaymentPercentage = $totalApproved > 0
            ? round(($currentUsage / $totalApproved) * 100, 1)
            : 0;

        $documents = $user->documents->keyBy('document_type');

        return view('dashboard.dashboard', compact(
            'card',
            'detail',
            'totalApproved',
            'currentUsage',
            'remainingLimit',
            'totalTransactions',
            'totalSpent',
            'totalFees',
            'monthlySpent',
            'monthlyFees',
            'repaymentPercentage',
            'documents'
        ));
    }
}
