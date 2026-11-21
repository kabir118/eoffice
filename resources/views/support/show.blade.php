@extends('layouts.app')

@section('title', 'Suggestion / Issue Details - USTB e-Office')
@section('page_title', 'Suggestion / Issue Conversation')

@section('styles')
<style>
    .ticket-details-layout {
        display: flex;
        gap: 30px;
        align-items: flex-start;
        width: 100%;
    }
    .thread-column {
        flex-grow: 2;
        width: 68%;
    }
    .sidebar-column {
        width: 30%;
        min-width: 280px;
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .thread-card {
        background-color: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 25px;
    }
    
    /* Blue header info banner */
    .thread-blue-header {
        background-color: var(--primary-color);
        color: white;
        padding: 10px 20px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .thread-subject-block {
        padding: 25px 20px;
        border-bottom: 1px solid var(--border-color);
        background-color: #fafafa;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
    }
    .thread-subject {
        font-size: 1.35rem;
        font-weight: 500;
        color: #2c3e50;
        margin: 0;
        line-height: 1.4;
    }
    .thread-action-btn {
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #666;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .thread-action-btn:hover {
        background-color: #f5f5f5;
        border-color: #ccc;
    }

    /* Tabs Bar */
    .thread-tabs-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f1f2f6;
        border-bottom: 1px solid var(--border-color);
        padding: 0 10px;
    }
    .tab-action-btn {
        background: none;
        border: none;
        padding: 14px 18px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #555;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
    }
    .tab-action-btn:hover {
        color: var(--primary-color);
    }
    .tab-action-btn.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background-color: var(--white);
    }

    /* Individual Posts */
    .thread-posts-list {
        background-color: #ffffff;
    }
    .message-post {
        display: flex;
        gap: 20px;
        padding: 25px;
        border-bottom: 1px solid var(--border-color);
    }
    .message-post-sidebar {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 75px;
        flex-shrink: 0;
    }
    .message-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background-color: #f0f3f6;
        border: 1px solid #e1e8ed;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 8px;
    }
    .sidebar-stats {
        font-size: 0.72rem;
        color: #888;
        margin-bottom: 10px;
    }
    .btn-like {
        background-color: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 0.72rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-like:hover {
        background-color: #f1f5f9;
        color: var(--primary-color);
    }
    .message-post-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .message-post-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed #f0f0f0;
        padding-bottom: 8px;
        margin-bottom: 4px;
    }
    .message-sender-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: #2c3e50;
    }
    .message-sender-label {
        font-size: 0.78rem;
        color: #999;
        font-weight: 500;
        margin-left: 5px;
    }
    .message-post-date {
        font-size: 0.76rem;
        color: #888;
    }
    .message-content {
        font-size: 0.92rem;
        color: #333;
        line-height: 1.6;
        margin: 0;
        white-space: pre-line;
    }
    .message-actions-bar {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        align-items: center;
    }

    /* Reply Editor Card */
    .editor-card {
        background-color: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 20px;
    }

    /* Right Sidebar Cards */
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
    .select-mimic {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border-color);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.82rem;
        color: #333;
        font-weight: 500;
        cursor: pointer;
    }
    .select-mimic:hover {
        background-color: #fafafa;
    }
    .side-meta-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .side-meta-table td {
        padding: 8px 0;
        border-bottom: 1px dashed #f5f5f5;
    }
    .label-cell {
        color: #777;
        font-weight: 600;
        width: 90px;
    }
    .value-cell {
        color: #333;
        font-weight: 700;
        text-align: right;
    }
</style>
@endsection

@section('content')
<div class="ticket-details-layout">
    <!-- Left Column: Messages Thread and Conversation -->
    <div class="thread-column">
        
        <div class="thread-card">
            <!-- Blue Header Info Banner -->
            <div class="thread-blue-header">
                <i class="bi bi-lock-fill"></i>
                <span>Private Ticket #{{ $query->id }}</span>
            </div>
            
            <!-- Subject Display Block -->
            <div class="thread-subject-block">
                <h2 class="thread-subject">{{ $query->subject }}</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if($query->status !== 'solved')
                        <form action="{{ route('support.reply', $query->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="status" value="read">
                            <button type="submit" class="thread-action-btn" style="background-color: var(--primary-color); color: white; border-color: var(--primary-color);">
                                <i class="bi bi-eye-fill"></i>
                                <span>Make it Read (No Action Needed)</span>
                            </button>
                        </form>
                    @else
                        <span class="badge" style="background-color: rgba(40, 199, 111, 0.1); color: #28c76f; font-weight: 600; padding: 6px 12px; font-size: 0.78rem;">
                            <i class="bi bi-check-circle-fill"></i> Solved (Closed)
                        </span>
                    @endif
                    <button class="thread-action-btn" style="padding: 6px;"><i class="bi bi-star"></i></button>
                </div>
            </div>
            
            <!-- Tabs Bar -->
            <div class="thread-tabs-bar">
                <div style="display: flex; gap: 10px;">
                    <!-- Post a Reply Toggle Button -->
                    <button id="btn-post-reply" onclick="toggleReplyCard()" class="tab-action-btn">
                        <i class="bi bi-chat-left-text-fill" style="color: var(--primary-color);"></i>
                        <span>Post a Reply</span>
                    </button>
                </div>
            </div>
            
            <!-- Post Reply Text Editor (Appears only here via the Post a Reply button) -->
            @if($query->status !== 'solved')
                <div id="replyEditorCard" class="editor-card" style="display: none; border-bottom: 1px solid var(--border-color); background-color: #fdfdfd; border-radius: 0; padding: 25px;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #2c3e50; margin-top: 0; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Write Response</h4>
                    <form action="{{ route('support.postMessage', $query->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <textarea name="message" class="form-control" style="min-height: 140px; font-family: inherit; font-size: 0.9rem;" placeholder="Type your response to the conversation here..." required></textarea>
                        </div>
                        
                        @if(Auth::user()->isSuperAdmin())
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <label for="ticket_status" style="font-size: 0.8rem; font-weight: 600; color: #555;">Update Status:</label>
                                    <select name="status" id="ticket_status" class="form-control" style="padding: 4px 10px; width: 120px; font-size: 0.8rem;" onchange="updateTicketStatus(this.value)">
                                        <option value="pending" {{ $query->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="read" {{ $query->status == 'read' ? 'selected' : '' }}>Read</option>
                                        <option value="replied" {{ $query->status == 'replied' ? 'selected' : '' }}>Replied</option>
                                        <option value="solved" {{ $query->status == 'solved' ? 'selected' : '' }}>Solved</option>
                                    </select>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="button" onclick="toggleReplyCard()" class="btn btn-secondary btn-sm">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                                </div>
                            </div>
                        @else
                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 15px;">
                                <button type="button" onclick="toggleReplyCard()" class="btn btn-secondary btn-sm">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif

            <!-- Messages List Thread -->
            <div class="thread-posts-list">
                @foreach($messages as $index => $msg)
                    @php
                        $isUserAdmin = $msg->user->role === 'super_admin';
                        
                        $initials = '';
                        $parts = explode(' ', $msg->user->name);
                        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    @endphp
                    <div class="message-post">
                        <!-- Message Sidebar Stats Column -->
                        <div class="message-post-sidebar">
                            <div class="message-avatar">
                                {{ $initials }}
                            </div>
                        </div>
                        
                        <!-- Message Details Column -->
                        <div class="message-post-body">
                            <div class="message-post-header">
                                <span class="message-sender-name">
                                    {{ $msg->user->name }}
                                    @if($msg->user && $msg->user->designation)
                                        <span style="font-size: 0.76rem; color: var(--primary-dark); font-weight: 600;">({{ $msg->user->designation->title }})</span>
                                    @endif
                                    <span class="message-sender-label">{{ $isUserAdmin ? 'replied' : 'submitted' }}</span>
                                </span>
                                <span class="message-post-date">
                                    {{ $msg->created_at->diffForHumans() }} on {{ $msg->created_at->format('F d, Y \a\t h:i A') }}
                                </span>
                            </div>
                            
                            <p class="message-content">{{ $msg->message }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Solved / Closed Banner (Always at the bottom if solved) -->
        @if($query->status === 'solved')
            <div style="background-color: #fafafa; border: 1px solid var(--border-color); padding: 25px; border-radius: var(--border-radius); text-align: center; color: var(--text-muted); margin-bottom: 25px;">
                <i class="bi bi-lock-fill" style="font-size: 1.8rem; display: block; margin-bottom: 8px; color: #a0aec0;"></i>
                <p style="margin: 0; font-size: 0.88rem; font-weight: 600; margin-bottom: 15px;">This support ticket is closed and solved. No further replies can be submitted.</p>
                
                @if(Auth::user()->isSuperAdmin())
                    <form action="{{ route('support.reply', $query->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                            <i class="bi bi-unlock-fill"></i>
                            <span>Reopen Ticket</span>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Right Column: Ticket Sidebar Details -->
    <div class="sidebar-column">
        <!-- Ticket Details Card -->
        <div class="sidebar-card">
            <h4 class="sidebar-card-title">Ticket Details</h4>
            
            <div>
                @if(Auth::user()->isSuperAdmin())
                    <div class="select-mimic" onclick="focusStatusField()">
                        <span>Status: {{ ucfirst($query->status) }}</span>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                @else
                    <div style="border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 4px; font-size: 0.82rem; color: #333; font-weight: 600; background-color: #fdfdfd;">
                        <span>Status: {{ ucfirst($query->status) }}</span>
                    </div>
                @endif
            </div>

            <table class="side-meta-table">
                <tr>
                    <td class="label-cell">Requester</td>
                    <td class="value-cell">
                        {{ $query->user ? $query->user->name : 'N/A' }}
                        @if($query->user && $query->user->designation)
                            <br><span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">({{ $query->user->designation->title }})</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label-cell">Email</td>
                    <td class="value-cell" style="word-break: break-all;">{{ $query->user ? $query->user->email : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Category</td>
                    <td class="value-cell">{{ $query->user && $query->user->department ? $query->user->department->code : 'Admin' }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Created</td>
                    <td class="value-cell">{{ $query->created_at->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="label-cell">Response</td>
                    <td class="value-cell">{{ $query->created_at->diffForHumans() }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Ticket Tags Card -->
        <div class="sidebar-card">
            <h4 class="sidebar-card-title">Ticket Tags</h4>
            <div style="border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 4px; font-size: 0.8rem; color: #7f8c8d; background-color: #fafafa;">
                <span>Suggestions & Feedback</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleReplyCard() {
        const card = document.getElementById('replyEditorCard');
        const btn = document.getElementById('btn-post-reply');
        if (!card) return;
        
        if (card.style.display === 'none') {
            card.style.display = 'block';
            if (btn) btn.classList.add('active');
        } else {
            card.style.display = 'none';
            if (btn) btn.classList.remove('active');
        }
    }

    function focusStatusField() {
        // Toggle card open first if hidden
        const card = document.getElementById('replyEditorCard');
        if (card && card.style.display === 'none') {
            toggleReplyCard();
        }
        
        setTimeout(() => {
            const select = document.getElementById('ticket_status');
            if (select) {
                select.focus();
                select.style.border = '2px solid var(--primary-color)';
            }
        }, 100);
    }

    function updateTicketStatus(value) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/support') }}/{{ $query->id }}/reply`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = value;
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
