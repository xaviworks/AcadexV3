<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Base MongoDB Model with Schema Versioning
 * 
 * Provides flexible schema evolution capabilities for all MongoDB models.
 * Handles schema version migrations and backward compatibility.
 * 
 * RATIONALE:
 * - Allows gradual schema changes without downtime
 * - Maintains backward compatibility with old documents
 * - Automatically migrates documents on read/write
 * - Provides audit trail for schema changes
 */
abstract class VersionedMongoModel extends Model
{
    /**
     * MongoDB connection name
     */
    protected $connection = 'mongodb';

    /**
     * Current schema version for this model
     * Override in child classes
     */
    protected int $currentSchemaVersion = 1;

    /**
     * Schema version field name in documents
     */
    protected string $versionField = '_schema_version';

    /**
     * Enable automatic schema migration on read
     */
    protected bool $autoMigrate = true;

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically add schema version on create
        static::creating(function ($model) {
            if (!isset($model->{$model->versionField})) {
                $model->{$model->versionField} = $model->currentSchemaVersion;
            }
        });

        // Automatically migrate schema on retrieve
        static::retrieved(function ($model) {
            if ($model->autoMigrate) {
                $model->migrateSchema();
            }
        });

        // Update schema version on save
        static::saving(function ($model) {
            $model->{$model->versionField} = $model->currentSchemaVersion;
        });
    }

    /**
     * Get schema version of current document
     */
    public function getSchemaVersion(): int
    {
        return $this->{$this->versionField} ?? 1;
    }

    /**
     * Check if document needs schema migration
     */
    public function needsMigration(): bool
    {
        return $this->getSchemaVersion() < $this->currentSchemaVersion;
    }

    /**
     * Migrate document schema to current version
     * Child classes should override getSchemaTransformations()
     */
    public function migrateSchema(): bool
    {
        if (!$this->needsMigration()) {
            return false;
        }

        $originalVersion = $this->getSchemaVersion();
        $transformations = $this->getSchemaTransformations();

        try {
            $hasChanges = false;
            
            // Apply transformations sequentially from original version to current
            for ($version = $originalVersion + 1; $version <= $this->currentSchemaVersion; $version++) {
                if (isset($transformations[$version])) {
                    // Only log in debug mode to reduce overhead
                    if (config('app.debug')) {
                        Log::debug("Migrating schema from v{$originalVersion} to v{$version}", [
                            'model' => get_class($this),
                            'document_id' => $this->_id,
                        ]);
                    }

                    $transformations[$version]($this);
                    $hasChanges = true;
                }
            }

            // Only save if there were actual changes
            if ($hasChanges || $this->{$this->versionField} !== $this->currentSchemaVersion) {
                $this->{$this->versionField} = $this->currentSchemaVersion;
                $this->save();

                // Log completion only in debug mode
                if (config('app.debug')) {
                    Log::debug("Schema migration completed", [
                        'model' => get_class($this),
                        'from_version' => $originalVersion,
                        'to_version' => $this->currentSchemaVersion,
                    ]);
                }
                
                return true;
            }

            return false; // No changes needed

        } catch (\Exception $e) {
            Log::error("Schema migration failed", [
                'model' => get_class($this),
                'document_id' => $this->_id,
                'from_version' => $originalVersion,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Define schema transformations for each version
     * Override in child classes
     * 
     * Example:
     * [
     *     2 => function($model) {
     *         $model->new_field = $model->old_field ?? null;
     *         unset($model->old_field);
     *     },
     *     3 => function($model) {
     *         $model->restructured_data = [
     *             'value' => $model->data_value,
     *             'timestamp' => now()
     *         ];
     *     },
     * ]
     */
    protected function getSchemaTransformations(): array
    {
        return [];
    }

    /**
     * Get documents that need schema migration
     */
    public static function getDocumentsNeedingMigration(int $limit = 100): \Illuminate\Support\Collection
    {
        $model = new static;
        
        return static::where($model->versionField, '<', $model->currentSchemaVersion)
            ->orWhereNull($model->versionField)
            ->limit($limit)
            ->get();
    }

    /**
     * Batch migrate documents
     */
    public static function batchMigrateDocuments(int $batchSize = 100): array
    {
        $migrated = 0;
        $failed = 0;

        do {
            $documents = static::getDocumentsNeedingMigration($batchSize);
            
            foreach ($documents as $document) {
                if ($document->migrateSchema()) {
                    $migrated++;
                } else {
                    $failed++;
                }
            }
        } while ($documents->isNotEmpty());

        return [
            'migrated' => $migrated,
            'failed' => $failed,
        ];
    }

    /**
     * Add metadata to document
     */
    public function addMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    /**
     * Get metadata from document
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Create audit trail entry
     */
    protected function addAuditTrail(string $action, $oldValue = null, $newValue = null, int $userId = null): void
    {
        $auditTrail = $this->audit_trail ?? [];
        
        $auditTrail[] = [
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip() ?? null,
        ];

        $this->audit_trail = $auditTrail;
    }

    /**
     * Get latest audit entry
     */
    public function getLatestAudit(): ?array
    {
        $auditTrail = $this->audit_trail ?? [];
        return end($auditTrail) ?: null;
    }

    /**
     * Soft delete implementation for MongoDB
     */
    public function softDelete(): bool
    {
        $this->is_deleted = true;
        $this->deleted_at = now();
        $this->deleted_by = auth()->id();
        
        $this->addAuditTrail('soft_deleted', false, true);
        
        return $this->save();
    }

    /**
     * Restore soft deleted document
     */
    public function restore(): bool
    {
        $this->is_deleted = false;
        $this->deleted_at = null;
        
        $this->addAuditTrail('restored', true, false);
        
        return $this->save();
    }

    /**
     * Scope for non-deleted documents
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope for active documents (not deleted and within date range if applicable)
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
