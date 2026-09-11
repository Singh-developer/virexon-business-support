<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo e($title ?? 'SANCTION LETTER A/F'); ?></title>
<style>
@page {
    size: Letter portrait;
    margin: 10pt 8pt 10pt 8pt;
}
body {
    font-family: Arial, Helvetica, "DejaVu Sans", sans-serif;
    font-size: 9.5pt;
    color: #000;
    line-height: 1.35;
    margin: 0;
    padding: 0;
}
table { border-collapse: collapse; }

/* ---------- shared header ---------- */
.hdr { width: 100%; margin-bottom: 4pt; }
.hdr-logo { vertical-align: top; text-align: left; }
.hdr-logo img { height: 50pt; }
.hdr-info { vertical-align: top; text-align: right; }
.hdr-info .box { text-align: right; font-size: 8pt; line-height: 1.35; }
.hdr-info .lbl { font-weight: bold; }
.hdr-info .val { }
.hdr-info .lnk { color: #0000ff; text-decoration: underline; }

/* ---------- body container (margins match reference sample) ---------- */
.body-wrap { margin-left: 42pt; margin-right: 54pt; }
.p2-wrap { margin-left: 28pt; margin-right: 54pt; }

/* ---------- page 1 ---------- */
.p1 { page-break-after: always; }

.doc-title { text-align: center; font-size: 14pt; font-weight: bold; letter-spacing: 1pt; margin-top: 18pt; }
.title-line { width: 132pt; border-bottom: 1pt solid #000; margin: 2pt auto 0 auto; }

.ref-row { width: 100%; margin-top: 22pt; font-size: 9.8pt; }
.ref-row td { vertical-align: top; padding: 0; }
.ref-row .right { text-align: right; }

.recipient { margin-top: 12pt; font-size: 9.8pt; line-height: 1.5; }

.salutation { margin-top: 10pt; font-size: 9.8pt; }

.intro { margin-top: 12pt; font-size: 9.8pt; line-height: 1.4; text-align: justify; }

.details-table { width: 100%; margin-top: 10pt; border-collapse: collapse; border: 1pt solid #9f8ab9; font-size: 9pt; }
.details-table th {
    background-color: #002060; color: #ffffff; font-weight: bold;
    text-align: left; padding: 3pt 7pt;
    border: 1pt solid #9f8ab9;
    letter-spacing: 0.3pt;
}
.details-table th.det { border-left: 1pt solid #8db3e2; }
.details-table td { padding: 2.8pt 7pt; border: 1pt solid #9f8ab9; vertical-align: middle; }
.details-table td.det { border-left: 1pt solid #8db3e2; }
.details-table tr.alt td { background-color: #dfd8e8; }
.details-table td.amt { font-weight: bold; }

.req { margin-top: 10pt; font-size: 9pt; line-height: 1.4; text-align: justify; }

.credit-head { margin-top: 12pt; font-size: 9.8pt; font-weight: bold; }

.credit-list { margin-top: 3pt; font-size: 9.8pt; line-height: 1.4; }
.cb { margin-bottom: 2pt; padding-left: 14pt; text-indent: -14pt; }

/* ---------- page 1 signature row ---------- */
.sig-row { width: 100%; margin-top: 84pt; font-size: 9.8pt; }
.sig-row td { vertical-align: top; padding: 0; }
.sig-row .right { text-align: right; }

/* ---------- page 2 ---------- */
.sec-title { font-size: 10pt; font-weight: bold; margin-top: 2pt; }
.sec-line { width: 240pt; border-bottom: 1pt solid #000; margin: 1pt 0 0 0; }

.terms { margin-top: 5pt; font-size: 6.9pt; line-height: 1.2; text-align: justify; }
.term { margin-bottom: 1pt; padding-left: 18pt; text-indent: -18pt; }
.term.doc { padding-left: 27pt; text-indent: -9pt; }

.terms-note { margin-top: 7pt; text-align: right; font-size: 7pt; }
.terms-note .chk { display: inline-block; width: 8pt; height: 8pt; border: 0.5pt solid #000; margin-right: 3pt; vertical-align: middle; }

.p2sig { margin-top: 10pt; font-size: 7pt; }
.p2sig .blank { text-align: right; height: 12pt; }
.p2sig .lg { text-align: right; }
.p2sig .btm { margin-top: 5pt; }
.p2sig .btm .lft { }
.p2sig .btm .rgt { text-align: right; float: right; }

/* ---------- footer ---------- */
.page-footer { position: fixed; bottom: 8pt; right: 6pt; font-size: 10pt; }
</style>
</head>
<body>

<?php
    $letterNo = 'VIREXON/AF/' . date('Y') . '/' . str_pad($agent->id ?? 0, 4, '0', STR_PAD_LEFT);
    $agentIdStr = $agent->detail?->agent_id_number ?? '';
    $agentAddress = $agent->detail?->current_address ?? '';
    if ($agent->detail?->current_city) $agentAddress .= ', ' . $agent->detail->current_city;
    if ($agent->detail?->current_state) $agentAddress .= ', ' . $agent->detail->current_state;
    if ($agent->detail?->current_pincode) $agentAddress .= ' - ' . $agent->detail->current_pincode;

    $guardian = $agent->detail?->father_name ?: ($agent->detail?->guardian_name ?? '');

    $df = is_array($dynamic_fields ?? null) ? $dynamic_fields : [];

    $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
    if ($advanceAmount == 0 && isset($df['approved_amount'])) {
        $advanceAmount = $df['approved_amount'];
    }
    $amountText = is_numeric($advanceAmount) && $advanceAmount > 0
        ? '₹ ' . number_format($advanceAmount, 2)
        : $advanceAmount;

    $disbursementMode = $df['disbursement_mode'] ?? 'Bank Account Transfer';
    $processFee       = $df['process_fee'] ?? '';
    $tenure           = $df['tenure'] ?? $df['loan_tenure'] ?? ($agent->detail?->loan_tenure ? $agent->detail->loan_tenure . ' Months' : '');
    $monthlyPrincipal = $df['monthly_principal_settlement'] ?? $df['monthly_principal'] ?? '';
    $monthlyCharges   = $df['monthly_portal_service_charges'] ?? $df['portal_charges'] ?? '';
    $totalMonthly     = $df['total_monthly_settlement'] ?? $df['monthly_settlement'] ?? '';

    $greetingLine = $greeting ?? 'Dear Sir/Madam,';
    $introBody = $body ?? 'With reference to your application/request for financial support and subject to the terms and conditions of the applicable Agent Agreement/ Advance Fund Agreement, we are pleased to inform you that the Company has approved the following Advance Fund in your favors:';
?>

<div class="page-footer">Page&nbsp;2/2</div>

<!-- ================= PAGE 1 ================= -->
<div class="p1">

    <table class="hdr">
        <tr>
            <td class="hdr-logo">
                <img src="<?php echo e(public_path('images/virexon-letter-logo.png')); ?>" alt="Virexon Logo">
            </td>
            <td class="hdr-info">
                <div class="box">
                    <div><span class="lbl">Address::-</span><span class="val"> Plot No. 6-10-3/5, Plot No. 25, Balanagar, Hyderabad -500043</div>
                    <div><span class="lbl">E-mail ID: -</span><span class="lnk"> support@finance.virexon.in</div>
                    <div><span class="lbl">Portal Website: -</span><span class="lnk"> https://finance.virexon.in</div>
                    <div><span class="lbl">Website: -</span><span class="lnk"> https://virexon.in</div>
                </div>
</td>
        </tr>
    </table>

    <div class="body-wrap">

        <div class="doc-title"><?php echo e(strtoupper($title ?? 'SANCTION LETTER A/F')); ?></div>
        <div class="title-line"></div>

        <table class="ref-row">
            <tr>
                <td>Reference No: -</td>
                <td class="right">Date :- <?php echo e(now()->format('d/m/Y')); ?></td>
            </tr>
        </table>

        <div class="recipient">
            <div><strong>MR. <?php echo e($agent->name); ?></strong></div>
            <div>S/o/D/o/W/o <?php if($guardian): ?> <?php echo e($guardian); ?> <?php endif; ?></div>
            <div>Agent ID – <?php echo e($agentIdStr); ?></div>
            <div>Address - <?php echo e($agentAddress); ?></div>
        </div>

        <div class="salutation"><?php echo $greetingLine; ?></div>

        <div class="intro"><?php echo nl2br($introBody); ?></div>

        <table class="details-table">
            <thead>
                <tr>
                    <th style="width:40%;">Particulars</th>
                    <th class="det" style="width:60%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr class="alt"><td class="font-bold"><strong>Agent Name</strong></td><td class="det"><?php echo e($agent->name); ?></td></tr>
                <tr><td class="font-bold"><strong>Agent ID</strong></td><td class="det"><?php echo e($agentIdStr); ?></td></tr>
                <tr class="alt"><td class="font-bold"><strong>Sanction Letter No.</strong></td><td class="det"><?php echo e($letterNo); ?></td></tr>
                <tr><td class="font-bold"><strong>Approved A/F Amount</strong></td><td class="det amt"><?php echo e($amountText); ?></td></tr>
                <tr class="alt"><td class="font-bold"><strong>Disbursement Mode</strong></td><td class="det"><?php echo e($disbursementMode); ?></td></tr>
                <tr><td class="font-bold"><strong>Process Fee</strong></td><td class="det"><?php echo e($processFee); ?></td></tr>
                <tr class="alt"><td class="font-bold"><strong>Tenure</strong></td><td class="det"><?php echo e($tenure); ?></td></tr>
                <tr><td class="font-bold"><strong>Monthly Principal Settlement</strong></td><td class="det"><?php echo e($monthlyPrincipal); ?></td></tr>
                <tr class="alt"><td class="font-bold"><strong>Monthly Portal &amp; Service Charges</strong></td><td class="det"><?php echo e($monthlyCharges); ?></td></tr>
                <tr><td class="font-bold"><strong>Total Monthly Settlement</strong></td><td class="det amt"><?php echo e($totalMonthly); ?></td></tr>
            </tbody>
        </table>

        <div class="req">
            Your are requested to go through the terms &amp; conditions f sanction and one copy of the same be signed and returned to us as an acceptance of the terms &amp; conditions of sanction.
        </div>

        <div class="credit-head">The Credit facilities shall be released on compliance of following:</div>
        <div class="credit-list">
            <div class="cb">&bull;&nbsp;&nbsp;To comply with all the terms and conditions specified in the A/F Sanction.</div>
            <div class="cb">&bull;&nbsp;&nbsp;To properly complete, execute, and sing all required documents relating to the A/F Sanction.</div>
            <div class="cb">&bull;&nbsp;&nbsp;To carefully read, review, and fully understand the information, terms and conditions mentioned on the subsequent 2 pages of the A/F Sanction before signing each page.</div>
        </div>

        <table class="sig-row">
            <tr>
                <td>Signature&nbsp;&nbsp;&nbsp;&nbsp;(Stamp)</td>
                <td class="right">Agent Signature</td>
            </tr>
        </table>

    </div>

</div>

<!-- ================= PAGE 2 ================= -->

<table class="hdr">
    <tr>
        <td class="hdr-logo">
            <img src="<?php echo e(public_path('images/virexon-letter-logo.png')); ?>" alt="Virexon Logo">
        </td>
        <td class="hdr-info">
            <div class="box">
                <div><span class="lbl">Address::-</span><span class="val"> Plot No. 6-10-3/5, Plot No. 25, Balanagar, Hyderabad -500043</div>
                <div><span class="lbl">E-mail ID: -</span><span class="lnk"> support@finance.virexon.in</div>
                <div><span class="lbl">Portal Website: -</span><span class="lnk"> https://finance.virexon.in</div>
                <div><span class="lbl">Website: -</span><span class="lnk"> https://virexon.in</div>
            </div>
</td>
        </tr>
    </table>

<div class="body-wrap p2-wrap">

<div class="sec-title">General, Special and Legally Binding Terms &amp; Conditions:-</div>
<div class="sec-line"></div>

<div class="terms">
    <div class="term">&bull;&nbsp;&nbsp;This A/F Sanction Letter shall constitute only a conditional, provisional and in-principle sanction and shall not be construed, interpreted or relied upon as an unconditional commitment, absolute entitlement or vested right of the agent to demand receive or claim disbursement of the sanctioned A/F amount.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Sanction and any proposed release of A/F shall at all times remain strictly subject to the fulfillment of all conditions prescribed by the Company, including satisfactory completion of verification, due diligence, documentation, eligibility assessment, internal approval and execution of all requisite agreements and declarations.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Company shall be entitled to undertake such verification, as it may deem necessary or appropriate, including verification of identity, residential address, current address, house/ property-related documents, bank account particulars, submitted declarations and any other information or documentation furnished by the Agent.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall be liable to pay the applicable Portal Charges prescribed by the Company for portal processing and related administrative/processing services. Such Portal Charges shall be strictly non-refundable, non-adjustable and non-recoverable, irrespective of whether the proposed A/F is subsequently approved declined, withheld, suspended, cancelle or not released for any reason whatsoever, subject to applicable law.</div>

    <div class="term">&bull;&nbsp;&nbsp;Subject to satisfactory completion of the prescribed verification, fulfillment of all applicable conditions, acceptance of the required documents, confirmation of the Agent&rsquo;s eligibility and due execution of the A/F Agreement, the sanctioned A/F shall be processed for release in accordance with the terms and conditions applicable to the A/F arrangement.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall be exclusively responsible for entering, uploading and submitting all requisite particulars, declarations and documents through the Company&rsquo;s designated portal. Any information or document submitted outside the prescribed portal shall not be treated as a valid submission unless confirmed by the Company.</div>

    <div class="term">&bull;&nbsp;&nbsp;<strong>Mandatory Documentation</strong></div>
    <div class="term doc">&bull;&nbsp;&nbsp;The Agent shall furnish, upload and/or execute, as applicable:</div>
    <div class="term doc">&bull;&nbsp;&nbsp;Self-attested copy of PAN Card</div>
    <div class="term doc">&bull;&nbsp;&nbsp;Self-attested copy of Aadhaar Card</div>
    <div class="term doc">&bull;&nbsp;&nbsp;Valid and verifiable Current Address Proof</div>
    <div class="term doc">&bull;&nbsp;&nbsp;Details and supporting documents relating to an active bank account</div>
    <div class="term doc">&bull;&nbsp;&nbsp;Such further documents, declarations, undertakings, confirmations or authorizations as may be demanded by the Company from time to time.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent hereby represents warrants and undertakes that every statement, declaration, document, record and information furnished to the Company shall be true, complete, accurate, authentic, valid and not misleading in any material respect.</div>

    <div class="term">&bull;&nbsp;&nbsp;Any concealment, suppression, misrepresentation, falsification, fabrication or material omission of information or documents shall constitute a material breach of the terms of this Sanction and shall entitle the Company, subject to applicable law, to reject, suspend, withdraw or cancel the Sanction and/or take such other action as may be available under the applicable agreement and law.</div>

    <div class="term">&bull;&nbsp;&nbsp;Satisfactory completion of verification shall not, in itself, create any enforceable right in favor of the Agent to demand disbursement. The Company shall retain the right to undertake such additional verification or assessment as it may consider necessary before proceeding further.</div>

    <div class="term">&bull;&nbsp;&nbsp;No final A/F arrangement shall come into force solely by virtue of this Sanction Letter. The A/F shall become operative only upon execution of a separate, duly authorized and legally valid A/F Agreement and satisfaction of all conditions precedent specified therein.</div>

    <div class="term">&bull;&nbsp;&nbsp;Upon execution, the A/F Agreement shall constitute the principal contractual instrument governing the A/F arrangement. In the event of any inconsistency, ambiguity or conflict between this Sanction Letter and the duly executed A/F Agreement, the provisions of the latter shall prevail to the extent legally permissible.</div>

    <div class="term">&bull;&nbsp;&nbsp;The proposed A/F is intended solely as a support facility for eligible working Agents associated with the Company, subject to the Company&rsquo;s eligibility criteria and applicable contractual terms. Nothing contained herein shall be construed as an offer or invitation to the general public to avail such facility.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall be required to maintain the eligibility conditions prescribed by the Company throughout the subsistence of the A/F arrangement. Eligibility shall not be deemed to be permanent merely because an initial Sanction has been issued.</div>

    <div class="term">&bull;&nbsp;&nbsp;In the event the Agent voluntarily ceases to work with or associate with the Company, becomes inactive, is removed from the Agent network, is terminated from the applicable engagement, or otherwise ceases to satisfy the prescribed eligibility requirements, the Company shall, subject to the executed A/F Agreement and applicable law, be entitled to suspend, withdraw or cancel the Sanction and/or take such action in respect of the A/F as may be contractually permissible.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Company shall retain the right, subject to applicable law and contractual obligations, to withhold, defer, modify, suspend, withdraw or cancel the Sanction where any material adverse information, discrepancy, deficiency, non-compliance, ineligibility, change in circumstances or other relevant fact comes to its knowledge.</div>

    <div class="term">&bull;&nbsp;&nbsp;Any delay, omission, indulgence, relaxation or failure by the Company to immediately exercise any right or remedy shall not operate as a waiver thereof, nor shall it prejudice or restrict the Company&rsquo;s right to exercise such right or remedy subsequently.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall remain solely responsible for the correctness, completeness, validity and authenticity of all information and documents submitted through the portal or otherwise furnished to the Company. The Company shall be entitled to rely upon the information and documents so furnished, subject to its verification rights.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall read, examine and understand the entire Sanction Letter, including every page, schedule, annexure, note and condition, before execution. The Agent shall sign/initial every page wherever required. Such execution shall constitute an acknowledgement that the Agent has had an opportunity to read and understand the contents thereof and has accepted the applicable terms, subject always to applicable law.</div>

    <div class="term">&bull;&nbsp;&nbsp;No amount shall become due, payable or disbursable merely upon issuance of this Sanction Letter. No disbursement obligation shall arise unless and until all applicable conditions precedent have been fulfilled and the requisite A/F Agreement and related documents have been duly executed.</div>

    <div class="term">&bull;&nbsp;&nbsp;Until completion of all conditions precedent and execution of the A/F Agreement, the Company shall be entitled, subject to applicable law, to discontinue or cancel the proposed A/F arrangement without such cancellation being construed as a breach of any unconditional disbursement commitment.</div>

    <div class="term">&bull;&nbsp;&nbsp;The Agent shall comply with all lawful requirements, verification procedures, documentation requirements, declarations, undertakings and instructions communicated by the Company in connection with the proposed A/F arrangement.</div>

    <div class="term">&bull;&nbsp;&nbsp;This Sanction Letter shall be read together with the documents and conditions specifically referred to herein. However, it shall not be construed as replacing or dispensing with the requirement of a separate A/F Agreement wherever such agreement is prescribed by the Company.</div>

    <div class="term">&bull;&nbsp;&nbsp;By signing this Sanction Letter, the Agent expressly acknowledges that the Agent has read, understood and accepted the conditional nature of the Sanction, including the requirement of verification, Portal Charges, submission of prescribed documents and mandatory execution of the A/F Agreement prior to any finalisation or release of A/F.</div>
</div>

<div class="terms-note"><span class="chk"></span>All Terms &amp; Conditions are read &amp; understand</div>

<div class="p2sig">
    <div class="blank">&nbsp;</div>
    <div class="lg">Signature</div>
    <div class="btm">
        <span class="lft">Signature Stamp</span>
        <span class="rgt">(Name)&nbsp;&nbsp;Agent ID (<?php echo e($agentIdStr); ?>)</span>
    </div>
</div>

</div>

</body>
</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/assertions/pdf.blade.php ENDPATH**/ ?>