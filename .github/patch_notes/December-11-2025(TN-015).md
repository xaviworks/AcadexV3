# TN-015: JavaScript Migration Bug Fixes & System Improvements
**Date:** December 11, 2025  
**Type:** Bug Fixes  
**Related:** JS extraction from Blade templates, Role-based access control, CSS overflow issues

## Summary
Fixed multiple bugs that emerged after migrating inline JavaScript from Blade templates to external JS files. Also addressed role-based permission issues, UI display problems, CSS-related visibility issues, and JavaScript escaping problems. Issues affected Admin, Chairperson, GE Coordinator, and Instructor roles.

---

## Critical Bugs Fixed

### 1. Admin Users Page - Disable Modal Showing on Page Load
**Issue:** The disable user modal was appearing immediately on page load instead of when clicking the Disable button on a user.

**Root Cause:** The modal div had a static `d-block-important` CSS class which sets `display: block !important`. Alpine.js `x-show` directive uses inline styles (`display: none`) to hide elements, but CSS `!important` declarations take precedence over inline styles. This caused the modal to always be visible regardless of the Alpine.js state.

**Fix:** 
- Added `x-cloak` directive to hide the modal until Alpine.js initializes
- Changed the `d-block-important` class to use Alpine's `:class` binding so it's only applied when the modal should be shown
- The conditional class binding ensures `display: block !important` only activates when `$store.modals.active === 'chooseDisableModal'`
- Changed `addslashes()` to `json_encode()` for proper JavaScript escaping in onclick handlers

**Files Modified:**
- `resources/views/admin/users.blade.php`

---

### 2. Chairperson/GE Coordinator - Password Validation Error on Override COs (Second Fix)
**Issue:** When inputting the correct password to override Course Outcomes, an error occurred: "TypeError: window.confirm is not a function"

**Root Cause:** The `window.confirm` global was replaced by a custom helper object (`{ ask: ... }`). The code was calling `window.confirm(...)` directly which tried to invoke an object as a function instead of using `window.confirm.ask(...)`.

**Fix:** Changed the confirm call to use `window.confirm.ask()` with async/await pattern to properly invoke the custom confirmation dialog helper.

**Code Change:**
```javascript
// Before (incorrect - calling object as function)
const confirmed = window.confirm('Are you sure?');

// After (correct - using the custom helper's ask method)
const confirmed = await window.confirm.ask({
    title: ' Final Warning',
    message: 'This will PERMANENTLY DELETE all existing course outcomes...',
    confirmText: 'Yes, Override All',
    cancelText: 'Cancel',
    type: 'danger'
});
```

**Files Modified:**
- `resources/js/pages/instructor/course-outcomes-wildcards.js`

---

### 3. Override Course Outcomes - Confirmation Dialog Not Appearing
**Issue:** After password validation succeeded for override mode, the button stayed stuck at "Verifying password..." and the confirmation dialog never appeared.

**Root Cause:** The `window.confirm.ask()` helper uses Alpine.js store to set `$store.confirm.show = true`, which should display the confirmation dialog component. However, the `confirmation-dialog` component was never included in the main layout template (`app.blade.php`), so there was no UI to display.

**Fix:** Added the confirmation dialog component include to the layout file.

**Code Change:**
```blade
{{-- In resources/views/layouts/app.blade.php --}}

{{-- Toast Notifications --}}
@include('components.toast-notifications')

{{-- Confirmation Dialog (Alpine.js) - ADDED --}}
@include('components.confirmation-dialog')

@stack('scripts')
```

**Files Modified:**
- `resources/views/layouts/app.blade.php`

---

### 4. Generate Course Outcomes - Modal Not Opening
**Issue:** Clicking "Generate COs" button caused JavaScript error: "Uncaught TypeError: Cannot read properties of null (reading 'hide')" at modal.js:362

**Root Cause:** The "Global Modal Backdrop Fix" script in `app.blade.php` was monkey-patching Bootstrap's Modal class incorrectly. The script was overwriting the constructor in a way that broke the prototype chain and internal instance initialization, causing `this._backdrop` to be null when Bootstrap tried to call `.hide()` on it.

**Fix:** Replaced the complex constructor monkey-patching with a cleaner approach using:
1. Event delegation to listen for `show.bs.modal` events and set `backdrop: false` on the instance config
2. Setting `data-bs-backdrop="false"` attribute on all existing `.modal` elements
3. A MutationObserver to automatically set the attribute on dynamically added modals

**Code Change:**
```javascript
// Before (broken - complex constructor overriding)
const OriginalModal = bootstrap.Modal;
bootstrap.Modal = function(element, config) {
    config = config || {};
    config.backdrop = false;
    return new OriginalModal(element, config); // Broke prototype chain
};
// ... copying static methods

// After (correct - event-based approach)
document.addEventListener('show.bs.modal', function(event) {
    const modal = event.target;
    const instance = bootstrap.Modal.getInstance(modal);
    if (instance && instance._config) {
        instance._config.backdrop = false;
    }
});

// Set data attribute on all modals
document.querySelectorAll('.modal').forEach(function(modal) {
    if (!modal.hasAttribute('data-bs-backdrop')) {
        modal.setAttribute('data-bs-backdrop', 'false');
    }
});

// MutationObserver for dynamic modals...
```

**Files Modified:**
- `resources/views/layouts/app.blade.php`

---

### 5. Instructor Course Outcome Results - CSS Displayed as Raw Text
**Issue:** Raw CSS code was being displayed on the Course Outcome Results page instead of being applied as styles.

**Root Cause:** The CSS in `course-outcome-results.blade.php` was placed directly after the `{{-- Styles: ... --}}` comment without being wrapped in a proper `@push('styles')` block with `<style>` tags.

**Fix:** Wrapped the inline CSS in `@push('styles')` with `<style>` tags and closed with `@endpush` before `@section('content')`.

**Files Modified:**
- `resources/views/instructor/scores/course-outcome-results.blade.php`

---

### 5. Instructor Course Outcome Results - JavaScript Null Reference Error
**Issue:** Console error: "Cannot read properties of null (reading 'value') at toggleScoreType"

**Root Cause:** The `toggleScoreType()` function was called on page load via DOMContentLoaded, but the `#scoreType` element doesn't exist on pages with no course outcomes. The code was accessing `.value` on a null element.

**Fix:** Added null guards to check if `scoreType` element exists before accessing its properties.

**Code Change:**
```javascript
// Before
function toggleScoreType() {
    var type = document.getElementById('scoreType').value;
    ...
}

// After
function toggleScoreType() {
    var scoreTypeEl = document.getElementById('scoreType');
    if (!scoreTypeEl) return; // Guard against null element
    var type = scoreTypeEl.value;
    ...
}
```

**Files Modified:**
- `public/js/course-outcome-results.js`

---

### 5. GE Coordinator - View/Edit Buttons Not Working on Assign Subjects Page
**Issue:** Clicking "View" or "Edit" buttons on the Manage Courses page did nothing - no modal appeared.

**Root Cause:** The onclick handlers were using `addslashes()` to escape string parameters, but this doesn't properly escape for JavaScript context (especially with special characters like `&` in subject descriptions). This could cause silent JavaScript errors.

**Fix:** Changed `addslashes()` to `json_encode()` for all inline onclick handlers. `json_encode()` properly escapes strings for JavaScript context.

**Code Change:**
```blade
{{-- Before (incorrect escaping for JS) --}}
onclick="openViewInstructorsModal({{ $subject->id }}, '{{ addslashes($subject->subject_code) }}')"

{{-- After (proper JS escaping) --}}
onclick="openViewInstructorsModal({{ $subject->id }}, {{ json_encode($subject->subject_code) }})"
```

**Files Modified:**
- `resources/views/gecoordinator/assign-subjects.blade.php`

---

### 3. GE Coordinator - Incorrect Deactivation Logic for Non-GE Instructors
**Issue:** GE Coordinator could deactivate instructor accounts even for instructors who were only approved for GE access but belonged to other departments. This was incorrect - GE Coordinator should only be able to deactivate GE department instructors and only remove GE access for others.

**Root Cause:** The `deactivateInstructor` method in `GECoordinatorController` was treating all instructors the same, setting `is_active = false` regardless of their department.

**Fix:** 
- Modified controller logic to check if instructor belongs to GE department
- For GE department instructors: Full deactivation (`is_active = false`)
- For non-GE department instructors: Only remove GE teaching access (`can_teach_ge = false`), keep account active
- Added new "Remove GE Access" modal for non-GE department instructors with appropriate warning text
- Updated blade template to show different buttons based on instructor's department
- Added "GE Access" badge to identify instructors from other departments who have GE teaching access

**Files Modified:**
- `app/Http/Controllers/GECoordinatorController.php`
- `resources/views/gecoordinator/manage-instructors.blade.php`
- `resources/js/pages/gecoordinator/manage-instructors.js`

---

### 4. Instructor Sidebar - Content Cut Off / Not Scrolling
**Issue:** Instructor sidebar was not displaying all menu items. The "REPORTS" section and some items were hidden/cut off.

**Root Cause:** The `.sidebar-wrapper` CSS had `overflow: hidden` which prevented the sidebar content from being scrollable when it exceeded the viewport height.

**Fix:** Changed `.sidebar-wrapper` overflow property from `overflow: hidden` to `overflow-x: hidden; overflow-y: auto` to allow vertical scrolling.

**Files Modified:**
- `resources/css/layout/app.css`

---

### 5. Instructor Sidebar - Content Being Clipped After CSS Load
**Issue:** Instructor sidebar displayed content correctly for a split second after page load, then items would disappear. Specifically, the "Manage Grades" and "View Grades" items under ACADEMIC RECORDS were being hidden, along with the entire REPORTS section.

**Root Cause:** The CSS rules in `resources/css/layout/app.css` had `overflow: hidden` on both `.sidebar-section` and `.sidebar-section ul` elements. When the page initially renders, the browser shows the raw HTML with all sidebar items visible. After the CSS bundle loads via Vite, these overflow rules were clipping the content that extended beyond the container's calculated height.

**Fix:** Removed the `overflow: hidden` rules from `.sidebar-section` and `.sidebar-section ul`.

**Code Change:**
```css
/* Before */
.sidebar-section {
    overflow: hidden;
}

.sidebar-section ul {
    overflow: hidden;
}

/* After */
.sidebar-section {
    /* Removed overflow: hidden - was causing content clipping */
}

.sidebar-section ul {
    /* Removed overflow: hidden - was causing content clipping */
}
```

**Files Modified:**
- `resources/css/layout/app.css` (lines 180-187)

---

## Previously Fixed (Earlier in Day)

### 6. Chairperson/GE Coordinator Route Conflict
**Issue:** Activate/Deactivate instructor buttons on Chairperson pages were redirecting to GE Coordinator routes.

**Fix:** Added page URL detection to both scripts to only run on their respective portals.

**Files Modified:**
- `resources/js/pages/chairperson/manage-instructors.js`
- `resources/js/pages/gecoordinator/manage-instructors.js`

---

### 7. DataTables Reinitialize Error on Admin Users Page
**Issue:** Console error "Cannot reinitialize DataTable" on the Admin Users page.

**Fix:** Removed duplicate DataTables initialization from blade template.

**Files Modified:**
- `resources/views/admin/users.blade.php`

---

### 8. Sidebar Text Cutoff
**Issue:** Sidebar navigation links were cutting off text with ellipsis.

**Fix:** Changed sidebar CSS to allow text wrapping with proper overflow handling.

**Files Modified:**
- `resources/css/layout/app.css`

---

### 9. Add Department/Program Buttons Not Working
**Issue:** "Add Department" and "Add Program" buttons on Admin pages did nothing when clicked.

**Fix:** Updated onclick handlers to use correct function names after JS extraction.

**Files Modified:**
- `resources/views/admin/departments.blade.php`
- `resources/views/admin/courses.blade.php`

---

### 10. Override COs Password Prompt Not Showing
**Issue:** Password confirmation field was not appearing when selecting override mode.

**Fix:** Added `isChairpersonOrGE` and `hasValidationErrors` to the `pageData` object.

**Files Modified:**
- `resources/views/instructor/course-outcomes-wildcards.blade.php`

---

## Technical Notes

### Alpine.js x-show vs CSS !important
When using Alpine's `x-show` with modals that require `display: block !important`:

```html
<!-- WRONG: !important in static class overrides x-show -->
<div x-show="condition" class="d-block-important">

<!-- CORRECT: Use x-cloak + conditional :class binding -->
<div x-show="condition" x-cloak :class="{ 'd-block-important': condition }">
```

The `x-cloak` directive hides the element until Alpine initializes, and the conditional class ensures the `!important` display rule only applies when intended.

### Pattern for Page-Specific JS in Bundled Environment
When multiple page scripts are bundled together but target similar DOM elements, use URL-based guards:

```javascript
function initPageSpecificFunction() {
    // Guard: Only run on this specific portal
    if (!window.location.pathname.includes('portalname')) {
        return;
    }
    // ... rest of initialization
}
```

### Native vs Custom Confirm Dialog
When the application has a custom `window.confirm` helper, use the helper's method:

```javascript
// WRONG: Calling the object as a function (will error)
const confirmed = window.confirm('Are you sure?');

// CORRECT: Use the custom helper's ask() method
const confirmed = await window.confirm.ask({
    title: 'Confirm Action',
    message: 'Are you sure?',
    confirmText: 'Yes',
    cancelText: 'Cancel',
    type: 'danger' // or 'info', 'warning'
});
```

### Proper JavaScript Escaping in Blade Templates
When passing PHP values to inline JavaScript handlers, use `json_encode()` instead of `addslashes()`:

```blade
{{-- WRONG: addslashes doesn't escape for JS context --}}
onclick="myFunction('{{ addslashes($value) }}')"

{{-- CORRECT: json_encode properly escapes for JS --}}
onclick="myFunction({{ json_encode($value) }})"
```

### Role-Based Actions in Controllers
Always check user context before performing destructive actions:

```php
// Example: Different behavior based on instructor's department
$geDepartment = Department::where('department_code', 'GE')->first();

if ($instructor->department_id === $geDepartment?->id) {
    // Full action for department members
} else {
    // Limited action for external users with access
}
```

---

## Build Information
- Build successful: 56 modules transformed
- Output: 287.00 KB (80.28 KB gzip)
- All CSS and JS assets compiled without errors

---

## Summary of All Changes

| # | Bug | Status |
|---|-----|--------|
| 1 | Admin Users - Disable modal showing on page load |  Fixed |
| 2 | Chairperson/GE - Password validation "window.confirm is not a function" |  Fixed |
| 3 | Override COs - Confirmation dialog not appearing after password validation |  Fixed |
| 4 | Instructor - Course Outcome Results CSS displayed as raw text |  Fixed |
| 5 | Instructor - Course Outcome Results null reference error |  Fixed |
| 6 | GE Coordinator - View/Edit buttons not working on Assign Subjects |  Fixed |
| 7 | GE Coordinator - Incorrect deactivation for non-GE instructors |  Fixed |
| 8 | Instructor sidebar - Content cut off / not scrolling |  Fixed |
| 9 | Instructor sidebar - Content clipped after CSS load |  Fixed |
| 10 | Chairperson/GE Coordinator - Route conflicts |  Fixed |
| 11 | Admin Users - DataTables reinitialization error |  Fixed |
| 12 | Sidebar - Text cutoff with ellipsis |  Fixed |
| 13 | Admin - Add Department/Program buttons not working |  Fixed |
| 14 | Override COs - Password prompt not showing |  Fixed |
| 15 | Generate COs - Modal not opening ("Cannot read properties of null") |  Fixed |

**Enhancements:**
- Added "Remove GE Access" modal with appropriate messaging for non-GE instructors
- Added "GE Access" badge to identify instructors with GE teaching access from other departments
- Improved role-based permission handling in GE Coordinator controller
- Improved JavaScript string escaping in Blade templates using `json_encode()`
- Added global confirmation dialog component to main layout
