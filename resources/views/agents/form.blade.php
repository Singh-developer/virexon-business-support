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
    .settings-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .settings-card:last-child { margin-bottom: 0; }
    .settings-card-title { font-weight: 800; font-size: 12px; text-transform: uppercase; color: #475569; margin-bottom: 16px; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px; }
    .info-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-top: 16px; font-size: 12px; color: #1e40af; font-weight: 600; }
</style>

@php
    function agentDv($val) { return filled($val) ? e($val) : '<span class="empty">—</span>'; }
@endphp


{{-- ====================================================================
     SECTION 1: ACCOUNT EDIT FORM
==================================================================== --}}
<div class="panel">
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
     LIMIT & COMMISSION SETTINGS
==================================================================== --}}
@php
    $detail = $agent->detail;
@endphp
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Limit & Commission Settings</h3>
            <p>Set the maximum transaction limit and commission rate for this agent. These settings control how much the agent can transact and earn.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('agents.update-limit-commission', $agent) }}">
        @csrf
        @method('PATCH')
        
        {{-- Transaction Limit Section --}}
        <div class="settings-card">
            <div class="settings-card-title">
                📊 Transaction Limit
            </div>
            
            <div class="form-grid">
                <label>
                    Maximum Transaction Limit (₹)
                    <input type="number" step="0.01" name="max_limit" 
                           value="{{ old('max_limit', $detail->max_limit ?? 500000) }}" 
                           min="0" max="999999999999.99"
                           style="color:#1e293b"
                           placeholder="e.g. 500000 for 5 Lakh">
                    <small style="color: #64748b; font-weight: 400;">Default maximum is ₹5,00,000 (5 Lakh)</small>
                </label>
            </div>
        </div>

        {{-- Commission Settings Section --}}
        <div class="settings-card">
            <div class="settings-card-title">
                💵 Commission Settings
            </div>
            <div class="form-grid">
                <label>
                    Commission Type
                    <select name="commission_type" id="commission_type" style="color:#1e293b">
                        <option value="percentage" @selected(old('commission_type', $detail->commission_type ?? 'percentage') === 'percentage')>Percentage (%)</option>
                        <option value="fixed" @selected(old('commission_type', $detail->commission_type ?? 'percentage') === 'fixed')>Fixed Amount (₹)</option>
                    </select>
                </label>
                <label id="commission_rate_label">
                    Commission Rate (%)
                    <input type="number" step="0.0001" name="commission_rate" id="commission_rate" value="{{ old('commission_rate', $detail->commission_rate ?? 0) }}" min="0" max="100" style="color:#1e293b" placeholder="e.g. 2.5">
                </label>
                <label id="commission_fixed_label" style="display: none;">
                    Fixed Commission (₹)
                    <input type="number" step="0.01" name="commission_fixed" id="commission_fixed" value="{{ old('commission_fixed', $detail->commission_fixed ?? 0) }}" min="0" style="color:#1e293b" placeholder="e.g. 100">
                </label>
            </div>
            <div class="info-banner">
                <div style="font-weight: 700; margin-bottom: 6px;">Commission Preview (on ₹10,000 transaction)</div>
                @php
                    $sampleAmount = 10000;
                    $preview = 0;
                    if (($detail->commission_type ?? 'percentage') === 'fixed') {
                        $preview = $detail->commission_fixed ?? 0;
                    } else {
                        $preview = $sampleAmount * (($detail->commission_rate ?? 0) / 100);
                    }
                @endphp
                <div style="margin-top: 4px;">Estimated Commission: <strong style="color: #059669;">₹{{ number_format($preview, 2) }}</strong></div>
            </div>
        </div>

        <div class="form-actions">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="color: #64748b; font-size: 12px;">Changes apply immediately to the agent's account.</span>
                <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                    @if($agent->virtualCard)
                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 10px;">
                        🃏 Card: {{ $agent->virtualCard->last4 ?: $agent->virtualCard->reference }}
                    </span>
                    @endif
                    <select name="action" style="border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:600; color:#1e293b; background:#fff;">
                        <option value="save">Save Only</option>
                        @if(!$agent->virtualCard)
                        <option value="save_and_card">Save & Create Virtual Card</option>
                        @endif
                    </select>
                </div>
            </div>
            <button type="submit" class="btn primary">Save Limit & Commission Settings</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const commissionType = document.getElementById('commission_type');
    const rateLabel = document.getElementById('commission_rate_label');
    const fixedLabel = document.getElementById('commission_fixed_label');
    function toggleCommissionFields() {
        if (commissionType.value === 'fixed') {
            rateLabel.style.display = 'none';
            fixedLabel.style.display = 'block';
        } else {
            rateLabel.style.display = 'block';
            fixedLabel.style.display = 'none';
        }
    }
    commissionType.addEventListener('change', toggleCommissionFields);
    toggleCommissionFields();
});
</script>

{{-- ====================================================================
     SECTION 2: APPLICATION STATUS
==================================================================== --}}
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Application Status</h3>
            <p>Approve or reject this agent's application. Setting <strong>"Form Received"</strong> will prompt the agent to upload their documents.</p>
        </div>
        <span class="badge {{ $statusColor }}">{{ ucfirst($appStatus) }}</span>
    </div>

    <form method="POST" action="{{ route('agents.application-status', $agent) }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @csrf
        @method('PATCH')
        <select name="application_status" style="border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:13px; font-weight:600; min-width:200px; color:#1e293b; background:#fff;">
            <option value="pending"       @selected($appStatus === 'pending')\>⏳ Pending</option>
            <option value="form_received" @selected($appStatus === 'form_received')>📋 Form Received — Request Docs</option>
            <option value="approved"      @selected($appStatus === 'approved')>✅ Approved</option>
            <option value="rejected"      @selected($appStatus === 'rejected')>❌ Rejected</option>
        </select>
        <button type="submit" class="btn primary" onclick="return confirm('Update this agent\'s application status?')">
            Save Status
        </button>
        <a href="{{ route('admin.documents.index', $agent->id) }}" class="btn secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            📁 Review Documents
        </a>
    </form>
</div>

@if($appStatus === 'approved')
{{-- ====================================================================
     SECTION 2.5: ADVANCE & COMMISSION
==================================================================== --}}
@php
    $activeAdvance = $agent->advances()->where('status', 'active')->first();
    $totalCommission = $agent->commissions()->sum('net_amount');
    $totalDeductions = $agent->commissions()->sum('advance_deduction');
@endphp
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Advance & Commission</h3>
            <p>Issue an advance to the agent or process their commission payouts.</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Net Commissions Paid</div>
            <div style="font-size:16px;font-weight:700;color:#10b981;">₹{{ number_format($totalCommission, 2) }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:20px;border-top:1px solid #f1f5f9; padding-top: 20px;">
        
        {{-- Advance Box --}}
        <div class="settings-card" style="margin-bottom:0;">
            <div class="settings-card-title">Issue Advance</div>
            
            @if($activeAdvance)
                <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;margin-bottom:12px;">
                    <div style="font-size:12px;color:#1e40af;font-weight:700;">Active Advance</div>
                    <div style="font-size:18px;font-weight:800;color:#1e293b;margin:4px 0;">₹{{ number_format($activeAdvance->outstanding_amount, 2) }} <span style="font-size:12px;font-weight:600;color:#64748b;">outstanding</span></div>
                    <div style="font-size:12px;color:#64748b;">Original: ₹{{ number_format($activeAdvance->total_amount, 2) }}</div>
                    
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e2e8f0;font-size:12px;">
                        Repayment Method: 
                        @if($activeAdvance->repayment_type === 'unselected')
                            <strong style="color:#ef4444;">Not selected yet</strong>
                        @elseif($activeAdvance->repayment_type === 'one_time')
                            <strong>One-Time Deduction</strong>
                        @elseif($activeAdvance->repayment_type === 'emi')
                            <strong>EMI (₹{{ number_format($activeAdvance->emi_amount, 2) }} per payout)</strong>
                        @endif
                    </div>
                </div>
            @else
                <form action="{{ route('admin.advances.store', $agent->id) }}" method="POST" style="margin:0;">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Advance Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        <button type="submit" class="btn primary" style="width:100%;justify-content:center;">Issue Advance</button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Commission Box --}}
        <div class="settings-card" style="margin-bottom:0;">
            <div class="settings-card-title">Pay Commission</div>
            
            @if($activeAdvance && $activeAdvance->repayment_type === 'unselected')
                <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:12px;color:#991b1b;font-size:13px;">
                    <strong>Wait!</strong> The agent has an active advance but hasn't selected a repayment method. Commission cannot be processed until they choose how to repay it.
                </div>
            @else
                @php
                    $commType = $detail->commission_type ?? 'percentage';
                    $commRate = $detail->commission_rate ?? 0;
                    $commFixed = $detail->commission_fixed ?? 0;
                @endphp
                <div style="background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px;color:#065f46;">
                    <strong>Agent Commission:</strong>
                    @if($commType === 'fixed')
                        Fixed ₹{{ number_format($commFixed, 2) }} / transaction
                    @else
                        {{ number_format($commRate, 4) }}% of transaction
                    @endif
                </div>
                <form action="{{ route('admin.commissions.store', $agent->id) }}" method="POST" style="margin:0;">
                    @csrf
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Transaction Amount (₹)</label>
                            <input type="number" step="0.01" name="transaction_amount" placeholder="Enter transaction amount..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                            <small style="color:#64748b;font-size:10px;">Commission auto-calculated from agent's rate.</small>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Or Override Gross Commission (₹)</label>
                            <input type="number" step="0.01" name="gross_amount" placeholder="Override amount..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Description / Note</label>
                            <input type="text" name="description" placeholder="e.g. October Sales" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        @if($activeAdvance)
                        <div style="background:#eff6ff;color:#1e40af;font-size:11px;padding:8px;border-radius:6px;">
                            ℹ️ Advance deduction ({{ $activeAdvance->repayment_type === 'emi' ? 'EMI' : 'Full' }}) will be auto-calculated and subtracted.
                        </div>
                        @endif
                        <button type="submit" class="btn primary" style="width:100%;justify-content:center;background:#10b981;border-color:#10b981;">Process Payout</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endif


{{-- ====================================================================
     SECTION 3: FULL APPLICATION DETAILS (Editable by Admin)
==================================================================== --}}
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Agent Application Details</h3>
            <p>Information submitted by the agent during registration. Editable by admin.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flash failed" style="margin:0 20px 16px;">
        <strong>Please fix the following errors:</strong>
        <ul style="margin:8px 0 0 18px;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $dobVal = old('date_of_birth', optional($detail)->date_of_birth ? \Carbon\Carbon::parse(optional($detail)->date_of_birth)->format('Y-m-d') : '');
        $marriedVal = old('is_married', optional($detail)->is_married);
        // Normalise boolean-ish values for the select.
        $marriedSelected = $marriedVal === '' || $marriedVal === null ? '' : (int) (bool) $marriedVal;
    @endphp

    <form method="POST" action="{{ route('agents.update-details', $agent) }}">
        @csrf
        @method('PATCH')

        {{-- Personal Information --}}
        <div class="agent-section-head" style="border-radius:8px 8px 0 0; border:1px solid #e2e8f0; border-bottom:none;">Personal Information</div>
        <div style="border:1px solid #e2e8f0; border-bottom:none; padding:16px;">
            <div class="form-grid">
                <label>Personal Email
                    <input name="personal_email" type="email" value="{{ old('personal_email', optional($detail)->personal_email) }}" style="color:#1e293b">
                </label>
                <label>Mobile
                    <input name="mobile" value="{{ old('mobile', optional($detail)->mobile) }}" style="color:#1e293b">
                </label>
                <label>WhatsApp Number
                    <input name="whatsapp_number" value="{{ old('whatsapp_number', optional($detail)->whatsapp_number) }}" style="color:#1e293b">
                </label>
                <label>Guardian / Father Name
                    <input name="guardian_name" value="{{ old('guardian_name', optional($detail)->guardian_name ?? optional($detail)->father_name) }}" style="color:#1e293b">
                </label>
                <label>Father Name
                    <input name="father_name" value="{{ old('father_name', optional($detail)->father_name) }}" style="color:#1e293b">
                </label>
                <label>Mother Name
                    <input name="mother_name" value="{{ old('mother_name', optional($detail)->mother_name) }}" style="color:#1e293b">
                </label>
                <label>Agent ID Number
                    <input name="agent_id_number" value="{{ old('agent_id_number', optional($detail)->agent_id_number) }}" style="color:#1e293b">
                </label>
                <label>Date of Birth
                    <input name="date_of_birth" type="date" value="{{ $dobVal }}" style="color:#1e293b">
                </label>
                <label>Gender
                    <select name="gender" style="color:#1e293b">
                        <option value="">— Select —</option>
                        <option value="male" @selected(old('gender', optional($detail)->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', optional($detail)->gender) === 'female')>Female</option>
                        <option value="other" @selected(old('gender', optional($detail)->gender) === 'other')>Other</option>
                    </select>
                </label>
                <label>PAN Number
                    <input name="pan_number" value="{{ old('pan_number', optional($detail)->pan_number) }}" style="color:#1e293b; text-transform:uppercase;">
                </label>
                <label>Aadhar Number
                    <input name="aadhar_number" value="{{ old('aadhar_number', optional($detail)->aadhar_number) }}" style="color:#1e293b">
                </label>
                <label>Marital Status
                    <select name="is_married" id="is_married" style="color:#1e293b">
                        <option value="">— Select —</option>
                        <option value="0" @selected((string) old('is_married', $marriedSelected) === '0')>Single</option>
                        <option value="1" @selected((string) old('is_married', $marriedSelected) === '1')>Married</option>
                    </select>
                </label>
                <label>Spouse Name
                    <input name="spouse_name" value="{{ old('spouse_name', optional($detail)->spouse_name) }}" style="color:#1e293b">
                </label>
                <label>Spouse Mobile
                    <input name="spouse_mobile" value="{{ old('spouse_mobile', optional($detail)->spouse_mobile) }}" style="color:#1e293b">
                </label>
            </div>
        </div>

        {{-- Address --}}
        <div class="agent-section-head" style="border:1px solid #e2e8f0; border-bottom:none; border-top:none;">Address</div>
        <div style="border:1px solid #e2e8f0; border-bottom:none; padding:16px;">
            <div class="form-grid">
                <label style="grid-column:1/-1;">Current Address
                    <input name="current_address" value="{{ old('current_address', optional($detail)->current_address) }}" style="color:#1e293b">
                </label>
                <label>Address Line 2
                    <input name="address_line_2" value="{{ old('address_line_2', optional($detail)->address_line_2) }}" style="color:#1e293b">
                </label>
                <label>Current City
                    <input name="current_city" value="{{ old('current_city', optional($detail)->current_city) }}" style="color:#1e293b">
                </label>
                <label>Current State
                    <input name="current_state" value="{{ old('current_state', optional($detail)->current_state) }}" style="color:#1e293b">
                </label>
                <label>Current Pincode
                    <input name="current_pincode" value="{{ old('current_pincode', optional($detail)->current_pincode) }}" style="color:#1e293b">
                </label>
                <label style="grid-column:1/-1;">Permanent Address
                    <input name="permanent_address" value="{{ old('permanent_address', optional($detail)->permanent_address) }}" style="color:#1e293b">
                </label>
                <label>Permanent City
                    <input name="permanent_city" value="{{ old('permanent_city', optional($detail)->permanent_city) }}" style="color:#1e293b">
                </label>
                <label>Permanent State
                    <input name="permanent_state" value="{{ old('permanent_state', optional($detail)->permanent_state) }}" style="color:#1e293b">
                </label>
                <label>Permanent Pincode
                    <input name="permanent_pincode" value="{{ old('permanent_pincode', optional($detail)->permanent_pincode) }}" style="color:#1e293b">
                </label>
            </div>
        </div>

        {{-- Bank Details --}}
        <div class="agent-section-head" style="border:1px solid #e2e8f0; border-bottom:none; border-top:none;">Bank Details</div>
        <div style="border:1px solid #e2e8f0; border-bottom:none; padding:16px;">
            <div class="form-grid">
                <label>Account Holder
                    <input name="account_name" value="{{ old('account_name', optional($detail)->account_name) }}" style="color:#1e293b">
                </label>
                <label>Bank Name
                    <input name="bank_name" value="{{ old('bank_name', optional($detail)->bank_name) }}" style="color:#1e293b">
                </label>
                <label>Account Number
                    <input name="account_number" value="{{ old('account_number', optional($detail)->account_number) }}" style="color:#1e293b">
                </label>
                <label>IFSC / Routing No.
                    <input name="routing_number" value="{{ old('routing_number', optional($detail)->routing_number) }}" style="color:#1e293b">
                </label>
                <label>Account Type
                    <input name="account_type" value="{{ old('account_type', optional($detail)->account_type) }}" placeholder="Savings / Current" style="color:#1e293b">
                </label>
                <label>Branch
                    <input name="branch_name" value="{{ old('branch_name', optional($detail)->branch_name) }}" style="color:#1e293b">
                </label>
            </div>
        </div>

        {{-- Advance --}}
        <div class="agent-section-head" style="border:1px solid #e2e8f0; border-bottom:none; border-top:none;">Advance Details</div>
        <div style="border:1px solid #e2e8f0; border-bottom:none; padding:16px;">
            <div class="form-grid">
                <label>Fund Amount Requested (₹)
                    <input name="loan_amount" type="number" step="0.01" min="0" value="{{ old('loan_amount', optional($detail)->loan_amount) }}" style="color:#1e293b">
                </label>
                <label>Loan Tenure (months)
                    <input name="loan_tenure" type="number" min="1" max="360" value="{{ old('loan_tenure', optional($detail)->loan_tenure ?? 60) }}" style="color:#1e293b">
                </label>
                <label style="grid-column:1/-1;">Purpose of Advance
                    <input name="purpose_of_advance" value="{{ old('purpose_of_advance', optional($detail)->purpose_of_advance) }}" style="color:#1e293b">
                </label>
            </div>
        </div>

        {{-- References --}}
        <div class="agent-section-head" style="border:1px solid #e2e8f0; border-bottom:none; border-top:none;">References</div>
        <div style="border:1px solid #e2e8f0; border-radius:0 0 10px 10px; padding:16px;">
            <div id="references-wrap" style="display:grid; gap:0;">
                @forelse($refs as $i => $ref)
                <div class="reference-row" data-ref-row style="padding:16px 0; border-top:1px dashed #e2e8f0; @if($loop->first) border-top:none; padding-top:0; @endif">
                    <input type="hidden" name="references[{{ $i }}][id]" value="{{ $ref->id }}">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <div style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8;" data-ref-title>Reference #{{ $i + 1 }}</div>
                        <button type="button" class="btn secondary" onclick="this.closest('[data-ref-row]').remove(); renumberReferences();">Remove</button>
                    </div>
                    <div class="form-grid">
                        <label>Name
                            <input name="references[{{ $i }}][person_name]" value="{{ old("references.$i.person_name", $ref->person_name) }}" style="color:#1e293b">
                        </label>
                        <label>Mobile
                            <input name="references[{{ $i }}][mobile]" value="{{ old("references.$i.mobile", $ref->mobile) }}" style="color:#1e293b">
                        </label>
                        <label style="grid-column:1/-1;">Company Agent ID
                            <input name="references[{{ $i }}][company_agent_id]" value="{{ old("references.$i.company_agent_id", $ref->company_agent_id) }}" style="color:#1e293b">
                        </label>
                    </div>
                </div>
                @empty
                <div id="references-empty" style="color:#94a3b8; font-size:12px;">No references submitted yet. Add one below.</div>
                @endforelse
            </div>
            <div style="margin-top:10px;">
                <button type="button" class="btn secondary" onclick="addReferenceRow()">+ Add Reference</button>
            </div>
        </div>

        <div class="form-actions">
            <span style="color:#64748b; font-size:12px;">Saved to the agent's application record.</span>
            <button class="btn primary">Save Agent Details</button>
        </div>
    </form>
</div>

<script>
let refIndex = {{ max(1, $refs->count()) }};
function renumberReferences() {
    const rows = document.querySelectorAll('#references-wrap [data-ref-row]');
    rows.forEach(function(row, idx) {
        const title = row.querySelector('[data-ref-title]');
        if (title) title.textContent = 'Reference #' + (idx + 1);
        if (idx === 0) { row.style.borderTop = 'none'; row.style.paddingTop = '0'; }
        else { row.style.borderTop = '1px dashed #e2e8f0'; row.style.paddingTop = '16px'; }
    });
}
function addReferenceRow() {
    const wrap = document.getElementById('references-wrap');
    const empty = document.getElementById('references-empty');
    if (empty) empty.remove();
    const hasRows = wrap.querySelector('[data-ref-row]') !== null;
    const div = document.createElement('div');
    div.setAttribute('data-ref-row', '');
    div.style.cssText = 'padding:16px 0;' + (hasRows ? 'border-top:1px dashed #e2e8f0;' : 'border-top:none;padding-top:0;');
    div.innerHTML =
        '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">' +
            '<div data-ref-title style="font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8;">Reference #' + (wrap.querySelectorAll('[data-ref-row]').length + 1) + '</div>' +
            '<button type="button" class="btn secondary" onclick="this.closest(\'[data-ref-row]\').remove(); renumberReferences();">Remove</button>' +
        '</div>' +
        '<div class="form-grid">' +
            '<label>Name<input name="references[' + refIndex + '][person_name]" style="color:#1e293b"></label>' +
            '<label>Mobile<input name="references[' + refIndex + '][mobile]" style="color:#1e293b"></label>' +
            '<label style="grid-column:1/-1;">Company Agent ID<input name="references[' + refIndex + '][company_agent_id]" style="color:#1e293b"></label>' +
        '</div>';
    wrap.appendChild(div);
    refIndex++;
}
</script>

@endif {{-- end @if($agent->exists) --}}

@endsection