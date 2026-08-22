# CRAD Second-Semester Phase 3

## Implemented flow

Phase 3 aligns the manuscript portion of the system with CRAD steps 12–14:

1. Chapters 1–5 are completed in the adviser progress track.
2. The student submits a consolidated final draft for formal adviser review.
3. CRAD completes the readiness checklist.
4. The adviser records the Final Defense Recommendation.
5. Only then may the student submit the official Chapters 1–5 manuscript.
6. CRAD or an authorized evaluator records the detailed manuscript evaluation.
7. `FOR REVISION` returns the workflow to official manuscript submission for a new immutable version.
8. `APPROVED` advances the workflow to Final Defense Scheduling.

The Grammarian `chapter_submissions` track and the Pre-Oral panel document track remain Chapters 1–3 only. They are intentionally separate from the Chapters 1–5 adviser track.

## Recommendation source of truth

`final_defense_recommendations` is the only live source of truth. Legacy recommendation columns on `research_plans` are no longer written. Existing positive legacy records are migrated only when the dedicated table has no record for that group.

## Ownership boundaries and handoff

AI Document Analysis, Defense Scheduling, and Monitoring & Reporting are owned by the other team and are not implemented in this phase.

The Defense Scheduling owner must enforce this contract before a Final Defense schedule is created or finalized:

```php
fpIsManuscriptApproved($crad, $researchGroupId) === true
```

The check must be performed on the server, not only by hiding a button. A group must not be scheduled from recommendation status alone.

## Grants and Funding scope

Grants and Funding pages are supplementary CRAD capabilities. They are not part of the official 20-step capstone lifecycle in `CRAD_Overall_Flow_With_1st_2nd_Semester.md`. They should be presented as an optional research-support feature, not as a required capstone completion step.

## Verified delegation

- `modules/faculty/pages/panel-final-defense-evaluation.php` delegates its database and evaluation rules to `modules/faculty/includes/final-defense-evaluation.php`.
- `modules/faculty/pages/final-defense-revision-monitoring.php` delegates final-phase queries and updates to `modules/crad/includes/final-phase-helpers.php`.
- `modules/crad/pages/research-repository.php` reads real `publications` and `research_groups` records and contains no sample repository dataset.
