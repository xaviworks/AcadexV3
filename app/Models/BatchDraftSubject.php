<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchDraftSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_draft_id',
        'subject_id',
        'configuration_applied',
    ];

    protected $casts = [
        'configuration_applied' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function batchDraft()
    {
        return $this->belongsTo(BatchDraft::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
