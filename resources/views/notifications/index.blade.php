@extends('layouts.app')

@section('title', 'e-Notifications - USTB e-Office')
@section('page_title', 'e-Notifications Center')

@section('content')
    <div class="{{ Auth::user()->canComposeNotification() ? 'split-layout' : '' }}">
        <!-- Left Side: Notification List -->
        <div class="dashboard-card" style="{{ !Auth::user()->canComposeNotification() ? 'width: 100%;' : '' }}">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <a href="{{ route('notifications.index', ['tab' => 'active']) }}" style="font-weight: 700; font-size: 0.95rem; text-decoration: none; color: {{ ($tab ?? 'active') === 'active' ? 'var(--primary-color)' : 'var(--text-muted)' }}; border-bottom: 2px solid {{ ($tab ?? 'active') === 'active' ? 'var(--primary-color)' : 'transparent' }}; padding-bottom: 4px;">
                        <i class="bi bi-bell-fill"></i> Active Feed
                    </a>
                    @if(Auth::user()->isSuperAdmin() || Auth::user()->canComposeNotification())
                        <a href="{{ route('notifications.index', ['tab' => 'trash']) }}" style="font-weight: 700; font-size: 0.95rem; text-decoration: none; color: {{ ($tab ?? '') === 'trash' ? '#e74c3c' : 'var(--text-muted)' }}; border-bottom: 2px solid {{ ($tab ?? '') === 'trash' ? '#e74c3c' : 'transparent' }}; padding-bottom: 4px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-trash3-fill"></i> Trash
                            @if(($trashedCount ?? 0) > 0)
                                <span class="badge" style="background-color: #e74c3c; color: white; border-radius: 10px; padding: 2px 7px; font-size: 0.65rem;">{{ $trashedCount }}</span>
                            @endif
                        </a>
                    @endif
                </div>

                @if(($tab ?? 'active') === 'active' && $feed->where('type', 'general')->where('is_read', false)->count() > 0)
                    <form action="{{ route('notifications.readAll') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <i class="bi bi-check2-all"></i>
                            <span>Mark all read</span>
                        </button>
                    </form>
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                @if($feed->count() > 0)
                    @foreach($feed as $item)
                        @php
                            $borderColor = $item->is_read ? 'var(--primary-color)' : 'var(--accent-color)';
                            if ($item->type === 'file_assignment') {
                                $borderColor = $item->is_read ? 'var(--primary-dark)' : 'var(--priority-urgent)';
                            }
                        @endphp
                        
                        <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 20px; background-color: {{ $item->is_read ? 'var(--white)' : 'rgba(219, 160, 54, 0.03)' }}; position: relative; border-left: 4px solid {{ $borderColor }};">
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if($item->type === 'file_assignment')
                                        <i class="bi bi-folder-fill" style="color: {{ $item->is_read ? 'var(--primary-dark)' : 'var(--priority-urgent)' }}; font-size: 1.2rem;"></i>
                                    @else
                                        <i class="bi bi-bell-fill" style="color: {{ $item->is_read ? 'var(--primary-color)' : 'var(--accent-color)' }}; font-size: 1.2rem;"></i>
                                    @endif
                                    <h4 style="font-size: 1.02rem; font-weight: 600; color: var(--primary-dark); margin: 0;">{{ $item->title }}</h4>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    @if($item->type === 'file_assignment')
                                        <a href="{{ $item->link }}" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.7rem; background-color: var(--primary-dark);">
                                            <i class="bi bi-folder2-open"></i>
                                            <span>Open File</span>
                                        </a>
                                    @else
                                        <button onclick="viewNotification({{ json_encode($item) }})" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.7rem;">
                                            <i class="bi bi-envelope-open"></i>
                                            <span>Open</span>
                                        </button>
                                    @endif

                                    @if(($tab ?? 'active') === 'active')
                                        @if(Auth::user()->isSuperAdmin() || Auth::user()->canComposeNotification())
                                            <form action="{{ route('notifications.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Move this notification to Trash?');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.7rem; color: #e74c3c;" title="Move to Trash">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <!-- Trash Actions -->
                                        @if(Auth::user()->isSuperAdmin() || Auth::user()->canComposeNotification())
                                            <form action="{{ route('notifications.restore', $item->id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.7rem; color: #27ae60;" title="Restore Notification">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                        @endif

                                        @if(Auth::user()->isSuperAdmin())
                                            <form action="{{ route('notifications.forceDelete', $item->id) }}" method="POST" onsubmit="return confirm('Permanently delete this notification? This action cannot be undone.');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.7rem; background-color: #c0392b; border-color: #c0392b;" title="Delete Permanently">
                                                    <i class="bi bi-x-circle-fill"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <p style="font-size: 0.9rem; color: var(--text-main); margin-bottom: 12px; white-space: pre-line;">{{ $item->message }}</p>

                            <div style="display: flex; gap: 15px; font-size: 0.75rem; color: var(--text-muted); border-top: 1px dashed var(--border-color); padding-top: 10px;">
                                <span><i class="bi bi-person-fill"></i> Sender: <strong>{{ $item->sender_name }}</strong> ({{ $item->department_code }})</span>
                                <span><i class="bi bi-clock"></i> {{ $item->date->format('M d, Y h:i A') }} ({{ $item->date->diffForHumans() }})</span>
                                <span class="badge" style="font-size: 0.65rem; padding: 2px 8px; font-weight: 600; background-color: {{ $item->type === 'file_assignment' ? 'rgba(1, 55, 86, 0.08)' : 'rgba(37, 111, 136, 0.08)' }}; color: {{ $item->type === 'file_assignment' ? 'var(--primary-dark)' : 'var(--primary-color)' }};">
                                    {{ $item->type === 'file_assignment' ? 'File Assignment' : 'Announcement' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="bi bi-bell-slash" style="font-size: 3rem; display: block; margin-bottom: 12px; color: var(--text-muted);"></i>
                        <p style="font-size: 1.1rem; font-weight: 500;">{{ ($tab ?? '') === 'trash' ? 'No trashed notifications found.' : 'No feed alerts found.' }}</p>
                        <p style="font-size: 0.85rem; margin-top: 5px;">{{ ($tab ?? '') === 'trash' ? 'Notifications moved to trash will be listed here.' : 'File transfers and announcements will be compiled into your feed.' }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if(Auth::user()->canComposeNotification())
            <!-- Right Side: Create/Broadcast Form (Registrar Office Only) -->
            <div class="dashboard-card" style="height: fit-content;">
                <div class="card-title">
                    <span>Compose e-Notification</span>
                </div>

                <form action="{{ route('notifications.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="title" class="form-label">Subject / Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Scheduled Maintenance Notice" required>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Alert Message Content</label>
                        <textarea name="message" id="message" class="form-control" style="min-height: 120px;" placeholder="Type your notification description here..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Departments</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 12px; background-color: var(--white);">
                            @if($departments->count() > 0)
                                <!-- Select All toggle -->
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                                    <input type="checkbox" id="select_all_recipients" onchange="toggleSelectAll(this)" style="width: 16px; height: 16px; accent-color: var(--primary-color);">
                                    <label for="select_all_recipients" style="font-size: 0.85rem; font-weight: 600; cursor: pointer; user-select: none;">Select All Departments</label>
                                </div>
                                <!-- Department checklists -->
                                @foreach($departments as $dept)
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                        <input type="checkbox" name="departments[]" id="dept_{{ $dept->id }}" value="{{ $dept->id }}" class="recipient-checkbox" style="width: 16px; height: 16px; accent-color: var(--primary-color);">
                                        <label for="dept_{{ $dept->id }}" style="font-size: 0.85rem; cursor: pointer; user-select: none;">
                                            {{ $dept->name }} <span style="font-size: 0.72rem; color: var(--text-muted);">({{ $dept->code }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            @else
                                <p style="font-size: 0.8rem; color: var(--text-muted);">No departments configured.</p>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center;">
                        <i class="bi bi-broadcast"></i>
                        <span>Broadcast Notification</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- View Notification Modal Overlay -->
    <div id="viewNotifModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h4 id="notif_modal_title">Notification</h4>
                <button onclick="closeNotifModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div style="margin-bottom: 20px;">
                <p id="notif_modal_message" style="font-size: 0.95rem; color: var(--text-main); white-space: pre-line; margin-bottom: 15px;"></p>
                <div style="font-size: 0.78rem; color: var(--text-muted); border-top: 1px dashed var(--border-color); padding-top: 10px;">
                    <span id="notif_modal_sender"></span><br>
                    <span id="notif_modal_date"></span>
                </div>
            </div>
            <div class="modal-footer">
                <form id="notif_modal_form" method="POST">
                    @csrf
                    <button type="submit" id="notif_modal_btn" class="btn btn-primary">Mark Read & Close</button>
                </form>
                <button type="button" id="notif_modal_close_btn" onclick="closeNotifModal()" class="btn btn-secondary" style="display: none;">Close</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleSelectAll(master) {
            const checkboxes = document.querySelectorAll('.recipient-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
            });
        }

        function viewNotification(item) {
            document.getElementById('notif_modal_title').textContent = item.title;
            document.getElementById('notif_modal_message').textContent = item.message;
            document.getElementById('notif_modal_sender').innerHTML = '<i class="bi bi-person-fill"></i> Sender: <strong>' + item.sender_name + '</strong> (' + item.department_code + ')';
            
            // Handle date formatting
            const d = new Date(item.date);
            document.getElementById('notif_modal_date').innerHTML = '<i class="bi bi-clock"></i> Sent: ' + d.toLocaleString();
            
            const form = document.getElementById('notif_modal_form');
            const closeBtn = document.getElementById('notif_modal_close_btn');
            
            if (!item.is_read) {
                form.action = `{{ url('/notifications') }}/${item.id}/read`;
                form.style.display = 'block';
                closeBtn.style.display = 'none';
            } else {
                form.style.display = 'none';
                closeBtn.style.display = 'block';
            }
            
            document.getElementById('viewNotifModal').classList.add('active');
        }

        function closeNotifModal() {
            document.getElementById('viewNotifModal').classList.remove('active');
        }
    </script>
@endsection
