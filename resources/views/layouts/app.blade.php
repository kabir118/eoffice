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
    <link rel="stylesheet" href="{{ asset('css/style.css?v=4.0') }}">
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>

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
                <li class="sidebar-item {{ Request::is('documents/create') ? 'active' : '' }}">
                    <a href="{{ route('documents.create') }}" class="sidebar-link">
                        <i class="bi bi-file-earmark-plus"></i>
                        <span>Create File</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('my-files') ? 'active' : '' }}">
                    <a href="{{ route('documents.myFiles') }}" class="sidebar-link">
                        <i class="bi bi-folder-symlink"></i>
                        <span>Files Created</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('documents') && !Request::query('tab') || Request::query('tab') === 'inbox' ? 'active' : '' }}">
                    <a href="{{ route('documents.index', ['tab' => 'inbox']) }}" class="sidebar-link">
                        <i class="bi bi-person-badge"></i>
                        <span>Marked to Me</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('documents') && Request::query('tab') == 'completed' ? 'active' : '' }}">
                    <a href="{{ route('documents.index', ['tab' => 'completed']) }}" class="sidebar-link">
                        <i class="bi bi-check2-all"></i>
                        <span>Closed Files</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('my-remarks') ? 'active' : '' }}">
                    <a href="{{ route('documents.myRemarks') }}" class="sidebar-link">
                        <i class="bi bi-chat-square-text"></i>
                        <span>My Comments Log</span>
                    </a>
                </li>
                @if(Auth::user()->canViewAllFiles())
                    <li class="sidebar-item {{ Request::is('documents') && Request::query('tab') == 'all' ? 'active' : '' }}">
                        <a href="{{ route('documents.index', ['tab' => 'all']) }}" class="sidebar-link">
                            <i class="bi bi-folder-fill"></i>
                            <span>Track All University Files</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="#" onclick="openSupportModal(event)" class="sidebar-link">
                        <i class="bi bi-envelope-paper"></i>
                        <span>Suggestions / Issues</span>
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
                    <li class="sidebar-item {{ Request::is('documents') && Request::query('tab') == 'trash' ? 'active' : '' }}">
                        <a href="{{ route('documents.index', ['tab' => 'trash']) }}" class="sidebar-link">
                            <i class="bi bi-file-earmark-x"></i>
                            <span>Archived Files</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::is('archives/comments*') ? 'active' : '' }}">
                        <a href="{{ route('archives.comments') }}" class="sidebar-link">
                            <i class="bi bi-chat-square-quote"></i>
                            <span>Archived Remarks</span>
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
            <div class="sidebar-footer" style="text-align: center; padding: 25px 15px 20px; width: 100%; border-top: 1px solid rgba(255,255,255,0.08); margin-top: auto;">
                <p style="font-size: 0.8rem; color: rgba(255,255,255,0.75); margin: 0; font-weight: 500;">&copy; {{ date('Y') }} UST Bannu</p>
                <p style="font-size: 0.7rem; color: var(--accent-color); margin-top: 4px; font-weight: 600; letter-spacing: 0.5px;">E-Office System v1.0</p>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main-wrapper">
            <!-- Header Bar -->
            <header class="top-header">
                <div class="page-title-area" style="display: flex; align-items: center;">
                    <button type="button" class="mobile-toggle-btn" onclick="toggleMobileSidebar()" style="display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary-dark); cursor: pointer; margin-right: 12px;" title="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2>@yield('page_title', 'Dashboard')</h2>
                </div>
                <div class="top-header-actions">
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

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <div>
                            <strong>Please fix the following error(s):</strong>
                            <ul style="margin: 5px 0 0 0; padding-left: 20px; font-size: 0.85rem;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Suggestions / Issues Modal — sends email to admin -->
    <div id="supportModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <h4><i class="bi bi-envelope-paper" style="color: var(--primary-color); margin-right: 6px;"></i> Suggestions / Issues</h4>
                <button onclick="closeSupportModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('suggestions.send') }}" method="POST">
                @csrf

                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 18px; line-height: 1.6;">
                    Please describe your suggestion or issue clearly and it will be forwarded accordingly.
                </p>

                <div class="form-group">
                    <label for="suggestion_subject" class="form-label">
                        Subject
                        <span style="float: right; font-size: 0.72rem; color: var(--text-muted); font-weight: 400;">
                            <span id="sug-subject-counter">0</span> / 120
                        </span>
                    </label>
                    <input type="text" name="subject" id="suggestion_subject" class="form-control" maxlength="120"
                        placeholder="e.g. File routing delay or Dashboard access issue" required>
                </div>

                <div class="form-group">
                    <label for="suggestion_message" class="form-label">
                        Your Message
                        <span style="float: right; font-size: 0.72rem; color: var(--text-muted); font-weight: 400;">
                            <span id="sug-msg-counter">0</span> / 3000
                        </span>
                    </label>
                    <textarea name="message" id="suggestion_message" class="form-control" style="min-height: 140px;" maxlength="3000"
                        placeholder="Describe your suggestion or issue in detail..." required></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeSupportModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i>
                        <span>Send Message</span>
                    </button>
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

        // Live counters for suggestion modal fields
        document.addEventListener('DOMContentLoaded', function() {
            const subjInput = document.getElementById('suggestion_subject');
            const subjCounter = document.getElementById('sug-subject-counter');
            if (subjInput && subjCounter) {
                subjInput.addEventListener('input', function() {
                    const len = this.value.length;
                    subjCounter.textContent = len;
                    subjCounter.style.color = len > 100 ? '#e74c3c' : len > 80 ? '#e67e22' : 'var(--text-muted)';
                });
            }

            const msgInput = document.getElementById('suggestion_message');
            const msgCounter = document.getElementById('sug-msg-counter');
            if (msgInput && msgCounter) {
                msgInput.addEventListener('input', function() {
                    const len = this.value.length;
                    msgCounter.textContent = len;
                    msgCounter.style.color = len > 2700 ? '#e74c3c' : len > 2000 ? '#e67e22' : 'var(--text-muted)';
                });
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

        // Toggle mobile sidebar
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                sidebar.classList.toggle('mobile-active');
            }
            if (backdrop) {
                backdrop.classList.toggle('active');
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
