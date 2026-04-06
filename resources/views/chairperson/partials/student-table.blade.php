{{--
    Student Table Partial
    
    Usage:
    @include('chairperson.partials.student-table', [
        'students' => $studentsCollection,
        'showCourse' => true,
        'showYearLevel' => true,
        'emptyMessage' => 'No students found'
    ])
--}}

@php
    $showCourse = $showCourse ?? true;
    $showYearLevel = $showYearLevel ?? true;
    $emptyMessage = $emptyMessage ?? 'No students found.';
    $droppedStudentIds = $droppedStudentIds ?? collect();
@endphp

<div class="table-responsive bg-white shadow-sm rounded-4 p-3">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Student Name</th>
                @if($showCourse)
                    <th>Course</th>
                @endif
                @if($showYearLevel)
                    <th class="text-center">Year Level</th>
                @endif
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                @php $isDropped = isset($droppedStudentIds[$student->id]); @endphp
                <tr class="{{ $isDropped ? 'student-dropped' : 'hover:bg-light' }}" style="{{ $isDropped ? 'opacity:0.75;background-color:#fff8f8;' : '' }}">
                    <td style="{{ $isDropped ? 'border-left:4px solid #dc3545;' : '' }}">
                        <span class="{{ $isDropped ? 'text-muted' : '' }}">{{ $student->last_name }}, {{ $student->first_name }}</span>
                    </td>
                    @if($showCourse)
                        <td>{{ $student->course->course_code ?? 'N/A' }}</td>
                    @endif
                    @if($showYearLevel)
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2 rounded-pill">
                                {{ $student->formatted_year_level }}
                            </span>
                        </td>
                    @endif
                    <td class="text-center">
                        @if($isDropped)
                            <span class="badge bg-danger-subtle text-danger fw-medium px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                                <i class="bi bi-slash-circle"></i> Dropped
                            </span>
                        @else
                            <span class="badge bg-success-subtle text-success fw-medium px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle"></i> Enrolled
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + ($showCourse ? 1 : 0) + ($showYearLevel ? 1 : 0) + 1 }}" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
