<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string|null $attachment_path
 * @property string|null $attachment_name
 * @property array $visible_roles
 * @property int $sort_order
 * @property bool $is_active
 * @property int $created_by
 * @property int|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $creator
 * @property-read User|null $updater
 */
class HelpGuide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'attachment_path',
        'attachment_name',
        'visible_roles',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visible_roles' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Role constants for reference
     */
    public const ROLE_INSTRUCTOR = 0;
    public const ROLE_CHAIRPERSON = 1;
    public const ROLE_DEAN = 2;
    public const ROLE_ADMIN = 3;
    public const ROLE_GE_COORDINATOR = 4;
    public const ROLE_VPAA = 5;

    /**
     * Get all available roles with their labels
     */
    public static function availableRoles(): array
    {
        return [
            self::ROLE_INSTRUCTOR => 'Instructor',
            self::ROLE_CHAIRPERSON => 'Chairperson',
            self::ROLE_DEAN => 'Dean',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_GE_COORDINATOR => 'GE Coordinator',
            self::ROLE_VPAA => 'VPAA',
        ];
    }

    /**
     * Get the user who created this guide
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this guide
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all attachments for this guide
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(HelpGuideAttachment::class)->ordered();
    }

    /**
     * Scope to get only active guides
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get guides visible to a specific role
     */
    public function scopeVisibleToRole($query, int $role)
    {
        return $query->whereJsonContains('visible_roles', $role);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Check if this guide is visible to a given role
     */
    public function isVisibleToRole(int $role): bool
    {
        return in_array($role, $this->visible_roles ?? []);
    }

    /**
     * Get the roles that can view this guide as labels
     */
    public function getVisibleRoleLabelsAttribute(): array
    {
        $roles = self::availableRoles();
        $labels = [];
        
        foreach ($this->visible_roles ?? [] as $roleId) {
            if (isset($roles[$roleId])) {
                $labels[] = $roles[$roleId];
            }
        }
        
        return $labels;
    }

    /**
     * Check if the guide has an attachment (legacy single or new multiple)
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path) || $this->attachments()->exists();
    }

    /**
     * Check if the guide has any attachments (new multiple attachments)
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Get all PDF attachments
     */
    public function getPdfAttachments()
    {
        return $this->attachments()->get()->filter(fn ($a) => $a->isPdf());
    }

    /**
     * Get the attachment URL
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    /**
     * Delete the attachment file
     */
    public function deleteAttachment(): bool
    {
        if ($this->hasAttachment() && Storage::disk('public')->exists($this->attachment_path)) {
            Storage::disk('public')->delete($this->attachment_path);
            $this->update([
                'attachment_path' => null,
                'attachment_name' => null,
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Get file extension from attachment
     */
    public function getAttachmentExtensionAttribute(): ?string
    {
        if (!$this->attachment_name) {
            return null;
        }
        
        return strtolower(pathinfo($this->attachment_name, PATHINFO_EXTENSION));
    }

    /**
     * Check if attachment is an image
     */
    public function attachmentIsImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array($this->attachment_extension, $imageExtensions);
    }

    /**
     * Check if attachment is a PDF
     */
    public function attachmentIsPdf(): bool
    {
        return $this->attachment_extension === 'pdf';
    }
}
