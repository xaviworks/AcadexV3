# Admin Two-Factor Authentication Security Guide

## Should Admins Use 2FA?

**YES - Strongly Recommended!** Admin accounts have elevated privileges and should always use 2FA for maximum security.

## Security Concerns & Solutions

### Problem: Admin Locked Out
**What if an admin loses their phone/authenticator and can't login?**

### Solution 1: Recovery Codes (PRIMARY METHOD) 
When enabling 2FA, **8 recovery codes** are automatically generated. Each code can be used once as a backup.

**How to use:**
1. Admin enables 2FA
2. **IMPORTANT:** View and save recovery codes immediately
3. Store codes securely (password manager, safe place)
4. If locked out, use a recovery code instead of the authenticator code

**To view recovery codes:**
- Profile → Two Factor Authentication → Click "Show Recovery Codes"
- Enter password to reveal codes
- Save them in a secure location

**To regenerate recovery codes:**
- Profile → Two Factor Authentication → Click "Regenerate Recovery Codes"
- Old codes will be invalidated

---

### Solution 2: Emergency CLI Reset (BACKUP METHOD) 
If recovery codes are lost AND admin is locked out, use this command:

```bash
php artisan user:reset-2fa admin@example.com
```

**What it does:**
- Disables 2FA for the specified user
- Clears all trusted devices
- Logs the action in user_logs
- Requires server/console access

**Example:**
```bash
$ php artisan user:reset-2fa admin@acadex.com

Are you sure you want to reset 2FA for Admin User (admin@acadex.com)? (yes/no) [no]:
> yes

✓ Two-factor authentication has been reset for Admin User
✓ All trusted devices have been cleared
⚠ The user should re-enable 2FA for security
```

**Requirements:**
- SSH/terminal access to the server
- Proper Laravel environment configured
- Database connection active

---

### Solution 3: Another Admin Can Reset 
Another admin can reset 2FA through the admin panel:

1. Go to **Session & Activity Monitor**
2. Find the locked admin in **Active Sessions**
3. Click the **Reset 2FA** button (🛡️)
4. Enter your admin password
5. Target admin's 2FA is disabled

**Note:** Admins **cannot** reset their own 2FA through this method (security measure).

---

### Solution 4: Database Direct Reset (LAST RESORT)
If no other method works, manually update the database:

```sql
UPDATE users 
SET two_factor_secret = NULL,
    two_factor_recovery_codes = NULL,
    two_factor_confirmed_at = NULL
WHERE email = 'admin@example.com';

DELETE FROM user_devices WHERE user_id = (SELECT id FROM users WHERE email = 'admin@example.com');
```

**Use only when:**
- No server access for CLI command
- No other admins available
- Recovery codes lost
- Emergency situation

---

## Best Practices

### DO:
1. **Enable 2FA immediately** after account creation
2. **Save recovery codes** in a secure password manager
3. **Test recovery codes** before storing them away
4. **Have multiple admins** with 2FA enabled
5. **Document the emergency reset process** for your team
6. **Regenerate recovery codes** periodically (every 6 months)
7. **Keep backup authenticator apps** (e.g., Authy synced across devices)

### DON'T:
1. Store recovery codes in plain text files on your computer
2. Share recovery codes with others
3. Screenshot recovery codes and store in cloud services
4. Ignore the recovery codes after enabling 2FA
5. Use the same authenticator app without backup/sync
6. Remove all other admins who can help reset 2FA

---

## Emergency Preparedness Checklist

- [ ] At least 2 admins have 2FA enabled
- [ ] All admins have saved their recovery codes securely
- [ ] Server access credentials are documented for CLI reset
- [ ] Database access is available for emergency manual reset
- [ ] Team knows the process to request 2FA reset
- [ ] Recovery codes are tested and verified working
- [ ] Backup authenticator app (Authy) is configured with sync

---

## Security Hierarchy (Admin Lockout Prevention)

```
┌─────────────────────────────────────┐
│  1. Use Recovery Code (Fastest)     │ ← Primary Method
├─────────────────────────────────────┤
│  2. Another Admin Resets via Panel  │ ← Best Practice
├─────────────────────────────────────┤
│  3. CLI Command (Server Access)     │ ← IT Department
├─────────────────────────────────────┤
│  4. Database Direct Edit (DBA)      │ ← Last Resort
└─────────────────────────────────────┘
```

---

## Related Files
- Command: `app/Console/Commands/ResetUserTwoFactor.php`
- Controller: `app/Http/Controllers/AdminController.php` (reset2FA method)
- Migration: `database/migrations/*_add_two_factor_and_user_devices_tables.php`

---

## Support

If you need help with 2FA issues, contact your system administrator or IT support with:
1. User email address
2. Description of the issue
3. Whether recovery codes were saved
4. Last successful login timestamp
