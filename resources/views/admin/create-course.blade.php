@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-admin.page-header
        title="Create Program"
        subtitle="Add a new academic program"
        icon="bi bi-mortarboard-fill"
    />

    <form method="POST" action="{{ route('admin.storeCourse') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold">Course Code:</label>
            <input type="text" name="course_code" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold">Course Description:</label>
            <input type="text" name="course_description" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold">Department:</label>
            <select name="department_id" class="w-full border px-3 py-2 rounded" required>
                <option value="">Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->department_description }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded">
            Save
        </button>
    </form>
</div>
@endsection
