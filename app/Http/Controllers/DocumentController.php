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
use Illuminate\Support\Str;

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

        $creatorId = $request->query('creator_id');

        if (!$request->has('tab')) {
            if ($filter === 'completed' || $filter === 'closed') {
                $tab = 'completed';
            } elseif ($filter === 'open') {
                $tab = 'open';
            }
        }

        $query = Document::with(['creator.designation', 'currentHolder.designation', 'currentDepartment', 'tracks.sender.designation', 'tracks.sender.department']);

        // Apply creator filter if selected
        if (!empty($creatorId)) {
            $query->where('creator_id', $creatorId);
        }

        // Apply runtime priority or status filter if selected
        if (!empty($filter)) {
            if ($filter === 'pending') {
                $query->where('status', 'pending');
            } elseif ($filter === 'forwarded') {
                $query->where('status', 'forwarded');
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
        } elseif ($tab === 'cc') {
            // Copied to Me (CC): Files CC'd to this user for information ONLY.
            // Excludes files where user is current holder (Marked to Me), creator, or completed files (Closed Files)
            $documents = $query->whereHas('ccUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('current_holder_id', '!=', $user->id)
            ->where('creator_id', '!=', $user->id)
            ->whereNotIn('status', ['draft', 'completed'])
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

        $selectedCreator = !empty($creatorId) ? User::with('designation')->find($creatorId) : null;

        return view('documents.index', compact('documents', 'tab', 'search', 'filter', 'creatorId', 'selectedCreator'));
    }

    /**
     * AJAX endpoint to search officers by name or designation in real-time.
     */
    public function searchUsers(Request $request)
    {
        $q = trim($request->query('q', ''));
        if (empty($q)) {
            return response()->json([]);
        }

        $users = User::with(['designation', 'department'])
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhereHas('designation', function($d) use ($q) {
                          $d->where('title', 'like', "%{$q}%");
                      })
                      ->orWhereHas('department', function($dep) use ($q) {
                          $dep->where('name', 'like', "%{$q}%")
                             ->orWhere('code', 'like', "%{$q}%");
                      });
            })
            ->limit(10)
            ->get();

        $data = $users->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'designation' => $u->designation ? $u->designation->title : '',
                'department' => $u->department ? $u->department->code : '',
            ];
        });

        return response()->json($data);
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
     * Resolve CC user IDs from individual selection and group presets.
     */
    protected function resolveCcUserIds(array $ccUserIds = [], array $ccGroups = []): array
    {
        $resolvedIds = collect($ccUserIds);

        foreach ($ccGroups as $group) {
            if ($group === 'ps_vc') {
                $ids = User::whereHas('designation', function($q) {
                    $q->where(function($sub) {
                        $sub->where('title', 'like', '%P.S%')
                            ->orWhere('title', 'like', '%PS%')
                            ->orWhere('title', 'like', '%P.A%')
                            ->orWhere('title', 'like', '%PA%')
                            ->orWhere('title', 'like', '%Secretary%')
                            ->orWhere('title', 'like', '%Assistant%');
                    })
                    ->where('title', '!=', 'Vice Chancellor')
                    ->where('title', 'not like', 'Vice Chancellor%')
                    ->where('title', '!=', 'VC');
                })->pluck('id');
                $resolvedIds = $resolvedIds->merge($ids);
            } elseif ($group === 'all_deans') {
                $ids = User::whereHas('designation', function($q) {
                    $q->where('title', 'like', '%Dean%')
                      ->orWhere('title', 'like', '%Director%')
                      ->orWhere('title', 'like', '%Chairman%')
                      ->orWhere('title', 'like', '%Chairperson%');
                })->pluck('id');
                $resolvedIds = $resolvedIds->merge($ids);
            } elseif ($group === 'sectional_heads') {
                $ids = User::whereHas('designation', function($q) {
                    $q->where('title', 'like', '%Head%')
                      ->orWhere('title', 'like', '%Incharge%')
                      ->orWhere('title', 'like', '%Sectional%')
                      ->orWhere('title', 'like', '%Manager%');
                })->pluck('id');
                $resolvedIds = $resolvedIds->merge($ids);
            }
        }

        return $resolvedIds->unique()->reject(fn($id) => $id == Auth::id())->values()->toArray();
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        $departments = Department::all();
        $users = User::with(['designation', 'department'])->where('id', '!=', Auth::id())->get();
        $allUsers = User::with(['designation', 'department'])->where('id', '!=', Auth::id())->get();
        return view('documents.create', compact('departments', 'users', 'allUsers'));
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
            'cc_users' => ['nullable', 'array'],
            'cc_users.*' => ['exists:users,id'],
            'cc_groups' => ['nullable', 'array'],
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

        // Resolve and attach CC recipients
        $ccUserIds = $this->resolveCcUserIds(
            $request->input('cc_users', []),
            $request->input('cc_groups', [])
        );

        if (!empty($ccUserIds)) {
            $document->ccUsers()->sync($ccUserIds);

            // Send notification alerts to CC recipients
            $ccNotif = \App\Models\Notification::create([
                'title' => 'Copied for Info: ' . $document->file_number,
                'message' => 'You received a copy of file "' . \Illuminate\Support\Str::limit($document->title, 50) . '" for information.',
                'sender_id' => Auth::id()
            ]);
            $ccNotif->recipients()->attach($ccUserIds);
        }

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

        // Security check: only users in the flow, CC recipients, or admins can view
        $isCreator = $document->creator_id === $user->id;
        $isHolder = $document->current_holder_id === $user->id;
        $isInTracks = $document->tracks()->where(function($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->exists();
        $isCcRecipient = $document->ccUsers()->where('user_id', $user->id)->exists();
        $isCcOnly = $isCcRecipient && !$isHolder && !$isCreator && !$user->isSuperAdmin();

        // Comments visibility rule:
        // ONLY the current holder of the file and Super Admin can view comments.
        // If an employee initiates a file (or handled it earlier), the comments are NOT visible
        // to them or anyone else unless the file is currently holding by / assigned to them.
        $canViewComments = ($isHolder || $user->isSuperAdmin());
        $isTrackingOnly = !$canViewComments;

        // Legacy compatibility flags for views
        $isRegistrarViewOnly = $user->isRegistrar() && !$canViewComments;
        $isCreatorViewOnly = $isCreator && !$canViewComments;

        if ($user->isRegistrar() && !$document->is_available_to_registrar && !$isCreator && !$isHolder && !$isInTracks && !$isCcRecipient) {
            abort(403, 'Unauthorized access. This document is marked confidential from the Registrar Office.');
        }

        if (!$isCreator && !$isHolder && !$isInTracks && !$isCcRecipient && !$user->canViewAllFiles()) {
            abort(403, 'Unauthorized access to this document.');
        }

        // Mark incoming tracks as read
        $document->tracks()->where('receiver_id', $user->id)
            ->whereNull('received_at')
            ->update(['received_at' => now()]);

        $departments = Department::all();
        $users = User::with(['designation', 'department'])->where('id', '!=', Auth::id())->get();
        $tracks = $document->tracks()->with(['sender.designation', 'sender.department', 'receiver.department'])->get();
        $ccUsers = $document->ccUsers()->with(['designation', 'department'])->get();

        $unifiedComments = collect();
        $archivedComments = collect();

        if ($canViewComments) {
            $comments = $document->comments()->with(['user.designation', 'user.department'])->get();

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
                    $alreadyInComments = $comments->contains(function ($c) use ($track, $r) {
                        return $c->user_id == $track->sender_id && trim($c->content) == $r;
                    });
                    if (!$alreadyInComments) {
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
            }

            $unifiedComments = $unifiedComments->sortByDesc('created_at')->values();

            if ($user->isSuperAdmin()) {
                $archivedComments = $document->comments()->onlyTrashed()->with(['user.designation', 'user.department'])->latest('deleted_at')->get();
            }
        }

        // Always fetch this user's OWN comments on this document (shown in tracking mode)
        $myOwnComments = Comment::where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($c) {
                return (object)[
                    'content'        => $c->content,
                    'created_at'     => $c->created_at,
                    'attachment_path'=> $c->attachment_path,
                    'id'             => $c->id,
                ];
            });

        return view('documents.show', compact('document', 'departments', 'users', 'unifiedComments', 'tracks', 'ccUsers', 'isCcOnly', 'archivedComments', 'isRegistrarViewOnly', 'isCreatorViewOnly', 'canViewComments', 'isTrackingOnly', 'myOwnComments'));
    }

    /**
     * Print-friendly view of the document with all comments and routing history.
     */
    public function printView(Document $document)
    {
        $user = Auth::user();

        // Security check
        $isCreator = $document->creator_id === $user->id;
        $isHolder = $document->current_holder_id === $user->id;
        $isInTracks = $document->tracks()->where(function($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->exists();
        $isCcRecipient = $document->ccUsers()->where('user_id', $user->id)->exists();

        if (!$isHolder && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Printing is only available to the current holder of the file.');
        }

        $canViewComments = true;

        $document->load(['creator.designation', 'currentHolder.designation', 'currentDepartment']);

        $tracks = $document->tracks()->with(['sender.designation', 'sender.department', 'receiver.designation', 'receiver.department'])->get();

        $unifiedComments = collect();

        if ($canViewComments) {
            $comments = $document->comments()->with(['user.designation', 'user.department'])->get();

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
                    $alreadyInComments = $comments->contains(function ($c) use ($track, $r) {
                        return $c->user_id == $track->sender_id && trim($c->content) == $r;
                    });
                    if (!$alreadyInComments) {
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
            }

            $unifiedComments = $unifiedComments->sortByDesc('created_at')->values();
        }

        return view('documents.print', compact('document', 'unifiedComments', 'tracks', 'canViewComments'));
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

        // Resolve and attach CC recipients when forwarding
        $ccUserIds = $this->resolveCcUserIds(
            $request->input('cc_users', []),
            $request->input('cc_groups', [])
        );

        if (!empty($ccUserIds)) {
            $document->ccUsers()->syncWithoutDetaching($ccUserIds);

            // Send notification alerts to new CC recipients
            $ccNotif = \App\Models\Notification::create([
                'title' => 'Copied for Info: ' . $document->file_number,
                'message' => 'You received a copy of file "' . \Illuminate\Support\Str::limit($document->title, 50) . '" for information.',
                'sender_id' => Auth::id()
            ]);
            $ccNotif->recipients()->attach($ccUserIds);
        }

        // If remarks were entered, also log into Comment table for discussion history & user remarks log
        $remarksText = !empty(trim($validated['remarks'] ?? '')) ? trim($validated['remarks']) : null;
        if ($remarksText) {
            Comment::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'content' => $remarksText,
                'attachment_path' => $attachmentPath
            ]);
        }

        // Add track step
        DocumentTrack::create([
            'document_id' => $document->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'remarks' => $remarksText ?? 'File forwarded.',
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

        $remarksText = !empty(trim($validated['remarks'] ?? '')) ? trim($validated['remarks']) : null;
        if ($remarksText) {
            Comment::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'content' => $remarksText,
                'attachment_path' => null
            ]);
        }

        // If completed, the holder remains the current one but the track finishes
        // We log a final self-track step for audit trail
        DocumentTrack::create([
            'document_id' => $document->id,
            'sender_id' => $user->id,
            'receiver_id' => $document->creator_id, // Route final notice back to creator
            'remarks' => $remarksText ?? 'File status updated to: ' . $validated['status'],
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

        // Only the active file holder or Super Admin can add comments
        if ($document->current_holder_id !== $user->id && !$user->isSuperAdmin()) {
            return back()->with('error', 'Unauthorized. Only the current holder of this file can add comments.');
        }

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
     * Archive / Soft-delete a comment (Super Admin only).
     */
    public function deleteComment(Request $request, Document $document, $id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can archive remarks.');
        }

        $type = $request->input('type', 'comment');

        if ($type === 'comment') {
            $comment = Comment::where('document_id', $document->id)->findOrFail($id);
            $comment->delete(); // Soft delete (moved to archived)
        } elseif ($type === 'track_remarks') {
            $track = DocumentTrack::where('document_id', $document->id)->findOrFail($id);
            $track->update([
                'remarks' => 'File forwarded.',
                'attachment_path' => null
            ]);
        }

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Remark moved to Archived Comments successfully.');
    }

    /**
     * Restore an archived comment (Super Admin only).
     */
    public function restoreComment(Document $document, $id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can restore remarks.');
        }

        $comment = Comment::onlyTrashed()->where('document_id', $document->id)->findOrFail($id);
        $comment->restore();

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Comment restored to active remarks successfully.');
    }

    /**
     * Permanently delete an archived comment (Super Admin only).
     */
    public function forceDeleteComment(Document $document, $id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can permanently delete remarks.');
        }

        $comment = Comment::onlyTrashed()->where('document_id', $document->id)->findOrFail($id);
        if ($comment->attachment_path && Storage::exists($comment->attachment_path)) {
            Storage::delete($comment->attachment_path);
        }
        $comment->forceDelete();

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Comment permanently deleted from system.');
    }

    /**
     * Bulk Action on comments (archive, restore, force delete) (Super Admin only).
     */
    public function bulkCommentAction(Request $request, Document $document)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can perform bulk actions on comments.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:archive,restore,force_delete'],
            'comment_ids' => ['required', 'array'],
            'comment_ids.*' => ['integer']
        ]);

        $ids = $validated['comment_ids'];
        $action = $validated['action'];

        if ($action === 'archive') {
            $count = Comment::where('document_id', $document->id)->whereIn('id', $ids)->delete();
            return redirect()->route('documents.show', $document->id)
                ->with('success', "{$count} remark(s) moved to Archived Comments successfully.");
        } elseif ($action === 'restore') {
            $count = Comment::onlyTrashed()->where('document_id', $document->id)->whereIn('id', $ids)->restore();
            return redirect()->route('documents.show', $document->id)
                ->with('success', "{$count} archived remark(s) restored successfully.");
        } elseif ($action === 'force_delete') {
            $comments = Comment::onlyTrashed()->where('document_id', $document->id)->whereIn('id', $ids)->get();
            foreach ($comments as $c) {
                if ($c->attachment_path && Storage::exists($c->attachment_path)) {
                    Storage::delete($c->attachment_path);
                }
                $c->forceDelete();
            }
            return redirect()->route('documents.show', $document->id)
                ->with('success', count($comments) . " archived remark(s) permanently deleted.");
        }

        return redirect()->route('documents.show', $document->id);
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
        $isCcRecipient = $document->ccUsers()->where('user_id', $user->id)->exists();

        if ($user->isRegistrar() && !$document->is_available_to_registrar && !$isCreator && !$isHolder && !$isInTracks && !$isCcRecipient) {
            abort(403, 'Unauthorized access. This document is marked confidential from the Registrar Office.');
        }

        if (!$isCreator && !$isHolder && !$isInTracks && !$isCcRecipient && !$user->canViewAllFiles()) {
            abort(403, 'Unauthorized access.');
        }

        if ($type === 'comment' && !$isHolder && !$user->isSuperAdmin() && $model->user_id !== $user->id) {
            abort(403, 'Unauthorized access to comment attachment.');
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

        $creatorId = $request->input('creator_id');
        if (!empty($creatorId)) {
            $documentsQuery->where('creator_id', $creatorId);
        }

        if (!empty($filter)) {
            if ($filter === 'pending') {
                $documentsQuery->where('status', 'pending');
            } elseif ($filter === 'forwarded') {
                $documentsQuery->where('status', 'forwarded');
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

        $selectedCreator = !empty($creatorId) ? User::with('designation')->find($creatorId) : null;

        return view('documents.my_remarks', compact('documents', 'user', 'search', 'filter', 'creatorId', 'selectedCreator'));
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

    /**
     * Display central list of all archived comments across the university (Super Admin only).
     */
    public function archivedCommentsIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $search = trim($request->query('search', ''));

        $query = Comment::onlyTrashed()->with(['document', 'user.designation', 'user.department']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('document', function($d) use ($search) {
                      $d->where('file_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $archivedComments = $query->latest('deleted_at')->paginate(15)->withQueryString();

        return view('documents.archived_comments', compact('archivedComments', 'search'));
    }

    /**
     * Bulk Action for central archived comments (Super Admin only).
     */
    public function bulkArchivedCommentsAll(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:restore,force_delete'],
            'comment_ids' => ['required', 'array'],
            'comment_ids.*' => ['integer']
        ]);

        $ids = $validated['comment_ids'];
        $action = $validated['action'];

        if ($action === 'restore') {
            $count = Comment::onlyTrashed()->whereIn('id', $ids)->restore();
            return redirect()->route('archives.comments')
                ->with('success', "{$count} archived remark(s) restored successfully.");
        } elseif ($action === 'force_delete') {
            $comments = Comment::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($comments as $c) {
                if ($c->attachment_path && Storage::exists($c->attachment_path)) {
                    Storage::delete($c->attachment_path);
                }
                $c->forceDelete();
            }
            return redirect()->route('archives.comments')
                ->with('success', count($comments) . " archived remark(s) permanently deleted.");
        }

        return redirect()->route('archives.comments');
    }
}
