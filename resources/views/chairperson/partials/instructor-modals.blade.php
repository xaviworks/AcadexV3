{{--
    Instructor Modals Partial

    Contains all modals used in instructor management.
--}}

<x-modal.destructive
    id="confirmDeactivateModal"
    title="Confirm Account Deactivation"
    form=""
    form-id="deactivateForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#chairperson-instructors-section" data-close-modal="confirmDeactivateModal" data-loading-text="Deactivating..."'
>
    @csrf
    Are you sure you want to deactivate <strong id="instructorName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions destructive-text="Deactivate" />
    </x-slot:footer>
</x-modal.destructive>

<x-modal.success
    id="confirmActivateModal"
    title="Confirm Activation"
    form=""
    form-id="activateForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#chairperson-instructors-section" data-close-modal="confirmActivateModal" data-loading-text="Activating..."'
>
    @csrf
    Are you sure you want to activate <strong id="activateName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions primary-text="Activate" primary-variant="success" />
    </x-slot:footer>
</x-modal.success>

<x-modal.success
    id="confirmApproveModal"
    title="Confirm Approval"
    form=""
    form-id="approveForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#chairperson-instructors-section" data-close-modal="confirmApproveModal" data-loading-text="Approving..."'
>
    @csrf
    Are you sure you want to approve <strong id="approveName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions primary-text="Approve" primary-variant="success" />
    </x-slot:footer>
</x-modal.success>

<x-modal.destructive
    id="confirmRejectModal"
    title="Confirm Rejection"
    form=""
    form-id="rejectForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#chairperson-instructors-section" data-close-modal="confirmRejectModal" data-loading-text="Rejecting..."'
>
    @csrf
    Are you sure you want to reject <strong id="rejectName"></strong>'s account?

    <x-slot:footer>
        <x-modal.actions destructive-text="Reject" />
    </x-slot:footer>
</x-modal.destructive>

<x-modal.warning
    id="requestGEAssignmentModal"
    title="Request GE Subject Assignment"
    form=""
    form-id="requestGEForm"
    form-class="ajax-action-form"
    form-attributes='data-refresh-target="#chairperson-instructors-section" data-close-modal="requestGEAssignmentModal" data-loading-text="Requesting..."'
>
    @csrf
    <p>Are you sure you want to request GE subject assignment for <strong id="requestGEName"></strong>?</p>
    <p class="text-muted small mb-0">
        <i class="bi bi-info-circle me-1"></i>
        This request will be sent to the GE Coordinator for approval. The instructor will remain visible in your list.
    </p>

    <x-slot:footer>
        <x-modal.actions primary-text="Request Assignment" primary-variant="warning" />
    </x-slot:footer>
</x-modal.warning>
