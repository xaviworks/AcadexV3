<?php

namespace App\Notifications;

use App\Models\User;

/**
 * Security notification for Admin users.
 * Includes events like: new user creation, password changes, login anomalies.
 * 
 * Admin view: Full technical security audit details
 * User view: Should not be sent to regular users (admin-only notification)
 */
class SecurityAlert extends BaseNotification
{
    public const TYPE_NEW_USER = 'new_user';
    public const TYPE_PASSWORD_CHANGED = 'password_changed';
    public const TYPE_ACCOUNT_ACTIVATED = 'account_activated';
    public const TYPE_ACCOUNT_DEACTIVATED = 'account_deactivated';
    public const TYPE_FAILED_LOGIN_ATTEMPTS = 'failed_login_attempts';
    public const TYPE_NEW_DEVICE_LOGIN = 'new_device_login';
    public const TYPE_2FA_ENABLED = '2fa_enabled';
    public const TYPE_2FA_DISABLED = '2fa_disabled';
    public const TYPE_FORCE_LOGOUT = 'force_logout';

    public function __construct(
        protected string $alertType,
        protected ?User $affectedUser = null,
        protected ?User $actorUser = null,
        protected array $metadata = []
    ) {}

    public function getCategory(): string
    {
        return 'security';
    }

    public function getPriority(): string
    {
        return match($this->alertType) {
            self::TYPE_FAILED_LOGIN_ATTEMPTS => 'high',
            self::TYPE_FORCE_LOGOUT => 'high',
            self::TYPE_2FA_DISABLED => 'high',
            default => 'normal',
        };
    }

    public function getIcon(): string
    {
        return match($this->alertType) {
            self::TYPE_NEW_USER => 'bi-person-plus-fill',
            self::TYPE_PASSWORD_CHANGED => 'bi-key-fill',
            self::TYPE_ACCOUNT_ACTIVATED => 'bi-person-check-fill',
            self::TYPE_ACCOUNT_DEACTIVATED => 'bi-person-x-fill',
            self::TYPE_FAILED_LOGIN_ATTEMPTS => 'bi-shield-exclamation',
            self::TYPE_NEW_DEVICE_LOGIN => 'bi-laptop',
            self::TYPE_2FA_ENABLED => 'bi-shield-lock-fill',
            self::TYPE_2FA_DISABLED => 'bi-shield-slash',
            self::TYPE_FORCE_LOGOUT => 'bi-box-arrow-right',
            default => 'bi-shield-fill',
        };
    }

    public function getColor(): string
    {
        return match($this->alertType) {
            self::TYPE_FAILED_LOGIN_ATTEMPTS => 'danger',
            self::TYPE_2FA_DISABLED => 'warning',
            self::TYPE_ACCOUNT_DEACTIVATED => 'warning',
            self::TYPE_FORCE_LOGOUT => 'warning',
            self::TYPE_2FA_ENABLED => 'success',
            self::TYPE_ACCOUNT_ACTIVATED => 'success',
            default => 'info',
        };
    }

    public function getAdminMessage(): string
    {
        $affectedName = $this->affectedUser 
            ? "{$this->affectedUser->full_name} (ID: {$this->affectedUser->id}, Email: {$this->affectedUser->email})"
            : 'Unknown user';
        
        $actorName = $this->actorUser
            ? "{$this->actorUser->full_name} (ID: {$this->actorUser->id})"
            : 'System';

        $roleLabel = $this->affectedUser ? match($this->affectedUser->role) {
            0 => 'Instructor',
            1 => 'Chairperson',
            2 => 'Dean',
            3 => 'Admin',
            4 => 'GE Coordinator',
            5 => 'VPAA',
            default => 'User',
        } : '';

        $ip = $this->metadata['ip_address'] ?? 'Unknown IP';
        $device = $this->metadata['device'] ?? 'Unknown device';
        $browser = $this->metadata['browser'] ?? 'Unknown browser';

        return match($this->alertType) {
            self::TYPE_NEW_USER => "[SECURITY] New {$roleLabel} account created: {$affectedName} by {$actorName}. IP: {$ip}",
            self::TYPE_PASSWORD_CHANGED => "[SECURITY] Password changed for {$affectedName}. IP: {$ip}, Device: {$device}",
            self::TYPE_ACCOUNT_ACTIVATED => "[SECURITY] Account activated: {$affectedName} by {$actorName}",
            self::TYPE_ACCOUNT_DEACTIVATED => "[SECURITY] Account deactivated: {$affectedName} by {$actorName}",
            self::TYPE_FAILED_LOGIN_ATTEMPTS => "[SECURITY ALERT] Multiple failed login attempts for {$affectedName}. IP: {$ip}, Attempts: " . ($this->metadata['attempts'] ?? 'N/A'),
            self::TYPE_NEW_DEVICE_LOGIN => "[SECURITY] New device login for {$affectedName}. Device: {$device}, Browser: {$browser}, IP: {$ip}",
            self::TYPE_2FA_ENABLED => "[SECURITY] 2FA enabled for {$affectedName}. IP: {$ip}",
            self::TYPE_2FA_DISABLED => "[SECURITY WARNING] 2FA disabled for {$affectedName}. IP: {$ip}",
            self::TYPE_FORCE_LOGOUT => "[SECURITY] Force logout executed for {$affectedName} by {$actorName}",
            default => "[SECURITY] Security event for {$affectedName}",
        };
    }

    public function getUserMessage(): string
    {
        // Security alerts are primarily for admins, but provide a simplified version
        $affectedName = $this->affectedUser?->full_name ?? 'A user';

        return match($this->alertType) {
            self::TYPE_NEW_USER => "New user account created: {$affectedName}",
            self::TYPE_PASSWORD_CHANGED => "{$affectedName} changed their password",
            self::TYPE_ACCOUNT_ACTIVATED => "{$affectedName}'s account was activated",
            self::TYPE_ACCOUNT_DEACTIVATED => "{$affectedName}'s account was deactivated",
            self::TYPE_FAILED_LOGIN_ATTEMPTS => "Security alert: Multiple failed login attempts detected",
            self::TYPE_NEW_DEVICE_LOGIN => "{$affectedName} logged in from a new device",
            self::TYPE_2FA_ENABLED => "{$affectedName} enabled two-factor authentication",
            self::TYPE_2FA_DISABLED => "{$affectedName} disabled two-factor authentication",
            self::TYPE_FORCE_LOGOUT => "{$affectedName} was logged out from all devices",
            default => "Security event occurred",
        };
    }

    public function getActionUrl(): ?string
    {
        if ($this->affectedUser && in_array($this->alertType, [
            self::TYPE_NEW_USER,
            self::TYPE_ACCOUNT_ACTIVATED,
            self::TYPE_ACCOUNT_DEACTIVATED,
        ])) {
            return route('admin.users');
        }

        return null;
    }

    public function getActionText(): ?string
    {
        if ($this->getActionUrl()) {
            return 'View Users';
        }
        return null;
    }

    public function getExtraData(): array
    {
        return [
            'alert_type' => $this->alertType,
            'affected_user_id' => $this->affectedUser?->id,
            'affected_user_email' => $this->affectedUser?->email,
            'affected_user_role' => $this->affectedUser?->role,
            'actor_id' => $this->actorUser?->id,
            'metadata' => $this->metadata,
        ];
    }
}
