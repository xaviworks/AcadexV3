{{--
    Instructor Table Partial
    
    Usage:
    @include('chairperson.partials.instructor-table', [
        'instructors' => $instructorsCollection,
        'filterActive' => true, // true = active only, false = inactive only
        'showGERequest' => true,
        'geRequests' => $geRequests
    ])
--}}

@php
    $filterActive = $filterActive ?? true;
    $showGERequest = $showGERequest ?? false;
    $filteredInstructors = $instructors->filter(fn($i) => $filterActive ? $i->is_active : !$i->is_active);
@endphp

@if($filteredInstructors->isEmpty())
    <div class="alert alert-warning shadow-sm rounded">
        No {{ $filterActive ? 'active' : 'inactive' }} instructors.
    </div>
@else
    <div class="table-responsive bg-white shadow-sm rounded-4 p-3">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Instructor Name</th>
                    <th>Email Address</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filteredInstructors as $instructor)
                    <tr>
                        <td>{{ $instructor->last_name }}, {{ $instructor->first_name }} {{ $instructor->middle_name }}</td>
                        <td>{{ $instructor->email }}</td>
                        <td class="text-center">
                            @if($filterActive)
                                <span class="badge border border-success text-success px-3 py-2 rounded-pill">Active</span>
                            @else
                                <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($filterActive)
                                {{-- Active instructor actions --}}
                                <div class="btn-group" role="group">
                                    @if($showGERequest)
                                        @php
                                            $geRequest = $geRequests->get($instructor->id);
                                            $hasRequest = $geRequest !== null;
                                            $requestStatus = $geRequest?->status ?? null;
                                            $canTeachGE = $instructor->can_teach_ge ?? false;
                                        @endphp
                                        
                                        @if($hasRequest && $canTeachGE)
                                            @if($requestStatus === 'pending')
                                                <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1" disabled>
                                                    <i class="bi bi-clock"></i> Pending
                                                </button>
                                            @elseif($requestStatus === 'approved')
                                                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" disabled>
                                                    <i class="bi bi-check-circle"></i> Assigned
                                                </button>
                                            @endif
                                        @elseif($hasRequest && !$canTeachGE && $requestStatus === 'pending')
                                            <button type="button" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1" disabled>
                                                <i class="bi bi-clock"></i> Pending
                                            </button>
                                        @else
                                            <button type="button"
                                                class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#requestGEAssignmentModal"
                                                data-instructor-id="{{ $instructor->id }}"
                                                data-instructor-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}"
                                                data-request-ge-url="{{ route('chairperson.requestGEAssignment', $instructor->id) }}">
                                                <i class="bi bi-journal-plus"></i> Request GE
                                            </button>
                                        @endif
                                    @endif
                                    
                                    <button type="button"
                                        class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmDeactivateModal"
                                        data-instructor-id="{{ $instructor->id }}"
                                        data-instructor-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}"
                                        data-deactivate-url="{{ route('chairperson.deactivateInstructor', $instructor->id) }}">
                                        <i class="bi bi-person-x-fill"></i> Deactivate
                                    </button>
                                </div>
                            @else
                                {{-- Inactive instructor actions --}}
                                <button type="button"
                                    class="btn btn-success btn-sm d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmActivateModal"
                                    data-id="{{ $instructor->id }}"
                                    data-name="{{ $instructor->last_name }}, {{ $instructor->first_name }}"
                                    data-activate-url="{{ route('chairperson.activateInstructor', $instructor->id) }}">
                                    <i class="bi bi-person-check-fill"></i> Activate
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
