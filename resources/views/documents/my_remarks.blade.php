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
            <form action="{{ route('documents.myRemarks') }}" method="GET" style="display: flex; gap: 10px; max-width: 780px; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex-grow: 1; min-width: 240px;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search files by ID, Subject, Description..." value="{{ $search ?? '' }}">
                </div>

                <select name="filter" class="form-control" onchange="this.form.submit()" style="width: auto; min-width: 175px; cursor: pointer; font-weight: 600; color: var(--primary-dark);">
                    <option value="">-- All Files --</option>
                    <option value="pending" {{ ($filter ?? '') === 'pending' ? 'selected' : '' }}>⌛ Pending Files</option>
                    <option value="normal" {{ ($filter ?? '') === 'normal' ? 'selected' : '' }}>🟢 Normal Priority</option>
                    <option value="urgent" {{ ($filter ?? '') === 'urgent' ? 'selected' : '' }}>🟠 Urgent Priority</option>
                    <option value="most_urgent" {{ ($filter ?? '') === 'most_urgent' ? 'selected' : '' }}>🔴 Most Urgent Priority</option>
                </select>

                <button type="submit" class="btn btn-primary">Search</button>
                @if(!empty($search) || !empty($filter))
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
                                <td>{{ Str::limit($doc->title, 40) }}</td>
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endsection
