<?php

namespace App\Console\Commands;

use App\Services\MongoDB\MongoIndexManager;
use Illuminate\Console\Command;

/**
 * Create MongoDB Indexes Command
 * 
 * Creates all required indexes for MongoDB collections to ensure optimal performance.
 */
class CreateMongoIndexes extends Command
{
    protected $signature = 'mongo:create-indexes 
                            {--collection= : Specific collection to index}
                            {--analyze : Analyze existing indexes}
                            {--drop : Drop existing indexes before creating}';

    protected $description = 'Create MongoDB indexes for optimal query performance';

    public function handle(MongoIndexManager $indexManager): int
    {
        $this->info('🔧 MongoDB Index Management Tool');
        $this->newLine();

        // Analyze existing indexes
        if ($this->option('analyze')) {
            return $this->analyzeIndexes($indexManager);
        }

        // Drop existing indexes
        if ($this->option('drop')) {
            if (!$this->confirm('⚠️  This will drop all existing indexes. Continue?')) {
                $this->warn('Operation cancelled.');
                return Command::FAILURE;
            }

            $collections = [
                'student_scores',
                'student_term_grades',
                'student_final_grades',
                'student_outcome_attainments',
                'subject_activities',
                'grading_formulas',
                'grade_notifications',
            ];

            foreach ($collections as $collection) {
                $indexManager->dropAllIndexes($collection);
                $this->info("✓ Dropped indexes for: {$collection}");
            }

            $this->newLine();
        }

        // Create indexes
        $this->info('Creating MongoDB indexes...');
        $this->newLine();

        $results = $indexManager->createAllIndexes();

        foreach ($results as $collection => $indexes) {
            $count = is_array($indexes) ? count($indexes) : 0;
            $this->info("✓ {$collection}: {$count} indexes created");
        }

        $this->newLine();
        $this->info('✅ All indexes created successfully!');

        // Show recommendations
        $this->showRecommendations();

        return Command::SUCCESS;
    }

    /**
     * Analyze existing indexes
     */
    protected function analyzeIndexes(MongoIndexManager $indexManager): int
    {
        $collections = [
            'student_scores',
            'student_term_grades',
            'student_final_grades',
            'student_outcome_attainments',
            'subject_activities',
            'grading_formulas',
            'grade_notifications',
        ];

        $this->info('📊 Analyzing MongoDB Indexes');
        $this->newLine();

        foreach ($collections as $collection) {
            $analysis = $indexManager->analyzeQueryPerformance($collection);

            if (isset($analysis['error'])) {
                $this->error("✗ {$collection}: {$analysis['error']}");
                continue;
            }

            $this->info("📁 Collection: {$collection}");
            $this->line("   Documents: " . number_format($analysis['document_count']));
            $this->line("   Size: " . $this->formatBytes($analysis['size_bytes']));
            $this->line("   Avg Doc Size: " . $this->formatBytes($analysis['avg_document_size']));
            $this->line("   Indexes: {$analysis['total_indexes']}");

            if (!empty($analysis['indexes'])) {
                foreach ($analysis['indexes'] as $index) {
                    $keys = json_encode($index['keys']);
                    $this->line("      - {$index['name']}: {$keys}");
                }
            }

            $this->newLine();
        }

        // Check for slow queries
        $this->info('🐌 Checking for slow queries (>100ms)...');
        $slowQueries = $indexManager->getSlowQueries(100);

        if (isset($slowQueries['error'])) {
            $this->warn('Could not retrieve slow queries: ' . $slowQueries['error']);
        } elseif (empty($slowQueries)) {
            $this->info('✓ No slow queries detected!');
        } else {
            $this->warn('Found ' . count($slowQueries) . ' slow queries:');
            foreach (array_slice($slowQueries, 0, 5) as $query) {
                $this->line("   - {$query['operation']} on {$query['namespace']}: {$query['duration_ms']}ms");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Show performance recommendations
     */
    protected function showRecommendations(): void
    {
        $this->newLine();
        $this->info('💡 Performance Recommendations:');
        $this->line('   1. Run "php artisan mongo:create-indexes --analyze" periodically');
        $this->line('   2. Monitor slow queries in production logs');
        $this->line('   3. Use compound indexes for frequently joined queries');
        $this->line('   4. Enable MongoDB profiling: db.setProfilingLevel(1, 100)');
        $this->line('   5. Consider sharding for large collections (>100M documents)');
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
