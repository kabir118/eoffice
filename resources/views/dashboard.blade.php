@extends('layouts.app')

@section('title', 'Dashboard - USTB e-Office')
@section('page_title', 'Administrative Overview')

@section('content')
    <!-- Shortcut Stat Cards Bar -->
    @include('partials.stats_cards')

    <!-- Search Bar & Filter Row -->
    <div style="margin-bottom: 25px;">
        <form action="{{ route('dashboard') }}" method="GET" style="display: flex; gap: 10px; max-width: 780px; flex-wrap: wrap; align-items: center;">
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
            <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">
                <span>View All Files</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive">
            @if($attentionFiles->count() > 0)
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>File No</th>
                            <th>Subject</th>
                            <th>Initiated On</th>
                            <th>Marked By</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attentionFiles as $doc)
                            <tr style="{{ $doc->is_unread ? 'font-weight: 600; background-color: rgba(231,76,60,0.02);' : '' }}">
                                <td style="font-weight: 600; color: var(--primary-color);">{{ $doc->file_number }}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="color: var(--primary-dark); font-weight: 500;">
                                            {{ Str::limit($doc->title, 45) }}
                                            @if($doc->is_unread)
                                                <span class="badge badge-priority-most-urgent" style="font-size: 0.65rem; padding: 2px 6px; margin-left: 5px;">NEW</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem; color: #2c3e50; font-weight: 600;">{{ $doc->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span style="font-weight: 600; display: block; color: var(--primary-dark);">{{ $doc->creator ? $doc->creator->name : 'N/A' }}</span>
                                    @if($doc->creator && $doc->creator->designation)
                                        <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">{{ $doc->creator->designation->title }}</span>
                                    @endif
                                </td>
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
                                    @if($doc->status === 'assigned_back' || ($doc->status === 'forwarded' && $doc->current_holder_id === $doc->creator_id))
                                        <span class="badge badge-status-assigned_back">Assigned Back</span>
                                    @else
                                        <span class="badge badge-status-{{ $doc->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
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
