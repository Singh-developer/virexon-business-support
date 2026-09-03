@extends('layouts.app')@section('content')<div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1>{{-- $agent->exists ? 'Edit Agent' : 'Create Agent' --}}</h1>
        <h1>{{ $agent->exists ? 'Update Agent' : 'Create Agent' }}</h1>
        <p>{{ $agent->exists ? 'Admins can update all agent details and the assigned business.' : 'Admin creates the Agent account first. A virtual card is created separately by an authorized Admin.' }}</p>
    </div>
</div>
<div class="panel form-panel">
    <form method="POST" action="{{ $agent->exists ? route('agents.update',$agent) : route('agents.store') }}">@csrf @if($agent->exists) @method('PUT') @endif<div class="form-grid"><label>Full Name<input name="name" value="{{ old('name',$agent->name) }}" required></label><label>Official Email<input name="email" type="email" value="{{ old('email',$agent->email) }}" required></label><label>Personal Email<input name="personal_email" type="email" value="{{ old('personal_email',$agent->detail->personal_email ?? '') }}"></label><label>Phone<input name="phone" value="{{ old('phone',$agent->phone) }}"></label><label>Business<select name="business_id" required>@foreach($businesses as $business)<option value="{{ $business->id }}" @selected(old('business_id',$agent->business_id)==$business->id)>{{ $business->name }}</option>@endforeach</select></label><label>Status<select name="status">
                    <option value="active" @selected(old('status',$agent->status ?: 'active')==='active')>Active</option>
                    <option value="inactive" @selected(old('status',$agent->status)==='inactive')>Inactive</option>
                </select></label><label>Password @if($agent->exists)<small>Leave blank to keep current password.</small>@endif<input name="password" type="password" {{ $agent->exists ? '' : 'required' }} autocomplete="new-password"></label><label>Password Confirmation<input name="password_confirmation" type="password" {{ $agent->exists ? '' : 'required' }} autocomplete="new-password"></label></div>
        <div class="notice">One-agent-one-card rule: an Agent account is assigned exactly one virtual card. The database enforces this with a unique constraint on <code>virtual_cards.agent_id</code>.</div>
        <div class="form-actions"><a class="btn secondary" href="{{ route('agents.index') }}">Cancel</a><button class="btn primary">{{ $agent->exists ? 'Update Agent' : 'Create Agent + Card' }}</button></div>
    </form>
</div>@endsection