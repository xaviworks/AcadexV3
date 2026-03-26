@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Page Header --}}
    @include('chairperson.partials.reports-header', [
        'title' => 'Student Outcomes Summary (GE Courses)',
        'subtitle' => 'Search a student, then choose an enrolled GE course to view outcome summary',
        'icon' => 'bi-person-lines-fill',
        'academicYear' => $academicYear,
        'semester' => $semester
    ])

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Student Outcomes Reports']
    ]" />

    {{-- Student Search --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('gecoordinator.reports.co-student') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <label for="student_query" class="form-label fw-semibold mb-2">Search Student</label>
                        <div
                            class="position-relative"
                            x-data="studentSuggestionPicker({
                                suggestions: @js($studentSuggestions),
                                initialQuery: @js($studentQuery),
                                initialStudentId: @js((int) request()->input('student_id', $selectedStudent?->id ?? 0))
                            })"
                            @click.outside="close()"
                        >
                            <input type="hidden" name="student_id" :value="studentId">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="text"
                                    id="student_query"
                                    name="student_query"
                                    class="form-control"
                                    x-model="query"
                                    @input="onInput()"
                                    @focus="openFromFocus()"
                                    @keydown.arrow-down.prevent="move(1)"
                                    @keydown.arrow-up.prevent="move(-1)"
                                    @keydown.enter="onEnter($event)"
                                    @keydown.escape="close()"
                                    autocomplete="off"
                                    placeholder="Type first name, last name, or middle name"
                                >
                            </div>

                            <div
                                x-show="open && filtered.length"
                                x-transition.opacity.duration.120ms
                                class="student-fb-suggestions"
                                role="listbox"
                                aria-label="Student suggestions"
                            >
                                <div class="student-fb-suggestions-header">People</div>
                                <template x-for="(item, index) in filtered" :key="item.id">
                                    <button
                                        type="button"
                                        class="student-fb-item"
                                        :class="{ 'active': index === highlightedIndex }"
                                        @mouseenter="highlightedIndex = index"
                                        @mousedown.prevent="select(item)"
                                    >
                                        <span class="student-fb-avatar">
                                            <i class="bi bi-person-fill"></i>
                                        </span>
                                        <span class="student-fb-text" x-text="item.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedStudent)
        <div class="mt-4">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-person-fill text-success"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted text-uppercase fw-medium" style="font-size: 0.7rem; letter-spacing: 0.5px;">Selected Student</small>
                            <div class="fw-semibold text-dark">{{ $selectedStudent->last_name }}, {{ $selectedStudent->first_name }} {{ $selectedStudent->middle_name ?? '' }}</div>
                        </div>
                    </div>
                    <a href="{{ route('gecoordinator.reports.co-student', ['student_query' => $studentQuery]) }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="bi bi-arrow-repeat me-1"></i>Change Student
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 enrolled-courses-card">
                <div class="card-body p-4 p-md-5">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-stars text-success me-2"></i>Enrolled GE Courses
                    </h6>

                    @if($enrolledSubjects->isEmpty())
                        <x-empty-state
                            icon="bi-journal-x"
                            title="No Enrolled GE Courses"
                            message="This student has no enrolled GE courses in the selected academic period."
                        />
                    @else
                        <div class="row g-3 enrolled-courses-list">
                            @foreach($enrolledSubjects as $subject)
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <a
                                        href="{{ route('gecoordinator.reports.co-student', ['student_id' => $selectedStudent->id, 'subject_id' => $subject->id]) }}"
                                        class="enrolled-course-card"
                                    >
                                        <span class="enrolled-course-icon">
                                            <i class="bi bi-journal-text"></i>
                                        </span>
                                        <span class="enrolled-course-code">{{ $subject->subject_code }}</span>
                                        <span class="enrolled-course-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                        </span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($studentQuery !== '')
        <div class="mt-4">
            @if($searchedStudents->isEmpty())
                <x-empty-state
                    icon="bi-person-x"
                    title="No Students Found"
                    message="No matching student with enrolled GE courses was found. Try a different keyword."
                />
            @else
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-people text-success me-2"></i>Search Results
                        </h6>
                        <div class="row g-3">
                            @foreach($searchedStudents as $stu)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fs-5 fw-bold text-dark">
                                                {{ $stu->last_name }}, {{ $stu->first_name }} {{ $stu->middle_name ?? '' }}
                                                @if($stu->course)
                                                    <span class="text-muted fw-normal ms-2">- {{ $stu->course->course_code }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('gecoordinator.reports.co-student', ['student_query' => $studentQuery, 'student_id' => $stu->id]) }}"
                                            class="btn btn-sm btn-success rounded-pill ms-2"
                                        >
                                            Select
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="mt-4">
            <x-empty-state
                icon="bi-search"
                title="Search a Student"
                message="Find a student first, then choose one of their enrolled GE courses to view the outcome summary."
            />
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .student-fb-suggestions {
        position: absolute;
        z-index: 1050;
        top: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(16, 24, 40, 0.18);
        overflow: hidden;
        max-height: 280px;
        overflow-y: auto;
    }

    .student-fb-suggestions-header {
        font-size: 0.72rem;
        font-weight: 700;
        color: #65676b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.65rem 0.95rem 0.45rem;
        background: #f7f8fa;
        border-bottom: 1px solid #edf0f2;
    }

    .student-fb-item {
        width: 100%;
        border: 0;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.68rem 0.95rem;
        text-align: left;
        transition: background-color 0.12s ease;
    }

    .student-fb-item:hover,
    .student-fb-item.active {
        background: #edf3ff;
    }

    .student-fb-avatar {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1b74e4;
        background: #e7f0ff;
        flex-shrink: 0;
    }

    .student-fb-text {
        color: #050505;
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.25;
    }

    .enrolled-courses-card {
        min-height: 170px;
    }

    .enrolled-courses-list {
        align-items: center;
    }

    .enrolled-course-card {
        border: 1px solid #c9e9d8;
        border-radius: 14px;
        background: #f8fffb;
        min-height: 92px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        text-decoration: none;
        color: #0f5132;
        transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
    }

    .enrolled-course-card:hover {
        transform: translateY(-2px);
        border-color: #86d1ad;
        box-shadow: 0 10px 24px rgba(16, 94, 58, 0.15);
        color: #0f5132;
    }

    .enrolled-course-icon {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: #e4f7ec;
        color: #198754;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .enrolled-course-code {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .enrolled-course-arrow {
        margin-left: auto;
        color: #198754;
    }
</style>
@endpush

@push('scripts')
<script>
    function studentSuggestionPicker({ suggestions, initialQuery, initialStudentId }) {
        return {
            query: initialQuery ?? '',
            suggestions: Array.isArray(suggestions) ? suggestions : [],
            studentId: Number(initialStudentId) > 0 ? Number(initialStudentId) : '',
            filtered: [],
            open: false,
            highlightedIndex: -1,

            openFromFocus() {
                this.filter();
            },

            onInput() {
                this.studentId = '';
                this.filter();
            },

            filter() {
                const keyword = String(this.query || '').trim().toLowerCase();
                this.filtered = this.suggestions
                    .filter((item) => String(item.label || '').toLowerCase().includes(keyword))
                    .slice(0, 8);
                this.open = this.filtered.length > 0 && keyword.length > 0;
                this.highlightedIndex = this.filtered.length ? 0 : -1;
            },

            move(step) {
                if (!this.open || !this.filtered.length) {
                    return;
                }

                const next = this.highlightedIndex + step;
                if (next < 0) {
                    this.highlightedIndex = this.filtered.length - 1;
                    return;
                }

                if (next >= this.filtered.length) {
                    this.highlightedIndex = 0;
                    return;
                }

                this.highlightedIndex = next;
            },

            onEnter(event) {
                if (this.open && this.highlightedIndex >= 0 && this.filtered[this.highlightedIndex]) {
                    event.preventDefault();
                    this.select(this.filtered[this.highlightedIndex]);
                }
            },

            select(value) {
                this.query = value.label;
                this.studentId = value.id;
                this.close();
            },

            close() {
                this.open = false;
                this.highlightedIndex = -1;
            }
        };
    }
</script>
@endpush


