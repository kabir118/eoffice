<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications list.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'active');
        
        if ($user->isSuperAdmin()) {
            // Super Admin sees ALL broadcasted notifications
            if ($tab === 'trash') {
                $generalNotifs = Notification::onlyTrashed()
                    ->where('title', 'NOT LIKE', 'Support Query%')
                    ->where('title', 'NOT LIKE', 'Replied Ticket%')
                    ->where('title', 'NOT LIKE', 'Reply Ticket%')
                    ->where('title', 'NOT LIKE', 'Status Updated Ticket%')
                    ->where('title', 'NOT LIKE', 'File Action Required%')
                    ->with('sender.department')
                    ->latest('deleted_at')
                    ->get();
            } else {
                $generalNotifs = Notification::where('title', 'NOT LIKE', 'Support Query%')
                    ->where('title', 'NOT LIKE', 'Replied Ticket%')
                    ->where('title', 'NOT LIKE', 'Reply Ticket%')
                    ->where('title', 'NOT LIKE', 'Status Updated Ticket%')
                    ->where('title', 'NOT LIKE', 'File Action Required%')
                    ->with('sender.department')
                    ->latest('created_at')
                    ->get();
            }
        } else {
            // Regular user sees notifications assigned to them
            if ($tab === 'trash') {
                $generalNotifs = $user->notifications()
                    ->onlyTrashed()
                    ->where('title', 'NOT LIKE', 'Support Query%')
                    ->where('title', 'NOT LIKE', 'Replied Ticket%')
                    ->where('title', 'NOT LIKE', 'Reply Ticket%')
                    ->where('title', 'NOT LIKE', 'Status Updated Ticket%')
                    ->where('title', 'NOT LIKE', 'File Action Required%')
                    ->with('sender.department')
                    ->latest('deleted_at')
                    ->get();
            } else {
                $generalNotifs = $user->notifications()
                    ->where('title', 'NOT LIKE', 'Support Query%')
                    ->where('title', 'NOT LIKE', 'Replied Ticket%')
                    ->where('title', 'NOT LIKE', 'Reply Ticket%')
                    ->where('title', 'NOT LIKE', 'Status Updated Ticket%')
                    ->where('title', 'NOT LIKE', 'File Action Required%')
                    ->with('sender.department')
                    ->latest('created_at')
                    ->get();
            }
        }

        // Map to e-notifications feed
        $feed = collect();

        foreach ($generalNotifs as $notif) {
            $isRead = false;
            if (isset($notif->pivot) && !is_null($notif->pivot->read_at)) {
                $isRead = true;
            }

            // Extract document reference if present
            $documentUrl = null;
            $document = null;
            if (preg_match('/(?:File Action Required|Copied for Info|File)\s*:?\s*([A-Z0-9\-]+)/i', $notif->title, $m)) {
                $fileNumber = trim($m[1]);
                $document = \App\Models\Document::where('file_number', $fileNumber)->first();
            }

            if (!$document && preg_match('/file\s+"([^"]+)"/i', $notif->message, $m)) {
                $titlePart = trim($m[1]);
                $document = \App\Models\Document::where('title', 'like', $titlePart . '%')->first();
            }

            if ($document) {
                $documentUrl = route('documents.show', $document->id);
            }

            $feed->push((object)[
                'type' => $documentUrl ? 'file_assignment' : 'general',
                'id' => $notif->id,
                'title' => $notif->title,
                'message' => $notif->message,
                'sender_name' => $notif->sender ? $notif->sender->name : 'System',
                'department_code' => ($notif->sender && $notif->sender->department) ? $notif->sender->department->code : 'Admin',
                'date' => $notif->created_at,
                'deleted_at' => $notif->deleted_at,
                'is_read' => $isRead,
                'document_url' => $documentUrl,
                'link' => $documentUrl ?? route('notifications.read', $notif->id)
            ]);
        }

        // Fetch count of trashed notifications for badge
        if ($user->isSuperAdmin()) {
            $trashedCount = Notification::onlyTrashed()->count();
        } else {
            $trashedCount = $user->notifications()->onlyTrashed()->count();
        }

        // Fetch all departments for notification targeting
        $departments = \App\Models\Department::all();

        return view('notifications.index', compact('feed', 'departments', 'tab', 'trashedCount'));
    }

    /**
     * Store and broadcast a new notification to chosen departments.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->canComposeNotification()) {
            abort(403, 'Unauthorized access. Only Registrar Office personnel (Registrar, Deputy Registrar, Assistant Registrar) are authorized to compose e-notifications.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'departments' => ['required', 'array'],
            'departments.*' => ['exists:departments,id']
        ]);

        $notification = Notification::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'sender_id' => Auth::id()
        ]);

        // Fetch all users belonging to selected departments
        $userIds = User::whereIn('department_id', $validated['departments'])->pluck('id');

        // Attach recipients via pivot table
        $notification->recipients()->attach($userIds);

        return redirect()->route('notifications.index')
            ->with('success', 'Notification broadcasted successfully to ' . count($validated['departments']) . ' department(s) (' . $userIds->count() . ' user(s) notified).');
    }

    /**
     * Mark a single notification as read and redirect to target entity (File, Ticket, or Notification Center).
     */
    public function read($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $user->notifications()->updateExistingPivot($notification->id, [
            'read_at' => now()
        ]);

        // 1. File Notifications -> Direct redirect to Document Show Page
        if (preg_match('/(?:File Action Required|Copied for Info|File)\s*:?\s*([A-Z0-9\-]+)/i', $notification->title, $matches)) {
            $fileNumber = trim($matches[1]);
            $doc = \App\Models\Document::where('file_number', $fileNumber)->first();
            if ($doc) {
                return redirect()->route('documents.show', $doc->id);
            }
        }

        // Check if message or title has file reference
        if (str_contains($notification->title, 'File Action Required') || str_contains($notification->title, 'File')) {
            $doc = \App\Models\Document::where('current_holder_id', $user->id)
                ->whereNotIn('status', ['draft', 'completed'])
                ->latest('updated_at')
                ->first();
            if ($doc) {
                return redirect()->route('documents.show', $doc->id);
            }
        }

        // 2. Support Ticket Notifications -> Direct redirect to Support Ticket
        if (preg_match('/Ticket #(\d+)/i', $notification->title, $matches)) {
            $ticketId = $matches[1];
            return redirect()->route('support.show', $ticketId);
        }

        if (str_starts_with($notification->title, 'Support Query') || str_starts_with($notification->title, 'Replied') || str_starts_with($notification->title, 'Reply') || str_starts_with($notification->title, 'Status Updated')) {
            return redirect()->route('support.index');
        }

        // 3. General e-Notification -> Open Notification Center
        return redirect()->route('notifications.index', ['view' => $notification->id])
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll()
    {
        $user = Auth::user();
        
        $unread = $user->unreadNotifications()->get();
        foreach ($unread as $notification) {
            $user->notifications()->updateExistingPivot($notification->id, [
                'read_at' => now()
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Soft delete notification (move to trash).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->canComposeNotification()) {
            abort(403, 'Unauthorized action.');
        }

        $notification = Notification::findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification moved to Trash.');
    }

    /**
     * Restore soft deleted notification from trash.
     */
    public function restore($id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->canComposeNotification()) {
            abort(403, 'Unauthorized action.');
        }

        $notification = Notification::onlyTrashed()->findOrFail($id);
        $notification->restore();

        return redirect()->back()->with('success', 'Notification restored successfully.');
    }

    /**
     * Permanently delete notification from database.
     */
    public function forceDelete($id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can permanently delete notifications.');
        }

        $notification = Notification::onlyTrashed()->findOrFail($id);
        $notification->forceDelete();

        return redirect()->back()->with('success', 'Notification permanently deleted.');
    }
}
