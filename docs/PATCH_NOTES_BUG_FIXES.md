# Patch Notes - Bug Fixes

## Bug Fix - Instructor Sidebar Content Being Clipped

**Date:** 2025-01-21

**Issue:** Instructor sidebar displayed content correctly for a split second after page load, then items would disappear. Specifically:
- The "Manage Grades" and "View Grades" items under ACADEMIC RECORDS were being hidden
- The entire REPORTS section (View Course Outcome, Course Outcome Attainment) was being hidden after render

**Root Cause:** 
The CSS rules in `resources/css/layout/app.css` had `overflow: hidden` on both `.sidebar-section` and `.sidebar-section ul` elements. When the page initially renders, the browser shows the raw HTML with all sidebar items visible. After the CSS bundle loads via Vite, these overflow rules were clipping the content that extended beyond the container's calculated height.

**Fix Applied:**
Removed the `overflow: hidden` rules from `.sidebar-section` and `.sidebar-section ul` in `resources/css/layout/app.css`.

**Files Changed:**
- `resources/css/layout/app.css` (lines 180-187)

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
    /* Removed overflow: hidden - was causing content clipping on instructor sidebar */
}

.sidebar-section ul {
    /* Removed overflow: hidden - was causing content clipping on instructor sidebar */
}
```

**Verification:**
Run `npm run build` to rebuild assets after this change.
