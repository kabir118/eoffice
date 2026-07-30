<?php

namespace App\Http\Controllers;

use App\Models\SupportQuery;
use App\Models\SupportQueryMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportQueryController extends Controller
{
    /**
     * Display a listing of support queries.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $baseQuery = SupportQuery::with(['user.department', 'user.designation', 'messages']);
            
            // Stale count for admin: pending tickets created > 7 days ago
            $staleCount = SupportQuery::where('status', 'pending')
                ->where('created_at', '<', now()->subDays(7))
                ->count();
            
            // Admin wants old tickets listed at the top (oldest first)
            $allTickets = $baseQuery->oldest()->get();
        } else {
            $baseQuery = SupportQuery::where('user_id', $user->id)->with(['user.department', 'messages']);
            $staleCount = 0;
            
            // User wants newly created and open tickets at the top (newest first)
            $allTickets = $baseQuery->latest()->get();
        }

        // Group tickets depending on roles
        if ($user->isSuperAdmin()) {
            // For admin: active tickets are those that are PENDING
            $activeTickets = $allTickets->filter(function($t) {
                return $t->status === 'pending';
            });
            
            // No Action Needed = read or replied
            $noActionNeeded = $allTickets->filter(function($t) {
                return $t->status === 'read' || $t->status === 'replied';
            });
            
            // Closed = solved
            $closedTickets = $allTickets->filter(function($t) {
                return $t->status === 'solved';
            });
        } else {
            // For staff: active tickets are those that are REPLIED (needs user feedback/action)
            $activeTickets = $allTickets->filter(function($t) {
                return $t->status === 'replied';
            });
            
            // No Action Needed = pending (waiting for admin response) or read
            $noActionNeeded = $allTickets->filter(function($t) {
                return $t->status === 'pending' || $t->status === 'read';
            });
            
            // Closed = solved
            $closedTickets = $allTickets->filter(function($t) {
                return $t->status === 'solved';
            });
        }

        // Group active tickets by age
        $overAWeekOld = $activeTickets->filter(function($t) {
            return $t->created_at < now()->subDays(7);
        });

        $overADayOld = $activeTickets->filter(function($t) {
            return $t->created_at >= now()->subDays(7) && $t->created_at < now()->subDay();
        });

        $updatedToday = $activeTickets->filter(function($t) {
            return $t->created_at >= now()->subDay();
        });

        // Load departments with count of active pending tickets (for category sidebar widget)
        $departments = \App\Models\Department::all()->map(function($dept) {
            $dept->active_tickets_count = SupportQuery::where('status', 'pending')
                ->whereHas('user', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })->count();

            $dept->total_tickets_count = SupportQuery::whereHas('user', function($q) use ($dept) {
                $q->where('department_id', $dept->id);
            })->count();
            
            return $dept;
        });

        return view('support.index', compact(
            'overAWeekOld',
            'overADayOld',
            'updatedToday',
            'noActionNeeded',
            'closedTickets',
            'staleCount',
            'departments'
        ));
    }

    /**
     * Store a newly created support query.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportQuery::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        // Insert first message in the thread
        SupportQueryMessage::create([
            'support_query_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        // Create notification alert for Super Admins
        $admins = \App\Models\User::where('role', 'super_admin')->get();
        if ($admins->count() > 0) {
            $notification = \App\Models\Notification::create([
                'title' => 'Support Query #' . $ticket->id . ': ' . $validated['subject'],
                'message' => Str::limit($validated['message'], 100),
                'sender_id' => Auth::id()
            ]);
            $notification->recipients()->attach($admins->pluck('id'));
        }

        return redirect()->route('support.index')
            ->with('success', 'Your query has been submitted successfully to the Super Admin.');
    }

    /**
     * Display support query details.
     */
    public function show(SupportQuery $query)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $query->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $query->load(['user.department', 'user.designation']);
        
        // Fetch messages latest first (most recent reply at the top)
        $messages = $query->messages()
            ->with(['user.department', 'user.designation'])
            ->reorder()
            ->latest()
            ->get();

        return view('support.show', compact('query', 'messages'));
    }

    /**
     * Update query status or add admin notes/reply.
     */
    public function reply(Request $request, SupportQuery $query)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin() && $query->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,read,replied,solved'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $status = $validated['status'];
        // Auto update status to Replied if there are admin notes and it was pending/read
        if ($user->isSuperAdmin() && !empty($validated['admin_notes']) && ($status === 'pending' || $status === 'read')) {
            $status = 'replied';
        }

        $query->update([
            'status' => $status,
            'admin_notes' => $validated['admin_notes'] ?? $query->admin_notes,
        ]);

        // Also add the reply as a message to the thread!
        if (!empty($validated['admin_notes'])) {
            SupportQueryMessage::create([
                'support_query_id' => $query->id,
                'user_id' => Auth::id(),
                'message' => $validated['admin_notes']
            ]);
        }

        // Trigger notifications
        if ($user->isSuperAdmin()) {
            // Notify the ticket owner
            $notification = \App\Models\Notification::create([
                'title' => 'Replied Ticket #' . $query->id . ': ' . $query->subject,
                'message' => 'Your support ticket status has been updated to: ' . ucfirst($status),
                'sender_id' => Auth::id()
            ]);
            $notification->recipients()->attach($query->user_id);
        } else {
            // Notify Admins
            $admins = \App\Models\User::where('role', 'super_admin')->get();
            if ($admins->count() > 0) {
                $notification = \App\Models\Notification::create([
                    'title' => 'Status Updated Ticket #' . $query->id . ': ' . $query->subject,
                    'message' => 'Ticket status updated by user to: ' . ucfirst($status),
                    'sender_id' => Auth::id()
                ]);
                $notification->recipients()->attach($admins->pluck('id'));
            }
        }

        return redirect()->route('support.show', $query->id)
            ->with('success', 'Support query status updated successfully.');
    }

    /**
     * Post a reply to the ticket discussion.
     */
    public function postMessage(Request $request, SupportQuery $query)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $query->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        SupportQueryMessage::create([
            'support_query_id' => $query->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // Automatically update ticket status
        $status = $query->status;
        if ($user->isSuperAdmin()) {
            $status = 'replied';
            
            // Sync status to parent table if admin writes reply
            $query->update(['admin_notes' => $validated['message']]);
        } else {
            $status = 'pending';
        }
        $query->update(['status' => $status]);

        // Trigger notifications
        if ($user->isSuperAdmin()) {
            $notification = \App\Models\Notification::create([
                'title' => 'Replied Ticket #' . $query->id . ': ' . $query->subject,
                'message' => 'New reply from Super Admin: ' . Str::limit($validated['message'], 70),
                'sender_id' => $user->id
            ]);
            $notification->recipients()->attach($query->user_id);
        } else {
            $admins = \App\Models\User::where('role', 'super_admin')->get();
            if ($admins->count() > 0) {
                $notification = \App\Models\Notification::create([
                    'title' => 'Reply Ticket #' . $query->id . ': ' . $query->subject,
                    'message' => 'New reply from ' . $user->name . ': ' . Str::limit($validated['message'], 70),
                    'sender_id' => $user->id
                ]);
                $notification->recipients()->attach($admins->pluck('id'));
            }
        }

        return redirect()->route('support.show', $query->id)
            ->with('success', 'Your reply has been posted successfully to this ticket thread.');
    }

    /**
     * Bulk close stale support tickets.
     */
    public function closeStale()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        SupportQuery::where('status', '!=', 'solved')
            ->where('created_at', '<', now()->subDays(7))
            ->update([
                'status' => 'solved',
                'admin_notes' => 'Ticket closed automatically due to inactivity (stale ticket closure).'
            ]);

        return redirect()->route('support.index')
            ->with('success', 'All stale tickets older than 7 days have been successfully closed.');
    }

    /**
     * Bulk close selected support tickets.
     */
    public function bulkClose(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => ['required', 'array'],
            'ticket_ids.*' => ['integer', 'exists:support_queries,id']
        ]);

        SupportQuery::whereIn('id', $validated['ticket_ids'])->update([
            'status' => 'solved',
            'admin_notes' => 'Ticket closed in bulk by administrator.'
        ]);

        return redirect()->route('support.index')
            ->with('success', 'Selected tickets have been successfully closed.');
    }
}
