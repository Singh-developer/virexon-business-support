@extends('layouts.app')@section('content')<div class="page-head">
    <div>
        <div class="eyebrow">OVERVIEW</div>
        <h1>Good day, {{ Str::before(auth()->user()->name,' ') }}!</h1>
        <p>Here’s what’s happening across your support and payment operations.</p>
    </div>
    <div class="actions">@if(auth()->user()->isAdmin())<a class="btn secondary" href="{{ route('businesses.create') }}">+ New Business</a>@endif<a class="btn primary" href="{{ route('payments.create') }}">+ New Payment</a></div>
</div>
<div class="hero-banner">
    <div><span class="pill light">Business Support</span>
        <h2>Advance For Your Growth</h2>
        <p>Manage clients, cards and payment workflows from one secure workspace.</p>
    </div>
    <div class="hero-art">↗</div>
</div>
<div class="stats-grid">@foreach([['Total Businesses',$stats['businesses'],'blue'],['Active Businesses',$stats['active_businesses'],'green'],['Total Users / Agents',$stats['users'],'indigo'],['Active Virtual Cards',$stats['active_cards'],'orange'],['Total Card Limit','₹'.number_format($stats['card_limit'],2),'cyan'],['Total Amount Spent','₹'.number_format($stats['spent'],2),'violet'],['Available Limit','₹'.number_format($stats['remaining'],2),'teal'],['Today’s Payments',$stats['today_payments'],'pink']] as $s)<div class="stat-card">
        <div class="stat-icon {{ $s[2] }}">◈</div>
        <div>
            <div class="stat-label">{{ $s[0] }}</div>
            <div class="stat-value">{{ $s[1] }}</div>
        </div>
    </div>@endforeach</div>
<div class="section-grid">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Payment Overview</h3>
                <p>Daily activity snapshot</p>
            </div><span class="badge success">Live</span>
        </div>
        <div class="mini-chart">@for($i=0;$i<18;$i++)<span style="height:{{ 28+($i%7)*9 }}%"></span>@endfor</div>
        <div class="chart-legend"><span>● ₹1.6L volume</span><span>● 92% successful</span></div>
    </div>
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Payment Status</h3>
                <p>Today</p>
            </div>
        </div>
        <div class="status-summary">
            <div><strong>{{ $stats['successful_payments'] }}</strong><span>Successful</span></div>
            <div><strong>{{ $stats['pending_payments'] }}</strong><span>Pending</span></div>
            <div><strong>{{ $stats['failed_payments'] }}</strong><span>Failed</span></div>
        </div>
        <div class="progress"><span style="width:78%"></span></div><small>78% successful processing rate</small>
    </div>
</div>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Recent Payments</h3>
            <p>Latest gateway activity</p>
        </div><a href="{{ route('payments.index') }}">View all →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Business</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>@forelse($recentPayments as $p)<tr>
                    <td class="mono">{{ $p->reference }}</td>
                    <td>{{ $p->business->name }}</td>
                    <td>₹{{ number_format($p->amount,2) }}</td>
                    <td><span class="badge neutral">{{ strtoupper($p->gateway) }}</span></td>
                    <td><span class="badge {{ $p->status->value }}">{{ ucfirst($p->status->value) }}</span></td>
                    <td>{{ $p->created_at->diffForHumans() }}</td>
                </tr>@empty<tr>
                    <td colspan="6" class="empty">No payments yet.</td>
                </tr>@endforelse</tbody>
        </table>
    </div>
</div>@endsection