# CRAD 20-Step Flow Consistency Audit

Audit date: 2026-08-21  
Reference: `CRAD_Overall_Flow_With_1st_2nd_Semester.md`  
Method: code, schema, role, status, and page-routing review. This is a consistency report only; items explicitly assigned to other team members were not changed.

## Status legend

- **Implemented** — the core role, records, gate, and user path exist and follow the reference flow.
- **Partial** — a working foundation exists, but an important rule, status mapping, evidence link, or end-to-end gate remains.
- **Excluded / other team** — intentionally not audited or changed under the agreed scope.

## Audit results

| Step | Reference flow | Status | Current implementation and finding |
|---:|---|---|---|
| 1 | Student submits title proposal | **Implemented** | Student submission is handled by `modules/student-portal/pages/research-proposal-submission.php`; proposal/title records and tracking pages exist. |
| 2 | Coordinator/CRAD reviews title | **Partial** | CRAD review and registration pages exist (`proposal-review.php`, `register-proposal.php`). The working vocabulary uses values such as `Pending`, `Reviewed`, `Approved`, and `Returned`; the reference names `APPROVED`, `REJECTED`, and `FOR REVISION`. The status meaning must be normalized in a later phase so reports and UI use one official vocabulary. |
| 3 | Coordinator/CRAD assigns adviser | **Implemented** | Adviser availability, lookup, assignment, confirmation, and assignment management are backed by `research_adviser_assignments`. |
| 4 | Research coordinator management | **Implemented** | Coordinator assignments are handled through `research-coordinator-management.php` and `research_coordinator_assignments`, with restricted CRAD/admin access. |
| 5 | Official research-group registry | **Implemented** | Group number, title, students, adviser, coordinator, academic year, and term are represented through `research_groups`, proposal-member records, and the assignment tables. Registry and group-number pages are present. |
| 6 | Development and progress monitoring | **Implemented** | Plans, milestones, updates, feedback, attachments, activity, notifications, student views, and adviser monitoring pages are connected. Milestone statuses support the review/revision cycle. |
| 7 | Chapters 1–3 submission | **Implemented** | The grammarian/document track remains deliberately limited to Chapters 1–3. Submission history and the statuses `Submitted`, `Under Review`, `Needs Revision`, and `Accepted` are supported. |
| 8 | Chapters 1–3 evaluation | **Partial / documented role difference** | Evaluation records and faculty scoring/history pages exist. Current authorization is the assigned grammarian role. The reference document says adviser/evaluator/authorized panel, while the agreed scope explicitly preserves the existing grammarian track. The official process document should name the grammarian role to match the system. |
| 9 | Pre-Oral defense scheduling | **Excluded / other team** | Defense scheduling is owned by another team and was not changed or audited. |
| 10 | Pre-Oral panel evaluation | **Implemented foundation** | Panel assignments, schedule linkage, per-panel scoring, and the outcomes `APPROVED`, `APPROVED WITH REVISION`, and `FAILED` exist. Scheduling itself remains outside this audit. |
| 11 | Adviser monitoring, revisions, Chapters 4–5, system/testing/documentation | **Implemented for adviser progress flow** | Adviser-approved chapter milestones now support Chapters 1–5. Chapters 4–5 were not added to the grammarian track. Progress updates, milestone feedback, panel remarks, attachments, and first-to-second-semester readiness records remain available. |
| 12 | Adviser recommends group for Final Defense | **Implemented** | `final_defense_recommendations` is now the single live source of truth. Recommendation is term-aware, adviser-authorized, auditable, and moves the group to official manuscript submission—not directly to scheduling. Legacy `research_plans` recommendation fields are migration input only. |
| 13 | Official Chapters 1–5 manuscript submission | **Implemented** | A separate post-recommendation submission stage now stores immutable versions in `manuscript_submissions`, checks the active second-semester term and recommendation, records checksum/version lineage, and routes the group to evaluation. It is distinct from the pre-recommendation final-draft review. |
| 14 | Full manuscript evaluation | **Implemented** | Authorized CRAD/coordinator/adviser evaluators use the official manuscript review page. `APPROVED` routes to the scheduling handoff; `FOR REVISION` routes back to submission for a new immutable version. Evaluation history is retained. |
| 15 | Final Defense scheduling | **Excluded / other team** | Scheduling was not changed. Required handoff contract: the scheduling owner must enforce `fpIsManuscriptApproved($crad, $groupId)` server-side before creating or activating a Final Defense schedule. |
| 16 | Final Defense panel evaluation | **Implemented** | Per-panel scoring is linked to an immutable defense attempt. The official result remains `UNRESOLVED` until all assigned panel members submit, then uses deterministic precedence: `FAILED` over `PASSED WITH REVISIONS` over `PASSED`. Mixed results are supported, the finalized basis is preserved, and a failed attempt creates a `Ready for Scheduling` Re-Defense handoff without changing the scheduling module. |
| 17 | Revision compliance | **Partial — critical before final sign-off** | Revision-cycle records and adviser monitoring exist, and mixed `PASSED WITH REVISIONS` outcomes now enter the revision path with dedicated panel revision items. Current student evidence is still inferred from a general progress update after the cycle opened. A dedicated revision-submission/evidence upload and item-by-item compliance link are still needed. |
| 18 | Final manuscript approval | **Partial — critical before final sign-off** | Final approval is gated by manuscript evaluation and defense/revision completion. The approval write does not yet pin the exact manuscript submission/version, defense attempt, and checksum/token even though schema fields are available. This must be corrected for traceability and audit safety. |
| 19 | Publication and repository | **Implemented foundation** | Publication creation/management and gating exist. The research repository now reads real `publications` and `research_groups` records, with live metrics, search, status/access filters, and safe DOI/publication links; the unreachable static sample catalogue was removed. |
| 20 | Monitoring and reporting | **Excluded / other team** | Analytics, dashboards, alerts, and reporting are owned by another team and were not changed or audited. |

## Phase 3 conclusion

Phase 3 is complete for the agreed scope:

1. Chapters 4–5 are supported in the adviser milestone path only.
2. Final Defense Recommendation has one live source of truth.
3. Recommendation leads to an official, versioned Chapters 1–5 submission.
4. Approval/revision evaluation loops are enforced server-side.
5. The scheduling handoff is explicit without modifying the other team's scheduler.
6. The repository displays real publication data.
7. Role-delegated pages were checked to avoid rebuilding duplicate implementations.

## Remaining work before the whole CRAD module is teacher-ready

1. Normalize the proposal review status vocabulary or formally map `Returned` to `FOR REVISION` and add a distinct rejection state.
2. Connect revision compliance to dedicated submitted evidence/version records instead of inferring it from any later progress update.
3. Pin final manuscript approval to the exact manuscript submission, defense attempt, checksum, and immutable token.
4. Obtain and verify the scheduling team's server-side manuscript-approval and Re-Defense attempt handoff gates.
5. Perform the final role-by-role browser/UAT pass using representative student, adviser, grammarian/panel, coordinator, and CRAD accounts.

## Scope clarifications

- AI Document Analysis, Defense Scheduling, and Monitoring & Reporting remain assigned to other team members.
- Grants are supplementary and are not one of the official 20 CRAD lifecycle steps in the supplied reference flow.
- A page being present is not treated as proof of completion; each final sign-off item must have a server-side authorization check, a persisted status/evidence record, and an auditable transition.
