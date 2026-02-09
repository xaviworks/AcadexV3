@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Manage Student Scores</h1>

    {{-- Subject and Term Selection --}}
    <form method="GET" action="{{ route('instructor.scores') }}" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-semibold text-gray-700">Select Subject:</label>
                <select name="subject_id" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                    <option value="">-- Choose Subject --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->subject_code }} - {{ $subject->subject_description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">Select Term:</label>
                <select name="term" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                    <option value="">-- Choose Term --</option>
                    @foreach(['prelim', 'midterm', 'prefinal', 'final'] as $term)
                        <option value="{{ $term }}" {{ request('term') == $term ? 'selected' : '' }}>
                            {{ ucfirst($term) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Scores Table --}}
    @if(!empty($students) && !empty($activities))
    <form method="POST" action="{{ route('instructor.scores.save') }}">
        @csrf
        <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
        <input type="hidden" name="term" value="{{ request('term') }}">

        <div class="overflow-x-auto bg-white shadow rounded p-4">
            <table class="min-w-full table-auto border-collapse border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Student Name</th>
                        @foreach($activities as $activity)
                            <th class="border p-2 text-center">
                                {{ ucfirst($activity->type) }}<br>
                                <small>{{ $activity->title }} ({{ $activity->number_of_items }} pts)</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 font-semibold">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                            @foreach($activities as $activity)
                                <td class="border p-2 text-center">
                                    <input type="number"
                                           name="scores[{{ $student->id }}][{{ $activity->id }}]"
                                           class="w-20 border rounded px-2 py-1 text-center"
                                           min="0"
                                           max="{{ $activity->number_of_items }}"
                                           step="1"
                                           placeholder="0"
                                           required>
                                    <div class="text-xs text-gray-500 mt-1">
                                        / {{ $activity->number_of_items }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-right">
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">
                Save Scores
            </button>
        </div>
    </form>
    @elseif(request('subject_id') && request('term'))
        <x-empty-state
            icon="bi-clipboard-data"
            title="No Data Found"
            message="No students or activities found for the selected subject and term."
            :compact="true"
        />
    @endif
</div>
@endsection
