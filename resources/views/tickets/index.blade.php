@extends('layouts.app')

@section('content')
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Support Tickets</h1>
                <p class="text-slate-500 mt-1 text-sm sm:text-base">Manage your support requests and issues</p>
            </div>
            <a href="{{ route('tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-medium transition-colors shadow-sm flex items-center justify-center gap-2 shrink-0">
                <i class="fa-solid fa-plus"></i> <span>New Ticket</span>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($tickets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="p-4">ID</th>
                                <th class="p-4">Title</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Created</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-slate-500 font-medium">#{{ $ticket->id }}</td>
                                    <td class="p-4 text-slate-900 font-semibold">{{ $ticket->title }}</td>
                                    <td class="p-4">
                                        @if($ticket->status === 'open')
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Open</span>
                                        @elseif($ticket->status === 'in_progress')
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">In Progress</span>
                                        @elseif($ticket->status === 'resolved')
                                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Resolved</span>
                                        @else
                                            <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Closed</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-slate-500 text-sm">{{ $ticket->created_at->format('M d, Y') }}</td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">View <i class="fa-solid fa-arrow-right ml-1"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">No tickets yet</h3>
                    <p class="text-slate-500 mb-6">You haven't created any support tickets.</p>
                    <a href="{{ route('tickets.create') }}" class="text-blue-600 font-medium hover:underline">Create your first ticket</a>
                </div>
            @endif
        </div>
    </main>
@endsection
