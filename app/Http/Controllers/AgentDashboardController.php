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

        $totalApproved = $detail->max_limit ?? 0;

        $currentUsage = $card->current_usage ?? 0;

        $remainingLimit = $card->remaining_limit ?? 0;

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

        $documents = $user->documents->keyBy('type');

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
