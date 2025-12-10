@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-book text-success me-2"></i>Course Outcomes Summary
            </h2>
            <p class="text-muted mb-0">Select a course to view detailed Course Outcome compliance</p>
        </div>
        <div>
            @if($academicYear && $semester)
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill me-2">
                    <i class="bi bi-calendar3 me-1"></i>{{ $academicYear }} – {{ $semester }}
                </span>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row g-4 px-4 py-2">
        @forelse($courses as $c)
            <div class="col-md-4">
                <div class="course-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden" 
                     data-url="{{ route('chairperson.reports.co-course') }}?course_id={{ $c->id }}"
                     style="cursor: pointer;">
                    <div class="position-relative" style="height: 80px;">
                        <div class="course-circle position-absolute start-50 translate-middle"
                            style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <h5 class="mb-0 text-white fw-bold">{{ $c->course_code }}</h5>
                        </div>
                    </div>
                    <div class="card-body pt-5 text-center">
                        <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $c->course_description }}">
                            {{ $c->course_description }}
                        </h6>
                        <div class="mt-3">
                            <span class="btn btn-success">
                                <i class="bi bi-arrow-right-circle me-1"></i> View Courses
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="bi bi-journal-x fs-1 opacity-50"></i>
                        </div>
                        <h5 class="text-muted mb-2">No Courses Found</h5>
                        <p class="text-muted mb-0">No courses available at this time.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
{{-- Styles: resources/css/chairperson/reports.css --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.course-card[data-url]').forEach(card => {
        card.addEventListener('click', function(e) {
            const url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
        
        // Add keyboard support
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const url = this.dataset.url;
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
});
</script>
@endpush
@endsection
