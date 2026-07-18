/**
 * Page Scripts Index
 *
 * This file provides a registry of page-specific initialization functions.
 * Each page script should register itself via window exports.
 *
 * Usage in Blade templates:
 * - Include the relevant page script via @vite or via script tag
 * - The script will auto-initialize when DOM is ready
 */

// Import all page scripts for bundling
// Admin pages
import './admin/users.js';
import './admin/sessions.js';
import './admin/subjects.js';
import './admin/departments.js';
import './admin/courses.js';
import './vpaa/grading-configuration/structure-template-requests.js';
import './vpaa/grading-configuration/grades-formula-course.js';
import './vpaa/grading-configuration/grades-formula-edit-global.js';
import './vpaa/grading-configuration/grades-formula-department.js';
import './vpaa/grading-configuration/grades-formula-select-period.js';
import './vpaa/grading-configuration/grades-formula-subject.js';
import './vpaa/grading-configuration/grades-formula-form.js';
import './vpaa/grading-configuration/grades-formula-wildcards.js';

// Dashboard pages
import './dashboard/instructor.js';
import './dashboard/chairperson.js';
import './dashboard/gecoordinator.js';

// Instructor pages
import './instructor/manage-students.js';
import './instructor/manage-grades.js';
import './instructor/course-outcomes.js';
import './instructor/activities-create.js';
import './instructor/partials/grade-script.js';
import './instructor/partials/auto-save-script.js';
import './instructor/excel/import-students.js';
import './instructor/course-outcomes-wildcards.js';
import './instructor/course-outcomes-table.js';
import './instructor/scores/final-grades.js';
import './instructor/scores/course-outcome-results-wildcards.js';
import './instructor/scores/course-outcome-results.js';

// Chairperson pages
import './chairperson/manage-instructors.js';
import './chairperson/assign-subjects.js';
import './chairperson/reports/co-course-chooser.js';
import './chairperson/reports/co-program.js';
import './chairperson/view-grades.js';
import './chairperson/structure-template-create.js';

// GE Coordinator pages
import './gecoordinator/manage-instructors.js';
import './gecoordinator/students-by-year.js';
import './gecoordinator/assign-subjects.js';

// Shared pages
import './shared/guest-password-toggles.js';
import './shared/select-curriculum-subjects.js';

// VPAA pages
import './vpaa/index.js';
import './vpaa/scores/course-outcome-results.js';
import './vpaa/scores/course-outcome-results-wildcards.js';
import './vpaa/scores/course-outcome-departments.js';

/**
 * Initialize a page by name
 * This can be called from Blade templates to manually trigger initialization
 * @param {string} pageName - The page identifier
 */
export function initPage(pageName) {
  const initFunctions = {
    // Admin
    'admin.users': window.initAdminUsersPage,
    'admin.sessions': window.initSessionsPage,
    'admin.subjects': window.initAdminSubjectsPage,
    'admin.departments': window.initAdminDepartmentsPage,
    'admin.courses': window.initAdminCoursesPage,

    // Dashboard
    'dashboard.instructor': window.initSubjectPerformanceChart,
    'dashboard.chairperson': window.initChairpersonDashboard,
    'dashboard.gecoordinator': window.initGECoordinatorDashboard,

    // Instructor
    'instructor.manage-students': window.initManageStudentsPage,
    'instructor.manage-grades': window.initManageGradesPage,
    'instructor.course-outcomes': window.initCourseOutcomesPage,
    'instructor.activities-create': window.initActivitiesCreatePage,
    'instructor.grade-script': window.initGradeScript,
    'instructor.auto-save-script': window.initAutoSaveScript,
    'instructor.import-students': window.initImportStudentsPage,
    'instructor.course-outcomes-wildcards': window.initCourseOutcomesWildcardsPage,
    'instructor.course-outcomes-table': window.initCourseOutcomesTablePage,
    'instructor.final-grades': window.initFinalGradesPage,
    'instructor.course-outcome-results': window.initCourseOutcomeResultsPage,
    'instructor.course-outcome-results-wildcards': window.initCourseOutcomeResultsWildcardsPage,

    // Chairperson
    'chairperson.manage-instructors': window.initChairpersonManageInstructorsPage,
    'chairperson.assign-subjects': window.initChairpersonAssignSubjectsPage,
    'chairperson.co-course-chooser': window.initCOCourseChooserPage,
    'chairperson.co-program': window.initChairpersonCoProgramPage,
    'chairperson.view-grades': window.initChairpersonViewGradesPage,
    'chairperson.structure-template-create': window.initStructureTemplateCreatePage,
    'chairperson.select-curriculum-subjects': window.initSelectCurriculumSubjectsPage,

    // GE Coordinator
    'gecoordinator.manage-instructors': window.initGECoordinatorManageInstructorsPage,
    'gecoordinator.students-by-year': window.initStudentsByYearPage,
    'gecoordinator.assign-subjects': window.initAssignSubjectsPage,
    'gecoordinator.select-curriculum-subjects': window.initSelectCurriculumSubjectsPage,

    // VPAA
    'vpaa.departments': window.initVpaaDepartmentsPage,
    'vpaa.students': window.initVpaaStudentsPage,
    'vpaa.students-departments': window.initVpaaStudentsDepartmentsPage,
    'vpaa.grades': window.initVpaaGradesPage,
    'vpaa.grading-configuration.template-requests': window.initStructureTemplateRequestsPage,
    'vpaa.grading-configuration.course': window.initGradesFormulaCourse,
    'vpaa.grading-configuration.edit-global': window.initGradesFormulaEditGlobal,
    'vpaa.grading-configuration.department': window.initGradesFormulaDepartment,
    'vpaa.grading-configuration.select-period': window.initGradesFormulaSelectPeriod,
    'vpaa.grading-configuration.subject': window.initGradesFormulaSubject,
    'vpaa.grading-configuration.form': window.initGradesFormulaForm,
    'vpaa.grading-configuration.wildcards': window.initGradesFormulaWildcards,
    'vpaa.reports.attainment': window.initVpaaCourseOutcomeResultsWildcardsPage,
    'vpaa.reports.attainment.subject': window.initVpaaCourseOutcomeResultsPage,
    'vpaa.course-outcome-results': window.initVpaaCourseOutcomeResultsPage,
    'vpaa.course-outcome-results-wildcards': window.initVpaaCourseOutcomeResultsWildcardsPage,
    'vpaa.course-outcome-departments': window.initVpaaCourseOutcomeDepartmentsPage,
  };

  const initFn = initFunctions[pageName];
  if (typeof initFn === 'function') {
    initFn();
  } else {
    console.warn(`No initialization function found for page: ${pageName}`);
  }
}

// Export for global access
window.initPage = initPage;
