<?php

namespace App\Services\MongoDB;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Driver\Exception\Exception as MongoException;

/**
 * MongoDB Index Manager
 * 
 * Handles index creation, optimization, and monitoring to prevent performance degradation.
 * 
 * RATIONALE:
 * - Ensures all collections have optimal indexes
 * - Monitors index usage and efficiency
 * - Provides recommendations for missing indexes
 */
class MongoIndexManager
{
    protected $connection = 'mongodb';

    /**
     * Create all required indexes for academic collections
     * 
     * @return array Index creation results
     */
    public function createAllIndexes(): array
    {
        $results = [];

        $results['student_scores'] = $this->createStudentScoresIndexes();
        $results['student_term_grades'] = $this->createStudentTermGradesIndexes();
        $results['student_final_grades'] = $this->createStudentFinalGradesIndexes();
        $results['student_outcome_attainments'] = $this->createStudentOutcomeAttainmentsIndexes();
        $results['subject_activities'] = $this->createSubjectActivitiesIndexes();
        $results['grading_formulas'] = $this->createGradingFormulasIndexes();
        $results['grade_notifications'] = $this->createGradeNotificationsIndexes();

        Log::info('MongoDB indexes created', ['results' => $results]);

        return $results;
    }

    /**
     * Create indexes for student_scores collection
     */
    protected function createStudentScoresIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('student_scores');
        $results = [];

        // Get existing indexes to avoid duplicates
        $existingIndexes = [];
        try {
            foreach ($collection->listIndexes() as $index) {
                $existingIndexes[] = $index['name'];
            }
        } catch (MongoException $e) {
            // Collection may not exist yet
        }

        try {
            // Compound index: student + academic period (most common query pattern)
            if (!in_array('idx_student_academic_period', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['student_id' => 1, 'academic_period_id' => 1],
                    ['name' => 'idx_student_academic_period', 'background' => true]
                );
            }

            // Index for activity-based queries
            if (!in_array('idx_activity', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['activity_id' => 1],
                    ['name' => 'idx_activity', 'background' => true]
                );
            }

            // Compound index: subject + academic period (for subject reports)
            if (!in_array('idx_subject_academic_period', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['subject_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_subject_academic_period', 'background' => true]
            );
            }

            // Index for soft deletes
            if (!in_array('idx_is_deleted', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['is_deleted' => 1],
                    ['name' => 'idx_is_deleted', 'background' => true]
                );
            }

            // Compound index for student + subject + not deleted (grade computation)
            if (!in_array('idx_student_subject_active', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['student_id' => 1, 'subject_id' => 1, 'is_deleted' => 1],
                    ['name' => 'idx_student_subject_active', 'background' => true]
                );
            }

            // TTL index for audit (created_at) - useful for archiving old records
            if (!in_array('idx_created_at', $existingIndexes)) {
                $results[] = $collection->createIndex(
                    ['created_at' => 1],
                    ['name' => 'idx_created_at', 'background' => true]
                );
            }

        } catch (MongoException $e) {
            Log::error('Failed to create student_scores indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for student_term_grades collection
     */
    protected function createStudentTermGradesIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('student_term_grades');
        $results = [];

        try {
            // Compound index: student + academic period
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_student_academic_period', 'background' => true]
            );

            // Compound index: subject + term (for term reports)
            $results[] = $collection->createIndex(
                ['subject_id' => 1, 'term_id' => 1],
                ['name' => 'idx_subject_term', 'background' => true]
            );

            // Unique compound index: prevent duplicate term grades
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'subject_id' => 1, 'term_id' => 1, 'academic_period_id' => 1],
                [
                    'name' => 'idx_unique_term_grade',
                    'unique' => true,
                    'partialFilterExpression' => ['is_deleted' => false],
                    'background' => true
                ]
            );

            // Index for soft deletes
            $results[] = $collection->createIndex(
                ['is_deleted' => 1],
                ['name' => 'idx_is_deleted', 'background' => true]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create student_term_grades indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for student_final_grades collection
     */
    protected function createStudentFinalGradesIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('student_final_grades');
        $results = [];

        try {
            // Compound index: student + academic period
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_student_academic_period', 'background' => true]
            );

            // Compound index: subject + academic period
            $results[] = $collection->createIndex(
                ['subject_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_subject_academic_period', 'background' => true]
            );

            // Compound index: remarks + not deleted (for pass/fail reports)
            $results[] = $collection->createIndex(
                ['remarks' => 1, 'is_deleted' => 1],
                ['name' => 'idx_remarks_active', 'background' => true]
            );

            // Unique compound index: prevent duplicate final grades
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'subject_id' => 1, 'academic_period_id' => 1],
                [
                    'name' => 'idx_unique_final_grade',
                    'unique' => true,
                    'partialFilterExpression' => ['is_deleted' => false],
                    'background' => true
                ]
            );

            // Index for grade range queries (e.g., find all grades > 90)
            $results[] = $collection->createIndex(
                ['final_grade' => 1],
                ['name' => 'idx_final_grade_value', 'background' => true]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create student_final_grades indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for student_outcome_attainments collection
     */
    protected function createStudentOutcomeAttainmentsIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('student_outcome_attainments');
        $results = [];

        try {
            // Compound index: student + subject
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'subject_id' => 1],
                ['name' => 'idx_student_subject', 'background' => true]
            );

            // Index for course outcome analysis
            $results[] = $collection->createIndex(
                ['course_outcome_id' => 1],
                ['name' => 'idx_course_outcome', 'background' => true]
            );

            // Index for academic period reports
            $results[] = $collection->createIndex(
                ['academic_period_id' => 1],
                ['name' => 'idx_academic_period', 'background' => true]
            );

            // Compound index for term-based queries
            $results[] = $collection->createIndex(
                ['term' => 1, 'course_outcome_id' => 1],
                ['name' => 'idx_term_outcome', 'background' => true]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create student_outcome_attainments indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for subject_activities collection
     */
    protected function createSubjectActivitiesIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('subject_activities');
        $results = [];

        try {
            // Compound index: subject + academic period
            $results[] = $collection->createIndex(
                ['subject_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_subject_academic_period', 'background' => true]
            );

            // Compound index: term + type (for filtering activities)
            $results[] = $collection->createIndex(
                ['term' => 1, 'type' => 1],
                ['name' => 'idx_term_type', 'background' => true]
            );

            // Index for soft deletes
            $results[] = $collection->createIndex(
                ['is_deleted' => 1],
                ['name' => 'idx_is_deleted', 'background' => true]
            );

            // Compound index: subject + term + not deleted (most common query)
            $results[] = $collection->createIndex(
                ['subject_id' => 1, 'term' => 1, 'is_deleted' => 1],
                ['name' => 'idx_subject_term_active', 'background' => true]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create subject_activities indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for grading_formulas collection
     */
    protected function createGradingFormulasIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('grading_formulas');
        $results = [];

        try {
            // Compound index: department + academic period
            $results[] = $collection->createIndex(
                ['department_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_department_academic_period', 'background' => true]
            );

            // Compound index: course + subject (for specific formula lookup)
            $results[] = $collection->createIndex(
                ['course_id' => 1, 'subject_id' => 1],
                ['name' => 'idx_course_subject', 'background' => true]
            );

            // Index for active formulas
            $results[] = $collection->createIndex(
                ['is_active' => 1],
                ['name' => 'idx_is_active', 'background' => true]
            );

            // Compound index: scope + active (for hierarchical formula lookup)
            $results[] = $collection->createIndex(
                ['scope' => 1, 'is_active' => 1],
                ['name' => 'idx_scope_active', 'background' => true]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create grading_formulas indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Create indexes for grade_notifications collection
     */
    protected function createGradeNotificationsIndexes(): array
    {
        $collection = DB::connection($this->connection)->getCollection('grade_notifications');
        $results = [];

        try {
            // Compound index: user + read status (for fetching unread notifications)
            $results[] = $collection->createIndex(
                ['user_id' => 1, 'is_read' => 1],
                ['name' => 'idx_user_read_status', 'background' => true]
            );

            // Index for sorting by date (descending)
            $results[] = $collection->createIndex(
                ['created_at' => -1],
                ['name' => 'idx_created_at_desc', 'background' => true]
            );

            // Compound index: student + academic period (for student-specific notifications)
            $results[] = $collection->createIndex(
                ['student_id' => 1, 'academic_period_id' => 1],
                ['name' => 'idx_student_academic_period', 'background' => true]
            );

            // TTL index: auto-delete old notifications after 90 days
            $results[] = $collection->createIndex(
                ['created_at' => 1],
                [
                    'name' => 'idx_ttl_notifications',
                    'expireAfterSeconds' => 7776000, // 90 days
                    'background' => true
                ]
            );

        } catch (MongoException $e) {
            Log::error('Failed to create grade_notifications indexes', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Analyze query performance and suggest missing indexes
     * 
     * @param string $collection Collection name
     * @return array Performance analysis
     */
    public function analyzeQueryPerformance(string $collection): array
    {
        try {
            $db = DB::connection($this->connection)->getMongoDB();
            
            // Get current indexes
            $currentIndexes = iterator_to_array($db->$collection->listIndexes());
            
            // Get collection stats
            $stats = $db->command(['collStats' => $collection])->toArray()[0];
            
            return [
                'collection' => $collection,
                'document_count' => $stats['count'] ?? 0,
                'size_bytes' => $stats['size'] ?? 0,
                'avg_document_size' => $stats['avgObjSize'] ?? 0,
                'indexes' => array_map(fn($idx) => [
                    'name' => $idx['name'],
                    'keys' => $idx['key'],
                ], $currentIndexes),
                'total_indexes' => count($currentIndexes),
            ];
        } catch (MongoException $e) {
            Log::error('Failed to analyze query performance', [
                'collection' => $collection,
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get slow queries from MongoDB profiler
     * 
     * @param int $thresholdMs Threshold in milliseconds
     * @return array Slow queries
     */
    public function getSlowQueries(int $thresholdMs = 100): array
    {
        try {
            $db = DB::connection($this->connection)->getMongoDB();
            
            // Enable profiling if not already enabled
            $db->command(['profile' => 2, 'slowms' => $thresholdMs]);
            
            // Query system.profile collection
            $slowQueries = $db->selectCollection('system.profile')
                ->find(
                    ['millis' => ['$gte' => $thresholdMs]],
                    ['sort' => ['ts' => -1], 'limit' => 50]
                )
                ->toArray();
            
            return array_map(function($query) {
                return [
                    'operation' => $query['op'] ?? 'unknown',
                    'namespace' => $query['ns'] ?? 'unknown',
                    'duration_ms' => $query['millis'] ?? 0,
                    'timestamp' => $query['ts'] ?? null,
                    'query' => $query['command'] ?? [],
                ];
            }, $slowQueries);
            
        } catch (MongoException $e) {
            Log::error('Failed to get slow queries', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Drop all indexes for a collection (use with caution)
     * 
     * @param string $collection Collection name
     * @return bool Success status
     */
    public function dropAllIndexes(string $collection): bool
    {
        try {
            $db = DB::connection($this->connection)->getMongoDB();
            $db->$collection->dropIndexes();
            
            Log::warning('All indexes dropped for collection', ['collection' => $collection]);
            return true;
        } catch (MongoException $e) {
            Log::error('Failed to drop indexes', [
                'collection' => $collection,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
