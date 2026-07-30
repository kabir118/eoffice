<div class="ticket-row">
    <div class="ticket-left-block">
        <!-- Initials Avatar -->
        <div class="ticket-avatar">
            @php
                $initials = '';
                if ($ticket->user) {
                    $parts = explode(' ', $ticket->user->name);
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                } else {
                    $initials = 'ST';
                }
            @endphp
            {{ $initials }}
        </div>
        
        <!-- Details Column -->
        <div class="ticket-details">
            <span class="ticket-user">
                {{ $ticket->user ? $ticket->user->name : 'Unknown Staff' }} 
                @if($ticket->user && $ticket->user->designation)
                    &middot; {{ $ticket->user->designation->title }}
                @endif
            </span>
            
            <div class="ticket-title" onclick="window.location.href='{{ route('support.show', $ticket->id) }}'">
                <i class="bi bi-lock-fill" style="font-size: 0.85rem; color: #7f8c8d;"></i>
                <span>{{ $ticket->subject }}</span>
            </div>
            
            <div class="ticket-meta">
                <div class="ticket-meta-item">
                    <i class="bi bi-folder" style="color: #95a5a6;"></i>
                    <span>{{ $ticket->user && $ticket->user->department ? $ticket->user->department->name : 'IT Resource Center' }}</span>
                </div>
                <div class="ticket-meta-item">
                    <i class="bi bi-person" style="color: #95a5a6;"></i>
                    <span>Assigned to Super Admin</span>
                </div>
                <div class="ticket-meta-item">
                    <i class="bi bi-calendar" style="color: #95a5a6;"></i>
                    <span>Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
                <div class="ticket-meta-item">
                    <i class="bi bi-chat-text" style="color: #95a5a6;"></i>
                    <span>{{ max(0, $ticket->messages->count() - 1) }} responses</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Indicator Circle and Checkbox Outline -->
    <div style="display: flex; align-items: center; gap: 5px;">
        <span class="status-dot status-{{ $ticket->status }}" title="Status: {{ ucfirst($ticket->status) }}"></span>
        <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-select-checkbox" style="margin-left: 20px; width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary-color);">
    </div>
</div>
