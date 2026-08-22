<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/final-readiness-helpers.php';

$assertions = 0;
function frTestAssert(bool $condition, string $message): void
{
    global $assertions;
    $assertions++;
    if (!$condition) {
        throw new RuntimeException('Assertion failed: ' . $message);
    }
}

$draft = ['status' => 'Endorsed'];
$review = [
    'decision' => 'Endorsed',
    'chapter_1_accepted' => 1,
    'chapter_2_accepted' => 1,
    'chapter_3_accepted' => 1,
    'chapter_4_accepted' => 1,
    'chapter_5_accepted' => 1,
    'formatting_accepted' => 1,
    'citations_accepted' => 1,
];
$check = [
    'ethics_clearance_complete' => 1,
    'similarity_check_complete' => 1,
    'required_documents_complete' => 1,
    'overall_status' => 'Ready',
];

$ready = frEvaluateReadinessRequirements($draft, $review, $check);
frTestAssert($ready['ready'], 'All adviser and CRAD requirements should be ready.');
frTestAssert($ready['missing'] === [], 'A ready record should have no missing requirements.');
frTestAssert(count($ready['requirements']) === 11, 'The gate should evaluate all eleven requirements.');

$missingChapterReview = $review;
$missingChapterReview['chapter_5_accepted'] = 0;
$missingChapter = frEvaluateReadinessRequirements($draft, $missingChapterReview, $check);
frTestAssert(!$missingChapter['ready'], 'A missing Chapter 5 acceptance must block recommendation.');
frTestAssert(
    in_array('Chapter 5 adviser acceptance', $missingChapter['missing'], true),
    'The missing Chapter 5 requirement should be explained.'
);

$incompleteCheck = $check;
$incompleteCheck['overall_status'] = 'Incomplete';
$incomplete = frEvaluateReadinessRequirements($draft, $review, $incompleteCheck);
frTestAssert(!$incomplete['ready'], 'An incomplete CRAD checklist must block recommendation.');

$notEndorsed = frEvaluateReadinessRequirements(['status' => 'Revision Requested'], $review, $check);
frTestAssert(!$notEndorsed['ready'], 'A non-endorsed draft must block recommendation.');
frTestAssert(
    in_array('Formal adviser endorsement', $notEndorsed['missing'], true),
    'The missing formal endorsement should be explained.'
);

frTestAssert(
    cradWorkflowCanTransition('Final Draft Adviser Review', 'Final Defense Readiness'),
    'Endorsement should move the workflow to Final Defense Readiness.'
);
frTestAssert(
    cradWorkflowCanTransition('Final Defense Readiness', 'Final Manuscript Submission'),
    'A gated recommendation should move the workflow to official manuscript submission.'
);
frTestAssert(
    !cradWorkflowCanTransition('Final Defense Readiness', 'Final Defense Scheduling'),
    'A group must not skip official manuscript submission and evaluation.'
);

echo '[OK] ' . $assertions . ' final-readiness assertions passed.' . PHP_EOL;
