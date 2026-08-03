@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <x-admin.page-header
        title="Create Academic Period"
        subtitle="Add a new school year and semester"
        icon="bi bi-calendar-event-fill"
    />

    <form method="POST" action="{{ route('admin.storeAcademicPeriod') }}" class="space-y-6">
        @csrf

        <!-- Academic Year -->
        <div>
            <label for="academic_year" class="block font-semibold mb-1">Academic Year (ex: 2024-2025):</label>
            <input type="text" name="academic_year" id="academic_year" class="w-full border px-3 py-2 rounded" placeholder="2024-2025" required>
            @error('academic_year')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Semester Dropdown -->
        <div>
            <label for="semester" class="block font-semibold mb-1">Semester:</label>
            <select name="semester" id="semester" class="w-full border px-3 py-2 rounded" required>
                <option value="" disabled selected>-- Select Semester --</option>
                <option value="1st">1st Semester</option>
                <option value="2nd">2nd Semester</option>
                <option value="Summer">Summer</option>
            </select>
            @error('semester')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded">
                Save
            </button>
        </div>
    </form>
</div>
@endsection
