# Alpine Store State Management - Usage Guide

## Overview

This document demonstrates practical usage of Alpine stores integrated into Acadex. Alpine stores provide centralized, reactive state management for client-side UI interactions.

## Implemented Stores

### 1. Notifications Store (`$store.notifications`)

**Purpose**: Centralized toast notification system replacing Laravel session flash messages.

**Implementation Example** (`manage-grades.blade.php`):

```php
<!-- BEFORE: Laravel Session Flash with Bootstrap -->
@if(session('success'))
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div class="toast show" role="alert">
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

<!-- AFTER: Alpine Notification Store -->
@if(session('success'))
    <script>notify.success('{{ session('success') }}');</script>
@endif

@if(session('error'))
    <script>notify.error('{{ session('error') }}');</script>
@endif
```

**Available Methods**:

- `notify.success(message)` - Green success toast with check icon
- `notify.error(message)` - Red error toast with X icon  
- `notify.warning(message)` - Yellow warning toast with exclamation icon
- `notify.info(message)` - Blue info toast with info icon

**Features**:

- Auto-dismiss after 5 seconds
- Slide-in/fade-out animations
- Multiple notifications queued
- Icon mapping based on type
- Position: top-right, z-index 9999

---

### 2. Grades Store (`$store.grades`)

**Purpose**: Track unsaved grade changes and manage term selection state.

**Implementation Example** (`grade-script.blade.php`):

```javascript
// BEFORE: Global variable pattern
let hasUnsavedChanges = false;

function updateSaveButtonState() {
    const { hasChanges } = checkForChanges();
    hasUnsavedChanges = hasChanges; // Global variable mutation
}

// AFTER: Alpine store integration
function updateSaveButtonState() {
    const { hasChanges } = checkForChanges();
    
    // Update Alpine store reactively
    if (Alpine && Alpine.store) {
        if (hasChanges) {
            Alpine.store('grades').markChanged();
        } else {
            Alpine.store('grades').clearUnsaved();
        }
    }
}
```

**Visual Indicator** (`grade-table.blade.php`):

```html
<!-- Alpine-powered unsaved changes badge -->
<div x-data x-show="$store.grades.unsavedChanges" x-transition class="me-3">
    <div class="alert alert-warning mb-0 py-2 px-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span class="small fw-semibold">Unsaved changes</span>
    </div>
</div>
```

**Available Methods**:

- `gradeState.markChanged()` - Mark form as having unsaved changes
- `gradeState.clearUnsaved()` - Clear unsaved changes flag
- `gradeState.hasUnsaved()` - Check if there are unsaved changes
- `gradeState.setTerm(term)` - Switch term with confirmation if unsaved

**Features**:

- Reactive UI updates across components
- Automatic beforeunload warning
- Confirmation modal on navigation with unsaved changes
- Visual warning badge with x-transition animations
- Integrates with existing showUnsavedChangesModal function

**Replaced Global Variables**:

- `hasUnsavedChanges` → `$store.grades.unsavedChanges`
- All `hasUnsavedChanges = false` → `Alpine.store('grades').clearUnsaved()`
- All `hasUnsavedChanges = true` → `Alpine.store('grades').markChanged()`

---

### 3. Modal Store (`$store.modals`)

**Purpose**: Centralized modal state management for opening/closing modals with data payloads.

**Available Methods**:

- `modal.open(modalId, data)` - Open modal with optional data payload
- `modal.close()` - Close active modal
- `modal.isOpen(modalId)` - Check if specific modal is active

**Example Usage**:

```html
<!-- Open modal with data -->
<button @click="$store.modals.open('editStudent', { studentId: 123 })">
    Edit Student
</button>

<!-- Modal component -->
<div x-data x-show="$store.modals.active === 'editStudent'" x-transition>
    <div class="modal-content">
        <h2>Edit Student #<span x-text="$store.modals.data.studentId"></span></h2>
        <!-- Modal content -->
    </div>
</div>
```

---

### 4. Dashboard Store (`$store.dashboard`)

**Purpose**: Persist dashboard filter selections across page refreshes using localStorage.

**Available Methods**:

- `filters.set(key, value)` - Set filter and save to localStorage
- `filters.clear()` - Clear all filters
- `filters.get(key)` - Get current filter value

**Example Usage**:

```html
<!-- Filter dropdowns -->
<select @change="$store.dashboard.setFilter('department', $event.target.value)">
    <option value="">All Departments</option>
    <option value="1">Computer Science</option>
    <option value="2">Engineering</option>
</select>

<!-- Filters persist across page refreshes -->
<div x-data x-init="$store.dashboard.loadFilters()">
    Current Department: <span x-text="$store.dashboard.filters.department"></span>
</div>
```

---

### 5. Table Store (`$store.table`)

**Purpose**: Manage table state including sorting, pagination, and row selection.

**Available State**:

- `sortColumn` - Current sort column
- `sortDirection` - 'asc' or 'desc'
- `currentPage` - Pagination current page
- `perPage` - Items per page
- `selectedRows` - Array of selected row IDs

**Example Usage**:

```html
<!-- Sortable table headers -->
<th @click="$store.table.sort('name')" class="cursor-pointer">
    Name
    <i x-show="$store.table.sortColumn === 'name'" 
       :class="$store.table.sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down'">
    </i>
</th>

<!-- Row selection -->
<input type="checkbox" 
       @change="$store.table.toggleRow($event.target.value)"
       :checked="$store.table.selectedRows.includes('123')">
```

---

### 6. Preferences Store (`$store.preferences`)

**Purpose**: Store user UI preferences (theme, sidebar state, display mode) with localStorage persistence.

**Available State**:

- `theme` - 'light' or 'dark'
- `sidebarCollapsed` - Boolean sidebar state
- `compactMode` - Boolean compact display mode

**Example Usage**:

```html
<!-- Theme toggle -->
<button @click="$store.preferences.theme = $store.preferences.theme === 'light' ? 'dark' : 'light'">
    Toggle Theme
</button>

<!-- Sidebar toggle -->
<button @click="$store.preferences.sidebarCollapsed = !$store.preferences.sidebarCollapsed">
    <i :class="$store.preferences.sidebarCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
</button>
```

---

## Integration Files

### Core Files

1. **`resources/js/stores.js`** - Alpine store definitions (7 stores)
2. **`resources/js/store-helpers.js`** - Global helper functions (notify, gradeState, modal, filters)
3. **`resources/views/components/toast-notifications.blade.php`** - Toast notification component
4. **`resources/js/app.js`** - Imports stores and helpers before Alpine.start()
5. **`resources/views/layouts/app.blade.php`** - Includes toast component before @stack('scripts')

### Enhanced Pages

1. **`resources/views/instructor/manage-grades.blade.php`** - Uses notification store for session flash
2. **`resources/views/instructor/partials/grade-script.blade.php`** - Migrated from global variable to grades store
3. **`resources/views/instructor/partials/grade-table.blade.php`** - Added Alpine unsaved changes badge

---

## Benefits Over Legacy Patterns

### Before: Laravel Session Flash + Bootstrap

```php
@if(session('success'))
    <div class="toast-container">
        <div class="toast show">{{ session('success') }}</div>
    </div>
@endif
```

**Issues**: Not reusable, requires page refresh, no animations, manual DOM manipulation

### After: Alpine Notification Store

```javascript
notify.success('Grade saved successfully!');
```

**Benefits**:

- Call from anywhere (Blade, inline scripts, Alpine components)
- No page refresh needed
- Smooth animations (slide-in/fade-out)
- Multiple notifications queued automatically
- Auto-dismiss with configurable duration
- Consistent styling and positioning

---

### Before: Global JavaScript Variables

```javascript
let hasUnsavedChanges = false;

function clearChanges() {
    hasUnsavedChanges = false; // Manual state update
}

// No automatic UI updates
```

**Issues**: Not reactive, must manually update UI, scattered state logic

### After: Alpine Grades Store

```javascript
Alpine.store('grades').clearUnsaved(); // Reactive state update
```

```html
<div x-show="$store.grades.unsavedChanges" x-transition>
    Unsaved changes detected
</div>
```

**Benefits**:

- Reactive UI updates automatically
- Centralized state management
- Declarative templates with x-show
- Built-in transitions with x-transition
- Type-safe methods (markChanged, clearUnsaved, hasUnsaved)
- No manual DOM manipulation

---

## Best Practices

1. **Use helper functions for simplicity**:

   ```javascript
   // Good - Simple and readable
   notify.success('Saved!');
   
   // Avoid - Direct store access is verbose
   Alpine.store('notifications').add({ type: 'success', message: 'Saved!' });
   ```

2. **Check Alpine availability before store access**:

   ```javascript
   if (Alpine && Alpine.store) {
       Alpine.store('grades').markChanged();
   }
   ```

3. **Use x-data for component-level state, stores for global state**:

   ```html
   <!-- Component-level: Use x-data -->
   <div x-data="{ open: false }">
       <button @click="open = !open">Toggle</button>
   </div>
   
   <!-- Global state: Use stores -->
   <div x-data x-show="$store.modals.active === 'myModal'">
       Modal content
   </div>
   ```

4. **Persist important state with localStorage**:
   - Dashboard filters (already implemented)
   - User preferences (already implemented)
   - Consider for: last selected term, last viewed subject

5. **Use x-transition for smooth animations**:

   ```html
   <div x-show="$store.grades.unsavedChanges" x-transition>
       <!-- Automatically animated show/hide -->
   </div>
   ```

---

## Future Enhancement Ideas

1. **Course Outcomes Store**: Track selected outcomes across multiple subjects
2. **Form Validation Store**: Centralized validation errors for multi-step forms
3. **Activity Store**: Manage activity creation/editing state
4. **Search Store**: Persist search queries and filters
5. **Notification Preferences**: User-configurable notification duration and position

---

## Quick Reference

| Task | Code |
| ------ | ------ |
| Show success toast | `notify.success('Message')` |
| Show error toast | `notify.error('Message')` |
| Mark grades changed | `gradeState.markChanged()` |
| Clear unsaved changes | `gradeState.clearUnsaved()` |
| Check unsaved state | `gradeState.hasUnsaved()` |
| Open modal with data | `modal.open('modalId', { key: value })` |
| Close active modal | `modal.close()` |
| Set dashboard filter | `filters.set('key', value)` |
| Clear all filters | `filters.clear()` |
| React to store in template | `x-show="$store.storeName.property"` |
| Initialize on mount | `x-init="$store.storeName.loadMethod()"` |

---

## Testing

Build successful: `npm run build` exits with code 0

- app-ix97aHah.js (82.63 kB, gzip: 32.24 kB)
- app-CCzK2OX3.css (58.23 kB, gzip: 9.77 kB)
- Zero errors, zero warnings

All stores initialize on `alpine:init` event and are available globally via `Alpine.store()`.
