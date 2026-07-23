{{--
    Subject Assignment Table Partial
    
    Displays subjects with assign/unassign actions.
    
    Usage:
    @include('chairperson.partials.subject-assignment-table', [
        'subjects' => $subjectsCollection,
        'tableStyle' => 'simple', // 'simple' or 'card'
        'yearLevel' => 1, // optional, for empty message
    ])
--}}

@php
    $tableStyle = $tableStyle ?? 'simple';
    $yearLevel = $yearLevel ?? null;
    $yearLabels = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];
    $yearSuffix = $yearLevel ? ($yearLabels[$yearLevel] ?? $yearLevel . 'th') . ' Year' : '';
@endphp

@if ($subjects->isNotEmpty())
    <x-data-table
        :title="$yearSuffix ? $yearSuffix . ' Subjects' : 'Subject Assignments'"
        subtitle="Assign instructors and review current teaching loads"
        icon="bi-journal-bookmark"
    >
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Description</th>
                    <th>Assigned Instructor</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $subject)
                    <tr>
                        <td class="fw-medium">{{ $subject->subject_code }}</td>
                        <td>{{ $subject->subject_description }}</td>
                        <td>
                            @if($subject->instructor)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-check-fill text-success me-2"></i>
                                    <span>{{ $subject->instructor->name }}</span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($subject->instructor)
                                <button type="button"
                                    class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmUnassignModal"
                                    data-subject-id="{{ $subject->id }}"
                                    data-subject-name="{{ $subject->subject_code }} - {{ $subject->subject_description }}">
                                    <i class="bi bi-x-circle me-1"></i> Unassign
                                </button>
                            @else
                                <button type="button"
                                    class="btn btn-success shadow-sm btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmAssignModal"
                                    data-subject-id="{{ $subject->id }}"
                                    data-subject-name="{{ $subject->subject_code }} - {{ $subject->subject_description }}">
                                    <i class="bi bi-person-plus me-1"></i> Assign
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
    </x-data-table>
@else
    <x-empty-state
        icon="bi-journal-x"
        title="No Subjects Available"
        message="There are no subjects available{{ $yearLevel ? ' for ' . $yearSuffix : '' }}."
    />
@endif
