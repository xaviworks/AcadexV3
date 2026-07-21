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

<x-modal.form
    id="addActivityModal"
    title="Add New Activity"
    form="{{ route('instructor.activities.store') }}"
    :show="$errors->any()"
>
    @csrf
    <div class="row g-3">
        {{-- Hidden subject_id & period if coming from filtered --}}
        @if(request('subject_id') && request('term'))
            <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
            <input type="hidden" name="term" value="{{ request('term') }}">
        @else
            <div class="col-md-6">
                <label class="form-label">Select Course</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->subject_code }} - {{ $subject->subject_description }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Select Period</label>
                <select name="term" class="form-select" required>
                    <option value="">-- Select Period --</option>
                    @foreach(['prelim','midterm','prefinal','final'] as $termOption)
                        <option value="{{ $termOption }}" {{ old('term') == $termOption ? 'selected' : '' }}>
                            {{ ucfirst($termOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-6">
            <label class="form-label">Activity Type</label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($typeOptions as $type)
                    <option value="{{ $type }}" {{ mb_strtolower(old('type', '')) == $type ? 'selected' : '' }}>
                        {{ $formatActivityType($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Number of Items</label>
            <input type="number" name="number_of_items" class="form-control" min="1" value="{{ old('number_of_items', 100) }}" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Course Outcome</label>
            <select name="course_outcome_id" class="form-select">
                <option value="">-- Select Course Outcome --</option>
                @if(isset($courseOutcomes))
                    @foreach($courseOutcomes as $co)
                        <option value="{{ $co->id }}">{{ $co->co_code }} - {{ $co->co_identifier }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <x-slot:footer>
        <x-modal.actions primary-text="Save Activity" primary-variant="success" />
    </x-slot:footer>
</x-modal.form>
