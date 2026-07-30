<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - USTB e-Office</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://ustb.edu.pk/assets/img/favicon/favicon-32x32.png">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Theme Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Left Banner Side -->
            <div class="auth-banner">
                <div class="auth-banner-header">
                    <img src="https://ustb.edu.pk/assets/img/logo.png" alt="UST Bannu Logo" class="auth-banner-logo">
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--white); margin: 0; line-height: 1;">UST Bannu</h2>
                        <span style="font-size: 0.65rem; color: var(--accent-color); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">e-Office System</span>
                    </div>
                </div>
                
                <div class="auth-banner-title">
                    <h1>Paperless<br>Administrative Workspace</h1>
                    <h2>File Tracking & Approvals</h2>
                </div>

                <div class="auth-banner-footer">
                    <p>Designed for professional administration, secure file flow, and transparent workflow operations.</p>
                </div>
            </div>

            <!-- Right Form Side -->
            <div class="auth-form-area">
                <div class="auth-form-header">
                    <h3>Sign In</h3>
                    <p>Access the E-Office administrative workspace</p>
                </div>

                <!-- Session messages -->
                @if(session('success'))
                    <div class="alert alert-success" style="padding: 8px 12px; margin-bottom: 15px; font-size: 0.8rem;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" style="padding: 8px 12px; margin-bottom: 15px; font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div style="position: relative;">
                            <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                            <input type="email" name="email" id="email" class="form-control" style="padding-left: 40px;" placeholder="you@ustb.edu.pk" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div style="position: relative;">
                            <i class="bi bi-lock" style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);"></i>
                            <input type="password" name="password" id="password" class="form-control" style="padding-left: 40px;" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 25px;">
                        <input type="checkbox" name="remember" id="remember" style="width: 16px; height: 16px; accent-color: var(--primary-color);">
                        <label for="remember" style="font-size: 0.85rem; color: var(--text-muted); cursor: pointer; user-select: none;">Remember this device</label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Sign In</span>
                    </button>
                </form>

                <div style="margin-top: 30px; text-align: center; font-size: 0.75rem; color: var(--text-muted);">
                    <p>Trouble logging in? Contact IT Resource Center (ITRC)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
