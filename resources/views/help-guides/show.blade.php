@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('help-guides.index') }}" class="text-decoration-none">
                    <i class="bi bi-question-circle me-1"></i>Help Guides
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($helpGuide->title, 40) }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            {{-- Main Content Card --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="guide-icon me-3">
                            <i class="bi bi-book text-success"></i>
                        </div>
                        <div>
                            <h1 class="h4 mb-0 fw-bold">{{ $helpGuide->title }}</h1>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="guide-content">
                        {!! nl2br(e($helpGuide->content)) !!}
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: {{ $helpGuide->updated_at->format('F d, Y') }} ({{ $helpGuide->updated_at->diffForHumans() }})
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Attachment Card --}}
            @if($helpGuide->hasAttachment())
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-paperclip me-2 text-primary"></i>Attachment
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="file-icon me-3">
                                @if($helpGuide->attachmentIsPdf())
                                    <i class="bi bi-file-pdf text-danger fs-2"></i>
                                @elseif($helpGuide->attachmentIsImage())
                                    <i class="bi bi-file-image text-info fs-2"></i>
                                @else
                                    <i class="bi bi-file-earmark text-secondary fs-2"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-semibold text-truncate">{{ $helpGuide->attachment_name }}</div>
                                <small class="text-muted text-uppercase">{{ $helpGuide->attachment_extension }} file</small>
                            </div>
                        </div>
                        <a href="{{ route('admin.help-guides.download', $helpGuide) }}" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-download me-2"></i>Download File
                        </a>
                    </div>
                </div>
            @endif

            {{-- Back Button Card --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('help-guides.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-2"></i>Back to All Guides
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .guide-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background-color: #e8f5e9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .guide-icon i {
        font-size: 1.5rem;
    }
    
    .guide-content {
        font-size: 1rem;
        line-height: 1.8;
        color: #333;
        white-space: pre-wrap;
    }
    
    .file-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .breadcrumb-item a {
        color: #198754;
    }
    
    .breadcrumb-item a:hover {
        color: #146c43;
    }
</style>
@endpush
