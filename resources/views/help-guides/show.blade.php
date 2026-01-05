@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="pdfViewerComponent()">
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
                        {!! $helpGuide->content !!}
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
            {{-- Attachments Card --}}
            @if($helpGuide->hasAttachment())
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-paperclip me-2 text-primary"></i>Attachments
                            @php
                                $attachmentCount = $helpGuide->attachments->count() + ($helpGuide->attachment_path ? 1 : 0);
                            @endphp
                            @if($attachmentCount > 1)
                                <span class="badge bg-info bg-opacity-10 text-info ms-2">{{ $attachmentCount }}</span>
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            {{-- Legacy single attachment --}}
                            @if($helpGuide->attachment_path)
                                <div class="pdf-thumbnail-card border rounded overflow-hidden"
                                     @click="openPdfViewer('{{ route('help-guides.preview', $helpGuide) }}', '{{ addslashes($helpGuide->attachment_name) }}')"
                                     style="cursor: pointer;">
                                    <div class="pdf-thumbnail-preview bg-light position-relative">
                                        <iframe src="{{ route('help-guides.preview', $helpGuide) }}#toolbar=0&navpanes=0&scrollbar=0" 
                                                class="pdf-thumb-iframe"
                                                title="Preview: {{ $helpGuide->attachment_name }}"></iframe>
                                        <div class="pdf-overlay d-flex align-items-center justify-content-center">
                                            <i class="bi bi-zoom-in fs-5 text-white"></i>
                                        </div>
                                    </div>
                                    <div class="pdf-thumbnail-info bg-white">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-pdf text-danger me-1" style="font-size: 0.65rem;"></i>
                                            <span class="small text-truncate" title="{{ $helpGuide->attachment_name }}">
                                                {{ Str::limit($helpGuide->attachment_name, 10) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Multiple attachments --}}
                            @foreach($helpGuide->attachments as $attachment)
                                <div class="pdf-thumbnail-card border rounded overflow-hidden"
                                     @click="openPdfViewer('{{ route('help-guides.attachment.preview', $attachment) }}', '{{ addslashes($attachment->file_name) }}')"
                                     style="cursor: pointer;">
                                    <div class="pdf-thumbnail-preview bg-light position-relative">
                                        <iframe src="{{ route('help-guides.attachment.preview', $attachment) }}#toolbar=0&navpanes=0&scrollbar=0" 
                                                class="pdf-thumb-iframe"
                                                title="Preview: {{ $attachment->file_name }}"></iframe>
                                        <div class="pdf-overlay d-flex align-items-center justify-content-center">
                                            <i class="bi bi-zoom-in fs-5 text-white"></i>
                                        </div>
                                    </div>
                                    <div class="pdf-thumbnail-info bg-white">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-pdf text-danger me-1" style="font-size: 0.65rem;"></i>
                                            <span class="small text-truncate" title="{{ $attachment->file_name }}">
                                                {{ Str::limit($attachment->file_name, 10) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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

    {{-- Full PDF Viewer Modal --}}
    <div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-hidden="true" x-ref="pdfModal">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header py-1 px-3 bg-dark border-0" style="min-height: auto;">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-secondary">
                    <iframe :src="currentPdfUrl" 
                            class="w-100 h-100 border-0"
                            style="min-height: calc(100vh - 40px);"
                            x-show="currentPdfUrl"></iframe>
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
    }
    
    .guide-content p {
        margin-bottom: 1rem;
    }
    
    .guide-content p:last-child {
        margin-bottom: 0;
    }
    
    .guide-content ul, .guide-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }
    
    .guide-content a {
        color: #198754;
        text-decoration: underline;
    }
    
    .guide-content a:hover {
        color: #146c43;
    }
    
    .breadcrumb-item a {
        color: #198754;
    }
    
    .breadcrumb-item a:hover {
        color: #146c43;
    }
    
    /* PDF Thumbnail Styles */
    .pdf-thumbnail-card {
        transition: all 0.2s ease;
        background: #fff;
        width: 100px;
    }
    
    .pdf-thumbnail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .pdf-thumbnail-preview {
        height: 70px;
        overflow: hidden;
    }
    
    .pdf-thumb-iframe {
        width: 200%;
        height: 300%;
        transform: scale(0.5);
        transform-origin: top left;
        pointer-events: none;
        border: none;
    }
    
    .pdf-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .pdf-thumbnail-card:hover .pdf-overlay {
        opacity: 1;
    }
    
    .pdf-thumbnail-info {
        border-top: 1px solid #e9ecef;
        padding: 4px 6px !important;
    }
    
    .pdf-thumbnail-info .small {
        font-size: 0.6rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function pdfViewerComponent() {
        return {
            currentPdfUrl: '',
            currentPdfName: '',
            pdfModal: null,
            
            init() {
                this.pdfModal = new bootstrap.Modal(this.$refs.pdfModal);
            },
            
            openPdfViewer(url, name) {
                this.currentPdfUrl = url;
                this.currentPdfName = name;
                this.pdfModal.show();
            }
        }
    }
</script>
@endpush
@endpush
