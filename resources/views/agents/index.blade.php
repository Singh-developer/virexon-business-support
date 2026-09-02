@extends('layouts.app')@section('content')<div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1>Agents</h1>
        <p>Admins can create, update, activate/deactivate, and manage every agent. Each agent can have a maximum of one virtual card. The card is created separately by an authorized Admin.</p>
    </div><a class="btn primary" href="{{ route('agents.create') }}">+ New Agent</a>
</div>
<div class="panel">
    <form class="toolbar"><input name="q" value="{{ request('q') }}" placeholder="Search agent name, email or phone..."><button class="btn secondary">Search</button></form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Business</th>
                    <th>Virtual Card</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>@forelse($agents as $agent)<tr>
                    <td><a class="link" href="{{ route('agents.show',$agent) }}"><strong>{{ $agent->name }}</strong></a><small>{{ $agent->email }} · {{ $agent->phone ?: 'No phone' }}</small></td>
                    <td>{{ $agent->business?->name ?: '—' }}</td>
                    <td>@if($agent->virtualCard)<a class="link" href="{{ route('cards.show',$agent->virtualCard) }}">{{ $agent->virtualCard->reference }}</a><small>{{ ucfirst($agent->virtualCard->status->value) }}</small>@else<span class="badge failed">Missing</span>@endif</td>
                    <td><span class="badge {{ $agent->status==='active'?'success':'neutral' }}">{{ ucfirst($agent->status) }}</span></td>
                    <td>{{ $agent->created_at->format('d M Y') }}</td>
                    <td class="flex items-center gap-2">
                        <a class="btn tiny" href="{{ route('agents.edit',$agent) }}">Manage</a>
                        <form method="POST" action="{{ route('agents.toggle-status', $agent) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn tiny {{ $agent->status === 'active' ? 'secondary' : 'primary' }}" onclick="return confirm('Are you sure you want to {{ $agent->status === 'active' ? 'disable' : 'enable' }} login for this agent?');">
                                {{ $agent->status === 'active' ? 'Disable Login' : 'Enable Login' }}
                            </button>
                        </form>
                    </td>
                </tr>@empty<tr>
                    <td colspan="6" class="empty">No agents found.</td>
                </tr>@endforelse</tbody>
        </table>
    </div>
    <div class="pagination">{{ $agents->links() }}</div>
</div>@endsection