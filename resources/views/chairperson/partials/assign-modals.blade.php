{{--
    Assign Subjects Modals Partial

    Contains modals for assigning/unassigning subjects to instructors.
--}}

<x-modal.destructive
    id="confirmUnassignModal"
    title="Confirm Unassign"
    form="{{ route('chairperson.toggleAssignedSubject') }}"
    form-id="unassignForm"
>
    @csrf
    <x-slot:icon><i class="bi bi-exclamation-triangle-fill me-1"></i></x-slot:icon>
    <p>Are you sure you want to unassign this subject? This action cannot be undone.</p>
    <p class="fw-semibold" id="unassignSubjectName"></p>
    <input type="hidden" name="subject_id" id="unassign_subject_id">
    <input type="hidden" name="instructor_id" value="">

    <x-slot:footer>
        <x-modal.actions destructive-text="Unassign" />
    </x-slot:footer>
</x-modal.destructive>

<x-modal.form
    id="confirmAssignModal"
    title="Confirm Assign"
    size="medium"
    form="{{ route('chairperson.storeAssignedSubject') }}"
    form-id="assignForm"
>
    @csrf
    <x-slot:icon><i class="bi bi-check-circle-fill me-1"></i></x-slot:icon>
    <p>Select the instructor to assign this subject to:</p>
    <p class="fw-semibold" id="assignSubjectName"></p>
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

    <x-slot:footer>
        <x-modal.actions primary-text="Assign" primary-variant="success" />
    </x-slot:footer>
</x-modal.form>
