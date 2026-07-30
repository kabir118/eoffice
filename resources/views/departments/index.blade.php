@extends('layouts.app')

@section('title', 'Departments - USTB e-Office')
@section('page_title', 'Department Management')

@section('content')
    <div class="split-layout">
        <!-- Left Pane: Departments list -->
        <div class="dashboard-card">
            <div class="card-title">
                <span>Existing University Departments</span>
            </div>

            <div class="table-responsive">
                @if($departments->count() > 0)
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Staff Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $dept)
                                <tr>
                                    <td style="font-weight: 600; color: var(--primary-color);">{{ $dept->code }}</td>
                                    <td style="font-weight: 500;">{{ $dept->name }}</td>
                                    <td style="font-size: 0.82rem; color: var(--text-muted);">{{ Str::limit($dept->description, 50) }}</td>
                                    <td>
                                        <span class="badge" style="background-color: rgba(1, 55, 86, 0.08); color: var(--primary-dark); font-weight: 600;">
                                            {{ $dept->users_count }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <!-- Edit Trigger -->
                                            <button onclick="openEditModal({{ json_encode($dept) }})" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            
                                            <!-- Delete Form -->
                                            <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <i class="bi bi-building" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                        <p>No departments configured.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Pane: Add Department form -->
        <div class="dashboard-card" style="height: fit-content;">
            <div class="card-title">
                <span>Create New Department</span>
            </div>

            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Department Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Directorate of Finance" required>
                </div>

                <div class="form-group">
                    <label for="code" class="form-label">Department Code (Short)</label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="e.g. DF" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Brief outline of operations..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-plus-circle"></i>
                    <span>Save Department</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Modal Overlay -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Modify Department</h4>
                <button onclick="closeEditModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="edit_name" class="form-label">Department Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_code" class="form-label">Department Code</label>
                    <input type="text" name="code" id="edit_code" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Details</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openEditModal(department) {
            document.getElementById('edit_name').value = department.name;
            document.getElementById('edit_code').value = department.code;
            document.getElementById('edit_description').value = department.description || '';
            
            // Set dynamic form action URL
            const form = document.getElementById('editForm');
            form.action = `/departments/${department.id}`;
            
            // Show modal
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
    </script>
@endsection
