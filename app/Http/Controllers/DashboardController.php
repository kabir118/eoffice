<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search');
        $filter = $request->query('filter');

        // 1. Calculate Statistics for Dashboard
        $myFilesCount = Document::where('creator_id', $user->id)->count();

        $markedToMeCount = Document::where('current_holder_id', $user->id)
            ->whereNotIn('status', ['draft', 'completed'])
            ->count();

        $openFilesCount = $markedToMeCount;

        $closedFilesCount = Document::where('status', 'completed')
            ->where(function($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('current_holder_id', $user->id)
                  ->orWhereHas('tracks', function($t) use ($user) {
                      $t->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
                  });
            })
            ->distinct()
            ->count();

        $commentsCount = \App\Models\Comment::where('user_id', $user->id)->count();

        $urgentCount = Document::where('current_holder_id', $user->id)
            ->whereIn('priority', ['urgent', 'most_urgent'])
            ->whereNotIn('status', ['draft', 'completed'])
            ->count();

        $unreadNotificationsCount = $user->unreadNotifications()->where('title', 'NOT LIKE', 'Support Query%')->count();

        // 2. Fetch Files Requiring User Action (Files Need Your Attention)
        $heldQuery = Document::where('current_holder_id', $user->id)
            ->whereNotIn('status', ['draft', 'completed']);

        if (!empty($search)) {
            $heldQuery->where(function($q) use ($search) {
                $q->where('file_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filter)) {
            if ($filter === 'pending') {
                $heldQuery->where('status', 'pending');
            } elseif ($filter === 'completed') {
                $heldQuery->where('status', 'completed');
            } elseif (in_array($filter, ['normal', 'urgent', 'most_urgent'])) {
                $heldQuery->where('priority', $filter);
            }
        }

        $attentionFiles = $heldQuery
            ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($attentionFiles as $doc) {
            // Determine if user has opened it yet (unread)
            $doc->is_unread = $doc->tracks()
                ->where('receiver_id', $user->id)
                ->whereNull('received_at')
                ->exists();
        }

        return view('dashboard', compact(
            'myFilesCount',
            'openFilesCount',
            'closedFilesCount',
            'markedToMeCount',
            'commentsCount',
            'urgentCount',
            'unreadNotificationsCount',
            'attentionFiles',
            'search',
            'filter'
        ));
    }
}
