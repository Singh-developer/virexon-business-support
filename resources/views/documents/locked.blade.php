<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents — Not Available Yet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F4F6F8; }</style>
</head>
<body class="text-slate-800 antialiased min-h-screen">
    @include('partials.navbar')

    <div class="max-w-2xl mx-auto px-4 py-16">

        <!-- Status Timeline Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <!-- Top Banner -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-8 py-6 text-white">
                <div class="flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-file-shield text-2xl text-slate-300"></i>
                    <h1 class="text-xl font-bold">My Documents</h1>
                </div>
                <p class="text-slate-300 text-sm">Document upload is managed in stages for your security.</p>
            </div>

            <!-- Current Status Banner -->
            <div class="px-8 py-5 border-b border-slate-100">
                @if($appStatus === 'pending')
                <div class="flex items-start gap-4 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clock text-yellow-600"></i>
                    </div>
                    <div>
                        <div class="font-bold text-yellow-900 mb-0.5">Application Under Review</div>
                        <div class="text-yellow-800 text-sm">Your Fund Application has been submitted and is currently being reviewed by the admin. Document upload will be enabled once the admin confirms receipt of your form.</div>
                    </div>
                </div>
                @elseif($appStatus === 'rejected')
                <div class="flex items-start gap-4 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-xmark text-red-600"></i>
                    </div>
                    <div>
                        <div class="font-bold text-red-900 mb-0.5">Application Rejected</div>
                        <div class="text-red-800 text-sm">Unfortunately, your Fund Application has been rejected. Please contact support if you believe this is a mistake or to understand the reason.</div>
                    </div>
                </div>
                @else
                <div class="flex items-start gap-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-hourglass-half text-slate-600"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 mb-0.5">Not Available Yet</div>
                        <div class="text-slate-600 text-sm">Document upload is not yet enabled for your account. Please wait for the admin to process your application.</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Application Stage Tracker -->
            <div class="px-8 py-6">
                <h2 class="font-bold text-slate-800 mb-5 text-sm uppercase tracking-wider">Your Application Progress</h2>

                <ol class="relative border-l-2 border-slate-100 ml-3 space-y-6">

                    <!-- Step 1 -->
                    <li class="ml-6">
                        <span class="absolute -left-3.5 w-7 h-7 rounded-full bg-green-500 flex items-center justify-center ring-4 ring-white">
                            <i class="fa-solid fa-check text-white text-xs"></i>
                        </span>
                        <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3">
                            <div class="font-bold text-green-900 text-sm">Fund Application Submitted ✓</div>
                            <div class="text-green-700 text-xs mt-0.5">Your application form has been successfully submitted.</div>
                        </div>
                    </li>

                    <!-- Step 2 -->
                    <li class="ml-6">
                        <span class="absolute -left-3.5 w-7 h-7 rounded-full flex items-center justify-center ring-4 ring-white
                            {{ in_array($appStatus, ['form_received', 'approved']) ? 'bg-green-500' : 'bg-yellow-400' }}">
                            @if(in_array($appStatus, ['form_received', 'approved']))
                                <i class="fa-solid fa-check text-white text-xs"></i>
                            @else
                                <i class="fa-solid fa-clock text-white text-xs"></i>
                            @endif
                        </span>
                        <div class="{{ in_array($appStatus, ['form_received', 'approved']) ? 'bg-green-50 border-green-100' : 'bg-yellow-50 border-yellow-100' }} border rounded-xl px-4 py-3">
                            <div class="font-bold text-sm {{ in_array($appStatus, ['form_received', 'approved']) ? 'text-green-900' : 'text-yellow-900' }}">
                                Admin Reviews & Confirms Form
                                @if(!in_array($appStatus, ['form_received', 'approved']))
                                <span class="text-xs font-medium text-yellow-700 ml-2">← Current Step</span>
                                @else
                                ✓
                                @endif
                            </div>
                            <div class="text-xs mt-0.5 {{ in_array($appStatus, ['form_received', 'approved']) ? 'text-green-700' : 'text-yellow-700' }}">Admin verifies your application and marks it as received.</div>
                        </div>
                    </li>

                    <!-- Step 3 — Current Goal -->
                    <li class="ml-6">
                        <span class="absolute -left-3.5 w-7 h-7 rounded-full flex items-center justify-center ring-4 ring-white
                            {{ in_array($appStatus, ['form_received', 'approved']) ? 'bg-blue-500' : 'bg-slate-200' }}">
                            @if(in_array($appStatus, ['form_received', 'approved']))
                                <i class="fa-solid fa-arrow-up text-white text-xs"></i>
                            @else
                                <span class="text-slate-500 font-bold text-xs">3</span>
                            @endif
                        </span>
                        <div class="{{ in_array($appStatus, ['form_received', 'approved']) ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-100' }} border rounded-xl px-4 py-3">
                            <div class="font-bold text-sm {{ in_array($appStatus, ['form_received', 'approved']) ? 'text-blue-900' : 'text-slate-400' }}">
                                Upload Your Documents
                            </div>
                            <div class="text-xs mt-0.5 {{ in_array($appStatus, ['form_received', 'approved']) ? 'text-blue-700' : 'text-slate-400' }}">
                                PAN Card, Aadhaar, Photograph, Bank Proof, Agent ID, Address Proof
                            </div>
                        </div>
                    </li>

                    <!-- Step 4 -->
                    <li class="ml-6">
                        <span class="absolute -left-3.5 w-7 h-7 rounded-full flex items-center justify-center ring-4 ring-white
                            {{ $appStatus === 'approved' ? 'bg-green-500' : 'bg-slate-200' }}">
                            @if($appStatus === 'approved')
                                <i class="fa-solid fa-check text-white text-xs"></i>
                            @else
                                <span class="text-slate-500 font-bold text-xs">4</span>
                            @endif
                        </span>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-3">
                            <div class="font-bold text-sm text-slate-400">Final Approval</div>
                            <div class="text-slate-400 text-xs mt-0.5">Admin reviews your documents and gives final approval.</div>
                        </div>
                    </li>

                </ol>
            </div>

            <!-- Footer Actions -->
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row gap-3 justify-between items-center">
                <p class="text-xs text-slate-500">
                    <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i>
                    Need help? Contact our support team.
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('tickets.create') }}"
                       class="flex items-center gap-2 text-xs font-semibold px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg transition">
                        <i class="fa-regular fa-headset"></i> Open Support Ticket
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-2 text-xs font-semibold px-4 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-white transition">
                        ← Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

