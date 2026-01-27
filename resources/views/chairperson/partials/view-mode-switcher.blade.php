{{--
    View Mode Switcher Partial
    
    Provides a dropdown to toggle between year view and full view.
    Used in assign-subjects page.
--}}

<div class="d-flex align-items-center">
    <label for="viewMode" class="me-2 fw-semibold text-nowrap">View Mode:</label>
    <select id="viewMode" class="form-select form-select-sm w-auto" onchange="toggleViewMode()">
        <option value="year" selected>Year View</option>
        <option value="full">Full View</option>
    </select>
</div>
