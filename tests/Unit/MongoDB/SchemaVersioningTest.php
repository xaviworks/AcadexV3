<?php

namespace Tests\Unit\MongoDB;

use App\Models\MongoDB\StudentScore;
use App\Models\MongoDB\SubjectActivity;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Schema Versioning and Migration
 * 
 * Validates:
 * - Automatic version assignment
 * - Schema migration on read
 * - Transformation functions
 * - Backward compatibility
 */
class SchemaVersioningTest extends TestCase
{
    public function test_new_document_gets_current_schema_version()
    {
        $score = new StudentScore([
            'student_id' => 1,
            'activity_id' => 'test123',
            'score' => 95.5,
            'max_score' => 100,
        ]);

        $this->assertEquals(3, $score->getSchemaVersion());
    }

    public function test_document_with_old_version_needs_migration()
    {
        $score = new StudentScore([
            'student_id' => 1,
            'activity_id' => 'test123',
            'score' => 95.5,
            '_schema_version' => 1, // Old version
        ]);

        $this->assertTrue($score->needsMigration());
    }

    public function test_schema_migration_transforms_document()
    {
        // Create document with v1 schema (no percentage field)
        $score = new StudentScore([
            'student_id' => 1,
            'activity_id' => 'test123',
            'score' => 90,
            'max_score' => 100,
            '_schema_version' => 1,
        ]);

        // Trigger migration
        $score->migrateSchema();

        // Should now have percentage calculated
        $this->assertEquals(90.0, $score->percentage);
        $this->assertEquals(3, $score->getSchemaVersion());
    }

    public function test_metadata_can_be_added_to_document()
    {
        $score = new StudentScore([
            'student_id' => 1,
            'activity_id' => 'test123',
            'score' => 95.5,
        ]);

        $score->addMetadata('submitted_late', true);
        $score->addMetadata('excuse_reason', 'Medical emergency');

        $this->assertTrue($score->getMetadata('submitted_late'));
        $this->assertEquals('Medical emergency', $score->getMetadata('excuse_reason'));
    }

    public function test_audit_trail_tracks_changes()
    {
        $score = new StudentScore([
            'student_id' => 1,
            'activity_id' => 'test123',
            'score' => 95.5,
            'max_score' => 100,
        ]);

        $score->updateScore(98.0);

        $audit = $score->getLatestAudit();
        
        $this->assertNotNull($audit);
        $this->assertEquals('score_updated', $audit['action']);
        $this->assertEquals(95.5, $audit['old_value']);
        $this->assertEquals(98.0, $audit['new_value']);
    }
}
