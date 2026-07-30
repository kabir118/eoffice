@extends('layouts.app')

@section('title', 'Support Tickets - USTB e-Office')
@section('page_title', 'Support Ticket Center')

@section('styles')
<style>
    .tickets-split-layout {
        display: flex;
        gap: 30px;
        align-items: flex-start;
        width: 100%;
    }
    .tickets-main-col {
        flex-grow: 2;
        width: 68%;
    }
    .tickets-side-col {
        width: 30%;
        min-width: 280px;
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .tickets-container {
        background-color: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        overflow: hidden;
    }
    .tickets-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: #fafafa;
    }
    .tickets-header-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #555;
        margin: 0;
    }
    
    /* Stale Tickets Banner */
    .stale-banner {
        background-color: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 30px 20px;
        text-align: center;
    }
    .stale-banner-title {
        font-size: 1.25rem;
        color: #2c3e50;
        font-weight: 500;
        margin-bottom: 10px;
    }
    .stale-banner-text {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 20px;
    }
    .btn-stale-close {
        background-color: #c0392b;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        cursor: pointer;
        transition: background-color 0.2s;
        letter-spacing: 0.5px;
    }
    .btn-stale-close:hover {
        background-color: #a93226;
    }
    .btn-stale-later {
        background-color: #f5f5f5;
        color: #555;
        border: 1px solid #ddd;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        cursor: pointer;
        margin-left: 10px;
        transition: background-color 0.2s;
    }
    .btn-stale-later:hover {
        background-color: #e8e8e8;
    }

    /* Category Subheaders */
    .ticket-cat-header {
        padding: 10px 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
    }
    .cat-blue {
        background-color: #eaf7fc;
        color: #00a0e3;
        border-left: 4px solid #00a0e3;
    }
    .cat-red {
        background-color: #fdf0ef;
        color: #c0392b;
        border-left: 4px solid #c0392b;
    }
    .cat-gray {
        background-color: #f5f5f5;
        color: #777;
        border-left: 4px solid #999;
    }

    /* Ticket Card Row */
    .ticket-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s;
    }
    .ticket-row:hover {
        background-color: #fbfbfb;
    }
    .ticket-left-block {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-grow: 1;
    }
    .ticket-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: rgba(37,111,136,0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        border: 2px solid rgba(37,111,136,0.15);
    }
    .ticket-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .ticket-user {
        font-size: 0.82rem;
        color: #888;
        font-weight: 500;
    }
    .ticket-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .ticket-title:hover {
        color: var(--primary-color);
    }
    .ticket-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 0.78rem;
        color: #777;
        margin-top: 2px;
    }
    .ticket-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Status Icons */
    .status-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-pending {
        border: 2.5px solid #ff9f43;
        background-color: transparent;
    }
    .status-replied {
        background-color: #ff9f43;
    }
    .status-solved {
        background-color: #28c76f;
    }

    /* Sidebar Cards */
    .sidebar-card {
        background-color: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .sidebar-card-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 8px;
        margin: 0;
    }
    .sidebar-link {
        font-size: 0.85rem;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        display: block;
        padding: 5px 0;
        transition: color 0.2s;
    }
    .sidebar-link:hover {
        color: var(--accent-color);
    }
</style>
@endsection

@section('content')
<div class="tickets-split-layout">
    
    <!-- Left Column: Tickets Listing -->
    <div class="tickets-main-col">
        
        <!-- Open Tickets Pane Card -->
        <div class="tickets-container">
            <!-- Main Panel Header -->
            <div class="tickets-header">
                <h4 class="tickets-header-title">Your Open Tickets</h4>
                @if(!Auth::user()->isSuperAdmin())
                    <button onclick="openCreateModal()" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i>
                        <span>Submit New Query</span>
                    </button>
                @endif
            </div>

            <!-- Stale Tickets Banner (Admin view only) -->
            @if(Auth::user()->isSuperAdmin() && $staleCount > 0)
                <div class="stale-banner" id="staleBanner">
                    <h3 class="stale-banner-title">Let's close out some stale tickets...</h3>
                    <p class="stale-banner-text">
                        You have <strong>{{ $staleCount }}</strong> stale {{ Str::plural('ticket', $staleCount) }} that have not received a response in over 7 days.<br>
                        Would you like to close those tickets? <a href="#" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Change Setting</a>
                    </p>
                    <form action="{{ route('support.closeStale') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-stale-close">Close The Tickets</button>
                    </form>
                    <button type="button" onclick="closeStaleBanner()" class="btn-stale-later">Ask Later</button>
                </div>
            @endif

            <!-- 2. OVER A WEEK OLD CATEGORY -->
            @if($overAWeekOld->count() > 0)
                <div class="ticket-cat-header cat-blue">Over a Week Old</div>
                @foreach($overAWeekOld as $ticket)
                    @include('support.partials.ticket_row', ['ticket' => $ticket])
                @endforeach
            @endif

            <!-- 3. OVER A DAY OLD CATEGORY -->
            @if($overADayOld->count() > 0)
                <div class="ticket-cat-header cat-blue">Over a Day Old</div>
                @foreach($overADayOld as $ticket)
                    @include('support.partials.ticket_row', ['ticket' => $ticket])
                @endforeach
            @endif

            <!-- 4. UPDATED TODAY CATEGORY -->
            @if($updatedToday->count() > 0)
                <div class="ticket-cat-header cat-red">Updated Today</div>
                @foreach($updatedToday as $ticket)
                    @include('support.partials.ticket_row', ['ticket' => $ticket])
                @endforeach
            @endif

            <!-- Empty State fallback -->
            @if($overAWeekOld->count() == 0 && $overADayOld->count() == 0 && $updatedToday->count() == 0)
                <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                    <i class="bi bi-ticket-detailed" style="font-size: 2.5rem; display: block; margin-bottom: 10px; color: var(--border-color);"></i>
                    <p style="font-size: 0.88rem; margin: 0;">No open tickets found.</p>
                </div>
            @endif
        </div>

        <!-- No Action Needed Tickets Pane Card -->
        <div class="tickets-container" style="margin-top: 30px;">
            <div class="tickets-header">
                <h4 class="tickets-header-title">No Action Needed</h4>
            </div>

            @if($noActionNeeded->count() > 0)
                @foreach($noActionNeeded as $ticket)
                    @include('support.partials.ticket_row', ['ticket' => $ticket])
                @endforeach
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-bookmark-check" style="font-size: 2rem; display: block; margin-bottom: 10px; color: var(--border-color);"></i>
                    <p style="font-size: 0.88rem; margin: 0;">No tickets in this category.</p>
                </div>
            @endif
        </div>

        <!-- Solved / Closed Tickets Pane Card -->
        <div class="tickets-container" style="margin-top: 30px;">
            <div class="tickets-header">
                <h4 class="tickets-header-title">Closed Tickets</h4>
            </div>

            @if($closedTickets->count() > 0)
                <div class="ticket-cat-header cat-gray">Solved / Closed</div>
                @foreach($closedTickets as $ticket)
                    @include('support.partials.ticket_row', ['ticket' => $ticket])
                @endforeach
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="bi bi-archive-fill" style="font-size: 2rem; display: block; margin-bottom: 10px; color: var(--border-color);"></i>
                    <p style="font-size: 0.88rem; margin: 0;">No solved tickets found.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Sidebar Widgets -->
    <div class="tickets-side-col">
        
        <!-- Widget 1: Bulk Actions -->
        @if(Auth::user()->isSuperAdmin())
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">Bulk Actions</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a onclick="selectAllTickets()" class="sidebar-link"><i class="bi bi-check2-all"></i> Select all</a>
                    <a onclick="deselectAllTickets()" class="sidebar-link"><i class="bi bi-x"></i> Deselect all</a>
                    <a onclick="submitBulkClose()" class="sidebar-link" style="color: #c0392b;"><i class="bi bi-lock"></i> Close selected</a>
                </div>
            </div>
        @endif

        <!-- Widget 2: Ticket Categories (Admin View Only) -->
        @if(Auth::user()->isSuperAdmin())
            <div class="sidebar-card">
                <h4 class="sidebar-card-title">Ticket Categories</h4>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                    @foreach($departments as $dept)
                        @if($dept->total_tickets_count > 0)
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #f0f0f0; padding-bottom: 8px;">
                                <span style="font-size: 0.82rem; font-weight: 600; color: #2c3e50; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 170px;" title="{{ $dept->name }}">
                                    {{ $dept->name }}
                                </span>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    @if($dept->active_tickets_count > 0)
                                        <span style="background-color: #ff9f43; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: bold;" title="Active/Pending: {{ $dept->active_tickets_count }}">
                                            {{ $dept->active_tickets_count }}
                                        </span>
                                    @endif
                                    <span style="background-color: #bbb; color: white; border-radius: 10px; padding: 1px 6px; font-size: 0.65rem; font-weight: bold;" title="Total: {{ $dept->total_tickets_count }}">
                                        {{ $dept->total_tickets_count }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @php
                        $generalActive = \App\Models\SupportQuery::where('status', 'pending')->whereHas('user', function($q) { $q->whereNull('department_id'); })->count();
                        $generalTotal = \App\Models\SupportQuery::whereHas('user', function($q) { $q->whereNull('department_id'); })->count();
                    @endphp
                    @if($generalTotal > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #f0f0f0; padding-bottom: 8px;">
                            <span style="font-size: 0.82rem; font-weight: 600; color: #2c3e50;">General Administration</span>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                @if($generalActive > 0)
                                    <span style="background-color: #ff9f43; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ $generalActive }}
                                    </span>
                                @endif
                                <span style="background-color: #bbb; color: white; border-radius: 10px; padding: 1px 6px; font-size: 0.65rem; font-weight: bold;">
                                    {{ $generalTotal }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Submit Ticket Modal -->
<div id="createTicketModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h4>Submit Support Ticket</h4>
            <button onclick="closeCreateModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        
        <form action="{{ route('support.store') }}" method="POST">
            @csrf
            
            <!-- Added Subject input field -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="subject" class="form-label">Subject / Title</label>
                <input type="text" name="subject" id="subject" class="form-control" placeholder="e.g. Dashboard access issue or File Routing delay" required>
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Describe Your Query / Inquiry</label>
                <p style="font-size: 0.72rem; color: var(--text-muted); margin-bottom: 10px;">Please be specific. Cite file tracking IDs if your query relates to active documents.</p>
                <textarea name="message" id="message" class="form-control" style="min-height: 150px;" placeholder="Type your support request details here..." required></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCreateModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createTicketModal').classList.add('active');
    }
    function closeCreateModal() {
        document.getElementById('createTicketModal').classList.remove('active');
    }

    function closeStaleBanner() {
        const banner = document.getElementById('staleBanner');
        if (banner) {
            banner.style.display = 'none';
        }
    }

    function selectAllTickets() {
        document.querySelectorAll('.ticket-select-checkbox').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllTickets() {
        document.querySelectorAll('.ticket-select-checkbox').forEach(cb => {
            cb.checked = false;
        });
    }

    function submitBulkClose() {
        const checkboxes = document.querySelectorAll('.ticket-select-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one ticket to close.');
            return;
        }
        
        if (confirm(`Are you sure you want to close the ${checkboxes.length} selected tickets?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("support.bulkClose") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ticket_ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection
