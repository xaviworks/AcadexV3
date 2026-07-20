# Seeded Account Remediation

Use this checklist after deploying the seeder hardening to handle accounts that may already exist with public default passwords.

1. Identify seeded accounts by known seeder email patterns such as `admin@brokenshire.edu.ph`, `admin1@brokenshire.edu.ph` through `admin5@brokenshire.edu.ph`, `vpaa@brokenshire.edu.ph`, `gecoordinator@brokenshire.edu.ph`, and demo role accounts.
2. For each account, confirm whether it is a legitimate production account before changing status or access.
3. Immediately reset passwords for retained accounts using the normal password reset flow or an administrator-controlled secure reset.
4. Set `must_change_password` to `true` for any retained account that receives a temporary password.
5. Disable seeded accounts that are not legitimate production users instead of blindly deleting them.
6. Revoke active sessions and trusted devices for remediated users from the admin session management screen.
7. Revoke any authentication tokens associated with affected users if token-based access is enabled.
8. Review audit logs and user logs for suspicious successful or failed logins involving seeded account email addresses.
9. Keep `ALLOW_PRIVILEGED_ACCOUNT_SEEDING=false` for ordinary deployments.
