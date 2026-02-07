{{--
    Pending Accounts Table Partial
    
    Displays pending instructor account applications awaiting approval.
    
    Usage:
    @include('chairperson.partials.pending-accounts-table', [
        'pendingAccounts' => $pendingAccounts
    ])
--}}

<div>
    @if($pendingAccounts->isEmpty())
        <x-empty-state
            icon="bi-check-circle"
            title="All Caught Up!"
            message="There are no pending instructor applications at this time."
        />
    @else
        <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant Name</th>
                        <th>Email Address</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingAccounts as $account)
                        <tr>
                            <td>{{ $account->last_name }}, {{ $account->first_name }} {{ $account->middle_name }}</td>
                            <td>{{ $account->email }}</td>
                            <td>{{ $account->department?->department_code ?? 'N/A' }}</td>
                            <td>{{ $account->course?->course_code ?? 'N/A' }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button"
                                        class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmApproveModal"
                                        data-id="{{ $account->id }}"
                                        data-approve-url="{{ route('chairperson.accounts.approve', $account->id) }}"
                                        data-name="{{ $account->last_name }}, {{ $account->first_name }}">
                                        <i class="bi bi-check-circle-fill"></i> Approve
                                    </button>

                                    <button type="button"
                                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmRejectModal"
                                        data-id="{{ $account->id }}"
                                        data-reject-url="{{ route('chairperson.accounts.reject', $account->id) }}"
                                        data-name="{{ $account->last_name }}, {{ $account->first_name }}">
                                        <i class="bi bi-x-circle-fill"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
