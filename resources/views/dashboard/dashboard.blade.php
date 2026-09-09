@extends('layouts.app')

@section('content')
        <main class="flex-1 p-4 lg:p-6 space-y-6 overflow-x-hidden min-w-0 max-w-full">

            <!-- HERO BLUE BANNER -->
            <div class="hero-banner-bg relative rounded-2xl overflow-hidden shadow-md p-4 sm:p-6 lg:p-10 py-8 lg:py-12 text-white min-h-[240px] sm:min-h-[290px] flex items-center" style="background-color: #051329ba;">

                <!-- Left Background Bar Chart Pillars -->
                <div class="absolute bottom-0 left-44 pointer-events-none hidden sm:flex items-end space-x-2.5 z-0 opacity-40">
                    <div class="w-10 h-28 bg-white/70 rounded-t-sm"></div>
                    <div class="w-10 h-36 bg-white/80 rounded-t-sm"></div>
                    <div class="w-10 h-20 bg-white/60 rounded-t-sm"></div>
                    <div class="w-10 h-44 bg-white/80 rounded-t-sm"></div>
                    <div class="w-10 h-32 bg-white/70 rounded-t-sm"></div>
                </div>

                <!-- Center Background Dark Navy Bars -->
                <div class="absolute bottom-0 left-[48%] pointer-events-none hidden lg:flex items-end space-x-2 z-0 opacity-30">
                    <div class="w-8 h-24 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-36 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-40 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-28 bg-[#041227] rounded-t-sm"></div>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 items-center w-full">

                    <!-- Left Greeting Text -->
                    <div class="lg:col-span-5 space-y-2">
                        <div class="text-[#d1d1d1] text-sm lg:text-base font-semibold">Welcome back</div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight drop-shadow-md">{{ Auth::user()->name }}!</h1>
                        <p class="text-[#d1d1d1] text-sm lg:text-base pt-1">Grow your business with our support.</p>
                    </div>

                    <!-- Center Figures & White Curved Arrow Graphic -->
                    <div class="lg:col-span-3 hidden lg:flex flex-col items-center justify-center relative min-h-[170px]">
                        <div class="absolute inset-0 pointer-events-none z-10">
                            <svg class="w-full h-full" viewBox="0 0 200 100" fill="none">
                                <path d="M 5 85 C 70 75, 110 40, 185 10" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" />
                                <polygon points="172,6 195,8 183,26" fill="#FFFFFF" />
                            </svg>
                        </div>
                        <div class="relative z-0 flex items-end space-x-2.5 pt-8">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-t-full bg-[#FAD7A0] relative overflow-hidden border border-slate-700">
                                    <div class="absolute top-0 inset-x-0 h-3.5 bg-[#1C2833]"></div>
                                </div>
                                <div class="w-11 h-20 bg-[#0B254E] rounded-t-md relative flex justify-center pt-1 mt-0.5">
                                    <div class="w-3 h-5 bg-white clip-v"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-t-full bg-[#F5CBA7] relative overflow-hidden border border-slate-700">
                                    <div class="absolute top-0 inset-x-0 h-3.5 bg-[#17202A]"></div>
                                </div>
                                <div class="w-11 h-20 bg-[#00897B] rounded-t-md relative flex justify-center pt-1 mt-0.5">
                                    <div class="w-3 h-5 bg-white"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Overlay Dark Box Card -->
                    <div class="lg:col-span-4 bg-[#051329] border border-white/10 rounded-2xl p-4 sm:p-6 shadow-2xl flex items-center space-x-3 sm:space-x-4">
                        <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full bg-[#0A1C36] border-2 border-white flex items-center justify-center text-white shrink-0 shadow-md">
                            <span class="text-lg sm:text-2xl font-bold">₹</span>
                        </div>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="font-bold text-white text-sm sm:text-base">Business Support Advance</div>
                            <div class="text-xs text-slate-400">Up to</div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-[#F59E0B] tracking-tight">₹{{ number_format($totalApproved, 0) }}</div>
                            <div class="text-[10px] sm:text-xs text-slate-300 pt-1">
                                <span class="font-semibold">Remaining ₹{{ number_format($remainingLimit, 0) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ROW 1: 4 SUMMARY CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Card 1: Approved Advance Amount -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#E8F8F5] flex items-center justify-center text-[#27AE60] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Approved Advance Amount</div>
                        <div class="text-2xl font-extrabold text-[#27AE60]">₹{{ number_format($totalApproved, 0) }}</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            Remaining <span class="font-semibold text-slate-700">₹{{ number_format($remainingLimit, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Advance Disbursed (Current Usage) -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#EBF5FB] flex items-center justify-center text-[#1565C0] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Advance Disbursed</div>
                        <div class="text-2xl font-extrabold text-[#1565C0]">₹{{ number_format($currentUsage, 0) }}</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            {{ $totalTransactions }} transaction(s) completed
                        </div>
                    </div>
                </div>

                <!-- Card 3: Outstanding Balance -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#FEF5E7] flex items-center justify-center text-[#E65100] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Outstanding Balance</div>
                        <div class="text-2xl font-extrabold text-[#E65100]">₹{{ number_format($currentUsage, 0) }}</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            Repayment via commission
                        </div>
                    </div>
                </div>

                <!-- Card 4: Total Commission -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#F4ECF7] flex items-center justify-center text-[#6A1B9A] font-extrabold text-xl shrink-0">
                        ₹
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Total Commission Earned</div>
                        <div class="text-2xl font-extrabold text-[#6A1B9A]">₹{{ number_format($totalFees, 0) }}</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            This month <span class="font-semibold text-slate-700">₹{{ number_format($monthlyFees, 0) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ROW 2: 3 COLUMNS CARDS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- CARD 1: Outstanding Overview -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h2 class="font-bold text-slate-800 text-sm">Outstanding Overview</h2>
                            <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                        </div>

                        <div class="grid grid-cols-12 gap-3 items-center">
                            <div class="col-span-7 space-y-3 text-xs">
                                <div>
                                    <div class="text-slate-500 font-medium">Total Approved</div>
                                    <div class="text-base font-extrabold text-slate-800">₹{{ number_format($totalApproved, 0) }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-500 font-medium">Total Disbursed</div>
                                    <div class="text-base font-extrabold text-[#27AE60]">₹{{ number_format($currentUsage, 0) }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-500 font-medium">Remaining Limit</div>
                                    <div class="text-base font-extrabold text-[#E65100]">₹{{ number_format($remainingLimit, 0) }}</div>
                                </div>
                            </div>

                            <div class="col-span-5 flex justify-center">
                                <div class="relative w-24 h-24 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-slate-200" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="text-[#27AE60]" stroke-dasharray="{{ $repaymentPercentage }}, 100" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span class="text-base font-black text-slate-800 leading-none">{{ $repaymentPercentage }}%</span>
                                        <span class="text-[10px] text-slate-500 font-medium pt-0.5">Disbursed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#F1F8E9] border border-emerald-200/70 rounded-xl p-3 flex items-start space-x-3 text-xs">
                        <div class="w-5 h-5 rounded-full bg-[#27AE60] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="space-y-1 flex-1">
                            <p class="text-emerald-950 font-medium leading-tight">Repayment is automatically adjusted from your future commissions.</p>
                            <a href="{{ route('payments.index') }}" class="inline-block font-bold text-[#0D6EFD] hover:underline">View Ledger →</a>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Commission Adjustment (Monthly) -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h2 class="font-bold text-slate-800 text-sm">Commission Summary (Monthly)</h2>
                            <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-50">
                                <span class="text-slate-500 font-medium">Monthly Spending</span>
                                <span class="font-extrabold text-slate-800 text-sm">₹{{ number_format($monthlySpent, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-50">
                                <span class="text-slate-500 font-medium">Monthly Fees/Commission</span>
                                <span class="font-extrabold text-slate-800 text-sm">₹{{ number_format($monthlyFees, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-slate-700 font-bold">Total Earned (All Time)</span>
                                <span class="font-black text-slate-900 text-base">₹{{ number_format($totalFees, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#EBF3FE] border border-blue-200/80 rounded-xl p-3 flex items-center space-x-3 text-xs">
                        <div class="w-9 h-9 rounded-lg bg-[#0D6EFD] text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-blue-800 font-semibold text-[11px]">Last Updated</div>
                            <div class="text-sm font-extrabold text-blue-950">{{ now()->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Quick Actions -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="font-bold text-slate-800 text-sm">Quick Actions</h2>
                        <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                    </div>

                    <div class="space-y-2 text-xs">
                        <!-- Action 1: Make Payment -->
                        <a href="{{ route('payments.create') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-[#E8F8F5] hover:bg-emerald-100/70 border border-emerald-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#27AE60] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Make a Payment</div>
                                    <div class="text-[10px] text-slate-500">Use your virtual card for payment</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <!-- Action 2: View Card -->
                        @if($card)
                        <a href="{{ route('cards.show', $card) }}" class="flex items-center justify-between p-2.5 rounded-xl bg-[#EBF5FB] hover:bg-blue-100/70 border border-blue-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#1565C0] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">View My Card</div>
                                    <div class="text-[10px] text-slate-500">Check card details and limits</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endif

                        <!-- Action 3: Payment History -->
                        <a href="{{ route('payments.index') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-[#F4ECF7] hover:bg-purple-100/70 border border-purple-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#6A1B9A] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Payment History</div>
                                    <div class="text-[10px] text-slate-500">View all past transactions</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <!-- Action 4: Download Agreement -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#FEF5E7] hover:bg-amber-100/70 border border-amber-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#E65100] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 11-18 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Download Agreement</div>
                                    <div class="text-[10px] text-slate-500">Business Support Advance Agreement</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        <!-- Action 5: Submit Ticket -->
                        <a href="{{ route('tickets.create') }}" class="flex items-center justify-between p-2.5 rounded-xl bg-[#E0F2F1] hover:bg-teal-100/70 border border-teal-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#00897B] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Submit a Ticket</div>
                                    <div class="text-[10px] text-slate-500">Get help from support team</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                    </div>
                </div>

            </div>

            <!-- ROW 3: COMPLIANCE SNAPSHOT -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Compliance Snapshot</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 items-center">

                    <!-- 1. KYC -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">KYC Documents</div>
                            @if(isset($documents['kyc']) && $documents['kyc']->status === 'verified')
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF5E7] text-[#E65100]">Pending</span>
                            @endif
                        </div>
                    </div>

                    <!-- 2. PAN -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-[#27AE60] border border-slate-200 shrink-0">
                            <span class="text-[9px] font-extrabold border border-[#27AE60] px-0.5 rounded">PAN</span>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">PAN Verified</div>
                            @if(isset($documents['pan']) && $documents['pan']->status === 'verified')
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF5E7] text-[#E65100]">Pending</span>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Bank -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Bank Details</div>
                            @if($detail && $detail->bank_name)
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Verified</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF5E7] text-[#E65100]">Pending</span>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Agreement -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Agreement Signed</div>
                            @if(isset($documents['agreement']) && $documents['agreement']->status === 'verified')
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF5E7] text-[#E65100]">Pending</span>
                            @endif
                        </div>
                    </div>

                    <!-- 5. Undertaking -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Undertaking</div>
                            @if(isset($documents['undertaking']) && $documents['undertaking']->status === 'verified')
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Submitted</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF5E7] text-[#E65100]">Pending</span>
                            @endif
                        </div>
                    </div>

                    <!-- 6. Status Shield Card -->
                    @php
                        $allCompliant = isset($documents['kyc']) && $documents['kyc']->status === 'verified'
                            && isset($documents['pan']) && $documents['pan']->status === 'verified'
                            && $detail && $detail->bank_name
                            && isset($documents['agreement']) && $documents['agreement']->status === 'verified'
                            && isset($documents['undertaking']) && $documents['undertaking']->status === 'verified';
                    @endphp
                    <div class="{{ $allCompliant ? 'bg-[#E8F8F5] border-emerald-200' : 'bg-[#FEF5E7] border-amber-200' }} border rounded-xl p-3 flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full {{ $allCompliant ? 'bg-[#27AE60]' : 'bg-[#E65100]' }} text-white flex items-center justify-center shrink-0 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-extrabold {{ $allCompliant ? 'text-emerald-950' : 'text-amber-950' }} text-xs">
                                {{ $allCompliant ? 'You are Compliant' : 'Action Required' }}
                            </div>
                            <div class="text-[10px] {{ $allCompliant ? 'text-emerald-800' : 'text-amber-800' }} font-medium">
                                {{ $allCompliant ? 'All good to continue business with us.' : 'Complete pending items to proceed.' }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </main>
@endsection
