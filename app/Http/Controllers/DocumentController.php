<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTrack;
use App\Models\Department;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'inbox');
        $search = $request->query('search');
        $filter = $request->query('filter');

        if ($filter === 'completed' || $filter === 'closed') {
            $tab = 'completed';
        } elseif ($filter === 'open') {
            $tab = 'open';
        }

        $query = Document::with(['creator.designation', 'currentHolder.designation', 'currentDepartment', 'tracks.sender.designation', 'tracks.sender.department']);

        // Apply runtime priority or status filter if selected
        if (!empty($filter)) {
            if ($filter === 'pending') {
                $query->where('status', 'pending');
            } elseif ($filter === 'completed') {
                $query->where('status', 'completed');
            } elseif (in_array($filter, ['normal', 'urgent', 'most_urgent'])) {
                $query->where('priority', $filter);
            }
        }

        // If user is Registrar, filter out files marked confidential from Registrar unless Registrar is directly involved
        if ($user->isRegistrar()) {
            $query->where(function($q) use ($user) {
                $q->where('is_available_to_registrar', true)
                  ->orWhere('creator_id', $user->id)
                  ->orWhere('current_holder_id', $user->id)
                  ->orWhereHas('tracks', function($t) use ($user) {
                      $t->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                  });
            });
        }

        // If search is active, perform a global search across all accessible documents
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('file_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            // Restrict search by user's access rights
            if (!$user->canViewAllFiles()) {
                $query->where(function($q) use ($user) {
                    $q->where('creator_id', $user->id)
                      ->orWhere('current_holder_id', $user->id)
                      ->orWhereHas('tracks', function($t) use ($user) {
                          $t->where('sender_id', $user->id)
                            ->orWhere('receiver_id', $user->id);
                      });
                });
            }

            $documents = $query->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
            return view('documents.index', compact('documents', 'tab', 'search', 'filter'));
        }

        if ($tab === 'all') {
            if (!$user->canViewAllFiles()) {
                abort(403, 'Unauthorized access.');
            }
            $documents = $query->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'outbox') {
            // Files created by this user
            $documents = $query->where('creator_id', $user->id)
                ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'forwarded') {
            // Files worked on (forwarded) by this user but created by someone else
            $documents = $query->where('creator_id', '!=', $user->id)
                ->whereHas('tracks', function($t) use ($user) {
                    $t->where('sender_id', $user->id);
                })
                ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'completed') {
            // Files marked completed
            $documents = $query->where('status', 'completed');
            if (!$user->canViewAllFiles()) {
                $documents = $documents->where(function($q) use ($user) {
                    $q->where('creator_id', $user->id)
                      ->orWhere('current_holder_id', $user->id)
                      ->orWhereHas('tracks', function($t) use ($user) {
                          $t->where('sender_id', $user->id)
                            ->orWhere('receiver_id', $user->id);
                      });
                });
            }
            $documents = $documents->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'open') {
            // Open Files: Active files where the user is directly involved (created, held, or forwarded)
            $documents = $query->whereNotIn('status', ['draft', 'completed'])
                ->where(function($q) use ($user) {
                    $q->where('creator_id', $user->id)
                      ->orWhere('current_holder_id', $user->id)
                      ->orWhereHas('tracks', function($t) use ($user) {
                          $t->where('sender_id', $user->id)
                            ->orWhere('receiver_id', $user->id);
                      });
                })
                ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } elseif ($tab === 'trash') {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Unauthorized access.');
            }
            $documents = Document::onlyTrashed()
                ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        } else {
            // Inbox: Files waiting for action in the user's inbox
            $documents = $query->where('current_holder_id', $user->id)
                ->whereNotIn('status', ['draft', 'completed'])
                ->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            // Mark tracking logs as received/read when visiting inbox
            DocumentTrack::where('receiver_id', $user->id)
                ->whereNull('received_at')
                ->update(['received_at' => now()]);
        }

        return view('documents.index', compact('documents', 'tab', 'search', 'filter'));
    }

    /**
     * Display all files created by the authenticated user.
     */
    public function myFiles(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search');
        $filter = $request->query('filter');
        $status = $request->query('status', 'open');

        // Counts for sub-tabs
        $openCount = Document::where('creator_id', $user->id)
            ->whereNotIn('status', ['draft', 'completed'])
            ->count();

        $closedCount = Document::where('creator_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $query = Document::where('creator_id', $user->id);

        if ($status === 'completed') {
            $query->where('status', 'completed');
        } else {
            $query->whereNotIn('status', ['draft', 'completed']);
        }

        // Apply runtime priority or status filter if selected
        if (!empty($filter)) {
            if ($filter === 'pending') {
                $query->where('status', 'pending');
            } elseif ($filter === 'completed') {
                $query->where('status', 'completed');
            } elseif (in_array($filter, ['normal', 'urgent', 'most_urgent'])) {
                $query->where('priority', $filter);
            }
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('file_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('documents.my_files', compact('documents', 'search', 'filter', 'status', 'openCount', 'closedCount'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        $departments = Department::all();
        $users = User::with(['designation', 'department'])->where('id', '!=', Auth::id())->get();
        return view('documents.create', compact('departments', 'users'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:normal,urgent,most_urgent'],
            'urgency_remarks' => ['required_if:priority,urgent,most_urgent', 'nullable', 'string'],
            'forward_to' => ['required', 'exists:users,id'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar,txt', 'max:20480'], // 20MB limit
            'is_available_to_registrar' => ['nullable', 'boolean'],
        ]);

        $receiver = User::findOrFail($validated['forward_to']);

        // Handle attachment upload with date prefix (e.g. 30072026_filename.pdf)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $origName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $datePrefix = date('dmY');
            $attachmentPath = $file->storeAs('documents', $datePrefix . '_' . $origName);
        }

        // Create the document
        $document = Document::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'urgency_remarks' => $validated['urgency_remarks'] ?? null,
            'status' => 'pending',
            'creator_id' => Auth::id(),
            'current_holder_id' => $receiver->id,
            'current_department_id' => $receiver->department_id,
            'attachment_path' => $attachmentPath,
            'is_available_to_registrar' => $request->has('is_available_to_registrar') ? $request->boolean('is_available_to_registrar') : true,
        ]);

        // Create the initial tracking step
        DocumentTrack::create([
            'document_id' => $document->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver->id,
            'remarks' => 'File initiated and forwarded.',
            'status_at_transfer' => 'pending',
            'received_at' => null
        ]);

        // Create database notification for file assignment
        $notification = \App\Models\Notification::create([
            'title' => 'File Action Required: ' . $document->file_number,
            'message' => 'Subject: "' . $document->title . '". Assigned to you by ' . Auth::user()->name . '.',
            'sender_id' => Auth::id()
        ]);
        $notification->recipients()->attach($receiver->id);

        // Send email alert to recipient
        try {
            \Illuminate\Support\Facades\Mail::to($receiver->email)
                ->send(new \App\Mail\FileForwardedMail($document, 'File initiated and forwarded.', Auth::user()->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending email notification: " . $e->getMessage());
        }

        return redirect()->route('documents.index')
            ->with('success', 'Document created and forwarded to ' . $receiver->name . '. File tracking ID: ' . $document->file_number);
    }

    /**
     * Display the specified document.
     */
    public function show($id)
    {
        $user = Auth::user();
        $document = Document::withTrashed()->findOrFail($id);

        // Security check: only users in the flow or admins can view
        $isCreator = $document->creator_id === $user->id;
        $isHolder = $document->current_holder_id === $user->id;
        $isInTracks = $document->tracks()->where(function($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->exists();

        if ($user->isRegistrar() && !$document->is_available_to_registrar && !$isCreator && !$isHolder && !$isInTracks) {
            abort(403, 'Unauthorized access. This document is marked confidential from the Registrar Office.');
        }

        if (!$isCreator && !$isHolder && !$isInTracks && !$user->canViewAllFiles()) {
            abort(403, 'Unauthorized access to this document.');
        }

        // Mark incoming tracks as read
        $document->tracks()->where('receiver_id', $user->id)
            ->whereNull('received_at')
            ->update(['received_at' => now()]);

        $departments = Department::all();
        $users = User::with(['designation', 'department'])->where('id', '!=', Auth::id())->get();
        $comments = $document->comments()->with(['user.designation', 'user.department'])->get();
        $tracks = $document->tracks()->with(['sender.designation', 'sender.department', 'receiver.department'])->get();

        $unifiedComments = collect();

        foreach ($comments as $comment) {
            $unifiedComments->push((object)[
                'type' => 'comment',
                'user' => $comment->user,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'attachment_path' => $comment->attachment_path,
                'id' => $comment->id
            ]);
        }

        foreach ($tracks as $track) {
            $r = trim($track->remarks ?? '');
            if (!empty($r) && !in_array($r, ['File forwarded.', 'File initiated and forwarded.'])) {
                $unifiedComments->push((object)[
                    'type' => 'track_remarks',
                    'user' => $track->sender,
                    'content' => $track->remarks,
                    'created_at' => $track->created_at,
                    'attachment_path' => $track->attachment_path,
                    'id' => $track->id,
                    'status' => $track->status_at_transfer
                ]);
            }
        }

        $unifiedComments = $unifiedComments->sortByDesc('created_at')->values();

        return view('documents.show', compact('document', 'departments', 'users', 'unifiedComments', 'tracks'));
    }

    /**
     * Print-friendly view of the document with all comments and routing history.
     */
    public function printView(Document $document)
    {
        $document->load(['creator.designation', 'currentHolder.designation', 'currentDepartment']);

        $comments = $document->comments()->with(['user.designation', 'user.department'])->get();
        $tracks = $document->tracks()->with(['sender.designation', 'sender.department', 'receiver.designation', 'receiver.department'])->get();

        $unifiedComments = collect();

        foreach ($comments as $comment) {
            $unifiedComments->push((object)[
                'type' => 'comment',
                'user' => $comment->user,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'attachment_path' => $comment->attachment_path,
                'id' => $comment->id
            ]);
        }

        foreach ($tracks as $track) {
            $r = trim($track->remarks ?? '');
            if (!empty($r) && !in_array($r, ['File forwarded.', 'File initiated and forwarded.'])) {
                $unifiedComments->push((object)[
                    'type' => 'track_remarks',
                    'user' => $track->sender,
                    'content' => $track->remarks,
                    'created_at' => $track->created_at,
                    'attachment_path' => $track->attachment_path,
                    'id' => $track->id,
                    'status' => $track->status_at_transfer
                ]);
            }
        }

        $unifiedComments = $unifiedComments->sortByDesc('created_at')->values();

        return view('documents.print', compact('document', 'unifiedComments', 'tracks'));
    }

    /**
     * Forward the document to another user.
     */
    public function forward(Request $request, Document $document)
    {
        $user = Auth::user();

        if ($document->current_holder_id !== $user->id) {
            return back()->with('error', 'You do not currently hold this file.');
        }

        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:forwarded,approved,rejected'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar,txt', 'max:20480'],
        ]);

        $receiver = User::findOrFail($validated['receiver_id']);

        // Handle attachment upload with date prefix (e.g. 30072026_filename.pdf)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $origName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $datePrefix = date('dmY');
            $attachmentPath = $file->storeAs('tracks', $datePrefix . '_' . $origName);
        }

        // Update document
        $document->update([
            'current_holder_id' => $receiver->id,
            'current_department_id' => $receiver->department_id,
            'status' => $validated['status']
        ]);

        // Add track step
        DocumentTrack::create([
            'document_id' => $document->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'remarks' => $validated['remarks'] ?? 'File forwarded.',
            'status_at_transfer' => $validated['status'],
            'attachment_path' => $attachmentPath
        ]);

        // Create database notification for file assignment
        $notification = \App\Models\Notification::create([
            'title' => 'File Action Required: ' . $document->file_number,
            'message' => 'Subject: "' . $document->title . '". Assigned to you by ' . $user->name . '.',
            'sender_id' => $user->id
        ]);
        $notification->recipients()->attach($receiver->id);

        // Send email alert to recipient
        try {
            \Illuminate\Support\Facades\Mail::to($receiver->email)
                ->send(new \App\Mail\FileForwardedMail($document, $validated['remarks'] ?? 'File forwarded.', $user->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending email notification: " . $e->getMessage());
        }

        return redirect()->route('documents.index')
            ->with('success', 'Document forwarded to ' . $receiver->name . ' successfully.');
    }

    /**
     * Update the status of the file (approve, reject, or complete).
     */
    public function updateStatus(Request $request, Document $document)
    {
        $user = Auth::user();

        if ($document->current_holder_id !== $user->id) {
            return back()->with('error', 'You do not currently hold this file.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,completed'],
            'remarks' => ['nullable', 'string'],
        ]);

        // Update document
        $document->update([
            'status' => $validated['status'],
        ]);

        // If completed, the holder remains the current one but the track finishes
        // We log a final self-track step for audit trail
        DocumentTrack::create([
            'document_id' => $document->id,
            'sender_id' => $user->id,
            'receiver_id' => $document->creator_id, // Route final notice back to creator
            'remarks' => $validated['remarks'] ?? 'File status updated to: ' . $validated['status'],
            'status_at_transfer' => $validated['status'],
            'received_at' => now()
        ]);

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document status updated to ' . ucfirst($validated['status']) . ' successfully.');
    }

    /**
     * Add a comment to the document.
     */
    public function comment(Request $request, Document $document)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar,txt', 'max:20480'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $origName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
            $datePrefix = date('dmY');
            $attachmentPath = $file->storeAs('comments', $datePrefix . '_' . $origName);
        }

        Comment::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'attachment_path' => $attachmentPath
        ]);

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Securely download document attachment.
     */
    public function download(Document $document)
    {
        return $this->downloadAttachment('document', $document->id);
    }

    /**
     * Securely download attachments from documents, comments, or tracks.
     */
    public function downloadAttachment($type, $id)
    {
        $user = Auth::user();

        // 1. Get the model
        if ($type === 'document') {
            $model = Document::findOrFail($id);
            $document = $model;
        } elseif ($type === 'comment') {
            $model = Comment::findOrFail($id);
            $document = $model->document;
        } elseif ($type === 'track') {
            $model = DocumentTrack::findOrFail($id);
            $document = $model->document;
        } else {
            abort(400, 'Invalid attachment type.');
        }

        // 2. Perform document security check
        $isCreator = $document->creator_id === $user->id;
        $isHolder = $document->current_holder_id === $user->id;
        $isInTracks = $document->tracks()->where(function($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->exists();

        if (!$isCreator && !$isHolder && !$isInTracks && !$user->canViewAllFiles()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$model->attachment_path || !Storage::exists($model->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        $downloadFilename = basename($model->attachment_path);

        if (request()->query('inline') === 'true') {
            $path = Storage::path($model->attachment_path);
            $mime = Storage::mimeType($model->attachment_path);
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $downloadFilename . '"'
            ]);
        }

        return Storage::download($model->attachment_path, $downloadFilename);
    }

    /**
     * Display all files the logged-in user has commented on, with search.
     */
    public function myRemarks(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $filter = $request->input('filter');

        // Get distinct document IDs this user has commented on
        $documentIds = Comment::where('user_id', $user->id)
            ->pluck('document_id')
            ->unique();

        // Query those documents with optional search and filter
        $documentsQuery = Document::whereIn('id', $documentIds)
            ->with(['creator.designation', 'currentHolder.designation', 'currentDepartment']);

        if (!empty($filter)) {
            if ($filter === 'pending') {
                $documentsQuery->where('status', 'pending');
            } elseif ($filter === 'completed') {
                $documentsQuery->where('status', 'completed');
            } elseif (in_array($filter, ['normal', 'urgent', 'most_urgent'])) {
                $documentsQuery->where('priority', $filter);
            }
        }

        if ($search) {
            $documentsQuery->where(function($q) use ($search) {
                $q->where('file_number', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $documents = $documentsQuery->orderByRaw("CASE priority WHEN 'most_urgent' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('documents.my_remarks', compact('documents', 'user', 'search', 'filter'));
    }

    /**
     * Return JSON of the logged-in user's comments on a specific document.
     */
    public function myComments(Document $document)
    {
        $user = Auth::user();

        $comments = Comment::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'content' => $c->content,
                    'date' => $c->created_at->format('M d, Y h:i A'),
                    'ago' => $c->created_at->diffForHumans(),
                    'attachment' => $c->attachment_path ? basename($c->attachment_path) : null,
                    'download_url' => $c->attachment_path ? route('attachments.download', ['type' => 'comment', 'id' => $c->id]) : null,
                ];
            });

        return response()->json(['comments' => $comments]);
    }

    /**
     * Bulk delete documents (Super Admin Only).
     */
    public function bulkDelete(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can bulk delete files.');
        }

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer']
        ]);

        $count = count($validated['ids']);
        $isForce = $request->boolean('force', false);

        if ($isForce) {
            Document::onlyTrashed()->whereIn('id', $validated['ids'])->forceDelete();
            return back()->with('success', "{$count} file(s) permanently deleted successfully.");
        } else {
            Document::whereIn('id', $validated['ids'])->delete();
            return back()->with('success', "{$count} file(s) moved to Trash Bin successfully.");
        }
    }

    /**
     * Move document to Trash Bin (Soft Delete - Super Admin Only).
     */
    public function destroy(Document $document)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can move files to Trash Bin.');
        }

        $document->delete();

        return redirect()->route('documents.index', ['tab' => 'trash'])
            ->with('success', 'File "' . $document->file_number . '" moved to Trash Bin successfully.');
    }

    /**
     * Restore document from Trash Bin (Super Admin Only).
     */
    public function restore($id)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can restore files.');
        }

        $document = Document::onlyTrashed()->findOrFail($id);
        $document->restore();

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'File "' . $document->file_number . '" restored successfully from Trash Bin.');
    }

    /**
     * Permanently delete document from Trash Bin (Super Admin Only).
     */
    public function forceDelete($id)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can permanently delete files.');
        }

        $document = Document::onlyTrashed()->findOrFail($id);
        $fileNumber = $document->file_number;

        // Force delete record
        $document->forceDelete();

        return redirect()->route('documents.index', ['tab' => 'trash'])
            ->with('success', 'File "' . $fileNumber . '" permanently deleted from the database.');
    }
}
