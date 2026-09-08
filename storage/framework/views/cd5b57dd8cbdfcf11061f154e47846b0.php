<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo e($title ?? 'Sanction Letter'); ?></title>
<style>
@page { margin: 30px; }
body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
table { width: 100%; border-collapse: collapse; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: bold; }

/* Header */
.header-table { margin-bottom: 10px; border-bottom: 2px solid #0033a0; padding-bottom: 10px; }
.logo-td { width: 40%; vertical-align: top; }
.logo-td img { max-width: 180px; }
.info-td { width: 60%; vertical-align: top; text-align: right; font-size: 10px; line-height: 1.5; color: #0033a0; font-weight: bold; }
.info-icon { display: inline-block; width: 12px; margin-right: 4px; }

/* Title */
.doc-title { background-color: #004593; color: white; text-align: center; font-size: 16px; font-weight: bold; padding: 6px 0; margin-bottom: 15px; border-radius: 4px; }

/* Meta */
.meta-table { margin-bottom: 15px; font-weight: bold; }
.meta-table td { background-color: #e6f0fa; padding: 4px 8px; border-radius: 4px; }

/* Address */
.address-section { margin-bottom: 15px; line-height: 1.5; }

/* Subject */
.subject-box { background-color: #e6f0fa; padding: 5px 10px; font-weight: bold; color: #004593; margin-bottom: 15px; border-radius: 4px; }

/* Body Text */
.body-text { margin-bottom: 15px; text-align: justify; }

/* Particulars Table */
.details-table { margin-bottom: 20px; border: 1px solid #b0c4de; }
.details-table th { background-color: #004593; color: white; text-align: left; padding: 6px 10px; border: 1px solid #b0c4de; }
.details-table td { padding: 5px 10px; border: 1px solid #b0c4de; background-color: #f0f8ff; }
.details-table tr:nth-child(even) td { background-color: #ffffff; }

/* Terms */
.terms-title { background-color: #004593; color: white; display: inline-block; padding: 5px 15px; font-weight: bold; border-top-right-radius: 15px; border-bottom-right-radius: 15px; margin-bottom: 10px; }
.terms-table { width: 100%; margin-bottom: 15px; }
.terms-table td { vertical-align: top; width: 50%; padding-right: 15px; }
.term-item { margin-bottom: 10px; }
.term-num { display: inline-block; background-color: #004593; color: white; width: 16px; height: 16px; text-align: center; border-radius: 50%; font-size: 10px; line-height: 16px; font-weight: bold; margin-right: 5px; vertical-align: top; }
.term-content { display: inline-block; width: 90%; }
.term-title { font-weight: bold; color: #004593; font-size: 10px; }
.term-desc { font-size: 9px; text-align: justify; margin-top: 2px; }
.term-desc ul { margin: 2px 0 0 15px; padding: 0; }
.term-desc li { margin-bottom: 2px; }

/* Notes Box */
.notes-box { background-color: #f0f8ff; border: 1px dashed #004593; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 10px; color: #004593; }

/* Appreciate */
.appreciate-text { color: #004593; font-weight: bold; font-size: 10px; margin-bottom: 15px; }

/* Signatures */
.sig-table { width: 100%; font-size: 10px; }
.sig-table td { vertical-align: top; width: 50%; }
.sig-left { padding-right: 10px; }
.sig-right { padding-left: 10px; border-left: 1px dashed #b0c4de; }
.sig-header { background-color: #e6f0fa; padding: 4px 8px; font-weight: bold; color: #004593; margin-bottom: 10px; border-radius: 2px; }
.sig-row { margin-bottom: 5px; }
.sig-label { display: inline-block; width: 100px; font-weight: bold; }
.sig-val { display: inline-block; border-bottom: 1px solid #000; width: 180px; height: 12px; }
.sig-img { max-height: 40px; }

/* Bottom Footer */
.bottom-footer { background-color: #004593; color: white; text-align: center; padding: 8px 0; font-size: 12px; font-weight: bold; letter-spacing: 2px; position: fixed; bottom: -30px; left: -30px; right: -30px; width: 110%; }
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

    $advanceAmount = $agent->advances()->where('status', 'active')->first()?->total_amount ?? 0;
    // Fallback to dynamic field if zero
    if ($advanceAmount == 0 && isset($dynamic_fields['approved_amount'])) {
        $advanceAmount = $dynamic_fields['approved_amount'];
    }
?>

<!-- Header -->
<table class="header-table">
    <tr>
        <td class="logo-td">
            <h1 style="color:#004593;margin:0;font-size:24px;letter-spacing:2px;font-weight:900;">VIREXON</h1>
            <div style="font-size:10px;color:#004593;font-weight:bold;margin-top:2px;">EASY ONLINE MARKETING</div>
            <div style="font-size:9px;color:#666;margin-top:2px;">Grow Together | Build Bigger | Earn More</div>
        </td>
        <td class="info-td">
            <div>🌐 https://virexon.in</div>
            <div>✉ info@virexon.in</div>
            <div>📞 +91 7011641765</div>
            <div>📍 Plot No - 6-10-3/5 plot no 25<br>Balanagar Hyderabad 500043<br>(Telangana) India</div>
        </td>
    </tr>
</table>

<!-- Title -->
<div class="doc-title">ADVANCE FUND SANCTION LETTER</div>

<!-- Meta Info -->
<table class="meta-table">
    <tr>
        <td style="width:70%;">Sanction Letter No. : <span style="font-weight:normal;"><?php echo e($letterNo); ?></span></td>
        <td style="width:30%; text-align:right;">Date : <span style="font-weight:normal;"><?php echo e(now()->format('d/m/Y')); ?></span></td>
    </tr>
</table>

<!-- Address Section -->
<div class="address-section">
    <strong>To,</strong><br>
    <strong>Mr. <?php echo e($agent->name); ?></strong><br>
    <strong>Agent ID:</strong> <span style="border-bottom:1px solid #ccc; padding-bottom:2px;"><?php echo e($agentIdStr); ?></span><br>
    <strong>Address:</strong> <span style="border-bottom:1px solid #ccc; padding-bottom:2px;"><?php echo e($agentAddress); ?></span>
</div>

<!-- Subject -->
<div class="subject-box">
    Subject: Sanction of Advance Fund / Financial Support
</div>

<!-- Body text -->
<div class="body-text">
    Dear Mr. <strong><?php echo e($agent->name); ?></strong>,<br><br>
    With reference to your application/request for financial support and subject to the terms and conditions of the applicable <strong>Agent Agreement / Advance Fund Agreement</strong>, we are pleased to inform you that the Company has approved the following Advance Fund in your favour:
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
        <tr>
            <td class="font-bold">Agent Name</td>
            <td><?php echo e($agent->name); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Agent ID</td>
            <td><?php echo e($agentIdStr); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Sanction Letter No.</td>
            <td><?php echo e($letterNo); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Approved Advance Fund Amount</td>
            <td style="font-weight:bold;">₹<?php echo e(is_numeric($advanceAmount) ? number_format($advanceAmount, 2) : $advanceAmount); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Nature of Support</td>
            <td>Advance Fund / Financial Support</td>
        </tr>
        <tr>
            <td class="font-bold">Interest</td>
            <td>Nil</td>
        </tr>
        <tr>
            <td class="font-bold">Disbursement Mode</td>
            <td>Bank Account Transfer</td>
        </tr>
        <tr>
            <td class="font-bold">Bank Account No.</td>
            <td><?php echo e($agent->detail?->account_number ?? 'As registered in system'); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Sanction Date</td>
            <td><?php echo e(now()->format('d/m/Y')); ?></td>
        </tr>
        <tr>
            <td class="font-bold">Validity of Sanction</td>
            <td>30 Days from Sanction Date</td>
        </tr>
    </tbody>
</table>

<!-- Terms and Conditions -->
<div class="terms-title">Terms & Conditions</div>

<table class="terms-table">
    <tr>
        <!-- Left Column -->
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
        <!-- Right Column -->
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
                    <div class="term-desc">
                        This sanction is subject to:
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
    <?php if(!empty($notes)): ?>
        <br><br><strong>Additional Notes:</strong><br><?php echo nl2br(e($notes)); ?>

    <?php endif; ?>
</div>

<!-- Appreciate -->
<div class="appreciate-text">
    We appreciate your association with VIREXON and look forward to your continued contribution as a working agent.
</div>

<!-- Signatures -->
<table class="sig-table">
    <tr>
        <td class="sig-left">
            <div class="sig-header">For VIREXON (Easy Online Marketing)</div>
            <div class="font-bold" style="margin-bottom:8px;">Authorized Signatory</div>
            
            <div class="sig-row">
                <span class="sig-label">Name</span> : <span class="font-bold"><?php echo e($signature_name ?? 'Admin'); ?></span>
            </div>
            <div class="sig-row">
                <span class="sig-label">Designation</span> : <span><?php echo e($signature_designation ?? 'Director'); ?></span>
            </div>
            <div class="sig-row">
                <span class="sig-label" style="vertical-align:bottom;">Signature</span> : 
                <span style="display:inline-block; vertical-align:bottom; border-bottom:1px solid #000; width:180px; text-align:center;">
                    <?php if(!empty($signature_image_url)): ?>
                        <img src="<?php echo e($signature_image_url); ?>" class="sig-img" alt="signature">
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </span>
            </div>
            <div class="sig-row" style="margin-top:10px;">
                <span class="sig-label">Company Seal</span> : <span class="sig-val"></span>
            </div>
        </td>
        <td class="sig-right">
            <div class="sig-header" style="background-color:transparent; border-bottom:1px solid #b0c4de;">AGENT ACKNOWLEDGEMENT</div>
            <div style="margin-bottom:10px; line-height:1.5;">
                I, <strong><?php echo e($agent->name); ?></strong>, Agent ID <strong><?php echo e($agentIdStr); ?></strong>, acknowledge receipt of this Advance Fund Sanction Letter and confirm that I have read and understood the applicable terms and conditions governing the sanctioned Advance Fund.
            </div>
            
            <div class="sig-row">
                <span class="sig-label" style="width:70px;">Agent Name</span> : <span class="sig-val" style="width:200px;text-align:center;"><strong><?php echo e($agent->name); ?></strong></span>
            </div>
            <div class="sig-row">
                <span class="sig-label" style="width:70px;">Agent ID</span> : <span class="sig-val" style="width:200px;text-align:center;"><?php echo e($agentIdStr); ?></span>
            </div>
            <div class="sig-row" style="margin-top:15px;">
                <span class="sig-label" style="width:70px;">Signature</span> : <span class="sig-val" style="width:200px;"></span>
            </div>
            <div class="sig-row" style="margin-top:10px;">
                <span class="sig-label" style="width:70px;">Date</span> : <span class="sig-val" style="width:200px;text-align:center;"><?php echo e(now()->format('d/m/Y')); ?></span>
            </div>
        </td>
    </tr>
</table>

<div class="bottom-footer">
    THANK YOU FOR BEING A PART OF VIREXON
</div>

</body>
</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/assertions/pdf.blade.php ENDPATH**/ ?>