<!DOCTYPE html>
<html lang="en" class="h-full w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $ticket->id }} - Agent Business Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-['Plus_Jakarta_Sans'] h-full flex flex-col">

    @include('partials.navbar')

    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-6 flex justify-between items-end">
            <div>
                <a href="{{ route('tickets.index') }}" class="text-slate-500 hover:text-slate-800 text-sm font-medium mb-4 inline-block"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Tickets</a>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $ticket->title }}</h1>
                <p class="text-slate-500 mt-1">Ticket #{{ $ticket->id }} • Opened on {{ $ticket->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div>
                @if($ticket->status === 'open')
                    <span class="bg-blue-100 text-blue-800 px-4 py-1.5 rounded-full text-sm font-bold border border-blue-200">Open</span>
                @elseif($ticket->status === 'in_progress')
                    <span class="bg-amber-100 text-amber-800 px-4 py-1.5 rounded-full text-sm font-bold border border-amber-200">In Progress</span>
                @elseif($ticket->status === 'resolved')
                    <span class="bg-emerald-100 text-emerald-800 px-4 py-1.5 rounded-full text-sm font-bold border border-emerald-200">Resolved</span>
                @else
                    <span class="bg-slate-200 text-slate-800 px-4 py-1.5 rounded-full text-sm font-bold border border-slate-300">Closed</span>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 sm:p-8 bg-slate-50 border-b border-slate-200 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-900">{{ $ticket->user->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $ticket->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="p-6 sm:p-8">
                <div class="prose prose-slate max-w-none mb-8 whitespace-pre-wrap">{{ $ticket->message }}</div>
                
                @if($ticket->attachment_path)
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h4 class="text-sm font-bold text-slate-700 mb-3">Attachment:</h4>
                        <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 hover:bg-slate-100 px-4 py-3 rounded-lg text-sm font-medium text-slate-700 transition">
                            <i class="fa-solid fa-paperclip text-slate-400"></i> View Attached File
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </main>

</body>
</html>

