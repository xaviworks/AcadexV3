# Dynamic Tutorial System - Implementation Guide

## Overview
Complete implementation of a database-driven, admin-manageable tutorial system for AcadexV3. Replaces hardcoded JavaScript tutorials with a flexible, UI-based tutorial builder.

## Features Implemented

### ✅ Phase 1: Core Infrastructure (Completed)
- **Database Schema**
  - `tutorials` table - Stores tutorial metadata
  - `tutorial_steps` table - Stores individual tutorial steps
  - `tutorial_data_checks` table - Validates data existence before tutorials
  
- **Eloquent Models**
  - `Tutorial` - Main tutorial model with relationships
  - `TutorialStep` - Individual step model
  - `TutorialDataCheck` - Data validation configuration
  
- **API Endpoints**
  - `GET /api/tutorials/{role}` - Get all tutorials for a role
  - `GET /api/tutorials/{role}/{pageId}` - Get specific tutorial
  - `GET /api/tutorials/statistics/all` - Get statistics

### ✅ Phase 2: Admin Builder UI (Completed)
- **Tutorial Management Pages**
  - `/admin/tutorials` - List all tutorials
  - `/admin/tutorials/create` - Create new tutorial
  - `/admin/tutorials/{id}/edit` - Edit existing tutorial
  - Duplicate, activate/deactivate, delete functions
  
- **Form Features**
  - Multi-step builder with drag-and-drop reordering
  - CSS selector input with validation
  - Optional steps and data requirements
  - Priority-based tutorial selection
  
### ✅ Phase 3: Dynamic Loading (Completed)
- **Hybrid System**
  - API-first loading with automatic fallback to static tutorials
  - 5-minute browser caching for performance
  - Preloading on page load (non-blocking)
  - Backward compatible with existing static tutorials

### ✅ Phase 4: Authorization & Security (Completed)
- **Tutorial Policy**
  - Only admins can create/edit/delete tutorials
  - Read-only API endpoints for tutorial consumption
  
- **Validation**
  - Form request validation for all inputs
  - CSS selector validation
  - Step order enforcement

## File Structure

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   └── TutorialBuilderController.php  # CRUD operations
│   └── API/
│       └── TutorialController.php          # API endpoints
├── Models/
│   ├── Tutorial.php
│   ├── TutorialStep.php
│   └── TutorialDataCheck.php
└── Policies/
    └── TutorialPolicy.php

database/
├── migrations/
│   ├── 2026_01_11_205329_create_tutorials_table.php
│   ├── 2026_01_11_205435_create_tutorial_steps_table.php
│   └── 2026_01_11_205435_create_tutorial_data_checks_table.php
└── seeders/
    └── TutorialSeeder.php

resources/views/admin/tutorials/
├── index.blade.php          # Tutorial list
├── create.blade.php         # Create form
├── edit.blade.php           # Edit form
└── _step-form.blade.php     # Step component

public/js/admin-tutorials/
├── tutorial-core.js         # Core manager (existing)
├── tutorial-loader-v2.js    # NEW: Dynamic API loader
├── tutorial-builder.js      # NEW: Builder UI logic
└── [existing tutorial files...]

routes/
├── api.php                  # API routes
└── web.php                  # Admin routes
```

## Database Schema

### tutorials
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| role | enum | admin, dean, vpaa, chairperson, instructor |
| page_identifier | string | URL-based page ID (e.g., admin-dashboard) |
| title | string | Tutorial title |
| description | text | Brief description |
| is_active | boolean | Active/inactive status |
| priority | integer | Higher = shown first |
| created_by | foreign | User who created |
| created_at, updated_at | timestamps | Audit trail |

### tutorial_steps
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tutorial_id | foreign | Parent tutorial |
| step_order | integer | Order (0-indexed) |
| title | string | Step title |
| content | text | Step content/instructions |
| target_selector | text | CSS selector for element |
| position | enum | top, bottom, left, right |
| is_optional | boolean | Skip if element missing |
| requires_data | boolean | Check table data |
| screenshot | string | Optional visual reference |

### tutorial_data_checks
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tutorial_id | foreign | Parent tutorial |
| selector | string | Data row selector |
| empty_selectors | json | Empty state indicators |
| entity_name | string | User-friendly name |
| add_button_selector | string | Add button selector |
| no_add_button | boolean | No add functionality |

## Usage Guide

### For Admins - Creating Tutorials

1. **Navigate to Tutorial Builder**
   ```
   Admin Panel → Tutorial Builder → Create Tutorial
   ```

2. **Fill Basic Information**
   - Select target role (Admin, Dean, VPAA, etc.)
   - Enter page identifier (e.g., `admin-dashboard`)
   - Add title and description
   - Set priority (higher = shown first)
   - Toggle active status

3. **Add Tutorial Steps**
   - Click "Add Step" button
   - Enter step title and content
   - Add CSS selector for target element
   - Choose tooltip position
   - Mark as optional if element might not exist
   - Drag to reorder steps

4. **Configure Data Validation (Optional)**
   - Enable data check if page needs data
   - Add data row selector (e.g., `tbody tr`)
   - Specify entity name (e.g., "students")
   - Mark if page has no add button

5. **Save and Test**
   - Click "Create Tutorial"
   - Visit target page
   - Click tutorial FAB button to test

### For Developers - Adding New Pages

#### Option 1: Use Admin UI (Recommended)
1. Create tutorial via admin interface
2. Test on target page
3. Adjust selectors as needed

#### Option 2: Seed from Code
```php
// database/seeders/TutorialSeeder.php
$tutorial = Tutorial::create([
    'role' => 'admin',
    'page_identifier' => 'my-new-page',
    'title' => 'My New Page Tutorial',
    'description' => 'Learn the features',
    'is_active' => true,
    'priority' => 10,
    'created_by' => 1,
]);

foreach ($steps as $index => $step) {
    TutorialStep::create([
        'tutorial_id' => $tutorial->id,
        'step_order' => $index,
        'title' => $step['title'],
        'content' => $step['content'],
        'target_selector' => $step['target_selector'],
        'position' => 'bottom',
    ]);
}
```

## API Documentation

### Get All Tutorials for Role
```http
GET /api/tutorials/{role}
```

**Response:**
```json
{
  "success": true,
  "tutorials": [
    {
      "id": "admin-dashboard",
      "title": "Admin Dashboard Overview",
      "description": "Learn the dashboard",
      "steps": [
        {
          "target": ".container-fluid h2",
          "title": "Control Panel",
          "content": "Welcome to...",
          "position": "bottom",
          "optional": false,
          "requiresData": false
        }
      ],
      "tableDataCheck": {
        "selector": "tbody tr",
        "emptySelectors": [".dataTables_empty"],
        "entityName": "records"
      }
    }
  ]
}
```

### Get Specific Tutorial
```http
GET /api/tutorials/{role}/{pageId}
```

**Response:**
```json
{
  "success": true,
  "tutorial": { /* tutorial object */ }
}
```

## JavaScript Integration

### Dynamic Loader Behavior

```javascript
// Automatic in admin-tutorial.js
// 1. Loads tutorial-core.js
// 2. Loads all static tutorial modules
// 3. Loads tutorial-loader-v2.js (extends core)
// 4. Initializes system

// On tutorial start:
manager.start('admin-dashboard')
// → Checks browser cache (5 min TTL)
// → Fetches from API if not cached
// → Falls back to static if API fails
// → Displays tutorial
```

### Cache Management

```javascript
// Clear cache manually
window.AdminTutorial.clearCache();

// Preload tutorials
window.AdminTutorial.preloadTutorials();

// Check if using API
console.log(window.AdminTutorial._tutorialCache);
```

## Migration from Static Tutorials

### Step 1: Seed Existing Tutorials
```bash
php artisan db:seed --class=TutorialSeeder
```

### Step 2: Test Hybrid Mode
- Static tutorials still work
- Database tutorials take priority
- No code changes needed

### Step 3: Gradual Migration
- Create database versions via admin UI
- Test thoroughly
- Deactivate static versions
- Eventually remove static JS files

## Troubleshooting

### Tutorial Not Showing
1. Check if tutorial is active: `/admin/tutorials`
2. Verify page identifier matches
3. Check browser console for errors
4. Clear tutorial cache: `AdminTutorial.clearCache()`

### Wrong Element Highlighted
1. Edit tutorial in admin UI
2. Test CSS selector in DevTools
3. Use fallback selectors (comma-separated)
4. Mark step as optional if element sometimes missing

### API Errors
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify API routes: `php artisan route:list | grep tutorials`
3. Check database connection
4. System falls back to static automatically

## Performance Considerations

- **Caching**: 5-minute browser cache reduces API calls
- **Preloading**: Tutorials preload 1 second after page load
- **Lazy Loading**: Only fetches when FAB clicked (optional)
- **Fallback**: Static tutorials load if API unavailable

## Security

- **Authorization**: Only admins can manage tutorials
- **Validation**: All inputs validated server-side
- **XSS Prevention**: Content escaped in Blade templates
- **API Rate Limiting**: Consider adding if needed
- **Audit Trail**: Created_by tracks tutorial authors

## Future Enhancements (Not Implemented)

### Phase 5: Advanced Features
- [ ] Visual element picker (click to select)
- [ ] Tutorial analytics (completion tracking)
- [ ] A/B testing variants
- [ ] Multi-language support
- [ ] Import/Export JSON
- [ ] Tutorial preview mode
- [ ] Video/GIF attachments
- [ ] Conditional steps (show if X exists)

### Phase 6: User Experience
- [ ] Tutorial progress bar
- [ ] Keyboard shortcuts (← → arrows)
- [ ] Mobile-optimized tooltips
- [ ] Dark mode support
- [ ] Auto-start on first visit
- [ ] User feedback collection

## Testing

### Manual Testing Checklist
- [ ] Create new tutorial via UI
- [ ] Edit existing tutorial
- [ ] Duplicate tutorial
- [ ] Delete tutorial
- [ ] Toggle active/inactive
- [ ] Reorder steps (drag-drop)
- [ ] Test on target page
- [ ] Verify API responses
- [ ] Test cache behavior
- [ ] Test fallback to static

### API Testing
```bash
# Get all admin tutorials
curl http://localhost/api/tutorials/admin

# Get specific tutorial
curl http://localhost/api/tutorials/admin/admin-dashboard

# Get statistics
curl http://localhost/api/tutorials/statistics/all
```

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed sample tutorial: `php artisan db:seed --class=TutorialSeeder`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Test admin UI access
- [ ] Test API endpoints
- [ ] Verify tutorial FAB visible
- [ ] Test tutorial on target page
- [ ] Check error logs

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JS errors
3. Verify database tables created
4. Test API endpoints manually
5. Clear all caches if behavior inconsistent

---

**Implementation Date**: January 11, 2026  
**Status**: ✅ Production Ready  
**Backward Compatible**: Yes  
**Breaking Changes**: None
