@extends('layouts.app')

@section('title', 'Initiate File - USTB e-Office')
@section('page_title', 'Initiate Document File')

@section('content')
    <div class="dashboard-card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
            <span>File Initiation Form</span>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Inbox</span>
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">Document/File Subject</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Requisition for IT Lab Equipment" value="{{ old('title') }}" required>
            </div>

            <!-- Priority -->
            <div class="form-group">
                <label for="priority" class="form-label">Urgency Priority Level</label>
                <select name="priority" id="priority" class="form-control" required>
                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal (Routine Processing)</option>
                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent (Requires Priority Action)</option>
                    <option value="most_urgent" {{ old('priority') == 'most_urgent' ? 'selected' : '' }}>Most Urgent (Immediate Processing Required)</option>
                </select>
            </div>

            <!-- Urgency Remarks Justification (Conditional) -->
            <div class="form-group" id="urgency-remarks-container" style="display: none; background-color: rgba(231,76,60,0.02); border: 1px solid rgba(231,76,60,0.12); padding: 15px; border-radius: var(--border-radius); margin-top: 15px;">
                <label for="urgency_remarks" class="form-label" style="color: var(--priority-urgent); font-weight: 600;"><i class="bi bi-exclamation-octagon-fill"></i> Urgency Justification / Reason</label>
                <textarea name="urgency_remarks" id="urgency_remarks" class="form-control" style="min-height: 80px;" placeholder="Please clarify the justification for classifying this file as urgent/most urgent..."></textarea>
            </div>

            <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Target Department -->
                <div class="form-group">
                    <label for="department_select" class="form-label">Forward to Department</label>
                    <select id="department_select" class="form-control" onchange="filterUsersByDepartment(this.value)" required>
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Recipient -->
                <div class="form-group">
                    <label for="forward_to" class="form-label">Forward to Staff Member</label>
                    <select name="forward_to" id="forward_to" class="form-control" required disabled>
                        <option value="">-- Choose Recipient --</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">
                    Description / Initiator's Detailed Note
                    <span style="float: right; font-size: 0.75rem; color: var(--text-muted); font-weight: 400;">
                        <span id="desc-counter">0</span> / 2000 characters
                    </span>
                </label>
                <textarea name="description" id="description" class="form-control" style="min-height: 150px;" maxlength="2000" placeholder="Provide details, justification, and reference notes for this file..." required>{{ old('description') }}</textarea>
            </div>

            <!-- Attachment -->
            <div class="form-group" style="background-color: var(--bg-color); border: 1px dashed var(--border-color); padding: 20px; border-radius: var(--border-radius);">
                <label for="attachment" class="form-label" style="margin-bottom: 5px;"><i class="bi bi-paperclip"></i> Upload Attachment Document</label>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 15px;">Allowed file formats: PDF, DOCX, XLSX, JPG, PNG, ZIP (Max size: 10MB)</p>
                <input type="file" name="attachment" id="attachment" class="form-control" style="border: none; padding: 0;">
            </div>

            @if(Auth::user()->isControllerOfExaminations())
                <!-- Confidentiality / Registrar Visibility Control (Controller of Examinations Only) -->
                <div class="form-group" style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 15px 18px; border-radius: var(--border-radius); margin-top: 15px;">
                    <input type="hidden" name="is_available_to_registrar" value="0">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_available_to_registrar" id="is_available_to_registrar" value="1" {{ old('is_available_to_registrar', '1') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer;">
                        <label for="is_available_to_registrar" style="font-weight: 600; color: var(--primary-dark); cursor: pointer; margin: 0; font-size: 0.9rem;">
                            <i class="bi bi-shield-check" style="color: var(--primary-color);"></i> Make available to Registrar Office
                        </label>
                    </div>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin: 5px 0 0 28px;">
                        When checked (default), the Registrar can view this file. Uncheck if this examination file should be restricted and hidden from the Registrar Office.
                    </p>
                </div>
            @endif

            <!-- Submit buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-fill"></i>
                    <span>Initiate & Forward File</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Array of users injected from backend
        const allUsers = @json($users);

        function filterUsersByDepartment(deptId) {
            const userSelect = document.getElementById('forward_to');
            
            // Clear existing options
            userSelect.innerHTML = '<option value="">-- Choose Recipient --</option>';
            
            if (!deptId) {
                userSelect.disabled = true;
                return;
            }

            // Filter users assigned to this department
            const filteredUsers = allUsers.filter(user => user.department_id == deptId);

            if (filteredUsers.length === 0) {
                userSelect.innerHTML = '<option value="">No staff in this department</option>';
                userSelect.disabled = true;
                return;
            }

            // Add options: Designation Title (Staff Name)
            filteredUsers.forEach(user => {
                const opt = document.createElement('option');
                opt.value = user.id;
                const desig = user.designation ? user.designation.title : null;
                opt.textContent = desig ? `${desig} (${user.name})` : user.name;
                userSelect.appendChild(opt);
            });

            userSelect.disabled = false;
        }

        // Toggle Urgency Justification visibility
        document.getElementById('priority').addEventListener('change', function() {
            const container = document.getElementById('urgency-remarks-container');
            const textarea = document.getElementById('urgency_remarks');
            if (this.value === 'urgent' || this.value === 'most_urgent') {
                container.style.display = 'block';
                textarea.required = true;
            } else {
                container.style.display = 'none';
                textarea.required = false;
                textarea.value = '';
            }
        });

        // Trigger priority change event on load to preserve old validation state
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('priority').dispatchEvent(new Event('change'));
        });

        // Dynamic Office / Department Staff Selector for CC
        const selectedCcUsers = new Map(); // id => { id, name, designation, deptCode }

        function filterCcStaffByDepartment(deptId) {
            const container = document.getElementById('cc-staff-checklist-container');
            const checklist = document.getElementById('cc-staff-checklist');
            const prompt = document.getElementById('cc-staff-prompt');
            const selectAllBtn = document.getElementById('btn-select-all-dept');

            checklist.innerHTML = '';

            if (!deptId) {
                container.style.display = 'none';
                prompt.style.display = 'block';
                selectAllBtn.style.display = 'none';
                return;
            }

            const deptUsers = allUsers.filter(u => u.department_id == deptId);

            if (deptUsers.length === 0) {
                container.style.display = 'block';
                prompt.style.display = 'none';
                selectAllBtn.style.display = 'none';
                checklist.innerHTML = '<div style="font-size:0.8rem; color:#94a3b8; grid-column: 1/-1;">No staff members found in this office.</div>';
                return;
            }

            container.style.display = 'block';
            prompt.style.display = 'none';
            selectAllBtn.style.display = 'inline-flex';

            deptUsers.forEach(u => {
                const desig = u.designation ? u.designation.title : '';
                const deptCode = u.department ? u.department.code : '';
                const isChecked = selectedCcUsers.has(u.id);

                const labelText = desig ? `<strong>${escapeHtml(desig)}</strong> (${escapeHtml(u.name)})` : `<strong>${escapeHtml(u.name)}</strong>`;

                const item = document.createElement('label');
                item.style.cssText = 'font-size: 0.8rem; display: flex; align-items: center; gap: 8px; cursor: pointer; background: white; padding: 6px 10px; border-radius: 5px; border: 1px solid #cbd5e1;';
                item.innerHTML = `
                    <input type="checkbox" value="${u.id}" ${isChecked ? 'checked' : ''} onchange="toggleCcUser(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(desig)}', '${escapeHtml(deptCode)}', this.checked)">
                    <span>${labelText}</span>
                `;
                checklist.appendChild(item);
            });
        }

        function toggleCcUser(id, name, designation, deptCode, isChecked) {
            if (isChecked) {
                selectedCcUsers.set(id, { id, name, designation, deptCode });
            } else {
                selectedCcUsers.delete(id);
            }
            renderCcBadges();
        }

        function selectAllCcStaffInDept() {
            const deptId = document.getElementById('cc_department_select').value;
            if (!deptId) return;

            const deptUsers = allUsers.filter(u => u.department_id == deptId);
            deptUsers.forEach(u => {
                const desig = u.designation ? u.designation.title : '';
                const deptCode = u.department ? u.department.code : '';
                selectedCcUsers.set(u.id, { id: u.id, name: u.name, designation: desig, deptCode: deptCode });
            });

            filterCcStaffByDepartment(deptId);
            renderCcBadges();
        }

        function removeCcUser(id) {
            selectedCcUsers.delete(id);
            const activeDeptId = document.getElementById('cc_department_select').value;
            if (activeDeptId) {
                filterCcStaffByDepartment(activeDeptId);
            }
            renderCcBadges();
        }

        function clearAllCcRecipients() {
            selectedCcUsers.clear();
            const activeDeptId = document.getElementById('cc_department_select').value;
            if (activeDeptId) {
                filterCcStaffByDepartment(activeDeptId);
            }
            renderCcBadges();
        }

        function renderCcBadges() {
            const badgesContainer = document.getElementById('cc-selected-badges');
            const hiddenInputs = document.getElementById('cc-hidden-inputs');
            const countSpan = document.getElementById('cc-selected-count');

            badgesContainer.innerHTML = '';
            hiddenInputs.innerHTML = '';
            countSpan.textContent = selectedCcUsers.size;

            if (selectedCcUsers.size === 0) {
                badgesContainer.innerHTML = '<span id="cc-empty-badge-msg" style="font-size: 0.78rem; color: #94a3b8; font-style: italic;">No individual staff members added yet.</span>';
                return;
            }

            selectedCcUsers.forEach(user => {
                const badgeText = user.designation ? `<strong>${escapeHtml(user.designation)}</strong> (${escapeHtml(user.name)})` : `<strong>${escapeHtml(user.name)}</strong>`;
                const badge = document.createElement('span');
                badge.style.cssText = 'display: inline-flex; align-items: center; gap: 6px; background-color: #f1f5f9; border: 1px solid #cbd5e1; color: #1e293b; font-size: 0.78rem; padding: 4px 10px; border-radius: 20px;';
                badge.innerHTML = `
                    <span>${badgeText}</span>
                    <i class="bi bi-x-circle-fill" onclick="removeCcUser(${user.id})" style="cursor: pointer; color: #ef4444; font-size: 0.85rem;" title="Remove"></i>
                `;
                badgesContainer.appendChild(badge);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'cc_users[]';
                hidden.value = user.id;
                hiddenInputs.appendChild(hidden);
            });
        }

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        // Description character counter
        const descTextarea = document.getElementById('description');
        const descCounter  = document.getElementById('desc-counter');
        if (descTextarea && descCounter) {
            const updateCounter = () => {
                const len = descTextarea.value.length;
                descCounter.textContent = len;
                descCounter.style.color = len > 1800 ? '#e74c3c' : len > 1500 ? '#e67e22' : 'var(--text-muted)';
            };
            descTextarea.addEventListener('input', updateCounter);
            updateCounter();
        }

    </script>
@endsection
