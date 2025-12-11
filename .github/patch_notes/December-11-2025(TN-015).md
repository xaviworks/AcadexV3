# TN-015: JavaScript Migration Bug Fixes
**Date:** December 11, 2025  
**Type:** Bug Fixes  
**Related:** JS extraction from Blade templates

## Summary
Fixed multiple bugs that emerged after migrating inline JavaScript from Blade templates to external JS files. Issues affected Admin, Chairperson, GE Coordinator, and Instructor roles.

---

## Bugs Fixed

### 1. Chairperson/GE Coordinator Route Conflict (Critical)
**Issue:** Activate/Deactivate instructor buttons on Chairperson pages were redirecting to GE Coordinator routes (`/gecoordinator/instructors/7/activate`), causing 403 Forbidden errors.

**Root Cause:** Both `chairperson/manage-instructors.js` and `gecoordinator/manage-instructors.js` were bundled together and both attached event handlers to the same modal elements (`confirmActivateModal`, `confirmDeactivateModal`). The GE Coordinator script ran second and overwrote the form action with hardcoded GE Coordinator URLs.

**Fix:** Added page URL detection to both scripts:
- `chairperson/manage-instructors.js` - Only runs if URL contains 'chairperson'
- `gecoordinator/manage-instructors.js` - Only runs if URL contains 'gecoordinator'

**Files Modified:**
- `resources/js/pages/chairperson/manage-instructors.js`
- `resources/js/pages/gecoordinator/manage-instructors.js`

---

### 2. DataTables Reinitialize Error on Admin Users Page
**Issue:** Console error "Cannot reinitialize DataTable" on the Admin Users page.

**Root Cause:** DataTables initialization existed in both the blade template (`users.blade.php` line 877) AND the external JS file (`admin/users.js`), causing double initialization.

**Fix:** Removed the duplicate DataTables initialization from `users.blade.php`, keeping only the external JS version.

**Files Modified:**
- `resources/views/admin/users.blade.php`

---

### 3. Sidebar Text Cutoff
**Issue:** Sidebar navigation links were cutting off text with ellipsis (`...`), making full menu item names unreadable.

**Root Cause:** CSS rules applied `white-space: nowrap`, `overflow: hidden`, and `text-overflow: ellipsis` to sidebar links.

**Fix:** Changed sidebar CSS to allow text wrapping:
- Removed `text-overflow: ellipsis`
- Changed `white-space: nowrap` to `white-space: normal`
- Added `word-wrap: break-word` and `overflow-wrap: break-word`

**Files Modified:**
- `resources/css/layout/app.css`

---

### 4. Add Department/Program Buttons Not Working
**Issue:** "Add Department" and "Add Program" buttons on Admin pages did nothing when clicked.

**Root Cause:** The buttons called `showModal()` but the functions were renamed to `showDepartmentModal()` and `showCourseModal()` during JS extraction to avoid naming conflicts.

**Fix:** Updated onclick handlers in blade templates to use the correct function names:
- `departments.blade.php`: `showModal()` → `showDepartmentModal()`
- `courses.blade.php`: `showModal()` → `showCourseModal()`

**Files Modified:**
- `resources/views/admin/departments.blade.php`
- `resources/views/admin/courses.blade.php`

---

### 5. Override COs Password Prompt Not Showing
**Issue:** When selecting "Override all existing COs" mode on the Course Outcomes Wildcards page, the password confirmation field was not appearing for Chairpersons and GE Coordinators.

**Root Cause:** The JavaScript looked for `pageData.isChairpersonOrGE` to determine whether to enable the override mode functionality, but the blade template was only passing `userRole` without the explicit `isChairpersonOrGE` boolean.

**Fix:** Added `isChairpersonOrGE` and `hasValidationErrors` to the `pageData` object in the blade template:
```javascript
window.pageData = {
    userRole: {{ Auth::user()->role }},
    isChairpersonOrGE: {{ (Auth::user()->role === 1 || Auth::user()->role === 4) ? 'true' : 'false' }},
    hasValidationErrors: {{ $errors->any() ? 'true' : 'false' }},
    // ...
};
```

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

### Data Passing Pattern: PHP → JavaScript
Always pass boolean flags explicitly in `pageData` when the JS logic depends on them:

```javascript
window.pageData = {
    isFeatureEnabled: {{ $featureEnabled ? 'true' : 'false' }},
    // NOT: relying on JS to derive from other values
};
```

---

## Build Information
- Build successful: 56 modules, 286.69 KB (80.24 KB gzip)
- All CSS and JS assets compiled without errors

**Fixes**
- Fixed critical error on Admin Users page: "Cannot end a push stack without first starting one" caused by orphaned JavaScript code from incomplete cleanup
- Fixed sidebar horizontal scroll issue and text cutoff by adding proper overflow handling and text ellipsis styles
- Fixed Instructor Dashboard chart not rendering - added auto-initialization when pageData is available
- Fixed Chairperson "View Courses" button not working on CO Course Chooser page by improving card URL detection
- Fixed Chairperson instructor cards not clickable on View Grades page - added missing `data-url` and `onclick` handler
- Fixed Override COs functionality breaking with "event is not defined" error by using button ID selector instead
- Fixed Admin "Add Department" button conflict by renaming to unique function `showDepartmentModal`
- Fixed Admin "Add Program" button conflict by renaming to unique function `showCourseModal`  
- Fixed Admin "Generate New" academic period button by correcting function name to `showGenerateModal`

**Technical Changes**
- Removed ~150 lines of orphaned JavaScript code from `admin/users.blade.php`
- Updated `admin/departments.js` to use page-specific initialization guard
- Updated `admin/courses.js` to use page-specific initialization guard
- Updated `chairperson/reports/co-course-chooser.js` to detect cards via data-url attribute
- Updated `dashboard/instructor.js` to auto-initialize chart on DOM ready
- Updated `instructor/course-outcomes-wildcards.js` to get submit button by ID
- Updated `academic-periods/index.blade.php` to use correct function name
- Added CSS fixes for sidebar content overflow handling
