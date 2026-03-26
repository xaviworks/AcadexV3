@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="h3 mb-4">Select Course to Manage Scores</h1>

    <div class="row">
        @foreach($subjects as $subject)
            <div class="col-md-4 mb-4">
                <a href="{{ route('instructor.manageScores', ['subject_id' => $subject->id]) }}"
                   class="card shadow-sm h-100 text-decoration-none text-dark border-0">
                    <div class="card-body bg-light rounded">
                        <div class="p-3 rounded text-white text-center mb-3" style="background-color: #007bff;">
                            <h5 class="mb-0">{{ $subject->subject_code }}</h5>
                        </div>
                        <p class="fw-semibold">{{ $subject->subject_description }}</p>
                        <p class="text-muted small">Instructor: {{ $subject->instructor->name ?? 'N/A' }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
