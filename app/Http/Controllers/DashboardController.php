<?php

namespace App\Http\Controllers;

use App\Models\{
    Business,
    User,
    VirtualCard,
    Payment,
    Transaction
};

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Agent scope
        |--------------------------------------------------------------------------
        */

        $cardQuery = VirtualCard::query();

        $paymentQuery = Payment::query();

        $transactionQuery = Transaction::query();

        $businessQuery = Business::query();

        if ($user->isAgent()) {

            $cardQuery->where(
                'agent_id',
                $user->id
            );

            $paymentQuery->where(
                'user_id',
                $user->id
            );

            $transactionQuery->where(
                'user_id',
                $user->id
            );

            $businessQuery->whereKey(
                $user->business_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalBusinesses =
            $businessQuery->count();

        $activeBusinesses =
            (clone $businessQuery)
            ->where('status', 'active')
            ->count();

        $totalUsers =
            $user->isAgent()
            ? 1
            : User::count();

        $activeCards =
            (clone $cardQuery)
            ->where('status', 'active')
            ->count();

        $totalCardLimit =
            (clone $cardQuery)->sum('card_limit');

        $totalSpent =
            (clone $transactionQuery)
            ->where('transaction_type', 'payment')
            ->where('status', 'successful')
            ->sum('amount');

        $currentUsage =
            (clone $cardQuery)->sum('current_usage');

        $remainingLimit =
            $totalCardLimit - $currentUsage;

        $todayPayments =
            (clone $paymentQuery)
            ->whereDate('created_at', today())
            ->count();

        $successfulPayments =
            (clone $paymentQuery)
            ->where('status', 'successful')
            ->whereDate('created_at', today())
            ->count();

        $failedPayments =
            (clone $paymentQuery)
            ->where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();

        $pendingPayments =
            (clone $paymentQuery)
            ->whereIn('status', [
                'created',
                'pending',
                'processing',
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent payments
        |--------------------------------------------------------------------------
        */

        $recentPayments =
            (clone $paymentQuery)
            ->with([
                'business',
                'card',
                'user',
            ])
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'businesses' => $totalBusinesses,
            'active_businesses' => $activeBusinesses,
            'users' => $totalUsers,
            'active_cards' => $activeCards,
            'card_limit' => $totalCardLimit,
            'spent' => $totalSpent,
            'remaining' => $remainingLimit,
            'today_payments' => $todayPayments,
            'successful_payments' => $successfulPayments,
            'failed_payments' => $failedPayments,
            'pending_payments' => $pendingPayments,
        ];

        return view(
            'dashboard.index',
            compact(
                'stats',
                'recentPayments'
            )
        );
    }
}
