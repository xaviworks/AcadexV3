# TN-015: JavaScript Migration Bug Fixes & System Improvements
**Date:** December 11, 2025  
**Type:** Bug Fixes  
**Related:** JS extraction from Blade templates, Role-based access control

## Summary
Fixed multiple bugs that emerged after migrating inline JavaScript from Blade templates to external JS files. Also addressed role-based permission issues and UI display problems. Issues affected Admin, Chairperson, GE Coordinator, and Instructor roles.

---

## Critical Bugs Fixed

### 1. Admin Users Page - Disable Modal Auto-Loading & Syntax Error
**Issue:** The disable user modal was loading immediately on page load instead of when clicking the Disable button. Console showed "Uncaught SyntaxError: Unexpected token '}'"

**Root Cause:** Orphaned JavaScript code from incomplete cleanup - duplicate/malformed closing braces and stale code block around lines 862-871 in `users.blade.php`.

**Fix:** Removed orphaned JavaScript code that included duplicate closing braces and dead code referencing 'Force Logout' button reset.

**Files Modified:**
- `resources/views/admin/users.blade.php`

---

### 2. Chairperson/GE Coordinator - Password Validation Error on Override COs
**Issue:** When inputting the correct password to override Course Outcomes, an error occurred: "TypeError: confirm is not a function"

**Root Cause:** The global `confirm` function was being shadowed by the custom `window.confirm` helper object in the application's store system. The code was calling `confirm()` directly instead of `window.confirm()`.

**Fix:** Changed the confirm call to explicitly use `window.confirm()` to invoke the browser's native confirmation dialog.

**Files Modified:**
- `resources/js/pages/instructor/course-outcomes-wildcards.js`

---

### 3. GE Coordinator - Incorrect Deactivation Logic for Non-GE Instructors
**Issue:** GE Coordinator could deactivate instructor accounts even for instructors who were only approved for GE access but belonged to other departments. This was incorrect - GE Coordinator should only be able to deactivate GE department instructors and only remove GE access for others.

**Root Cause:** The `deactivateInstructor` method in `GECoordinatorController` was treating all instructors the same, setting `is_active = false` regardless of their department.

**Fix:** 
- Modified controller logic to check if instructor belongs to GE department
- For GE department instructors: Full deactivation (is_active = false)
- For non-GE department instructors: Only remove GE teaching access (can_teach_ge = false), keep account active
- Added new modal "Remove GE Access" for non-GE department instructors with appropriate warning text
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

## Previously Fixed (Earlier in Day)

### 5. Chairperson/GE Coordinator Route Conflict
**Issue:** Activate/Deactivate instructor buttons on Chairperson pages were redirecting to GE Coordinator routes.

**Fix:** Added page URL detection to both scripts to only run on their respective portals.

**Files Modified:**
- `resources/js/pages/chairperson/manage-instructors.js`
- `resources/js/pages/gecoordinator/manage-instructors.js`

---

### 6. DataTables Reinitialize Error on Admin Users Page
**Issue:** Console error "Cannot reinitialize DataTable" on the Admin Users page.

**Fix:** Removed duplicate DataTables initialization from blade template.

**Files Modified:**
- `resources/views/admin/users.blade.php`

---

### 7. Sidebar Text Cutoff
**Issue:** Sidebar navigation links were cutting off text with ellipsis.

**Fix:** Changed sidebar CSS to allow text wrapping with proper overflow handling.

**Files Modified:**
- `resources/css/layout/app.css`

---

### 8. Add Department/Program Buttons Not Working
**Issue:** "Add Department" and "Add Program" buttons on Admin pages did nothing when clicked.

**Fix:** Updated onclick handlers to use correct function names after JS extraction.

**Files Modified:**
- `resources/views/admin/departments.blade.php`
- `resources/views/admin/courses.blade.php`

---

### 9. Override COs Password Prompt Not Showing
**Issue:** Password confirmation field was not appearing when selecting override mode.

**Fix:** Added `isChairpersonOrGE` and `hasValidationErrors` to the `pageData` object.

**Files Modified:**
- `resources/views/instructor/course-outcomes-wildcards.blade.php`

---

## Technical Notes

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
When the application has a custom `window.confirm` helper, use the browser's native confirm explicitly:

```javascript
// Use window.confirm() for native browser confirmation
const confirmed = window.confirm('Are you sure?');

// NOT: confirm() which may be shadowed by custom helper
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
- Build successful: 56 modules, 286.98 KB (80.29 KB gzip)
- All CSS and JS assets compiled without errors

---

## Summary of All Changes

**Bug Fixes:**
- Fixed Admin Users page disable modal auto-loading and syntax error
- Fixed password validation "confirm is not a function" error on Override COs
- Fixed GE Coordinator incorrectly deactivating non-GE department instructors
- Fixed Instructor sidebar content being cut off/not scrollable
- Fixed Chairperson/GE Coordinator route conflicts
- Fixed DataTables reinitialization error
- Fixed sidebar text cutoff
- Fixed Add Department/Program buttons not working
- Fixed Override COs password prompt not showing

**Enhancements:**
- Added "Remove GE Access" modal with appropriate messaging for non-GE instructors
- Added "GE Access" badge to identify instructors with GE teaching access from other departments
- Improved role-based permission handling in GE Coordinator controller
