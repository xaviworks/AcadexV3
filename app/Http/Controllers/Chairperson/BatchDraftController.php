<?php

namespace App\Http\Controllers\Chairperson;

use App\Http\Controllers\Controller;
use App\Models\BatchDraft;
use App\Models\BatchDraftStudent;
use App\Models\BatchDraftSubject;
use App\Models\Course;
use App\Models\CourseOutcomeTemplate;
use App\Models\CourseOutcomes;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentReviewImport;

class BatchDraftController extends Controller
{
    /**
     * Display a listing of batch drafts
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $academicPeriodId = session('active_academic_period_id');
        
        $batchDraftsQuery = BatchDraft::with(['course', 'coTemplate', 'creator', 'students'])
            ->active();

        if ($academicPeriodId) {
            $batchDraftsQuery->where('academic_period_id', $academicPeriodId);
        }

        // Filter by course for chairperson
        if ($user->role === 1) {
            $batchDraftsQuery->where('course_id', $user->course_id);
        }

        $batchDrafts = $batchDraftsQuery->orderBy('created_at', 'desc')->get();

        return view('chairperson.batch-drafts.index', compact('batchDrafts'));
    }

    /**
     * Show the form for creating a new batch draft
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $academicPeriodId = session('active_academic_period_id');
        
        if (!$academicPeriodId) {
            return redirect()->back()->with('error', 'No active academic period set.');
        }

        // Get available courses
        if ($user->role === 1) {
            $courses = Course::where('id', $user->course_id)->where('is_deleted', false)->get();
        } else {
            $courses = Course::where('is_deleted', false)->get();
        }

        // Get available CO templates
        if ($user->role === 1) {
            $coTemplates = CourseOutcomeTemplate::where(function ($query) use ($user) {
                $query->where('course_id', $user->course_id)
                      ->orWhere('is_universal', true);
            })->active()->get();
        } else {
            $coTemplates = CourseOutcomeTemplate::where('is_universal', true)->active()->get();
        }

        return view('chairperson.batch-drafts.create', compact('courses', 'coTemplates'));
    }

    /**
     * Store a newly created batch draft
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'batch_name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'year_level' => 'required|integer|min:1|max:10',
            'co_template_id' => 'required|exists:course_outcome_templates,id',
            'students_file' => 'required|file|mimes:xlsx,xls,csv',
            'description' => 'nullable|string',
        ]);

        $academicPeriodId = session('active_academic_period_id');
        
        if (!$academicPeriodId) {
            return redirect()->back()->with('error', 'No active academic period set.');
        }

        // Check uniqueness of batch name
        $exists = BatchDraft::where('batch_name', $validated['batch_name'])
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'A batch draft with this name already exists for the current academic period.');
        }

        DB::beginTransaction();
        try {
            // Create batch draft
            $batchDraft = BatchDraft::create([
                'batch_name' => $validated['batch_name'],
                'description' => $validated['description'] ?? null,
                'academic_period_id' => $academicPeriodId,
                'course_id' => $validated['course_id'],
                'year_level' => $validated['year_level'],
                'co_template_id' => $validated['co_template_id'],
                'created_by' => $user->id,
            ]);

            // Import students from file
            $file = $request->file('students_file');
            $this->importStudentsFromFile($file, $batchDraft);

            DB::commit();

            return redirect()
                ->route('chairperson.batch-drafts.show', $batchDraft)
                ->with('success', 'Batch draft created successfully with ' . $batchDraft->students()->count() . ' students.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create batch draft: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified batch draft
     */
    public function show(BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $batchDraft->load([
            'course',
            'coTemplate.items',
            'creator',
            'students.course',
            'batchDraftSubjects.subject.instructor'
        ]);

        // Get available subjects for assignment
        $academicPeriodId = $batchDraft->academic_period_id;
        $availableSubjects = Subject::where('academic_period_id', $academicPeriodId)
            ->where('course_id', $batchDraft->course_id)
            ->where('is_deleted', false)
            ->whereNotIn('id', $batchDraft->subjects->pluck('id'))
            ->get();

        return view('chairperson.batch-drafts.show', compact('batchDraft', 'availableSubjects'));
    }

    /**
     * Attach subjects to batch draft
     */
    public function attachSubjects(Request $request, BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $validated = $request->validate([
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            $attachedCount = 0;
            foreach ($validated['subject_ids'] as $subjectId) {
                // Check if already attached
                if (!$batchDraft->subjects()->where('subject_id', $subjectId)->exists()) {
                    BatchDraftSubject::create([
                        'batch_draft_id' => $batchDraft->id,
                        'subject_id' => $subjectId,
                        'configuration_applied' => false,
                    ]);
                    $attachedCount++;
                }
            }

            DB::commit();

            return redirect()
                ->route('chairperson.batch-drafts.show', $batchDraft)
                ->with('success', $attachedCount . ' subject(s) attached to batch draft successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to attach subjects: ' . $e->getMessage());
        }
    }

    /**
     * Apply batch draft configuration to subjects
     */
    public function applyConfiguration(Request $request, BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            $batchDraft->load('coTemplate.items', 'students');

            $batchDraftSubject = BatchDraftSubject::where('batch_draft_id', $batchDraft->id)
                ->where('subject_id', $validated['subject_id'])
                ->first();

            if (!$batchDraftSubject) {
                return redirect()->back()->with('error', 'Subject not found in this batch draft.');
            }

            if ($batchDraftSubject->configuration_applied) {
                return redirect()->back()->with('warning', 'Configuration already applied to this subject.');
            }

            $subject = Subject::find($validated['subject_id']);
            
            // Apply CO template to subject
            $this->applyCOTemplate($subject, $batchDraft->coTemplate, $batchDraft->academic_period_id, $user->id);
            
            // Import students to subject
            $this->importStudentsToSubject($subject, $batchDraft->students);
            
            // Mark configuration as applied
            $batchDraftSubject->update(['configuration_applied' => true]);

            DB::commit();

            return redirect()
                ->route('chairperson.batch-drafts.show', $batchDraft)
                ->with('success', "Configuration applied successfully! Imported {$batchDraft->students->count()} students and created {$batchDraft->coTemplate->items->count()} course outcomes.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to apply configuration: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified batch draft
     */
    public function edit(BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        // Get available courses
        if ($user->role === 1) {
            $courses = Course::where('id', $user->course_id)->where('is_deleted', false)->get();
        } else {
            $courses = Course::where('is_deleted', false)->get();
        }

        // Get available CO templates
        if ($user->role === 1) {
            $coTemplates = CourseOutcomeTemplate::where(function ($query) use ($user) {
                $query->where('course_id', $user->course_id)
                      ->orWhere('is_universal', true);
            })->active()->get();
        } else {
            $coTemplates = CourseOutcomeTemplate::where('is_universal', true)->active()->get();
        }

        return view('chairperson.batch-drafts.edit', compact('batchDraft', 'courses', 'coTemplates'));
    }

    /**
     * Update the specified batch draft
     */
    public function update(Request $request, BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $validated = $request->validate([
            'batch_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'co_template_id' => 'required|exists:course_outcome_templates,id',
        ]);

        // Check if user has access to the selected course
        if ($user->role === 1 && $validated['course_id'] != $user->course_id) {
            return redirect()->back()->with('error', 'You can only create batch drafts for your assigned course.')->withInput();
        }

        $batchDraft->update($validated);

        return redirect()
            ->route('chairperson.batch-drafts.show', $batchDraft)
            ->with('success', 'Batch draft updated successfully.');
    }

    /**
     * Remove the specified batch draft (soft delete)
     */
    public function destroy(BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $batchDraft->update(['is_deleted' => true]);

        return redirect()
            ->route('chairperson.batch-drafts.index')
            ->with('success', 'Batch draft deleted successfully.');
    }

    /**
     * Import students from uploaded file
     */
    private function importStudentsFromFile($file, BatchDraft $batchDraft)
    {
        // Read the file as array without using an import class
        $data = Excel::toArray([], $file);
        
        if (empty($data) || empty($data[0])) {
            throw new \Exception('No data found in the uploaded file.');
        }

        $rows = $data[0];
        $header = array_shift($rows); // Remove header row
        
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row[0]) && empty($row[2])) {
                continue;
            }

            BatchDraftStudent::create([
                'batch_draft_id' => $batchDraft->id,
                'first_name' => trim($row[0] ?? ''),
                'middle_name' => !empty($row[1]) ? trim($row[1]) : null,
                'last_name' => trim($row[2] ?? ''),
                'year_level' => !empty($row[3]) ? (int)$row[3] : $batchDraft->year_level,
                'course_id' => $batchDraft->course_id,
            ]);
        }
    }

    /**
     * Apply CO template to a subject
     */
    private function applyCOTemplate($subject, $coTemplate, $academicPeriodId, $userId)
    {
        foreach ($coTemplate->items as $item) {
            // Generate CO identifier (e.g., IT101.1, IT101.2)
            $coIdentifier = $subject->subject_code . '.' . substr($item->co_code, 2); // Remove "CO" prefix
            
            // Check if CO already exists
            $existingCO = CourseOutcomes::where('subject_id', $subject->id)
                ->where('academic_period_id', $academicPeriodId)
                ->where('co_code', $item->co_code)
                ->where('is_deleted', false)
                ->first();

            if (!$existingCO) {
                CourseOutcomes::create([
                    'subject_id' => $subject->id,
                    'academic_period_id' => $academicPeriodId,
                    'co_code' => $item->co_code,
                    'co_identifier' => $coIdentifier,
                    'description' => $item->description,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }

    /**
     * Import students from batch draft to subject
     */
    private function importStudentsToSubject($subject, $batchStudents)
    {
        foreach ($batchStudents as $batchStudent) {
            // Check if student exists in students table
            $student = Student::where('first_name', $batchStudent->first_name)
                ->where('last_name', $batchStudent->last_name)
                ->where('course_id', $batchStudent->course_id)
                ->first();

            // Create student if doesn't exist
            if (!$student) {
                $student = Student::create([
                    'first_name' => $batchStudent->first_name,
                    'middle_name' => $batchStudent->middle_name,
                    'last_name' => $batchStudent->last_name,
                    'year_level' => $batchStudent->year_level,
                    'course_id' => $batchStudent->course_id,
                    'department_id' => $subject->department_id,
                    'academic_period_id' => $subject->academic_period_id,
                    'is_deleted' => false,
                ]);
            }

            // Link student to subject if not already linked
            $exists = StudentSubject::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->exists();

            if (!$exists) {
                StudentSubject::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                ]);
            }
        }
    }
}
