@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="{ search: '', openGuide: {{ $guides->first()?->id ?? 'null' }} }">
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 text-dark fw-bold mb-1">
            <i class="bi bi-question-circle-fill text-success me-2"></i>Help Guides
        </h1>
        <p class="text-muted mb-0">Find answers to common questions and learn how to use the system effectively.</p>
    </div>

    @if($guides->isEmpty())
        {{-- Empty State --}}
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-journal-x display-1 d-block mb-3 opacity-50"></i>
                    <h5>No Help Guides Available</h5>
                    <p class="mb-0">There are currently no help guides available for your role.</p>
                    <p class="small text-muted">Please check back later or contact your administrator for assistance.</p>
                </div>
            </div>
        </div>
    @else
        {{-- Search Box --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           x-model="search"
                           placeholder="Search help guides...">
                </div>
            </div>
        </div>

        {{-- Help Guides List --}}
        <div class="help-guides-list">
            @foreach($guides as $index => $guide)
                <div class="card shadow-sm mb-3 guide-item" 
                     x-show="search === '' || '{{ strtolower($guide->title) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes(strip_tags($guide->content))) }}'.includes(search.toLowerCase())">
                    {{-- Guide Header --}}
                    <div class="card-header bg-white py-3 cursor-pointer" 
                         @click="openGuide = openGuide === {{ $guide->id }} ? null : {{ $guide->id }}"
                         style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="guide-icon me-3">
                                    <i class="bi bi-book text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $guide->title }}</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                @if($guide->hasAttachment())
                                    <span class="badge bg-info bg-opacity-10 text-info me-3" title="Has attachment">
                                        <i class="bi bi-paperclip"></i>
                                    </span>
                                @endif
                                <i class="bi transition-transform" 
                                   :class="openGuide === {{ $guide->id }} ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Guide Content --}}
                    <div class="card-body border-top guide-body" x-show="openGuide === {{ $guide->id }}" x-transition>
                        {{-- Content --}}
                        <div class="guide-content mb-3">
                            {!! nl2br(e($guide->content)) !!}
                        </div>
                        
                        {{-- Attachment (PDF Preview) --}}
                        @if($guide->hasAttachment())
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="text-muted small fw-semibold mb-2">
                                    <i class="bi bi-file-pdf me-1 text-danger"></i> PDF Attachment
                                </h6>
                                
                                {{-- PDF Preview --}}
                                <div class="pdf-preview mb-3 border rounded">
                                    <iframe src="{{ route('help-guides.preview', $guide) }}" 
                                            class="w-100 rounded" 
                                            style="height: 400px; border: none;"
                                            title="PDF Preview: {{ $guide->attachment_name }}"></iframe>
                                </div>
                                
                                <a href="{{ route('help-guides.download', $guide) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-download me-1"></i>
                                    Download {{ $guide->attachment_name }}
                                </a>
                            </div>
                        @endif
                        
                        {{-- Meta --}}
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                Last updated: {{ $guide->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- No Results Message --}}
        <div x-show="search !== '' && document.querySelectorAll('.guide-item[style*=\'display: none\']').length === {{ $guides->count() }}" 
             x-cloak>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-search display-4 d-block mb-3 opacity-50"></i>
                        <h5>No Matching Guides Found</h5>
                        <p class="mb-0">Try adjusting your search terms.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Need More Help Card --}}
    <div class="card mt-4 border-0 bg-gradient" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="rounded-circle bg-success bg-opacity-25 p-3">
                        <i class="bi bi-headset text-success fs-3"></i>
                    </div>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1 text-success">Need More Help?</h5>
                    <p class="mb-0 text-muted">If you can't find what you're looking for, contact your system administrator for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] { 
        display: none !important; 
    }
    
    .guide-item .card-header:hover {
        background-color: #f8fdf9 !important;
    }
    
    .guide-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background-color: #e8f5e9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .guide-icon i {
        font-size: 1.2rem;
    }
    
    .guide-content {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #444;
        white-space: pre-wrap;
    }
    
    .transition-transform {
        transition: transform 0.2s ease;
    }
    
    #searchGuides:focus {
        box-shadow: none;
        border-color: #198754;
    }
</style>
@endpush
