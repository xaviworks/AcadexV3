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
        // Redirect to wizard for simplified single-entry workflow
        return redirect()->route('chairperson.batch-drafts.wizard');
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
     * Show the smart wizard for quick batch setup
     */
    public function wizard()
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

        // Get previous batch drafts for cloning
        $previousBatches = BatchDraft::with('course', 'coTemplate')
            ->active()
            ->where('academic_period_id', '!=', $academicPeriodId)
            ->when($user->role === 1, function ($q) use ($user) {
                $q->where('course_id', $user->course_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('chairperson.batch-drafts.wizard', compact('courses', 'coTemplates', 'previousBatches'));
    }

    /**
     * Store batch draft from wizard with all configurations
     */
    public function storeWizard(Request $request)
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
            'description' => 'nullable|string',
            'student_import_method' => 'required|in:file,paste,previous_batch',
            'students_file' => 'required_if:student_import_method,file|file|mimes:xlsx,xls,csv',
            'students_paste' => 'nullable|required_if:student_import_method,paste|string',
            'previous_batch_id' => 'nullable|required_if:student_import_method,previous_batch|exists:batch_drafts,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'apply_immediately' => 'boolean',
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

            // Import students based on method
            switch ($validated['student_import_method']) {
                case 'file':
                    $this->importStudentsFromFile($request->file('students_file'), $batchDraft);
                    break;
                case 'paste':
                    $this->importStudentsFromPaste($validated['students_paste'], $batchDraft);
                    break;
                case 'previous_batch':
                    $this->importStudentsFromPreviousBatch($validated['previous_batch_id'], $batchDraft);
                    break;
            }

            // Attach and configure subjects if provided (merged attach + apply)
            if (!empty($validated['subject_ids'])) {
                $batchDraft->load('coTemplate.items', 'students');
                
                foreach ($validated['subject_ids'] as $subjectId) {
                    $subject = Subject::find($subjectId);
                    
                    // Create batch draft subject relationship
                    BatchDraftSubject::create([
                        'batch_draft_id' => $batchDraft->id,
                        'subject_id' => $subjectId,
                        'configuration_applied' => $validated['apply_immediately'] ?? true,
                    ]);
                    
                    // Apply configuration by default (unless explicitly disabled)
                    if ($validated['apply_immediately'] ?? true) {
                        $this->applyCOTemplate($subject, $batchDraft->coTemplate, $batchDraft->academic_period_id, $user->id);
                        $this->importStudentsToSubject($subject, $batchDraft->students);
                    }
                }
            }

            DB::commit();

            $configuredCount = ($validated['apply_immediately'] ?? true) && !empty($validated['subject_ids']) 
                ? count($validated['subject_ids']) 
                : 0;
            
            $message = 'Batch draft created successfully with ' . $batchDraft->students()->count() . ' students';
            if (!empty($validated['subject_ids'])) {
                $message .= ' and ' . count($validated['subject_ids']) . ' subjects';
                if ($configuredCount > 0) {
                    $message .= ' (all configured automatically)';
                } else {
                    $message .= ' (ready for configuration)';
                }
                if ($validated['apply_immediately'] ?? false) {
                    $message .= ' (configuration applied)';
                }
            }

            return redirect()
                ->route('chairperson.batch-drafts.show', $batchDraft)
                ->with('success', $message . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create batch draft: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk operations dashboard
     */
    public function bulkOperations()
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $academicPeriodId = session('active_academic_period_id');
        
        if (!$academicPeriodId) {
            return redirect()->back()->with('error', 'No active academic period set.');
        }

        // Get all subjects for the current academic period
        $subjectsQuery = Subject::with(['course', 'instructor', 'batchDraftSubject'])
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false);

        if ($user->role === 1) {
            $subjectsQuery->where('course_id', $user->course_id);
        }

        $subjects = $subjectsQuery->get();

        // Get batch drafts
        $batchDraftsQuery = BatchDraft::with('course', 'coTemplate')
            ->active()
            ->where('academic_period_id', $academicPeriodId);

        if ($user->role === 1) {
            $batchDraftsQuery->where('course_id', $user->course_id);
        }

        $batchDrafts = $batchDraftsQuery->get();

        // Get available courses
        if ($user->role === 1) {
            $courses = Course::where('id', $user->course_id)->where('is_deleted', false)->get();
        } else {
            $courses = Course::where('is_deleted', false)->get();
        }

        return view('chairperson.batch-drafts.bulk-operations', compact('subjects', 'batchDrafts', 'courses'));
    }

    /**
     * Apply bulk configuration to multiple subjects
     */
    public function bulkApplyConfiguration(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 1 && $user->role !== 4) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'batch_draft_id' => 'required|exists:batch_drafts,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $batchDraft = BatchDraft::with('coTemplate.items', 'students')->findOrFail($validated['batch_draft_id']);

        // Check permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            $failedSubjects = [];

            foreach ($validated['subject_ids'] as $subjectId) {
                try {
                    // Attach subject to batch if not already attached
                    $batchDraftSubject = BatchDraftSubject::firstOrCreate([
                        'batch_draft_id' => $batchDraft->id,
                        'subject_id' => $subjectId,
                    ], [
                        'configuration_applied' => false,
                    ]);

                    // Skip if already applied
                    if ($batchDraftSubject->configuration_applied) {
                        continue;
                    }

                    $subject = Subject::find($subjectId);
                    
                    // Apply CO template
                    $this->applyCOTemplate($subject, $batchDraft->coTemplate, $batchDraft->academic_period_id, $user->id);
                    
                    // Import students
                    $this->importStudentsToSubject($subject, $batchDraft->students);
                    
                    // Mark as applied
                    $batchDraftSubject->update(['configuration_applied' => true]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $failedSubjects[] = $subject->subject_code . ': ' . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully configured {$successCount} subject(s).";
            if (!empty($failedSubjects)) {
                $message .= ' Failed: ' . implode(', ', $failedSubjects);
            }

            return redirect()
                ->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate an existing batch draft
     */
    public function duplicate(BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $batchDraft->load('students', 'batchDraftSubjects');

        $academicPeriodId = session('active_academic_period_id');

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

        return view('chairperson.batch-drafts.duplicate', compact('batchDraft', 'courses', 'coTemplates'));
    }

    /**
     * Store duplicated batch draft
     */
    public function storeDuplicate(Request $request, BatchDraft $batchDraft)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role === 1 && $batchDraft->course_id !== $user->course_id) {
            return redirect()->back()->with('error', 'Unauthorized access to this batch draft.');
        }

        $validated = $request->validate([
            'batch_name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'year_level' => 'required|integer|min:1|max:10',
            'co_template_id' => 'required|exists:course_outcome_templates,id',
            'description' => 'nullable|string',
            'clone_students' => 'boolean',
            'clone_subjects' => 'boolean',
            'promote_year_level' => 'boolean',
        ]);

        $academicPeriodId = session('active_academic_period_id');
        
        if (!$academicPeriodId) {
            return redirect()->back()->with('error', 'No active academic period set.');
        }

        // Check uniqueness
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
            // Create new batch draft
            $newBatchDraft = BatchDraft::create([
                'batch_name' => $validated['batch_name'],
                'description' => $validated['description'] ?? $batchDraft->description,
                'academic_period_id' => $academicPeriodId,
                'course_id' => $validated['course_id'],
                'year_level' => $validated['year_level'],
                'co_template_id' => $validated['co_template_id'],
                'created_by' => $user->id,
            ]);

            // Clone students if requested
            if ($validated['clone_students'] ?? false) {
                $batchDraft->load('students');
                foreach ($batchDraft->students as $student) {
                    $newYearLevel = ($validated['promote_year_level'] ?? false) 
                        ? $student->year_level + 1 
                        : $student->year_level;

                    BatchDraftStudent::create([
                        'batch_draft_id' => $newBatchDraft->id,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'year_level' => $newYearLevel,
                        'course_id' => $validated['course_id'],
                    ]);
                }
            }

            // Clone subject associations if requested
            if ($validated['clone_subjects'] ?? false) {
                $batchDraft->load('batchDraftSubjects');
                
                // Get subjects for the new academic period with same codes
                $originalSubjects = Subject::whereIn('id', $batchDraft->batchDraftSubjects->pluck('subject_id'))->get();
                
                foreach ($originalSubjects as $originalSubject) {
                    $newSubject = Subject::where('subject_code', $originalSubject->subject_code)
                        ->where('academic_period_id', $academicPeriodId)
                        ->where('course_id', $validated['course_id'])
                        ->where('is_deleted', false)
                        ->first();

                    if ($newSubject) {
                        BatchDraftSubject::create([
                            'batch_draft_id' => $newBatchDraft->id,
                            'subject_id' => $newSubject->id,
                            'configuration_applied' => false,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('chairperson.batch-drafts.show', $newBatchDraft)
                ->with('success', 'Batch draft duplicated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to duplicate batch draft: ' . $e->getMessage());
        }
    }

    /**
     * Get subjects for a course and year level (AJAX)
     */
    public function getSubjectsForCourse(Request $request)
    {
        $courseId = $request->input('course_id');
        $yearLevel = $request->input('year_level');
        $academicPeriodId = session('active_academic_period_id');

        $subjects = Subject::where('course_id', $courseId)
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false)
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel);
            })
            ->with('batchDraftSubject')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'subject_code' => $subject->subject_code,
                    'subject_name' => $subject->subject_description,
                    'has_batch_draft' => $subject->batchDraftSubject && $subject->batchDraftSubject->configuration_applied,
                ];
            });

        return response()->json($subjects);
    }

    /**
     * Remove a student from batch draft
     */
    public function destroyStudent(BatchDraft $batchDraft, BatchDraftStudent $student)
    {
        if ($student->batch_draft_id !== $batchDraft->id) {
            abort(403, 'Unauthorized');
        }
        
        $student->delete();
        
        return back()->with('success', 'Student removed successfully');
    }

    /**
     * Add a new student to batch draft
     */
    public function addStudent(Request $request, BatchDraft $batchDraft)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
        ]);
        
        BatchDraftStudent::create([
            'batch_draft_id' => $batchDraft->id,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'year_level' => $batchDraft->year_level,
            'course_id' => $batchDraft->course_id,
        ]);
        
        return back()->with('success', 'Student added successfully');
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
     * Import students from pasted text
     */
    private function importStudentsFromPaste($pasteData, BatchDraft $batchDraft)
    {
        // Split by newlines
        $lines = preg_split('/\r\n|\r|\n/', trim($pasteData));
        
        if (empty($lines)) {
            throw new \Exception('No data found in pasted content.');
        }

        // Remove header if it looks like one
        if (!empty($lines[0]) && stripos($lines[0], 'name') !== false) {
            array_shift($lines);
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try to parse with tab or comma delimiter
            $parts = preg_split('/\t|,/', $line);
            
            // Remove quotes and trim
            $parts = array_map(function($part) {
                return trim($part, " \t\n\r\0\x0B\"'");
            }, $parts);

            if (count($parts) < 2) {
                continue; // Need at least first and last name
            }

            // Detect format: FirstName, MiddleName, LastName or FirstName, LastName
            $firstName = $parts[0] ?? '';
            $middleName = null;
            $lastName = '';
            $yearLevel = $batchDraft->year_level;

            if (count($parts) >= 4) {
                // Format: FirstName, MiddleName, LastName, YearLevel
                $middleName = $parts[1];
                $lastName = $parts[2];
                $yearLevel = is_numeric($parts[3]) ? (int)$parts[3] : $yearLevel;
            } elseif (count($parts) == 3) {
                // Could be: FirstName, MiddleName, LastName OR FirstName, LastName, YearLevel
                if (is_numeric($parts[2])) {
                    // FirstName, LastName, YearLevel
                    $lastName = $parts[1];
                    $yearLevel = (int)$parts[2];
                } else {
                    // FirstName, MiddleName, LastName
                    $middleName = $parts[1];
                    $lastName = $parts[2];
                }
            } else {
                // FirstName, LastName
                $lastName = $parts[1];
            }

            if (empty($firstName) || empty($lastName)) {
                continue;
            }

            BatchDraftStudent::create([
                'batch_draft_id' => $batchDraft->id,
                'first_name' => trim($firstName),
                'middle_name' => $middleName ? trim($middleName) : null,
                'last_name' => trim($lastName),
                'year_level' => $yearLevel,
                'course_id' => $batchDraft->course_id,
            ]);
        }
    }

    /**
     * Import students from a previous batch draft
     */
    private function importStudentsFromPreviousBatch($previousBatchId, BatchDraft $newBatchDraft)
    {
        $previousBatch = BatchDraft::with('students')->findOrFail($previousBatchId);
        
        if ($previousBatch->students->isEmpty()) {
            throw new \Exception('Previous batch has no students to import.');
        }

        foreach ($previousBatch->students as $student) {
            BatchDraftStudent::create([
                'batch_draft_id' => $newBatchDraft->id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'year_level' => $student->year_level,
                'course_id' => $newBatchDraft->course_id,
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
