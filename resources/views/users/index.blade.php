@extends('layouts.app')

@section('title', 'Users - USTB e-Office')
@section('page_title', 'User Management')

@section('content')
    <div class="split-layout">
        <!-- Left Pane: Users list -->
        <div class="dashboard-card">
            <div class="card-title">
                <span>Administrative & Staff Accounts</span>
            </div>

            <div class="table-responsive">
                @if($users->count() > 0)
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td style="font-weight: 500;">{{ $user->name }}</td>
                                    <td style="font-weight: 500; color: var(--primary-color);">{{ $user->designation ? $user->designation->title : 'N/A' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge" style="font-weight: 600; background-color: {{ $user->role === 'super_admin' ? 'rgba(231, 76, 60, 0.1)' : 'rgba(37, 111, 136, 0.1)' }}; color: {{ $user->role === 'super_admin' ? 'var(--priority-most-urgent)' : 'var(--primary-color)' }};">
                                            {{ $user->role === 'super_admin' ? 'Super Admin' : 'Staff' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">
                                            {{ $user->department ? $user->department->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <!-- Edit Trigger -->
                                            <button onclick="openEditModal({{ json_encode($user) }})" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            
                                            <!-- Delete Form -->
                                            @if($user->id !== Auth::id())
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <i class="bi bi-people" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                        <p>No user accounts configured.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Pane: Add User form -->
        <div class="dashboard-card" style="height: fit-content;">
            <div class="card-title">
                <span>Add New User</span>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Kabir Ahmad" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g. kabirbangash118@ustb.edu.pk" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Temporary Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters" required>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">System Role</label>
                    <select name="role" id="role" class="form-control" onchange="toggleDepartmentSelect(this.value, 'dept_group')" required>
                        <option value="staff" selected>Staff Member</option>
                        <option value="super_admin">Super Administrator</option>
                    </select>
                </div>

                <div class="form-group" id="dept_group">
                    <label for="department_id" class="form-label">Assigned Department</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-top: 15px; margin-bottom: 20px;">
                    <label for="designation_id" class="form-label">User Designation</label>
                    <select name="designation_id" id="designation_id" class="form-control">
                        <option value="">-- Choose Designation --</option>
                        @foreach($designations as $desg)
                            <option value="{{ $desg->id }}">{{ $desg->title }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-person-plus"></i>
                    <span>Save User Account</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal Overlay -->
    <div id="editUserModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Modify User Account</h4>
                <button onclick="closeEditModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="edit_name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_password" class="form-label">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_password" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label for="edit_role" class="form-label">System Role</label>
                    <select name="role" id="edit_role" class="form-control" onchange="toggleDepartmentSelect(this.value, 'edit_dept_group')" required>
                        <option value="staff">Staff Member</option>
                        <option value="super_admin">Super Administrator</option>
                    </select>
                </div>

                <div class="form-group" id="edit_dept_group">
                    <label for="edit_department_id" class="form-label">Assigned Department</label>
                    <select name="department_id" id="edit_department_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label for="edit_designation_id" class="form-label">User Designation</label>
                    <select name="designation_id" id="edit_designation_id" class="form-control">
                        <option value="">-- Choose Designation --</option>
                        @foreach($designations as $desg)
                            <option value="{{ $desg->id }}">{{ $desg->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleDepartmentSelect(role, elementId) {
            const deptGroup = document.getElementById(elementId);
            if (role === 'super_admin') {
                deptGroup.style.display = 'none';
            } else {
                deptGroup.style.display = 'block';
            }
        }

        function openEditModal(user) {
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_department_id').value = user.department_id || '';
            document.getElementById('edit_designation_id').value = user.designation_id || '';
            
            toggleDepartmentSelect(user.role, 'edit_dept_group');
            
            // Set dynamic form action URL
            const form = document.getElementById('editUserForm');
            form.action = `{{ url('/users') }}/${user.id}`;
            
            // Show modal
            document.getElementById('editUserModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.remove('active');
        }
    </script>
@endsection
