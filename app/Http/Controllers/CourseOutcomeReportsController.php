<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Services\CourseOutcomeReportingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseOutcomeReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'academic.period.set']);
    }

    // VPAA: Per-course CO summary - can view any course
    public function vpaaCourse(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);
        $courseId = (int)$request->input('course_id', 0);

        if (!$courseId) {
            // List all courses to choose from
            $courses = Course::where('is_deleted', false)
                ->orderBy('course_code')
                ->get(['id','course_code','course_description']);
            return view('vpaa.reports.co-course-chooser', [
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $course = Course::where('id', $courseId)
            ->where('is_deleted', false)
            ->firstOrFail();
        
        // Get all subjects for this course in the active period
        $subjects = Subject::where('course_id', $courseId)
            ->where('is_deleted', false)
            ->where('academic_period_id', $periodId)
            ->orderBy('subject_code')
            ->get();

        // Calculate CO for each subject
        $subjectCOs = [];
        foreach ($subjects as $subject) {
            $subjectCOs[$subject->id] = [
                'subject' => $subject,
                'co' => $service->aggregateSubject($subject->id),
            ];
        }

        return view('vpaa.reports.co-course', [
            'course' => $course,
            'subjectCOs' => $subjectCOs,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    // VPAA: Per-student CO report - can view any subject/student
    public function vpaaStudent(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);

        $subjectId = (int)$request->input('subject_id', 0);
        $studentId = (int)$request->input('student_id', 0);
        $studentQuery = trim((string) $request->input('student_query', ''));

        if (!$subjectId || !$studentId) {
            $searchedStudents = collect();
            $selectedStudent = null;
            $enrolledSubjects = collect();
            $studentSuggestions = Student::with('course')
                ->where('students.is_deleted', false)
                ->whereHas('subjects', function ($q) use ($periodId) {
                    $q->where('subjects.is_deleted', false)
                        ->where('student_subjects.is_deleted', false)
                        ->where('subjects.academic_period_id', $periodId);
                })
                ->orderBy('students.last_name')
                ->orderBy('students.first_name')
                ->limit(40)
                ->get()
                ->map(function ($stu) {
                    return [
                        'id' => $stu->id,
                        'label' => trim($stu->last_name . ', ' . $stu->first_name . ' ' . ($stu->middle_name ?? '')),
                    ];
                })
                ->values();

            if ($studentQuery !== '') {
                $searchTerm = '%' . $studentQuery . '%';
                $searchedStudents = Student::with('course')
                    ->where('students.is_deleted', false);

                $this->applyStudentNameSearch($searchedStudents, $searchTerm);

                $searchedStudents = $searchedStudents
                    ->whereHas('subjects', function ($q) use ($periodId) {
                        $q->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->orderBy('students.last_name')
                    ->orderBy('students.first_name')
                    ->limit(25)
                    ->get();
            }

            if ($studentId) {
                $selectedStudent = Student::with('course')
                    ->where('students.id', $studentId)
                    ->where('students.is_deleted', false)
                    ->whereHas('subjects', function ($q) use ($periodId) {
                        $q->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->firstOrFail();

                $enrolledSubjects = Subject::with('course')
                    ->where('subjects.is_deleted', false)
                    ->where('subjects.academic_period_id', $periodId)
                    ->whereHas('students', function ($q) use ($selectedStudent) {
                        $q->where('students.id', $selectedStudent->id)
                            ->where('student_subjects.is_deleted', false);
                    })
                    ->orderBy('subjects.subject_code')
                    ->get();
            }

            return view('vpaa.reports.co-student-chooser', [
                'studentQuery' => $studentQuery,
                'searchedStudents' => $searchedStudents,
                'selectedStudent' => $selectedStudent,
                'enrolledSubjects' => $enrolledSubjects,
                'studentSuggestions' => $studentSuggestions,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $subject = Subject::with(['course','academicPeriod'])
            ->where('id', $subjectId)
            ->where('academic_period_id', $periodId)
            ->where('is_deleted', false)
            ->firstOrFail();
            
        $student = Student::where('students.id', $studentId)
            ->where('students.is_deleted', false)
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                    ->where('student_subjects.is_deleted', false);
            })
            ->firstOrFail();
        $data = $service->computeStudentSubject($studentId, $subjectId);

        return view('vpaa.reports.co-student', array_merge($data, [
            'selectedSubject' => $subject,
            'student' => $student,
        ]));
    }

    // GE Coordinator: Per-course CO summary - shows all courses, but only GE subjects
    public function geCoordinatorCourse(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;
        $courseId = (int)$request->input('course_id', 0);

        if (!$courseId) {
            // List all courses that have GE subjects
            $coursesWithGESubjects = Subject::where('department_id', 1) // GE subjects
                ->where('is_deleted', false)
                ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
                ->distinct()
                ->pluck('course_id')
                ->filter()
                ->all();
            
            $courses = Course::whereIn('id', $coursesWithGESubjects)
                ->where('is_deleted', false)
                ->orderBy('course_code')
                ->get(['id','course_code','course_description']);
                
            return view('gecoordinator.reports.co-course-chooser', [
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $course = Course::where('id', $courseId)
            ->where('is_deleted', false)
            ->firstOrFail();
        
        // Get only GE subjects for this course in the active period
        $subjects = Subject::where('course_id', $courseId)
            ->where('department_id', 1) // Only GE subjects
            ->where('is_deleted', false)
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('subject_code')
            ->get();

        // Calculate CO for each GE subject
        $subjectCOs = [];
        foreach ($subjects as $subject) {
            $subjectCOs[$subject->id] = [
                'subject' => $subject,
                'co' => $service->aggregateSubject($subject->id),
            ];
        }

        return view('gecoordinator.reports.co-course', [
            'course' => $course,
            'subjectCOs' => $subjectCOs,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    // GE Coordinator: Per-student CO report - only GE subjects
    public function geCoordinatorStudent(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;

        $subjectId = (int)$request->input('subject_id', 0);
        $studentId = (int)$request->input('student_id', 0);
        $studentQuery = trim((string) $request->input('student_query', ''));

        if (!$subjectId || !$studentId) {
            $searchedStudents = collect();
            $selectedStudent = null;
            $enrolledSubjects = collect();
            $studentSuggestions = Student::with('course')
                ->where('students.is_deleted', false)
                ->whereHas('subjects', function ($q) use ($periodId) {
                    $q->where('subjects.department_id', 1)
                        ->where('subjects.is_deleted', false)
                        ->where('student_subjects.is_deleted', false)
                        ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                })
                ->orderBy('students.last_name')
                ->orderBy('students.first_name')
                ->limit(40)
                ->get()
                ->map(function ($stu) {
                    return [
                        'id' => $stu->id,
                        'label' => trim($stu->last_name . ', ' . $stu->first_name . ' ' . ($stu->middle_name ?? '')),
                    ];
                })
                ->values();

            if ($studentQuery !== '') {
                $searchTerm = '%' . $studentQuery . '%';
                $searchedStudents = Student::with('course')
                    ->where('students.is_deleted', false);

                $this->applyStudentNameSearch($searchedStudents, $searchTerm);

                $searchedStudents = $searchedStudents
                    ->whereHas('subjects', function ($q) use ($periodId) {
                        $q->where('subjects.department_id', 1)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->orderBy('students.last_name')
                    ->orderBy('students.first_name')
                    ->limit(25)
                    ->get();
            }

            if ($studentId) {
                $selectedStudent = Student::with('course')
                    ->where('students.id', $studentId)
                    ->where('students.is_deleted', false)
                    ->whereHas('subjects', function ($q) use ($periodId) {
                        $q->where('subjects.department_id', 1)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->firstOrFail();

                $enrolledSubjects = Subject::with('course')
                    ->where('subjects.department_id', 1)
                    ->where('subjects.is_deleted', false)
                    ->when($periodId, fn($q) => $q->where('subjects.academic_period_id', $periodId))
                    ->whereHas('students', function ($q) use ($selectedStudent) {
                        $q->where('students.id', $selectedStudent->id)
                            ->where('student_subjects.is_deleted', false);
                    })
                    ->orderBy('subjects.subject_code')
                    ->get();
            }

            return view('gecoordinator.reports.co-student-chooser', [
                'studentQuery' => $studentQuery,
                'searchedStudents' => $searchedStudents,
                'selectedStudent' => $selectedStudent,
                'enrolledSubjects' => $enrolledSubjects,
                'studentSuggestions' => $studentSuggestions,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Ensure the subject is a GE subject
        $subject = Subject::with(['course','academicPeriod'])
            ->where('department_id', 1) // GE subject
            ->where('id', $subjectId)
            ->where('is_deleted', false)
            ->firstOrFail();
            
        $student = Student::where('students.id', $studentId)
            ->where('students.is_deleted', false)
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                    ->where('student_subjects.is_deleted', false);
            })
            ->firstOrFail();
        $data = $service->computeStudentSubject($studentId, $subjectId);

        return view('gecoordinator.reports.co-student', array_merge($data, [
            'selectedSubject' => $subject,
            'student' => $student,
        ]));
    }

    // Chair: Per-course (program/course_id) CO summary for active period
    public function chairCourse(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;
        $courseId = (int)$request->input('course_id', 0);

        if (!$courseId) {
            // List only the chairperson's assigned course
            $userCourseId = Auth::user()?->course_id;
            
            if (!$userCourseId) {
                abort(403, 'You are not assigned to any course.');
            }
            
            $courses = Course::where('id', $userCourseId)
                ->where('is_deleted', false)
                ->get(['id','course_code','course_description']);
            
            return view('chairperson.reports.co-course-chooser', [
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Ensure the course belongs to the chairperson's assigned course
        $userCourseId = Auth::user()?->course_id;
        $course = Course::where('id', $courseId)
            ->where('id', $userCourseId)
            ->where('is_deleted', false)
            ->firstOrFail();
        
        // Get subjects for this course in the active period (excluding GE subjects)
        $subjects = Subject::where('course_id', $courseId)
            ->where('department_id', '!=', 1) // Exclude GE subjects (department_id = 1)
            ->where('is_deleted', false)
            ->when($periodId, fn($q) => $q->where('academic_period_id', $periodId))
            ->orderBy('subject_code')
            ->get();

        // Calculate CO for each subject
        $subjectCOs = [];
        foreach ($subjects as $subject) {
            $subjectCOs[$subject->id] = [
                'subject' => $subject,
                'co' => $service->aggregateSubject($subject->id),
            ];
        }

        return view('chairperson.reports.co-course', [
            'course' => $course,
            'subjectCOs' => $subjectCOs,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    // Chair: Per-student CO report within a subject
    public function chairStudent(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;

        $subjectId = (int)$request->input('subject_id', 0);
        $studentId = (int)$request->input('student_id', 0);
        $studentQuery = trim((string) $request->input('student_query', ''));

        if (!$subjectId || !$studentId) {
            $userCourseId = Auth::user()?->course_id;
            
            if (!$userCourseId) {
                abort(403, 'You are not assigned to any course.');
            }

            $searchedStudents = collect();
            $selectedStudent = null;
            $enrolledSubjects = collect();
            $studentSuggestions = Student::with('course')
                ->where('students.is_deleted', false)
                ->whereHas('subjects', function ($q) use ($userCourseId, $periodId) {
                    $q->where('subjects.course_id', $userCourseId)
                        ->where('subjects.department_id', '!=', 1)
                        ->where('subjects.is_deleted', false)
                        ->where('student_subjects.is_deleted', false)
                        ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                })
                ->orderBy('students.last_name')
                ->orderBy('students.first_name')
                ->limit(40)
                ->get()
                ->map(function ($stu) {
                    return [
                        'id' => $stu->id,
                        'label' => trim($stu->last_name . ', ' . $stu->first_name . ' ' . ($stu->middle_name ?? '')),
                    ];
                })
                ->values();

            if ($studentQuery !== '') {
                $searchTerm = '%' . $studentQuery . '%';
                $searchedStudents = Student::with('course')
                    ->where('students.is_deleted', false);

                $this->applyStudentNameSearch($searchedStudents, $searchTerm);

                $searchedStudents = $searchedStudents
                    ->whereHas('subjects', function ($q) use ($userCourseId, $periodId) {
                        $q->where('subjects.course_id', $userCourseId)
                            ->where('subjects.department_id', '!=', 1)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->orderBy('students.last_name')
                    ->orderBy('students.first_name')
                    ->limit(25)
                    ->get();
            }

            if ($studentId) {
                $selectedStudent = Student::with('course')
                    ->where('students.id', $studentId)
                    ->where('students.is_deleted', false)
                    ->whereHas('subjects', function ($q) use ($userCourseId, $periodId) {
                        $q->where('subjects.course_id', $userCourseId)
                            ->where('subjects.department_id', '!=', 1)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->when($periodId, fn($sq) => $sq->where('subjects.academic_period_id', $periodId));
                    })
                    ->firstOrFail();

                $enrolledSubjects = Subject::with('course')
                    ->where('subjects.course_id', $userCourseId)
                    ->where('subjects.department_id', '!=', 1)
                    ->where('subjects.is_deleted', false)
                    ->when($periodId, fn($q) => $q->where('subjects.academic_period_id', $periodId))
                    ->whereHas('students', function ($q) use ($selectedStudent) {
                        $q->where('students.id', $selectedStudent->id)
                            ->where('student_subjects.is_deleted', false);
                    })
                    ->orderBy('subjects.subject_code')
                    ->get();
            }

            return view('chairperson.reports.co-student-chooser', [
                'studentQuery' => $studentQuery,
                'searchedStudents' => $searchedStudents,
                'selectedStudent' => $selectedStudent,
                'enrolledSubjects' => $enrolledSubjects,
                'studentSuggestions' => $studentSuggestions,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Ensure the subject belongs to the chairperson's assigned course and is not a GE subject
        $userCourseId = Auth::user()?->course_id;
        $subject = Subject::with(['course','academicPeriod'])
            ->where('course_id', $userCourseId)
            ->where('department_id', '!=', 1) // Exclude GE subjects
            ->where('id', $subjectId)
            ->where('is_deleted', false)
            ->firstOrFail();
            
        $student = Student::where('students.id', $studentId)
            ->where('students.is_deleted', false)
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                    ->where('student_subjects.is_deleted', false);
            })
            ->firstOrFail();
        $data = $service->computeStudentSubject($studentId, $subjectId);

        return view('chairperson.reports.co-student', array_merge($data, [
            'selectedSubject' => $subject,
            'student' => $student,
        ]));
    }

    // Dean: Per-course CO summary - their department only
    public function deanCourse(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);
        $courseId = (int)$request->input('course_id', 0);
        $user = Auth::user();
        $departmentId = $user?->department_id;

        if (!$departmentId) {
            abort(403, 'You are not assigned to any department.');
        }

        if (!$courseId) {
            // List courses in dean's department
            $courses = Course::where('department_id', $departmentId)
                ->where('is_deleted', false)
                ->orderBy('course_code')
                ->get(['id','course_code','course_description']);
            return view('dean.reports.co-course-chooser', [
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Ensure the course belongs to dean's department
        $course = Course::where('id', $courseId)
            ->where('department_id', $departmentId)
            ->where('is_deleted', false)
            ->firstOrFail();
        
        // Get all subjects for this course in the active period
        $subjects = Subject::where('course_id', $courseId)
            ->where('is_deleted', false)
            ->where('academic_period_id', $periodId)
            ->orderBy('subject_code')
            ->get();

        // Calculate CO for each subject
        $subjectCOs = [];
        foreach ($subjects as $subject) {
            $subjectCOs[$subject->id] = [
                'subject' => $subject,
                'co' => $service->aggregateSubject($subject->id),
            ];
        }

        return view('dean.reports.co-course', [
            'course' => $course,
            'subjectCOs' => $subjectCOs,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    // Dean: Per-student CO report - their department only
    public function deanStudent(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);

        $subjectId = (int)$request->input('subject_id', 0);
        $studentId = (int)$request->input('student_id', 0);
        $studentQuery = trim((string) $request->input('student_query', ''));
        $user = Auth::user();
        $departmentId = $user?->department_id;

        if (!$departmentId) {
            abort(403, 'You are not assigned to any department.');
        }

        if (!$subjectId || !$studentId) {
            $searchedStudents = collect();
            $selectedStudent = null;
            $enrolledSubjects = collect();
            $studentSuggestions = Student::with('course')
                ->where('students.is_deleted', false)
                ->whereHas('subjects', function ($q) use ($departmentId, $periodId) {
                    $q->where('subjects.department_id', $departmentId)
                        ->where('subjects.is_deleted', false)
                        ->where('student_subjects.is_deleted', false)
                        ->where('subjects.academic_period_id', $periodId);
                })
                ->orderBy('students.last_name')
                ->orderBy('students.first_name')
                ->limit(40)
                ->get()
                ->map(function ($stu) {
                    return [
                        'id' => $stu->id,
                        'label' => trim($stu->last_name . ', ' . $stu->first_name . ' ' . ($stu->middle_name ?? '')),
                    ];
                })
                ->values();

            if ($studentQuery !== '') {
                $searchTerm = '%' . $studentQuery . '%';
                $searchedStudents = Student::with('course')
                    ->where('students.is_deleted', false);

                $this->applyStudentNameSearch($searchedStudents, $searchTerm);

                $searchedStudents = $searchedStudents
                    ->whereHas('subjects', function ($q) use ($departmentId, $periodId) {
                        $q->where('subjects.department_id', $departmentId)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->where('subjects.academic_period_id', $periodId);
                    })
                    ->orderBy('students.last_name')
                    ->orderBy('students.first_name')
                    ->limit(25)
                    ->get();
            }

            if ($studentId) {
                $selectedStudent = Student::with('course')
                    ->where('students.id', $studentId)
                    ->where('students.is_deleted', false)
                    ->whereHas('subjects', function ($q) use ($departmentId, $periodId) {
                        $q->where('subjects.department_id', $departmentId)
                            ->where('subjects.is_deleted', false)
                            ->where('student_subjects.is_deleted', false)
                            ->where('subjects.academic_period_id', $periodId);
                    })
                    ->firstOrFail();

                $enrolledSubjects = Subject::with('course')
                    ->where('subjects.department_id', $departmentId)
                    ->where('subjects.is_deleted', false)
                    ->where('subjects.academic_period_id', $periodId)
                    ->whereHas('students', function ($q) use ($selectedStudent) {
                        $q->where('students.id', $selectedStudent->id)
                            ->where('student_subjects.is_deleted', false);
                    })
                    ->orderBy('subjects.subject_code')
                    ->get();
            }

            return view('dean.reports.co-student-chooser', [
                'studentQuery' => $studentQuery,
                'searchedStudents' => $searchedStudents,
                'selectedStudent' => $selectedStudent,
                'enrolledSubjects' => $enrolledSubjects,
                'studentSuggestions' => $studentSuggestions,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Ensure the subject belongs to dean's department
        $subject = Subject::with(['course','academicPeriod'])
            ->where('department_id', $departmentId)
            ->where('id', $subjectId)
            ->where('academic_period_id', $periodId)
            ->where('is_deleted', false)
            ->firstOrFail();
            
        $student = Student::where('students.id', $studentId)
            ->where('students.is_deleted', false)
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                    ->where('student_subjects.is_deleted', false);
            })
            ->firstOrFail();
        $data = $service->computeStudentSubject($studentId, $subjectId);

        return view('dean.reports.co-student', array_merge($data, [
            'selectedSubject' => $subject,
            'student' => $student,
        ]));
    }

    private function applyStudentNameSearch(Builder $query, string $searchTerm): void
    {
        [$formattedNameWithMiddle, $formattedNameWithoutMiddle] = $this->studentNameSearchExpressions();

        $query->where(function (Builder $nameQuery) use ($searchTerm, $formattedNameWithMiddle, $formattedNameWithoutMiddle) {
            $nameQuery->where('students.first_name', 'like', $searchTerm)
                ->orWhere('students.last_name', 'like', $searchTerm)
                ->orWhere('students.middle_name', 'like', $searchTerm)
                ->orWhereRaw($formattedNameWithMiddle . ' like ?', [$searchTerm])
                ->orWhereRaw($formattedNameWithoutMiddle . ' like ?', [$searchTerm]);
        });
    }

    private function studentNameSearchExpressions(): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return [
                "students.last_name || ', ' || students.first_name || ' ' || COALESCE(students.middle_name, '')",
                "students.last_name || ', ' || students.first_name",
            ];
        }

        return [
            "CONCAT(students.last_name, ', ', students.first_name, ' ', COALESCE(students.middle_name, ''))",
            "CONCAT(students.last_name, ', ', students.first_name)",
        ];
    }

    private function resolveRequiredAcademicPeriodId(): int
    {
        $sessionPeriodId = session('active_academic_period_id');
        if ($sessionPeriodId && AcademicPeriod::where('id', $sessionPeriodId)->where('is_deleted', false)->exists()) {
            return (int) $sessionPeriodId;
        }

        $latestPeriod = AcademicPeriod::where('is_deleted', false)
            ->orderByDesc('academic_year')
            ->orderByRaw("CASE semester WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN 'Summer' THEN 3 ELSE 4 END")
            ->first();

        if ($latestPeriod) {
            session(['active_academic_period_id' => $latestPeriod->id]);

            return (int) $latestPeriod->id;
        }

        abort(403, 'No active academic period is available.');
    }
}
