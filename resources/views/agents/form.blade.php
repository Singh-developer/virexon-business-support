@extends('layouts.app')

@section('content')

@php
    $detail    = $agent->detail;
    $refs      = $agent->referencePersons ?? collect();
    $appStatus = optional($detail)->application_status ?? 'pending';

    $statusColor = match($appStatus) {
        'approved' => 'success',
        'rejected' => 'failed',
        default    => 'pending',
    };
@endphp

<div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1>{{ $agent->exists ? $agent->name : 'Create Agent' }}</h1>
        <p>{{ $agent->exists ? 'Manage agent account, application status, and view submitted details.' : 'Create a new agent account. Virtual card is created separately.' }}</p>
    </div>
    @if($agent->exists)
    <span class="badge {{ $statusColor }}">{{ ucfirst($appStatus) }}</span>
    @endif
</div>

<style>
    .agent-detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0; border: 1px solid #f1f5f9; border-radius: 0 0 10px 10px; overflow: hidden; }
    .agent-detail-grid .detail-item { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
    .agent-detail-grid .detail-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }
    .detail-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
    .detail-value { font-size: 13px; font-weight: 600; color: #1e293b; }
    .detail-value.empty { color: #cbd5e1; font-style: italic; font-weight: 400; }
    .agent-section-head { grid-column: 1 / -1; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
    @media(max-width:600px){ .agent-detail-grid { grid-template-columns: 1fr; } .agent-detail-grid .detail-item { border-right: none !important; } }
</style>

@php
    function agentDv($val) { return filled($val) ? e($val) : '<span class="empty">—</span>'; }
@endphp


{{-- ====================================================================
     SECTION 1: ACCOUNT EDIT FORM
==================================================================== --}}
<div class="panel form-panel">
    <div class="panel-head">
        <div>
            <h3>Account Details</h3>
            <p>Login credentials and account assignment.</p>
        </div>
    </div>

    <form method="POST" action="{{ $agent->exists ? route('agents.update', $agent) : route('agents.store') }}">
        @csrf
        @if($agent->exists) @method('PUT') @endif

        <div class="form-grid">

            <label>
                Full Name
                <input name="name" value="{{ old('name', $agent->name) }}" style="color:#1e293b" required>
            </label>

            <label>
                Official Email
                <input name="email" type="email" value="{{ old('email', $agent->email) }}" style="color:#1e293b" required>
            </label>

            <label>
                Phone
                <input name="phone" value="{{ old('phone', $agent->phone) }}" style="color:#1e293b">
            </label>

            <label>
                Business
                <select name="business_id" style="color:#1e293b" required>
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}" @selected(old('business_id', $agent->business_id) == $business->id)>
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Login Status
                <select name="status" style="color:#1e293b">
                    <option value="active"   @selected(old('status', $agent->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $agent->status) === 'inactive')>Inactive</option>
                </select>
            </label>

            <label>
                Password @if($agent->exists)<small>Leave blank to keep current password.</small>@endif
                <div style="position: relative;">
                    <input name="password" id="agent-password" type="password" {{ $agent->exists ? '' : 'required' }} autocomplete="new-password" style="color:#1e293b; width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePassword('agent-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.2rem; padding: 0;">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </label>

            @if(!$agent->exists)
            <label>
                Password Confirmation
                <div style="position: relative;">
                    <input name="password_confirmation" id="agent-password-confirm" type="password" required autocomplete="new-password" style="color:#1e293b; width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePassword('agent-password-confirm', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.2rem; padding: 0;">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </label>
            @endif
            
            <script>
                function togglePassword(inputId, btn) {
                    const input = document.getElementById(inputId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.querySelector('.eye-icon').innerText = '🙈';
                    } else {
                        input.type = 'password';
                        btn.querySelector('.eye-icon').innerText = '👁️';
                    }
                }
            </script>

        </div>

        <div class="notice">
            One-agent-one-card rule: an Agent account is assigned exactly one virtual card. The database enforces this with a unique constraint on <code>virtual_cards.agent_id</code>.
        </div>

        <div class="form-actions">
            <a class="btn secondary" href="{{ route('agents.index') }}">Cancel</a>
            <button class="btn primary">{{ $agent->exists ? 'Update Agent' : 'Create Agent' }}</button>
        </div>
    </form>
</div>


@if($agent->exists)

{{-- ====================================================================
     SECTION 2: APPLICATION STATUS
==================================================================== --}}
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Application Status</h3>
            <p>Approve or reject this agent's application. Approved agents have some profile fields locked.</p>
        </div>
        <span class="badge {{ $statusColor }}">{{ ucfirst($appStatus) }}</span>
    </div>

    <form method="POST" action="{{ route('agents.application-status', $agent) }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @csrf
        <select name="application_status" style="border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:13px; font-weight:600; min-width:180px; color:#1e293b; background:#fff;">
            <option value="pending"  @selected($appStatus === 'pending')>⏳ Pending</option>
            <option value="approved" @selected($appStatus === 'approved')>✅ Approved</option>
            <option value="rejected" @selected($appStatus === 'rejected')>❌ Rejected</option>
        </select>
        <button type="submit" class="btn primary" onclick="return confirm('Update this agent\'s application status?')">
            Save Status
        </button>
    </form>
</div>


{{-- ====================================================================
     SECTION 3: FULL APPLICATION DETAILS (Read-only)
==================================================================== --}}
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Agent Application Details</h3>
            <p>Information submitted by the agent during registration. Read-only.</p>
        </div>
    </div>

    <div class="agent-detail-grid">

        {{-- Personal Info --}}
        <div class="agent-section-head">Personal Information</div>
        <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-value">{!! agentDv($agent->name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Official Email</div><div class="detail-value">{!! agentDv($agent->email) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Personal Email</div><div class="detail-value">{!! agentDv(optional($detail)->personal_email) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Mobile</div><div class="detail-value">{!! agentDv(optional($detail)->mobile) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Guardian / Father Name</div><div class="detail-value">{!! agentDv(optional($detail)->guardian_name ?? optional($detail)->father_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Agent ID Number</div><div class="detail-value">{!! agentDv(optional($detail)->agent_id_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Date of Birth</div><div class="detail-value">{!! agentDv(optional($detail)->date_of_birth) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Gender</div><div class="detail-value">{!! agentDv(optional($detail)->gender ? ucfirst(optional($detail)->gender) : null) !!}</div></div>
        <div class="detail-item"><div class="detail-label">PAN Number</div><div class="detail-value">{!! agentDv(optional($detail)->pan_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Marital Status</div><div class="detail-value">{!! agentDv(optional($detail)->is_married === 1 ? 'Married' : (optional($detail)->is_married === 0 ? 'Single' : null)) !!}</div></div>

        {{-- Address --}}
        <div class="agent-section-head">Address</div>
        <div class="detail-item" style="grid-column:1/-1">
            <div class="detail-label">Current Address</div>
            <div class="detail-value">
                {!! agentDv(optional($detail)->current_address) !!}
                @if(optional($detail)->address_line_2), {{ optional($detail)->address_line_2 }}@endif
            </div>
        </div>
        <div class="detail-item"><div class="detail-label">City</div><div class="detail-value">{!! agentDv(optional($detail)->current_city) !!}</div></div>
        <div class="detail-item"><div class="detail-label">State</div><div class="detail-value">{!! agentDv(optional($detail)->current_state) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Pincode</div><div class="detail-value">{!! agentDv(optional($detail)->current_pincode) !!}</div></div>

        {{-- Bank Details --}}
        <div class="agent-section-head">Bank Details</div>
        <div class="detail-item"><div class="detail-label">Account Holder</div><div class="detail-value">{!! agentDv(optional($detail)->account_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Bank Name</div><div class="detail-value">{!! agentDv(optional($detail)->bank_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Account Number</div><div class="detail-value">{!! agentDv(optional($detail)->account_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">IFSC / Routing No.</div><div class="detail-value">{!! agentDv(optional($detail)->routing_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Account Type</div><div class="detail-value">{!! agentDv(optional($detail)->account_type) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Branch</div><div class="detail-value">{!! agentDv(optional($detail)->branch_name) !!}</div></div>

        {{-- Advance --}}
        <div class="agent-section-head">Advance Details</div>
        <div class="detail-item">
            <div class="detail-label">Loan Amount Requested</div>
            <div class="detail-value">
                {{ optional($detail)->loan_amount ? '₹' . number_format(optional($detail)->loan_amount, 2) : '—' }}
            </div>
        </div>
        <div class="detail-item"><div class="detail-label">Purpose of Advance</div><div class="detail-value">{!! agentDv(optional($detail)->purpose_of_advance) !!}</div></div>

    </div>

    {{-- References --}}
    @if($refs->count())
    <div style="border-top:1px solid #f1f5f9; margin-top:0;">
        <div style="background:#f8fafc; padding:10px 16px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#64748b;">
            References ({{ $refs->count() }})
        </div>
        <div style="padding:14px 16px; display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:12px;">
            @foreach($refs as $i => $ref)
            <div style="border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                <div style="font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:6px;">Reference {{ $i + 1 }}</div>
                <div style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:4px;">{{ $ref->person_name }}</div>
                <div style="font-size:12px; color:#64748b;">📞 {{ $ref->mobile }}</div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px;">ID: {{ $ref->company_agent_id }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div style="padding:14px 16px; color:#94a3b8; font-size:12px; border-top:1px solid #f1f5f9;">
        No references submitted yet.
    </div>
    @endif
</div>

@endif {{-- end @if($agent->exists) --}}

@endsection