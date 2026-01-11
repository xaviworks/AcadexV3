# Default Tutorials Seeder

## Overview

The `DefaultTutorialsSeeder` preserves all legacy tutorials from the original JavaScript-based tutorial system as database seedable content. This ensures backward compatibility and provides a baseline set of educational content for the platform.

## Migration Details

- **Migration Date:** January 11, 2026
- **Source Files:** `public/js/*-tutorials/*.js`
- **Target:** Database-backed tutorial system
- **Seeder File:** `database/seeders/DefaultTutorialsSeeder.php`

## What Was Migrated

All tutorials from the following JavaScript modules were extracted and converted to database seeders:

### Admin Tutorials
- ✅ Admin Dashboard Overview (`admin-dashboard`)
- ⏳ User Management (`admin-users`)
- ⏳ Session & Activity Monitor (`admin-sessions`)
- ⏳ Disaster Recovery (`admin-disaster-recovery`)
- ⏳ Academic Structure Management
- ⏳ Grades Formula Management
- ⏳ Help Guides Management
- ⏳ Structure Template Requests

### Dean Tutorials
- ⏳ Dean Dashboard (`dean-dashboard`)
- ⏳ Department Instructors (`dean-instructors`)
- ⏳ Department Students (`dean-students`)
- ⏳ Grades Management (Multi-step wizard)
- ⏳ CO Reports

### VPAA Tutorials
- ⏳ VPAA Dashboard (`vpaa-dashboard`)
- ⏳ Departments Overview (`vpaa-departments`)
- ⏳ Instructor Management (`vpaa-instructors`)
- ⏳ Students Management (`vpaa-students`)
- ⏳ Final Grades Management
- ⏳ CO Attainment
- ⏳ CO Reports (Student, Course, Program)

### Chairperson Tutorials
- ⏳ Chairperson tutorials (TBD)

### Instructor Tutorials
- ⏳ Instructor tutorials (TBD)

## Usage

### Running the Seeder

The `DefaultTutorialsSeeder` is automatically called by `DatabaseSeeder`:

```bash
# Seed all default data including tutorials
php artisan db:seed

# Seed only default tutorials
php artisan db:seed --class=DefaultTutorialsSeeder
```

### Fresh Installation

```bash
# Fresh migration with all seeders
php artisan migrate:fresh --seed
```

### Updating Default Tutorials

When adding new default tutorials:

1. Add the tutorial method to `DefaultTutorialsSeeder.php`
2. Call the method from the `run()` method
3. Follow the existing naming convention: `seedRolePage()`

Example:
```php
private function seedAdminUsers(User $adminUser): void
{
    $tutorial = Tutorial::create([
        'role' => 'admin',
        'page_identifier' => 'admin-users',
        'title' => 'User Management',
        'description' => 'Learn how to manage system users',
        'is_active' => true,
        'priority' => 10,
        'created_by' => $adminUser->id,
    ]);

    $steps = [
        [
            'title' => 'Step Title',
            'content' => 'Step content...',
            'target_selector' => '.css-selector',
            'position' => 'bottom',
            'is_optional' => false,
            'requires_data' => false,
        ],
        // ... more steps
    ];

    foreach ($steps as $index => $stepData) {
        TutorialStep::create([
            'tutorial_id' => $tutorial->id,
            'step_order' => $index,
            'title' => $stepData['title'],
            'content' => $stepData['content'],
            'target_selector' => $stepData['target_selector'],
            'position' => $stepData['position'],
            'is_optional' => $stepData['is_optional'],
            'requires_data' => $stepData['requires_data'],
        ]);
    }

    $this->command->info("  ✓ Admin Users: {$tutorial->title}");
}
```

## Tutorial Structure

Each tutorial consists of:

### Tutorial Properties
- `role`: User role (admin, dean, vpaa, chairperson, instructor)
- `page_identifier`: Unique page ID (e.g., 'admin-dashboard')
- `title`: Tutorial title
- `description`: Short description
- `is_active`: Whether tutorial is active (default: true)
- `priority`: Display priority (higher = shown first)
- `created_by`: Admin user ID

### Step Properties
- `tutorial_id`: Parent tutorial ID
- `step_order`: Step sequence (0-indexed)
- `title`: Step title
- `content`: Step content/instructions
- `target_selector`: CSS selector for highlighted element
- `position`: Tooltip position (top, bottom, left, right)
- `is_optional`: Whether step is optional
- `requires_data`: Whether step requires data to be present

## Backward Compatibility

The original JavaScript tutorial files remain in place for reference:
- `public/js/admin-tutorials/`
- `public/js/dean-tutorials/`
- `public/js/vpaa-tutorials/`

These files are now **read-only** and serve as historical reference. All new tutorials should be created through the Tutorial Builder admin interface or added to `DefaultTutorialsSeeder`.

## Migration Status Legend

- ✅ Migrated and seeded
- ⏳ Pending migration
- 🔄 In progress
- ❌ Deprecated/removed

## Notes for Developers

1. **Do not edit JavaScript tutorial files** - they are deprecated
2. **Use Tutorial Builder** - for creating new tutorials via admin UI
3. **Update this seeder** - when adding new baseline tutorials
4. **Test after seeding** - verify tutorials display correctly
5. **Document changes** - update this file when modifying default tutorials

## Related Files

- `database/seeders/DefaultTutorialsSeeder.php` - Main seeder file
- `database/seeders/TutorialSeeder.php` - Deprecated sample seeder
- `database/seeders/DatabaseSeeder.php` - Master seeder
- `app/Models/Tutorial.php` - Tutorial model
- `app/Models/TutorialStep.php` - Tutorial step model
- `app/Http/Controllers/Admin/TutorialBuilderController.php` - Tutorial builder

## See Also

- [Dynamic Tutorial System Documentation](DYNAMIC_TUTORIAL_SYSTEM.md)
- [Tutorial Builder Admin Guide](../README.md#tutorial-builder)
