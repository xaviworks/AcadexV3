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
 * @property int $term_id
 * @property float|null $term_grade
 * @property bool $is_deleted
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read Student $student
 * @property-read Subject $subject
 * @property-read Term $term
 */
class TermGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'subject_id', 'academic_period_id', 'term_id', 
        'term_grade', 'is_deleted', 'created_by', 'updated_by'
    ];

    /**
     * Attribute casting.
     * 
     * Note: term_grade uses EncryptedDecimalCast which only encrypts
     * when ENCRYPT_GRADES=true is set in .env
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'term_grade' => EncryptedDecimalCast::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
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
     * Scope to filter only active (non-deleted) grades.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to filter by term.
     */
    public function scopeForTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Scope to filter by academic period.
     */
    public function scopeForPeriod(Builder $query, int $academicPeriodId): Builder
    {
        return $query->where('academic_period_id', $academicPeriodId);
    }

    // =========================================================================
    // Optimized Query Methods
    // =========================================================================

    /**
     * Get graded counts for multiple subjects in a single query.
     * Replaces N+1 pattern: foreach($subjects) { TermGrade::where('subject_id', $id)->count() }
     * 
     * @param Collection|array $subjectIds
     * @param int|null $termId
     * @return Collection keyed by subject_id => graded_count
     */
    public static function getGradedCountsBySubject(Collection|array $subjectIds, ?int $termId = null): Collection
    {
        $ids = $subjectIds instanceof Collection ? $subjectIds->all() : $subjectIds;
        
        $query = static::query()
            ->active()
            ->whereIn('subject_id', $ids)
            ->select('subject_id', DB::raw('COUNT(DISTINCT student_id) as graded_count'))
            ->groupBy('subject_id');

        if ($termId !== null) {
            $query->where('term_id', $termId);
        }

        return $query->pluck('graded_count', 'subject_id');
    }

    /**
     * Get term grades for a subject keyed by student_id.
     * 
     * @param int $subjectId
     * @param int $termId
     * @return Collection keyed by student_id
     */
    public static function getForSubjectTerm(int $subjectId, int $termId): Collection
    {
        return static::query()
            ->active()
            ->forSubject($subjectId)
            ->forTerm($termId)
            ->get()
            ->keyBy('student_id');
    }

    /**
     * Get all term grades for a subject grouped by term then keyed by student.
     * 
     * @param int $subjectId
     * @return Collection [term_id => [student_id => TermGrade]]
     */
    public static function getAllTermsForSubject(int $subjectId): Collection
    {
        return static::query()
            ->active()
            ->forSubject($subjectId)
            ->get()
            ->groupBy('term_id')
            ->map(fn ($group) => $group->keyBy('student_id'));
    }
}
