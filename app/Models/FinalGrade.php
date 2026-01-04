<?php

namespace App\Models;

use App\Support\Casts\EncryptedDecimalCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $student_id
 * @property int $subject_id
 * @property int|null $academic_period_id
 * @property float|null $final_grade
 * @property string|null $remarks
 * @property string|null $notes
 * @property bool $is_deleted
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read Student $student
 * @property-read Subject $subject
 * @property-read AcademicPeriod|null $academicPeriod
 */
class FinalGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_period_id',
        'final_grade',
        'remarks',
        'notes',
        'is_deleted',
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute casting.
     * 
     * Note: final_grade uses EncryptedDecimalCast which only encrypts
     * when ENCRYPT_GRADES=true is set in .env
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'final_grade' => EncryptedDecimalCast::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
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
     * Scope for active (non-deleted) records.
     * Alias: notDeleted() for backward compatibility.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope for not deleted records (backward compatibility).
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $this->scopeActive($query);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to filter by academic period.
     */
    public function scopeForPeriod(Builder $query, int $academicPeriodId): Builder
    {
        return $query->where('academic_period_id', $academicPeriodId);
    }

    /**
     * Scope to filter by remarks (Passed, Failed, Dropped).
     */
    public function scopeWithRemarks(Builder $query, string $remarks): Builder
    {
        return $query->where('remarks', $remarks);
    }

    // =========================================================================
    // Optimized Query Methods
    // =========================================================================

    /**
     * Get final grades for multiple subjects in a single query.
     * 
     * @param Collection|array $subjectIds
     * @param int|null $academicPeriodId
     * @return Collection grouped by subject_id
     */
    public static function getForSubjects(Collection|array $subjectIds, ?int $academicPeriodId = null): Collection
    {
        $ids = $subjectIds instanceof Collection ? $subjectIds->all() : $subjectIds;
        
        $query = static::query()
            ->active()
            ->whereIn('subject_id', $ids);

        if ($academicPeriodId !== null) {
            $query->forPeriod($academicPeriodId);
        }

        return $query->get()->groupBy('subject_id');
    }

    /**
     * Get statistics (pass/fail counts) for subjects.
     * 
     * @param Collection|array $subjectIds
     * @param int|null $academicPeriodId
     * @return Collection [subject_id => ['passed' => count, 'failed' => count, 'total' => count]]
     */
    public static function getStatsBySubject(Collection|array $subjectIds, ?int $academicPeriodId = null): Collection
    {
        $ids = $subjectIds instanceof Collection ? $subjectIds->all() : $subjectIds;
        
        $query = static::query()
            ->active()
            ->whereIn('subject_id', $ids)
            ->select(
                'subject_id',
                DB::raw("SUM(CASE WHEN remarks = 'Passed' THEN 1 ELSE 0 END) as passed"),
                DB::raw("SUM(CASE WHEN remarks = 'Failed' THEN 1 ELSE 0 END) as failed"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('subject_id');

        if ($academicPeriodId !== null) {
            $query->where('academic_period_id', $academicPeriodId);
        }

        return $query->get()->keyBy('subject_id');
    }

    /**
     * Get final grades for a subject keyed by student_id.
     * 
     * @param int $subjectId
     * @return Collection keyed by student_id
     */
    public static function getForSubjectByStudent(int $subjectId): Collection
    {
        return static::query()
            ->active()
            ->forSubject($subjectId)
            ->get()
            ->keyBy('student_id');
    }
}
