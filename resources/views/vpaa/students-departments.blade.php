@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-mortarboard me-2"></i>Students by Department
            </h2>
            <p class="text-muted mb-0">Select a department to view its students</p>
        </div>
    </div>

    @if(isset($departments) && count($departments))
        <div class="row g-4 px-4 py-2" id="department-selection">
            @foreach($departments as $dept)
                <div class="col-md-4">
                    <div
                        class="subject-card card h-100 border-0 shadow-lg rounded-4 overflow-hidden transform transition hover:scale-105 hover:shadow-xl"
                        data-url="{{ route('vpaa.students') }}?department_id={{ $dept->id }}"
                        style="cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;"
                    >
                        <div class="position-relative" style="height: 80px; background-color: #4ecd85;">
                            <div class="subject-circle position-absolute start-50 translate-middle"
                                style="top: 100%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: linear-gradient(135deg, #4da674, #023336); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                                <h5 class="mb-0 text-white fw-bold">{{ $dept->department_code }}</h5>
                            </div>
                        </div>
                        <div class="card-body pt-5 text-center">
                            <h6 class="fw-semibold mt-4 text-dark text-truncate" title="{{ $dept->department_description }}">
                                {{ $dept->department_description }}
                            </h6>
                            <div class="mt-3">
                                <a class="btn btn-success" href="{{ route('vpaa.students') }}?department_id={{ $dept->id }}">
                                    <i class="bi bi-arrow-right-circle me-1"></i> View Students
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state
            icon="bi-journal-x"
            title="No Departments Found"
            message="Departments data is required to browse students."
        />
    @endif
</div>
@endsection

{{-- Styles: resources/css/vpaa/cards.css --}}
{{-- JavaScript: resources/js/pages/vpaa/students-departments.js --}}
