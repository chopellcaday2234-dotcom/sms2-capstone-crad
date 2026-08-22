# CRAD Second-Semester Phase 4

## Implemented scope

Phase 4 aligns CRAD step 16, Final Defense Panel Evaluation, with the official second-semester workflow:

1. A panel member may submit only for an assigned, reached Final Defense or Re-Defense schedule.
2. Each evaluation remains immutable per schedule and panel member.
3. `APPROVED WITH REVISION` and `FAILED` require written panel remarks.
4. The schedule is linked to an immutable `defense_attempts` record.
5. The official result stays `UNRESOLVED` until every assigned panel member has submitted.
6. Complete results use deterministic precedence:
   - any `FAILED` → `FAILED`
   - otherwise, any `APPROVED WITH REVISION` → `PASSED WITH REVISIONS`
   - otherwise → `PASSED`
7. The result basis, panel count, submitted count, average score, finalizer, and finalization time are stored in `defense_official_results`.
8. `PASSED` advances to Final Manuscript Approval.
9. `PASSED WITH REVISIONS` opens Post-Defense Revision and creates dedicated revision items from the applicable panel remarks.
10. `FAILED` returns to the Final Defense Scheduling handoff and creates the next immutable Re-Defense attempt as `Ready for Scheduling`.

## Re-Defense scheduling handoff

Actual schedule creation remains owned by the Defense Scheduling team. After a failed official result, that team should select the latest `defense_attempts` record where:

```text
defense_type = Re-Defense
status = Ready for Scheduling
defense_schedule_id IS NULL
```

When the Re-Defense schedule is evaluated, Phase 4 automatically links it to that prepared attempt. No date, venue, room, or panel scheduling UI was changed in this phase.

## Downstream source of truth

Final approval and revision eligibility now read `defense_official_results`, not unanimous counts from `final_defense_evaluations`. This prevents mixed results from becoming stuck.

## Preserved boundaries

- Grammarian submission and evaluation remain Chapters 1–3 only.
- Chapters 4–5 remain in the adviser milestone/readiness path.
- AI Document Analysis, Defense Scheduling, and Monitoring & Reporting were not modified.
- No global CSS, JavaScript, typography, color, or shared layout file was changed.

## Verification target

The Phase 4 database integration test covers:

- incomplete results remaining unresolved;
- mixed approval/revision becoming `PASSED WITH REVISIONS`;
- revision item creation;
- failed precedence and Re-Defense handoff;
- unanimous approval becoming `PASSED`;
- downstream approval eligibility; and
- one official result and workflow audit event per completed attempt.
