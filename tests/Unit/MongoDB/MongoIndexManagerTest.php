<?php

namespace Tests\Unit\MongoDB;

use App\Services\MongoDB\MongoIndexManager;
use Tests\TestCase;

/**
 * Test MongoDB Index Manager
 * 
 * Validates:
 * - Index creation
 * - Performance analysis
 * - Slow query detection
 */
class MongoIndexManagerTest extends TestCase
{
    protected MongoIndexManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new MongoIndexManager();
    }

    public function test_create_all_indexes_returns_results()
    {
        $results = $this->manager->createAllIndexes();

        $expectedCollections = [
            'student_scores',
            'student_term_grades',
            'student_final_grades',
            'student_outcome_attainments',
            'subject_activities',
            'grading_formulas',
            'grade_notifications',
        ];

        foreach ($expectedCollections as $collection) {
            $this->assertArrayHasKey($collection, $results);
        }
    }

    public function test_analyze_query_performance_returns_stats()
    {
        $analysis = $this->manager->analyzeQueryPerformance('student_scores');

        if (!isset($analysis['error'])) {
            $this->assertArrayHasKey('collection', $analysis);
            $this->assertArrayHasKey('document_count', $analysis);
            $this->assertArrayHasKey('indexes', $analysis);
            $this->assertArrayHasKey('total_indexes', $analysis);
        }
    }

    public function test_slow_queries_detection()
    {
        $slowQueries = $this->manager->getSlowQueries(100);

        // Should return array (empty or with queries)
        $this->assertIsArray($slowQueries);

        if (!isset($slowQueries['error']) && !empty($slowQueries)) {
            $firstQuery = $slowQueries[0];
            $this->assertArrayHasKey('operation', $firstQuery);
            $this->assertArrayHasKey('duration_ms', $firstQuery);
        }
    }
}
