<!-- resources/views/instructor/scores/partials/auto-save-script.blade.php -->
@push('scripts')
{{-- JavaScript moved to: resources/js/pages/instructor/partials/auto-save-script.js --}}
<script>
    // Pass route data to external JS
    window.pageData = window.pageData || {};
    window.pageData.saveScoreUrl = "{{ route('grades.ajaxSaveScore') }}";
</script>
@endpush
