<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Support Advance Application Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .form-section-title { color: #1e3a8a; font-weight: 600; font-size: 1.125rem; display: flex; align-items: center; margin-bottom: 1rem; }
        .form-section-title span.number { background-color: #1e3a8a; color: white; width: 24px; height: 24px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; font-size: 0.875rem; margin-right: 0.75rem; }
        .form-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; outline: none; transition: border-color 0.2s; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .form-label { font-size: 0.75rem; font-weight: 500; color: #475569; margin-bottom: 0.25rem; display: block; }
        .form-label span.req { color: #ef4444; }
        .sidebar-card { background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 1.25rem; }
        .sidebar-title { font-weight: 600; font-size: 1rem; margin-bottom: 1rem; display: flex; align-items: center; }
        .sidebar-list li { font-size: 0.875rem; color: #475569; margin-bottom: 0.5rem; display: flex; align-items: flex-start; }
        .sidebar-list li i { margin-top: 0.25rem; margin-right: 0.5rem; color: #22c55e; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <!-- Navbar (simplified) -->
    <header class="bg-slate-900 text-white py-4 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Logo" class="h-8 object-contain">
        </div>
        <div class="hidden md:flex items-center gap-6 text-sm">
            <a href="<?php echo e(route('home')); ?>" class="hover:text-blue-300">Home</a>
            <a href="#" class="hover:text-blue-300">About Us</a>
            <a href="#" class="hover:text-blue-300">Contact Us</a>
            <div class="flex gap-3">
                <a href="<?php echo e(route('login')); ?>" class="border border-white/30 rounded px-4 py-2 hover:bg-white/10"><i class="fa-regular fa-user mr-2"></i>Login</a>
                <a href="<?php echo e(route('register.agent')); ?>" class="bg-green-500 rounded px-4 py-2 hover:bg-green-600"><i class="fa-solid fa-user-plus mr-2"></i>Agent Portal</a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <ul class="list-disc pl-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Form Column -->
            <div class="lg:w-2/3 bg-white border border-gray-200 rounded-lg p-6 lg:p-8 shadow-sm">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800 mb-1">Business Support Advance Application Form</h1>
                        <p class="text-green-600 font-medium text-sm">Apply for Interest-Free Business Support Advance up to ₹5,00,000</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 bg-green-50 text-green-700 border border-green-200 rounded-full px-4 py-1.5 text-sm font-semibold">
                        <span class="bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">%</span>
                        0% Interest<br>60 Months Tenure
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('register.agent.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <!-- 1. Personal Information -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <h2 class="form-section-title"><span class="number">1</span> Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="form-label">Full Name (As per PAN) <span class="req">*</span></label>
                                <input type="text" name="name" value="<?php echo e(old('name')); ?>" class="form-input" placeholder="Enter full name" required>
                            </div>
                            <div>
                                <label class="form-label">Father / Mother / Spouse Name <span class="req">*</span></label>
                                <input type="text" name="guardian_name" value="<?php echo e(old('guardian_name')); ?>" class="form-input" placeholder="Enter name" required>
                            </div>
                            <div>
                                <label class="form-label">Agent ID <span class="req">*</span></label>
                                <input type="text" name="agent_id_number" value="<?php echo e(old('agent_id_number')); ?>" class="form-input" placeholder="Enter Agent ID" required>
                            </div>
                            <div>
                                <label class="form-label">Date of Birth <span class="req">*</span></label>
                                <input type="date" name="date_of_birth" value="<?php echo e(old('date_of_birth')); ?>" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Gender <span class="req">*</span></label>
                                <select name="gender" class="form-input" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php if(old('gender') == 'male'): echo 'selected'; endif; ?>>Male</option>
                                    <option value="female" <?php if(old('gender') == 'female'): echo 'selected'; endif; ?>>Female</option>
                                    <option value="other" <?php if(old('gender') == 'other'): echo 'selected'; endif; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Marital Status <span class="req">*</span></label>
                                <select name="is_married" class="form-input" required>
                                    <option value="">Select Status</option>
                                    <option value="0" <?php if(old('is_married') === '0'): echo 'selected'; endif; ?>>Single</option>
                                    <option value="1" <?php if(old('is_married') === '1'): echo 'selected'; endif; ?>>Married</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Mobile Number <span class="req">*</span></label>
                                <div class="flex">
                                    <span class="bg-gray-50 border border-gray-200 border-r-0 rounded-l-md px-3 py-2 text-sm text-gray-500">+91</span>
                                    <input type="text" name="mobile" value="<?php echo e(old('mobile')); ?>" class="form-input rounded-l-none" placeholder="Enter mobile number" required>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Email ID <span class="req">*</span></label>
                                <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-input" placeholder="Enter email address" required>
                            </div>
                            <div>
                                <label class="form-label">PAN Number <span class="req">*</span></label>
                                <input type="text" name="pan_number" value="<?php echo e(old('pan_number')); ?>" class="form-input" placeholder="Enter PAN number" required>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Address Information -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <h2 class="form-section-title"><span class="number">2</span> Address Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label class="form-label">Address Line 1 <span class="req">*</span></label>
                                <input type="text" name="current_address" value="<?php echo e(old('current_address')); ?>" class="form-input" placeholder="House No., Building, Street" required>
                            </div>
                            <div>
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line_2" value="<?php echo e(old('address_line_2')); ?>" class="form-input" placeholder="Area / Landmark">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="form-label">City / District <span class="req">*</span></label>
                                <input type="text" name="current_city" value="<?php echo e(old('current_city')); ?>" class="form-input" placeholder="Enter city / district" required>
                            </div>
                            <div>
                                <label class="form-label">State <span class="req">*</span></label>
                                <input type="text" name="current_state" value="<?php echo e(old('current_state')); ?>" class="form-input" placeholder="Select State" required>
                            </div>
                            <div>
                                <label class="form-label">Pin Code <span class="req">*</span></label>
                                <input type="text" name="current_pincode" value="<?php echo e(old('current_pincode')); ?>" class="form-input" placeholder="Enter pin code" required>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Bank Details -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <h2 class="form-section-title"><span class="number">3</span> Bank Details (For Disbursement)</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="form-label">Account Holder Name <span class="req">*</span></label>
                                <input type="text" name="account_name" value="<?php echo e(old('account_name')); ?>" class="form-input" placeholder="Enter account holder name" required>
                            </div>
                            <div>
                                <label class="form-label">Bank Name <span class="req">*</span></label>
                                <input type="text" name="bank_name" value="<?php echo e(old('bank_name')); ?>" class="form-input" placeholder="Enter bank name" required>
                            </div>
                            <div>
                                <label class="form-label">Account Number <span class="req">*</span></label>
                                <input type="text" name="account_number" value="<?php echo e(old('account_number')); ?>" class="form-input" placeholder="Enter account number" required>
                            </div>
                            <div>
                                <label class="form-label">IFSC Code <span class="req">*</span></label>
                                <input type="text" name="routing_number" value="<?php echo e(old('routing_number')); ?>" class="form-input" placeholder="Enter IFSC code" required>
                            </div>
                            <div>
                                <label class="form-label">Account Type <span class="req">*</span></label>
                                <select name="account_type" class="form-input" required>
                                    <option value="">Select Account Type</option>
                                    <option value="Savings" <?php if(old('account_type') == 'Savings'): echo 'selected'; endif; ?>>Savings</option>
                                    <option value="Current" <?php if(old('account_type') == 'Current'): echo 'selected'; endif; ?>>Current</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Branch Name <span class="req">*</span></label>
                                <input type="text" name="branch_name" value="<?php echo e(old('branch_name')); ?>" class="form-input" placeholder="Enter branch name" required>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Advance Details -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <h2 class="form-section-title"><span class="number">4</span> Advance Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="form-label">Requested Advance Amount <span class="req">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">₹</span>
                                    <input type="number" name="loan_amount" value="<?php echo e(old('loan_amount')); ?>" class="form-input pl-8" placeholder="Enter amount" required max="500000">
                                </div>
                                <span class="text-xs text-gray-500 mt-1 block">Maximum Limit: ₹5,00,000</span>
                            </div>
                            <div>
                                <label class="form-label">Purpose of Advance <span class="req">*</span></label>
                                <textarea name="purpose_of_advance" rows="2" class="form-input" placeholder="Briefly describe the purpose of advance" required maxlength="300"><?php echo e(old('purpose_of_advance')); ?></textarea>
                                <span class="text-xs text-gray-500 mt-1 block">Maximum 300 characters</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Reference Person Details -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <div class="flex justify-between items-center mb-5">
                            <h2 class="form-section-title !mb-0"><span class="number">5</span> Reference Person Detail</h2>
                            <button type="button" onclick="addReference()" class="px-3 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold hover:bg-green-200"><i class="fa-solid fa-plus mr-1"></i> Add Reference</button>
                        </div>
                        <div id="references-container">
                            <div class="reference-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 items-end bg-gray-50 p-4 rounded border border-gray-200 relative">
                                <div>
                                    <label class="form-label">Person Name <span class="req">*</span></label>
                                    <input type="text" name="references[0][person_name]" class="form-input" placeholder="Enter name" required>
                                </div>
                                <div>
                                    <label class="form-label">Mobile Number <span class="req">*</span></label>
                                    <input type="text" name="references[0][mobile]" class="form-input" placeholder="Enter 10-digit mobile" required pattern="^[0-9]{10}$" title="Enter exactly 10 digits (no +91 or 91)">
                                </div>
                                <div>
                                    <label class="form-label">Company Agent ID <span class="req">*</span></label>
                                    <input type="text" name="references[0][company_agent_id]" class="form-input" placeholder="Enter Agent ID" required>
                                </div>
                                <div>
                                    <button type="button" onclick="removeReference(this)" class="remove-btn px-3 py-2 bg-red-100 text-red-600 rounded text-sm hover:bg-red-200 w-full hidden">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. OTP Verification -->
                    <div class="mb-8 border border-gray-100 rounded-lg p-5">
                        <h2 class="form-section-title"><span class="number">6</span> Email OTP Verification</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="form-label">Verification OTP <span class="req">*</span></label>
                                <div class="flex gap-3">
                                    <input type="text" name="otp" class="form-input flex-1" placeholder="Enter 6-digit OTP" required maxlength="6">
                                    <button type="button" onclick="sendOtp()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded text-sm font-medium hover:bg-slate-300 shrink-0">Send OTP</button>
                                </div>
                                <span class="text-xs text-gray-500 mt-1 block">Click 'Send OTP' to receive a code on the Email ID entered above.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Declaration & Submit -->
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-4 border-t border-gray-200">
                        <label class="flex items-start gap-3 text-xs text-gray-600 max-w-xl cursor-pointer">
                            <input type="checkbox" required class="mt-1">
                            <span>I hereby declare that all the information provided above is true, correct and complete to the best of my knowledge. I have read and understood the terms and conditions of Business Support Advance.</span>
                        </label>
                        <div class="flex gap-3 shrink-0">
                            <button type="reset" class="px-6 py-2 border border-gray-300 text-gray-700 rounded text-sm font-medium hover:bg-gray-50 flex items-center gap-2"><i class="fa-solid fa-rotate-right"></i> Reset</button>
                            <button type="submit" class="px-6 py-2 bg-[#1e3a8a] text-white rounded text-sm font-medium hover:bg-blue-900 flex items-center gap-2"><i class="fa-regular fa-paper-plane"></i> Submit Application</button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Sidebar Info -->
            <div class="lg:w-1/3">
                <div class="sidebar-card">
                    <h3 class="sidebar-title text-blue-600"><i class="fa-solid fa-circle-info mr-2"></i> Important Information</h3>
                    <ul class="sidebar-list">
                        <li><i class="fa-solid fa-circle-check"></i> Maximum Advance Amount: ₹5,00,000 (Five Lakh Only)</li>
                        <li><i class="fa-solid fa-circle-check"></i> Interest Rate: 0% (Zero Percent)</li>
                        <li><i class="fa-solid fa-circle-check"></i> Tenure: Up to 60 Months</li>
                        <li><i class="fa-solid fa-circle-check"></i> Disbursement: Direct Bank Transfer</li>
                        <li><i class="fa-solid fa-circle-check"></i> Adjustment: Through Future Commissions</li>
                        <li><i class="fa-solid fa-circle-check"></i> Only selected working agents are eligible (Up to 10% of active agent base)</li>
                    </ul>
                </div>

                <div class="sidebar-card">
                    <h3 class="sidebar-title text-green-600"><i class="fa-regular fa-file-lines mr-2"></i> Documents Required</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-blue-500 mt-1"><i class="fa-regular fa-id-card"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">PAN Card</div>
                                <div class="text-xs text-gray-500">Clear copy of PAN card</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-green-500 mt-1"><i class="fa-regular fa-address-card"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">Aadhaar Card</div>
                                <div class="text-xs text-gray-500">Clear copy of Aadhaar card</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-yellow-500 mt-1"><i class="fa-solid fa-money-check-dollar"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">Bank Passbook / Cancelled Cheque</div>
                                <div class="text-xs text-gray-500">First page / Cancelled cheque leaf</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-purple-500 mt-1"><i class="fa-regular fa-id-badge"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">Agent ID Proof</div>
                                <div class="text-xs text-gray-500">Your Agent ID or Appointment letter</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-red-500 mt-1"><i class="fa-solid fa-house-user"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">Address Proof</div>
                                <div class="text-xs text-gray-500">Any one: Aadhaar / Voter ID / Utility Bill</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                        <div class="flex gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="text-blue-600 mt-1"><i class="fa-regular fa-image"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">Photograph</div>
                                <div class="text-xs text-gray-500">Recent passport size photograph</div>
                            </div>
                            <div class="text-green-500"><i class="fa-regular fa-circle-check"></i></div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card">
                    <h3 class="sidebar-title text-blue-600"><i class="fa-regular fa-circle-check mr-2"></i> Eligibility Criteria</h3>
                    <ul class="sidebar-list">
                        <li><i class="fa-solid fa-check text-gray-400"></i> Must be a working agent of the firm</li>
                        <li><i class="fa-solid fa-check text-gray-400"></i> Good track record and active business</li>
                        <li><i class="fa-solid fa-check text-gray-400"></i> Advance will be provided for genuine business purposes only</li>
                        <li><i class="fa-solid fa-check text-gray-400"></i> Subject to internal approval and verification</li>
                    </ul>
                </div>

                <div class="sidebar-card bg-yellow-50 border-yellow-200">
                    <div class="flex gap-3">
                        <div class="text-yellow-600 text-2xl mt-1"><i class="fa-solid fa-shield-halved"></i></div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">All information is kept secure and confidential.</div>
                            <div class="text-xs text-gray-600 mt-1">We do not share your data with any third party.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let referenceCount = 1;

        function addReference() {
            const container = document.getElementById('references-container');
            const row = document.createElement('div');
            row.className = 'reference-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 items-end bg-gray-50 p-4 rounded border border-gray-200 relative';
            
            row.innerHTML = `
                <div>
                    <label class="form-label">Person Name <span class="req">*</span></label>
                    <input type="text" name="references[${referenceCount}][person_name]" class="form-input" placeholder="Enter name" required>
                </div>
                <div>
                    <label class="form-label">Mobile Number <span class="req">*</span></label>
                    <input type="text" name="references[${referenceCount}][mobile]" class="form-input" placeholder="Enter 10-digit mobile" required pattern="^[0-9]{10}$" title="Enter exactly 10 digits (no +91 or 91)">
                </div>
                <div>
                    <label class="form-label">Company Agent ID <span class="req">*</span></label>
                    <input type="text" name="references[${referenceCount}][company_agent_id]" class="form-input" placeholder="Enter Agent ID" required>
                </div>
                <div>
                    <button type="button" onclick="removeReference(this)" class="remove-btn px-3 py-2 bg-red-100 text-red-600 rounded text-sm hover:bg-red-200 w-full block">Remove</button>
                </div>
            `;
            container.appendChild(row);
            referenceCount++;
            
            updateRemoveButtons();
        }

        function removeReference(btn) {
            btn.closest('.reference-row').remove();
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.reference-row');
            rows.forEach((row, index) => {
                const btn = row.querySelector('.remove-btn');
                if (rows.length === 1) {
                    btn.classList.add('hidden');
                    btn.classList.remove('block');
                } else {
                    btn.classList.remove('hidden');
                    btn.classList.add('block');
                }
            });
        }

        function sendOtp() {
            const emailInput = document.querySelector('input[name="email"]');
            if (!emailInput || !emailInput.value) {
                alert('Please enter your email address first.');
                emailInput.focus();
                return;
            }
            
            const btn = document.querySelector('button[onclick="sendOtp()"]');
            const originalText = btn.innerText;
            btn.innerText = 'Sending...';
            btn.disabled = true;

            fetch('<?php echo e(route('agent.send-otp')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ email: emailInput.value })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('OTP sent successfully to ' + emailInput.value);
                } else if (data.errors) {
                    alert(Object.values(data.errors).join('\n'));
                } else {
                    alert(data.error || 'Failed to send OTP.');
                }
            })
            .catch(err => {
                alert('An error occurred while sending the OTP.');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views\auth\register-new.blade.php ENDPATH**/ ?>