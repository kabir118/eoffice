<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'USTB e-Office')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://ustb.edu.pk/assets/img/favicon/favicon-32x32.png">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Theme Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="https://ustb.edu.pk/assets/img/logo.png" alt="UST Bannu Logo" class="sidebar-logo">
                <span class="sidebar-title">e-Office Portal</span>
                <span class="sidebar-subtitle">UST Bannu</span>
            </div>
            <ul class="sidebar-menu">
                <li class="sidebar-item {{ Request::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item sidebar-has-submenu">
                    <a href="{{ route('documents.myFiles') }}" class="sidebar-link sidebar-toggle" onclick="toggleSubmenu(event, this)">
                        <i class="bi bi-folder-symlink"></i>
                        <span>My Files</span>
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-subitem {{ Request::is('documents') && Request::query('tab') != 'outbox' && Request::query('tab') != 'forwarded' && Request::query('tab') != 'completed' && Request::query('tab') != 'all' ? 'active' : '' }}">
                            <a href="{{ route('documents.index') }}" class="sidebar-sublink">
                                <i class="bi bi-inbox"></i>
                                <span>Files Assigned</span>
                            </a>
                        </li>
                        <li class="sidebar-subitem {{ Request::is('documents') && Request::query('tab') == 'outbox' ? 'active' : '' }}">
                            <a href="{{ route('documents.index', ['tab' => 'outbox']) }}" class="sidebar-sublink">
                                <i class="bi bi-send"></i>
                                <span>Files Created</span>
                            </a>
                        </li>
                        <li class="sidebar-subitem {{ Request::is('documents') && Request::query('tab') == 'forwarded' ? 'active' : '' }}">
                            <a href="{{ route('documents.index', ['tab' => 'forwarded']) }}" class="sidebar-sublink">
                                <i class="bi bi-arrow-right-circle"></i>
                                <span>Files Forwarded</span>
                            </a>
                        </li>
                        <li class="sidebar-subitem {{ Request::is('documents') && Request::query('tab') == 'completed' ? 'active' : '' }}">
                            <a href="{{ route('documents.index', ['tab' => 'completed']) }}" class="sidebar-sublink">
                                <i class="bi bi-check2-all"></i>
                                <span>Completed Files</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @if(Auth::user()->canViewAllFiles())
                    <li class="sidebar-item {{ Request::is('documents') && Request::query('tab') == 'all' ? 'active' : '' }}">
                        <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="sidebar-link">
                            <i class="bi bi-folder-fill"></i>
                            <span>All University Files</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item {{ Request::is('documents/create') ? 'active' : '' }}">
                    <a href="{{ route('documents.create') }}" class="sidebar-link">
                        <i class="bi bi-file-earmark-plus"></i>
                        <span>Initiate File</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('my-remarks') ? 'active' : '' }}">
                    <a href="{{ route('documents.myRemarks') }}" class="sidebar-link">
                        <i class="bi bi-chat-square-text"></i>
                        <span>My Comments Log</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('notifications') ? 'active' : '' }}">
                    <a href="{{ route('notifications.index') }}" class="sidebar-link">
                        <i class="bi bi-bell"></i>
                        <span>Notification Center</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('support') ? 'active' : '' }}">
                    <a href="{{ route('support.index') }}" class="sidebar-link">
                        <i class="bi bi-ticket-detailed"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
                @if(Auth::user()->isSuperAdmin())
                    <li class="sidebar-item {{ Request::is('departments') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="sidebar-link">
                            <i class="bi bi-building"></i>
                            <span>Departments</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('designations') ? 'active' : '' }}">
                        <a href="{{ route('designations.index') }}" class="sidebar-link">
                            <i class="bi bi-award"></i>
                            <span>Designations</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('users') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="sidebar-link">
                            <i class="bi bi-people"></i>
                            <span>User Accounts</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item {{ Request::is('profile/password') ? 'active' : '' }}">
                    <a href="{{ route('profile.password') }}" class="sidebar-link" style="border-top: 1px solid rgba(255,255,255,0.08); margin-top: 10px; padding-top: 15px;">
                        <i class="bi bi-key"></i>
                        <span>Change Password</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <p>&copy; {{ date('Y') }} UST Bannu</p>
                <p style="font-size: 0.65rem; color: var(--accent-color); margin-top: 3px;">E-Office System v1.0</p>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main-wrapper">
            <!-- Header Bar -->
            <header class="top-header">
                <div class="page-title-area">
                    <h2>@yield('page_title', 'Dashboard')</h2>
                </div>
                <div class="top-header-actions">
                    <!-- Notification Bell & Dropdown -->
                    @php
                        $unreadNotifications = Auth::user() ? Auth::user()->unreadNotifications()->where('title', 'NOT LIKE', 'Support Query%')->latest()->take(5)->get() : collect();
                        $unreadNotifCount = Auth::user() ? Auth::user()->unreadNotifications()->where('title', 'NOT LIKE', 'Support Query%')->count() : 0;
                    @endphp
                    <div class="notification-dropdown-wrapper" style="position: relative; display: inline-block; margin-right: 15px;">
                        <button onclick="toggleNotificationDropdown(event)" class="notification-bell-btn" style="background: none; border: none; cursor: pointer; position: relative; padding: 5px;">
                            <i class="bi bi-bell" style="font-size: 1.25rem; color: var(--primary-dark);"></i>
                            @if($unreadNotifCount > 0)
                                <span class="bell-badge" style="position: absolute; top: -2px; right: -2px; background-color: var(--priority-most-urgent); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid var(--white);">
                                    {{ $unreadNotifCount }}
                                </span>
                            @endif
                        </button>
                        
                        <!-- Dropdown Menu Card -->
                        <div id="notificationDropdownMenu" class="dropdown-menu-card" style="display: none; position: absolute; right: 0; top: 38px; width: 330px; background-color: var(--white); border: 1px solid var(--border-color); border-radius: var(--border-radius); box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; padding: 10px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; border-bottom: 1px solid var(--border-color);">
                                <span style="font-weight: 600; font-size: 0.85rem; color: var(--primary-dark);"><i class="bi bi-bell-fill"></i> e-Notifications</span>
                                @if($unreadNotifCount > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; color: var(--primary-color); font-size: 0.75rem; cursor: pointer; font-weight: 600; padding: 0;">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            
                            <div style="max-height: 250px; overflow-y: auto;">
                                @if($unreadNotifications->count() > 0)
                                    @foreach($unreadNotifications as $notif)
                                        <div style="padding: 12px 15px; border-bottom: 1px solid var(--bg-color); display: flex; flex-direction: column; gap: 4px; transition: background-color 0.2s;">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                                                <span style="font-weight: 600; font-size: 0.8rem; color: var(--text-main); line-height: 1.2;">{{ $notif->title }}</span>
                                                <span style="font-size: 0.65rem; color: var(--text-muted); white-space: nowrap;">{{ $notif->created_at->diffForHumans(null, true) }}</span>
                                            </div>
                                            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0; line-height: 1.3;">{{ Str::limit($notif->message, 70) }}</p>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                                                <span style="font-size: 0.68rem; color: var(--primary-color); font-weight: 500;">
                                                    <i class="bi bi-person"></i> {{ $notif->sender ? $notif->sender->name : 'System' }}
                                                </span>
                                                <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-accent btn-sm" style="font-size: 0.65rem; padding: 2px 6px; font-weight: 600;">
                                                        Read
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="text-align: center; padding: 25px 15px; color: var(--text-muted);">
                                        <i class="bi bi-bell-slash" style="font-size: 1.5rem; display: block; margin-bottom: 8px; color: var(--border-color);"></i>
                                        <p style="font-size: 0.78rem; margin: 0;">No unread notifications.</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div style="text-align: center; padding: 8px 15px 0 15px; border-top: 1px solid var(--border-color); margin-top: 5px;">
                                <a href="{{ route('notifications.index') }}" style="font-size: 0.8rem; color: var(--primary-color); text-decoration: none; font-weight: 600; display: block; padding: 5px 0;">
                                    View Notification Center
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Card -->
                    <div class="user-profile-menu">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-info-name">{{ Auth::user()->name }}</span>
                            <span class="user-info-role">
                                {{ Auth::user()->designation ? Auth::user()->designation->title : '' }} 
                                <span style="font-size: 0.72rem; opacity: 0.85;">
                                    ({{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->department ? Auth::user()->department->code : 'Staff') }})
                                </span>
                            </span>
                        </div>
                        
                        <!-- Logout Form Trigger -->
                        <form action="{{ route('logout') }}" method="POST" style="margin-left: 15px;">
                            @csrf
                            <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--priority-most-urgent); font-size: 1.1rem; padding: 5px;" title="Logout">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content Body -->
            <div class="content-body">
                <!-- Session Success/Error Banners -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Support Modal Overlay -->
    <div id="supportModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Contact Super Admin</h4>
                <button onclick="closeSupportModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form action="{{ route('contact.admin') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="support_message" class="form-label">Describe Your Query / Inquiry</label>
                    <textarea name="message" id="support_message" class="form-control" style="min-height: 120px;" placeholder="Type your question or support request here..." required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeSupportModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Query</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSupportModal(e) {
            e.preventDefault();
            document.getElementById('supportModal').classList.add('active');
        }

        function closeSupportModal() {
            document.getElementById('supportModal').classList.remove('active');
        }

        function toggleNotificationDropdown(event) {
            event.stopPropagation();
            const menu = document.getElementById('notificationDropdownMenu');
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
            } else {
                menu.style.display = 'none';
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(event) {
            const menu = document.getElementById('notificationDropdownMenu');
            if (menu && menu.style.display === 'block') {
                const isClickInside = menu.contains(event.target) || event.target.closest('.notification-bell-btn');
                if (!isClickInside) {
                    menu.style.display = 'none';
                }
            }
        });

        // Toggle sidebar submenu
        function toggleSubmenu(event, el) {
            event.preventDefault();
            const parentItem = el.closest('.sidebar-has-submenu');
            const submenu = parentItem.querySelector('.sidebar-submenu');
            if (parentItem.classList.contains('open')) {
                parentItem.classList.remove('open');
                submenu.style.maxHeight = '0px';
            } else {
                parentItem.classList.add('open');
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            }
        }


    </script>

    @yield('scripts')
</body>
</html>
