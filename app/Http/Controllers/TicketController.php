<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource for the Agent.
     */
    public function index()
    {
        $tickets = auth()->user()->tickets()->latest()->get();
        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:png,jpeg,jpg,webp,pdf|max:5120', // 5MB max
        ]);

        $ticket = new Ticket();
        $ticket->user_id = auth()->id();
        $ticket->title = $request->title;
        $ticket->message = $request->message;
        
        if ($request->hasFile('attachment')) {
            $ticket->attachment_path = $request->file('attachment')->store('tickets', 'public');
        }
        
        $ticket->status = 'open';
        $ticket->save();

        // Notify Admins
        $admins = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'super-admin']);
        })->get();
        
        if ($admins->count() > 0 && class_exists(TicketCreatedNotification::class)) {
            Notification::send($admins, new TicketCreatedNotification($ticket));
        }

        return redirect()->route('tickets.index')->with('success', 'Support ticket created successfully. We will get back to you soon.');
    }

    /**
     * Display the specified resource for Agent.
     */
    public function show(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        return view('tickets.show', compact('ticket'));
    }

    // ==========================================
    // ADMIN METHODS
    // ==========================================

    public function adminIndex()
    {
        $tickets = Ticket::with('user')->latest()->get();
        return view('admin.tickets.index', compact('tickets'));
    }

    public function adminShow(Ticket $ticket)
    {
        $ticket->load('user');
        return view('admin.tickets.show', compact('ticket'));
    }

    public function adminUpdate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $oldStatus = $ticket->status;
        $ticket->status = $request->status;
        $ticket->save();

        if ($oldStatus !== $ticket->status && class_exists(TicketStatusUpdatedNotification::class)) {
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket));
        }

        return back()->with('success', 'Ticket status updated to ' . ucfirst(str_replace('_', ' ', $request->status)));
    }
}
