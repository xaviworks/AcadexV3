@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Manage Grades</h1>

    {{-- Subject and Term Selection --}}
    <form method="GET" action="{{ route('grades.index') }}">
        <div class="mb-6 flex items-center space-x-4">
            <div>
                <label class="block text-sm font-medium mb-2">Select Subject:</label>
                <select name="subject_id" class="border rounded px-3 py-2 w-64" onchange="this.form.submit()">
                    <option value="">-- Choose Subject --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->subject_code }} - {{ $subject->subject_description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Term:</label>
                <select name="term" class="border rounded px-3 py-2" onchange="this.form.submit()">
                    @foreach(['prelim', 'midterm', 'prefinal', 'final'] as $termOption)
                        <option value="{{ $termOption }}" {{ request('term', 'prelim') == $termOption ? 'selected' : '' }}>
                            {{ ucfirst($termOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    @if($subject && count($students) > 0)
        @include('instructor.grades.partials.score-table', [
            'students' => $students,
            'activities' => $activities,
            'scores' => $scores,
            'termGrades' => $termGrades,
            'subject' => $subject,
            'term' => $term
        ])
    @else
        <x-empty-state
            icon="bi-hand-index"
            title="Select Subject & Term"
            message="Please select a subject and term to manage grades."
            :compact="true"
        />
    @endif
</div>
@endsection
