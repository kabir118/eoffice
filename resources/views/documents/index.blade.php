@extends('layouts.app')

@section('title', 'Files - USTB e-Office')

@section('page_title')
    @if($tab === 'outbox')
        Files Created
    @elseif($tab === 'completed')
        Completed Archive
    @elseif($tab === 'cc')
        Copied to Me (CC) — For Information
    @elseif($tab === 'all')
        Track All University Files
    @elseif($tab === 'trash')
        Recycle Bin / Trash
    @else
        Files Assigned
    @endif
@endsection

@section('content')
    <!-- Shortcut Stat Cards Bar -->
    @include('partials.stats_cards')

    <!-- Search Bar & Filter Row -->
    <div style="margin-bottom: 25px;">
        <form action="{{ route('documents.index') }}" method="GET" class="search-filter-form" style="display: flex; gap: 10px; max-width: 950px; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="tab" value="{{ $tab }}">
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

            <select name="filter" class="form-control" onchange="this.form.submit()" style="width: auto; min-width: 175px; cursor: pointer; font-weight: 600; color: var(--primary-dark);">
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
                <a href="{{ route('documents.index', ['tab' => $tab]) }}" class="btn btn-secondary" title="Clear Search & Filter">
                    <i class="bi bi-x-lg"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Main List Card -->
    <div class="dashboard-card">
        @if(Auth::user()->isSuperAdmin() && $documents->count() > 0)
            <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; background-color: #fafafa; padding: 10px 15px; border-radius: var(--border-radius); border: 1px solid var(--border-color); flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; font-size: 0.85rem; color: var(--primary-dark);">
                        <i class="bi bi-check2-square" style="color: var(--primary-color);"></i> Bulk Actions:
                    </span>
                    <button type="submit" form="bulk-delete-form" id="btn-bulk-delete" class="btn btn-secondary btn-sm" disabled onclick="return confirm('Are you sure you want to delete all selected files?');" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; color: #e74c3c; border-color: #e74c3c;">
                        <i class="bi bi-trash3-fill"></i>
                        <span>Delete Selected (<span id="selected-count">0</span>)</span>
                    </button>
                </div>
                <span style="font-size: 0.78rem; color: var(--text-muted);">Select checkboxes to delete multiple files at once.</span>
            </div>
        @endif

        <div class="table-responsive">
            @if($documents->count() > 0)
                <form id="bulk-delete-form" action="{{ route('documents.bulkDelete') }}" method="POST">
                    @csrf
                    @if($tab === 'trash')
                        <input type="hidden" name="force" value="1">
                    @endif
                    <table class="custom-table">
                        <thead>
                            <tr>
                                @if(Auth::user()->isSuperAdmin())
                                    <th style="width: 35px; text-align: center;"><input type="checkbox" id="select-all-files" style="cursor: pointer;"></th>
                                @endif
                                <th>File Tracking ID</th>
                                <th>Title</th>
                                <th>Initiated On</th>
                                <th>Initiated By</th>
                                @if($tab === 'inbox')
                                    <th>Assigned By</th>
                                @else
                                    <th>Current Holder</th>
                                @endif
                                <th>Last Activity</th>
                                <th>Priority</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr style="{{ $tab === 'inbox' && !$doc->tracks()->where('receiver_id', Auth::id())->whereNotNull('received_at')->exists() ? 'font-weight: 600; background-color: rgba(37,111,136,0.01);' : '' }}">
                                    @if(Auth::user()->isSuperAdmin())
                                        <td style="text-align: center;"><input type="checkbox" name="ids[]" value="{{ $doc->id }}" class="file-checkbox" style="cursor: pointer;"></td>
                                    @endif
                                    <td style="font-weight: 600; color: var(--primary-color);">{{ $doc->file_number }}</td>
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
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($doc->description, 50) }}</span>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #2c3e50; font-weight: 600;">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($doc->creator && $doc->creator->designation)
                                            <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator->designation->title }}</strong>
                                            <span style="font-size: 0.74rem; color: var(--text-muted); display: block;">({{ $doc->creator->name }})</span>
                                        @else
                                            <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator ? $doc->creator->name : 'N/A' }}</strong>
                                        @endif
                                    </td>
                                    @if($tab === 'inbox')
                                        @php
                                            $lastTrack = $doc->tracks->where('receiver_id', Auth::id())->sortByDesc('id')->first();
                                            $assignedByUser = $lastTrack ? $lastTrack->sender : $doc->creator;
                                        @endphp
                                        <td>
                                            <span style="font-weight: 600; display: block; color: var(--primary-dark);">{{ $assignedByUser ? $assignedByUser->name : 'N/A' }}</span>
                                            <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">
                                                {{ $assignedByUser && $assignedByUser->designation ? $assignedByUser->designation->title : '' }}
                                                ({{ $assignedByUser && $assignedByUser->department ? $assignedByUser->department->code : 'N/A' }})
                                            </span>
                                        </td>
                                    @else
                                        <td>
                                            <span style="font-weight: 500; display: block;">{{ $doc->currentHolder ? $doc->currentHolder->name : 'N/A' }}</span>
                                            <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">
                                                {{ $doc->currentHolder && $doc->currentHolder->designation ? $doc->currentHolder->designation->title : '' }}
                                                ({{ $doc->currentDepartment ? $doc->currentDepartment->code : 'N/A' }})
                                            </span>
                                        </td>
                                    @endif
                                    <td style="font-size: 0.85rem; color: #2c3e50; font-weight: 600;">{{ $doc->updated_at->diffForHumans() }}</td>
                                    <td>
                                        @if($doc->priority === 'most_urgent')
                                            <span style="font-weight: 800; color: #e74c3c; font-size: 0.82rem;">⚠ Most Urgent</span>
                                        @else
                                            <span class="badge badge-priority-{{ $doc->priority }}">
                                                {{ ucfirst(str_replace('_', ' ', $doc->priority)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            @if($tab === 'trash')
                                                <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="View Trashed File">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-primary btn-sm" style="padding: 5px 10px;">
                                                    <i class="bi bi-folder2-open"></i>
                                                    <span>Open</span>
                                                </a>
                                                @if(Auth::user()->isSuperAdmin())
                                                    <button type="button" onclick="if(confirm('Move file {{ $doc->file_number }} to Trash Bin?')) { document.getElementById('single-delete-{{ $doc->id }}').submit(); }" class="btn btn-secondary btn-sm" style="padding: 5px 8px; color: #e74c3c;" title="Move to Trash Bin">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>

                <!-- Hidden Single Delete Forms for Super Admin -->
                @if(Auth::user()->isSuperAdmin())
                    @foreach($documents as $doc)
                        <form id="single-delete-{{ $doc->id }}" action="{{ route('documents.destroy', $doc->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif

                <!-- Pagination Links -->
                <div style="margin-top: 25px;">
                    {{ $documents->links() }}
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-folder-x" style="font-size: 3rem; display: block; margin-bottom: 12px; color: var(--text-muted);"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">No files in this folder.</p>
                    <p style="font-size: 0.85rem; margin-top: 5px;">When files are sent to you or initiated by you, they will appear here.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live Creator Autocomplete Search
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

            // Super Admin Bulk Delete Script
            const selectAll = document.getElementById('select-all-files');
            const checkboxes = document.querySelectorAll('.file-checkbox');
            const bulkBtn = document.getElementById('btn-bulk-delete');
            const countSpan = document.getElementById('selected-count');

            function updateState() {
                const checkedCount = document.querySelectorAll('.file-checkbox:checked').length;
                if (countSpan) countSpan.textContent = checkedCount;
                if (bulkBtn) {
                    bulkBtn.disabled = checkedCount === 0;
                    if (checkedCount > 0) {
                        bulkBtn.style.backgroundColor = '#e74c3c';
                        bulkBtn.style.color = '#ffffff';
                    } else {
                        bulkBtn.style.backgroundColor = 'transparent';
                        bulkBtn.style.color = '#e74c3c';
                    }
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateState();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (selectAll) {
                        selectAll.checked = checkboxes.length > 0 && Array.from(checkboxes).every(c => c.checked);
                    }
                    updateState();
                });
            });
        });
    </script>
@endsection
