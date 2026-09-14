<?php $__env->startSection('content'); ?>
<?php
    $cfg = $docConfig ?? \App\Models\AgentDocument::types();
    $statusOf = function ($type) use ($documents) {
        return $documents[$type]->status ?? 'not_uploaded';
    };
    $fileOf = function ($type) use ($documents) {
        $d = $documents[$type] ?? null;
        return $d && $d->file_path ? \Illuminate\Support\Facades\Storage::url($d->file_path) : '';
    };
    $noteOf = function ($type) use ($documents) {
        $d = $documents[$type] ?? null;
        return ($d && $d->admin_note && in_array($d->status ?? '', ['rejected', 're_upload'])) ? $d->admin_note : '';
    };

    // KYC row combines PAN + Aadhaar + Photo
    $kycTypes = ['pan_card', 'aadhaar', 'photo'];
    $kycStatuses = [];
    foreach ($kycTypes as $t) { $kycStatuses[$t] = $statusOf($t); }
    if (in_array('rejected', $kycStatuses) || in_array('re_upload', $kycStatuses)) {
        $kycStatus = in_array('rejected', $kycStatuses) ? 'rejected' : 're_upload';
    } elseif (in_array('pending', $kycStatuses)) {
        $kycStatus = 'pending';
    } elseif (count(array_unique($kycStatuses)) === 1 && $kycStatuses['pan_card'] === 'approved') {
        $kycStatus = 'approved';
    } else {
        $kycStatus = 'not_uploaded';
    }
    $kycUploadType = 'pan_card';
    foreach ($kycTypes as $t) {
        if (($statusOf($t)) !== 'approved') { $kycUploadType = $t; break; }
    }
    $kycFileUrl = '';
    foreach ($kycTypes as $t) {
        if ($fileOf($t) !== '') { $kycFileUrl = $fileOf($t); break; }
    }
    $kycNote = '';
    foreach ($kycTypes as $t) {
        if ($noteOf($t) !== '') { $kycNote = $noteOf($t); break; }
    }

    $panStatus = $statusOf('pan_card');
    $bankStatus = $statusOf('bank_proof');
    $agreeStatus = $statusOf('agent_id_proof');
    $underStatus = $statusOf('address_proof');

    $isApproved = ($appStatus ?? '') === 'approved';
    $purposeStatus = $isApproved ? 'approved' : 'pending';

    $rowBucket = function ($st) {
        if ($st === 'approved') return 'completed';
        if (in_array($st, ['rejected', 're_upload'])) return 'rejected';
        return 'pending';
    };
    $buckets = [
        $rowBucket($kycStatus),
        $rowBucket($panStatus),
        $rowBucket($bankStatus),
        $rowBucket($agreeStatus),
        $rowBucket($underStatus),
        $rowBucket($purposeStatus),
    ];
    $totalRows = 6;
    $compCount = count(array_filter($buckets, fn($b) => $b === 'completed'));
    $pendCount = count(array_filter($buckets, fn($b) => $b === 'pending'));
    $rejCount = count(array_filter($buckets, fn($b) => $b === 'rejected'));
    $pct = $totalRows > 0 ? round($compCount / $totalRows * 100) : 0;
    $circ = 2 * 3.14159265 * 52;
    $dash = round($circ * $compCount / max(1, $totalRows), 1);
    $isCompliant = ($compCount === $totalRows);

    $pill = function ($st, $posWord) {
        if ($st === 'approved') return ['cls' => 'green', 'txt' => $posWord];
        if ($st === 'rejected') return ['cls' => 'red', 'txt' => 'Rejected'];
        if ($st === 're_upload') return ['cls' => 'amber', 'txt' => 'Re-upload'];
        if ($st === 'pending') return ['cls' => 'amber', 'txt' => 'Pending'];
        return ['cls' => 'slate', 'txt' => 'Pending'];
    };
    $kycPill = $pill($kycStatus, 'Verified');
    $panPill = $pill($panStatus, 'Verified');
    $bankPill = $pill($bankStatus, 'Verified');
    $agreePill = $pill($agreeStatus, 'Signed');
    $underPill = $pill($underStatus, 'Submitted');
    $purposePill = $isApproved ? ['cls' => 'green', 'txt' => 'Approved'] : ['cls' => 'amber', 'txt' => 'Pending'];
?>
<div class="comp-wrap">
    <style>
        .comp-wrap {
            background: #ffffff;
            margin: -32px -16px -16px -16px;
            padding: 0 0 96px 0;
            min-height: calc(100vh - 60px);
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        @media (min-width: 1024px) {
            .comp-wrap {
                margin: -32px auto -24px auto;
                max-width: 430px;
                border-left: 1px solid #eef1f5;
                border-right: 1px solid #eef1f5;
                min-height: calc(100vh - 60px);
                box-shadow: 0 0 24px rgba(15,35,60,0.06);
            }
        }
        .comp-head { padding: 20px 16px 0 16px; }
        .comp-title { font-size: 24px; line-height: 28px; font-weight: 800; color: #17233c; letter-spacing: -0.2px; margin: 0; }
        .comp-sub { font-size: 14px; line-height: 20px; color: #6b7a90; margin: 6px 0 0 0; font-weight: 400; }
        .comp-flash { margin: 12px 12px 0 12px; border-radius: 10px; padding: 10px 12px; font-size: 13px; line-height: 18px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .comp-flash.ok { background: #e9f7ee; border: 1px solid #bfe6cb; color: #14663a; }
        .comp-flash.err { background: #ffebe9; border: 1px solid #f3c1bb; color: #a02a1e; }
        .comp-banner { margin: 14px 12px 0 12px; border-radius: 12px; padding: 14px 12px; display: flex; gap: 12px; align-items: flex-start; }
        .comp-banner.green { background: #eef7f0; border: 1px solid #d9efdf; }
        .comp-banner.amber { background: #fff7e8; border: 1px solid #f0dcb0; }
        .comp-banner .b-ico { width: 44px; height: 44px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .comp-banner .b-ico svg { width: 40px; height: 40px; }
        .comp-banner .b-title { font-size: 16px; line-height: 22px; font-weight: 800; }
        .comp-banner.green .b-title { color: #1a9e50; }
        .comp-banner.amber .b-title { color: #b45309; }
        .comp-banner .b-desc { font-size: 13px; line-height: 18px; color: #5b6b82; margin-top: 2px; font-weight: 400; }
        .comp-label { font-size: 17px; line-height: 22px; font-weight: 800; color: #17233c; padding: 18px 16px 0 16px; margin: 0; letter-spacing: -0.1px; }
        .comp-list { padding: 12px 12px 0 12px; display: flex; flex-direction: column; gap: 10px; }
        .comp-card {
            display: flex; align-items: center; gap: 12px;
            background: #fff; border: 1px solid #eef1f5; border-radius: 12px;
            padding: 12px 10px 12px 12px; width: 100%; min-height: 72px;
            text-align: left; cursor: pointer; font: inherit; color: inherit; text-decoration: none;
        }
        button.comp-card { appearance: none; }
        .comp-ico { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .comp-ico svg { width: 26px; height: 26px; }
        .comp-main { flex: 1; min-width: 0; }
        .comp-name { font-size: 15px; line-height: 20px; font-weight: 700; color: #1a2744; letter-spacing: -0.1px; display: block; }
        .comp-desc { font-size: 13px; line-height: 18px; color: #6b7a90; margin-top: 3px; font-weight: 400; display: block; }
        .comp-note { font-size: 12px; line-height: 16px; color: #c53e2e; margin-top: 4px; display: block; }
        .comp-note strong { font-weight: 700; }
        .comp-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .comp-pill { font-size: 13px; line-height: 18px; font-weight: 700; border-radius: 999px; padding: 5px 12px; white-space: nowrap; }
        .comp-pill.green { color: #1a9e50; background: #e9f7ee; }
        .comp-pill.amber { color: #a66a00; background: #fff4da; }
        .comp-pill.red { color: #c53e2e; background: #ffebe9; }
        .comp-pill.slate { color: #5c6f84; background: #eef2f7; }
        .comp-chev { color: #5b6b82; display: flex; align-items: center; padding-right: 2px; }
        .comp-chev svg { width: 20px; height: 20px; }
        .comp-summary { margin: 14px 12px 0 12px; border: 1px solid #eef1f5; border-radius: 12px; padding: 16px 14px; background: #fff; }
        .comp-summary h3 { font-size: 17px; line-height: 22px; font-weight: 800; color: #17233c; margin: 0; letter-spacing: -0.1px; }
        .comp-sum-body { display: flex; align-items: center; gap: 20px; margin-top: 14px; }
        .comp-donut { position: relative; width: 120px; height: 120px; flex-shrink: 0; }
        .comp-donut svg { display: block; }
        .comp-donut-c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .comp-donut-n { font-size: 20px; line-height: 24px; font-weight: 800; color: #17233c; }
        .comp-donut-t { font-size: 12px; line-height: 16px; font-weight: 600; color: #3d4b60; margin-top: 2px; }
        .comp-legend { display: flex; flex-direction: column; gap: 12px; }
        .comp-leg { display: flex; align-items: center; gap: 10px; font-size: 14px; line-height: 20px; font-weight: 600; color: #3d4b60; }
        .comp-dot { width: 14px; height: 14px; border-radius: 999px; flex-shrink: 0; }
        .comp-support { text-align: center; padding: 12px 16px 0 16px; font-size: 11px; color: #9aa7ba; }
        .comp-support a { color: #1f6bff; font-weight: 600; text-decoration: none; }
        .comp-bottomnav { position: fixed; left: 0; right: 0; bottom: 0; z-index: 45; background: #fff; border-top: 1px solid #e9edf2; display: flex; align-items: stretch; justify-content: space-around; padding: 8px 4px calc(8px + env(safe-area-inset-bottom)) 4px; }
        @media (min-width: 1024px) { .comp-bottomnav { left: 288px; } }
        .comp-bnav { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; padding: 2px 0; min-width: 0; }
        .comp-bnav svg { width: 24px; height: 24px; }
        .comp-bnav span { font-size: 11px; line-height: 14px; font-weight: 500; color: #6b7a90; }
        .comp-bnav.active span { color: #1f6bff; font-weight: 700; }
        #upload-modal .modal-card { background: #fff; border-radius: 12px; box-shadow: 0 20px 50px rgba(10,25,50,.25); width: 100%; max-width: 28rem; padding: 24px; }
    </style>

    <div class="comp-head">
        <h1 class="comp-title">Compliance</h1>
        <p class="comp-sub">Track your compliance and verification status</p>
    </div>

    <?php if(session('success')): ?>
    <div class="comp-flash ok">&#10003;&nbsp;&nbsp;<?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="comp-flash err">&#10005;&nbsp;&nbsp;<?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($isCompliant): ?>
    <div class="comp-banner green">
        <span class="b-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#1a9e50" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7.5 3v5.5c0 4.5-3.2 7.6-7.5 9-4.3-1.4-7.5-4.5-7.5-9V6z"/><path d="M9 11.5l2.2 2.2L15.5 9.5"/></svg>
        </span>
        <span>
            <span class="b-title" style="display:block;">You are Compliant</span>
            <span class="b-desc" style="display:block;">All required documents and verification steps are complete.</span>
        </span>
    </div>
    <?php else: ?>
    <div class="comp-banner amber">
        <span class="b-ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
        </span>
        <span>
            <span class="b-title" style="display:block;">Action Required</span>
            <span class="b-desc" style="display:block;"><?php echo e($compCount); ?> of <?php echo e($totalRows); ?> steps complete. Upload pending documents to proceed.</span>
        </span>
    </div>
    <?php endif; ?>

    <h2 class="comp-label">Compliance Snapshot</h2>

    <div class="comp-list">
        <!-- KYC Documents (combined PAN + Aadhaar + Photo) -->
        <button type="button" class="comp-card" onclick="openUploadModal('<?php echo e($kycUploadType); ?>', '<?php echo e($cfg[$kycUploadType]['label']); ?>', '<?php echo e($cfg[$kycUploadType]['accept']); ?>', '<?php echo e($kycFileUrl); ?>')">
            <span class="comp-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 17c.6-1.8 1.7-2.7 3-2.7s2.4.9 3 2.7"/><path d="M14 9h4M14 12.5h4M14 16h2.5"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">KYC Documents</span>
                <span class="comp-desc">All KYC documents are verified</span>
                <?php if($kycNote !== ''): ?><span class="comp-note"><strong>Admin Note:</strong> <?php echo e($kycNote); ?></span><?php endif; ?>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($kycPill['cls']); ?>"><?php echo e($kycPill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </button>

        <!-- PAN Verification -->
        <button type="button" class="comp-card" onclick="openUploadModal('pan_card', '<?php echo e($cfg['pan_card']['label']); ?>', '<?php echo e($cfg['pan_card']['accept']); ?>', '<?php echo e($fileOf('pan_card')); ?>')">
            <span class="comp-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><rect x="6" y="8.5" width="5" height="3.5" rx="0.8"/><path d="M6 15h12"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">PAN Verification</span>
                <span class="comp-desc">PAN number is verified</span>
                <?php if($noteOf('pan_card') !== ''): ?><span class="comp-note"><strong>Admin Note:</strong> <?php echo e($noteOf('pan_card')); ?></span><?php endif; ?>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($panPill['cls']); ?>"><?php echo e($panPill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </button>

        <!-- Bank Details -->
        <button type="button" class="comp-card" onclick="openUploadModal('bank_proof', '<?php echo e($cfg['bank_proof']['label']); ?>', '<?php echo e($cfg['bank_proof']['accept']); ?>', '<?php echo e($fileOf('bank_proof')); ?>')">
            <span class="comp-ico" style="background:#e0f5f0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#14a085" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 4l9 5.5"/><path d="M4 9.5V19M20 9.5V19"/><path d="M2.5 19.5h19"/><path d="M8.5 12v7M12 12v7M15.5 12v7"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">Bank Details</span>
                <span class="comp-desc">Bank account details are verified</span>
                <?php if($noteOf('bank_proof') !== ''): ?><span class="comp-note"><strong>Admin Note:</strong> <?php echo e($noteOf('bank_proof')); ?></span><?php endif; ?>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($bankPill['cls']); ?>"><?php echo e($bankPill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </button>

        <!-- Agreement -->
        <button type="button" class="comp-card" onclick="openUploadModal('agent_id_proof', '<?php echo e($cfg['agent_id_proof']['label']); ?>', '<?php echo e($cfg['agent_id_proof']['accept']); ?>', '<?php echo e($fileOf('agent_id_proof')); ?>')">
            <span class="comp-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><path d="M8.5 13h5M8.5 16h3"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">Agreement</span>
                <span class="comp-desc">Agreement is signed by both parties</span>
                <?php if($noteOf('agent_id_proof') !== ''): ?><span class="comp-note"><strong>Admin Note:</strong> <?php echo e($noteOf('agent_id_proof')); ?></span><?php endif; ?>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($agreePill['cls']); ?>"><?php echo e($agreePill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </button>

        <!-- Agent Undertaking -->
        <button type="button" class="comp-card" onclick="openUploadModal('address_proof', '<?php echo e($cfg['address_proof']['label']); ?>', '<?php echo e($cfg['address_proof']['accept']); ?>', '<?php echo e($fileOf('address_proof')); ?>')">
            <span class="comp-ico" style="background:#fff1e0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><path d="M8.5 13h5M8.5 16h3"/><circle cx="16.5" cy="16.5" r="2.4"/><path d="M15.6 16.5l.7.7 1.3-1.4"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">Agent Undertaking</span>
                <span class="comp-desc">Undertaking is submitted</span>
                <?php if($noteOf('address_proof') !== ''): ?><span class="comp-note"><strong>Admin Note:</strong> <?php echo e($noteOf('address_proof')); ?></span><?php endif; ?>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($underPill['cls']); ?>"><?php echo e($underPill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </button>

        <!-- Advance Purpose -->
        <a class="comp-card" href="<?php echo e(route('advances.index')); ?>">
            <span class="comp-ico" style="background:#e6f6ec;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1faa59" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><path d="M8.5 13.5l2.5 2.5 4.5-4.5"/></svg>
            </span>
            <span class="comp-main">
                <span class="comp-name">Advance Purpose</span>
                <span class="comp-desc">Purpose is approved by the firm</span>
            </span>
            <span class="comp-right">
                <span class="comp-pill <?php echo e($purposePill['cls']); ?>"><?php echo e($purposePill['txt']); ?></span>
                <span class="comp-chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
            </span>
        </a>
    </div>

    <div class="comp-summary">
        <h3>Compliance Summary</h3>
        <div class="comp-sum-body">
            <div class="comp-donut">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="52" stroke="#edf0f4" stroke-width="12" fill="none"/>
                    <circle cx="60" cy="60" r="52" stroke="#16a34a" stroke-width="12" fill="none" stroke-linecap="round"
                        stroke-dasharray="<?php echo e($dash); ?> <?php echo e(round($circ, 1)); ?>" transform="rotate(-90 60 60)"/>
                </svg>
                <div class="comp-donut-c">
                    <div class="comp-donut-n"><?php echo e($compCount); ?>/<?php echo e($totalRows); ?></div>
                    <div class="comp-donut-t">Completed</div>
                </div>
            </div>
            <div class="comp-legend">
                <div class="comp-leg"><span class="comp-dot" style="background:#16a34a;"></span>Completed (<?php echo e($compCount); ?>)</div>
                <div class="comp-leg"><span class="comp-dot" style="background:#f59e0b;"></span>Pending (<?php echo e($pendCount); ?>)</div>
                <div class="comp-leg"><span class="comp-dot" style="background:#ef4444;"></span>Rejected (<?php echo e($rejCount); ?>)</div>
            </div>
        </div>
    </div>

    <div class="comp-support">
        Need help? <a href="<?php echo e(route('tickets.create')); ?>">Contact support</a> &middot; <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
    </div>

    <nav class="comp-bottomnav" aria-label="Primary">
        <a class="comp-bnav" href="<?php echo e(route('dashboard')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11l8-7 8 7"/><path d="M6 9.5V20h12V9.5"/><path d="M10 20v-5h4v5"/></svg>
            <span>Dashboard</span>
        </a>
        <a class="comp-bnav" href="<?php echo e(route('advances.index')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><circle cx="11" cy="14" r="2.2"/><path d="M11 12.8v2.4M10 13.4h2"/></svg>
            <span>Advance</span>
        </a>
        <a class="comp-bnav" href="<?php echo e(route('documents.index')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h8L19 8.5V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M13.5 3.5V9H19"/><path d="M9 13h6M9 16.5h6"/></svg>
            <span>Documents</span>
        </a>
        <a class="comp-bnav active" href="#" aria-current="page">
            <svg viewBox="0 0 24 24" fill="#1f6bff"><rect x="5" y="3" width="14" height="18" rx="2"/><rect x="8.5" y="7.5" width="7" height="1.8" rx="0.9" fill="#fff"/><rect x="8.5" y="11" width="7" height="1.8" rx="0.9" fill="#fff"/><rect x="8.5" y="14.5" width="4.5" height="1.8" rx="0.9" fill="#fff"/></svg>
            <span>Compliance</span>
        </a>
        <a class="comp-bnav" href="<?php echo e(route('settings.profile')); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6b7a90" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.5 3.9-5.2 7-5.2s5.8 1.7 7 5.2"/></svg>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Upload Modal (functionality preserved) -->
    <div id="upload-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
        <div class="modal-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900" id="modal-title">Upload Document</h3>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="<?php echo e(route('documents.store')); ?>" method="POST" enctype="multipart/form-data" id="upload-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="document_type" id="modal-doc-type">

                <a id="modal-view-link" href="#" target="_blank" class="hidden mb-3 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 items-center gap-2 text-sm text-blue-700 font-semibold" style="display:none;">
                    View current file &rarr;
                </a>

                <div id="drop-zone" class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-50 transition mb-4 group"
                     onclick="document.getElementById('file-input').click()">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-400 mb-2 group-hover:text-blue-600"></i>
                    <p class="text-sm font-semibold text-slate-700">Click or drag a file here</p>
                    <p class="text-xs text-slate-400 mt-1" id="modal-accept-text">PDF, JPG, PNG up to 5MB</p>
                    <input type="file" name="file" id="file-input" class="hidden" required onchange="updateFileName(this)">
                </div>

                <div id="file-name-display" class="hidden mb-3 bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center gap-2 text-sm text-green-700">
                    <i class="fa-solid fa-file-check"></i>
                    <span id="file-name-text"></span>
                </div>

                <p class="text-xs text-slate-400 mb-5">
                    <i class="fa-solid fa-shield-halved mr-1 text-green-500"></i>
                    Your file will be encrypted and stored securely.
                </p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeUploadModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="upload-btn"
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(type, label, accept, fileUrl) {
            document.getElementById('modal-doc-type').value = type;
            document.getElementById('modal-title').textContent = 'Upload: ' + label;
            document.getElementById('file-input').accept = accept || '';
            var exts = (accept || '').split(',')
                .map(function(e) { return e.replace(/^\./, '').toUpperCase(); })
                .filter(function(e) { return e; })
                .join(', ');
            document.getElementById('modal-accept-text').textContent = (exts ? exts : 'PDF, JPG, PNG') + ' up to 5MB';
            document.getElementById('file-name-display').classList.add('hidden');
            var vl = document.getElementById('modal-view-link');
            if (fileUrl) {
                vl.href = fileUrl;
                vl.style.display = 'flex';
            } else {
                vl.style.display = 'none';
            }
            document.getElementById('upload-modal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('upload-modal').classList.add('hidden');
            document.getElementById('file-input').value = '';
        }

        function updateFileName(input) {
            if (input.files.length > 0) {
                document.getElementById('file-name-text').textContent = input.files[0].name;
                document.getElementById('file-name-display').classList.remove('hidden');
            }
        }

        // Drag and drop
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');

        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-500', 'bg-blue-50'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-blue-500', 'bg-blue-50'); });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                updateFileName(fileInput);
            }
        });

        // Submit with loading state
        document.getElementById('upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('upload-btn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;
        });

        // Close modal on backdrop click
        document.getElementById('upload-modal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/documents/index.blade.php ENDPATH**/ ?>