# CRAD Second-Semester Phase 2

Phase 2 implements the mandatory final-draft and readiness gates before a research group can be recommended for Final Defense.

## Enforced workflow

1. The group must be enrolled in the active 2nd Semester and be in **Final Documentation**.
2. The student leader submits an immutable consolidated Chapters 1–5 draft.
3. The group moves to **Final Draft Adviser Review**.
4. The assigned adviser checks Chapters 1–5, formatting, and citations.
5. A revision decision returns the group to **Final Documentation** and permits a new version.
6. An endorsement moves the group to **Final Defense Readiness**.
7. CRAD verifies ethics clearance, similarity checking, and required supporting documents.
8. The formal adviser recommendation is enabled only when every requirement is complete.
9. A successful recommendation moves the group to **Final Manuscript Submission**. Phase 3 then requires official manuscript evaluation before scheduling.

## Audit and integrity rules

- Uploaded final-draft versions are immutable and linked through `supersedes_submission_id`.
- Every stored draft has a SHA-256 checksum.
- Adviser decisions are recorded in `final_draft_reviews`.
- Readiness checks are versioned; an older Ready check becomes Revoked when superseded.
- Recommendations pin the exact draft, adviser review, and readiness check.
- Recommendation revocation requires a reason and is written to the workflow event history.
- Revocation is blocked after an active Final Defense schedule exists.

## User pages

- Student: **Final Documentation** (`modules/student-portal/pages/final-manuscript.php`)
- Adviser: **Final Draft Review** (`modules/faculty/pages/final-draft-review.php`)
- CRAD/Research Coordinator: **Final Defense Readiness** (`modules/crad/pages/final-defense-readiness.php`)

## Database upgrade

Configure valid CRAD MySQL credentials and run:

```text
C:\xampp\php\php.exe modules\crad\database\migrate_second_semester.php
```

The migration is idempotent and preserves existing records.

## Verification

```text
C:\xampp\php\php.exe modules\crad\tests\second_semester_workflow_test.php
C:\xampp\php\php.exe modules\crad\tests\final_readiness_test.php
C:\xampp\php\php.exe modules\crad\tests\phase3_manuscript_flow_test.php
C:\xampp\php\php.exe modules\crad\tests\second_semester_schema_integration_test.php
```

The schema integration test uses a temporary database and skips cleanly when valid MySQL credentials are unavailable.
