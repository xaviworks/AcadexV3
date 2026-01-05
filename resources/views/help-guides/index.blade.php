@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="helpGuidesViewer()">
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
                                        @php
                                            $attachmentCount = $guide->attachments->count() + ($guide->attachment_path ? 1 : 0);
                                        @endphp
                                        @if($attachmentCount > 1)
                                            {{ $attachmentCount }}
                                        @endif
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
                            {!! $guide->content !!}
                        </div>
                        
                        {{-- Attachments Grid (Multiple PDFs) --}}
                        @if($guide->hasAttachment())
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="text-muted small fw-semibold mb-3">
                                    <i class="bi bi-file-pdf me-1 text-danger"></i> PDF Attachments
                                </h6>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- Legacy single attachment --}}
                                    @if($guide->attachment_path)
                                        <div class="pdf-thumbnail-card border rounded overflow-hidden"
                                             @click="openPdfViewer('{{ route('help-guides.preview', $guide) }}', '{{ addslashes($guide->attachment_name) }}')"
                                             style="cursor: pointer;">
                                            <div class="pdf-thumbnail-preview bg-light position-relative">
                                                <iframe src="{{ route('help-guides.preview', $guide) }}#toolbar=0&navpanes=0&scrollbar=0" 
                                                        class="pdf-thumb-iframe"
                                                        title="Preview: {{ $guide->attachment_name }}"></iframe>
                                                <div class="pdf-overlay d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-zoom-in fs-5 text-white"></i>
                                                </div>
                                            </div>
                                            <div class="pdf-thumbnail-info bg-white">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-pdf text-danger me-1" style="font-size: 0.7rem;"></i>
                                                    <span class="small text-truncate" title="{{ $guide->attachment_name }}">
                                                        {{ Str::limit($guide->attachment_name, 12) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- Multiple attachments --}}
                                    @foreach($guide->attachments as $attachment)
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
                                                    <i class="bi bi-file-pdf text-danger me-1" style="font-size: 0.7rem;"></i>
                                                    <span class="small text-truncate" title="{{ $attachment->file_name }}">
                                                        {{ Str::limit($attachment->file_name, 12) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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
    }
    
    .guide-content p {
        margin-bottom: 0.75rem;
    }
    
    .guide-content p:last-child {
        margin-bottom: 0;
    }
    
    .guide-content ul, .guide-content ol {
        margin-bottom: 0.75rem;
        padding-left: 1.5rem;
    }
    
    .guide-content a {
        color: #198754;
        text-decoration: underline;
    }
    
    .guide-content a:hover {
        color: #146c43;
    }
    
    .transition-transform {
        transition: transform 0.2s ease;
    }
    
    #searchGuides:focus {
        box-shadow: none;
        border-color: #198754;
    }
    
    /* PDF Thumbnail Styles */
    .pdf-thumbnail-card {
        transition: all 0.2s ease;
        background: #fff;
        max-width: 120px;
    }
    
    .pdf-thumbnail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .pdf-thumbnail-preview {
        height: 80px;
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
        padding: 6px 8px !important;
    }
    
    .pdf-thumbnail-info .small {
        font-size: 0.65rem !important;
    }
    
    /* Modal full-screen PDF */
    #pdfViewerModal .modal-body iframe {
        min-height: calc(100vh - 56px);
    }
</style>
@endpush

@push('scripts')
<script>
    function helpGuidesViewer() {
        return {
            search: '',
            openGuide: {{ $guides->first()?->id ?? 'null' }},
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
