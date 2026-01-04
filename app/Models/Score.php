<?php

namespace App\Models;

use App\Support\Casts\EncryptedDecimalCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $activity_id
 * @property int $student_id
 * @property float|null $score
 * @property bool $is_deleted
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read Activity $activity
 * @property-read Student $student
 */
class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id', 'student_id', 'score',
        'is_deleted', 'created_by', 'updated_by'
    ];

    /**
     * Attribute casting.
     * 
     * Note: score uses EncryptedDecimalCast which only encrypts
     * when ENCRYPT_GRADES=true is set in .env
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'score' => EncryptedDecimalCast::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Scope to filter only active (non-deleted) scores.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope to filter by student IDs.
     */
    public function scopeForStudents(Builder $query, array|Collection $studentIds): Builder
    {
        $ids = $studentIds instanceof Collection ? $studentIds->all() : $studentIds;
        return $query->whereIn('student_id', $ids);
    }

    /**
     * Scope to filter by activity IDs.
     */
    public function scopeForActivities(Builder $query, array|Collection $activityIds): Builder
    {
        $ids = $activityIds instanceof Collection ? $activityIds->all() : $activityIds;
        return $query->whereIn('activity_id', $ids);
    }

    /**
     * Scope for optimized bulk loading of scores.
     * Returns scores keyed by student_id -> activity_id -> score.
     */
    public static function getBulkScores(array|Collection $studentIds, array|Collection $activityIds): Collection
    {
        return static::query()
            ->active()
            ->forStudents($studentIds)
            ->forActivities($activityIds)
            ->get()
            ->groupBy('student_id')
            ->map(fn ($group) => $group->keyBy('activity_id'));
    }

    /**
     * Scope for getting scores keyed by activity.
     */
    public static function getScoresByActivity(int $studentId, array|Collection $activityIds): Collection
    {
        return static::query()
            ->active()
            ->where('student_id', $studentId)
            ->forActivities($activityIds)
            ->get()
            ->keyBy('activity_id');
    }
}
