@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">ASSERTION LETTER</div>
        <h1>Letter #{{ $letter->id }}</h1>
        <p style="color:#64748b;font-size:14px;margin-top:5px">Details of the sent assertion letter.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('assertions.download', $letter) }}" class="btn primary">Download PDF</a>
        <a href="{{ route('assertions.index') }}" class="btn secondary">← Back</a>
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
    <div class="panel" style="padding:20px">
        <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:12px">Recipient</div>
        <div style="font-size:16px;font-weight:700;color:#062b66">{{ $letter->user->name }}</div>
        <div style="color:#64748b;font-size:13px;margin-top:4px">{{ $letter->user->email }}</div>
        @if($letter->user->detail?->mobile)<div style="color:#64748b;font-size:13px">📞 {{ $letter->user->detail->mobile }}</div>@endif
        @if($letter->business)<div style="color:#1557d6;font-size:13px;margin-top:6px;font-weight:600">{{ $letter->business->name }}</div>@endif
    </div>
    <div class="panel" style="padding:20px">
        <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:12px">Status &amp; Timing</div>
        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#dcfce7;color:#16a34a">✓ Sent</div>
        <div style="color:#64748b;font-size:13px;margin-top:10px">Sent: {{ $letter->sent_at?->format('d M Y, h:i A') }}</div>
        <div style="color:#64748b;font-size:13px">Created: {{ $letter->created_at->format('d M Y, h:i A') }}</div>
    </div>
</div>
<div class="panel" style="padding:24px">
    <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:16px">Letter Preview</div>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;max-height:500px;overflow:auto">
        <div style="font-size:22px;font-weight:900;color:#062b66;letter-spacing:2px;margin-bottom:4px">AGENT BUSINESS SUPPORT</div>
        <div style="font-size:10px;color:#1557d6;letter-spacing:3px;font-weight:700;margin-bottom:20px">PARTNERING YOUR GROWTH</div>
        <div style="font-size:18px;font-weight:800;color:#062b66;text-align:center;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px">{{ $letter->title }}</div>
        <div style="text-align:center;font-size:11px;color:#64748b;margin-bottom:20px">Date: {{ $letter->sent_at?->format('d F Y') ?? now()->format('d F Y') }}</div>
        <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px">
            <div><strong>Agent:</strong> {{ $letter->user->name }}</div>
            <div><strong>Email:</strong> {{ $letter->user->email }}</div>
            @if($letter->business)<div><strong>Business:</strong> {{ $letter->business->name }}</div>@endif
        </div>
        <div style="font-weight:700;color:#062b66;margin-bottom:12px">{{ $letter->greeting }}</div>
        <div style="white-space:pre-wrap;margin-bottom:20px;line-height:1.7">{{ $letter->body }}</div>
        <div style="margin-top:20px">{{ $letter->closing }}</div>
        <div style="margin-top:30px;border-top:1px solid #e2e8f0;padding-top:12px">
            <div style="font-weight:800;color:#062b66">{{ $letter->signature_name }}</div>
            @if($letter->signature_designation)<div style="color:#1557d6;font-weight:600;font-size:12px">{{ $letter->signature_designation }}</div>@endif
            @if($letter->signature_company)<div style="color:#64748b;font-size:11px">{{ $letter->signature_company }}</div>@endif
        </div>
    </div>
</div>
@endsection
