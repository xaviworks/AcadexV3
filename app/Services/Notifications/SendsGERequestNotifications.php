<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\GESubjectRequest;
use App\Notifications\GERequestSubmitted;
use App\Notifications\GERequestApproved;
use App\Notifications\GERequestRejected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Trait for GE assignment request notifications.
 * Handles submitted, approved, and rejected GE request notifications.
 */
trait SendsGERequestNotifications
{
    /**
     * Notify GE Coordinator(s) when a new GE assignment request is submitted.
     * System notification only (no email).
     */
    public static function notifyGERequestSubmitted(GESubjectRequest $request): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            $geCoordinators = User::where('role', 4)->where('is_active', true)->get();

            if ($geCoordinators->isNotEmpty()) {
                Notification::send(
                    $geCoordinators,
                    new GERequestSubmitted($request, $instructor, $requestedBy)
                );
                
                Log::info('GE request submitted notification sent', [
                    'request_id' => $request->id,
                    'instructor_id' => $instructor->id,
                    'requested_by_id' => $requestedBy->id,
                    'coordinator_count' => $geCoordinators->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send GE request submitted notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    /**
     * Notify the requesting chairperson when a GE request is approved.
     * Email and system notification.
     */
    public static function notifyGERequestApproved(GESubjectRequest $request, ?User $approvedBy = null): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request approved notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            $requestedBy->notify(new GERequestApproved($request, $instructor, $requestedBy, $approvedBy));
            
            Log::info('GE request approved notification sent', [
                'request_id' => $request->id,
                'instructor_id' => $instructor->id,
                'requested_by_id' => $requestedBy->id,
                'approved_by_id' => $approvedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send GE request approved notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    /**
     * Notify the requesting chairperson when a GE request is rejected.
     * Email and system notification.
     */
    public static function notifyGERequestRejected(GESubjectRequest $request, ?User $rejectedBy = null): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request rejected notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            $requestedBy->notify(new GERequestRejected($request, $instructor, $requestedBy, $rejectedBy));
            
            Log::info('GE request rejected notification sent', [
                'request_id' => $request->id,
                'instructor_id' => $instructor->id,
                'requested_by_id' => $requestedBy->id,
                'rejected_by_id' => $rejectedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send GE request rejected notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }
}
