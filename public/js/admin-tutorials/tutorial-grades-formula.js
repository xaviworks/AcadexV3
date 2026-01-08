/**
 * Admin Tutorial - Grades Formula
 * Tutorials for all Grades Formula management pages
 */

(function() {
    'use strict';

    // Wait for AdminTutorial to be available
    if (typeof window.AdminTutorial === 'undefined') {
        console.warn('AdminTutorial core not loaded. Grades formula tutorial registration deferred.');
        return;
    }

    // Register the grades formula select period tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-select', {
        title: 'Select Academic Period for Formulas',
        description: 'Learn how to select a period before managing grade formulas',
        steps: [
            {
                target: '#academic-period-select',
                title: 'Select Academic Period',
                content: 'Choose which academic period to manage formulas for. Options include: "All Academic Periods" (global formulas), or specific periods like "2025-2026 - 1st Semester".',
                position: 'bottom'
            },
            {
                target: 'option[value="all"]',
                title: 'All Periods Option',
                content: 'Select "All Academic Periods" to manage global baseline formulas that apply across all periods unless overridden.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#continue-button',
                title: 'Continue Button',
                content: 'After selecting a period, click Continue. The button enables only after you make a selection.',
                position: 'top'
            },
            {
                target: '.btn-outline-secondary, a[href*="departments"]',
                title: 'Back to Departments',
                content: 'Return to the Departments page if you need to manage academic structure first.',
                position: 'right',
                optional: true
            }
        ]
    });

    // Register the grades formula main tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula', {
        title: 'Grades Formula Management',
        description: 'Learn how to configure grading scales, structure templates, global formulas, and department baselines',
        steps: [
            {
                target: '.container-fluid h1, .h3.text-dark.fw-bold',
                title: 'Grades Formula Management',
                content: 'Welcome to the Grades Formula Management system! This is the central hub for configuring how student grades are calculated across the entire institution. You can set up structure templates, global formulas, and department-specific baselines.',
                position: 'bottom'
            },
            {
                target: 'select[name="academic_period_id"]',
                title: 'Academic Period Filter',
                content: 'Filter formulas by academic period. Select "All Periods" to view global formulas that apply across all semesters, or choose a specific period to see period-specific configurations.',
                position: 'bottom'
            },
            {
                target: '.bg-gradient-green-card, .card.bg-gradient-green-card',
                title: 'Wildcard Summary Card',
                content: 'This summary shows the overall formula status: total departments, how many have custom formula catalogs, and how many use the system baseline. Use this as a quick health check for your grading configurations.',
                position: 'bottom'
            },
            {
                target: '.wildcard-section-btn[data-section-target="overview"]',
                title: 'Overview Tab',
                content: 'The Overview tab displays all departments as "wildcard" cards. Each card represents a department and shows its formula status: whether it has a custom baseline or uses the global default. Click any department to drill down into its course and subject formulas.',
                position: 'bottom'
            },
            {
                target: '.wildcard-section-btn[data-section-target="formulas"]',
                title: 'Formulas Tab',
                content: 'The Formulas tab is where you manage: 1) Structure Templates (reusable grading blueprints), 2) Global Formulas (department-independent formulas), and 3) Department Baselines. This is your primary configuration workspace.',
                position: 'bottom'
            },
            {
                target: 'form select[name="semester"]',
                title: 'Semester Filter',
                content: 'Further filter the department view by semester: 1st, 2nd, or Summer. This helps focus on specific periods when managing large numbers of courses.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#overview-department-grid .wildcard-card:first-of-type, .wildcard-card:first-of-type',
                title: 'Department Wildcard Card',
                content: 'Each card represents a department. The code appears in the circle, description below. Badge colors indicate status: Green checkmark = has custom baseline, Gray = using global default. Yellow/Red warnings show courses or subjects needing configuration.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-success.text-white.px-3',
                title: 'Baseline Status Badge',
                content: 'This badge shows the current baseline formula being used. When departments don\'t have custom formulas, they inherit from the global baseline, ensuring all courses have a valid grading configuration.',
                position: 'left',
                optional: true
            },
            {
                target: '.badge.bg-warning',
                title: 'Pending Configuration Warnings',
                content: 'Yellow or red warning badges indicate courses or subjects that need formula configuration. Address these to ensure all grades can be calculated correctly.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'a[href*="structure-template-requests"]',
                title: 'Formula Requests Link',
                content: 'Chairpersons can submit formula template requests for approval. Click here to review pending submissions. Approved templates become available system-wide.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register the grades formula formulas section tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-formulas', {
        title: 'Formula Templates & Global Formulas',
        description: 'Learn how to create and manage structure templates, global formulas, and department baselines',
        steps: [
            {
                target: '#open-create-template',
                title: 'Create Structure Template',
                content: 'Structure Templates are reusable grading blueprints. Create templates for common course types like "Lecture Only" (theory courses) or "Lecture + Lab" (courses with practical components). Templates define the activity types and their weight distribution.',
                position: 'left'
            },
            {
                target: 'button[data-bs-target="#create-formula-modal"]',
                title: 'Create Global Formula',
                content: 'Global Formulas are department-independent configurations that can be applied across the entire institution. They use a structure template as their base and can be period-specific or apply to all periods.',
                position: 'left'
            },
            {
                target: '.structure-card:first-of-type, .card:has(.badge.bg-success-subtle)',
                title: 'Structure Template Card',
                content: 'Each template card shows: Name, Description, and Activity Weights as colored badges. Blue badges represent composite components (like Lecture or Lab sections), green badges show individual activities (Quiz, Exam, etc.) with their percentages.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.structure-card .badge.bg-primary, .badge.bg-primary.text-white',
                title: 'Composite Components',
                content: 'Blue badges indicate COMPOSITE components - these are major grade sections like "Lecture Component 60%" or "Lab Component 40%". They contain sub-activities whose percentages are relative to their parent component.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.structure-card .badge.bg-success-subtle, .badge:has(.bi-arrow-return-right)',
                title: 'Sub-Activities',
                content: 'Green badges with arrows are SUB-ACTIVITIES under a composite component. For example, under "Lecture Component 60%", you might have "Quizzes 40%" and "Exams 60%" - these percentages are relative to the Lecture Component, not the total grade.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.structure-card .btn-outline-secondary',
                title: 'Edit Template',
                content: 'Click Edit to modify an existing template\'s structure, weights, or description. Changes affect how the template appears when creating new formulas, but don\'t automatically update existing formulas.',
                position: 'left',
                optional: true
            },
            {
                target: '.js-delete-structure-template, .btn-outline-danger:has(.bi-trash)',
                title: 'Delete Template',
                content: 'Remove a structure template that\'s no longer needed. Note: Templates in use by existing formulas may have restrictions on deletion to maintain data integrity.',
                position: 'left',
                optional: true
            },
            {
                target: '.formula-card.border-info:first-of-type, .card:has(.bi-globe2)',
                title: 'Global Formula Card',
                content: 'Global Formula cards show: Formula name, scope (which periods it applies to), and hierarchical weight badges. These formulas can be applied to any course regardless of department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.badge.bg-info-subtle.text-info',
                title: 'Department-Independent Badge',
                content: 'This badge indicates the formula is global and not tied to any specific department. It can be used by any instructor when configuring their subject grades.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.formula-card[data-department-id], .card:has(.badge.bg-success.fw-semibold)',
                title: 'Department Baseline Card',
                content: 'Department Baseline cards show each department\'s default formula configuration. If a department has a custom baseline (green border), courses in that department inherit it. Otherwise, they use the global formula.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'a.btn-outline-success[href*="edit.department"]',
                title: 'Configure Department Baseline',
                content: 'Click to set up or modify a department\'s baseline formula. This formula becomes the default for all courses and subjects in that department unless they have their own custom configuration.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register the grades formula department tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-department', {
        title: 'Department Formula Management',
        description: 'Learn how to manage department-level formulas, create catalog formulas, and drill into courses',
        steps: [
            {
                target: 'a[href*="edit.department"], .btn-success:first-of-type',
                title: 'Edit/Create Department Formula',
                content: 'Create or edit the department\'s fallback formula. This becomes the baseline for ALL courses and subjects in this department unless overridden.',
                position: 'left'
            },
            {
                target: '.btn-outline-secondary[href*="overview"]',
                title: 'Back to Overview',
                content: 'Return to the main formula overview to select a different department.',
                position: 'left'
            },
            {
                target: 'select[name="academic_year"]',
                title: 'Filter by Academic Year',
                content: 'Filter the course list by academic year. Useful when you have courses across multiple years.',
                position: 'bottom'
            },
            {
                target: 'select[name="semester"]',
                title: 'Filter by Semester',
                content: 'Further filter by semester: 1st, 2nd, or Summer. Combine with year for precise filtering.',
                position: 'bottom'
            },
            {
                target: '.badge.bg-success',
                title: 'Current Baseline Formula',
                content: 'This badge shows which formula is the current baseline for this department. All courses inherit this unless they have custom formulas.',
                position: 'left',
                optional: true
            },
            {
                target: '.wildcard-filter-btn[data-filter="all"]',
                title: 'View All Wildcards',
                content: 'Show all items: both the formula catalog and course wildcards.',
                position: 'bottom'
            },
            {
                target: '.wildcard-filter-btn[data-filter="custom"]',
                title: 'View Catalog Formulas',
                content: 'Show only the department formula catalog - reusable formula templates instructors can apply.',
                position: 'bottom'
            },
            {
                target: '.wildcard-filter-btn[data-filter="default"]',
                title: 'View Course Wildcards',
                content: 'Show only course wildcards to see which courses have custom formulas vs using the baseline.',
                position: 'bottom'
            },
            {
                target: 'a[href*="formulas.create"]',
                title: 'Create Catalog Formula',
                content: 'Add a new formula to the department catalog. These templates can be selected by instructors when setting up their subjects.',
                position: 'left',
                optional: true
            },
            {
                target: '.formula-card, .wildcard-card[data-status="catalog"]',
                title: 'Catalog Formula Card',
                content: 'Each catalog formula shows: Label, weight distribution (Quiz %, Exam %, etc.), base score, scale multiplier, and passing grade. Click to edit.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.wildcard-card:not([data-status="catalog"]), .course-card',
                title: 'Course Wildcard Card',
                content: 'Click any course card to drill down into that course and manage subject-level formulas.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'form[onsubmit*="confirm"], button[type="submit"].btn-outline-danger',
                title: 'Delete Formula',
                content: 'Remove a catalog formula. The fallback formula cannot be deleted. Requires confirmation.',
                position: 'left',
                optional: true
            }
        ]
    });

    // Register the grades formula course tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-course', {
        title: 'Course Formula Management',
        description: 'Learn how to manage course-level formulas and drill into individual subjects',
        steps: [
            {
                target: 'a[href*="edit.course"], .btn-success:first-of-type',
                title: 'Edit/Create Course Formula',
                content: 'Create or edit a formula specific to this course. This overrides the department baseline for all subjects in this course.',
                position: 'left'
            },
            {
                target: '.btn-outline-secondary[href*="department"]',
                title: 'Back to Department',
                content: 'Return to the department view to select a different course or manage department formulas.',
                position: 'left'
            },
            {
                target: 'select[name="academic_year"]',
                title: 'Filter by Academic Year',
                content: 'Filter subjects by academic year to focus on a specific period.',
                position: 'bottom'
            },
            {
                target: 'select[name="semester"]',
                title: 'Filter by Semester',
                content: 'Further filter by semester for precise subject filtering.',
                position: 'bottom'
            },
            {
                target: '.bg-gradient-green-card, .card.bg-success',
                title: 'Subject Overview Summary',
                content: 'Shows: Total subjects in this course, how many have custom formulas, how many use the course/department fallback, and which fallback is active.',
                position: 'bottom'
            },
            {
                target: '.alert-info',
                title: 'Active Fallback Information',
                content: 'Shows which formula subjects will use if they don\'t have a custom one: Course Formula (if set) or Department Baseline.',
                position: 'left',
                optional: true
            },
            {
                target: '.wildcard-filter-btn[data-filter="all"]',
                title: 'View All Subjects',
                content: 'Show all subjects in this course regardless of formula status.',
                position: 'bottom'
            },
            {
                target: '.wildcard-filter-btn[data-filter="custom"]',
                title: 'View Custom Formulas',
                content: 'Show only subjects that have custom formulas defined (overriding course/department baseline).',
                position: 'bottom'
            },
            {
                target: '.wildcard-filter-btn[data-filter="default"]',
                title: 'View Default/Inherited',
                content: 'Show only subjects using the inherited formula (no custom override).',
                position: 'bottom'
            },
            {
                target: '#subject-wildcards .wildcard-card, .subject-card',
                title: 'Subject Card',
                content: 'Click any subject card to view its formula details. The badge shows formula status: Custom (green) or inherited from Course/Department.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.alert-info',
                title: 'No Course Formula Notice',
                content: 'This alert appears when no course formula exists. Subjects will inherit from department baseline or system default.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the grades formula subject tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-subject', {
        title: 'Subject Formula Configuration',
        description: 'Learn how to configure subject-specific formulas and apply catalog templates',
        steps: [
            {
                target: 'a[href*="edit.subject"], .btn-success:first-of-type',
                title: 'Edit Subject Formula',
                content: 'Create or edit a formula specific to this subject. This is the most granular level - overrides both course and department settings.',
                position: 'left'
            },
            {
                target: '.btn-outline-secondary[href*="course"]',
                title: 'Back to Course',
                content: 'Return to the course view to select a different subject or manage course formula.',
                position: 'left'
            },
            {
                target: '.card-body .badge.bg-success-subtle, .formula-weight-chip',
                title: 'Current Weight Distribution',
                content: 'View the current activity weights: Quiz, Exam, Assignment, Project, etc. Each shows the percentage of the final grade.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.card.border-0.shadow-sm',
                title: 'Formula Details',
                content: 'Shows complete formula configuration: Base Score, Scale Multiplier, Passing Grade, and all activity type weights.',
                position: 'bottom',
                optional: true
            },
            {
                target: '#subject-formula-apply-form, select[name="formula_id"]',
                title: 'Apply Catalog Formula',
                content: 'Instead of manually configuring, select a formula from the department catalog. This copies all settings from the selected template.',
                position: 'top'
            },
            {
                target: 'button[type="submit"].btn-primary',
                title: 'Apply Selected Formula',
                content: 'Click to apply the selected catalog formula to this subject. Previous settings will be replaced.',
                position: 'left',
                optional: true
            },
            {
                target: '.badge.bg-info',
                title: 'Formula Source',
                content: 'Shows where the current formula comes from: Subject-level (custom), Course-level, or Department-level.',
                position: 'bottom',
                optional: true
            }
        ]
    });

    // Register the grades formula edit tutorial
    window.AdminTutorial.registerTutorial('admin-grades-formula-edit', {
        title: 'Edit Grade Formula',
        description: 'Learn how to configure all formula parameters: weights, scaling, and grade calculations',
        steps: [
            {
                target: 'input[name="label"]',
                title: 'Formula Label',
                content: 'Give your formula a descriptive name (e.g., "Standard Lecture Formula", "Lab Heavy Formula"). This helps identify the formula when selecting it.',
                position: 'bottom'
            },
            {
                target: 'textarea[name="description"], input[name="description"]',
                title: 'Formula Description',
                content: 'Add an optional description explaining when to use this formula or any special considerations.',
                position: 'bottom',
                optional: true
            },
            {
                target: '.card-header',
                title: 'Weight Distribution Section',
                content: 'Configure how different activity types contribute to the final grade. All weights must sum to 100%.',
                position: 'bottom'
            },
            {
                target: 'input[name*="weight"][name*="quiz"], .weight-input:first-of-type',
                title: 'Activity Type Weights',
                content: 'Set the percentage for each activity type: Quiz, Exam, Assignment, Project, Attendance, etc. Enter as whole numbers (e.g., 20 for 20%).',
                position: 'right',
                optional: true
            },
            {
                target: '.badge.bg-primary',
                title: 'Weight Total Indicator',
                content: 'The total shows the sum of all weights. Must equal exactly 100% before you can save.',
                position: 'bottom',
                optional: true
            },
            {
                target: 'input[name="base_score"]',
                title: 'Base Score',
                content: 'The starting point for grade calculations. Typically 50 or 60. Raw scores are adjusted relative to this base.',
                position: 'right'
            },
            {
                target: 'input[name="scale_multiplier"]',
                title: 'Scale Multiplier',
                content: 'Multiplier applied to adjusted scores. Typically 50. Formula: Final = Base + (Adjusted × Multiplier).',
                position: 'right'
            },
            {
                target: 'input[name="passing_grade"]',
                title: 'Passing Grade',
                content: 'The minimum final grade required to pass. Typically 75. Students below this threshold fail the subject.',
                position: 'right'
            },
            {
                target: '.preview-section, .formula-preview',
                title: 'Formula Preview',
                content: 'See a live preview of how the formula will calculate grades based on your settings.',
                position: 'top',
                optional: true
            },
            {
                target: 'button[type="submit"].btn-success',
                title: 'Save Formula',
                content: 'Click to save your formula. Changes apply immediately to all subjects using this formula.',
                position: 'top'
            },
            {
                target: '.btn-outline-secondary',
                title: 'Cancel Changes',
                content: 'Discard your changes and return to the previous page without saving.',
                position: 'top',
                optional: true
            }
        ]
    });
})();
