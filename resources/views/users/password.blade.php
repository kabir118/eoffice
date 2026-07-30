@extends('layouts.app')

@section('title', 'Change Password - USTB e-Office')
@section('page_title', 'Update Profile Security')

@section('content')
    <div style="max-width: 500px; margin: 0 auto;">
        <div class="dashboard-card" style="margin-bottom: 0;">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 25px;">
                <span>Change Your Password</span>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 15px; font-size: 0.85rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.password.update') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters" required>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repeat new password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 25px;">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Update Security Password</span>
                </button>
            </form>
        </div>
    </div>
@endsection
