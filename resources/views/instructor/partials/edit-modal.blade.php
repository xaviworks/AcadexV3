<!-- resources/views/instructor/activities/partials/edit-modal.blade.php -->

<x-modal.form
    id="editActivityModal"
    title="Edit Activity"
    form="{{ route('instructor.activities.update', ['activity' => $activity->id]) }}"
    variant="default"
>
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="{{ $activity->title }}" required>
    </div>
    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select class="form-select" id="type" name="type" required>
            <option value="quiz" {{ $activity->type == 'quiz' ? 'selected' : '' }}>Quiz</option>
            <option value="ocr" {{ $activity->type == 'ocr' ? 'selected' : '' }}>OCR</option>
            <option value="exam" {{ $activity->type == 'exam' ? 'selected' : '' }}>Exam</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="number_of_items" class="form-label">Number of Items</label>
        <input type="number" class="form-control" id="number_of_items" name="number_of_items" value="{{ $activity->number_of_items }}" required>
    </div>
    <div class="mb-3">
        <label for="course_outcome_id" class="form-label">Course Outcome</label>
        <select class="form-select" id="course_outcome_id" name="course_outcome_id">
            <option value="">-- Select Course Outcome --</option>
            @if(isset($courseOutcomes))
                @foreach($courseOutcomes as $co)
                    <option value="{{ $co->id }}" @if($activity->course_outcome_id == $co->id) selected @endif>{{ $co->co_code }} - {{ $co->co_identifier }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <x-slot:footer>
        <x-modal.actions primary-text="Save Changes" />
    </x-slot:footer>
</x-modal.form>
