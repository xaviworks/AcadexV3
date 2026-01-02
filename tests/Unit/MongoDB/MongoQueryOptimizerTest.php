<?php

namespace Tests\Unit\MongoDB;

use App\Services\MongoDB\MongoQueryOptimizer;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

/**
 * Test MongoDB Query Optimizer
 * 
 * Validates performance optimization utilities
 */
class MongoQueryOptimizerTest extends TestCase
{
    protected MongoQueryOptimizer $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optimizer = new MongoQueryOptimizer();
    }

    public function test_cache_query_with_tags()
    {
        $result = $this->optimizer->cacheQuery(
            'test_query',
            fn() => ['data' => 'test'],
            ['test_tag'],
            60
        );

        $this->assertEquals(['data' => 'test'], $result);
        
        // Verify it's cached
        $cached = Cache::tags(['test_tag'])->get('test_query');
        $this->assertNotNull($cached);
    }

    public function test_track_query_performance()
    {
        $result = $this->optimizer->trackQueryPerformance(
            fn() => ['data' => 'test'],
            'test_query'
        );

        $this->assertEquals(['data' => 'test'], $result);
    }

    public function test_calculate_optimal_batch_size()
    {
        // 2KB document, 50MB target = ~25,600 documents
        $batchSize = $this->optimizer->calculateOptimalBatchSize(2048, 50);

        $this->assertGreaterThanOrEqual(100, $batchSize);
        $this->assertLessThanOrEqual(10000, $batchSize);
    }

    public function test_generate_cache_key_consistent()
    {
        // Same params in different order should generate same key
        $key1 = $this->optimizer->generateCacheKey('test', [
            'b' => 2,
            'a' => 1,
        ]);

        $key2 = $this->optimizer->generateCacheKey('test', [
            'a' => 1,
            'b' => 2,
        ]);

        $this->assertEquals($key1, $key2);
    }

    public function test_build_projection()
    {
        $projection = $this->optimizer->buildProjection([
            'student_id',
            'final_grade',
            'remarks'
        ]);

        $this->assertEquals([
            'student_id' => 1,
            'final_grade' => 1,
            'remarks' => 1,
        ], $projection);
    }

    public function test_should_index_field()
    {
        $shouldIndex = $this->optimizer->shouldIndexField(
            'student_scores',
            'student_id',
            queryCount: 50,
            threshold: 10
        );

        $this->assertTrue($shouldIndex);
    }

    public function test_invalidate_cache()
    {
        // Set cache
        Cache::tags(['test_tag'])->put('test_key', 'value', 60);
        
        // Invalidate
        $this->optimizer->invalidateCache(['test_tag']);
        
        // Verify invalidated
        $cached = Cache::tags(['test_tag'])->get('test_key');
        $this->assertNull($cached);
    }
}
