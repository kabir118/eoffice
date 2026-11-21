@extends('layouts.app')

@section('title', 'Files Created - USTB e-Office')

@section('page_title', 'Files Created')

@section('content')
    <!-- Shortcut Stat Cards Bar -->
    @include('partials.stats_cards')

    <!-- Header Row -->
    <div style="margin-bottom: 20px;">
        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">
            <i class="bi bi-info-circle"></i> Active open files initiated and created by you.
        </p>
    </div>

    <!-- Search Bar & Filter Row -->
    <div style="margin-bottom: 25px;">
        <form action="{{ route('documents.myFiles') }}" method="GET" class="search-filter-form" style="display: flex; gap: 10px; max-width: 750px; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="status" value="{{ $status }}">
            <div style="position: relative; flex-grow: 1; min-width: 240px;">
                <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search files by ID, Subject, Description..." value="{{ $search ?? '' }}">
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
            @if(!empty($search) || !empty($filter))
                <a href="{{ route('documents.myFiles', ['status' => $status]) }}" class="btn btn-secondary" title="Clear Search & Filter">
                    <i class="bi bi-x-lg"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Files Table -->
    <div class="dashboard-card">
        @if(Auth::user()->isSuperAdmin() && $documents->count() > 0)
            <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; background-color: #fafafa; padding: 10px 15px; border-radius: var(--border-radius); border: 1px solid var(--border-color); flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; font-size: 0.85rem; color: var(--primary-dark);">
                        <i class="bi bi-check2-square" style="color: var(--primary-color);"></i> Bulk Actions:
                    </span>
                    <button type="submit" form="bulk-delete-form-mf" id="btn-bulk-delete-mf" class="btn btn-secondary btn-sm" disabled onclick="return confirm('Are you sure you want to delete all selected files?');" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; color: #e74c3c; border-color: #e74c3c;">
                        <i class="bi bi-trash3-fill"></i>
                        <span>Delete Selected (<span id="selected-count-mf">0</span>)</span>
                    </button>
                </div>
                <span style="font-size: 0.78rem; color: var(--text-muted);">Select checkboxes to delete multiple files at once.</span>
            </div>
        @endif

        <div class="table-responsive">
            @if($documents->count() > 0)
                <form id="bulk-delete-form-mf" action="{{ route('documents.bulkDelete') }}" method="POST">
                    @csrf
                    <table class="custom-table">
                        <thead>
                            <tr>
                                @if(Auth::user()->isSuperAdmin())
                                    <th style="width: 35px; text-align: center;"><input type="checkbox" id="select-all-files-mf" style="cursor: pointer;"></th>
                                @endif
                                <th>File Tracking ID</th>
                                <th>Title</th>
                                <th>Initiated On</th>
                                <th>Initiated By</th>
                                <th>Current Holder</th>
                                <th>Last Activity</th>
                                <th>Priority</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    @if(Auth::user()->isSuperAdmin())
                                        <td style="text-align: center;"><input type="checkbox" name="ids[]" value="{{ $doc->id }}" class="file-checkbox-mf" style="cursor: pointer;"></td>
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
                                    <td>
                                        <span style="font-weight: 500; display: block;">{{ $doc->currentHolder ? $doc->currentHolder->name : 'N/A' }}</span>
                                        <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">
                                            {{ $doc->currentHolder && $doc->currentHolder->designation ? $doc->currentHolder->designation->title : '' }}
                                            ({{ $doc->currentDepartment ? $doc->currentDepartment->code : 'N/A' }})
                                        </span>
                                    </td>
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
                                        @php
                                            $isWithMe = $doc->current_holder_id === Auth::id();
                                        @endphp
                                        <div style="display: flex; flex-direction: column; gap: 5px; align-items: flex-start;">
                                            @if($doc->status === 'completed')
                                                <span class="badge badge-status-approved" style="font-size: 0.72rem;">Closed</span>
                                            @elseif($isWithMe)
                                                <span class="badge" style="background-color: rgba(39,174,96,0.12); color: #1e8449; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 20px;">
                                                    <i class="bi bi-person-check-fill"></i> With You
                                                </span>
                                            @else
                                                <span class="badge" style="background-color: rgba(231,76,60,0.1); color: #c0392b; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 20px;">
                                                    <i class="bi bi-arrow-right-circle-fill"></i> In Transit
                                                </span>
                                                <div style="font-size: 0.7rem; color: var(--text-muted);">
                                                    {{ $doc->currentHolder ? ($doc->currentHolder->designation ? $doc->currentHolder->designation->title : $doc->currentHolder->name) : '—' }}
                                                </div>
                                            @endif
                                            <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-primary btn-sm" style="padding: 4px 10px; margin-top: 4px; font-size: 0.78rem;">
                                                <i class="bi bi-folder2-open"></i>
                                                <span>Open</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>

                <!-- Pagination -->
                <div style="margin-top: 25px;">
                    {{ $documents->links() }}
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-folder-x" style="font-size: 3rem; display: block; margin-bottom: 12px; color: var(--text-muted);"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">No files found.</p>
                    <p style="font-size: 0.85rem; margin-top: 5px;">When files are sent to you or initiated by you, they will appear here.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @if(Auth::user()->isSuperAdmin())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('select-all-files-mf');
                const checkboxes = document.querySelectorAll('.file-checkbox-mf');
                const bulkBtn = document.getElementById('btn-bulk-delete-mf');
                const countSpan = document.getElementById('selected-count-mf');

                function updateState() {
                    const checkedCount = document.querySelectorAll('.file-checkbox-mf:checked').length;
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
    @endif
@endsection
