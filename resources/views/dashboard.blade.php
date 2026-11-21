@extends('layouts.app')

@section('title', 'Dashboard - USTB e-Office')
@section('page_title', 'Administrative Overview')

@section('content')
    <!-- Shortcut Stat Cards Bar -->
    @include('partials.stats_cards')

    <!-- Search Bar & Filter Row -->
    <div style="margin-bottom: 25px;">
        <form action="{{ route('dashboard') }}" method="GET" class="search-filter-form" style="display: flex; gap: 10px; max-width: 950px; flex-wrap: wrap; align-items: center;">
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
                <a href="{{ route('dashboard') }}" class="btn btn-secondary" title="Clear Search & Filter">
                    <i class="bi bi-x-lg"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Single Table: Files Need Your Attention -->
    <div class="dashboard-card" style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e74c3c; padding-bottom: 12px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: #e74c3c; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="bi bi-bell-fill" style="color: #e74c3c;"></i>
                <span>Files Need Your Attention</span>
                @if($attentionFiles->count() > 0)
                    <span class="badge" style="background-color: #e74c3c; color: white; padding: 2px 8px; font-size: 0.75rem; border-radius: 10px;">{{ $attentionFiles->count() }}</span>
                @endif
            </div>
            <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="btn btn-secondary btn-sm">
                <span>View Marked to Me</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive">
            @if($attentionFiles->count() > 0)
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>File Tracking ID</th>
                            <th>Title</th>
                            <th>Initiated On</th>
                            <th>Initiated By</th>
                            <th>Assigned By</th>
                            <th>Last Activity</th>
                            <th>Priority</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attentionFiles as $doc)
                            @php
                                $lastTrack = $doc->tracks->where('receiver_id', Auth::id())->sortByDesc('id')->first();
                                $assignedByUser = $lastTrack ? $lastTrack->sender : $doc->creator;
                            @endphp
                            <tr style="{{ $doc->is_unread ? 'font-weight: 600; background-color: rgba(231,76,60,0.02);' : '' }}">
                                <td style="font-weight: 600; color: var(--primary-color);">{{ $doc->file_number }}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span style="color: var(--primary-dark); font-weight: 500;">{{ Str::limit($doc->title, 45) }}</span>
                                            @if($doc->is_unread)
                                                <span class="badge badge-priority-most-urgent" style="font-size: 0.65rem; padding: 2px 6px; margin-left: 5px;">NEW</span>
                                            @endif
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($doc->description, 50) }}</span>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem; color: #2c3e50; font-weight: 600;">{{ $doc->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($doc->creator && $doc->creator->designation)
                                        <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator->designation->title }}</strong>
                                        <span style="font-size: 0.76rem; color: var(--text-main); display: block;">({{ $doc->creator->name }})</span>
                                    @else
                                        <strong style="color: var(--primary-dark); display: block;">{{ $doc->creator ? $doc->creator->name : 'N/A' }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($assignedByUser && $assignedByUser->designation)
                                        <strong style="color: var(--primary-dark); display: block;">{{ $assignedByUser->designation->title }}</strong>
                                        <span style="font-size: 0.76rem; color: var(--text-main); display: block;">({{ $assignedByUser->name }})</span>
                                    @else
                                        <strong style="color: var(--primary-dark); display: block;">{{ $assignedByUser ? $assignedByUser->name : 'N/A' }}</strong>
                                    @endif
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
                                    <a href="{{ route('documents.show', $doc->id) }}" class="btn {{ $doc->is_unread ? 'btn-accent' : 'btn-primary' }} btn-sm" style="padding: 5px 12px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;">
                                        <i class="bi {{ $doc->is_unread ? 'bi-envelope-open-fill' : 'bi-eye' }}"></i>
                                        <span>{{ $doc->is_unread ? 'Open File' : 'View' }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 45px 20px; color: var(--text-muted);">
                    <i class="bi bi-check-circle" style="font-size: 2.8rem; display: block; margin-bottom: 12px; color: #2ecc71;"></i>
                    <p style="font-size: 1rem; font-weight: 600; color: var(--primary-dark); margin: 0;">You're all caught up!</p>
                    <p style="font-size: 0.85rem; margin-top: 5px;">No pending files requiring your attention right now.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
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
