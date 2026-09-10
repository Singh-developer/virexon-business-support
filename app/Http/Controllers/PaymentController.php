<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\{Business, Payment, PaymentGateway, VirtualCard};
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
                $request->filled('payment_type'),
                fn($query) =>
                $query->where(
                    'payment_type',
                    $request->payment_type
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
            $business = $u->business;
            if ($business) {
                $business->load('cards');
            }
            $businesses = $business ? collect([$business]) : collect();
        } else {
            $businesses = Business::with('cards')->where('status', 'active')->get();
        }

        $activeGateways = PaymentGateway::where('status', true)->get();

        return view('payments.form', [
            'businesses' => $businesses,
            'agentCard' => $u->isAgent() ? $u->virtualCard : null,
            'activeGateways' => $activeGateways,
        ]);
    }
    public function store(PaymentRequest $r, PaymentService $service)
    {
        $d = $r->validated();
        $paymentType = $d['payment_type'] ?? 'spending';
        $p = $service->create(
            $r->user()->id,
            (int) $d['business_id'],
            (int) $d['card_id'],
            (float) $d['amount'],
            $d['gateway'],
            $paymentType
        );

        if ($p->status->value === 'failed') {
            return redirect()->route('payments.show', $p)->with(
                'error',
                'Payment failed: ' . ($p->gateway_response['error'] ?? 'Gateway returned an error. Please try again.')
            );
        }

        if ($p->gateway === 'paytm') {
            $checkoutType = $p->gateway_response['checkout_type'] ?? null;

            if ($checkoutType === 'standard' && isset($p->gateway_response['checksum'])) {
                $environment = $p->gateway_response['environment'] ?? 'production';

                return view('payments.paytm-checkout', [
                    'environment' => $environment,
                    'params' => $p->gateway_response['params'],
                    'checksum' => $p->gateway_response['checksum'],
                ]);
            }

            if (isset($p->gateway_response['txn_token'])) {
                $gw = PaymentGateway::where('slug', 'paytm')->first();
                $credentials = $gw->credentials ?? [];
                $env = $gw->environment ?? 'sandbox';
                $isProduction = ($env === 'production');

                $checkoutUrl = $isProduction
                    ? 'https://secure.paytmpayments.com/theia/oneclick/'
                    : 'https://securestage.paytmpayments.com/theia/oneclick/';

                return view('payments.paytm-checkout', [
                    'checkoutUrl' => $checkoutUrl,
                    'environment' => $isProduction ? 'production' : 'staging',
                    'mid' => $credentials["{$env}_api_key"] ?? '',
                    'orderId' => $p->reference,
                    'txnToken' => $p->gateway_response['txn_token'],
                    'callbackUrl' => config('services.paytm.callback_url', url('/payments/paytm/callback')),
                    'website' => $credentials["{$env}_website"] ?? 'DEFAULT',
                ]);
            }
        }

        return redirect()->route('payments.show', $p)->with(
            'success',
            $p->gateway === 'mock'
                ? 'Payment created. Use the sandbox action to complete it.'
                : 'Payment initiated. Complete gateway checkout using configured credentials.'
        );
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
