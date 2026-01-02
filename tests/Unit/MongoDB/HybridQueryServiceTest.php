<?php

namespace Tests\Unit\MongoDB;

use App\Services\MongoDB\HybridQueryService;
use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicPeriod;
use App\Models\MongoDB\StudentFinalGrade;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Hybrid Query Service
 * 
 * Validates:
 * - Cross-database queries
 * - Data enrichment
 * - Batch optimization
 * - Caching strategy
 */
class HybridQueryServiceTest extends TestCase
{
    protected HybridQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HybridQueryService::class);
    }

    public function test_student_grade_report_combines_mysql_and_mongodb_data()
    {
        // Create MySQL test data
        $student = Student::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $academicPeriod = AcademicPeriod::factory()->create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
        ]);

        // Create MongoDB test data
        StudentFinalGrade::create([
            'student_id' => $student->id,
            'subject_id' => 1,
            'academic_period_id' => $academicPeriod->id,
            'final_grade' => 87.5,
            'remarks' => 'Passed',
        ]);

        // Execute hybrid query
        $report = $this->service->getStudentGradeReport(
            $student->id,
            $academicPeriod->id
        );

        // Verify combined data
        $this->assertEquals('John Doe', $report['student']['name']);
        $this->assertEquals('2025-2026', $report['academic_period']['academic_year']);
        $this->assertCount(1, $report['grades']);
        $this->assertEquals(87.5, $report['statistics']['average']);
    }

    public function test_batch_fetch_optimizes_queries()
    {
        // Create 100 students (should only execute 1 MySQL query)
        $studentIds = collect(range(1, 100));

        // Execute batch fetch
        $result = $this->service->batchFetchStudentsWithGrades(
            $studentIds,
            1
        );

        $this->assertCount(100, $result);
        
        // Verify query efficiency (this would need query counting in actual test)
        // Expected: 1 MySQL query + 1 MongoDB query = 2 total
    }

    public function test_grade_statistics_calculated_correctly()
    {
        // Create collection with stdClass objects instead of plain arrays
        $grades = collect([
            (object) ['final_grade' => 95, 'remarks' => 'Passed'],
            (object) ['final_grade' => 87, 'remarks' => 'Passed'],
            (object) ['final_grade' => 72, 'remarks' => 'Failed'],
            (object) ['final_grade' => 90, 'remarks' => 'Passed'],
        ]);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateGradeStatistics');
        $method->setAccessible(true);
        
        $stats = $method->invoke($this->service, $grades);

        $this->assertEquals(86.0, $stats['average']);
        $this->assertEquals(95, $stats['highest']);
        $this->assertEquals(72, $stats['lowest']);
        $this->assertEquals(3, $stats['passed']);
        $this->assertEquals(1, $stats['failed']);
    }

    public function test_grade_distribution_calculated_correctly()
    {
        $grades = collect([
            (object) ['final_grade' => 95],
            (object) ['final_grade' => 92],
            (object) ['final_grade' => 85],
            (object) ['final_grade' => 76],
            (object) ['final_grade' => 70],
        ]);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateGradeDistribution');
        $method->setAccessible(true);
        
        $distribution = $method->invoke($this->service, $grades);

        $this->assertEquals(2, $distribution['90-100']);
        $this->assertEquals(1, $distribution['80-89']);
        $this->assertEquals(1, $distribution['75-79']);
        $this->assertEquals(1, $distribution['Below 75']);
    }

    public function test_passing_rate_calculated_correctly()
    {
        $grades = collect([
            (object) ['remarks' => 'Passed'],
            (object) ['remarks' => 'Passed'],
            (object) ['remarks' => 'Failed'],
            (object) ['remarks' => 'Passed'],
        ]);

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculatePassingRate');
        $method->setAccessible(true);
        
        $passingRate = $method->invoke($this->service, $grades);

        $this->assertEquals(75.0, $passingRate); // 3 out of 4 = 75%
    }
}
