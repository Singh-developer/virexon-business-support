<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::with([
            'business',
            'card',
            'payment',
            'user',
        ])
            ->when(
                $user->isAgent(),
                fn($query) =>
                $query->where(
                    'user_id',
                    $user->id
                )
            )
            ->when(
                $request->filled('q'),
                function ($query) use ($request) {

                    $search = $request->q;

                    $query->where(function ($q) use ($search) {
                        $q->where(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'gateway_transaction_id',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $request->filled('type'),
                fn($query) =>
                $query->where(
                    'transaction_type',
                    $request->type
                )
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'transactions.index',
            compact('transactions')
        );
    }
}
