@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="helpGuidesViewer()" x-init="init()">
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="h3 text-dark fw-bold mb-1">
            <i class="bi bi-question-circle-fill text-success me-2"></i>Help Guides
        </h1>
        <p class="text-muted mb-0">Find answers to common questions and learn how to use the system effectively.</p>
    </div>

    {{-- Empty State --}}
    <template x-if="guides.length === 0">
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
    </template>

    {{-- Guides List --}}
    <template x-if="guides.length > 0">
        <div>
            {{-- Search Box --}}
            <div class="mb-3">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           x-model="search"
                           placeholder="Search guides...">
                </div>
            </div>

            {{-- Help Guides List --}}
            <div class="help-guides-list">
                <template x-for="guide in guides" :key="guide.id">
                    <div class="card shadow-sm mb-3 guide-item" 
                         x-show="matchesSearch(guide)">
                        {{-- Guide Header --}}
                        <div class="card-header bg-white py-3 cursor-pointer" 
                             @click="toggleGuide(guide.id)"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="guide-icon me-3">
                                        <i class="bi bi-book text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold" x-text="guide.title"></h6>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span x-show="guide.has_attachment" 
                                          class="badge bg-info bg-opacity-10 text-info me-3" 
                                          title="Has attachment">
                                        <i class="bi bi-paperclip"></i>
                                        <span x-show="guide.attachment_count > 1" x-text="guide.attachment_count"></span>
                                    </span>
                                    <i class="bi transition-transform" 
                                       :class="openGuide === guide.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Guide Content --}}
                        <div class="card-body border-top guide-body" x-show="openGuide === guide.id" x-transition>
                            {{-- Content --}}
                            <div class="guide-content mb-3" x-html="guide.content"></div>
                            
                            {{-- Attachments Grid (Multiple PDFs) --}}
                            <template x-if="guide.has_attachment">
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="text-muted small fw-semibold mb-3">
                                        <i class="bi bi-file-pdf me-1 text-danger"></i> PDF Attachments
                                    </h6>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <template x-for="attachment in guide.attachments" :key="attachment.preview_url">
                                            <div class="pdf-thumbnail-card border rounded overflow-hidden"
                                                 @click="openPdfViewer(attachment.preview_url, attachment.file_name)"
                                                 style="cursor: pointer;">
                                                <div class="pdf-thumbnail-preview bg-light position-relative">
                                                    <iframe :src="attachment.preview_url + '#toolbar=0&navpanes=0&scrollbar=0'" 
                                                            class="pdf-thumb-iframe"
                                                            :title="'Preview: ' + attachment.file_name"></iframe>
                                                    <div class="pdf-overlay d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-zoom-in fs-5 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="pdf-thumbnail-info bg-white">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-file-pdf text-danger me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="small text-truncate" 
                                                              :title="attachment.file_name"
                                                              x-text="limitString(attachment.file_name, 12)">
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            {{-- Meta --}}
                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    Last updated: <span x-text="guide.updated_at"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- No Results Message --}}
            <div x-show="search !== '' && !hasSearchResults()" x-cloak>
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
        </div>
    </template>

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
{{-- Styles: resources/css/help-guides/index.css --}}
@endpush

@push('scripts')
<script>
    window.helpGuidesPageConfig = {
        guides: @json($guidesData ?? []),
        firstGuideId: {{ $guides->first()?->id ?? 'null' }},
        pollUrl: '{{ route("help-guides.poll") }}'
    };
</script>
{{-- JavaScript: resources/js/pages/shared/help-guides.js --}}
@endpush
