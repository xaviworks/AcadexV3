# Announcement Popup Troubleshooting Guide

## Issue Fixed: Popup Not Showing

### Root Cause
The announcement popup had critical JavaScript syntax errors that prevented it from initializing:
1. CSS media query block wasn't properly closed, causing JavaScript to be embedded inside CSS
2. `previousAnnouncement()` function was incomplete with syntax errors

### Fix Applied (Jan 6, 2026)
- ✅ Fixed JavaScript syntax errors in `announcement-popup.blade.php`
- ✅ Properly closed CSS media query block
- ✅ Completed the `previousAnnouncement()` function
- ✅ Rebuilt Vite assets
- ✅ Cleared Laravel caches

### Verification Steps

1. **Open Browser Console** (F12 or Cmd+Option+I)
   - Check for JavaScript errors
   - You should NOT see any "Unexpected token" or syntax errors
   
2. **Check Network Tab**
   - Look for request to `/announcements/active`
   - Should return HTTP 200 with JSON array of announcements
   
3. **Verify Alpine.js**
   - In console, type: `window.Alpine`
   - Should return Alpine object (not undefined)
   
4. **Check for Announcements**
   - In console, type: `fetch('/announcements/active').then(r => r.json()).then(console.log)`
   - Should log array of active announcements

### Current Status
- ✅ 3 active announcements in database
- ✅ All routes registered correctly
- ✅ JavaScript syntax errors fixed
- ✅ Alpine.js properly loaded
- ✅ Component included in layout

### Testing Instructions

1. **Login to the system** (as any user role)
2. **Wait 1-2 seconds** after page load for announcements to fetch
3. **Popup should appear** at top-center of the page
4. **If you see multiple announcements**, use Previous/Next buttons to navigate
5. **Close button** works if announcement is dismissible

### If Popup Still Doesn't Show

1. **Hard Refresh**: Press Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
   - This ensures new JavaScript is loaded

2. **Check Browser Console** for errors:
   ```javascript
   // Test announcement fetching manually
   fetch('/announcements/active', {
     headers: {
       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
       'Accept': 'application/json',
     }
   }).then(r => r.json()).then(data => {
     console.log('Announcements:', data);
   });
   ```

3. **Verify User Role** in announcements:
   - Check `target_roles` field in database
   - Ensure your role is included (or target_roles is `["all"]`)
   
4. **Check Date Range**:
   - Verify announcement's `start_date` and `end_date`
   - Current date must be within range

5. **Verify is_active flag**:
   ```bash
   php artisan tinker
   >>> \App\Models\Announcement::current()->get(['id', 'title', 'is_active', 'start_date', 'end_date']);
   ```

### Create Test Announcement

```bash
php artisan tinker
```

```php
$announcement = new \App\Models\Announcement();
$announcement->title = 'Test Popup';
$announcement->message = 'If you see this, the popup is working!';
$announcement->type = 'info';
$announcement->priority = 'urgent';
$announcement->target_roles = ['all'];
$announcement->is_active = true;
$announcement->is_dismissible = true;
$announcement->show_once = false;
$announcement->created_by = 1; // Adjust to valid admin user ID
$announcement->save();
```

### Important Files
- Component: `resources/views/components/announcement-popup.blade.php`
- Controller: `app/Http/Controllers/AnnouncementController.php`
- Routes: `routes/web.php` (search for "announcements")
- Layout: `resources/views/layouts/app.blade.php` (line 450)

### Technical Details
- **Endpoint**: `GET /announcements/active` (returns JSON)
- **Mark as Viewed**: `POST /announcements/{id}/view`
- **Auto-dismiss**: 12 seconds for low/normal priority
- **Authentication**: Required (middleware: auth)
- **Alpine.js Component**: `announcementPopup()`

### Support
If issues persist after following these steps:
1. Check `storage/logs/laravel.log` for PHP errors
2. Verify XAMPP Apache and MySQL are running
3. Ensure database migrations are up to date: `php artisan migrate:status`
