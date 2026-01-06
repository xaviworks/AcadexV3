# System Announcement - Modal Behavior

**Updated:** January 6, 2026  
**Type:** Blocking Modal Dialog

---

## Overview

Announcements now display as **blocking modal dialogs** that require user acknowledgment before they can interact with the system. This ensures important messages are seen and acknowledged.

---

## Key Features

### 🔒 **Blocking Behavior**

1. **Dark Backdrop Overlay**
   - Semi-transparent black overlay (50% opacity)
   - Backdrop blur effect (2px) for better focus
   - Prevents clicking on page content behind modal

2. **Must Acknowledge**
   - Users cannot interact with the page until announcement is closed
   - "Got it" or "Acknowledge" button to dismiss
   - ESC key works (only if announcement is dismissible)

3. **Centered Modal**
   - Appears in center of screen (50% top, 50% left)
   - Scale animation (95% → 100%)
   - Maximum width: 650px
   - Responsive on mobile

4. **Body Scroll Lock**
   - Page scrolling disabled when modal is open
   - Prevents confusion and ensures focus on announcement

---

## User Experience Flow

### **Single Announcement:**
```
Page loads → Backdrop appears → Modal fades in (center)
↓
User reads announcement
↓
User clicks "Got it" button (or presses ESC if dismissible)
↓
Modal fades out → Backdrop fades out → User can interact with page
```

### **Multiple Announcements:**
```
Page loads → Backdrop appears → First announcement shown
↓
User can navigate: [◄ Previous] [Next ►]
↓
Must dismiss each announcement or navigate through all
↓
After last dismissal → Page becomes interactive
```

---

## UI Components

### **1. Backdrop**
- Full screen overlay (`position: fixed; inset: 0`)
- Background: `rgba(0, 0, 0, 0.5)`
- Backdrop filter: `blur(2px)`
- Z-index: 9998
- Blocks all page interaction

### **2. Modal Dialog**
- Centered positioning
- Alert component (Bootstrap)
- Z-index: 9999
- Width: 90% (max 650px)
- Shadow: Large elevation
- Border radius: 12px (rounded-4)

### **3. Action Button**
- Color matches announcement type:
  - Info → Primary (blue)
  - Success → Success (green)
  - Warning → Warning (yellow)
  - Danger → Danger (red)
- Text: "Got it" (dismissible) or "Acknowledge" (non-dismissible)
- Icon: Checkmark

### **4. Close Button**
- Only shown if announcement is dismissible
- Larger size (1.25rem)
- Top-right corner
- Standard Bootstrap close button

---

## Keyboard Support

- **ESC Key**: Closes announcement (only if dismissible)
- **Tab**: Navigate between buttons
- **Enter/Space**: Activate focused button
- **Arrow Left/Right**: Could be added for previous/next (future)

---

## Accessibility

✅ **ARIA Attributes:**
- `role="alertdialog"` - Announces as modal dialog
- `aria-modal="true"` - Indicates modal behavior
- `aria-labelledby="announcement-title"` - Links to title

✅ **Keyboard Navigation:**
- ESC key support (if dismissible)
- Focus trap within modal
- Clear focus indicators

✅ **Screen Readers:**
- Semantic HTML structure
- Proper heading hierarchy
- Descriptive button labels

---

## Technical Implementation

### **Changes Made:**

1. **Removed auto-dismiss** - Users must acknowledge
2. **Added backdrop overlay** - Blocks page interaction
3. **Centered modal** - Better visibility and focus
4. **Added body scroll lock** - Prevents confusion
5. **Larger action button** - "Got it" / "Acknowledge"
6. **ESC key support** - Quick dismissal (if allowed)
7. **Scale animation** - Modern modal entrance

### **CSS:**
```css
/* Backdrop prevents interaction */
.announcement-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    z-index: 9998;
}

/* Modal centered */
.announcement-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
}

/* Body scroll lock */
body:has(.announcement-backdrop) {
    overflow: hidden;
}
```

---

## Behavior Comparison

| Aspect | Before | After |
|--------|--------|-------|
| Position | Top-center | Screen center |
| Backdrop | None | Dark overlay + blur |
| Page Interaction | Allowed | Blocked |
| Auto-dismiss | Yes (12s) | No - must acknowledge |
| Scroll | Page scrollable | Locked |
| Urgency | Passive | Active/Required |

---

## Use Cases

### ✅ **Perfect For:**
- 🚨 System maintenance notices
- 📢 Critical policy changes
- ⚠️ Security alerts
- 🎉 Important feature launches
- 📅 Deadline reminders
- 🔒 Terms of service updates

### ⚠️ **Not Ideal For:**
- Minor informational messages
- Optional tips or tutorials
- Frequent updates
- Non-critical notifications

---

## Admin Guidance

### **When Creating Announcements:**

1. **Keep it concise** - Users must read before continuing
2. **Clear title** - What is this about?
3. **Actionable message** - What should they know/do?
4. **Appropriate priority** - Urgent = truly urgent
5. **Target wisely** - Don't block everyone for role-specific info

### **Priority Guidelines:**

- **Urgent** → System-wide critical issues (outages, security)
- **High** → Important deadlines, policy changes
- **Normal** → General updates, feature announcements
- **Low** → Minor improvements, tips

---

## Testing Checklist

- [x] Modal appears centered on screen
- [x] Backdrop blocks page interaction
- [x] Cannot scroll page when modal is open
- [x] "Got it" button dismisses announcement
- [x] ESC key works (if dismissible)
- [x] Close button works (if dismissible)
- [x] Multiple announcements navigate correctly
- [x] Mobile responsive (95% width)
- [x] Animations smooth (scale + fade)
- [x] Screen reader compatible

---

## Performance

- **Fast rendering** - Minimal CSS
- **No auto-dismiss timers** - Cleaner JavaScript
- **Efficient transitions** - Hardware accelerated
- **Small payload** - Bootstrap native components

---

## Future Enhancements

Consider adding:

1. **Focus trap** - Keep Tab key within modal
2. **Animation preferences** - Respect `prefers-reduced-motion`
3. **Read tracking** - How long user viewed announcement
4. **Snooze option** - "Remind me later" (for non-urgent)
5. **Preview mode** - Admin can preview before publishing

---

## Troubleshooting

### **Modal doesn't appear:**
- Check browser console for errors
- Verify announcement is active and within date range
- Ensure user role is in target_roles
- Hard refresh (Cmd+Shift+R)

### **Can still click page behind modal:**
- Check backdrop z-index (should be 9998)
- Verify backdrop has `pointer-events: auto`
- Clear browser cache

### **ESC key doesn't work:**
- Check if announcement is dismissible
- Verify keyboard event listener is attached
- Test in different browser

---

## Summary

The announcement system now enforces **user acknowledgment** before allowing page interaction. This ensures:

✅ Important messages are seen  
✅ Users are aware of critical updates  
✅ Better compliance with system changes  
✅ Clear user intent (they clicked "Got it")  
✅ Professional, modal-based UI pattern

---

**Best Practice:** Use this for truly important announcements. For minor updates, consider alternative notification methods (toast, banner, email digest).
