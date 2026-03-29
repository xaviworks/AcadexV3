---
layout: default
title: GE Registration Cutover
nav_order: 7
description: "Operational runbook for canonical GE registration and approval handling"
---

# GE Registration Cutover
{: .fs-9 }

Operational guidance for the canonical GE-as-program-under-ASE registration model.
{: .fs-6 .fw-300 }

---

## Canonical Rule

General Education registrations are canonical only when:

1. Department is ASE
2. Program is GE

Legacy GE-department-only registration payloads are not eligible for GE coordinator approval.

---

## Approval Rejection Message

When a legacy payload reaches GE coordinator approval endpoints, the system now returns this explicit error:

"Legacy GE-department registration payloads are no longer eligible. Use ASE department with the GE program selection."

---

## Operator Verification

Use the canonical GE verification suite:

```bash
php artisan test tests/Feature/GECanonicalRegistrationModeTest.php tests/Feature/Auth/RegistrationTest.php tests/Feature/GEProgramUnderASEPresentationTest.php
```

---

## Remediation Checklist

If you encounter the rejection message above:

1. Verify the pending account selected the ASE department.
2. Verify the pending account selected the GE program.
3. Ask the instructor to resubmit using ASE + GE program.
4. Confirm the request appears in GE coordinator approvals queue.
5. Re-run the verification suite above if incident scope is unclear.

---

## Post-Cutover Verification

Success criteria:

1. GE coordinator approval queue only contains canonical GE program registrations.
2. Legacy GE-department payloads are rejected with the explicit message above.
3. Canonical GE feature tests remain green in CI.

---

## Retirement Planning

For command lifecycle retirement criteria and rollback procedure, see:

- [GE Migration Command Decommission](GE_MIGRATION_COMMAND_DECOMMISSION)
