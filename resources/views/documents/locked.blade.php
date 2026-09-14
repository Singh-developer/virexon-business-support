@extends('layouts.app')

@section('content')
<div class="doc-locked-wrap">
    <style>
        .doc-locked-wrap {
            background: #ffffff;
            margin: -32px -16px -16px -16px;
            padding: 0 0 96px 0;
            min-height: calc(100vh - 60px);
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        @media (min-width: 1024px) {
            .doc-locked-wrap {
                margin: -32px auto -24px auto;
                max-width: 430px;
                border-left: 1px solid #eef1f5;
                border-right: 1px solid #eef1f5;
                min-height: calc(100vh - 60px);
                box-shadow: 0 0 24px rgba(15,35,60,0.06);
            }
        }
        .doc-head { padding: 20px 16px 0 16px; }
        .doc-title {
            font-size: 24px;
            line-height: 28px;
            font-weight: 800;
            color: #17233c;
            letter-spacing: -0.2px;
            margin: 0;
        }
        .doc-sub {
            font-size: 14px;
            line-height: 20px;
            color: #6b7a90;
            margin: 6px 0 0 0;
            font-weight: 400;
        }
        .doc-tabs {
            display: flex;
            align-items: stretch;
            gap: 24px;
            margin-top: 16px;
            padding: 0 16px;
            border-bottom: 1px solid #edf0f4;
            overflow-x: auto;
            scrollbar-width: none;
            white-space: nowrap;
        }
        .doc-tabs::-webkit-scrollbar { display: none; }
        .doc-tab {
            appearance: none;
            background: transparent;
            border: 0;
            padding: 10px 1px 11px 1px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7a90;
            cursor: pointer;
            position: relative;
            line-height: 20px;
            flex-shrink: 0;
        }
        .doc-tab.active { color: #1f6bff; font-weight: 700; }
        .doc-tab.active::after {
            content: "";
            position: absolute;
            left: -2px; right: -2px; bottom: -1px;
            height: 3px;
            background: #1f6bff;
            border-radius: 3px 3px 0 0;
        }
        .doc-list {
            padding: 14px 12px 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            list-style: none;
            margin: 0;
        }
        .doc-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 12px;
            padding: 12px 10px 12px 12px;
            width: 100%;
            text-align: left;
            min-height: 72px;
        }
        li.doc-card { list-style: none; }
        .doc-ico {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .doc-ico svg { width: 26px; height: 26px; }
        .doc-ico i { font-size: 20px; line-height: 1; }
        .doc-main { flex: 1; min-width: 0; }
        .doc-name {
            font-size: 15px; line-height: 20px;
            font-weight: 700; color: #1a2744;
            letter-spacing: -0.1px;
        }
        .doc-desc {
            font-size: 13px; line-height: 18px;
            color: #6b7a90; margin-top: 3px; font-weight: 400;
        }
        .doc-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .doc-pill {
            font-size: 13px; line-height: 18px; font-weight: 700;
            border-radius: 999px; padding: 5px 12px; white-space: nowrap;
        }
        .doc-pill.green { color: #1a9e50; background: #e9f7ee; }
        .doc-pill.yellow { color: #a66a00; background: #fff4da; }
        .doc-pill.red { color: #c53e2e; background: #ffebe9; }
        .doc-pill.slate { color: #5c6f84; background: #eef2f7; }
        .doc-pill.blue { color: #1f6bff; background: #e8f0fe; }
        .doc-chev { color: #5b6b82; display: flex; align-items: center; padding-right: 2px; }
        .doc-chev svg { width: 20px; height: 20px; }
        .doc-section-label {
            font-size: 12px; font-weight: 800; letter-spacing: 0.6px;
            color: #17233c; text-transform: uppercase;
            padding: 14px 16px 0 16px; margin: 0;
        }
        .doc-support {
            text-align: center;
            padding: 10px 16px 0 16px;
            font-size: 11px;
            color: #9aa7ba;
        }
        .doc-support a { color: #1f6bff; font-weight: 600; text-decoration: none; }
        .doc-footer-actions {
            margin: 12px 12px 0 12px;
            background: #f8fafc;
            border: 1px solid #eef1f5;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .doc-footer-actions p {
            font-size: 12px; color: #6b7a90; margin: 0;
            display: flex; align-items: center; gap: 6px;
        }
        .doc-footer-btns { display: flex; gap: 8px; }
        .doc-btn {
            flex: 1;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 12px; font-weight: 700;
            padding: 9px 12px; border-radius: 9px; text-decoration: none;
            border: 1px solid transparent;
        }
        .doc-btn.dark { background: #17233c; color: #fff; }
        .doc-btn.light { background: #fff; color: #5c6f84; border-color: #e2e8f0; }
        .doc-bottomnav {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 45;
            background: #fff; border-top: 1px solid #e9edf2;
            display: flex; align-items: stretch; justify-content: space-around;
            padding: 8px 4px calc(8px + env(safe-area-inset-bottom)) 4px;
        }
        @media (min-width: 1024px) { .doc-bottomnav { left: 288px; } }
        .doc-bnav-item {
            flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;
            text-decoration: none; padding: 2px 0; min-width: 0;
        }
        .doc-bnav-item svg { width: 24px; height: 24px; }
        .doc-bnav-item span { font-size: 11px; line-height: 14px; font-weight: 500; color: #6b7a90; }
        .doc-bnav-item.active span { color: #1f6bff; font-weight: 700; }
    </style>

    <!-- Header (UI only, same visual language as reference) -->
    <div class="doc-head">
        <h1 class="doc-title">Documents</h1>
        <p class="doc-sub">Upload and manage your documents securely</p>
    </div>

    <!-- Tabs (UI only) -->
    <div class="doc-tabs" role="tablist">
        <button type="button" class="doc-tab active" data-filter="all" role="tab">All Documents</button>
        <button type="button" class="doc-tab" data-filter="kyc" role="tab">KYC</button>
        <button type="button" class="doc-tab" data-filter="bank" role="tab">Bank</button>
        <button type="button" class="doc-tab" data-filter="agreement" role="tab">Agreement</button>
        <button type="button" class="doc-tab" data-filter="more" role="tab">More</button>
    </div>

    <!-- Current Status Banner — DYNAMIC (same $appStatus branches + texts as before, UI restyled as list card) -->
    <div class="doc-list" id="doc-status-list">
        @if($appStatus === 'pending')
        <div class="doc-card" data-cat="kyc">
            <span class="doc-ico" style="background:#fff4da;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
            </span>
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Application Under Review</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Your Fund Application has been submitted and is currently being reviewed by the admin. Document upload will be enabled once the admin confirms receipt of your form.</span>
            </span>
            <span class="doc-right">
                <span class="doc-pill yellow">Pending</span>
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </div>
        @elseif($appStatus === 'rejected')
        <div class="doc-card" data-cat="kyc">
            <span class="doc-ico" style="background:#ffebe9;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c53e2e" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M9.5 9.5l5 5M14.5 9.5l-5 5"/></svg>
            </span>
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Application Rejected</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Unfortunately, your Fund Application has been rejected. Please contact support if you believe this is a mistake or to understand the reason.</span>
            </span>
            <span class="doc-right">
                <span class="doc-pill red">Rejected</span>
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </div>
        @else
        <div class="doc-card" data-cat="kyc">
            <span class="doc-ico" style="background:#eef2f7;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#5c6f84" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 8v4M12 15h.01"/></svg>
            </span>
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Not Available Yet</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Document upload is not yet enabled for your account. Please wait for the admin to process your application.</span>
            </span>
            <span class="doc-right">
                <span class="doc-pill slate">Locked</span>
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </div>
        @endif
    </div>

    <!-- Application Stage Tracker — DYNAMIC (same 4 steps + same $appStatus conditions, UI restyled as reference cards) -->
    <p class="doc-section-label">Your Application Progress</p>

    <ol class="doc-list" id="doc-list">

        <!-- Step 1 -->
        <li class="doc-card" data-cat="kyc">
            <span class="doc-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </span>
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Fund Application Submitted ✓</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Your application form has been successfully submitted.</span>
            </span>
            <span class="doc-right">
                <span class="doc-pill green">Verified</span>
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </li>

        <!-- Step 2 -->
        <li class="doc-card" data-cat="bank">
            @if(in_array($appStatus, ['form_received', 'approved']))
            <span class="doc-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </span>
            @else
            <span class="doc-ico" style="background:#e8f0fe;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#2b7fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
            </span>
            @endif
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Admin Reviews &amp; Confirms Form</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Admin verifies your application and marks it as received.</span>
            </span>
            <span class="doc-right">
                @if(!in_array($appStatus, ['form_received', 'approved']))
                <span class="doc-pill yellow">Current Step</span>
                @else
                <span class="doc-pill green">Verified</span>
                @endif
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </li>

        <!-- Step 3 — Current Goal -->
        <li class="doc-card" data-cat="agreement">
            @if(in_array($appStatus, ['form_received', 'approved']))
            <span class="doc-ico" style="background:#f1eafe;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
            </span>
            @else
            <span class="doc-ico" style="background:#f1f3f5;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/></svg>
            </span>
            @endif
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Upload Your Documents</span>
                <span class="doc-desc" style="display:block; white-space:normal;">PAN Card, Aadhaar, Photograph, Bank Proof, Agent ID, Address Proof</span>
            </span>
            <span class="doc-right">
                @if(in_array($appStatus, ['form_received', 'approved']))
                <span class="doc-pill blue">Next</span>
                @else
                <span class="doc-pill slate">Locked</span>
                @endif
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </li>

        <!-- Step 4 -->
        <li class="doc-card" data-cat="more">
            @if($appStatus === 'approved')
            <span class="doc-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </span>
            @else
            <span class="doc-ico" style="background:#f1f3f5;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7.5 3v5.5c0 4.5-3.2 7.6-7.5 9-4.3-1.4-7.5-4.5-7.5-9V6z"/></svg>
            </span>
            @endif
            <span class="doc-main">
                <span class="doc-name" style="display:block;">Disbursement Process</span>
                <span class="doc-desc" style="display:block; white-space:normal;">Admin reviews your documents and gives disbursement.</span>
            </span>
            <span class="doc-right">
                @if($appStatus === 'approved')
                <span class="doc-pill green">Completed</span>
                @else
                <span class="doc-pill slate">Locked</span>
                @endif
                <span class="doc-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </li>

    </ol>

    <!-- Footer Actions — DYNAMIC (same routes + help text as before, UI restyled) -->
    <div class="doc-footer-actions">
        <p>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a9e50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7.5 3v5.5c0 4.5-3.2 7.6-7.5 9-4.3-1.4-7.5-4.5-7.5-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
            Need help? Contact our support team.
        </p>
        <div class="doc-footer-btns">
            <a href="{{ route('tickets.create') }}" class="doc-btn dark">Open Support Ticket</a>
            <a href="{{ route('dashboard') }}" class="doc-btn light">← Dashboard</a>
        </div>
    </div>

    <div class="doc-support">
        Need help? <a href="{{ route('tickets.create') }}">Contact support</a> · <a href="{{ route('dashboard') }}">Dashboard</a>
    </div>

    <!-- Bottom tab bar (UI parity with reference) -->
    <!-- <nav class="doc-bottomnav" aria-label="Primary">
        <a class="doc-bnav-item" href="{{ route('dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11l8-7 8 7"/><path d="M6 9.5V20h12V9.5"/><path d="M10 20v-5h4v5"/></svg>
            <span>Dashboard</span>
        </a>
        <a class="doc-bnav-item" href="{{ route('advances.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><circle cx="11" cy="14" r="2.2"/><path d="M11 12.8v2.4M10 13.4h2"/></svg>
            <span>Advance</span>
        </a>
        <a class="doc-bnav-item active" href="{{ route('documents.index') }}" aria-current="page">
            <svg viewBox="0 0 24 24" fill="#1f6bff"><rect x="5" y="3" width="14" height="18" rx="2"/><rect x="8.5" y="7.5" width="7" height="1.8" rx="0.9" fill="#fff"/><rect x="8.5" y="11" width="7" height="1.8" rx="0.9" fill="#fff"/><rect x="8.5" y="14.5" width="4.5" height="1.8" rx="0.9" fill="#fff"/></svg>
            <span>Documents</span>
        </a>
        <a class="doc-bnav-item" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><path d="M9 13h6M9 16.5h6"/></svg>
            <span>Compliance</span>
        </a>
        <a class="doc-bnav-item" href="{{ route('settings.profile') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.5 3.9-5.2 7-5.2s5.8 1.7 7 5.2"/></svg>
            <span>Profile</span>
        </a>
    </nav> -->

    <script>
        (function() {
            var tabs = document.querySelectorAll('.doc-locked-wrap .doc-tab');
            var cards = document.querySelectorAll('.doc-locked-wrap #doc-list .doc-card');
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    tabs.forEach(function(t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                    var f = tab.getAttribute('data-filter');
                    cards.forEach(function(c) {
                        if (f === 'all' || c.getAttribute('data-cat') === f) {
                            c.style.display = '';
                        } else {
                            c.style.display = 'none';
                        }
                    });
                });
            });
        })();
    </script>
</div>
@endsection
