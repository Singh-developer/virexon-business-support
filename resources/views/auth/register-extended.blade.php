<x-loanform-layout>
    @php
    // Calculate how far the user has progressed in the database
    $user = auth()->user();
    $detail = $user?->detail;
    $dbMaxStep = 1;
    if ($user) {
    $dbMaxStep = 2; // User exists, step 1 is done
    if (!empty($detail?->agent_id_number)) $dbMaxStep = 3;
    if (!empty($detail?->mobile)) $dbMaxStep = 4;
    if (!empty($detail?->current_address)) $dbMaxStep = 5;
    if (!empty($detail?->loan_amount)) $dbMaxStep = 6;
    if (!empty($detail?->account_number)) $dbMaxStep = 7;
    }
    // The highest step they can access is the max of their DB progress or the step they were just sent to
    $unlockedStep = max($dbMaxStep, session('step', old('current_step', 1)));
    @endphp
    <!-- Main Wrapper -->
    <div class="min-h-screen bg-gray-50 py-8 md:py-12 px-4 sm:px-6 lg:px-8 font-sans flex flex-col">
        <!-- Form Container: Added 'items-stretch' to ensure the blue sidebar never cuts off -->
        <div class="max-w-6xl w-full mx-auto my-auto bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row items-stretch"
            x-data="{
                step: {{ session('step', old('current_step', 1)) }},
                maxStep: {{ $unlockedStep ?? 1 }},
                isMarried: '{{ old('is_married', '0') }}',
                sameAsCurrent: false,
                otpSent: {{ session()->has('loan_form_otp') || $errors->has('otp') ? 'true' : 'false' }},
                sendingOtp: false,
                next() { this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); },
                prev() { this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); },
                async sendOtp() {
                    this.sendingOtp = true;
                    try {
                        let response = await fetch('{{ route('agent.send-otp') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                            }
                        });
                        if(response.ok) {
                            this.otpSent = true;
                        } else {
                            alert('Failed to send OTP. Please try again.');
                        }
                    } catch(e) {
                        alert('An error occurred. Check your network and try again.');
                    }
                    this.sendingOtp = false;
                }
             }">
            <!-- RESPONSIVE SIDEBAR -->
            <!-- <div class="w-full md:w-2/5 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 p-6 md:p-10 text-white flex flex-col justify-center md:justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500 opacity-10 blur-3xl hidden md:block"></div>
                <div class="relative z-10">
                    <div class="mb-6 md:mb-12 text-center md:text-left">
                        <h2 class="text-2xl md:text-3xl font-black mb-1 md:mb-2 tracking-tight">Application Form</h2>
                        <p class="text-blue-200 text-xs md:text-sm">Complete your profile to continue.</p>
                    </div>
                    <div class="flex flex-row md:flex-col justify-between md:justify-start overflow-x-auto md:overflow-visible space-x-4 md:space-x-0 md:space-y-8 pb-2 md:pb-0 hide-scrollbar">
                        <div @click="if(maxStep >= 1) { step = 1; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 1, 'cursor-not-allowed opacity-40': maxStep < 1, 'opacity-100': step >= 1, 'opacity-60': step < 1 && maxStep >= 1 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 1 ? 'bg-blue-500 border-blue-500 text-white' : (step > 1 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 1">1</span>
                                <svg x-show="step > 1" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Account</h3>
                                <p class="text-xs text-blue-200">Login Details</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 2) { step = 2; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 2, 'cursor-not-allowed opacity-40': maxStep < 2, 'opacity-100': step >= 2, 'opacity-60': step < 2 && maxStep >= 2 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 2 ? 'bg-blue-500 border-blue-500 text-white' : (step > 2 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 2">2</span>
                                <svg x-show="step > 2" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Personal</h3>
                                <p class="text-xs text-blue-200">Basic Info</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 3) { step = 3; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 3, 'cursor-not-allowed opacity-40': maxStep < 3, 'opacity-100': step >= 3, 'opacity-60': step < 3 && maxStep >= 3 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 3 ? 'bg-blue-500 border-blue-500 text-white' : (step > 3 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 3">3</span>
                                <svg x-show="step > 3" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Contact</h3>
                                <p class="text-xs text-blue-200">Phone & Status</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 4) { step = 4; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 4, 'cursor-not-allowed opacity-40': maxStep < 4, 'opacity-100': step >= 4, 'opacity-60': step < 4 && maxStep >= 4 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 4 ? 'bg-blue-500 border-blue-500 text-white' : (step > 4 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 4">4</span>
                                <svg x-show="step > 4" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Address</h3>
                                <p class="text-xs text-blue-200">Locations</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 5) { step = 5; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 5, 'cursor-not-allowed opacity-40': maxStep < 5, 'opacity-100': step >= 5, 'opacity-60': step < 5 && maxStep >= 5 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 5 ? 'bg-blue-500 border-blue-500 text-white' : (step > 5 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 5">5</span>
                                <svg x-show="step > 5" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Loan</h3>
                                <p class="text-xs text-blue-200">Calculator</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 6) { step = 6; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 6, 'cursor-not-allowed opacity-40': maxStep < 6, 'opacity-100': step >= 6, 'opacity-60': step < 6 && maxStep >= 6 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 6 ? 'bg-blue-500 border-blue-500 text-white' : (step > 6 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 6">6</span>
                                <svg x-show="step > 6" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Bank</h3>
                                <p class="text-xs text-blue-200">Details</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 7) { step = 7; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 7, 'cursor-not-allowed opacity-40': maxStep < 7, 'opacity-100': step >= 7, 'opacity-60': step < 7 && maxStep >= 7 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 7 ? 'bg-blue-500 border-blue-500 text-white' : 'border-slate-500 text-slate-400'">
                                <span>7</span>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Verify</h3>
                                <p class="text-xs text-blue-200">Documents</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- RESPONSIVE SIDEBAR -->
            <div class="w-full md:w-2/5 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 p-6 md:p-10 text-white flex flex-col justify-center md:justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500 opacity-10 blur-3xl hidden md:block"></div>
                <div class="relative z-10">
                    <div class="mb-6 md:mb-12 text-center md:text-left">
                        <h2 class="text-2xl md:text-3xl font-black mb-1 md:mb-2 tracking-tight">Application Form</h2>
                        <p class="text-blue-200 text-xs md:text-sm">Complete your profile to continue.</p>
                    </div>

                    <div class="flex flex-row md:flex-col justify-between md:justify-start overflow-x-auto md:overflow-visible space-x-4 md:space-x-0 md:space-y-8 pb-2 md:pb-0 hide-scrollbar">
                        <!-- Keep your existing step 1 through 7 divs exactly as they are here -->

                        <!-- Step 1 -->
                        <div @click="if(maxStep >= 1) { step = 1; window.scrollTo({ top: 0, behavior: 'smooth' }) }" class="flex items-center flex-shrink-0 transition" :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 1, 'cursor-not-allowed opacity-40': maxStep < 1, 'opacity-100': step >= 1, 'opacity-60': step < 1 && maxStep >= 1 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400" :class="step === 1 ? 'bg-blue-500 border-blue-500 text-white' : (step > 1 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 1">1</span>
                                <svg x-show="step > 1" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Account</h3>
                                <p class="text-xs text-blue-200">Login Details</p>
                            </div>
                        </div>

                        <div @click="if(maxStep >= 2) { step = 2; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 2, 'cursor-not-allowed opacity-40': maxStep < 2, 'opacity-100': step >= 2, 'opacity-60': step < 2 && maxStep >= 2 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 2 ? 'bg-blue-500 border-blue-500 text-white' : (step > 2 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 2">2</span>
                                <svg x-show="step > 2" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Personal</h3>
                                <p class="text-xs text-blue-200">Basic Info</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 3) { step = 3; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 3, 'cursor-not-allowed opacity-40': maxStep < 3, 'opacity-100': step >= 3, 'opacity-60': step < 3 && maxStep >= 3 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 3 ? 'bg-blue-500 border-blue-500 text-white' : (step > 3 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 3">3</span>
                                <svg x-show="step > 3" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Contact</h3>
                                <p class="text-xs text-blue-200">Phone & Status</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 4) { step = 4; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 4, 'cursor-not-allowed opacity-40': maxStep < 4, 'opacity-100': step >= 4, 'opacity-60': step < 4 && maxStep >= 4 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 4 ? 'bg-blue-500 border-blue-500 text-white' : (step > 4 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 4">4</span>
                                <svg x-show="step > 4" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Address</h3>
                                <p class="text-xs text-blue-200">Locations</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 5) { step = 5; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 5, 'cursor-not-allowed opacity-40': maxStep < 5, 'opacity-100': step >= 5, 'opacity-60': step < 5 && maxStep >= 5 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 5 ? 'bg-blue-500 border-blue-500 text-white' : (step > 5 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 5">5</span>
                                <svg x-show="step > 5" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Loan</h3>
                                <p class="text-xs text-blue-200">Calculator</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 6) { step = 6; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 6, 'cursor-not-allowed opacity-40': maxStep < 6, 'opacity-100': step >= 6, 'opacity-60': step < 6 && maxStep >= 6 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 6 ? 'bg-blue-500 border-blue-500 text-white' : (step > 6 ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-500 text-slate-400')">
                                <span x-show="step <= 6">6</span>
                                <svg x-show="step > 6" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Bank</h3>
                                <p class="text-xs text-blue-200">Details</p>
                            </div>
                        </div>
                        <div @click="if(maxStep >= 7) { step = 7; window.scrollTo({ top: 0, behavior: 'smooth' }) }"
                            class="flex items-center flex-shrink-0 transition"
                            :class="{ 'cursor-pointer group hover:opacity-100': maxStep >= 7, 'cursor-not-allowed opacity-40': maxStep < 7, 'opacity-100': step >= 7, 'opacity-60': step < 7 && maxStep >= 7 }">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold border-2 transition-colors duration-300 group-hover:border-blue-400"
                                :class="step === 7 ? 'bg-blue-500 border-blue-500 text-white' : 'border-slate-500 text-slate-400'">
                                <span>7</span>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition">Verify</h3>
                                <p class="text-xs text-blue-200">Documents</p>
                            </div>
                        </div>

                    </div>

                    <!-- NEW: Dynamic Mobile Step Progress -->
                    <div class="block md:hidden mt-8 w-full transition-all duration-300">
                        <!-- Title and Percentage Row -->
                        <div class="flex justify-between items-baseline mb-2">
                            <h3 class="text-sm font-bold text-white tracking-wide">
                                <span class="text-blue-300">Step <span x-text="step"></span> of 7:</span>
                                <span class="ml-1" x-text="
                step === 1 ? 'Account Details' :
                step === 2 ? 'Personal Info' :
                step === 3 ? 'Contact & Status' :
                step === 4 ? 'Address Locations' :
                step === 5 ? 'Loan Calculator' :
                step === 6 ? 'Bank Details' :
                'Verify Documents'
            "></span>
                            </h3>
                            <span class="text-sm font-bold text-blue-200" x-text="Math.round((step / 7) * 100) + '%'"></span>
                        </div>

                        <!-- Gradient Progress Bar -->
                        <div class="w-full bg-slate-700/50 rounded-full h-1.5 md:h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-400 h-1.5 md:h-2 rounded-full transition-all duration-500 ease-out"
                                :style="`width: ${Math.round((step / 7) * 100)}%`">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- RIGHT CONTENT -->
            <div class="w-full md:w-3/5 p-6 md:p-12 relative bg-white h-full">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6 shadow-sm">
                    <p class="font-bold text-sm mb-1">Please fix the following errors:</p>
                    <ul class="list-disc pl-5 font-medium text-xs">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <!-- Removed flex stretch classes so the form flows naturally -->
                <form x-ref="loanForm" method="POST" action="{{ route('register.agent.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <!-- STEP 1: Account Setup -->
                    <div x-show="step === 1"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Create Your Account</h3>
                        <div class="space-y-4 md:space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            @guest
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Password
                                    </label>
                                    <input type="password" name="password" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                </div>
                            </div>
                            @else
                            <div class="bg-blue-50 p-4 rounded-xl text-sm text-blue-800">
                                <p>Your password is managed by your administrator.</p>
                            </div>
                            @endguest
                        </div>
                    </div>
                    <!-- STEP 2: Personal Details -->
                    <div x-show="step === 2"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Personal Details</h3>
                        <div class="space-y-4 md:space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Agent ID Number</label>
                                <input type="text" name="agent_id_number" value="{{ old('agent_id_number', auth()->user()?->detail?->agent_id_number) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition uppercase tracking-wide">
                                <x-input-error :messages="$errors->get('agent_id_number')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Father's Name</label>
                                    <input type="text" name="father_name" value="{{ old('father_name', auth()->user()?->detail?->father_name) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('father_name')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mother's Name</label>
                                    <input type="text" name="mother_name" value="{{ old('mother_name', auth()->user()?->detail?->mother_name) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('mother_name')" class="mt-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date of Birth</label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', auth()->user()?->detail?->date_of_birth) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gender</label>
                                    @php $savedGender = old('gender', auth()->user()?->detail?->gender); @endphp
                                    <select name="gender" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ $savedGender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $savedGender == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $savedGender == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- STEP 3: Contact & Status -->
                    <div x-show="step === 3"
                        x-data="{ isMarried: '{{ old('is_married', auth()->user()?->detail?->is_married ?? '0') }}' }"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Contact & Status</h3>
                        <div class="space-y-4 md:space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mobile Number</label>
                                    <input type="text" name="mobile" value="{{ old('mobile', auth()->user()?->detail?->mobile) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp Number</label>
                                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', auth()->user()?->detail?->whatsapp_number) }}" class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Marital Status</label>
                                <select name="is_married" x-model="isMarried" class="w-full sm:w-1/2 rounded-xl border-gray-300 bg-gray-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 transition">
                                    <option value="0">Single</option>
                                    <option value="1">Married</option>
                                </select>
                                <x-input-error :messages="$errors->get('is_married')" class="mt-2" />
                            </div>
                            <!-- Conditional Spouse Fields -->
                            <div x-show="isMarried === '1'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5 bg-blue-50 p-4 rounded-xl border border-blue-100" style="display: none;">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Spouse Name</label>
                                    <input type="text" name="spouse_name" value="{{ old('spouse_name', auth()->user()?->detail?->spouse_name) }}" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('spouse_name')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Spouse Mobile</label>
                                    <input type="text" name="spouse_mobile" value="{{ old('spouse_mobile', auth()->user()?->detail?->spouse_mobile) }}" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                    <x-input-error :messages="$errors->get('spouse_mobile')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- STEP 4: Addresses -->
                    @php
                    $currAddr = auth()->user()?->detail?->current_address;
                    $permAddr = auth()->user()?->detail?->permanent_address;
                    $isSame = (!empty($currAddr) && $currAddr === $permAddr) ? 'true' : 'false';
                    @endphp
                    <div x-show="step === 4"
                        x-data="{ sameAsCurrent: {{ old('same_as_current', $isSame) }} }"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Address Information</h3>
                        <div class="space-y-4">
                            <!-- Current Address -->
                            <div class="bg-gray-50 p-4 md:p-5 rounded-xl border border-gray-200 shadow-sm">
                                <h4 class="font-bold text-gray-700 mb-3 text-xs md:text-sm uppercase tracking-wider">Current Address</h4>
                                <div class="space-y-3 md:space-y-4">
                                    <textarea name="current_address" x-ref="curr_addr" @input="if(sameAsCurrent) $refs.perm_addr.value = $event.target.value" rows="2" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition placeholder-gray-400" placeholder="Street Address / Flat / Building">{{ old('current_address', auth()->user()?->detail?->current_address) }}</textarea>
                                    <x-input-error :messages="$errors->get('current_address')" class="mt-2" />
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <input type="text" name="current_city" x-ref="curr_city" @input="if(sameAsCurrent) $refs.perm_city.value = $event.target.value" value="{{ old('current_city', auth()->user()?->detail?->current_city) }}" placeholder="City" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                        <input type="text" name="current_state" x-ref="curr_state" @input="if(sameAsCurrent) $refs.perm_state.value = $event.target.value" value="{{ old('current_state', auth()->user()?->detail?->current_state) }}" placeholder="State" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                        <input type="text" name="current_pincode" x-ref="curr_pin" @input="if(sameAsCurrent) $refs.perm_pin.value = $event.target.value" value="{{ old('current_pincode', auth()->user()?->detail?->current_pincode) }}" placeholder="Pincode" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                    </div>
                                </div>
                            </div>
                            <!-- "Same As Current" Checkbox -->
                            <div class="flex items-center my-4 bg-blue-50 p-4 rounded-xl border border-blue-100 cursor-pointer" @click="sameAsCurrent = !sameAsCurrent; if(sameAsCurrent) { $refs.perm_addr.value = $refs.curr_addr.value; $refs.perm_city.value = $refs.curr_city.value; $refs.perm_state.value = $refs.curr_state.value; $refs.perm_pin.value = $refs.curr_pin.value; }">
                                <input type="checkbox" id="sameAddress" x-model="sameAsCurrent" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 pointer-events-none">
                                <label class="ml-3 text-sm font-bold text-blue-900 pointer-events-none">
                                    My Permanent Address is the same as my Current Address
                                </label>
                            </div>
                            <!-- Permanent Address -->
                            <div x-show="!sameAsCurrent" x-transition class="bg-gray-50 p-4 md:p-5 rounded-xl border border-gray-200 shadow-sm">
                                <h4 class="font-bold text-gray-700 mb-3 text-xs md:text-sm uppercase tracking-wider">Permanent Address</h4>
                                <div class="space-y-3 md:space-y-4">
                                    <textarea name="permanent_address" x-ref="perm_addr" rows="2" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition placeholder-gray-400" placeholder="Street Address / Flat / Building">{{ old('permanent_address', auth()->user()?->detail?->permanent_address) }}</textarea>
                                    <x-input-error :messages="$errors->get('permanent_address')" class="mt-2" />
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <input type="text" name="permanent_city" x-ref="perm_city" value="{{ old('permanent_city', auth()->user()?->detail?->permanent_city) }}" placeholder="City" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                        <input type="text" name="permanent_state" x-ref="perm_state" value="{{ old('permanent_state', auth()->user()?->detail?->permanent_state) }}" placeholder="State" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                        <input type="text" name="permanent_pincode" x-ref="perm_pin" value="{{ old('permanent_pincode', auth()->user()?->detail?->permanent_pincode) }}" placeholder="Pincode" class="w-full rounded-xl border-gray-300 bg-white p-3 focus:ring-2 focus:ring-blue-500 transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- STEP 5: Loan Calculator -->
                    <div x-show="step === 5"
                        x-data="{
            amount: {{ old('loan_amount', auth()->user()?->detail?->loan_amount ?? request('amount', 50000)) }},
            tenure: {{ old('loan_tenure', auth()->user()?->detail?->loan_tenure ?? request('tenure', 3)) }},
            rate: 9.5, 
            get emi() {
                let p = parseFloat(this.amount) || 0;
                let n = parseFloat(this.tenure) || 0;
                if(p <= 0 || n <= 0) return 0;
                let r = (this.rate / 12) / 100;
                let mathPower = Math.pow(1 + r, n);
                return p * r * (mathPower / (mathPower - 1));
            },
            get totalPayable() {
                let n = parseFloat(this.tenure) || 0;
                return this.emi * n;
            },
            get totalInterest() {
                let p = parseFloat(this.amount) || 0;
                return this.totalPayable - p;
            },
            formatINR(value) {
                if(isNaN(value) || value < 0) return '₹0';
                return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Math.round(value));
            }
        }"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Loan Requirements</h3>
                        <p class="text-gray-500 text-sm md:text-base mb-6">Confirm the loan amount and tenure you require.</p>
                        <div class="space-y-6 bg-gray-50 p-5 md:p-6 rounded-xl border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Requested Loan Amount (₹)</label>
                                    <input type="number" name="loan_amount" x-model="amount" min="50000" max="500000" step="1000" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 text-base md:text-lg focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('loan_amount')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tenure (Months)</label>
                                    <input type="number" name="loan_tenure" x-model="tenure" min="3" max="60" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 text-base md:text-lg focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('loan_tenure')" class="mt-2" />
                                </div>
                            </div>
                            <!-- Dynamic Repayment Summary -->
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mt-2 shadow-sm">
                                <h4 class="text-xs md:text-sm font-bold text-blue-800 uppercase tracking-wider mb-4 border-b border-blue-200 pb-2">Estimated Repayment Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
                                    <div>
                                        <span class="block text-xs md:text-sm text-blue-600 font-semibold mb-1">Monthly EMI (9.5% p.a.)</span>
                                        <span class="block text-xl md:text-2xl font-black text-gray-900" x-text="formatINR(emi)"></span>
                                    </div>
                                    <div class="md:border-l border-blue-200 md:pl-6">
                                        <span class="block text-xs md:text-sm text-blue-600 font-semibold mb-1">Total Interest</span>
                                        <span class="block text-xl md:text-2xl font-black text-gray-900" x-text="formatINR(totalInterest)"></span>
                                    </div>
                                    <div class="md:border-l border-blue-200 md:pl-6">
                                        <span class="block text-xs md:text-sm text-blue-600 font-semibold mb-1">Total Payable</span>
                                        <span class="block text-xl md:text-2xl font-black text-blue-700" x-text="formatINR(totalPayable)"></span>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-500 mt-4 text-center md:text-left opacity-75">* EMI shown is an illustrative estimate based on the standard interest rate.</p>
                            </div>
                        </div>
                    </div>
                    <!-- STEP 6: Bank Details Section -->
                    <div x-show="step === 6"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Bank Details</h3>
                        <p class="text-gray-500 text-sm md:text-base mb-6">Please provide your official bank account information.</p>
                        <div class="space-y-4 md:space-y-6 bg-gray-50 p-5 md:p-6 rounded-xl border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Account Name</label>
                                    <input type="text" name="account_name" value="{{ old('account_name', auth()->user()?->detail?->account_name) }}" placeholder="e.g. John Doe" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 text-base md:text-lg focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', auth()->user()?->detail?->bank_name) }}" placeholder="e.g. HDFC Bank" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 text-base md:text-lg focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Account Number</label>
                                    <input type="text" name="account_number" value="{{ old('account_number', auth()->user()?->detail?->account_number) }}" placeholder="0000 0000 0000" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 font-mono text-base md:text-lg tracking-widest focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">IFSC / Routing Number</label>
                                    <input type="text" name="routing_number" value="{{ old('routing_number', auth()->user()?->detail?->routing_number) }}" placeholder="ABCD0123456" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 font-mono text-base md:text-lg uppercase tracking-widest focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('routing_number')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- STEP 7: Documents -->
                    @php
                    // Check if the user is approved.
                    // Update 'status' to match whatever column name you use in your database!
                    $isApproved = auth()->user()?->status === 'approved';
                    @endphp
                    <div x-show="step === 7"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        style="display: none;">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-3 md:mb-6">Verification Documents</h3>
                        <p class="text-gray-500 text-sm md:text-base mb-6">Please provide your official identification numbers.</p>
                        <div class="space-y-6 md:space-y-8 bg-gray-50 p-5 md:p-6 rounded-xl border border-gray-200">
                            <!-- PAN Card Section -->
                            <div x-data="{ photoPreview: null }" class="space-y-4 pb-6 border-b border-gray-200 last:border-0 last:pb-0">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">PAN Card Number</label>
                                    <input type="text" name="pan_number" value="{{ old('pan_number', auth()->user()?->detail?->pan_number) }}" placeholder="ABCDE1234F" maxlength="10" minlength="10" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 font-mono text-base md:text-lg uppercase tracking-widest focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('pan_number')" class="mt-2" />
                                </div>
                                <!-- Conditionally show PAN upload OR Pending Message -->
                                @if($isApproved)
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Upload PAN Document (Image or PDF)</label>
                                    <input type="file" name="pan_file" accept=".jpg,.jpeg,.png,.pdf"
                                        x-on:change="
                            const file = $event.target.files[0];
                            if (file && file.type.match('image.*')) {
                                const reader = new FileReader();
                                reader.onload = (e) => { photoPreview = e.target.result; };
                                reader.readAsDataURL(file);
                            } else {
                                photoPreview = null;
                            }
                        "
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                                    <div class="mt-4" x-show="photoPreview" style="display: none;" x-transition>
                                        <p class="text-xs text-gray-500 mb-2 font-semibold uppercase tracking-wider">Image Preview:</p>
                                        <img x-bind:src="photoPreview" class="w-full max-w-sm h-auto rounded-xl border-2 border-gray-200 shadow-sm object-cover" alt="PAN Preview">
                                    </div>
                                </div>
                                @else
                                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <p class="text-sm text-yellow-800">
                                        <strong>Upload Locked:</strong> You will be able to upload your PAN document here once an admin reviews and approves your initial application details.
                                    </p>
                                </div>
                                @endif
                            </div>
                            <!-- ID Document Section -->
                            <div x-data="{ photoPreview: null }" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">ID Card Number</label>
                                    <!-- Using generic ID label here, you can switch back to Aadhar if you prefer -->
                                    <input type="text" name="aadhar_number" value="{{ old('aadhar_number', auth()->user()?->detail?->aadhar_number) }}" placeholder="0000 0000 0000" maxlength="12" minlength="12" class="w-full rounded-xl border-gray-300 bg-white p-3 md:p-4 font-mono text-base md:text-lg tracking-widest focus:ring-2 focus:ring-blue-500 transition shadow-sm">
                                    <x-input-error :messages="$errors->get('aadhar_number')" class="mt-2" />
                                </div>
                                <!-- Conditionally show ID upload OR Pending Message -->
                                @if($isApproved)
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Upload ID Document (Image or PDF)</label>
                                    <input type="file" name="aadhar_file" accept=".jpg,.jpeg,.png,.pdf"
                                        x-on:change="
                            const file = $event.target.files[0];
                            if (file && file.type.match('image.*')) {
                                const reader = new FileReader();
                                reader.onload = (e) => { photoPreview = e.target.result; };
                                reader.readAsDataURL(file);
                            } else {
                                photoPreview = null;
                            }
                        "
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                                    <div class="mt-4" x-show="photoPreview" style="display: none;" x-transition>
                                        <p class="text-xs text-gray-500 mb-2 font-semibold uppercase tracking-wider">Image Preview:</p>
                                        <img x-bind:src="photoPreview" class="w-full max-w-sm h-auto rounded-xl border-2 border-gray-200 shadow-sm object-cover" alt="ID Preview">
                                    </div>
                                </div>
                                @else
                                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <p class="text-sm text-yellow-800">
                                        <strong>Upload Locked:</strong> You will be able to upload your ID document here once an admin reviews and approves your initial application details.
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-6 mt-8">
                            <label class="flex items-center space-x-3 text-sm md:text-base cursor-pointer">
                                <input type="checkbox" name="terms" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-5 h-5" required>
                                <span class="text-gray-700">I accept the <a href="#" class="text-blue-600 font-semibold hover:underline">Terms & Conditions</a> and authorize this transaction.</span>
                            </label>
                            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
                        </div>
                        <!-- NEW: OTP Verification Section -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mt-8 shadow-sm">
                            <h4 class="text-sm font-bold text-blue-900 mb-3">Email Verification Required</h4>

                            <!-- Send OTP Button (Shows initially) -->
                            <div x-show="!otpSent">
                                <p class="text-sm text-blue-800 mb-4">Please verify your email address before finalizing the application.</p>
                                <button type="button" @click="sendOtp" :disabled="sendingOtp" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-text="sendingOtp ? 'Sending OTP...' : 'Send OTP to Email'"></span>
                                </button>
                            </div>

                            <!-- OTP Input Field (Shows after clicking "Send OTP") -->
                            <div x-show="otpSent" style="display: none;" x-transition>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Enter 6-Digit OTP</label>
                                <div class="flex items-center space-x-3">
                                    <input type="text" name="otp" maxlength="6" class="w-1/2 rounded-xl border-gray-300 bg-white p-3 font-mono text-lg tracking-widest focus:ring-2 focus:ring-blue-500 transition shadow-sm" placeholder="XXXXXX">
                                    <button type="button" @click="sendOtp" class="text-sm text-blue-600 font-semibold hover:underline bg-transparent border-0 cursor-pointer">Resend OTP</button>
                                </div>
                                <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                                <p class="text-xs text-blue-600 mt-2">Check your inbox and spam folder.</p>
                            </div>
                        </div>
                    </div>


                    <!-- Navigation Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                        <button type="button" x-show="step > 1" @click="step--; window.scrollTo({ top: 0, behavior: 'smooth' });" class="px-4 py-2 md:px-6 md:py-3 font-bold text-slate-500 hover:text-slate-800 transition rounded-lg hover:bg-slate-100 flex items-center text-sm md:text-base" style="display: none;">
                            <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </button>
                        <div x-show="step === 1"></div>
                        <button type="submit" name="current_step" x-bind:value="step" class="px-6 py-2 md:px-8 md:py-3 bg-blue-600 text-white font-black rounded-xl shadow-lg hover:bg-blue-700 transition hover:-translate-y-0.5 flex items-center text-sm md:text-base">
                            <span x-show="step < 7">Save & Next Step</span>
                            <span x-show="step === 7">Submit Application</span>
                            <svg class="w-4 h-4 md:w-5 md:h-5 ml-1 md:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-loanform-layout>