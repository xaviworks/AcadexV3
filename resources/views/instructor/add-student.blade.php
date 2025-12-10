@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Enroll New Student</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <script>notify.success('{{ session('success') }}');</script>
    @endif

    {{-- Enroll Student Form --}}
    <form action="{{ route('instructor.students.store') }}" method="POST" class="space-y-6">
        @csrf

    {{-- First Name --}}
    <div>
        <label class="block text-gray-700 font-medium mb-1">
            First Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="first_name" class="w-full border px-3 py-2 rounded" value="{{ old('first_name') }}" required>
        @error('first_name')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Middle Name --}}
    <div>
        <label class="block text-gray-700 font-medium mb-1">
            Middle Name
        </label>
        <input type="text" name="middle_name" class="w-full border px-3 py-2 rounded" value="{{ old('middle_name') }}">
        @error('middle_name')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Last Name --}}
    <div>
        <label class="block text-gray-700 font-medium mb-1">
            Last Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="last_name" class="w-full border px-3 py-2 rounded" value="{{ old('last_name') }}" required>
        @error('last_name')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>

        {{-- Year Level --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Year Level <span class="text-red-500">*</span></label>
            <select name="year_level" class="w-full border px-3 py-2 rounded" required>
                <option value="">-- Select Year Level --</option>
                @foreach([1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'] as $level => $label)
                    <option value="{{ $level }}" {{ old('year_level') == $level ? 'selected' : '' }}>
                        {{ $label }} Year
                    </option>
                @endforeach
            </select>
            @error('year_level')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Assign Subject --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Assign Subject <span class="text-red-500">*</span></label>
            <select name="subject_id" class="w-full border px-3 py-2 rounded" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->subject_code }} - {{ $subject->subject_description }}
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Assign Course --}}
        <div>
            <label class="block text-gray-700 font-medium mb-1">Assign Course <span class="text-red-500">*</span></label>
            <select name="course_id" class="w-full border px-3 py-2 rounded" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->course_code }} - {{ $course->course_description }}
                    </option>
                @endforeach
            </select>
            @error('course_id')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                Enroll Student
            </button>
        </div>
    </form>
</div>
@endsection
