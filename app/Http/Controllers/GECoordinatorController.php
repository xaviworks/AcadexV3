<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\UnverifiedUser;
use App\Support\Organization\GEContext;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GECoordinatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Get all available instructors who can teach GE subjects
     */
    public function getAvailableInstructors()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');
        
        $instructors = User::where('role', 0)
            ->where('is_active', true)
            ->where(function($query) use ($geDepartment) {
                $this->applyManagedGEInstructorFilter($query, $geDepartment);
            })
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name)
                ];
            });
            
        return response()->json($instructors);
    }
    
    /**
     * Get all instructors assigned to a subject
     */
    public function getSubjectInstructors(Subject $subject)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        // Ensure the subject is a GE subject.
        if (!GEContext::isGESubject($subject)) {
            return response()->json(['error' => 'Subject is not managed by GE Coordinator'], 403);
        }
        
        $instructors = $subject->instructors->map(function($instructor) {
            return [
                'id' => $instructor->id,
                'name' => $instructor->name
            ];
        });
        
        return response()->json($instructors);
    }

    // ============================
    // Instructor Management
    // ============================

    public function manageInstructors()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        // GE Coordinator: show instructors from GE department AND those who can/could teach GE subjects
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');
        
        $instructors = User::where('role', 0)
            ->where(function($query) use ($geDepartment) {
                // Always show primary GE instructors (legacy GE department + canonical GE program)
                // and those with GE teaching access.
                $this->applyManagedGEInstructorFilter($query, $geDepartment);

                $query->orWhere(function($subQuery) {
                    // Show inactive instructors who previously had approved GE requests.
                    $subQuery->where('is_active', false)
                        ->whereHas('geSubjectRequests', function($requestQuery) {
                            $requestQuery->where('status', 'approved');
                        });
                });
            })
            ->orderBy('last_name')
            ->get();
            
        $pendingAccountsQuery = UnverifiedUser::with('department', 'course')
            ->whereNotNull('email_verified_at');

        GEContext::applyGERegistrationTargetFilter($pendingAccountsQuery);
        $pendingAccounts = $pendingAccountsQuery->get();
        
        return view('gecoordinator.manage-instructors', compact('instructors', 'pendingAccounts'));
    }

    public function storeInstructor(Request $request)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Validate base email format first
        $request->validate([
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|string|regex:/^[^@]+$/|max:255',
            'password'      => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'department_id' => 'required|exists:departments,id',
            'course_id'     => 'required|exists:courses,id',
        ]);

        $fullEmail = strtolower(trim($request->email)) . '@brokenshire.edu.ph';

        // Check uniqueness of the full email in both unverified_users and users tables
        if (UnverifiedUser::where('email', $fullEmail)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered and pending verification.'])->withInput();
        }

        if (User::where('email', $fullEmail)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        }

        UnverifiedUser::create([
            'first_name'    => $request->first_name,
            'middle_name'   => $request->middle_name,
            'last_name'     => $request->last_name,
            'email'         => $fullEmail,
            'password'      => Hash::make($request->password),
            'department_id' => $request->department_id,
            'course_id'     => $request->course_id,
        ]);

        return redirect()->back()->with('status', 'Instructor account submitted for approval.');
    }

    public function deactivateInstructor($id)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        // Find the instructor
        $instructor = User::where('id', $id)
            ->where('role', 0)
            ->where('is_active', true)
            ->firstOrFail();
        
        // Get GE department to check if instructor belongs to it
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');
        
        // Primary GE instructors (legacy GE department or canonical GE program)
        // are fully deactivated. Others only lose GE teaching access.
        if ($this->isPrimaryGEInstructor($instructor, $geDepartment)) {
            $instructor->update([
                'can_teach_ge' => false,
                'is_active' => false
            ]);
            $message = 'Instructor deactivated successfully.';
        } else {
            // For non-primary GE instructors, only remove GE teaching capability
            $instructor->update([
                'can_teach_ge' => false
            ]);
            $message = 'GE teaching access removed successfully. The instructor\'s account remains active under their department.';
        }
        
        // Update any existing approved GE requests to "revoked" status
        \App\Models\GESubjectRequest::where('instructor_id', $id)
            ->where('status', 'approved')
            ->update(['status' => 'revoked']);
        
        return redirect()->back()->with('success', $message);
    }

    public function activateInstructor($id)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        // Find the instructor
        $instructor = User::where('id', $id)
            ->where('role', 0)
            ->firstOrFail();
        
        // Get GE department to check if instructor belongs to it
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');
        
        // Primary GE instructors (legacy GE department or canonical GE program)
        // can be fully activated by GE Coordinator.
        if ($this->isPrimaryGEInstructor($instructor, $geDepartment)) {
            $instructor->update([
                'can_teach_ge' => true,
                'is_active' => true
            ]);
            $message = 'Instructor activated successfully.';
        } else {
            // For non-primary GE instructors, GE Coordinator cannot activate their account
            // They can only restore GE access if the instructor is already active
            if (!$instructor->is_active) {
                return redirect()->back()->with('error', 'Cannot activate instructors from other departments. Please contact the department chairperson to activate this instructor first.');
            }
            
            // Restore GE teaching capability only
            $instructor->update([
                'can_teach_ge' => true
            ]);
            $message = 'GE teaching access restored successfully.';
        }
        
        return redirect()->back()->with('success', $message);
    }

    // ============================
    // Subject Assignment
    // ============================

    public function assignSubjects()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403, 'Unauthorized action.');
        }
        
        $academicPeriodId = session('active_academic_period_id');
        if (!$academicPeriodId) {
            $academicPeriodId = \App\Models\AcademicPeriod::where('is_deleted', false)
                ->latest('id')
                ->value('id') ?? 1;
        }

        // Get subjects (GE, PD, NSTP, RS, PE, and universal subjects) for the current academic period
        $subjects = Subject::with('instructors')
            ->where(function($query) {
                $query->where('subject_code', 'LIKE', 'GE%')
                      ->orWhere('subject_code', 'LIKE', 'PD%')
                      ->orWhere('subject_code', 'LIKE', 'NSTP%')
                      ->orWhere('subject_code', 'LIKE', 'RS%')
                      ->orWhere('subject_code', 'LIKE', 'PE%')
                      ->orWhere('is_universal', true);
            })
            ->where('academic_period_id', $academicPeriodId) // Filter by current academic period
            ->where('is_deleted', false)
            ->orderBy('subject_code')
            ->get();
            
        // Group subjects by year level for the view
        $yearLevels = [];
        for ($i = 1; $i <= 4; $i++) {
            $yearLevels[$i] = $subjects->where('year_level', $i)->values();
        }
            
        // Get all GE instructors
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');
        
        // Get available instructors
        $instructors = User::where('role', 0)
            ->where(function($query) use ($geDepartment) {
                $this->applyManagedGEInstructorFilter($query, $geDepartment);
            })
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();

        // Add debug info to view
        $debugInfo = [
            'ge_department' => $geDepartment->toArray(),
            'subjects_count' => $subjects->count(),
            'year_levels' => array_map('count', $yearLevels),
            'instructors_count' => $instructors->count(),
            'academic_period_id' => $academicPeriodId
        ];

        return view('gecoordinator.assign-subjects', [
            'yearLevels' => $yearLevels, 
            'instructors' => $instructors,
            'debug' => $debugInfo  // Add debug info to view
        ]);
    }

    public function storeAssignedSubject(Request $request)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->managedByGE()
            ->firstOrFail();

        // Ensure the subject is managed by GE Coordinator.
        if (!$subject->isManagedByGE()) {
            return redirect()->back()->with('error', 'Only General Education subjects can be assigned by GE Coordinator.');
        }

        // Attach the instructor to the subject (many-to-many)
        $subject->instructors()->syncWithoutDetaching([$request->instructor_id]);

        return redirect()->back()->with('success', 'Instructor assigned to subject successfully.');
    }

    public function manageSchedule()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Get active academic period from session
        $academicPeriodId = session('active_academic_period_id');

        // Get GE subjects for the current academic period
        $subjects = Subject::query()
            ->where('academic_period_id', $academicPeriodId)
            ->managedByGE()
            ->with(['instructors', 'students'])
            ->orderBy('subject_code')
            ->get();

        return view('gecoordinator.manage-schedule', compact('subjects'));
    }

    public function reports()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Get active academic period from session
        $academicPeriodId = session('active_academic_period_id');

        // Get GE subjects statistics
        $totalSubjects = Subject::query()
            ->where('academic_period_id', $academicPeriodId)
            ->managedByGE()
            ->count();

        $assignedSubjects = Subject::query()
            ->where('academic_period_id', $academicPeriodId)
            ->managedByGE()
            ->whereHas('instructors')
            ->count();

        $unassignedSubjects = $totalSubjects - $assignedSubjects;

        // Get instructor statistics
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');

        $totalInstructors = User::where('role', 0)
            ->where(function($query) use ($geDepartment) {
                $this->applyManagedGEInstructorFilter($query, $geDepartment);
            })
            ->where('is_active', true)
            ->count();

        $geDepartmentId = GEContext::geDepartmentId();
        $geCourseId = GEContext::geCourseId();

        // Get student enrollment statistics
        $totalEnrollments = DB::table('student_subjects')
            ->join('subjects', 'student_subjects.subject_id', '=', 'subjects.id')
            ->where('subjects.academic_period_id', $academicPeriodId)
            ->where(function ($query) use ($geDepartmentId, $geCourseId) {
                $query->where('subjects.is_universal', true);

                if ($geDepartmentId !== null) {
                    $query->orWhere('subjects.department_id', $geDepartmentId);
                }

                if ($geCourseId !== null) {
                    $query->orWhere('subjects.course_id', $geCourseId);
                }
            })
            ->where('student_subjects.is_deleted', false)
            ->count();

        // Get subjects by year level
        $subjectsByYear = Subject::query()
            ->where('academic_period_id', $academicPeriodId)
            ->managedByGE()
            ->select('year_level', DB::raw('count(*) as count'))
            ->groupBy('year_level')
            ->get()
            ->pluck('count', 'year_level')
            ->toArray();

        $reportData = [
            'total_subjects' => $totalSubjects,
            'assigned_subjects' => $assignedSubjects,
            'unassigned_subjects' => $unassignedSubjects,
            'total_instructors' => $totalInstructors,
            'total_enrollments' => $totalEnrollments,
            'subjects_by_year' => $subjectsByYear
        ];

        return view('gecoordinator.reports', compact('reportData'));
    }

    public function toggleAssignedSubject(Request $request)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $subject = Subject::where('id', $request->subject_id)
            ->managedByGE()
            ->firstOrFail();
        
        // Ensure the subject is managed by GE Coordinator.
        if (!$subject->isManagedByGE()) {
            return response()->json(['error' => 'Only General Education subjects can be managed by GE Coordinator.'], 403);
        }
        
        // Get academic period label for notifications
        $academicPeriodId = session('active_academic_period_id');
        $academicPeriod = \App\Models\AcademicPeriod::find($academicPeriodId);
        $periodLabel = $academicPeriod ? "{$academicPeriod->semester} Semester {$academicPeriod->academic_year}" : null;

        // Check if we're assigning or unassigning
        if ($request->isMethod('delete')) {
            // Get instructor for notification before detaching
            $instructor = User::find($request->instructor_id);
            
            // Unassign the instructor
            $subject->instructors()->detach($request->instructor_id);
            
            // Notify instructor about course removal (Email + System)
            if ($instructor) {
                NotificationService::notifyCourseRemoved($instructor, $subject, $periodLabel);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Instructor unassigned successfully.',
                'action' => 'unassigned'
            ]);
        } else {
            // Assign the instructor (if not already assigned)
            $subject->instructors()->syncWithoutDetaching([$request->instructor_id]);
            $instructor = User::find($request->instructor_id);
            
            // Send notification to the instructor (Email + System)
            if ($instructor) {
                NotificationService::notifyCourseAssigned($instructor, $subject, $periodLabel);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Instructor assigned successfully.',
                'action' => 'assigned',
                'instructor_name' => $instructor?->name
            ]);
        }
    }

    // ============================
    // Grades Management
    // ============================

    public function viewGrades(Request $request)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }
        
        $selectedInstructorId = $request->input('instructor_id');
        $selectedSubjectId = $request->input('subject_id');
        
        $academicPeriodId = session('active_academic_period_id');
        
        // If no active academic period is set in session, use the latest non-deleted period
        if (!$academicPeriodId) {
            $academicPeriodId = \App\Models\AcademicPeriod::where('is_deleted', false)
                ->latest('id')
                ->value('id') ?? 1;
        }
        
        // Fetch all active instructors who are either:
        // - From the GE department, or
        // - Marked as can_teach_ge, or
        // - Assigned to GE subjects in the selected academic period.
        $geDepartment = Department::generalEducation();
        abort_if(!$geDepartment, 404, 'General Education department not found.');

        $instructors = User::where('role', 0)
            ->where('is_active', true)
            ->where(function($query) use ($academicPeriodId, $geDepartment) {
                $query->whereHas('subjects', function($q) use ($academicPeriodId) {
                        $q->managedByGE()
                            ->where('academic_period_id', $academicPeriodId)
                            ->where('is_deleted', false);
                    })
                    ->orWhere(function ($managedQuery) use ($geDepartment) {
                        $this->applyManagedGEInstructorFilter($managedQuery, $geDepartment);
                    });
            })
            ->orderBy('last_name')
            ->get();
    
        // Ensure selected instructor is in the list of accessible instructors
        if ($selectedInstructorId && !$instructors->pluck('id')->contains((int)$selectedInstructorId)) {
            $selectedInstructorId = null;
            $selectedSubjectId = null;
        }

        // Courses are loaded only when an instructor is selected
        $subjects = [];
        if ($selectedInstructorId) {
            $subjects = Subject::query()
                ->where('academic_period_id', $academicPeriodId)
                ->where('is_deleted', false)
                ->managedByGE()
                ->whereHas('instructors', function($query) use ($selectedInstructorId) {
                    $query->where('users.id', $selectedInstructorId);
                })
                ->orderBy('subject_code')
                ->get();
        }
    
        // Students and grades are only loaded when a subject is selected
        $students = [];
        if ($selectedSubjectId) {
            $subject = Subject::where('id', $selectedSubjectId)
                ->where('is_deleted', false)
                ->firstOrFail();
    
            $students = $subject->students()
                ->with(['termGrades' => function ($q) use ($selectedSubjectId) {
                    $q->where('subject_id', $selectedSubjectId);
                }])
                ->get();
        }
    
        return view('gecoordinator.view-grades', [
            'instructors' => $instructors,
            'subjects' => $subjects,
            'students' => $students,
            'selectedInstructorId' => $selectedInstructorId,
            'selectedSubjectId' => $selectedSubjectId,
        ]);
    }

    // ============================
    // Student Management
    // ============================

    public function viewStudentsPerYear()
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $selectedSubjectId = request()->input('subject_id');

        // Get all GE subjects that have instructors assigned
        $subjects = Subject::with('instructors')
            ->where(function($query) {
                $query->where('subject_code', 'LIKE', 'GE%')
                      ->orWhere('subject_code', 'LIKE', 'PE%')
                      ->orWhere('subject_code', 'LIKE', 'RS%')
                      ->orWhere('subject_code', 'LIKE', 'NSTP%');
            })
            ->whereHas('instructors')
            ->where('is_deleted', false)
            ->orderBy('subject_code')
            ->get();

        // Get students enrolled in the selected subject
        $students = collect();
        if ($selectedSubjectId) {
            $subject = Subject::find($selectedSubjectId);
            if ($subject) {
                $students = $subject->students()
                    ->with(['course', 'department'])
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        return view('gecoordinator.students-by-year', [
            'subjects' => $subjects,
            'students' => $students,
            'selectedSubjectId' => $selectedSubjectId
        ]);
    }

    // ============================
    // GE Assignment Request Management
    // ============================

    public function approveGERequest($id)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $request = \App\Models\GESubjectRequest::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Update the request status
        $request->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Get the instructor without changing their department
        $instructor = User::find($request->instructor_id);
        
        if ($instructor) {
            // Instead of changing the department, we can add a flag or role
            // to indicate they can teach GE subjects
            $instructor->can_teach_ge = true;
            $instructor->save();
        }
        
        // Notify the requesting chairperson (Email + System)
        NotificationService::notifyGERequestApproved($request, Auth::user());

        return redirect()->back()->with('status', 'GE assignment request approved successfully. The instructor can now teach GE subjects.');
    }

    public function rejectGERequest($id)
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $request = \App\Models\GESubjectRequest::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Update the request status
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        // Notify the requesting chairperson (Email + System)
        NotificationService::notifyGERequestRejected($request, Auth::user());

        return redirect()->back()->with('status', 'GE assignment request rejected successfully.');
    }

    private function applyManagedGEInstructorFilter(Builder $query, Department $geDepartment): Builder
    {
        $geCourseId = GEContext::geCourseId();

        $query->where('department_id', $geDepartment->id)
            ->orWhere('can_teach_ge', true);

        if ($geCourseId !== null) {
            $query->orWhere('course_id', $geCourseId);
        }

        return $query;
    }

    private function isPrimaryGEInstructor(User $instructor, ?Department $geDepartment = null): bool
    {
        $geDepartmentId = $geDepartment?->id ?? Department::generalEducation()?->id;

        $isLegacyGEDepartmentInstructor = $geDepartmentId !== null
            && (int) $instructor->department_id === (int) $geDepartmentId;

        $isCanonicalGEProgramInstructor = GEContext::isGERegistrationTarget(
            $instructor->department_id !== null ? (int) $instructor->department_id : null,
            $instructor->course_id !== null ? (int) $instructor->course_id : null
        );

        return $isLegacyGEDepartmentInstructor || $isCanonicalGEProgramInstructor;
    }
}