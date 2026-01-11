@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.tutorials.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Tutorials
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-book"></i> {{ isset($tutorial) ? 'Edit' : 'Create' }} Tutorial
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($tutorial) ? route('admin.tutorials.update', $tutorial) : route('admin.tutorials.store') }}" 
                          method="POST" 
                          id="tutorialForm">
                        @csrf
                        @if(isset($tutorial))
                            @method('PUT')
                        @endif

                        {{-- Basic Information --}}
                        <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-info-circle"></i> Basic Information</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Target Role *</label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role', $tutorial->role ?? '') === $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="page_identifier" class="form-label">
                                    Page Identifier * 
                                    <i class="bi bi-question-circle" 
                                       data-bs-toggle="tooltip" 
                                       title="Unique identifier for the page (e.g., admin-dashboard, dean-grades)"></i>
                                </label>
                                <input type="text" 
                                       name="page_identifier" 
                                       id="page_identifier" 
                                       class="form-control @error('page_identifier') is-invalid @enderror" 
                                       value="{{ old('page_identifier', $tutorial->page_identifier ?? '') }}"
                                       placeholder="e.g., admin-dashboard"
                                       required>
                                @error('page_identifier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Tutorial Title *</label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $tutorial->title ?? '') }}"
                                   placeholder="e.g., Admin Dashboard Overview"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="2"
                                      placeholder="Brief description of what this tutorial covers">{{ old('description', $tutorial->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="priority" class="form-label">
                                    Priority 
                                    <i class="bi bi-question-circle" 
                                       data-bs-toggle="tooltip" 
                                       title="Higher number = higher priority. Used when multiple tutorials exist for same page."></i>
                                </label>
                                <input type="number" 
                                       name="priority" 
                                       id="priority" 
                                       class="form-control @error('priority') is-invalid @enderror" 
                                       value="{{ old('priority', $tutorial->priority ?? 0) }}"
                                       min="0">
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           class="form-check-input" 
                                           value="1"
                                           {{ old('is_active', $tutorial->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (visible to users)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Tutorial Steps --}}
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-list-ol"></i> Tutorial Steps
                            <button type="button" class="btn btn-sm btn-success float-end" id="addStepBtn">
                                <i class="bi bi-plus"></i> Add Step
                            </button>
                        </h5>

                        <div id="stepsContainer">
                            @if(isset($tutorial) && $tutorial->steps->count() > 0)
                                @foreach($tutorial->steps as $index => $step)
                                    @include('admin.tutorials._step-form', ['index' => $index, 'step' => $step])
                                @endforeach
                            @else
                                @include('admin.tutorials._step-form', ['index' => 0])
                            @endif
                        </div>

                        {{-- Data Check Configuration --}}
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-database-check"></i> Data Validation (Optional)
                        </h5>

                        <div class="form-check mb-3">
                            <input type="checkbox" 
                                   name="has_data_check" 
                                   id="has_data_check" 
                                   class="form-check-input"
                                   value="1"
                                   {{ old('has_data_check', isset($tutorial) && $tutorial->dataCheck ? true : false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_data_check">
                                Check if page has data before starting tutorial
                            </label>
                        </div>

                        <div id="dataCheckConfig" style="display: {{ old('has_data_check', isset($tutorial) && $tutorial->dataCheck ? true : false) ? 'block' : 'none' }};">
                            <div class="bg-light p-3 rounded">
                                <div class="mb-3">
                                    <label for="data_check_selector" class="form-label">Data Selector</label>
                                    <input type="text" 
                                           name="data_check[selector]" 
                                           id="data_check_selector" 
                                           class="form-control" 
                                           value="{{ old('data_check.selector', $tutorial->dataCheck->selector ?? '') }}"
                                           placeholder="tbody tr">
                                    <small class="text-muted">CSS selector for data rows</small>
                                </div>

                                <div class="mb-3">
                                    <label for="data_check_entity_name" class="form-label">Entity Name</label>
                                    <input type="text" 
                                           name="data_check[entity_name]" 
                                           id="data_check_entity_name" 
                                           class="form-control" 
                                           value="{{ old('data_check.entity_name', $tutorial->dataCheck->entity_name ?? 'records') }}"
                                           placeholder="records">
                                    <small class="text-muted">User-friendly name (e.g., "students", "courses")</small>
                                </div>

                                <div class="form-check">
                                    <input type="hidden" name="data_check[no_add_button]" value="0">
                                    <input type="checkbox" 
                                           name="data_check[no_add_button]" 
                                           id="data_check_no_add_button" 
                                           class="form-check-input"
                                           value="1"
                                           {{ old('data_check.no_add_button', $tutorial->dataCheck->no_add_button ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="data_check_no_add_button">
                                        Page has no "Add" button (data comes from external source)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ isset($tutorial) ? 'Update' : 'Create' }} Tutorial
                            </button>
                            <a href="{{ route('admin.tutorials.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar with Help --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Use specific CSS selectors for reliable targeting</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Mark steps as optional if elements might not exist</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Test selectors in browser DevTools first</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Higher priority tutorials show first</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-tools"></i> Element Selector Tool</h5>
                </div>
                <div class="card-body">
                    <p class="small">Click "Pick Element" on any step to visually select DOM elements from the page.</p>
                    <button type="button" class="btn btn-warning btn-sm w-100" id="openSelectorTool" disabled>
                        <i class="bi bi-cursor"></i> Open Visual Selector
                    </button>
                    <small class="text-muted d-block mt-2">Save draft first to enable this feature</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-tutorials/tutorial-builder.js') }}"></script>
@endpush
@endsection
