<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/second-semester-workflow.php';

$assertions = 0;

function cradTestAssert(bool $condition, string $message): void
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

cradTestAssert(
    cradAcademicTermCode('2026-2027', '2nd Semester') === '2026-2027-2',
    'Second-semester term code should be deterministic.'
);
cradTestAssert(
    cradWorkflowCanTransition('Second Semester Intake', 'Final Documentation'),
    'Intake should proceed to Final Documentation.'
);
cradTestAssert(
    !cradWorkflowCanTransition('Second Semester Intake', 'Final Defense'),
    'A group must not skip directly from intake to Final Defense.'
);

$incomplete = cradAggregateFinalDefenseResult(['APPROVED', 'APPROVED WITH REVISION'], 3);
cradTestAssert(!$incomplete['complete'], 'Two of three panel submissions should be incomplete.');
cradTestAssert($incomplete['official_result'] === 'UNRESOLVED', 'Incomplete panel results must stay unresolved.');
cradTestAssert(
    $incomplete['provisional_result'] === 'PASSED WITH REVISIONS',
    'Incomplete results may show a non-official provisional outcome.'
);

$passed = cradAggregateFinalDefenseResult([
    ['result' => 'APPROVED', 'overall_score' => 90],
    ['result' => 'PASSED', 'overall_score' => 88],
    ['result' => 'PASS', 'overall_score' => 92],
], 3);
cradTestAssert($passed['complete'], 'All assigned panel members submitted.');
cradTestAssert($passed['official_result'] === 'PASSED', 'All-pass decisions should aggregate to PASSED.');
cradTestAssert($passed['average_score'] === 90.0, 'Average panel score should be calculated correctly.');

$withRevisions = cradAggregateFinalDefenseResult([
    'APPROVED',
    'APPROVED WITH REVISION',
    'APPROVED',
], 3);
cradTestAssert(
    $withRevisions['official_result'] === 'PASSED WITH REVISIONS',
    'A mixed pass/revision decision should require revisions.'
);

$failed = cradAggregateFinalDefenseResult([
    'APPROVED WITH REVISION',
    'FAILED',
    'APPROVED',
], 3);
cradTestAssert($failed['official_result'] === 'FAILED', 'Any FAILED decision should take precedence.');

cradTestAssert(
    cradWorkflowCanTransition('Final Defense', 'Final Manuscript Approval'),
    'A passed Final Defense should proceed to final manuscript approval.'
);
cradTestAssert(
    cradWorkflowCanTransition('Final Defense', 'Post-Defense Revision'),
    'A passed-with-revisions Final Defense should proceed to revision.'
);
cradTestAssert(
    cradWorkflowCanTransition('Final Defense', 'Final Defense Scheduling'),
    'A failed Final Defense should return to the re-defense scheduling handoff.'
);
cradTestAssert(
    !cradWorkflowCanTransition('Final Defense', 'Completed'),
    'A Final Defense result must not skip final approval and publication steps.'
);

echo '[OK] ' . $assertions . ' second-semester workflow assertions passed.' . PHP_EOL;
