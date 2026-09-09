@extends('layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8 transition-all">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Advance & Balance</h1>
                <p class="text-slate-500 text-sm mt-1">Manage your advances and view your commission history.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Net Commissions Earned</div>
                <div class="text-3xl font-bold text-slate-800">₹{{ number_format($totalCommission, 2) }}</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Advance Deductions</div>
                <div class="text-3xl font-bold text-slate-800">₹{{ number_format($totalDeductions, 2) }}</div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm bg-gradient-to-br from-slate-800 to-slate-900 text-white">
                <div class="text-slate-300 text-sm font-semibold uppercase tracking-wider mb-2">Outstanding Advance</div>
                <div class="text-3xl font-bold text-white">
                    @if($activeAdvance)
                        ₹{{ number_format($activeAdvance->outstanding_amount, 2) }}
                    @else
                        ₹0.00
                    @endif
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Max Transaction Limit</div>
                <div class="text-3xl font-bold text-blue-600">₹{{ number_format($maxLimit, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">Set by admin</div>
            </div>
        </div>

        {{-- Commission Settings Display --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                <h2 class="font-bold text-slate-800">Your Commission Configuration</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Commission Type</div>
                    <div class="text-lg font-bold text-slate-800">
                        @if($commissionType === 'fixed')
                            Fixed Amount
                        @else
                            Percentage
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Commission Rate</div>
                    <div class="text-lg font-bold text-green-600">
                        @if($commissionType === 'fixed')
                            ₹{{ number_format($commissionFixed, 2) }} per transaction
                        @else
                            {{ number_format($commissionRate, 4) }}%
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Advance Section --}}
        @if($activeAdvance)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h2 class="font-bold text-slate-800">Current Advance Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="mb-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Original Amount Granted</div>
                                <div class="text-xl font-bold text-slate-800">₹{{ number_format($activeAdvance->total_amount, 2) }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Repayment Status</div>
                                @if($activeAdvance->repayment_type === 'unselected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        Action Required: Select Repayment Method
                                    </span>
                                @elseif($activeAdvance->repayment_type === 'one_time')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        One-Time Deduction
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                        EMI: ₹{{ number_format($activeAdvance->emi_amount, 2) }} / payout
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Repayment Selection Form --}}
                        @if($activeAdvance->repayment_type === 'unselected')
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                                <h3 class="font-bold text-blue-900 mb-2">How would you like to repay this advance?</h3>
                                <p class="text-sm text-blue-700 mb-4">You must select a repayment method before you can receive commission payouts.</p>
                                
                                <form action="{{ route('advances.repayment') }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-300">
                                            <input type="radio" name="repayment_type" value="one_time" class="mt-1" onclick="document.getElementById('emi_options').style.display='none'">
                                            <div>
                                                <div class="font-bold text-slate-800">One-Time Deduction</div>
                                                <div class="text-xs text-slate-500">The entire outstanding amount will be deducted from your next commission payout(s) until cleared.</div>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-300">
                                            <input type="radio" name="repayment_type" value="emi" class="mt-1" onclick="document.getElementById('emi_options').style.display='block'">
                                            <div>
                                                <div class="font-bold text-slate-800">EMI (Monthly / Per Payout)</div>
                                                <div class="text-xs text-slate-500">A fixed amount will be deducted from each commission payout.</div>
                                            </div>
                                        </label>

                                        <div id="emi_options" style="display:none;" class="pl-7 pt-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">EMI Amount to Deduct (₹)</label>
                                            <input type="number" name="emi_amount" step="0.01" min="1" max="{{ $activeAdvance->outstanding_amount }}" placeholder="Enter amount..." class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        </div>
                                    </div>
                                    <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                        Confirm Repayment Plan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Commission History --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 flex justify-between items-center">
                <h2 class="font-bold text-slate-800">Commission & Deduction History</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Description</th>
                            <th class="px-6 py-3 font-semibold text-right">Gross Commission</th>
                            <th class="px-6 py-3 font-semibold text-right text-red-500">Advance Deduction</th>
                            <th class="px-6 py-3 font-semibold text-right text-green-600">Net Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($commissions as $commission)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-600">{{ $commission->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $commission->description }}</td>
                            <td class="px-6 py-4 text-right">₹{{ number_format($commission->gross_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right text-red-500">
                                {{ $commission->advance_deduction > 0 ? '- ₹' . number_format($commission->advance_deduction, 2) : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">₹{{ number_format($commission->net_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No commission records found yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

