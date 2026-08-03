@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-admin.page-header
        title="Create Course"
        subtitle="Add a new academic course or subject"
        icon="bi bi-book-fill"
    />

    <form method="POST" action="{{ route('admin.storeSubject') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold mb-1">Subject Code:</label>
            <input type="text" name="subject_code" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Subject Description:</label>
            <input type="text" name="subject_description" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Units:</label>
            <input type="number" name="units" class="w-full border px-3 py-2 rounded" min="1" required>
        </div>

        <div>
            <label class="block font-semibold mb-1">Academic Period:</label>
            <select name="academic_period_id" class="w-full border px-3 py-2 rounded" required>
                <option value="">Select Period</option>
                @foreach($academicPeriods as $period)
                    <option value="{{ $period->id }}">{{ $period->academic_year }} - {{ ucfirst($period->semester) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Department (optional):</label>
            <select name="department_id" class="w-full border px-3 py-2 rounded">
                <option value="">None</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->department_description }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Course (optional):</label>
            <select name="course_id" class="w-full border px-3 py-2 rounded">
                <option value="">None</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->course_description }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded">
                Save
            </button>
        </div>
    </form>
</div>
@endsection
