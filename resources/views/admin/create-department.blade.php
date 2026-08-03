@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-admin.page-header
        title="Create Department"
        subtitle="Add a new academic department"
        icon="bi bi-building-fill"
    />

    <form method="POST" action="{{ route('admin.storeDepartment') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold">Department Code:</label>
            <input type="text" name="department_code" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold">Department Description:</label>
            <input type="text" name="department_description" class="w-full border px-3 py-2 rounded" required>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded">
            Save
        </button>
    </form>
</div>
@endsection
