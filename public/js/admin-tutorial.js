/**
 * Admin Tutorial System
 * Provides contextual step-based guided tours for admin pages
 * 
 * Features:
 * - Step-based guided tours with spotlight highlighting
 * - Auto-advance on correct actions
 * - First-visit detection
 * - Accessible via header button
 */

(function() {
    'use strict';

    // Tutorial Manager
    window.AdminTutorial = {
        currentTutorial: null,
        currentStep: 0,
        overlay: null,
        spotlight: null,
        tooltip: null,
        isActive: false,
        
        // Storage key prefix for tracking completed tutorials
        STORAGE_PREFIX: 'acadex_admin_tutorial_',
        
        /**
         * Tutorial definitions for each admin page
         * Each tutorial teaches ALL available functionalities on the page
         */
        tutorials: {
            // Dashboard tutorial - COMPREHENSIVE
            'admin-dashboard': {
                title: 'Admin Dashboard Overview',
                description: 'Learn how to monitor system activity, user statistics, and login patterns',
                steps: [
                    {
                        target: '.hover-lift:first-child',
                        title: 'Total Users Card',
                        content: 'This card shows the total number of registered users in the system. The number updates in real-time as users are added or removed.',
                        position: 'bottom'
                    },
                    {
                        target: '.hover-lift:nth-child(2)',
                        title: 'Successful Logins Today',
                        content: 'Monitor how many successful logins occurred today. This helps you understand daily system usage and peak activity periods.',
                        position: 'bottom'
                    },
                    {
                        target: '.hover-lift:nth-child(3)',
                        title: 'Failed Login Attempts',
                        content: 'Track failed login attempts today. High numbers may indicate brute-force attacks, forgotten passwords, or security concerns that need attention.',
                        position: 'bottom'
                    },
                    {
                        target: '.table-responsive',
                        title: 'Hourly Login Activity Table',
                        content: 'This table shows login activity broken down by hour. Highlighted rows indicate peak usage hours. Use this to plan maintenance windows during low-activity periods.',
                        position: 'top'
                    },
                    {
                        target: 'select[name="year"]',
                        title: 'Year Filter',
                        content: 'Use this dropdown to view monthly login statistics for different years. This helps track long-term usage trends and compare activity across academic years.',
                        position: 'left'
                    },
                    {
                        target: 'canvas, .chart-container',
                        title: 'Login Trends Chart',
                        content: 'The chart visualizes login patterns over time. Hover over data points to see exact numbers for each month.',
                        position: 'top',
                        optional: true
                    }
                ]
            },
            
            // Users management tutorial - COMPREHENSIVE
            'admin-users': {
                title: 'User Management',
                description: 'Learn how to add users, manage roles, disable accounts, reset 2FA, and force logout',
                steps: [
                    {
                        target: 'button.btn-success',
                        title: 'Add New User',
                        content: 'Click this button to add a new system user. You can create Instructors, Chairpersons, Deans, Admins, GE Coordinators, or VPAA accounts. Each role has different permissions.',
                        position: 'left'
                    },
                    {
                        target: '#usersTable thead',
                        title: 'Users Table Overview',
                        content: 'The table displays all users with: Name, Email, Role (color-coded badge), Account Status (Active/Disabled), 2FA Status (enabled/disabled), and available Actions.',
                        position: 'bottom'
                    },
                    {
                        target: '#usersTable_filter input, .dataTables_filter input',
                        title: 'Search Users',
                        content: 'Use the search box to quickly find users by name, email, or any visible field. Results filter in real-time as you type.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.role-badge, .badge',
                        title: 'User Roles',
                        content: 'Each user has a role badge: Instructor (blue), Chairperson (teal), Dean (purple), Admin (red), GE Coordinator (orange), or VPAA (indigo). Roles determine system permissions.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.action-btn.btn-disable, button[onclick*="disable"]',
                        title: 'Disable Account',
                        content: 'Click the ban icon to temporarily disable a user account. Choose duration: 1 Week, 1 Month, Indefinite, or Custom date/time. The user will be logged out immediately.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.action-btn.btn-enable, button[onclick*="enable"]',
                        title: 'Enable Account',
                        content: 'For disabled accounts, click the checkmark to re-enable access. The user can log in again immediately after enabling.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.action-btn.btn-reset-2fa, button[onclick*="Reset2FA"]',
                        title: 'Reset Two-Factor Authentication',
                        content: 'If a user loses access to their authenticator app, click the shield icon to reset their 2FA. They will need to set up 2FA again on next login.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.your-session-badge',
                        title: 'Your Account',
                        content: 'Your own account is marked with "You" badge. You cannot disable or modify your own account from this interface for security reasons.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Sessions & Activity tutorial - COMPREHENSIVE
            'admin-sessions': {
                title: 'Session & Activity Monitor',
                description: 'Learn how to monitor active sessions, view logs, revoke sessions, and reset 2FA',
                steps: [
                    {
                        target: 'button.btn-danger.btn-sm',
                        title: 'Revoke All Sessions (Emergency)',
                        content: 'CAUTION: This emergency button terminates ALL active sessions except your own. Use only for security incidents like suspected breaches. Requires password confirmation.',
                        position: 'left'
                    },
                    {
                        target: '#sessions-tab',
                        title: 'Active Sessions Tab',
                        content: 'This tab shows all currently logged-in users with their session details: device type, browser, platform, IP address, and last activity timestamp.',
                        position: 'bottom'
                    },
                    {
                        target: '#logs-tab',
                        title: 'User Logs Tab',
                        content: 'Switch here to view the authentication history log. See login attempts, logouts, failed logins, and session revocations with timestamps.',
                        position: 'bottom'
                    },
                    {
                        target: '.session-status-badge, .session-status-current',
                        title: 'Session Status',
                        content: 'Sessions are marked as: Current (your session, highlighted), Active (online users), or Expired. Current sessions cannot be revoked.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.device-icon-wrapper, .device-icon',
                        title: 'Device Information',
                        content: 'See what device each user is on: Desktop, Tablet, or Mobile. This helps identify suspicious logins from unexpected devices.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: 'code.text-muted, .device-fingerprint',
                        title: 'Device Fingerprint',
                        content: 'Each device has a unique fingerprint for security tracking. Hover to see the full fingerprint. Mismatched fingerprints may indicate session hijacking.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.action-btn.btn-revoke, button[onclick*="confirmRevoke"]',
                        title: 'Revoke Individual Session',
                        content: 'Click the user-slash icon to force logout a specific user session. Requires password confirmation. The user will need to log in again.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '#logs-pane form, form[action*="logs"]',
                        title: 'Filter Logs by Date',
                        content: 'In the User Logs tab, use the date filter to view authentication events for a specific time period. Helpful for security audits.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.badge[class*="login"], .badge[class*="logout"]',
                        title: 'Event Types',
                        content: 'Log entries show event types: login (successful), logout (user logout), failed_login (wrong password), session_revoked (admin action), all_sessions_revoked, bulk_sessions_revoked.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Disaster Recovery tutorial - COMPREHENSIVE (including Auto Backup)
            'admin-disaster-recovery': {
                title: 'Disaster Recovery & Backup Management',
                description: 'Learn how to create backups, configure automatic backups, restore data, and manage backup storage',
                steps: [
                    {
                        target: 'button[data-bs-target="#backupModal"], button.btn-success',
                        title: 'Create Manual Backup',
                        content: 'Click here to create a new backup immediately. Choose between Full Backup (all database tables and data) or Config Only (settings and configurations only).',
                        position: 'left'
                    },
                    {
                        target: 'a[href*="activity"]',
                        title: 'Activity Log',
                        content: 'View the complete backup history: when backups were created, who created them, downloads, restores, and deletions. Essential for audit trails.',
                        position: 'left'
                    },
                    {
                        target: '.col-xl-6:first-child .card',
                        title: 'Storage Usage Monitor',
                        content: 'Monitor your backup storage capacity. The progress bar shows how much space is used. Green is healthy, yellow is moderate, red means action needed.',
                        position: 'right'
                    },
                    {
                        target: '.col-md-6.col-xl-3:first-of-type .card',
                        title: 'Total Backups Count',
                        content: 'See how many backup files are stored on the server. Regular backups ensure data safety.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.col-md-6.col-xl-3:last-of-type .card',
                        title: 'Last Backup Time',
                        content: 'Shows when the most recent backup was created. If this is too old, consider creating a new backup or enabling automatic backups.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: 'select[name="frequency"]',
                        title: 'Automatic Backup Frequency',
                        content: 'Configure automatic backups: Disabled (manual only), Daily (every day), Weekly (every week), or Monthly (once per month). Automated backups ensure you never lose data.',
                        position: 'right'
                    },
                    {
                        target: 'input[name="time"], input[type="time"]',
                        title: 'Backup Schedule Time',
                        content: 'Set the time when automatic backups should run. Choose a low-activity period (e.g., early morning) to minimize system impact.',
                        position: 'right'
                    },
                    {
                        target: 'button[type="submit"][form*="schedule"], form[action*="schedule"] button',
                        title: 'Save Backup Schedule',
                        content: 'Click "Save Schedule" to apply your automatic backup settings. Changes take effect immediately.',
                        position: 'right',
                        optional: true
                    },
                    {
                        target: '#runManualBackupForm button, button[onclick*="confirmRunNow"]',
                        title: 'Run Manual Backup Now',
                        content: 'Trigger an immediate backup regardless of the schedule. Useful before major changes or updates.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.table-responsive table',
                        title: 'Backups List',
                        content: 'All backups are listed with: Type (Full/Config), Date & Time, Size, Number of Tables, and Creator. Newest backups appear first.',
                        position: 'top'
                    },
                    {
                        target: '.btn-outline-secondary[href*="download"], a[title="Download"]',
                        title: 'Download Backup',
                        content: 'Click the download icon to save a backup file to your computer. Store offline copies for extra protection against server failures.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.btn-outline-warning, button[onclick*="showRestoreModal"]',
                        title: 'Restore Backup',
                        content: 'Click the restore icon to replace current database with this backup. WARNING: This overwrites all current data! You can optionally create a safety backup first.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.btn-outline-danger, button[onclick*="showDeleteModal"]',
                        title: 'Delete Backup',
                        content: 'Remove a backup file permanently. Requires password confirmation. Deleted backups cannot be recovered.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.list-group, .recent-activity',
                        title: 'Recent Activity Panel',
                        content: 'Quick view of recent backup activities. Click "View All" to see the complete activity log with more details.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Disaster Recovery Activity Log tutorial - COMPREHENSIVE
            'admin-disaster-recovery-activity': {
                title: 'Backup Activity Log',
                description: 'Learn how to track all backup operations, filter by event type, and rollback changes',
                steps: [
                    {
                        target: 'select[name="event_type"], form select:first-of-type',
                        title: 'Filter by Event Type',
                        content: 'Filter the activity log by event type: All Events, Created (new backups), Restored (backup restorations), Downloaded, or Deleted.',
                        position: 'bottom'
                    },
                    {
                        target: 'input[type="date"], input[name*="date"]',
                        title: 'Filter by Date Range',
                        content: 'Set start and end dates to view activities within a specific time period. Useful for auditing and compliance reporting.',
                        position: 'bottom'
                    },
                    {
                        target: 'button[type="submit"], form button.btn-primary',
                        title: 'Apply Filters',
                        content: 'Click to apply your selected filters. The activity list will update to show only matching entries.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.list-group, .activity-list',
                        title: 'Activity Timeline',
                        content: 'Each entry shows: What happened (created, restored, deleted), Who performed the action, When it occurred, and the IP address for security auditing.',
                        position: 'top'
                    },
                    {
                        target: '.list-group-item:first-child, .activity-item:first-child',
                        title: 'Activity Entry Details',
                        content: 'Click on any entry to see more details. Entries are color-coded: green for creates, yellow for restores, red for deletes.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.btn-outline-warning, button[onclick*="rollback"]',
                        title: 'Rollback Changes',
                        content: 'For restore operations, you may be able to rollback to the previous state. This requires password confirmation and creates a new backup point.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.pagination, nav[aria-label*="pagination"]',
                        title: 'Navigate Pages',
                        content: 'Use pagination to browse through older activity entries. Activities are sorted newest-first.',
                        position: 'top',
                        optional: true
                    },
                    {
                        target: 'a[href*="disaster-recovery"]:not([href*="activity"])',
                        title: 'Back to Backups',
                        content: 'Click here to return to the main Disaster Recovery page where you can create and manage backups.',
                        position: 'left'
                    }
                ]
            },
            
            // Departments tutorial - COMPREHENSIVE
            'admin-departments': {
                title: 'Department Management',
                description: 'Learn how to add and view academic departments',
                steps: [
                    {
                        target: 'button.btn-success',
                        title: 'Add Department',
                        content: 'Click here to create a new academic department. You\'ll enter a short code (e.g., CITE, CAS, CON) and the full department description.',
                        position: 'left'
                    },
                    {
                        target: '#departmentsTable thead, table thead',
                        title: 'Departments Table',
                        content: 'All departments are listed with: ID (unique identifier), Code (short name), Description (full name), and Creation Date.',
                        position: 'bottom'
                    },
                    {
                        target: '#departmentsTable_filter input, .dataTables_filter input',
                        title: 'Search Departments',
                        content: 'Use the search box to quickly find departments by code or description. Results filter as you type.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '#departmentsTable tbody tr:first-child, table tbody tr:first-child',
                        title: 'Department Entry',
                        content: 'Each row represents a department. The code is used throughout the system to identify the department (e.g., in programs, subjects, and formulas).',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.dataTables_length select, select[name*="length"]',
                        title: 'Entries Per Page',
                        content: 'Change how many departments are shown per page: 10, 25, 50, or 100 entries at a time.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '#departmentsTable th, table thead th',
                        title: 'Sort Columns',
                        content: 'Click any column header to sort the table. Click again to reverse the sort order (ascending/descending).',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Programs/Courses tutorial - COMPREHENSIVE
            'admin-programs': {
                title: 'Program Management',
                description: 'Learn how to add and manage academic programs (degree courses)',
                steps: [
                    {
                        target: 'button.btn-success',
                        title: 'Add Program',
                        content: 'Click here to add a new academic program (e.g., BSIT, BSCS, BSN). You\'ll specify the program code, description, and select which department it belongs to.',
                        position: 'left'
                    },
                    {
                        target: '#coursesTable thead, table thead',
                        title: 'Programs Table',
                        content: 'View all academic programs with: ID, Code (e.g., BSIT), Description (full program name), Department association, and Creation Date.',
                        position: 'bottom'
                    },
                    {
                        target: '#coursesTable_filter input, .dataTables_filter input',
                        title: 'Search Programs',
                        content: 'Quickly find programs by searching for code, description, or department name. Useful when you have many programs.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.badge.bg-light, table tbody td:nth-child(4)',
                        title: 'Department Association',
                        content: 'Each program belongs to exactly one department. The badge shows both the department code and full description.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#coursesTable th, table thead th',
                        title: 'Sort by Column',
                        content: 'Click column headers to sort programs alphabetically or by date. Sorting helps organize large program lists.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Subjects/Courses tutorial - COMPREHENSIVE
            'admin-subjects': {
                title: 'Course (Subject) Management',
                description: 'Learn how to add and manage academic courses/subjects',
                steps: [
                    {
                        target: 'button[data-bs-target="#subjectModal"], button.btn-success',
                        title: 'Add Course',
                        content: 'Click to add a new course/subject. You\'ll specify: Academic Period, Department, Program, Course Code, Description, Units, and Year Level.',
                        position: 'left'
                    },
                    {
                        target: '#subjectsTable thead, table thead',
                        title: 'Courses Table',
                        content: 'All courses are displayed with: ID, Code (e.g., ITE 101), Description, Units, Year Level, Department, Program, and Academic Period.',
                        position: 'bottom'
                    },
                    {
                        target: '#subjectsTable_filter input, .dataTables_filter input',
                        title: 'Search Courses',
                        content: 'Find courses by code, description, department, or program. Essential when managing hundreds of subjects.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '#subjectsTable tbody td:nth-child(4), table tbody td:nth-child(4)',
                        title: 'Course Units',
                        content: 'Units determine the credit weight of each course. Typically ranges from 1-6 units depending on course intensity.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#subjectsTable tbody td:nth-child(5), table tbody td:nth-child(5)',
                        title: 'Year Level',
                        content: 'Indicates which year students typically take this course: 1st, 2nd, 3rd, 4th, or 5th year.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#subjectsTable tbody td:last-child, table tbody td:last-child',
                        title: 'Academic Period',
                        content: 'Shows which academic year and semester this course is offered. Courses can be offered in multiple periods.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Academic Periods tutorial - COMPREHENSIVE
            'admin-academic-periods': {
                title: 'Academic Period Management',
                description: 'Learn how to generate and manage academic periods (semesters)',
                steps: [
                    {
                        target: 'button.btn-success',
                        title: 'Generate New Period',
                        content: 'Click to automatically generate the next academic period. The system calculates the next semester based on the latest existing period.',
                        position: 'left'
                    },
                    {
                        target: 'table thead',
                        title: 'Periods Table',
                        content: 'View all academic periods with: Academic Year (e.g., 2025-2026), Semester (1st, 2nd, or Summer), and Creation Date.',
                        position: 'bottom'
                    },
                    {
                        target: 'table tbody tr:first-child',
                        title: 'Period Entry',
                        content: 'Academic periods organize all academic activities by semester. Courses, grades, and enrollments are all tied to specific periods.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#confirmModal, .modal-content',
                        title: 'Generation Confirmation',
                        content: 'When generating a new period, you\'ll see a confirmation dialog. The system automatically determines whether the next period is a new semester or new academic year.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Select Period tutorial - COMPREHENSIVE
            'admin-grades-formula-select': {
                title: 'Select Academic Period for Formulas',
                description: 'Learn how to select a period before managing grade formulas',
                steps: [
                    {
                        target: '#academic-period-select',
                        title: 'Select Academic Period',
                        content: 'Choose which academic period to manage formulas for. Options include: "All Academic Periods" (global formulas), or specific periods like "2025-2026 - 1st Semester".',
                        position: 'bottom'
                    },
                    {
                        target: 'option[value="all"]',
                        title: 'All Periods Option',
                        content: 'Select "All Academic Periods" to manage global baseline formulas that apply across all periods unless overridden.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#continue-button',
                        title: 'Continue Button',
                        content: 'After selecting a period, click Continue. The button enables only after you make a selection.',
                        position: 'top'
                    },
                    {
                        target: '.btn-outline-secondary, a[href*="departments"]',
                        title: 'Back to Departments',
                        content: 'Return to the Departments page if you need to manage academic structure first.',
                        position: 'right',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Wildcards (Main) tutorial - COMPREHENSIVE
            'admin-grades-formula': {
                title: 'Grades Formula Management',
                description: 'Learn how to configure grading scales, formulas, and structure templates at all levels',
                steps: [
                    {
                        target: 'select[name="academic_period_id"]',
                        title: 'Academic Period Filter',
                        content: 'Filter formulas by academic period. Select "All Periods" to see global formulas or choose a specific semester.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-section-btn[data-section-target="overview"], button:contains("Overview")',
                        title: 'Overview Tab',
                        content: 'The Overview shows all departments as "wildcard" cards. Each card represents a department\'s formula status. Click any card to drill down.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-section-btn[data-section-target="formulas"], button:contains("Formulas")',
                        title: 'Structure Formulas Tab',
                        content: 'View and manage structure templates here. Create new templates, edit existing ones, or delete unused formulas.',
                        position: 'bottom'
                    },
                    {
                        target: '.bg-success.bg-opacity-10, .card-body .badge',
                        title: 'Wildcard Summary Card',
                        content: 'This summary shows: Total departments, how many have custom catalog formulas, and how many use the system baseline.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="all"]',
                        title: 'Filter All',
                        content: 'Show all department wildcards regardless of their formula status.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="custom"]',
                        title: 'Filter Custom Formulas',
                        content: 'Show only departments that have custom formula catalogs defined.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="default"]',
                        title: 'Filter Default/Baseline',
                        content: 'Show only departments using the system default formula (no customization).',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-card, .department-card',
                        title: 'Department Card',
                        content: 'Click any department card to enter that department and manage its course and subject formulas. The badge shows if it has custom formulas.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: 'a[href*="structure-template-requests"]',
                        title: 'View Requests',
                        content: 'Chairpersons can submit formula requests. Click here to review pending submissions and approve or reject them.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Department tutorial - COMPREHENSIVE
            'admin-grades-formula-department': {
                title: 'Department Formula Management',
                description: 'Learn how to manage department-level formulas, create catalog formulas, and drill into courses',
                steps: [
                    {
                        target: 'a[href*="edit.department"], .btn-success:first-of-type',
                        title: 'Edit/Create Department Formula',
                        content: 'Create or edit the department\'s fallback formula. This becomes the baseline for ALL courses and subjects in this department unless overridden.',
                        position: 'left'
                    },
                    {
                        target: '.btn-outline-secondary[href*="overview"]',
                        title: 'Back to Overview',
                        content: 'Return to the main formula overview to select a different department.',
                        position: 'left'
                    },
                    {
                        target: 'select[name="academic_year"]',
                        title: 'Filter by Academic Year',
                        content: 'Filter the course list by academic year. Useful when you have courses across multiple years.',
                        position: 'bottom'
                    },
                    {
                        target: 'select[name="semester"]',
                        title: 'Filter by Semester',
                        content: 'Further filter by semester: 1st, 2nd, or Summer. Combine with year for precise filtering.',
                        position: 'bottom'
                    },
                    {
                        target: '.badge.bg-success, .badge:contains("Baseline")',
                        title: 'Current Baseline Formula',
                        content: 'This badge shows which formula is the current baseline for this department. All courses inherit this unless they have custom formulas.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="all"]',
                        title: 'View All Wildcards',
                        content: 'Show all items: both the formula catalog and course wildcards.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="custom"]',
                        title: 'View Catalog Formulas',
                        content: 'Show only the department formula catalog - reusable formula templates instructors can apply.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="default"]',
                        title: 'View Course Wildcards',
                        content: 'Show only course wildcards to see which courses have custom formulas vs using the baseline.',
                        position: 'bottom'
                    },
                    {
                        target: 'a[href*="formulas.create"], .btn:contains("Create Catalog")',
                        title: 'Create Catalog Formula',
                        content: 'Add a new formula to the department catalog. These templates can be selected by instructors when setting up their subjects.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.formula-card, .wildcard-card[data-status="catalog"]',
                        title: 'Catalog Formula Card',
                        content: 'Each catalog formula shows: Label, weight distribution (Quiz %, Exam %, etc.), base score, scale multiplier, and passing grade. Click to edit.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.wildcard-card:not([data-status="catalog"]), .course-card',
                        title: 'Course Wildcard Card',
                        content: 'Click any course card to drill down into that course and manage subject-level formulas.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: 'form[onsubmit*="confirm"], button[type="submit"].btn-outline-danger',
                        title: 'Delete Formula',
                        content: 'Remove a catalog formula. The fallback formula cannot be deleted. Requires confirmation.',
                        position: 'left',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Course tutorial - COMPREHENSIVE
            'admin-grades-formula-course': {
                title: 'Course Formula Management',
                description: 'Learn how to manage course-level formulas and drill into individual subjects',
                steps: [
                    {
                        target: 'a[href*="edit.course"], .btn-success:first-of-type',
                        title: 'Edit/Create Course Formula',
                        content: 'Create or edit a formula specific to this course. This overrides the department baseline for all subjects in this course.',
                        position: 'left'
                    },
                    {
                        target: '.btn-outline-secondary[href*="department"]',
                        title: 'Back to Department',
                        content: 'Return to the department view to select a different course or manage department formulas.',
                        position: 'left'
                    },
                    {
                        target: 'select[name="academic_year"]',
                        title: 'Filter by Academic Year',
                        content: 'Filter subjects by academic year to focus on a specific period.',
                        position: 'bottom'
                    },
                    {
                        target: 'select[name="semester"]',
                        title: 'Filter by Semester',
                        content: 'Further filter by semester for precise subject filtering.',
                        position: 'bottom'
                    },
                    {
                        target: '.bg-gradient-green-card, .card.bg-success',
                        title: 'Subject Overview Summary',
                        content: 'Shows: Total subjects in this course, how many have custom formulas, how many use the course/department fallback, and which fallback is active.',
                        position: 'bottom'
                    },
                    {
                        target: '.badge:contains("Fallback"), .fw-semibold:contains("Fallback")',
                        title: 'Active Fallback Information',
                        content: 'Shows which formula subjects will use if they don\'t have a custom one: Course Formula (if set) or Department Baseline.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="all"]',
                        title: 'View All Subjects',
                        content: 'Show all subjects in this course regardless of formula status.',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="custom"]',
                        title: 'View Custom Formulas',
                        content: 'Show only subjects that have custom formulas defined (overriding course/department baseline).',
                        position: 'bottom'
                    },
                    {
                        target: '.wildcard-filter-btn[data-filter="default"]',
                        title: 'View Default/Inherited',
                        content: 'Show only subjects using the inherited formula (no custom override).',
                        position: 'bottom'
                    },
                    {
                        target: '#subject-wildcards .wildcard-card, .subject-card',
                        title: 'Subject Card',
                        content: 'Click any subject card to view its formula details. The badge shows formula status: Custom (green) or inherited from Course/Department.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.alert-info',
                        title: 'No Course Formula Notice',
                        content: 'This alert appears when no course formula exists. Subjects will inherit from department baseline or system default.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Subject tutorial - COMPREHENSIVE
            'admin-grades-formula-subject': {
                title: 'Subject Formula Configuration',
                description: 'Learn how to configure subject-specific formulas and apply catalog templates',
                steps: [
                    {
                        target: 'a[href*="edit.subject"], .btn-success:first-of-type',
                        title: 'Edit Subject Formula',
                        content: 'Create or edit a formula specific to this subject. This is the most granular level - overrides both course and department settings.',
                        position: 'left'
                    },
                    {
                        target: '.btn-outline-secondary[href*="course"]',
                        title: 'Back to Course',
                        content: 'Return to the course view to select a different subject or manage course formula.',
                        position: 'left'
                    },
                    {
                        target: '.card-body .badge.bg-success-subtle, .formula-weight-chip',
                        title: 'Current Weight Distribution',
                        content: 'View the current activity weights: Quiz, Exam, Assignment, Project, etc. Each shows the percentage of the final grade.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.card:contains("Formula Details"), .formula-details-card',
                        title: 'Formula Details',
                        content: 'Shows complete formula configuration: Base Score, Scale Multiplier, Passing Grade, and all activity type weights.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '#subject-formula-apply-form, select[name="formula_id"]',
                        title: 'Apply Catalog Formula',
                        content: 'Instead of manually configuring, select a formula from the department catalog. This copies all settings from the selected template.',
                        position: 'top'
                    },
                    {
                        target: 'button[type="submit"]:contains("Apply"), .btn:contains("Apply Formula")',
                        title: 'Apply Selected Formula',
                        content: 'Click to apply the selected catalog formula to this subject. Previous settings will be replaced.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.badge:contains("Source"), .formula-source',
                        title: 'Formula Source',
                        content: 'Shows where the current formula comes from: Subject-level (custom), Course-level, or Department-level.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            },
            
            // Grades Formula - Edit Form tutorial - COMPREHENSIVE
            'admin-grades-formula-edit': {
                title: 'Edit Grade Formula',
                description: 'Learn how to configure all formula parameters: weights, scaling, and grade calculations',
                steps: [
                    {
                        target: 'input[name="label"]',
                        title: 'Formula Label',
                        content: 'Give your formula a descriptive name (e.g., "Standard Lecture Formula", "Lab Heavy Formula"). This helps identify the formula when selecting it.',
                        position: 'bottom'
                    },
                    {
                        target: 'textarea[name="description"], input[name="description"]',
                        title: 'Formula Description',
                        content: 'Add an optional description explaining when to use this formula or any special considerations.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.card-header:contains("Weight"), .weight-section',
                        title: 'Weight Distribution Section',
                        content: 'Configure how different activity types contribute to the final grade. All weights must sum to 100%.',
                        position: 'bottom'
                    },
                    {
                        target: 'input[name*="weight"][name*="quiz"], .weight-input:first-of-type',
                        title: 'Activity Type Weights',
                        content: 'Set the percentage for each activity type: Quiz, Exam, Assignment, Project, Attendance, etc. Enter as whole numbers (e.g., 20 for 20%).',
                        position: 'right',
                        optional: true
                    },
                    {
                        target: '.weight-total, .badge:contains("Total")',
                        title: 'Weight Total Indicator',
                        content: 'The total shows the sum of all weights. Must equal exactly 100% before you can save.',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: 'input[name="base_score"]',
                        title: 'Base Score',
                        content: 'The starting point for grade calculations. Typically 50 or 60. Raw scores are adjusted relative to this base.',
                        position: 'right'
                    },
                    {
                        target: 'input[name="scale_multiplier"]',
                        title: 'Scale Multiplier',
                        content: 'Multiplier applied to adjusted scores. Typically 50. Formula: Final = Base + (Adjusted × Multiplier).',
                        position: 'right'
                    },
                    {
                        target: 'input[name="passing_grade"]',
                        title: 'Passing Grade',
                        content: 'The minimum final grade required to pass. Typically 75. Students below this threshold fail the subject.',
                        position: 'right'
                    },
                    {
                        target: '.preview-section, .formula-preview',
                        title: 'Formula Preview',
                        content: 'See a live preview of how the formula will calculate grades based on your settings.',
                        position: 'top',
                        optional: true
                    },
                    {
                        target: 'button[type="submit"].btn-success, .btn:contains("Save")',
                        title: 'Save Formula',
                        content: 'Click to save your formula. Changes apply immediately to all subjects using this formula.',
                        position: 'top'
                    },
                    {
                        target: '.btn-outline-secondary, a:contains("Cancel")',
                        title: 'Cancel Changes',
                        content: 'Discard your changes and return to the previous page without saving.',
                        position: 'top',
                        optional: true
                    }
                ]
            },
            
            // Structure Template Requests tutorial - COMPREHENSIVE
            'admin-structure-template-requests': {
                title: 'Formula Request Review',
                description: 'Learn how to review, approve, and reject chairperson formula submissions',
                steps: [
                    {
                        target: 'a[href*="status=all"], .btn:contains("All Requests")',
                        title: 'All Requests Filter',
                        content: 'View all formula requests regardless of status. Gives you a complete picture of submission history.',
                        position: 'bottom'
                    },
                    {
                        target: 'a[href*="status=pending"], .btn-warning',
                        title: 'Pending Requests',
                        content: 'Filter to see only pending requests awaiting your review. The badge shows how many need attention.',
                        position: 'bottom'
                    },
                    {
                        target: 'a[href*="status=approved"], .btn:contains("Approved")',
                        title: 'Approved Requests',
                        content: 'View previously approved formulas. These are now available system-wide for chairpersons to use.',
                        position: 'bottom'
                    },
                    {
                        target: 'a[href*="status=rejected"], .btn:contains("Rejected")',
                        title: 'Rejected Requests',
                        content: 'View rejected submissions with admin notes explaining why they were declined.',
                        position: 'bottom'
                    },
                    {
                        target: 'table thead',
                        title: 'Request Details Table',
                        content: 'Each request shows: Template Name, Submitter (chairperson), Structure Type (Lecture Only, Lecture+Lab, Custom), Status, and Submission Date.',
                        position: 'bottom'
                    },
                    {
                        target: '.badge.bg-info, .badge:contains("Lecture")',
                        title: 'Structure Type',
                        content: 'The structure type indicates: Lecture Only (theory courses), Lecture + Lab (with practical component), or Custom (special configuration).',
                        position: 'bottom',
                        optional: true
                    },
                    {
                        target: '.btn-info, button:contains("View")',
                        title: 'View Request Details',
                        content: 'Click View to see the complete formula structure including all weights, parameters, and the chairperson\'s notes.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.btn-success.btn-sm, button:contains("Approve")',
                        title: 'Approve Request',
                        content: 'Approve the formula to make it available system-wide. The formula becomes part of the global template catalog.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '.btn-danger.btn-sm, button:contains("Reject")',
                        title: 'Reject Request',
                        content: 'Reject with notes explaining why. The chairperson will see your feedback and can submit a revised request.',
                        position: 'left',
                        optional: true
                    },
                    {
                        target: '#viewRequestModal, .modal-content',
                        title: 'Request Details Modal',
                        content: 'The modal shows complete formula details: all activity weights, scaling parameters, passing grade, and chairperson description.',
                        position: 'center',
                        optional: true
                    },
                    {
                        target: 'textarea[name="admin_notes"], input[name="admin_notes"]',
                        title: 'Admin Notes',
                        content: 'When approving or rejecting, add notes to communicate with the chairperson about your decision.',
                        position: 'bottom',
                        optional: true
                    }
                ]
            }
        },
        
        /**
         * Initialize the tutorial system
         */
        init: function() {
            this.createOverlayElements();
            this.bindEvents();
            this.createTutorialButton();
            
            // Check for first-time visit to current page
            const pageId = this.getCurrentPageId();
            if (pageId && this.tutorials[pageId] && !this.hasCompletedTutorial(pageId)) {
                // Add pulse animation to FAB to draw attention
                const fab = document.getElementById('tutorial-fab');
                if (fab) {
                    fab.classList.add('pulse');
                    // Remove pulse after animation completes
                    setTimeout(() => fab.classList.remove('pulse'), 6000);
                }
                
                // Small delay to let page render
                setTimeout(() => {
                    this.promptTutorial(pageId);
                }, 1000);
            }
        },
        
        /**
         * Get current page identifier
         */
        getCurrentPageId: function() {
            const path = window.location.pathname;
            
            // Map URL paths to tutorial IDs - Order matters (more specific first)
            
            // Admin Dashboard
            if (path.includes('/dashboard') && document.querySelector('.bi-sliders')) {
                return 'admin-dashboard';
            }
            
            // User Management
            if (path.includes('/admin/users')) {
                return 'admin-users';
            }
            
            // Session Management
            if (path.includes('/admin/sessions')) {
                return 'admin-sessions';
            }
            
            // Disaster Recovery - Activity Log (more specific, check first)
            if (path.includes('/admin/disaster-recovery/activity')) {
                return 'admin-disaster-recovery-activity';
            }
            
            // Disaster Recovery - Main
            if (path.includes('/admin/disaster-recovery')) {
                return 'admin-disaster-recovery';
            }
            
            // Structure Template Requests
            if (path.includes('/admin/structure-template-requests')) {
                return 'admin-structure-template-requests';
            }
            
            // Grades Formula - Edit forms (most specific first)
            if (path.includes('/grades-formula') && path.includes('/edit')) {
                return 'admin-grades-formula-edit';
            }
            
            // Grades Formula - Subject level
            if (path.includes('/grades-formula/subject/')) {
                return 'admin-grades-formula-subject';
            }
            
            // Grades Formula - Course level
            if (path.includes('/grades-formula/department/') && path.includes('/course/')) {
                return 'admin-grades-formula-course';
            }
            
            // Grades Formula - Department level
            if (path.includes('/grades-formula/department/')) {
                return 'admin-grades-formula-department';
            }
            
            // Grades Formula - Select Period
            if (path.includes('/grades-formula') && document.querySelector('#academic-period-select')) {
                return 'admin-grades-formula-select';
            }
            
            // Grades Formula - Main wildcards page
            if (path.includes('/admin/grades-formula')) {
                return 'admin-grades-formula';
            }
            
            // Academic Structure
            if (path.includes('/admin/departments')) {
                return 'admin-departments';
            }
            
            if (path.includes('/admin/courses') || path.includes('/admin/programs')) {
                return 'admin-programs';
            }
            
            if (path.includes('/admin/subjects')) {
                return 'admin-subjects';
            }
            
            if (path.includes('/admin/academic-periods')) {
                return 'admin-academic-periods';
            }
            
            return null;
        },
        
        /**
         * Create overlay elements for the tutorial
         */
        createOverlayElements: function() {
            // Main overlay
            this.overlay = document.createElement('div');
            this.overlay.className = 'tutorial-overlay';
            this.overlay.id = 'tutorial-overlay';
            document.body.appendChild(this.overlay);
            
            // Spotlight element
            this.spotlight = document.createElement('div');
            this.spotlight.className = 'tutorial-spotlight';
            this.spotlight.id = 'tutorial-spotlight';
            document.body.appendChild(this.spotlight);
            
            // Tooltip element
            this.tooltip = document.createElement('div');
            this.tooltip.className = 'tutorial-tooltip';
            this.tooltip.id = 'tutorial-tooltip';
            this.tooltip.innerHTML = `
                <div class="tutorial-tooltip-header">
                    <span class="tutorial-step-indicator"></span>
                    <button class="tutorial-close-btn" aria-label="Close tutorial">&times;</button>
                </div>
                <h4 class="tutorial-tooltip-title"></h4>
                <p class="tutorial-tooltip-content"></p>
                <div class="tutorial-tooltip-actions">
                    <button class="tutorial-btn tutorial-btn-secondary tutorial-skip-btn">Skip Tutorial</button>
                    <div class="tutorial-nav-btns">
                        <button class="tutorial-btn tutorial-btn-secondary tutorial-prev-btn">
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <button class="tutorial-btn tutorial-btn-primary tutorial-next-btn">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(this.tooltip);
        },
        
        /**
         * Create the floating tutorial button at bottom right
         */
        createTutorialButton: function() {
            // Create floating action button
            const fab = document.createElement('button');
            fab.id = 'tutorial-fab';
            fab.className = 'tutorial-fab';
            fab.setAttribute('aria-label', 'Start Page Tutorial');
            fab.setAttribute('title', 'Page Tutorial');
            fab.innerHTML = `
                <i class="bi bi-question-lg"></i>
                <span class="tutorial-fab-tooltip">Page Tutorial</span>
            `;
            
            document.body.appendChild(fab);
            
            // Bind click event
            fab.addEventListener('click', () => {
                const pageId = this.getCurrentPageId();
                if (pageId && this.tutorials[pageId]) {
                    this.start(pageId);
                } else {
                    this.showNoTutorialMessage();
                }
            });
        },
        
        /**
         * Bind event listeners
         */
        bindEvents: function() {
            // Close button
            this.tooltip.querySelector('.tutorial-close-btn').addEventListener('click', () => this.end());
            
            // Skip button
            this.tooltip.querySelector('.tutorial-skip-btn').addEventListener('click', () => this.end());
            
            // Previous button
            this.tooltip.querySelector('.tutorial-prev-btn').addEventListener('click', () => this.prevStep());
            
            // Next button
            this.tooltip.querySelector('.tutorial-next-btn').addEventListener('click', () => this.nextStep());
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (!this.isActive) return;
                
                if (e.key === 'Escape') {
                    this.end();
                } else if (e.key === 'ArrowRight' || e.key === 'Enter') {
                    this.nextStep();
                } else if (e.key === 'ArrowLeft') {
                    this.prevStep();
                }
            });
            
            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                if (!this.isActive) return;
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => this.updatePosition(), 100);
            });
        },
        
        /**
         * Prompt user to start tutorial (first visit)
         */
        promptTutorial: function(pageId) {
            const tutorial = this.tutorials[pageId];
            if (!tutorial) return;
            
            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '👋 Welcome!',
                    html: `
                        <div class="text-start">
                            <h5 class="mb-2">${tutorial.title}</h5>
                            <p class="text-muted">${tutorial.description}</p>
                            <p class="mb-0"><small>Would you like a quick tour of this page?</small></p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Start Tutorial',
                    cancelButtonText: 'Maybe Later'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.start(pageId);
                    } else {
                        // Mark as seen but not completed
                        this.markTutorialSeen(pageId);
                    }
                });
            } else {
                // Fallback to confirm dialog
                if (confirm(`Welcome! Would you like a quick tour of ${tutorial.title}?`)) {
                    this.start(pageId);
                } else {
                    this.markTutorialSeen(pageId);
                }
            }
        },
        
        /**
         * Show message when no tutorial is available
         */
        showNoTutorialMessage: function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'No Tutorial Available',
                    text: 'There is no tutorial available for this page yet.',
                    icon: 'info',
                    confirmButtonColor: '#198754'
                });
            } else {
                alert('No tutorial available for this page.');
            }
        },
        
        /**
         * Start a tutorial
         */
        start: function(tutorialId) {
            const tutorial = this.tutorials[tutorialId];
            if (!tutorial) {
                console.warn('Tutorial not found:', tutorialId);
                return;
            }
            
            this.currentTutorial = { id: tutorialId, ...tutorial };
            this.currentStep = 0;
            this.isActive = true;
            
            // Hide FAB
            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.add('hidden');
            
            // Show overlay
            this.overlay.classList.add('active');
            this.tooltip.classList.add('active');
            
            // Show first step
            this.showStep(0);
        },
        
        /**
         * End the tutorial
         */
        end: function() {
            this.isActive = false;
            this.overlay.classList.remove('active');
            this.tooltip.classList.remove('active');
            this.spotlight.classList.remove('active');
            
            // Show FAB again
            const fab = document.getElementById('tutorial-fab');
            if (fab) fab.classList.remove('hidden');
            
            // Mark as completed
            if (this.currentTutorial) {
                this.markTutorialCompleted(this.currentTutorial.id);
            }
            
            this.currentTutorial = null;
            this.currentStep = 0;
            
            // Remove any highlighted elements
            document.querySelectorAll('.tutorial-highlight').forEach(el => {
                el.classList.remove('tutorial-highlight');
            });
        },
        
        /**
         * Show a specific step
         */
        showStep: function(stepIndex) {
            if (!this.currentTutorial) return;
            
            const steps = this.currentTutorial.steps;
            
            // Handle optional steps that might not have targets
            let step = steps[stepIndex];
            let targetEl = this.findTarget(step.target);
            
            // If target not found and step is optional, skip to next
            while (!targetEl && step.optional && stepIndex < steps.length - 1) {
                stepIndex++;
                step = steps[stepIndex];
                targetEl = this.findTarget(step.target);
            }
            
            // If still no target, try next non-optional or show completion
            if (!targetEl) {
                if (stepIndex < steps.length - 1) {
                    this.showStep(stepIndex + 1);
                    return;
                } else {
                    this.showCompletion();
                    return;
                }
            }
            
            this.currentStep = stepIndex;
            
            // Update UI
            this.updateStepIndicator();
            this.updateButtons();
            this.highlightElement(targetEl, step);
            this.positionTooltip(targetEl, step);
            
            // Update content
            this.tooltip.querySelector('.tutorial-tooltip-title').textContent = step.title;
            this.tooltip.querySelector('.tutorial-tooltip-content').textContent = step.content;
            
            // Scroll element into view
            this.scrollIntoView(targetEl);
        },
        
        /**
         * Find target element using selector
         */
        findTarget: function(selector) {
            if (!selector) return null;
            
            // Handle jQuery-like :contains selector
            if (selector.includes(':contains')) {
                const match = selector.match(/(.+):contains\("(.+)"\)/);
                if (match) {
                    const baseSelector = match[1];
                    const text = match[2];
                    const elements = document.querySelectorAll(baseSelector);
                    for (const el of elements) {
                        if (el.textContent.includes(text)) {
                            return el;
                        }
                    }
                }
            }
            
            // Handle multiple selectors (comma-separated)
            const selectors = selector.split(',').map(s => s.trim());
            for (const sel of selectors) {
                try {
                    const el = document.querySelector(sel);
                    if (el && this.isVisible(el)) {
                        return el;
                    }
                } catch (e) {
                    // Invalid selector, continue
                }
            }
            
            return null;
        },
        
        /**
         * Check if element is visible
         */
        isVisible: function(el) {
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            return rect.width > 0 && 
                   rect.height > 0 && 
                   style.visibility !== 'hidden' && 
                   style.display !== 'none';
        },
        
        /**
         * Highlight the target element
         */
        highlightElement: function(el, step) {
            // Remove previous highlights
            document.querySelectorAll('.tutorial-highlight').forEach(e => {
                e.classList.remove('tutorial-highlight');
            });
            
            // Add highlight to current element
            el.classList.add('tutorial-highlight');
            
            // Position spotlight
            const rect = el.getBoundingClientRect();
            const padding = 8;
            
            this.spotlight.style.top = (rect.top + window.scrollY - padding) + 'px';
            this.spotlight.style.left = (rect.left + window.scrollX - padding) + 'px';
            this.spotlight.style.width = (rect.width + padding * 2) + 'px';
            this.spotlight.style.height = (rect.height + padding * 2) + 'px';
            this.spotlight.classList.add('active');
        },
        
        /**
         * Position the tooltip relative to target
         */
        positionTooltip: function(el, step) {
            const rect = el.getBoundingClientRect();
            const tooltipRect = this.tooltip.getBoundingClientRect();
            const position = step.position || 'bottom';
            const spacing = 16;
            
            let top, left;
            
            switch (position) {
                case 'top':
                    top = rect.top + window.scrollY - tooltipRect.height - spacing;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'bottom':
                    top = rect.bottom + window.scrollY + spacing;
                    left = rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2);
                    break;
                case 'left':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.left + window.scrollX - tooltipRect.width - spacing;
                    break;
                case 'right':
                    top = rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2);
                    left = rect.right + window.scrollX + spacing;
                    break;
            }
            
            // Ensure tooltip stays within viewport
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            if (left < 10) left = 10;
            if (left + tooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - tooltipRect.width - 10;
            }
            if (top < window.scrollY + 10) {
                top = rect.bottom + window.scrollY + spacing;
            }
            if (top + tooltipRect.height > window.scrollY + viewportHeight - 10) {
                top = rect.top + window.scrollY - tooltipRect.height - spacing;
            }
            
            this.tooltip.style.top = top + 'px';
            this.tooltip.style.left = left + 'px';
            
            // Set position class for arrow
            this.tooltip.className = 'tutorial-tooltip active tutorial-tooltip-' + position;
        },
        
        /**
         * Scroll element into view smoothly
         */
        scrollIntoView: function(el) {
            const rect = el.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            
            if (rect.top < 100 || rect.bottom > viewportHeight - 100) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        },
        
        /**
         * Update step indicator
         */
        updateStepIndicator: function() {
            const indicator = this.tooltip.querySelector('.tutorial-step-indicator');
            const total = this.currentTutorial.steps.length;
            indicator.textContent = `Step ${this.currentStep + 1} of ${total}`;
        },
        
        /**
         * Update navigation buttons
         */
        updateButtons: function() {
            const prevBtn = this.tooltip.querySelector('.tutorial-prev-btn');
            const nextBtn = this.tooltip.querySelector('.tutorial-next-btn');
            const total = this.currentTutorial.steps.length;
            
            prevBtn.style.display = this.currentStep === 0 ? 'none' : 'inline-flex';
            
            if (this.currentStep === total - 1) {
                nextBtn.innerHTML = 'Finish <i class="bi bi-check-lg"></i>';
            } else {
                nextBtn.innerHTML = 'Next <i class="bi bi-chevron-right"></i>';
            }
        },
        
        /**
         * Go to next step
         */
        nextStep: function() {
            if (!this.currentTutorial) return;
            
            if (this.currentStep < this.currentTutorial.steps.length - 1) {
                this.showStep(this.currentStep + 1);
            } else {
                this.showCompletion();
            }
        },
        
        /**
         * Go to previous step
         */
        prevStep: function() {
            if (this.currentStep > 0) {
                this.showStep(this.currentStep - 1);
            }
        },
        
        /**
         * Show completion message
         */
        showCompletion: function() {
            this.end();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🎉 Tutorial Complete!',
                    text: 'You\'ve completed the tutorial. You can restart it anytime using the help button in the header.',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Got it!'
                });
            }
        },
        
        /**
         * Update position on resize
         */
        updatePosition: function() {
            if (!this.isActive || !this.currentTutorial) return;
            this.showStep(this.currentStep);
        },
        
        /**
         * Check if tutorial was completed
         */
        hasCompletedTutorial: function(tutorialId) {
            return localStorage.getItem(this.STORAGE_PREFIX + tutorialId + '_completed') === 'true';
        },
        
        /**
         * Mark tutorial as completed
         */
        markTutorialCompleted: function(tutorialId) {
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_completed', 'true');
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_seen', 'true');
        },
        
        /**
         * Mark tutorial as seen (but not completed)
         */
        markTutorialSeen: function(tutorialId) {
            localStorage.setItem(this.STORAGE_PREFIX + tutorialId + '_seen', 'true');
        },
        
        /**
         * Reset all tutorial progress (for testing)
         */
        resetAllProgress: function() {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(this.STORAGE_PREFIX)) {
                    localStorage.removeItem(key);
                }
            });
            console.log('Tutorial progress reset');
        },
        
        /**
         * Reset specific tutorial progress
         */
        resetProgress: function(tutorialId) {
            localStorage.removeItem(this.STORAGE_PREFIX + tutorialId + '_completed');
            localStorage.removeItem(this.STORAGE_PREFIX + tutorialId + '_seen');
            console.log('Tutorial progress reset for:', tutorialId);
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AdminTutorial.init());
    } else {
        // Small delay to ensure all dynamic content is loaded
        setTimeout(() => AdminTutorial.init(), 500);
    }
})();
