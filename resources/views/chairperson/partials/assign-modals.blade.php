{{--
    Assign Subjects Modals Partial
    
    Contains modals for assigning/unassigning subjects to instructors.
--}}

{{-- Confirm Unassign Modal --}}
<div id="confirmUnassignModal" class="modal fade" tabindex="-1" aria-labelledby="confirmUnassignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmUnassignModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Unassign
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to unassign this subject? This action cannot be undone.</p>
                <p class="fw-semibold" id="unassignSubjectName"></p>
            </div>
            <div class="modal-footer bg-light">
                <form id="unassignForm" action="{{ route('chairperson.toggleAssignedSubject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" id="unassign_subject_id">
                    <input type="hidden" name="instructor_id" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Unassign
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Assign Modal --}}
<div id="confirmAssignModal" class="modal fade" tabindex="-1" aria-labelledby="confirmAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmAssignModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> Confirm Assign
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select the instructor to assign this subject to:</p>
                <p class="fw-semibold" id="assignSubjectName"></p>
                <form id="assignForm" method="POST" action="{{ route('chairperson.storeAssignedSubject') }}">
                    @csrf
                    <input type="hidden" name="subject_id" id="assign_subject_id">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Instructor</label>
                        <select name="instructor_id" class="form-select" required>
                            <option value="">-- Choose Instructor --</option>
                            @foreach ($instructors as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
