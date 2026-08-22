<?php
declare(strict_types=1);

ini_set('session.save_path', sys_get_temp_dir());

require_once __DIR__ . '/../includes/research-progress-helpers.php';
require_once __DIR__ . '/../includes/second-semester-workflow.php';
require_once __DIR__ . '/../includes/chapter-evaluation-workflow.php';

$assertions = 0;
function phase3Assert(bool $condition, string $message): void
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

$chapterNames = rpChapterMilestoneNames();
phase3Assert(array_keys($chapterNames) === [1, 2, 3, 4, 5], 'The adviser milestone lookup must cover Chapters 1–5.');
phase3Assert($chapterNames[4] === 'Chapter 4', 'Chapter 4 must have a non-blank label.');
phase3Assert($chapterNames[5] === 'Chapter 5', 'Chapter 5 must have a non-blank label.');
phase3Assert(
    array_keys(chapterAllowedChapters()) === [1, 2, 3],
    'The Grammarian submission/evaluation track must remain Chapters 1–3 only.'
);

phase3Assert(
    cradWorkflowCanTransition('Final Defense Readiness', 'Final Manuscript Submission'),
    'Recommendation must unlock official manuscript submission.'
);
phase3Assert(
    !cradWorkflowCanTransition('Final Defense Readiness', 'Final Defense Scheduling'),
    'Recommendation alone must not unlock scheduling.'
);
phase3Assert(
    cradWorkflowCanTransition('Final Manuscript Submission', 'Final Manuscript Evaluation'),
    'An official submission must proceed to evaluation.'
);
phase3Assert(
    cradWorkflowCanTransition('Final Manuscript Evaluation', 'Final Manuscript Submission'),
    'A revision result must return to official submission.'
);
phase3Assert(
    cradWorkflowCanTransition('Final Manuscript Evaluation', 'Final Defense Scheduling'),
    'An approved official manuscript may proceed to scheduling.'
);

$researchHelpers = file_get_contents(__DIR__ . '/../includes/research-progress-helpers.php');
$readinessHelpers = file_get_contents(__DIR__ . '/../includes/final-readiness-helpers.php');
$studentPage = file_get_contents(__DIR__ . '/../../student-portal/pages/final-manuscript.php');
$defenseSchedulingPage = file_get_contents(__DIR__ . '/../pages/research-defense-scheduling.php');
$panelAssignmentPage = file_get_contents(__DIR__ . '/../pages/adviser-panel-assignment.php');
$progressUpdatesPage = file_get_contents(__DIR__ . '/../../student-portal/pages/progress-updates.php');
phase3Assert($researchHelpers !== false, 'Research progress helpers must be readable.');
phase3Assert($readinessHelpers !== false, 'Final readiness helpers must be readable.');
phase3Assert($studentPage !== false, 'Student final documentation page must be readable.');
phase3Assert($defenseSchedulingPage !== false, 'Defense scheduling page must be readable.');
phase3Assert($panelAssignmentPage !== false, 'Panel assignment page must be readable.');
phase3Assert($progressUpdatesPage !== false, 'Student progress-updates page must be readable.');
phase3Assert(
    !str_contains((string) $readinessHelpers, 'UPDATE research_plans SET final_defense_recommended'),
    'Readiness actions must not write the legacy recommendation columns.'
);
phase3Assert(
    str_contains((string) $researchHelpers, 'FROM final_defense_recommendations fdr'),
    'The compatibility recommendation reader must use the dedicated table.'
);
phase3Assert(
    str_contains((string) $studentPage, 'name="submission_stage" value="official_manuscript"'),
    'The student page must expose a distinct official post-recommendation submission action.'
);
phase3Assert(
    str_contains((string) $studentPage, 'fpIsRecommendedForFinalDefense'),
    'The official manuscript controller must enforce Final Defense Recommendation.'
);
phase3Assert(
    !preg_match(
        '/DELETE\s+FROM\s+research_defense_schedules/i',
        (string) $defenseSchedulingPage . "\n" . (string) $panelAssignmentPage
    ),
    'Viewing scheduling and assignment pages must never delete retained defense records.'
);
phase3Assert(
    str_contains((string) $defenseSchedulingPage, 'UPDATE research_defense_schedules rds')
        && str_contains((string) $panelAssignmentPage, 'UPDATE research_defense_schedules rds'),
    'Scheduling pages must repair missing research-group links without deleting records.'
);
phase3Assert(
    !str_contains((string) $defenseSchedulingPage, 'FROM research_defense_schedules rds' . "\n            JOIN research_proposals"),
    'Retained schedules must remain visible after the legacy proposal row is superseded.'
);
phase3Assert(
    str_contains((string) $panelAssignmentPage, 'status = research_defense_schedules.status'),
    'Re-recording an assignment must not reset a completed defense schedule.'
);
phase3Assert(
    str_contains((string) $panelAssignmentPage, "COALESCE(NULLIF(VALUES(panel_members), ''), research_defense_schedules.panel_members)"),
    'Re-recording an assignment must not erase the assigned panel names.'
);
phase3Assert(
    str_contains((string) $progressUpdatesPage, "const firstBrace = text.indexOf('{');"),
    'Progress submission must recover valid JSON when local PHP emits an upload warning prefix.'
);

echo '[OK] ' . $assertions . ' Phase 3 manuscript-flow assertions passed.' . PHP_EOL;
