@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT MANAGEMENT</div>
        <h1>Payments</h1>
        <p>Gateway-aware payment operations with status, verification and auditability.</p>
    </div>
    <a class="btn primary" href="{{ route('payments.create') }}">+ New Payment</a>
</div>
<div class="panel">
    <form class="toolbar">
        <input name="q" value="{{ request('q') }}" placeholder="Payment reference or gateway ID">
        <select name="status">
            <option value="">All statuses</option>
            @foreach(['created','pending','processing','successful','failed','cancelled','refunded'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="gateway">
            <option value="">All gateways</option>
            <option value="mock">Mock</option>
            <option value="razorpay">Razorpay</option>
            <option value="paytm">Paytm</option>
        </select>
        <select name="payment_type">
            <option value="">All types</option>
            <option value="spending" @selected(request('payment_type') === 'spending')">Spending</option>
            <option value="repayment" @selected(request('payment_type') === 'repayment')">Repayment</option>
        </select>
        <button class="btn secondary">Filter</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Payment</th>
                    @if(auth()->user()->isAdmin())
                        <th>Agent</th>
                    @endif
                    <th>Business</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td><a class="link mono" href="{{ route('payments.show', $p) }}">{{ $p->reference }}</a></td>
                        @if(auth()->user()->isAdmin())
                            <td>{{ $p->user?->name ?? '—' }}</td>
                        @endif
                        <td>{{ $p->business->name }}</td>
                        <td>
                            @if($p->isRepayment())
                                <span class="badge" style="background: #E8F8F5; color: #27AE60;">Repayment</span>
                            @else
                                <span class="badge" style="background: #FEF5E7; color: #E65100;">Spending</span>
                            @endif
                        </td>
                        <td style="font-weight: 600; color: {{ $p->isRepayment() ? '#27AE60' : '#1565C0' }}">
                            @if($p->isRepayment())−@endif ₹{{ number_format($p->amount, 2) }}
                        </td>
                        <td><span class="badge neutral">{{ strtoupper($p->gateway) }}</span></td>
                        <td><span class="badge {{ $p->status->value }}">{{ ucfirst($p->status->value) }}</span></td>
                        <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="empty">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $payments->links() }}</div>
</div>
@endsection
