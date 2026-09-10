<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $title ?? 'Sanction Letter' }}</title>
<style>
@page {
    size: A4 portrait;
    margin: 15px 25px 10px 25px;
}
body {
    font-family: "Helvetica", Arial, sans-serif;
    font-size: 9.5px;
    color: #000;
    line-height: 1.35;
    margin: 0;
    padding: 0;
}
table { width: 100%; border-collapse: collapse; }
.font-bold { font-weight: bold; }

/* Header */
.header-table { width: 100%; margin-bottom: 3px; border-bottom: 2px solid #0033a0; padding-bottom: 3px; }
.header-table .logo-td { width: 20%; vertical-align: top; }
.header-table .logo-td img { max-width: 80px; height: auto; }
.header-table .info-td { width: 65%; vertical-align: top; text-align: right; font-size: 12px; line-height: 1.35; color: #0033a0; font-weight: bold; }
.tagline { font-size: 6.5px; color: #666; margin-top: 1px; margin-bottom: 5px; }

/* Title */
.doc-title { background-color: #004593; color: white; text-align: center; font-size: 12px; font-weight: bold; padding: 4px 0; margin-bottom: 6px; border-radius: 3px; }

/* Meta */
.meta-table { margin-bottom: 6px; font-weight: bold; }
.meta-table td { background-color: #e6f0fa; padding: 2px 6px; font-size: 8.5px; }

/* Address */
.address-section { margin-bottom: 6px; line-height: 1.35; font-size: 9px; }

/* Subject */
.subject-box { background-color: #e6f0fa; padding: 3px 8px; font-weight: bold; color: #004593; margin-bottom: 6px; border-radius: 3px; font-size: 9px; }

/* Body Text */
.body-text { margin-bottom: 8px; text-align: justify; font-size: 9.5px; }

/* Particulars Table */
.details-table { margin-bottom: 8px; border: 1px solid #b0c4de; font-size: 8.5px; }
.details-table th { background-color: #004593; color: white; text-align: left; padding: 3px 8px; border: 1px solid #b0c4de; }
.details-table td { padding: 2.5px 8px; border: 1px solid #b0c4de; background-color: #f0f8ff; }
.details-table tr:nth-child(even) td { background-color: #ffffff; }

/* Terms */
.terms-title { background-color: #004593; color: white; display: inline-block; padding: 3px 12px; font-weight: bold; border-top-right-radius: 10px; border-bottom-right-radius: 10px; margin-bottom: 5px; font-size: 9px; }
.terms-table { width: 100%; margin-bottom: 6px; border-collapse: collapse; }
.terms-table td { vertical-align: top; width: 50%; padding: 0 8px; }
.term-item { margin-bottom: 5px; overflow: hidden; }
.term-num { display: inline-block; background-color: #004593; color: white; width: 12px; height: 12px; text-align: center; border-radius: 50%; font-size: 7px; line-height: 12px; font-weight: bold; margin-right: 3px; float: left; }
.term-content { overflow: hidden; }
.term-title { font-weight: bold; color: #004593; font-size: 8px; }
.term-desc { font-size: 7.5px; text-align: justify; margin-top: 1px; }
.term-desc ul { margin: 1px 0 0 12px; padding: 0; }
.term-desc li { margin-bottom: 1px; }

/* Notes Box */
.notes-box { background-color: #f0f8ff; border: 1px dashed #004593; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px; font-size: 7.5px; color: #004593; }

/* Appreciate */
.appreciate-text { color: #004593; font-weight: bold; font-size: 8px; margin-bottom: 10px; }

/* Signatures */
.sig-table { width: 100%; font-size: 8px; }
.sig-table td { vertical-align: top; width: 50%; padding-top: 3px; }
.sig-left { padding-right: 8px; }
.sig-right { padding-left: 8px; border-left: 1px dashed #b0c4de; }
.sig-header { background-color: #e6f0fa; padding: 3px 6px; font-weight: bold; color: #004593; margin-bottom: 5px; border-radius: 2px; font-size: 8px; }
.sig-row { margin-bottom: 3px; }
.sig-label { display: inline-block; width: 70px; font-weight: bold; }
.sig-val { display: inline-block; border-bottom: 1px solid #000; width: 140px; height: 9px; }
.sig-img { max-height: 30px; }

/* Footer */
.page-footer {
    background-color: #004593;
    color: white;
    text-align: center;
    padding: 6px 0;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 2px;
}
</style>
</head>
<body>

@php
    $letterNo = 'VIREXON/AF/' . date('Y') . '/' . str_pad($agent->id ?? 0, 4, '0', STR_PAD_LEFT);
    $agentIdStr = $agent->detail?->agent_id_number ?? '';
    $agentAddress = $agent->detail?->current_address ?? '';
    if ($agent->detail?->current_city) $agentAddress .= ', ' . $agent->detail->current_city;
    if ($agent->detail?->current_state) $agentAddress .= ', ' . $agent->detail->current_state;
    if ($agent->detail?->current_pincode) $agentAddress .= ' - ' . $agent->detail->current_pincode;

    $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
    if ($advanceAmount == 0 && isset($dynamic_fields['approved_amount'])) {
        $advanceAmount = $dynamic_fields['approved_amount'];
    }
@endphp

<!-- ===== HEADER ===== -->
<table class="header-table">
    <tr>
        <td class="logo-td">
            <img src="{{ public_path('images/virexon-light.png') }}" alt="Virexon Logo">
            <div class="tagline">Grow Together | Build Bigger | Earn More</div>
        </td>
        <td class="info-td">
            <div>Web: https://virexon.in</div>
            <div>Email: info@virexon.in</div>
            <div>Phone: +91 7011641765</div>
            <div>Address: Plot No - 6-10-3/5 plot no 25, Balanagar Hyderabad 500043, (Telangana) India</div>
        </td>
    </tr>
</table>

<!-- Title -->
<div class="doc-title">{{ strtoupper($title ?? 'ADVANCE FUND SANCTION LETTER') }}</div>

<!-- Meta Info -->
<table class="meta-table">
    <tr>
        <td style="width:70%;">Sanction Letter No.: <span style="font-weight:normal;">{{ $letterNo }}</span></td>
        <td style="width:30%; text-align:right;">Date: <span style="font-weight:normal;">{{ now()->format('d/m/Y') }}</span></td>
    </tr>
</table>

<!-- Address Section -->
<div class="address-section">
    <strong>To,</strong><br>
    <strong>Mr. {{ $agent->name }}</strong><br>
    <strong>Agent ID:</strong> <span style="border-bottom:1px solid #ccc;">{{ $agentIdStr }}</span><br>
    <strong>Address:</strong> <span style="border-bottom:1px solid #ccc;">{{ $agentAddress }}</span>
</div>

<!-- Subject -->
<div class="subject-box">
    {{ $subject ?? 'Subject: Sanction of Advance Fund / Financial Support' }}
</div>

<!-- Body text -->
<div class="body-text">
    {!! nl2br($body ?? '') !!}
</div>

<!-- Details Table -->
<table class="details-table">
    <thead>
        <tr>
            <th style="width:40%;">Particulars</th>
            <th style="width:60%;">Details</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="font-bold">Agent Name</td><td>{{ $agent->name }}</td></tr>
        <tr><td class="font-bold">Agent ID</td><td>{{ $agentIdStr }}</td></tr>
        <tr><td class="font-bold">Sanction Letter No.</td><td>{{ $letterNo }}</td></tr>
        <tr><td class="font-bold">Approved Advance Fund Amount</td><td class="font-bold">₹{{ is_numeric($advanceAmount) ? number_format($advanceAmount, 2) : $advanceAmount }}</td></tr>
        <tr><td class="font-bold">Nature of Support</td><td>Advance Fund / Financial Support</td></tr>
        <tr><td class="font-bold">Interest</td><td>Nil</td></tr>
        <tr><td class="font-bold">Disbursement Mode</td><td>Bank Account Transfer</td></tr>
        <tr><td class="font-bold">Bank Account No.</td><td>{{ $agent->detail?->account_number ?? 'As registered in system' }}</td></tr>
        <tr><td class="font-bold">Sanction Date</td><td>{{ now()->format('d/m/Y') }}</td></tr>
        <tr><td class="font-bold">Validity of Sanction</td><td>30 Days from Sanction Date</td></tr>
        @if(!empty($dynamic_fields) && is_array($dynamic_fields))
            @foreach($dynamic_fields as $fieldKey => $fieldValue)
            <tr><td class="font-bold">{{ ucwords(str_replace('_', ' ', $fieldKey)) }}</td><td>{{ $fieldValue }}</td></tr>
            @endforeach
        @endif
    </tbody>
</table>

<!-- Terms and Conditions -->
<!-- <div class="terms-title">Terms & Conditions</div> -->
<div class="terms-title">Informations</div>

<table class="terms-table">
    <tr>
        <td>
            <div class="term-item">
                <span class="term-num">1</span>
                <div class="term-content">
                    <div class="term-title">Purpose of Fund</div>
                    <div class="term-desc">The sanctioned amount is provided as Advance Fund / Financial Support in accordance with the Company's applicable policy for eligible working agents. The fund shall be used only for the purposes permitted under the applicable agreement and Company policy.</div>
                </div>
            </div>
            <div class="term-item">
                <span class="term-num">2</span>
                <div class="term-content">
                    <div class="term-title">Disbursement</div>
                    <div class="term-desc">The approved amount shall be transferred to the bank account registered and verified with the Company, subject to completion of all required verification, documentation and compliance formalities.</div>
                </div>
            </div>
            <div class="term-item">
                <span class="term-num">3</span>
                <div class="term-content">
                    <div class="term-title">Interest</div>
                    <div class="term-desc">No interest shall be charged on the sanctioned Advance Fund, subject to compliance with the applicable agreement and Company policy.</div>
                </div>
            </div>
        </td>
        <td>
            <div class="term-item">
                <span class="term-num">4</span>
                <div class="term-content">
                    <div class="term-title">Adjustment / Settlement</div>
                    <div class="term-desc">The sanctioned amount shall be adjusted, recovered or settled in accordance with the terms specified in the Advance Fund Agreement executed between the Agent and the Company.</div>
                </div>
            </div>
            <div class="term-item">
                <span class="term-num">5</span>
                <div class="term-content">
                    <div class="term-title">Conditions</div>
                    <div class="term-desc">This sanction is subject to:
                        <ul>
                            <li>Successful completion of the Company's verification process.</li>
                            <li>Execution and acceptance of the applicable Advance Fund / Agent Agreement.</li>
                            <li>Submission and verification of required documents.</li>
                            <li>Compliance with Company policies and eligibility criteria.</li>
                            <li>The information and documents submitted by the Agent being genuine and accurate.</li>
                            <li>The Company reserving the right to cancel or modify the sanction before disbursement if any material discrepancy, misrepresentation or non-compliance is identified.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="term-item">
                <span class="term-num">6</span>
                <div class="term-content">
                    <div class="term-title">Important Notice</div>
                    <div class="term-desc">This Sanction Letter confirms the Company's approval of the Advance Fund subject to the terms and conditions of the executed agreement. The actual disbursement shall be governed by the Advance Fund Agreement and applicable Company policies.</div>
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- Info Box & Notes -->
<div class="notes-box">
    <strong>Note:</strong> This letter should be read together with the applicable Agent Agreement, Advance Fund Agreement and Terms & Conditions. In case of any inconsistency, the executed agreement shall prevail.
    @if(!empty($notes))
        <br><strong>Additional Notes:</strong> {!! nl2br(e($notes)) !!}
    @endif
</div>

<!-- Appreciate -->
<div class="appreciate-text">
    We appreciate your association with VIREXON and look forward to your continued contribution as a working agent.
</div>

<!-- Closing -->
<div style="margin-bottom: 12px; font-size: 9.5px;">{!! nl2br(e($closing ?? 'Yours sincerely,')) !!}</div>

<!-- Signatures -->
<table class="sig-table">
    <tr>
        <td class="sig-left">
            <div class="sig-header">For VIREXON (Easy Online Marketing)</div>
            <div class="font-bold" style="margin-bottom:5px;">Authorized Signatory</div>
            <div class="sig-row">
                <span class="sig-label">Name</span> : <span class="font-bold">{{ $signature_name ?? 'Admin' }}</span>
            </div>
            <div class="sig-row">
                <span class="sig-label">Designation</span> : <span>{{ $signature_designation ?? 'Director' }}</span>
            </div>
            <div class="sig-row">
                <span class="sig-label" style="vertical-align:bottom;">Signature</span> :
                <span style="display:inline-block; vertical-align:bottom; border-bottom:1px solid #000; width:130px; text-align:center;">
                    @if(!empty($signature_image_url))
                        <img src="{{ $signature_image_url }}" class="sig-img" alt="signature">
                    @else
                        &nbsp;
                    @endif
                </span>
            </div>
            <div class="sig-row" style="margin-top:5px;">
                <span class="sig-label">Company Seal</span> : <span class="sig-val"></span>
            </div>
        </td>
        <td class="sig-right">
            <div class="sig-header" style="background-color:transparent; border-bottom:1px solid #b0c4de;">AGENT ACKNOWLEDGEMENT</div>
            <div style="margin-bottom:5px; line-height:1.35;">
                I, <strong style="text-decoration: underline">{{ $agent->name }}</strong>, Agent ID <strong style="text-decoration: underline">{{ $agentIdStr }}</strong>, acknowledge receipt of this Advance Fund Sanction Letter and confirm that I have read and understood the applicable terms and conditions governing the sanctioned Advance Fund.
            </div>
            <div class="sig-row">
                <span class="sig-label" style="width:60px;">Agent Name</span> : <span style="display:inline-block;border-bottom:1px solid #000;min-width:160px;text-align:center;font-weight:bold;"></span>
            </div>
            <div class="sig-row">
                <span class="sig-label" style="width:60px;">Agent ID</span> : <span style="display:inline-block;border-bottom:1px solid #000;min-width:160px;text-align:center;"></span>
            </div>
            <div class="sig-row" style="margin-top:8px;">
                <span class="sig-label" style="width:60px;">Signature</span> : <span class="sig-val" style="width:160px;"></span>
            </div>
            <div class="sig-row" style="margin-top:6px;">
                <span class="sig-label" style="width:60px;">Date</span> : <span class="sig-val" style="width:160px;text-align:center;"></span>
            </div>
        </td>
    </tr>
</table>

<!-- Footer -->
<div class="page-footer">
    THANK YOU FOR BEING A PART OF VIREXON
</div>

</body>
</html>