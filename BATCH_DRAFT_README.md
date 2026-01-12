# 🎓 Batch Draft & Course Outcome Template System

## Quick Start

### 1. Run Setup
```bash
# Linux/Mac
./setup-batch-draft.sh

# Windows
setup-batch-draft.bat
```

### 2. What This System Does

This implementation transforms how Course Outcomes and students are managed:

**Before:**
- ❌ Manual CO generation for each subject
- ❌ Individual student imports by instructors
- ❌ No validation before subject assignment
- ❌ Inconsistent CO configurations

**After:**
- ✅ Reusable CO templates
- ✅ Bulk student imports by chairperson
- ✅ **Mandatory batch draft before instructor assignment**
- ✅ Standardized, consistent configurations

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    CHAIRPERSON WORKFLOW                      │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1️⃣ CREATE CO TEMPLATE                                      │
│     └─ Define reusable CO configurations                     │
│        (e.g., "Standard 3 COs", "Advanced 6 COs")           │
│                                                               │
│  2️⃣ CREATE BATCH DRAFT                                      │
│     ├─ Name: "Student Batch 2024 First Year BSIT"          │
│     ├─ Upload: student_list.csv                             │
│     ├─ Select: CO Template                                  │
│     └─ Configure: Course, Year Level                        │
│                                                               │
│  3️⃣ ATTACH SUBJECTS                                         │
│     └─ Link subjects to batch draft                         │
│                                                               │
│  4️⃣ APPLY CONFIGURATION                                     │
│     ├─ Generate COs from template                           │
│     ├─ Import students to subjects                          │
│     └─ Mark as "configuration applied"                      │
│                                                               │
│  5️⃣ ASSIGN TO INSTRUCTOR                                    │
│     └─ ✅ Only possible if batch draft applied              │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## 📁 Files Created

### Database Migrations
- `database/migrations/2026_01_12_000001_create_course_outcome_templates_table.php`
- `database/migrations/2026_01_12_000002_create_batch_drafts_table.php`

### Models
- `app/Models/CourseOutcomeTemplate.php`
- `app/Models/CourseOutcomeTemplateItem.php`
- `app/Models/BatchDraft.php`
- `app/Models/BatchDraftStudent.php`
- `app/Models/BatchDraftSubject.php`

### Controllers
- `app/Http/Controllers/Chairperson/CourseOutcomeTemplateController.php`
- `app/Http/Controllers/Chairperson/BatchDraftController.php`

### Views Created
- `resources/views/chairperson/co-templates/index.blade.php` ✅

### Views Needed (Templates Provided)
- `resources/views/chairperson/co-templates/create.blade.php`
- `resources/views/chairperson/co-templates/show.blade.php`
- `resources/views/chairperson/co-templates/edit.blade.php`
- `resources/views/chairperson/batch-drafts/index.blade.php`
- `resources/views/chairperson/batch-drafts/create.blade.php`
- `resources/views/chairperson/batch-drafts/show.blade.php`

### Documentation
- `docs/BATCH_DRAFT_CO_TEMPLATE_SYSTEM.md` - Complete system documentation
- `docs/BATCH_DRAFT_IMPLEMENTATION_SUMMARY.md` - Implementation details
- `sample_imports/batch_draft_students_template.csv` - CSV template

## 🔄 New Routes

### CO Templates
```
GET    /chairperson/co-templates                         - List templates
GET    /chairperson/co-templates/create                  - Create form
POST   /chairperson/co-templates                         - Store template
GET    /chairperson/co-templates/{id}                    - View details
GET    /chairperson/co-templates/{id}/edit               - Edit form
PUT    /chairperson/co-templates/{id}                    - Update template
DELETE /chairperson/co-templates/{id}                    - Delete template
POST   /chairperson/co-templates/{id}/toggle-status      - Toggle active status
```

### Batch Drafts
```
GET    /chairperson/batch-drafts                         - List batch drafts
GET    /chairperson/batch-drafts/create                  - Create form
POST   /chairperson/batch-drafts                         - Store batch draft
GET    /chairperson/batch-drafts/{id}                    - View details
DELETE /chairperson/batch-drafts/{id}                    - Delete batch draft
POST   /chairperson/batch-drafts/{id}/attach-subjects    - Attach subjects
POST   /chairperson/batch-drafts/{id}/apply-configuration - Apply config
```

## 🎯 Key Validation Rule

**CRITICAL**: Subjects can only be assigned to instructors if they have a batch draft configuration applied.

```php
// In ChairpersonController::storeAssignedSubject()
$hasBatchDraft = \App\Models\BatchDraftSubject::where('subject_id', $subject->id)
    ->where('configuration_applied', true)
    ->exists();

if (!$hasBatchDraft) {
    return redirect()->back()->with('error', 
        'Cannot assign subject: batch draft required');
}
```

This ensures:
- ✅ All subjects have proper COs before assignment
- ✅ All subjects have students enrolled before assignment
- ✅ Consistent configuration across all subjects
- ✅ No subjects can "slip through" without proper setup

## 📊 Database Schema

### course_outcome_templates
Stores reusable CO configurations
- `id`, `template_name`, `description`
- `created_by`, `course_id`, `is_universal`
- `is_active`, `is_deleted`

### course_outcome_template_items
Individual COs within a template
- `id`, `template_id`, `co_code`
- `description`, `order`

### batch_drafts
Configuration packages for subjects
- `id`, `batch_name`, `academic_period_id`
- `course_id`, `year_level`, `co_template_id`
- `created_by`, `is_active`, `is_deleted`

### batch_draft_students
Students imported in a batch
- `id`, `batch_draft_id`, `student_id`
- `first_name`, `middle_name`, `last_name`
- `year_level`, `course_id`

### batch_draft_subjects
Links subjects to batch drafts
- `id`, `batch_draft_id`, `subject_id`
- `configuration_applied` ← **Key field for validation**

## 📝 Example Workflow

### Creating a CO Template

1. Navigate to `/chairperson/co-templates`
2. Click "Create New Template"
3. Fill in:
   - **Template Name**: "Standard 3 COs - BSIT First Year"
   - **Description**: "Standard three course outcomes for first year subjects"
   - **CO Items**:
     - CO1: "Demonstrate fundamental knowledge"
     - CO2: "Apply concepts in practical scenarios"
     - CO3: "Achieve 75% of course outcomes"

### Creating a Batch Draft

1. Navigate to `/chairperson/batch-drafts`
2. Click "Create Batch Draft"
3. Fill in:
   - **Batch Name**: "Student Batch 2024-2025 First Year BSIT"
   - **Course**: BSIT
   - **Year Level**: 1
   - **CO Template**: Select "Standard 3 COs - BSIT First Year"
   - **Student File**: Upload `students.csv` with columns:
     ```
     Student ID, First Name, Middle Name, Last Name
     2024001, John, M, Doe
     2024002, Jane, A, Smith
     ```

### Applying Configuration

1. Open the batch draft details
2. Click "Attach Subjects"
3. Select subjects (e.g., IT 101, IT 102, IT 103)
4. Click "Apply Configuration"
5. System will:
   - Create CO1, CO2, CO3 for each subject
   - Import all students to each subject
   - Mark subjects as "configuration applied"

### Assigning to Instructor

1. Navigate to `/chairperson/assign-subjects`
2. Subjects with batch draft will show a ✅ badge
3. Select instructor for configured subjects
4. Click "Assign"
5. **Validation**: If subject lacks batch draft, assignment fails with clear error message

## ⚠️ Important Notes

1. **Breaking Change**: Old workflow no longer works for new subjects
2. **Student Import**: Now chairperson-level instead of instructor-level
3. **CO Generation**: Template-based instead of manual
4. **Mandatory Validation**: Enforced at controller level

## 🧪 Testing Checklist

- [ ] Run migrations successfully
- [ ] Create a CO template
- [ ] View template list
- [ ] Edit template
- [ ] Create batch draft with CSV
- [ ] View imported students
- [ ] Attach subjects to batch
- [ ] Apply configuration
- [ ] Verify COs created
- [ ] Verify students imported
- [ ] Try assigning subject without batch (should fail)
- [ ] Assign subject with batch (should succeed)
- [ ] Delete unused template
- [ ] Delete batch draft

## 📚 Documentation

### Full Documentation
- **System Documentation**: `docs/BATCH_DRAFT_CO_TEMPLATE_SYSTEM.md`
- **Implementation Summary**: `docs/BATCH_DRAFT_IMPLEMENTATION_SUMMARY.md`

### Quick Reference
- **Sample CSV**: `sample_imports/batch_draft_students_template.csv`
- **Setup Script**: `setup-batch-draft.sh` (Linux/Mac) or `setup-batch-draft.bat` (Windows)

## 🐛 Troubleshooting

**Cannot assign subject to instructor**
→ Ensure subject has batch draft with `configuration_applied = true`

**Student CSV import fails**
→ Check CSV format: `Student ID, First Name, Middle Name, Last Name`

**Template cannot be deleted**
→ Template is in use by batch drafts - remove dependencies first

**Batch name already exists**
→ Batch names must be unique per academic period

## 🚀 Next Steps

1. ✅ Run `./setup-batch-draft.sh`
2. 📝 Create remaining Blade views (6 files needed)
3. 🧪 Test complete workflow
4. 📖 Train chairpersons on new process
5. 🎯 Monitor first batch draft creation
6. 📊 Gather feedback and iterate

## 📞 Support

For questions or issues:
1. Check documentation in `docs/` folder
2. Review code comments in controllers
3. Test with sample data first
4. Document any bugs or improvement suggestions

---

**Version**: 1.0.0  
**Date**: January 12, 2026  
**Status**: Core implementation complete - views needed  
**Priority**: High - Required for subject assignment workflow
