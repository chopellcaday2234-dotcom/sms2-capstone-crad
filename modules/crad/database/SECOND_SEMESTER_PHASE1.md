# CRAD Second-Semester Phase 1

Phase 1 adds the semester and workflow foundation without deleting existing CRAD data.

## What is included

- Academic-year and semester records with Draft, Active, and Closed states
- One controlled active term at a time
- Explicit research-group carry-over into the active 2nd Semester
- Per-term group status and current workflow phase
- Append-only workflow event history
- Foundations for final-draft readiness, defense attempts, official results, and revision evidence
- Repair for final-phase tables imported without `AUTO_INCREMENT`

## Upgrade an existing CRAD database

From the project root, run:

```text
C:\xampp\php\php.exe modules\crad\database\migrate_second_semester.php
```

The migration is idempotent. It can be rerun safely after an interrupted attempt.

An authorized CRAD Officer, Admin, or Super Admin may also open:

```text
/modules/crad/database/migrate_second_semester.php
```

and use the protected migration form.

## Fresh installation

Run the existing CRAD installer. It now applies the Phase 1 migration automatically:

```text
C:\xampp\php\php.exe modules\crad\database\install.php
```

## Correct start-of-semester operation

1. A CRAD Officer/Admin creates a term in **Academic Term Management**.
2. The previous Active term must be closed before the new term can be activated.
3. Activate the new **2nd Semester** term.
4. In **Second Semester Transition**, confirm each continuing group individually.
5. Each confirmed group begins at **Second Semester Intake**.
6. After intake confirmation, advance the group to **Final Documentation**.

The system rejects direct phase skipping and records each accepted transition in `research_workflow_events`.

## Verification

Run the standalone workflow checks:

```text
C:\xampp\php\php.exe modules\crad\tests\second_semester_workflow_test.php
```
