<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\{Business, Payment, VirtualCard};
use App\Services\{PaymentService, PaymentGatewayManager};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController
{
    /* public function index(Request $r)
    {
        $u = $r->user();
        $payments = Payment::with(['business', 'user', 'card'])->when($u->isAgent(), fn($q) => $q->where('user_id', $u->id))->when($r->filled('status'), fn($q) => $q->where('status', $r->status))->when($r->filled('gateway'), fn($q) => $q->where('gateway', $r->gateway))->when($r->filled('q'), fn($q) => $q->where(function ($x) use ($r) {
            $x->where('reference', 'like', '%' . $r->q . '%')->orWhere('gateway_payment_id', 'like', '%' . $r->q . '%');
        }))->latest()->paginate(15)->withQueryString();
        return view('payments.index', compact('payments'));
    } */
    public function index(Request $request)
    {
        $user = $request->user();

        $payments = Payment::with([
            'business',
            'user',
            'card',
        ])
            ->when(
                $user->isAgent(),
                fn($query) => $query->where(
                    'user_id',
                    $user->id
                )
            )
            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where(
                    'status',
                    $request->status
                )
            )
            ->when(
                $request->filled('gateway'),
                fn($query) =>
                $query->where(
                    'gateway',
                    $request->gateway
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
                                'gateway_payment_id',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'payments.index',
            compact('payments')
        );
    }
    public function create(Request $r)
    {
        $u = $r->user();
        if ($u->isAgent()) {
            abort_unless($u->virtualCard, 403, 'Agent does not have a virtual card assigned.');
            $businesses = $u->business ? collect([$u->business]) : collect();
        } else {
            $businesses = Business::with('cards')->where('status', 'active')->get();
        }
        return view('payments.form', ['businesses' => $businesses, 'agentCard' => $u->isAgent() ? $u->virtualCard : null]);
    }
    public function store(PaymentRequest $r, PaymentService $service)
    {
        $d = $r->validated();
        $p = $service->create($r->user()->id, (int)$d['business_id'], (int)$d['card_id'], (float)$d['amount'], $d['gateway']);
        return redirect()->route('payments.show', $p)->with('success', $p->gateway === 'mock' ? 'Mock payment created. Use the sandbox action to complete it.' : 'Payment initiated. Complete gateway checkout using configured credentials.');
    }
    /* public function show(Request $r, Payment $payment)
    {
        if ($r->user()->isAgent() && $payment->user_id !== $r->user()->id) abort(403);
        $payment->load(['business', 'user', 'card', 'transaction']);
        return view('payments.show', compact('payment'));
    } */
    public function show(
        Request $request,
        Payment $payment
    ) {
        $user = $request->user();

        if (
            $user->isAgent() &&
            $payment->user_id !== $user->id
        ) {
            abort(403);
        }

        $payment->load([
            'business',
            'user',
            'card',
            'transaction',
        ]);

        return view(
            'payments.show',
            compact('payment')
        );
    }
    public function mockComplete(Request $r, Payment $payment, PaymentService $service)
    {
        abort_unless($payment->gateway === 'mock', 404);
        if ($r->user()->isAgent() && $payment->user_id !== $r->user()->id) abort(403);
        $service->markSuccessful($payment, 'mock_' . Str::lower(Str::random(12)), ['mode' => 'demo']);
        return back()->with('success', 'Mock payment marked successful.');
    }
    public function paytmCallback(Request $r, PaymentService $service)
    {
        $params = $r->all();
        $ref = $params['ORDERID'] ?? null;
        if (!$ref) return response('Missing order ID', 400);
        $payment = Payment::where('reference', $ref)->firstOrFail();
        $result = app(PaymentGatewayManager::class)->driver('paytm')->verifyPayment(['params' => $params, 'expected_amount' => $payment->amount]);
        if ($result['status'] === 'successful') $service->markSuccessful($payment, $result['payment_id'], $result['raw']);
        elseif ($result['status'] === 'failed') $service->markFailed($payment, $result['raw']);
        return redirect()->route('payments.show', $payment);
    }
}
