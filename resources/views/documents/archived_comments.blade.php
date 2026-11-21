@extends('layouts.app')

@section('title', 'Archived Remarks - USTB e-Office')
@section('page_title', 'Archived Remarks & Comments')

@section('content')
    <div class="dashboard-card" style="margin-bottom: 0;">
        <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-chat-square-quote-fill" style="color: #e74c3c; font-size: 1.15rem;"></i>
                <span style="color: var(--primary-dark);">Central Archived Remarks Repository</span>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('documents.index', ['tab' => 'trash']) }}" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">
                    <i class="bi bi-file-earmark-x"></i> View Archived Files
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div style="margin-bottom: 20px;">
            <form action="{{ route('archives.comments') }}" method="GET" style="display: flex; gap: 10px; max-width: 600px; align-items: center;">
                <div style="position: relative; flex-grow: 1;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" style="padding-left: 40px; font-size: 0.88rem;" placeholder="Search by File Number, User Name, or Remarks text..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                @if(!empty($search))
                    <a href="{{ route('archives.comments') }}" class="btn btn-secondary" title="Clear Search">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>

        @if($archivedComments->count() > 0)
            <!-- Bulk Actions Toolbar -->
            <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <label style="font-size: 0.82rem; font-weight: 600; color: var(--primary-dark); cursor: pointer; display: flex; align-items: center; gap: 8px; user-select: none; margin: 0;">
                    <input type="checkbox" id="select_all_comments" onchange="toggleSelectAll(this)" style="width: 16px; height: 16px; accent-color: #e74c3c;">
                    Select All Remarks
                </label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" onclick="submitBulkAction('restore')" class="btn btn-secondary btn-sm" style="padding: 5px 12px; font-size: 0.78rem; color: #27ae60; border-color: #a7f3d0;">
                        <i class="bi bi-arrow-counterclockwise"></i> Bulk Restore
                    </button>
                    <button type="button" onclick="submitBulkAction('force_delete')" class="btn btn-secondary btn-sm" style="padding: 5px 12px; font-size: 0.78rem; color: #c53030; border-color: #feb2b2; background-color: #fff5f5;">
                        <i class="bi bi-trash3-fill"></i> Bulk Permanently Delete
                    </button>
                </div>
            </div>

            <!-- Archived Comments Listing -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach($archivedComments as $comment)
                    <div style="border: 1px solid #fed7d7; border-left: 5px solid #e74c3c; border-radius: 8px; padding: 16px; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <input type="checkbox" name="comment_ids[]" value="{{ $comment->id }}" class="comment-checkbox" style="width: 16px; height: 16px; accent-color: #e74c3c;">
                                
                                @if($comment->document)
                                    <a href="{{ route('documents.show', $comment->document->id) }}" style="font-weight: 700; font-size: 0.85rem; color: var(--primary-dark); text-decoration: none; background: rgba(37, 111, 136, 0.08); padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(37, 111, 136, 0.2);">
                                        <i class="bi bi-file-earmark-text"></i> {{ $comment->document->file_number }}
                                    </a>
                                    <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">({{ Str::limit($comment->document->title, 40) }})</span>
                                @else
                                    <span class="badge" style="background-color: #95a5a6; color: white;">File Deleted</span>
                                @endif

                                <span style="font-size: 0.82rem; color: #4a5568;">
                                    By: <strong>{{ $comment->user ? $comment->user->name : 'Unknown User' }}</strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">
                                        ({{ $comment->user && $comment->user->designation ? $comment->user->designation->title : ($comment->user && $comment->user->department ? $comment->user->department->code : 'Admin') }})
                                    </span>
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.75rem; color: #a0aec0;">
                                    <i class="bi bi-clock-history"></i> Archived {{ $comment->deleted_at->diffForHumans() }}
                                </span>
                                
                                @if($comment->document)
                                    <form action="{{ route('documents.restoreComment', ['document' => $comment->document->id, 'id' => $comment->id]) }}" method="POST" style="margin: 0; display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 3px 9px; font-size: 0.72rem; color: #27ae60; border-color: #a7f3d0;" title="Restore Remark">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('documents.forceDeleteComment', ['document' => $comment->document->id, 'id' => $comment->id]) }}" method="POST" onsubmit="return confirm('Permanently delete this comment? This action CANNOT be undone.');" style="margin: 0; display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 3px 9px; font-size: 0.72rem; color: #c53030; border-color: #feb2b2;" title="Permanently Delete">
                                            <i class="bi bi-x-circle-fill"></i> Delete Permanently
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <p style="font-size: 0.9rem; color: #2d3748; margin-bottom: 8px; white-space: pre-line; background-color: #fff9f9; padding: 10px 14px; border-radius: 6px; border: 1px dashed #fed7d7;">
                            {{ $comment->content }}
                        </p>

                        @if($comment->attachment_path)
                            @php 
                                $cExt = strtolower(pathinfo($comment->attachment_path, PATHINFO_EXTENSION)); 
                                $downloadUrl = route('attachments.download', ['type' => 'comment', 'id' => $comment->id]);
                            @endphp
                            <div style="margin-top: 6px;">
                                <a href="{{ $downloadUrl }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; padding: 3px 10px; text-decoration: none;">
                                    <i class="bi bi-paperclip"></i> Attached File ({{ strtoupper($cExt) }})
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="margin-top: 25px;">
                {{ $archivedComments->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <i class="bi bi-chat-square-dots" style="font-size: 3rem; display: block; margin-bottom: 12px; color: var(--border-color);"></i>
                <h4 style="font-size: 1.1rem; color: var(--primary-dark); margin-bottom: 6px;">No Archived Remarks Found</h4>
                <p style="font-size: 0.85rem; margin: 0;">Any comments deleted by Super Admin will appear in this repository for review, restoration, or permanent deletion.</p>
            </div>
        @endif
    </div>

    <!-- Hidden Bulk Action Form -->
    <form id="bulkForm" action="{{ route('archives.comments.bulkAction') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk_action_input">
        <div id="bulk_inputs_container"></div>
    </form>
@endsection

@section('scripts')
    <script>
        function toggleSelectAll(master) {
            const checkboxes = document.querySelectorAll('.comment-checkbox');
            checkboxes.forEach(cb => cb.checked = master.checked);
        }

        function submitBulkAction(action) {
            const selected = document.querySelectorAll('.comment-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one remark.');
                return;
            }

            let msg = `Restore ${selected.length} selected archived remark(s)?`;
            if (action === 'force_delete') {
                msg = `Permanently delete ${selected.length} selected archived remark(s)? This action CANNOT be undone.`;
            }

            if (!confirm(msg)) {
                return;
            }

            const form = document.getElementById('bulkForm');
            document.getElementById('bulk_action_input').value = action;
            const container = document.getElementById('bulk_inputs_container');
            container.innerHTML = '';

            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'comment_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            form.submit();
        }
    </script>
@endsection
