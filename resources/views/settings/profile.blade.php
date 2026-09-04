@extends('layouts.app')

@section('content')

<style>
    /* Guarantee input text is always visible on this page */
    .profile-form input,
    .profile-form select,
    .profile-form textarea {
        color: #1e293b !important;
    }
    .profile-form input[readonly],
    .profile-form select[disabled],
    .profile-form input.locked {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        opacity: 0.7;
    }
    .section-divider {
        grid-column: 1 / -1;
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        margin-top: 0.5rem;
    }
    .section-divider h3 {
        font-size: 0.875rem;
        font-weight: 700;
        color: #475569;
        margin: 0 0 0.25rem;
    }
    .section-divider p {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ACCOUNT SETTINGS</div>
        <h1>My Profile</h1>
        <p>View and update your account information.</p>
    </div>
</div>

<div class="panel form-panel">

    @if($isAgent)
        <div class="mb-4 px-3 py-2 rounded-lg {{ $isApproved ? 'bg-green-50 border border-green-200' : 'bg-orange-50 border border-orange-200' }}" style="font-size:12px; font-weight:600;">
            @if($isApproved)
                <span style="color:#15803d;">✓ Application Approved — Some fields are locked and cannot be edited.</span>
            @else
                <span style="color:#c2410c;">⏳ Application Pending — You can view and edit all your details below.</span>
            @endif
        </div>

        {{-- TEMPORARY DEBUG - REMOVE LATER --}}
        {{--<div style="background:#fff3cd; border:1px solid #ffc107; padding:10px; border-radius:6px; font-size:11px; margin-bottom:12px; font-family:monospace;">
            <strong>DEBUG (remove later):</strong><br>
            isAgent: {{ $isAgent ? 'YES' : 'NO' }}<br>
            isApproved: {{ $isApproved ? 'YES' : 'NO' }}<br>
            detail is null: {{ is_null($detail) ? 'YES (NULL!)' : 'NO' }}<br>
            @if($detail)
                detail ID: {{ $detail->id }}<br>
                guardian_name: {{ $detail->guardian_name ?? 'null' }}<br>
                mobile: {{ $detail->mobile ?? 'null' }}<br>
                personal_email: {{ $detail->personal_email ?? 'null' }}<br>
                agent_id_number: {{ $detail->agent_id_number ?? 'null' }}<br>
            @endif
        </div>--}}
        {{-- END DEBUG --}}
    @endif

    <form method="POST" action="{{ route('settings.profile.update') }}" class="profile-form">
        @csrf
        @method('PUT')

        <div class="form-grid">

            {{-- ===================== BASIC ACCOUNT INFO ===================== --}}
            <div class="section-divider">
                <h3>Account Information</h3>
                <p>Basic login details for your account</p>
            </div>

            <label>
                Full Name
                <input type="text" name="name" value="{{ old('name', $user->name) }}" style="color:#1e293b" required>
            </label>

            <label>
                Login Email (Official)
                <input type="email" name="email" value="{{ old('email', $user->email) }}" style="color:#1e293b" required>
            </label>

            <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="color:#1e293b">
            </label>

            <label>
                Role
                <input type="text" value="{{ $user->role?->name }}" style="color:#1e293b" readonly>
            </label>

            @if($isAgent)

            {{-- ===================== AGENT PERSONAL DETAILS ===================== --}}
            <div class="section-divider">
                <h3>Personal Details</h3>
                <p>
                    @if($isApproved)
                        Locked after approval. Contact admin to make changes.
                    @else
                        Editable until admin approves your application.
                    @endif
                </p>
            </div>

            <label>
                Personal Email
                <input type="email" name="personal_email"
                    value="{{ old('personal_email', $detail?->personal_email) }}"
                    style="color:#1e293b"
                    placeholder="Enter personal email">
            </label>

            <label>
                Mobile Number
                <input type="text" name="mobile"
                    value="{{ old('mobile', $detail?->mobile) }}"
                    style="color:#1e293b"
                    placeholder="Enter mobile number">
            </label>

            <label>
                Guardian / Father Name
                <input type="text" name="guardian_name"
                    value="{{ old('guardian_name', $detail?->guardian_name ?? $detail?->father_name) }}"
                    style="color:#1e293b"
                    placeholder="Enter guardian/father name"
                    @if($isApproved) readonly class="locked" @endif>
            </label>

            <label>
                Agent ID Number
                <input type="text" name="agent_id_number"
                    value="{{ old('agent_id_number', $detail?->agent_id_number) }}"
                    style="color:#1e293b"
                    placeholder="Enter agent ID"
                    @if($isApproved) readonly class="locked" @endif>
            </label>

            <label>
                Date of Birth
                <input type="date" name="date_of_birth"
                    value="{{ old('date_of_birth', $detail?->date_of_birth) }}"
                    style="color:#1e293b"
                    @if($isApproved) readonly class="locked" @endif>
            </label>

            <label>
                Gender
                <select name="gender" style="color:#1e293b" @if($isApproved) disabled class="locked" @endif>
                    <option value="">Select Gender</option>
                    <option value="male"   @selected(old('gender', $detail?->gender) === 'male')>Male</option>
                    <option value="female" @selected(old('gender', $detail?->gender) === 'female')>Female</option>
                    <option value="other"  @selected(old('gender', $detail?->gender) === 'other')>Other</option>
                </select>
                @if($isApproved)
                    <input type="hidden" name="gender" value="{{ $detail?->gender }}">
                @endif
            </label>

            <label>
                PAN Number
                <input type="text" name="pan_number"
                    value="{{ old('pan_number', $detail?->pan_number) }}"
                    style="color:#1e293b"
                    placeholder="Enter PAN number"
                    @if($isApproved) readonly class="locked" @endif>
            </label>

            <label>
                Marital Status
                <select name="is_married" style="color:#1e293b" @if($isApproved) disabled class="locked" @endif>
                    <option value="0" @selected(old('is_married', $detail?->is_married) == '0')>Single</option>
                    <option value="1" @selected(old('is_married', $detail?->is_married) == '1')>Married</option>
                </select>
                @if($isApproved)
                    <input type="hidden" name="is_married" value="{{ $detail?->is_married }}">
                @endif
            </label>

            {{-- ===================== ADDRESS (always editable) ===================== --}}
            <div class="section-divider">
                <h3>Address Details</h3>
                <p>Always editable — keep your address up to date.</p>
            </div>

            <label class="wide">
                Current Address
                <input type="text" name="current_address"
                    value="{{ old('current_address', $detail?->current_address) }}"
                    style="color:#1e293b"
                    placeholder="House No., Building, Street">
            </label>

            <label class="wide">
                Address Line 2
                <input type="text" name="address_line_2"
                    value="{{ old('address_line_2', $detail?->address_line_2) }}"
                    style="color:#1e293b"
                    placeholder="Area / Landmark (optional)">
            </label>

            <label>
                City
                <input type="text" name="current_city"
                    value="{{ old('current_city', $detail?->current_city) }}"
                    style="color:#1e293b"
                    placeholder="City / District">
            </label>

            <label>
                State
                <input type="text" name="current_state"
                    value="{{ old('current_state', $detail?->current_state) }}"
                    style="color:#1e293b"
                    placeholder="State">
            </label>

            <label>
                Pincode
                <input type="text" name="current_pincode"
                    value="{{ old('current_pincode', $detail?->current_pincode) }}"
                    style="color:#1e293b"
                    placeholder="Pin code">
            </label>

            {{-- ===================== BANK DETAILS (always editable) ===================== --}}
            <div class="section-divider">
                <h3>Bank Details</h3>
                <p>Always editable — used for reimbursements.</p>
            </div>

            <label>
                Account Holder Name
                <input type="text" name="account_name"
                    value="{{ old('account_name', $detail?->account_name) }}"
                    style="color:#1e293b"
                    placeholder="Enter account holder name">
            </label>

            <label>
                Bank Name
                <input type="text" name="bank_name"
                    value="{{ old('bank_name', $detail?->bank_name) }}"
                    style="color:#1e293b"
                    placeholder="Enter bank name">
            </label>

            <label>
                Account Number
                <input type="text" name="account_number"
                    value="{{ old('account_number', $detail?->account_number) }}"
                    style="color:#1e293b"
                    placeholder="Enter account number">
            </label>

            <label>
                IFSC / Routing Number
                <input type="text" name="routing_number"
                    value="{{ old('routing_number', $detail?->routing_number) }}"
                    style="color:#1e293b"
                    placeholder="Enter IFSC code">
            </label>

            <label>
                Account Type
                <input type="text" name="account_type"
                    value="{{ old('account_type', $detail?->account_type) }}"
                    style="color:#1e293b"
                    placeholder="Savings / Current">
            </label>

            <label>
                Branch Name
                <input type="text" name="branch_name"
                    value="{{ old('branch_name', $detail?->branch_name) }}"
                    style="color:#1e293b"
                    placeholder="Enter branch name">
            </label>

            @endif {{-- end @if($isAgent) --}}

        </div>

        <div class="form-actions">
            <button type="submit" class="btn primary">
                Save Changes
            </button>
        </div>

    </form>

</div>

@endsection