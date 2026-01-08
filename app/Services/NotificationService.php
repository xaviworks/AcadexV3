<?php

namespace App\Services;

use App\Services\Notifications\ManagesNotifications;
use App\Services\Notifications\SendsCourseNotifications;
use App\Services\Notifications\SendsGERequestNotifications;
use App\Services\Notifications\SendsGradeNotifications;
use App\Services\Notifications\SendsInstructorNotifications;
use App\Services\Notifications\SendsSecurityNotifications;
use App\Services\Notifications\SendsSystemNotifications;

/**
 * Central service for managing notifications throughout the application.
 * 
 * This service uses traits to organize notification logic by domain:
 * - SendsGradeNotifications: Grade submission notifications
 * - SendsCourseNotifications: Course assignment/removal notifications
 * - SendsInstructorNotifications: Instructor account notifications
 * - SendsGERequestNotifications: GE teaching request notifications
 * - SendsSecurityNotifications: Security alert notifications
 * - SendsSystemNotifications: System-wide announcements
 * - ManagesNotifications: Utility methods for fetching/managing notifications
 * 
 * @see \App\Services\Notifications for individual trait implementations
 */
class NotificationService
{
    use SendsGradeNotifications;
    use SendsCourseNotifications;
    use SendsInstructorNotifications;
    use SendsGERequestNotifications;
    use SendsSecurityNotifications;
    use SendsSystemNotifications;
    use ManagesNotifications;
}
