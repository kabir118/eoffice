@extends('layouts.app')

@section('title', 'My Comments Log - USTB e-Office')
@section('page_title', 'My Remarks & Comments History')

@section('content')
    <!-- Shortcut Stat Cards Bar -->
    @include('partials.stats_cards')

    <div class="dashboard-card" style="margin-bottom: 0;">
        <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
            <span>Files I Have Commented On</span>
        </div>

        <!-- Search Bar & Filter Row (Matching Screenshot) -->
        <div style="margin-bottom: 25px;">
            <form action="{{ route('documents.myRemarks') }}" method="GET" class="search-filter-form" style="display: flex; gap: 10px; max-width: 950px; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex-grow: 1; min-width: 220px;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search files by ID, Subject, Description..." value="{{ $search ?? '' }}">
                </div>

                <!-- Live Initiator Filter Auto-complete -->
                <div style="position: relative; min-width: 210px;">
                    <input type="hidden" name="creator_id" id="filter_creator_id" value="{{ $creatorId ?? '' }}">
                    <div style="display: flex; align-items: center; position: relative;">
                        <i class="bi bi-person-search" style="position: absolute; left: 12px; color: var(--text-muted); z-index: 2;"></i>
                        <input type="text" id="creator_search_input" class="form-control" style="padding-left: 36px; padding-right: 28px; font-weight: 500; font-size: 0.85rem;" placeholder="Filter by Initiator..." value="{{ $selectedCreator ? ($selectedCreator->designation ? $selectedCreator->designation->title . ' (' . $selectedCreator->name . ')' : $selectedCreator->name) : '' }}" autocomplete="off">
                        @if(!empty($creatorId))
                            <button type="button" id="clear_creator_btn" style="position: absolute; right: 8px; border: none; background: none; color: #e74c3c; cursor: pointer; font-size: 0.9rem;" title="Clear Initiator Filter">✖</button>
                        @endif
                    </div>
                    <div id="creator_results_dropdown" class="dashboard-card" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; margin-top: 4px; max-height: 220px; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border: 1px solid var(--border-color); padding: 4px 0;">
                    </div>
                </div>

                <select name="filter" class="form-control" onchange="this.form.submit()" style="width: auto; min-width: 190px; cursor: pointer; font-weight: 600; color: var(--primary-dark);">
                    <option value="">-- All Filter Options --</option>
                    <optgroup label="File Status">
                        <option value="forwarded" {{ ($filter ?? '') === 'forwarded' ? 'selected' : '' }}>📤 Forwarded</option>
                        <option value="pending" {{ ($filter ?? '') === 'pending' ? 'selected' : '' }}>⌛ Pending</option>
                        <option value="completed" {{ ($filter ?? '') === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                    </optgroup>
                    <optgroup label="Priority Level">
                        <option value="normal" {{ ($filter ?? '') === 'normal' ? 'selected' : '' }}>🟢 Normal Priority</option>
                        <option value="urgent" {{ ($filter ?? '') === 'urgent' ? 'selected' : '' }}>🟠 Urgent Priority</option>
                        <option value="most_urgent" {{ ($filter ?? '') === 'most_urgent' ? 'selected' : '' }}>🔴 Most Urgent Priority</option>
                    </optgroup>
                </select>

                <button type="submit" class="btn btn-primary">Search</button>
                @if(!empty($search) || !empty($filter) || !empty($creatorId))
                    <a href="{{ route('documents.myRemarks') }}" class="btn btn-secondary" title="Clear Search & Filter">
                        <i class="bi bi-x-lg"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        @if(!empty($search) || !empty($filter))
            <div style="margin-bottom: 15px; font-size: 0.82rem; color: var(--text-muted);">
                Showing results &mdash; <strong style="color: var(--primary-color);">{{ $documents->total() }}</strong> file(s) found.
            </div>
        @endif

        @if($documents->count() > 0)
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>File No.</th>
                            <th>Subject</th>
                            <th>Initiated By</th>
                            <th>Currently Held By</th>
                            <th>Created</th>
                            <th>Priority</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr id="row-{{ $doc->id }}">
                                <td style="font-weight: 600; color: var(--primary-color); white-space: nowrap;">{{ $doc->file_number }}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span style="color: var(--primary-dark); font-weight: 600;">{{ Str::limit($doc->title, 40) }}</span>
                                            @if($doc->status === 'completed')
                                                <span class="badge badge-status-completed" style="font-size: 0.65rem; padding: 2px 6px;">Closed</span>
                                            @elseif($doc->status === 'assigned_back')
                                                <span class="badge badge-status-assigned_back" style="font-size: 0.65rem; padding: 2px 6px;">Assigned Back</span>
                                            @elseif($doc->status === 'draft')
                                                <span class="badge" style="background-color: #95a5a6; color: white; font-size: 0.65rem; padding: 2px 6px;">Draft</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($doc->creator && $doc->creator->designation)
                                        <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator->designation->title }}</strong>
                                        <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">({{ $doc->creator->name }})</span>
                                    @else
                                        <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator ? $doc->creator->name : 'N/A' }}</strong>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight: 500;">{{ $doc->currentHolder ? $doc->currentHolder->name : 'N/A' }}</span>
                                    @if($doc->currentHolder && $doc->currentHolder->designation)
                                        <br><span style="font-size: 0.72rem; color: var(--text-muted);">{{ $doc->currentHolder->designation->title }}</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.85rem; color: #2c3e50; font-weight: 700; white-space: nowrap;">{{ $doc->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($doc->priority === 'most_urgent')
                                        <span style="font-weight: 800; color: #e74c3c; font-size: 0.82rem;">⚠ Most Urgent</span>
                                    @else
                                        <span class="badge badge-priority-{{ $doc->priority }}">
                                            {{ ucfirst(str_replace('_', ' ', $doc->priority)) }}
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <button onclick="toggleComments({{ $doc->id }})" class="btn btn-accent btn-sm" id="btn-toggle-{{ $doc->id }}" style="padding: 5px 10px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-chat-square-text-fill"></i>
                                        <span>My Comments</span>
                                    </button>
                                    <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-primary btn-sm" style="padding: 5px 10px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <i class="bi bi-folder2-open"></i>
                                        <span>Open File</span>
                                    </a>
                                </td>
                            </tr>
                            <!-- Expandable comments row -->
                            <tr id="comments-row-{{ $doc->id }}" style="display: none;">
                                <td colspan="6" style="padding: 0; border-top: none;">
                                    <div id="comments-container-{{ $doc->id }}" style="padding: 15px 20px; background-color: rgba(37,111,136,0.02); border-left: 4px solid var(--primary-color);">
                                        <div style="text-align: center; padding: 15px; color: var(--text-muted); font-size: 0.85rem;">
                                            <i class="bi bi-hourglass-split"></i> Loading comments...
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 15px;">
                {{ $documents->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                <i class="bi bi-chat-square-text-fill" style="font-size: 2.5rem; display: block; margin-bottom: 12px; color: var(--border-color);"></i>
                @if($search)
                    <p style="font-size: 0.95rem; margin: 0;">No files found matching "<strong>{{ $search }}</strong>".</p>
                @else
                    <p style="font-size: 0.95rem; margin: 0;">You have not written any remarks or comments on documents yet.</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    const loadedComments = {};
    const commentsBaseUrl = "{{ url('/documents') }}";

    function toggleComments(docId) {
        const row = document.getElementById('comments-row-' + docId);
        const btn = document.getElementById('btn-toggle-' + docId);

        if (row.style.display === 'none') {
            row.style.display = 'table-row';
            btn.innerHTML = '<i class="bi bi-chevron-up"></i> <span>Hide</span>';

            if (!loadedComments[docId]) {
                fetchComments(docId);
            }
        } else {
            row.style.display = 'none';
            btn.innerHTML = '<i class="bi bi-chat-square-text-fill"></i> <span>My Comments</span>';
        }
    }

    function fetchComments(docId) {
        const container = document.getElementById('comments-container-' + docId);

        fetch(commentsBaseUrl + '/' + docId + '/my-comments', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            loadedComments[docId] = true;
            if (data.comments.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 15px; color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-chat-square"></i> No comments from you on this file.</div>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
            html += '<div style="font-size: 0.78rem; font-weight: 600; color: var(--primary-dark); margin-bottom: 2px;"><i class="bi bi-chat-left-quote-fill"></i> Your Comments (' + data.comments.length + ')</div>';

            data.comments.forEach(function(c, i) {
                html += '<div style="background: var(--white); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 14px;">';
                html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">';
                html += '<span style="font-size: 0.75rem; font-weight: 600; color: var(--primary-color);">#' + (i + 1) + '</span>';
                html += '<span style="font-size: 0.72rem; color: var(--text-muted);">' + c.date + ' (' + c.ago + ')</span>';
                html += '</div>';
                html += '<p style="font-size: 0.85rem; color: var(--text-main); margin: 0; white-space: pre-line;">' + escapeHtml(c.content) + '</p>';
                if (c.attachment) {
                    html += '<div style="margin-top: 6px;"><a href="' + c.download_url + '" style="font-size: 0.75rem; color: var(--primary-color); text-decoration: none; font-weight: 600;"><i class="bi bi-paperclip"></i> ' + escapeHtml(c.attachment) + '</a></div>';
                }
                html += '</div>';
            });

            html += '</div>';
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = '<div style="text-align: center; padding: 15px; color: #e74c3c; font-size: 0.85rem;"><i class="bi bi-exclamation-triangle"></i> Failed to load comments.</div>';
        });
    }

    // Live Creator Autocomplete Search
    document.addEventListener('DOMContentLoaded', function() {
        const creatorInput = document.getElementById('creator_search_input');
        const creatorHidden = document.getElementById('filter_creator_id');
        const dropdown = document.getElementById('creator_results_dropdown');
        const clearBtn = document.getElementById('clear_creator_btn');
        let debounceTimer = null;

        if (creatorInput) {
            creatorInput.addEventListener('keyup', function() {
                clearTimeout(debounceTimer);
                const q = this.value.trim();
                if (q.length < 1) {
                    dropdown.style.display = 'none';
                    return;
                }
                debounceTimer = setTimeout(() => {
                    fetch(`{{ route('users.search') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(data => {
                            dropdown.innerHTML = '';
                            if (data.length === 0) {
                                dropdown.innerHTML = '<div style="padding: 10px; font-size: 0.82rem; color: var(--text-muted); text-align: center;">No matching officers found</div>';
                            } else {
                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 0.82rem; transition: background 0.15s ease;';
                                    div.onmouseover = function() { this.style.backgroundColor = '#f8f9fa'; };
                                    div.onmouseout = function() { this.style.backgroundColor = 'transparent'; };
                                    div.innerHTML = `<strong style="color: var(--primary-dark); display: block;">${item.designation}</strong><span style="color: var(--text-muted); font-size: 0.76rem;">(${item.name})</span>`;
                                    div.addEventListener('click', function() {
                                        creatorHidden.value = item.id;
                                        creatorInput.value = `${item.designation} (${item.name})`;
                                        dropdown.style.display = 'none';
                                        creatorInput.form.submit();
                                    });
                                    dropdown.appendChild(div);
                                });
                            }
                            dropdown.style.display = 'block';
                        })
                        .catch(err => console.error(err));
                }, 250);
            });

            document.addEventListener('click', function(e) {
                if (!creatorInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    creatorHidden.value = '';
                    creatorInput.value = '';
                    creatorInput.form.submit();
                });
            }
        }
    });
</script>
@endsection
