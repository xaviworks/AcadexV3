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
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr class="hover:bg-light">
                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
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
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + ($showCourse ? 1 : 0) + ($showYearLevel ? 1 : 0) }}" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
