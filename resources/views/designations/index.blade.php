@extends('layouts.app')

@section('title', 'Designations Management - USTB e-Office')
@section('page_title', 'Designations Management Directory')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
        <!-- Designations Table List -->
        <div class="dashboard-card" style="margin-bottom: 0;">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <span>Designations Directory</span>
            </div>

            <div class="table-responsive">
                @if($designations->count() > 0)
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Designation ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Associated Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $designation)
                                <tr>
                                    <td style="font-weight: 600; color: var(--primary-color);">#DES-{{ $designation->id }}</td>
                                    <td style="font-weight: 600; color: var(--primary-dark);">{{ $designation->title }}</td>
                                    <td>{{ $designation->description ?? 'No description provided.' }}</td>
                                    <td>
                                        <span class="badge badge-status-approved" style="font-size: 0.72rem; padding: 3px 8px;">
                                            {{ $designation->users_count }} Staff Members
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick='openEditModal(@json($designation))' class="btn btn-primary btn-sm" style="padding: 4px 8px;" title="Edit Designation">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('designations.destroy', $designation->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this designation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; background-color: var(--priority-most-urgent); border-color: var(--priority-most-urgent); color: white;" title="Delete Designation">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top: 15px;">
                        {{ $designations->links() }}
                    </div>
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="bi bi-award" style="font-size: 2.5rem; display: block; margin-bottom: 12px; color: var(--border-color);"></i>
                        <p style="font-size: 0.92rem; margin: 0;">No designations found. Please create one.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Add Designation Form -->
        <div class="dashboard-card" style="margin-bottom: 0;">
            <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
                <span>Create Designation</span>
            </div>

            <form action="{{ route('designations.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="title" class="form-label">Designation Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Lecturer, Registrar, Director IT" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description (Optional)</label>
                    <textarea name="description" id="description" class="form-control" style="min-height: 80px;" placeholder="Brief details about the designation..."></textarea>
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Create Designation</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Designation Modal -->
    <div id="editDesignationModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Edit Designation</h4>
                <button onclick="closeEditModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_title" class="form-label">Designation Title</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" style="min-height: 80px;"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openEditModal(designation) {
            const form = document.getElementById('editForm');
            form.action = `{{ url('/designations') }}/${designation.id}`;
            document.getElementById('edit_title').value = designation.title;
            document.getElementById('edit_description').value = designation.description || '';
            document.getElementById('editDesignationModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editDesignationModal').classList.remove('active');
        }
    </script>
@endsection
