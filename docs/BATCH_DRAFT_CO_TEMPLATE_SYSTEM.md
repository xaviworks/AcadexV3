# Batch Draft & CO Template System

## Overview
This system implements a new workflow for managing Course Outcomes (COs) and student imports through a template-based batch draft system. The key change is that **subjects can only be assigned to instructors if they have a batch draft configuration applied**.

## Architecture Components

### 1. Course Outcome Templates
**Purpose**: Reusable CO configurations that can be applied to multiple subjects

**Database Tables**:
- `course_outcome_templates` - Template metadata
- `course_outcome_template_items` - Individual CO items within a template

**Key Features**:
- Templates can be course-specific or universal (for GE coordinators)
- Each template contains multiple CO items (CO1, CO2, etc.)
- Templates are created by chairpersons or GE coordinators
- Can be activated/deactivated without deletion

**Models**:
- `App\Models\CourseOutcomeTemplate`
- `App\Models\CourseOutcomeTemplateItem`

**Routes**:
```php
GET    /chairperson/co-templates              - List all templates
GET    /chairperson/co-templates/create       - Create new template
POST   /chairperson/co-templates              - Store new template
GET    /chairperson/co-templates/{id}         - View template
GET    /chairperson/co-templates/{id}/edit    - Edit template
PUT    /chairperson/co-templates/{id}         - Update template
DELETE /chairperson/co-templates/{id}         - Delete template
POST   /chairperson/co-templates/{id}/toggle-status - Toggle active status
```

### 2. Batch Drafts
**Purpose**: Configuration packages that bundle students, CO templates, and subject metadata

**Database Tables**:
- `batch_drafts` - Batch draft metadata
- `batch_draft_students` - Students imported for this batch
- `batch_draft_subjects` - Subjects associated with this batch

**Key Features**:
- Each batch draft has a unique name per academic period
- Contains imported student list (CSV/Excel)
- References a CO template for subject configuration
- Can be applied to one or multiple subjects
- Tracks which subjects have had configuration applied

**Models**:
- `App\Models\BatchDraft`
- `App\Models\BatchDraftStudent`
- `App\Models\BatchDraftSubject`

**Routes**:
```php
GET    /chairperson/batch-drafts                         - List all batch drafts
GET    /chairperson/batch-drafts/create                  - Create new batch draft
POST   /chairperson/batch-drafts                         - Store new batch draft
GET    /chairperson/batch-drafts/{id}                    - View batch draft
DELETE /chairperson/batch-drafts/{id}                    - Delete batch draft
POST   /chairperson/batch-drafts/{id}/attach-subjects    - Attach subjects to batch
POST   /chairperson/batch-drafts/{id}/apply-configuration - Apply config to subjects
```

## New Workflow

### Step 1: Create CO Template (Chairperson/GE Coordinator)
1. Navigate to **CO Templates**
2. Click **Create Template**
3. Enter template name and description
4. Add CO items (CO1, CO2, CO3, etc.) with descriptions
5. Save template

**Example Template**:
- Template Name: "Standard 3 COs - BSIT"
- Items:
  - CO1: "Students can demonstrate knowledge of fundamental concepts"
  - CO2: "Students can apply learned skills in practical scenarios"
  - CO3: "Students can achieve 75% of the course outcomes"

### Step 2: Create Batch Draft (Chairperson)
1. Navigate to **Batch Drafts**
2. Click **Create Batch Draft**
3. Enter batch name (e.g., "Student Batch 2024 First Year BSIT")
4. Select course and year level
5. Select CO template
6. Upload student list (CSV/Excel)
7. Save batch draft

**CSV Format**:
```
Student ID,First Name,Middle Name,Last Name
2024001,John,M,Doe
2024002,Jane,A,Smith
```

### Step 3: Attach Subjects to Batch Draft
1. Open the batch draft details page
2. Click **Attach Subjects**
3. Select one or more subjects
4. Click **Attach Selected Subjects**

### Step 4: Apply Configuration
1. From the batch draft details page
2. Select subjects to configure
3. Click **Apply Configuration**
4. This will:
   - Create COs for each subject using the template
   - Import students into each subject
   - Mark subjects as "configuration applied"

### Step 5: Assign Subjects to Instructors
1. Navigate to **Assign Subjects**
2. Subjects with batch draft configuration will be marked with a badge
3. Select instructor for each subject
4. **Validation**: Can only assign subjects that have batch draft configuration applied
5. Click **Assign**

## Database Schema

### course_outcome_templates
```sql
id bigint PRIMARY KEY
template_name varchar(255)
description text
created_by bigint FK(users)
course_id bigint FK(courses) NULLABLE
is_universal boolean DEFAULT false
is_active boolean DEFAULT true
is_deleted boolean DEFAULT false
created_at timestamp
updated_at timestamp
```

### course_outcome_template_items
```sql
id bigint PRIMARY KEY
template_id bigint FK(course_outcome_templates)
co_code varchar(255)  -- "CO1", "CO2", etc.
description text
order integer
created_at timestamp
updated_at timestamp
```

### batch_drafts
```sql
id bigint PRIMARY KEY
batch_name varchar(255)
academic_period_id bigint FK(academic_periods)
course_id bigint FK(courses)
year_level integer
co_template_id bigint FK(course_outcome_templates)
created_by bigint FK(users)
is_active boolean DEFAULT true
is_deleted boolean DEFAULT false
created_at timestamp
updated_at timestamp
UNIQUE(batch_name, academic_period_id)
```

### batch_draft_students
```sql
id bigint PRIMARY KEY
batch_draft_id bigint FK(batch_drafts)
student_id varchar(255) NULLABLE
first_name varchar(255)
middle_name varchar(255) NULLABLE
last_name varchar(255)
year_level integer
course_id bigint FK(courses)
created_at timestamp
updated_at timestamp
INDEX(batch_draft_id, student_id)
```

### batch_draft_subjects
```sql
id bigint PRIMARY KEY
batch_draft_id bigint FK(batch_drafts)
subject_id bigint FK(subjects)
configuration_applied boolean DEFAULT false
created_at timestamp
updated_at timestamp
UNIQUE(batch_draft_id, subject_id)
```

## Business Rules

1. **Template Creation**:
   - Chairpersons can create templates for their course
   - GE coordinators can create universal templates
   - Templates can be edited before being used in batch drafts
   - Cannot delete templates that are in use

2. **Batch Draft Creation**:
   - Batch name must be unique per academic period
   - Must select a CO template before saving
   - Student file is required (CSV/Excel format)
   - Students are imported and stored in batch_draft_students table

3. **Subject Assignment Validation**:
   - **CRITICAL**: Subjects can ONLY be assigned to instructors if `configuration_applied = true` in batch_draft_subjects
   - This ensures all subjects have proper COs and students before assignment
   - Assignment validation happens in `ChairpersonController::storeAssignedSubject()`

4. **Configuration Application**:
   - Can apply configuration to multiple subjects at once
   - Creates COs for each subject using the template
   - Imports all batch students into each subject
   - Marks subjects as configured in batch_draft_subjects

## Migration from Old System

### Old System:
- COs generated directly by chairperson via "Generate COs" modal
- Students imported individually by instructors
- No validation before subject assignment

### New System:
- COs created from reusable templates
- Students imported in batches at chairperson level
- **Mandatory batch draft before assignment**

### Migration Steps:
1. Run migrations to create new tables
2. Create default CO templates for existing courses
3. (Optional) Create batch drafts for current academic period
4. Start using new workflow for new assignments

## Testing Checklist

- [ ] Create CO template (chairperson)
- [ ] Create CO template (GE coordinator)
- [ ] Edit existing template
- [ ] Delete unused template
- [ ] Create batch draft with student CSV
- [ ] Attach subjects to batch draft
- [ ] Apply configuration to subjects
- [ ] Verify COs created correctly
- [ ] Verify students imported correctly
- [ ] Try to assign subject without batch draft (should fail)
- [ ] Assign subject with batch draft (should succeed)
- [ ] Verify instructor can see students and COs

## Files Created/Modified

### New Files:
- `database/migrations/2026_01_12_000001_create_course_outcome_templates_table.php`
- `database/migrations/2026_01_12_000002_create_batch_drafts_table.php`
- `app/Models/CourseOutcomeTemplate.php`
- `app/Models/CourseOutcomeTemplateItem.php`
- `app/Models/BatchDraft.php`
- `app/Models/BatchDraftStudent.php`
- `app/Models/BatchDraftSubject.php`
- `app/Http/Controllers/Chairperson/CourseOutcomeTemplateController.php`
- `app/Http/Controllers/Chairperson/BatchDraftController.php`

### Modified Files:
- `routes/web.php` - Added new routes for templates and batch drafts
- `app/Http/Controllers/ChairpersonController.php` - Added batch draft validation to assignSubjects and storeAssignedSubject

### Views to Create:
- `resources/views/chairperson/co-templates/index.blade.php`
- `resources/views/chairperson/co-templates/create.blade.php`
- `resources/views/chairperson/co-templates/show.blade.php`
- `resources/views/chairperson/co-templates/edit.blade.php`
- `resources/views/chairperson/batch-drafts/index.blade.php`
- `resources/views/chairperson/batch-drafts/create.blade.php`
- `resources/views/chairperson/batch-drafts/show.blade.php`

## Next Steps

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Create views for the new features (see Views to Create section)

3. Test the complete workflow

4. Update existing subjects to have batch drafts (if needed for current academic period)

5. Train chairpersons and GE coordinators on new workflow

## Support & Troubleshooting

**Issue**: Cannot assign subject to instructor
**Solution**: Ensure the subject has a batch draft with configuration applied

**Issue**: Template cannot be deleted
**Solution**: Check if any batch drafts are using this template

**Issue**: Student import fails
**Solution**: Verify CSV format matches expected columns: Student ID, First Name, Middle Name, Last Name

**Issue**: Batch draft name already exists
**Solution**: Batch names must be unique per academic period - choose a different name
