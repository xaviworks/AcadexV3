# System Announcements Feature

## Overview

The **System Announcements** feature allows administrators to create and manage popup notifications that appear to users when they log in or access their dashboards. This is perfect for communicating important updates, maintenance schedules, deadlines, and other critical information.

---

## Features

### ✨ **Key Capabilities**

1. **Admin Management Interface**
   - Create, edit, delete announcements
   - Toggle active/inactive status
   - View announcement analytics (view counts)

2. **Flexible Targeting**
   - Show to all users or specific roles
   - Target: Instructors, Chairpersons, Deans, Admins, GE Coordinators, VPAA

3. **Scheduling**
   - Set start/end dates for time-limited announcements
   - Permanent announcements (no end date)
   - Future-scheduled announcements

4. **Customization Options**
   - **Types**: Info (Blue), Success (Green), Warning (Yellow), Danger (Red)
   - **Priority Levels**: Low, Normal, High, Urgent
   - Dismissible or persistent popups
   - Show once per user option

5. **User Experience**
   - Beautiful gradient popups (bottom-right corner)
   - Smooth animations (slide-in/fade-out)
   - Auto-dismiss for low-priority announcements (10 seconds)
   - Responsive design (mobile-friendly)

6. **Tracking**
   - Track which users have viewed announcements
   - View count statistics
   - Show once functionality prevents repeat displays

---

## Installation

Already installed! The feature includes:

✅ **Database Tables**
- `announcements` - Stores announcement data
- `announcement_views` - Tracks user views

✅ **Routes**
```php
// Admin routes
GET  /admin/announcements
GET  /admin/announcements/create
POST /admin/announcements
GET  /admin/announcements/{id}/edit
PUT  /admin/announcements/{id}
DELETE /admin/announcements/{id}
POST /admin/announcements/{id}/toggle

// User routes (all authenticated users)
GET  /announcements/active
POST /announcements/{id}/view
```

✅ **Components**
- Admin management views (index, create, edit)
- Popup component (included in app layout)
- Alpine.js integration

---

## Usage

### **Admin: Creating an Announcement**

1. Navigate to **Admin → Announcements** in sidebar
2. Click **Create Announcement**
3. Fill in the form:
   - **Title**: Short, attention-grabbing headline
   - **Message**: Detailed message (supports line breaks)
   - **Type**: Visual style (info/success/warning/danger)
   - **Priority**: Controls auto-dismiss behavior
   - **Target Users**: All users or specific roles
   - **Dates**: Optional start/end dates
   - **Options**:
     - ✓ Dismissible: Users can close it
     - ✓ Show once: Won't reappear after dismissal
     - ✓ Active: Publish immediately

4. Click **Create Announcement**

### **Admin: Managing Announcements**

- **Toggle Status**: Click status button to activate/deactivate
- **Edit**: Click edit icon to modify
- **Delete**: Click trash icon (permanent deletion)
- **View Analytics**: See view count for each announcement

### **User Experience**

When users log in:
1. Active announcements appear as popups (bottom-right)
2. Multiple announcements stack vertically
3. Users can dismiss (if allowed)
4. Low/Normal priority auto-dismiss after 10 seconds
5. High/Urgent stay until manually dismissed

---

## Examples

### **Example 1: System Maintenance**

```
Title: ⚠️ Scheduled Maintenance
Message: System will be down Jan 10, 2-6 AM for maintenance.
Type: Warning
Priority: Urgent
Target: All Users
Dismissible: Yes
Show Once: No
```

### **Example 2: Grade Deadline**

```
Title: 📝 Final Grade Submission Deadline
Message: All grades must be submitted by January 15, 2026.
Type: Info
Priority: High
Target: Instructors only
Start Date: Jan 8, 2026
End Date: Jan 15, 2026
Dismissible: Yes
Show Once: Yes
```

### **Example 3: Feature Announcement**

```
Title: 🎉 New Feature: Course Analytics
Message: Check out the new analytics dashboard for course insights!
Type: Success
Priority: Normal
Target: Chairpersons, Deans, VPAA
Dismissible: Yes
Show Once: Yes
```

---

## Technical Details

### **Database Schema**

#### `announcements`
```sql
- id (bigint)
- title (varchar)
- message (text)
- type (enum: info, warning, success, danger)
- priority (enum: low, normal, high, urgent)
- target_roles (json, nullable) - [0,1,2,3,4,5] or null for all
- start_date (datetime, nullable)
- end_date (datetime, nullable)
- is_active (boolean)
- is_dismissible (boolean)
- show_once (boolean)
- created_by (foreign key to users)
- timestamps
```

#### `announcement_views`
```sql
- id (bigint)
- announcement_id (foreign key)
- user_id (foreign key)
- viewed_at (timestamp)
- timestamps
- UNIQUE(announcement_id, user_id)
```

### **Role Mapping**
```
0 = Instructor
1 = Chairperson
2 = Dean
3 = Admin
4 = GE Coordinator
5 = VPAA
```

### **API Endpoints**

#### Get Active Announcements (AJAX)
```javascript
GET /announcements/active
Response: Array of announcement objects
```

#### Mark as Viewed (AJAX)
```javascript
POST /announcements/{id}/view
Response: { success: true }
```

---

## Customization

### **Changing Popup Position**

Edit `resources/views/components/announcement-popup.blade.php`:

```css
.announcement-popup {
    /* Current: bottom-right */
    right: 20px;
    bottom: 20px;
    
    /* Alternative: top-right */
    /* right: 20px; */
    /* top: 80px; */
}
```

### **Changing Auto-Dismiss Duration**

Edit `resources/views/components/announcement-popup.blade.php`:

```javascript
// Current: 10 seconds for low/normal
setTimeout(() => {
    this.dismissAnnouncement(announcement.id, index);
}, 10000); // Change to 15000 for 15 seconds
```

### **Changing Colors/Styles**

Edit gradient colors in `announcement-popup.blade.php`:

```css
.announcement-popup.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

---

## Best Practices

### ✅ **DO:**
- Use clear, concise titles
- Set appropriate priority levels
- Use "Show once" for one-time announcements
- Set end dates for time-sensitive announcements
- Test announcements before activating

### ❌ **DON'T:**
- Create too many active announcements (max 3-4)
- Use "Urgent" priority for non-critical messages
- Forget to set end dates for temporary announcements
- Make announcements non-dismissible unless critical

---

## Troubleshooting

### **Announcements Not Showing**

1. Check if announcement is active
2. Verify dates (start_date <= now <= end_date)
3. Check target_roles matches your user role
4. Clear browser cache
5. Check browser console for JavaScript errors

### **Announcement Shows Multiple Times**

- If `show_once = true`, check `announcement_views` table
- Clear views: `DELETE FROM announcement_views WHERE announcement_id = X`

### **Styling Issues**

- Ensure Alpine.js is loaded (check browser console)
- Check for CSS conflicts
- Test on different screen sizes

---

## Future Enhancements

Potential improvements:
- 📧 Email notifications for urgent announcements
- 📊 Advanced analytics (click-through rates)
- 🎨 Rich text editor for messages
- 📱 Push notifications (mobile)
- 🔔 In-app notification center
- 📅 Recurring announcements
- 👥 User-specific announcements

---

## Support

For issues or questions:
1. Check this documentation
2. Review console logs for errors
3. Test with sample data using `AnnouncementSeeder`
4. Contact system administrator

---

**Created**: January 6, 2026  
**Version**: 1.0  
**Status**: ✅ Production Ready
