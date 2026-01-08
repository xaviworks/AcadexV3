# Notification System Documentation

> **Version:** 1.0.0  
> **Last Updated:** January 6, 2026  
> **Authors:** Development Team

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [User Roles & Notification Types](#user-roles--notification-types)
4. [Database Schema](#database-schema)
5. [Notification Classes](#notification-classes)
6. [NotificationService API](#notificationservice-api)
7. [User Interface](#user-interface)
8. [Integration Guide](#integration-guide)
9. [Configuration](#configuration)
10. [Troubleshooting](#troubleshooting)

---

## Overview

The Acadex Notification System provides real-time, role-aware notifications to keep users informed about important events within the academic management platform. The system is designed with two core principles:

- **Admin Transparency:** Administrators receive detailed, technical audit-level information including user IDs, timestamps, IP addresses, and action metadata for security and compliance purposes.

- **End-User Clarity:** Regular users (instructors, chairpersons, coordinators) receive friendly, actionable messages focused on what matters to them without technical clutter.

### Key Features

| Feature | Description |
|---------|-------------|
| **Role-Aware Messaging** | Different message formats based on user role |
| **Infinite Scroll** | Facebook-style pagination for browsing notification history |
| **Category Filtering** | Filter notifications by type (academic, security, system) |
| **Priority Indicators** | Visual indicators for urgent, high, normal, and low priority |
| **Action Buttons** | Direct links to relevant pages from notifications |
| **Real-Time Ready** | Broadcasting infrastructure for live updates (requires configuration) |

---

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────────┐
│                      Controllers                                 │
│  (ChairpersonController, AdminController, GradeController, etc.)│
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                   NotificationService                            │
│  Central factory for creating and sending notifications          │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Notification Classes                            │
│  BaseNotification → GradeSubmitted, SubjectAssigned, etc.       │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                   Laravel Notification                           │
│  Database Channel (+ optional Broadcast Channel)                 │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    notifications table                           │
│  Polymorphic storage for all notification types                  │
└─────────────────────────────────────────────────────────────────┘
```

### Design Decisions

1. **Laravel Native Notifications:** We leverage Laravel's built-in notification system with polymorphic `notifications` table rather than a custom implementation, ensuring compatibility with Laravel ecosystem tools.

2. **Synchronous Processing:** Notifications are processed synchronously (no queue) for immediate delivery. This can be changed by implementing `ShouldQueue` in `BaseNotification` if queue workers are available.

3. **Abstract Base Class:** All notifications extend `BaseNotification`, enforcing consistent structure and enabling role-aware message formatting.

4. **Cursor-Based Pagination:** Instead of offset pagination, we use cursor-based pagination (timestamp-based) for efficient infinite scrolling with large datasets.

---

## User Roles & Notification Types

*GE Coordinator receives grade notifications for GE subjects only.

### Message Format Examples

#### Subject Assignment Notification

**Admin View:**
```
[Subject Assignment] John Doe (ID: 15, Role: 1) assigned subject 
CS101 - Introduction to Programming (ID: 42) for 1st Semester 2025-2026
```

**Instructor View:**
```
You've been assigned to teach CS101 (Introduction to Programming)
```

#### Security Alert (New User)

**Admin View:**
```
[Security] New user account created: jane.smith@acadex.edu (ID: 156, 
Role: Instructor). Created by: admin@acadex.edu (ID: 1) from IP: 
192.168.1.100 at 2026-01-06 14:30:00
```

---

## Database Schema

### notifications Table

Laravel's standard polymorphic notifications table:

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX notifications_notifiable_type_notifiable_id_index 
        (notifiable_type, notifiable_id)
);
```

### notification_preferences Table

User-specific notification settings:

```sql
CREATE TABLE notification_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    email_enabled BOOLEAN DEFAULT FALSE,
    push_enabled BOOLEAN DEFAULT TRUE,
    academic_notifications BOOLEAN DEFAULT TRUE,
    security_notifications BOOLEAN DEFAULT TRUE,
    system_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE INDEX notification_preferences_user_id_unique (user_id)
);
```

### Data Structure (JSON Column)

Each notification stores structured data:

```json
{
    "type": "SubjectAssigned",
    "category": "academic",
    "priority": "normal",
    "icon": "bi-book",
    "color": "primary",
    "admin_message": "[Subject Assignment] John Doe (ID: 15)...",
    "user_message": "You've been assigned to teach CS101...",
    "action_url": "http://localhost/instructor/dashboard",
    "action_text": "View Dashboard",
    "actor_id": 15,
    "actor_name": "John Doe",
    "subject_id": 42,
    "subject_code": "CS101",
    "academic_period": "1st Semester 2025-2026"
}
```

---

## Notification Classes

### Class Hierarchy

```
BaseNotification (abstract)
├── GradeSubmitted
├── SubjectAssigned
├── InstructorAnnouncement
├── AdminAnnouncement
├── SecurityAlert
└── SystemNotification
```

### BaseNotification

Location: `app/Notifications/BaseNotification.php`

Abstract base class that all notifications must extend. Provides:

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getCategory()` | `string` | Category: academic, security, system |
| `getPriority()` | `string` | Priority: low, normal, high, urgent |
| `getIcon()` | `string` | Bootstrap icon class |
| `getColor()` | `string` | Color theme: primary, success, warning, danger, info |
| `getAdminMessage()` | `string` | Detailed message for admins |
| `getUserMessage()` | `string` | Friendly message for end users |
| `getActionUrl()` | `?string` | Optional link URL |
| `getActionText()` | `?string` | Optional button text |
| `getExtraData()` | `array` | Additional metadata |

### GradeSubmitted

Location: `app/Notifications/GradeSubmitted.php`

Sent to chairpersons/coordinators when an instructor submits grades.

```php
new GradeSubmitted(
    User $instructor,      // Who submitted
    Subject $subject,      // Which subject
    string $term,          // Prelim, Midterm, Finals
    int $studentsGraded    // How many students
);
```

### SubjectAssigned

Location: `app/Notifications/SubjectAssigned.php`

Sent to instructors when assigned a new subject to teach.

```php
new SubjectAssigned(
    User $assignedBy,           // Who made the assignment
    Subject $subject,           // The subject assigned
    ?string $academicPeriod     // e.g., "1st Semester 2025-2026"
);
```

### SecurityAlert

Location: `app/Notifications/SecurityAlert.php`

Sent to admin users for security-related events.

**Alert Types:**
- `SecurityAlert::TYPE_NEW_USER` — New user account created
- `SecurityAlert::TYPE_PASSWORD_CHANGED` — User changed their password
- `SecurityAlert::TYPE_ACCOUNT_ACTIVATED` — Account activated
- `SecurityAlert::TYPE_ACCOUNT_DEACTIVATED` — Account deactivated
- `SecurityAlert::TYPE_FAILED_LOGIN_ATTEMPTS` — Multiple failed logins
- `SecurityAlert::TYPE_2FA_ENABLED` — Two-factor authentication enabled
- `SecurityAlert::TYPE_2FA_DISABLED` — Two-factor authentication disabled

```php
new SecurityAlert(
    string $alertType,      // One of the TYPE_* constants
    ?User $affectedUser,    // User the event relates to
    ?User $actorUser,       // User who performed the action
    array $metadata         // Additional context (IP, user agent, etc.)
);
```

### InstructorAnnouncement

Location: `app/Notifications/InstructorAnnouncement.php`

For sending announcements from chairpersons/deans to instructors.

```php
new InstructorAnnouncement(
    User $sender,
    string $title,
    string $message,
    string $priority,       // low, normal, high, urgent
    ?string $actionUrl,
    ?string $actionText
);
```

### AdminAnnouncement

Location: `app/Notifications/AdminAnnouncement.php`

For sending targeted announcements from administrators. Supports flexible targeting options.

**Target Types:**
- `AdminAnnouncement::TARGET_SPECIFIC_USER` — Send to a single user
- `AdminAnnouncement::TARGET_DEPARTMENT` — Send to all users in a department
- `AdminAnnouncement::TARGET_PROGRAM` — Send to all users in a program/course
- `AdminAnnouncement::TARGET_ROLE` — Send to all users with a specific role

```php
new AdminAnnouncement(
    User $sender,           // Admin sending the announcement
    string $title,          // Announcement title
    string $message,        // Full message content
    string $targetType,     // One of the TARGET_* constants
    ?string $targetName,    // Name of target (dept name, role name, etc.)
    string $priority,       // low, normal, high, urgent
    ?string $actionUrl,     // Optional action link
    ?string $actionText,    // Optional button text
    array $metadata         // Additional data (recipient_count, sent_at)
);
```

**Example Messages:**

*Admin View:*
```
[Admin Announcement - high] From John Admin (Admin, ID: 1) to Department 
(College of Computer Studies) (15 recipients): System Update - Please update 
your profile information by end of week.
```

*User View:*
```
📢 Admin Announcement: System Update
```

### SystemNotification

Location: `app/Notifications/SystemNotification.php`

Generic system-wide notifications for maintenance, updates, etc.

```php
new SystemNotification(
    string $type,           // info, warning, success, error
    string $title,
    string $message,
    ?string $actionUrl,
    ?string $actionText
);
```

---

## NotificationService API

Location: `app/Services/NotificationService.php`

The `NotificationService` is the recommended way to send notifications. It handles recipient resolution, duplicate prevention, and consistent formatting.

### Sending Notifications

#### Grade Submission

```php
use App\Services\NotificationService;

// In your controller after saving grades
NotificationService::notifyGradeSubmitted(
    $subject,           // Subject model
    'Midterm',          // Term name
    25                  // Number of students graded
);
// Automatically notifies the subject's chairperson and/or GE coordinator
```

#### Subject Assignment

```php
NotificationService::notifySubjectAssigned(
    $instructor,        // User model (the instructor)
    $subject,           // Subject model
    '1st Semester 2025-2026'  // Optional academic period
);
// Notifies the instructor about their new assignment
```

#### Security Alerts

```php
use App\Notifications\SecurityAlert;

NotificationService::notifySecurityAlert(
    SecurityAlert::TYPE_NEW_USER,
    $newUser,           // The newly created user
    Auth::user(),       // Who created them
    ['ip_address' => request()->ip()]
);
// Automatically notifies all admin users
```

#### Instructor Announcements

```php
$instructors = User::where('role', 0)
    ->where('department_id', $departmentId)
    ->get();

NotificationService::sendInstructorAnnouncement(
    $instructors,
    'Grade Submission Deadline',
    'Please submit all grades by January 15, 2026.',
    'high',             // Priority
    route('instructor.grades.index'),
    'Submit Grades'
);
```

#### System Notifications

```php
NotificationService::sendSystemNotification(
    'maintenance',      // Type
    'Scheduled Maintenance',
    'The system will be down for maintenance on Sunday.',
    User::all(),        // Recipients (or specific users)
    route('dashboard'),
    'Learn More'
);
```

### Querying Notifications

#### Get Paginated Notifications (Cursor-Based)

```php
$result = NotificationService::getNotifications(
    $user,              // User model
    20,                 // Per page
    $cursorTimestamp,   // null for first page, timestamp for subsequent
    'academic'          // Optional category filter
);

// Returns:
[
    'notifications' => [...],
    'next_cursor' => '2026-01-05T10:30:00.000000Z',
    'has_more' => true
]
```

#### Format for API Response

```php
$formatted = NotificationService::formatForResponse($notification, $user);

// Returns role-appropriate data structure
```

---

## User Interface

### Notification Bell (Navigation)

Located in `resources/views/layouts/navigation.blade.php`

The notification bell appears in the main navigation for all authenticated users. It displays:

- Unread count badge (red indicator)
- Dropdown with recent notifications
- Quick actions (mark as read, view all)

### Notifications Page

Located at `/notifications` (route: `notifications.index`)

Features:

1. **Category Tabs:** Filter by All, Academic, Security, System
2. **Infinite Scroll:** Automatically loads older notifications when scrolling
3. **Priority Indicators:** Visual badges for urgent/high priority items
4. **Expandable Details:** Admin users can expand notifications for technical details
5. **Bulk Actions:** Mark all as read, clear all read notifications
6. **Individual Actions:** Mark as read, delete, follow action link

### Admin Announcement Page

Located at `/admin/announcements/create` (route: `admin.announcements.create`)

Accessible via: **Admin Sidebar → System Monitoring → Announcements**

This dedicated interface allows administrators to send targeted announcements to specific audiences.

#### Features

1. **Message Composition**
   - Title field (max 255 characters) with live character count
   - Message body (max 2000 characters) with live character count
   - Visual warnings when approaching limits

2. **Priority Selection**
   - **Low** (🔵) — General information, no urgency
   - **Normal** (🟢) — Standard announcements
   - **High** (🟡) — Important, requires attention
   - **Urgent** (🔴) — Critical, immediate action needed

3. **Target Audience Options**
   - **Specific User** — Search and select a single user by name or email
   - **Department** — All active users in a department
   - **Program** — All active users in a program/course
   - **Role** — All active users with a specific role (Instructor, Chairperson, etc.)

4. **Recipient Preview**
   - Shows count of recipients before sending
   - Preview of first 5 recipients with names and emails
   - Excludes the sender from recipient list

5. **Optional Action Button**
   - Add a URL link to the notification
   - Custom button text (e.g., "View Details", "Take Action")

#### Usage Example

1. Navigate to **Admin → Announcements**
2. Enter announcement title and message
3. Select priority level
4. Choose target type (e.g., "Department")
5. Select specific target (e.g., "College of Computer Studies")
6. Review recipient preview
7. Optionally add action URL/button
8. Click **Send Announcement**

### CSS & JavaScript

- **Styles:** Inlined in view via `@push('styles')` block
- **JavaScript:** `resources/js/pages/notifications/index.js` (Alpine.js component)
- **Dependencies:** `@alpinejs/intersect` plugin for infinite scroll

---

## Integration Guide

### Adding Notifications to a New Feature

1. **Identify the event** that should trigger notifications
2. **Determine recipients** based on roles and context
3. **Choose or create** the appropriate notification class
4. **Add the service call** in your controller

#### Example: Notifying When an Assignment is Graded

```php
// In your controller method
public function submitGrade(Request $request, Assignment $assignment)
{
    // Your existing grade submission logic
    $assignment->update(['grade' => $request->grade]);
    
    // Send notification
    NotificationService::notifyGradeSubmitted(
        $assignment->subject,
        $assignment->term,
        1 // One student graded
    );
    
    return redirect()->back()->with('success', 'Grade submitted.');
}
```

### Creating a Custom Notification

1. Create a new class extending `BaseNotification`:

```php
<?php

namespace App\Notifications;

use App\Models\User;

class CustomEvent extends BaseNotification
{
    public function __construct(
        protected User $actor,
        protected string $details
    ) {}

    public function getCategory(): string
    {
        return 'academic'; // or 'security', 'system'
    }

    public function getIcon(): string
    {
        return 'bi-star'; // Bootstrap icon
    }

    public function getColor(): string
    {
        return 'info'; // primary, success, warning, danger, info
    }

    public function getAdminMessage(): string
    {
        return "[Custom Event] User {$this->actor->email} (ID: {$this->actor->id}) performed action: {$this->details}";
    }

    public function getUserMessage(): string
    {
        return "A custom event occurred: {$this->details}";
    }

    public function getExtraData(): array
    {
        return [
            'actor_id' => $this->actor->id,
            'details' => $this->details,
        ];
    }
}
```

2. Send it:

```php
$user->notify(new CustomEvent(Auth::user(), 'Something happened'));
```

---

## Configuration

### Environment Variables

```env
# Queue Configuration (affects notification delivery)
QUEUE_CONNECTION=sync    # Use 'sync' for immediate delivery
                         # Use 'database' or 'redis' with queue worker

# Broadcasting (for real-time notifications)
BROADCAST_CONNECTION=log # Change to 'pusher' or 'reverb' for live updates
```

### Enabling Queued Notifications

To process notifications via queue (recommended for production):

1. Update `BaseNotification.php`:

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;
    // ...
}
```

2. Ensure queue worker is running:

```bash
php artisan queue:work
```

### Enabling Real-Time Notifications

1. Configure broadcasting in `.env`
2. Set up Laravel Echo in your JavaScript
3. The `broadcast` channel is already included in `BaseNotification::via()`

---

## Troubleshooting

### Notifications Not Appearing

| Symptom | Possible Cause | Solution |
|---------|---------------|----------|
| No notifications created | Queue not running | Set `QUEUE_CONNECTION=sync` or run `php artisan queue:work` |
| Notification not showing for user | Wrong recipient | Check `NotificationService` logic for recipient resolution |
| Missing unread badge | JavaScript error | Check browser console for errors |
| Infinite scroll not working | Missing Alpine Intersect | Run `npm install @alpinejs/intersect` |

### Database Issues

```bash
# Clear notification data (development only)
php artisan tinker
>>> \DB::table('notifications')->truncate();

# Verify table exists
php artisan migrate:status
```

### Common Errors

**"Route [instructor.subjects] not defined"**
- The notification has an invalid action URL
- Fix: Update the `getActionUrl()` method to use a valid route

**"Class BaseNotification not found"**
- Autoloader needs refresh
- Fix: Run `composer dump-autoload`

---

## API Reference

### Routes

#### User Notification Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/notifications` | `notifications.index` | Notifications page |
| GET | `/notifications/paginate` | `notifications.paginate` | AJAX infinite scroll |
| GET | `/notifications/unread` | `notifications.unread` | Get unread for bell |
| POST | `/notifications/{id}/read` | `notifications.markAsRead` | Mark one as read |
| POST | `/notifications/read-all` | `notifications.markAllAsRead` | Mark all as read |
| DELETE | `/notifications/{id}` | `notifications.destroy` | Delete notification |
| GET | `/notifications/preferences` | `notifications.preferences` | Get user preferences |
| POST | `/notifications/preferences` | `notifications.preferences.update` | Update preferences |

#### Admin Announcement Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/admin/announcements/create` | `admin.announcements.create` | Announcement form |
| POST | `/admin/announcements/store` | `admin.announcements.store` | Send announcement |
| POST | `/admin/announcements/preview` | `admin.announcements.preview` | AJAX recipient preview |

### AJAX Response Formats

**Paginate Response:**
```json
{
    "notifications": [
        {
            "id": "uuid-here",
            "type": "SubjectAssigned",
            "category": "academic",
            "priority": "normal",
            "icon": "bi-book",
            "color": "primary",
            "message": "You've been assigned to teach CS101...",
            "action_url": "/instructor/dashboard",
            "action_text": "View Dashboard",
            "is_read": false,
            "created_at": "2026-01-06T14:30:00.000000Z",
            "time_ago": "2 hours ago",
            "extra": { ... }
        }
    ],
    "next_cursor": "2026-01-05T10:30:00.000000Z",
    "has_more": true
}
```

**Unread Response:**
```json
{
    "count": 5,
    "notifications": [ ... ]
}
```

---

## Changelog

### Version 1.1.0 (January 6, 2026)

- **New Feature:** Admin Announcement System
  - Administrators can now send targeted announcements
  - Target options: Specific User, Department, Program, Role
  - Live recipient preview before sending
  - Priority levels with visual indicators
  - Optional action buttons with custom URLs
  - New sidebar link under System Monitoring

- **New Files:**
  - `app/Notifications/AdminAnnouncement.php`
  - `app/Http/Controllers/Admin/AnnouncementController.php`
  - `resources/views/admin/announcements/create.blade.php`

- **New Routes:**
  - `GET /admin/announcements/create`
  - `POST /admin/announcements/store`
  - `POST /admin/announcements/preview`

### Version 1.0.0 (January 6, 2026)

- Initial implementation of notification system
- Laravel native notifications with polymorphic storage
- Role-aware messaging (admin vs. end-user)
- Infinite scroll UI with cursor-based pagination
- Security audit notifications for admin users
- Subject assignment and grade submission notifications
- User notification preferences

---

## Support

For questions or issues related to the notification system, please:

1. Check this documentation first
2. Review the troubleshooting section
3. Contact the development team

---

*This documentation is maintained by the Acadex Development Team.*
