<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminTicketController extends Controller
{
    /**
     * Display all tickets across the platform.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $priorityFilter = $request->query('priority', 'all');
        $categoryFilter = $request->query('category', 'all');
        $search = $request->query('search', '');

        $query = Ticket::with(['user:id,name,email,company_name', 'lastReplier:id,name,is_admin'])
            ->withCount('messages')
            ->latest('last_reply_at');

        if ($statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $query->whereIn('status', ['open', 'in_progress', 'answered']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }

        if ($categoryFilter !== 'all') {
            $query->where('category', $categoryFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        $metrics = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'answered' => Ticket::where('status', 'answered')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'metrics' => $metrics,
            'filters' => [
                'status' => $statusFilter,
                'priority' => $priorityFilter,
                'category' => $categoryFilter,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display ticket conversation thread and client info.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'user' => function ($q) {
                $q->withCount(['licenses', 'hostingAccounts', 'invoices']);
            },
            'messages.user:id,name,email,is_admin',
        ]);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Admin staff reply or internal note.
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
            'is_internal' => 'required|boolean',
            'status' => 'nullable|in:open,in_progress,answered,resolved,closed',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,jpg,png,gif,webp,pdf,txt,log,zip',
        ]);

        $admin = Auth::user();
        $isInternal = (bool) $validated['is_internal'];

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
            'user_id' => $admin->id,
            'message' => $validated['message'],
            'is_internal' => $isInternal,
            'attachments' => $attachmentPaths,
        ]);

        if (!$isInternal) {
            $newStatus = $validated['status'] ?? 'answered';
            $updateData = [
                'status' => $newStatus,
                'last_reply_at' => now(),
                'last_reply_by' => $admin->id,
            ];

            if ($newStatus === 'resolved') {
                $updateData['resolved_at'] = now();
            }

            $ticket->update($updateData);

            // Send notification to the client
            try {
                $notificationType = ($newStatus === 'resolved') ? 'resolved' : 'reply';
                $ticket->user->notify(new TicketNotification(
                    $ticket,
                    $notificationType,
                    $validated['message'],
                    $admin->name . ' (Nimbus Support)'
                ));
            } catch (\Throwable $e) {
                // Log mail transport error if any
            }

            $successMsg = ($newStatus === 'resolved')
                ? 'Reply sent and ticket marked as resolved.'
                : 'Reply dispatched to client successfully.';
        } else {
            $successMsg = 'Internal staff note added successfully (hidden from client).';
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Update ticket status (e.g. resolve, close, in-progress).
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,answered,resolved,closed',
        ]);

        $status = $validated['status'];
        $updateData = ['status' => $status];

        if ($status === 'resolved') {
            $updateData['resolved_at'] = now();
            try {
                $ticket->user->notify(new TicketNotification($ticket, 'resolved', 'Ticket resolved by support engineer.'));
            } catch (\Throwable $e) {}
        } elseif ($status === 'closed') {
            $updateData['closed_at'] = now();
        } else {
            $updateData['resolved_at'] = null;
            $updateData['closed_at'] = null;
        }

        $ticket->update($updateData);

        return back()->with('success', "Ticket status updated to " . ucfirst(str_replace('_', ' ', $status)) . ".");
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return back()->with('success', "Ticket priority set to " . ucfirst($validated['priority']) . ".");
    }

    /**
     * Delete ticket.
     */
    public function destroy(Ticket $ticket)
    {
        $number = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', "Ticket #{$number} deleted successfully.");
    }
}
