@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="px-4 pt-4 pb-2">
        <h1 class="h4 fw-bold mb-0 d-flex align-items-center">
            <i class="bi bi-list-task text-success me-2" style="font-size: 1.5rem;"></i>
            <span>Manage Activities</span>
        </h1>
    </div>

    <div id="activity-section">
        @php
            $typeOptions = collect($activityTypes ?? [])
                ->map(fn ($type) => mb_strtolower($type))
                ->unique()
                ->values()
                ->all();

            if (empty($typeOptions)) {
                $typeOptions = ['quiz', 'ocr', 'exam'];
            }

            $formatActivityType = fn ($type) => ucwords(str_replace('_', ' ', $type));
        @endphp

        @if(count($subjects))
            <div class="px-4 py-4">
            {{-- Course Selection Form --}}
            <form method="GET" action="{{ route('instructor.activities.index') }}" class="mb-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-grow-1">
                                <label class="form-label small fw-medium mb-1">Select Course:</label>
                                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Choose Course --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject_code }} - {{ $subject->subject_description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- If a course is selected --}}
            @if(request('subject_id'))
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body px-4 py-3">
                <h2 class="h5 fw-semibold mb-3">Add New Activity</h2>

                <form method="POST" action="{{ route('instructor.activities.store') }}">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Title:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Type:</label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Select --</option>
                                @foreach($typeOptions as $type)
                                    <option value="{{ $type }}">{{ $formatActivityType($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Term:</label>
                            <select name="term" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="prelim">Prelim</option>
                                <option value="midterm">Midterm</option>
                                <option value="prefinal">Prefinal</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Number of Items:</label>
                            <input type="number" name="number_of_items" class="form-control" required min="1" value="{{ old('number_of_items', 100) }}">
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-success px-4">
                            Save Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Existing Activities --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body px-4 py-3">
                <h2 class="h5 fw-semibold mb-3">Existing Activities</h2>

                @if($activities->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover bg-white mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Title</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Term</th>
                                    <th class="text-center">Number of Items</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->title }}</td>
                                        <td class="text-center text-capitalize">{{ $activity->type }}</td>
                                        <td class="text-center text-capitalize">{{ $activity->term }}</td>
                                        <td class="text-center">{{ $activity->number_of_items }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('instructor.activities.delete', $activity->id) }}" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0 fw-semibold">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state
                        icon="bi-clipboard-x"
                        title="No Activities Found"
                        message="No activities found for this course."
                        :compact="true"
                    />
                @endif
            </div>
        </div>
            @else
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body px-4 py-4">
                        <x-empty-state
                            icon="bi-clipboard-x"
                            title="No Course Selected"
                            message="Please select a course above to manage its activities."
                        />
                    </div>
                </div>
            @endif
            </div>  <!-- closes px-4 py-4 -->
        @else
            <div class="px-4 py-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body px-4 py-4">
                        <x-empty-state
                            icon="bi-journal-x"
                            title="No Assigned Courses"
                            message="No courses have been assigned to you yet."
                        />
                    </div>
                </div>
            </div>
        @endif
    </div>  <!-- closes activity-section -->
</div>  <!-- closes container-fluid -->
@endsection
