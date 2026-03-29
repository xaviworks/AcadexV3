---
layout: default
title: GE Migration Command Decommission
nav_order: 8
description: "Completed retirement record for temporary GE migration artisan commands"
---

# GE Migration Command Decommission
{: .fs-9 }

Completed retirement record for temporary GE migration commands after canonical GE registration cutover.
{: .fs-6 .fw-300 }

---

## Status

Decommission completed. The following commands are retired from this codebase:

1. `ge:migration:audit`
2. `ge:migration:normalize-registration`
3. `ge:migration:rollout`

---

## Why They Were Removed

These commands were temporary migration tooling. After hard canonical enforcement, keeping them would add maintenance overhead and stale operator pathways.

---

## Current Verification Path

Use canonical feature coverage instead of migration commands:

```bash
php artisan test tests/Feature/GECanonicalRegistrationModeTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/GEProgramUnderASEPresentationTest.php
```

---

## Rollback Checklist

If post-removal incidents occur:

1. Revert to the last release/tag containing migration commands.
2. Run:

```bash
php artisan optimize:clear
php artisan test tests/Feature/GECanonicalRegistrationModeTest.php tests/Feature/Auth/RegistrationTest.php
```

3. If needed, run legacy migration commands only on that reverted tag.

4. Capture incident notes and update cutover docs before re-attempting retirement.

---

## Retirement Verification Suite

Used during this retirement implementation:

```bash
php artisan test tests/Feature/GECanonicalRegistrationModeTest.php tests/Feature/Auth/RegistrationTest.php
```
