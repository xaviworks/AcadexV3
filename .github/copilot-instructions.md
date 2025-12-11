# Acadex Copilot Instructions

## 🏗️ Architecture
- Laravel 12 monolith with Blade views (`resources/views`) and Vite pipeline; AlpineJS 3.x + Bootstrap 5.3 are the UI stack.
- Domain models: `Student`, `Subject`, `TermGrade`, `FinalGrade`, `Activity`, `Score`—all use manual `is_deleted` flags instead of `SoftDeletes`.
- `DashboardController@index` dispatches to role-specific dashboards by checking `$user->role` and model helpers (`isChairperson()`, `isAdmin()`, etc.).
- Grade formula logic lives in `GradesFormulaService` with hierarchical resolution (subject → course → department → default) and caching.

## 🔐 Roles & Middleware
| Role | Integer | Gate | Model Helper |
|------|---------|------|--------------|
| Instructor | 0 | `instructor` | — |
| Chairperson | 1 | `chairperson` | `isChairperson()` |
| Dean | 2 | `dean` | — |
| Admin | 3 | `admin` | `isAdmin()` |
| GE Coordinator | 4 | `gecoordinator` | `isGECoordinator()` |
| VPAA | 5 | `vpaa` | `isVPAA()` |

- Gates defined in `App\Providers\AuthServiceProvider`; use `Gate::authorize('role')` in controllers.
- **Academic period middleware**: `academic.period.set` (alias in `bootstrap/app.php`) redirects to `/select-academic-period` until `session('active_academic_period_id')` is set. Required for instructor/chairperson/dean/gecoordinator/vpaa routes.
- Routes grouped by portal in `routes/web.php`; reuse existing middleware stacks when adding endpoints.

## 🔑 Authentication
- **Google OAuth**: Primary login via `GoogleAuthController` using Laravel Socialite. Only `@brokenshire.edu.ph` emails allowed.
- Users must be pre-created by Admin—no auto-registration on Google login.
- Single-session enforcement: Non-admin users blocked from concurrent logins on multiple devices.
- `UnverifiedUser` model stages pending account requests until Admin/Chairperson approval.

## 🧮 Grades & Activities
- `GradeCalculationTrait` calculates term grades using configurable formula structures (lecture_only, lecture_lab, etc.) from `GradesFormulaService`.
- `ActivityManagementTrait::getOrCreateDefaultActivities()` seeds activities per term based on formula weights; default is 3 quizzes, 3 OCRs, 1 exam for lecture_only.
- Term mapping via `getTermId()`: `prelim`→1, `midterm`→2, `prefinal`→3, `final`→4.
- **Critical**: Grade saves must call both `updateTermGrade()` AND `calculateAndUpdateFinalGrade()` to keep averages in sync.
- Final grade = average of all 4 term grades; remarks set by comparing against `passing_grade` from formula.
- `GradeNotification` alerts chairpersons/GE coordinators when instructors submit grades; handled via `NotificationController`.

## 📊 Course Outcome (CO) Tracking
- `CourseOutcomes` model stores CO definitions per subject (`co_code`, `co_identifier`, `description`).
- Each `Activity` can be mapped to a `course_outcome_id` to track which CO it assesses.
- `CourseOutcomeTrait::computeCoAttainment()` calculates per-term and semester-total CO percentages:
  ```
  CO% = (Sum of raw scores for CO ÷ Sum of max possible scores for CO) × 100
  ```
- `CourseOutcomeAttainmentController` renders student-level CO attainment matrices.
- Reports hierarchy: Student → Course → Program → Department (controllers: `CourseOutcomeReportsController`, `ProgramReportsController`).

## 🌐 GE Subject Management
- GE (General Education) subjects span multiple departments; managed by GE Coordinator (role 4).
- `GESubjectRequest` model handles cross-department instructor assignment requests:
  - Chairperson requests → GE Coordinator approves/rejects
  - Fields: `instructor_id`, `requested_by`, `status`, `reviewed_by`, `reviewed_at`
- `GECoordinatorController` manages GE-specific instructor pools and subject assignments.
- Users with `can_teach_ge` flag can be assigned to GE subjects.

## 📐 Structure Templates
- `StructureTemplate` model stores reusable grading formula structures (activity weights, types).
- `StructureTemplateRequest` enables chairpersons to propose new templates for Admin approval:
  - Status flow: `pending` → `approved`/`rejected`
  - Fields: `chairperson_id`, `label`, `structure_config` (JSON), `status`, `admin_notes`
- Admin manages templates via `AdminController::*StructureTemplate*` methods.
- Chairpersons submit requests via `ChairpersonController::*TemplateRequest*` routes.

## 📥 Student Imports
- `StudentImportController` stages Excel uploads into `review_students` table using `StudentReviewImport`.
- Expected columns: last name, first name, middle name, year level, course code.
- `confirmImport()` deduplicates by name/course/period, creates `StudentSubject` links, ensures activities exist.
- **Always validate**: `session('active_academic_period_id')` and subject's `academic_period_id` must match.

## 🧑‍🏫 Portal Controllers
| Portal | Controller | Key Constraints |
|--------|------------|-----------------|
| Chairperson | `ChairpersonController` | Filters by user's department/course + active period |
| GE Coordinator | `GECoordinatorController` | Manages GE subjects across departments |
| Dean | `DeanController` | Department-wide read access |
| VPAA | `VPAAController` | Institution-wide read access |
| Admin | `AdminController` | Full CRUD, toggles `is_deleted` (never hard deletes) |

- `AcademicPeriodController::generate` auto-creates academic year (1st/2nd/Summer semesters).

## 🛠️ Developer Workflows
```bash
# Fresh setup
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed

# Development (runs server, queue, pail logs, vite concurrently)
composer run dev

# Build assets
npm run build

# Run tests (clears config cache first)
composer test
```

## 🧩 Frontend Patterns
- Vite entry: `resources/js/app.js` (imports Bootstrap CSS, initializes Alpine).
- Views organized by portal: `resources/views/{admin,chairperson,dean,gecoordinator,instructor,vpaa}/`.
- Alpine stores documented in `docs/ALPINE_STORE_USAGE.md`.
- Bootstrap 5.3 is primary styling; Tailwind configured but legacy Bootstrap still dominant.

## ⚙️ Data Conventions
- **Soft delete pattern**: Filter with `->where('is_deleted', false)`; applies to all major models.
- **Audit columns**: `created_by`/`updated_by` set via `Auth::id()` in controllers.
- **User emails**: Stored with `@brokenshire.edu.ph` domain suffix.
- **Pivot model**: `StudentSubject` has its own `is_deleted` flag for enrollment status.

## 📈 Usage Analytics
- Event listeners in `app/Listeners/LogUser{Login,Logout,FailedLogin}.php` write to `user_logs`.
- Uses `jenssegers/agent` for device detection; dedupes events within 5 seconds.
- Session management in `AdminController` handles force logout/disable via `disabled_until` field.

## 🧪 Testing
- Tests extend `Tests\TestCase`; use `RefreshDatabase` for grade/import scenarios.
- Stub academic period in tests: `session(['active_academic_period_id' => $period->id])` before hitting protected routes.

## 🚫 Code Quality Rules
- **Null safety**: Always use `?->` operator or `optional()` when accessing potentially null objects.
- **Relationship loading**: Use `loadMissing()` or eager loading before accessing relationship properties.
- **Safe routes**: `$model ? route('name', $model) : route('fallback')` when model might be null.
- **Match expressions**: Always include `default` case; ensure all matched values are initialized.
- **PHPDoc annotations**: Add `@property`, `@return`, `@var` for complex types to satisfy static analysis.
- **No undefined access**: Declare attributes in `$fillable`, `$casts`, or PHPDoc before referencing.
