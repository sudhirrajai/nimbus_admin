<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Display client's support tickets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->query('status', 'all');

        $query = Ticket::where('user_id', $user->id)
            ->with(['lastReplier:id,name,is_admin'])
            ->withCount('publicMessages')
            ->latest('last_reply_at');

        if ($statusFilter === 'open') {
            $query->open();
        } elseif ($statusFilter === 'resolved') {
            $query->resolved();
        } elseif ($statusFilter === 'closed') {
            $query->closed();
        }

        $tickets = $query->paginate(15)->withQueryString();

        $metrics = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'open' => Ticket::where('user_id', $user->id)->open()->count(),
            'resolved' => Ticket::where('user_id', $user->id)->resolved()->count(),
        ];

        // Also fetch user's active hosting accounts or licenses for quick reference
        $userServices = [
            'licenses' => $user->licenses()->select('id', 'license_key', 'domain', 'status')->get(),
            'hosting' => $user->hostingAccounts()->select('id', 'domain', 'status')->get(),
        ];

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'metrics' => $metrics,
            'filters' => [
                'status' => $statusFilter,
            ],
            'userServices' => $userServices,
        ]);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:180',
            'category' => 'required|in:technical,managed_hosting,billing,license,feature_request,general',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,jpg,png,gif,webp,pdf,txt,log,zip',
        ]);

        $user = Auth::user();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by' => $user->id,
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('tickets/' . $ticket->id, 'public');
            $attachmentPaths[] = [
                'name' => $file->getClientOriginalName(),
                'path' => Storage::disk('public')->url($path),
                'size' => $file->getSize(),
            ];
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_internal' => false,
            'attachments' => $attachmentPaths,
        ]);

        // Send email notification to user
        try {
            $user->notify(new TicketNotification($ticket, 'created', $validated['message'], $user->name));
        } catch (\Throwable $e) {
            // Log mail transport error if any
        }

        return redirect()->route('tickets.show', ['ticket' => $ticket->id])
            ->with('success', "Support Ticket #{$ticket->ticket_number} created successfully. Our team has been notified!");
    }

    /**
     * View ticket conversation and replies.
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to this support ticket.');
        }

        $ticket->load([
            'user:id,name,email,company_name',
            'publicMessages.user:id,name,is_admin',
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Client reply to an existing ticket.
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to this support ticket.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,jpg,png,gif,webp,pdf,txt,log,zip',
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('tickets/' . $ticket->id, 'public');
            $attachmentPaths[] = [
                'name' => $file->getClientOriginalName(),
                'path' => Storage::disk('public')->url($path),
                'size' => $file->getSize(),
            ];
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_internal' => false,
            'attachments' => $attachmentPaths,
        ]);

        // If ticket was answered or resolved, client's reply switches it back to open/in_progress
        $newStatus = in_array($ticket->status, ['resolved', 'closed', 'answered']) ? 'open' : $ticket->status;

        $ticket->update([
            'status' => $newStatus,
            'last_reply_at' => now(),
            'last_reply_by' => $user->id,
        ]);

        return back()->with('success', 'Your reply has been posted to the ticket.');
    }

    /**
     * Client marks their own ticket as resolved.
     */
    public function close(Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to this support ticket.');
        }

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'Client marked this support ticket as resolved.',
            'is_internal' => false,
            'attachments' => [],
        ]);

        return back()->with('success', 'Support ticket marked as resolved. Glad we could help!');
    }
}
