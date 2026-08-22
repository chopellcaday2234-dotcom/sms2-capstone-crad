<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/second_semester_schema.php';

const CRAD_SECOND_SEMESTER_PHASES = [
    'Second Semester Intake',
    'Final Documentation',
    'Final Draft Adviser Review',
    'Final Defense Readiness',
    'Final Manuscript Submission',
    'Final Manuscript Evaluation',
    'Final Defense Scheduling',
    'Final Defense',
    'Post-Defense Revision',
    'Final Manuscript Approval',
    'Publication and Repository',
    'Completed',
];

/** @return array<string,string[]> */
function cradSecondSemesterAllowedTransitions(): array
{
    return [
        'Second Semester Intake' => ['Final Documentation'],
        'Final Documentation' => ['Final Draft Adviser Review'],
        'Final Draft Adviser Review' => ['Final Documentation', 'Final Defense Readiness'],
        'Final Defense Readiness' => ['Final Draft Adviser Review', 'Final Manuscript Submission'],
        'Final Manuscript Submission' => ['Final Defense Readiness', 'Final Manuscript Evaluation'],
        'Final Manuscript Evaluation' => ['Final Defense Readiness', 'Final Manuscript Submission', 'Final Defense Scheduling'],
        'Final Defense Scheduling' => ['Final Defense Readiness', 'Final Manuscript Submission', 'Final Manuscript Evaluation', 'Final Defense'],
        'Final Defense' => ['Post-Defense Revision', 'Final Manuscript Approval', 'Final Defense Scheduling'],
        'Post-Defense Revision' => ['Final Manuscript Approval', 'Final Defense Scheduling'],
        'Final Manuscript Approval' => ['Post-Defense Revision', 'Publication and Repository'],
        'Publication and Repository' => ['Completed'],
        'Completed' => [],
    ];
}

/**
 * Return the authoritative first-semester completion state for a group.
 *
 * The second-semester carry-over gate deliberately uses database evidence,
 * never a user-supplied checkbox: a completed Pre-Oral result plus all eight
 * adviser-track milestones at 100% and in a terminal accepted state.
 *
 * @return array{complete:bool,reason:string,completed_milestones:int,required_milestones:int,preoral_result:string}
 */
function cradGetFirstSemesterCompletionStatus(PDO $pdo, int $researchGroupId): array
{
    $result = [
        'complete' => false,
        'reason' => 'The first-semester research plan has not been created.',
        'completed_milestones' => 0,
        'required_milestones' => 8,
        'preoral_result' => '',
    ];

    if ($researchGroupId <= 0) {
        $result['reason'] = 'Research group not found.';
        return $result;
    }

    $planStatement = $pdo->prepare(
        'SELECT id FROM research_plans WHERE research_group_id = ? ORDER BY id DESC LIMIT 1'
    );
    $planStatement->execute([$researchGroupId]);
    $planId = (int) ($planStatement->fetchColumn() ?: 0);
    if ($planId <= 0) {
        return $result;
    }

    $milestoneStatement = $pdo->prepare(
        "SELECT COUNT(*) AS required_count,
                SUM(CASE WHEN status IN ('Approved','Completed') AND progress_percentage >= 100 THEN 1 ELSE 0 END) AS completed_count
         FROM research_milestones
         WHERE research_plan_id = ?
           AND milestone_order BETWEEN 1 AND 8
           AND LOWER(TRIM(milestone_name)) IN
               ('chapter 1','chapter 2','chapter 3','chapter 4','chapter 5','system development','testing','documentation')"
    );
    $milestoneStatement->execute([$planId]);
    $milestoneCounts = $milestoneStatement->fetch(PDO::FETCH_ASSOC) ?: [];
    $requiredCount = (int) ($milestoneCounts['required_count'] ?? 0);
    $completedCount = (int) ($milestoneCounts['completed_count'] ?? 0);
    $result['completed_milestones'] = $completedCount;

    if ($requiredCount !== 8 || $completedCount !== 8) {
        $result['reason'] = 'Complete and obtain adviser approval for all 8 first-semester milestones (Chapters 1-5, System Development, Testing, and Documentation).';
        return $result;
    }

    $scheduleStatement = $pdo->prepare(
        "SELECT id
         FROM research_defense_schedules
         WHERE research_group_id = ?
           AND LOWER(TRIM(COALESCE(defense_type, ''))) IN ('pre-oral','pre-oral defense')
           AND defense_datetime IS NOT NULL
           AND LOWER(TRIM(status)) IN ('scheduled','finalized','final','completed','passed','failed')
         ORDER BY defense_datetime DESC, id DESC LIMIT 1"
    );
    $scheduleStatement->execute([$researchGroupId]);
    $scheduleId = (int) ($scheduleStatement->fetchColumn() ?: 0);
    if ($scheduleId <= 0) {
        $result['reason'] = 'A completed Pre-Oral Defense record is required before second-semester carry-over.';
        return $result;
    }

    $assignedStatement = $pdo->prepare(
        "SELECT COUNT(DISTINCT panel_user_id)
         FROM research_panel_assignments
         WHERE research_group_id = ?
           AND defense_phase = 'Pre-Oral Defense'
           AND assignment_status = 'Assigned'"
    );
    $assignedStatement->execute([$researchGroupId]);
    $assignedCount = (int) $assignedStatement->fetchColumn();

    $evaluationStatement = $pdo->prepare(
        "SELECT result
         FROM preoral_defense_evaluations
         WHERE defense_schedule_id = ? AND status = 'Submitted'"
    );
    $evaluationStatement->execute([$scheduleId]);
    $evaluations = $evaluationStatement->fetchAll(PDO::FETCH_COLUMN) ?: [];
    if ($assignedCount <= 0 || count($evaluations) < $assignedCount) {
        $result['reason'] = 'All assigned panel members must submit their Pre-Oral evaluation first.';
        return $result;
    }

    if (in_array('FAILED', $evaluations, true)) {
        $result['preoral_result'] = 'FAILED';
        $result['reason'] = 'The latest Pre-Oral Defense result is FAILED. Complete the required re-defense process first.';
        return $result;
    }

    $result['preoral_result'] = in_array('APPROVED WITH REVISION', $evaluations, true)
        ? 'APPROVED WITH REVISION'
        : 'APPROVED';
    $result['complete'] = true;
    $result['reason'] = 'First-semester completion requirements are satisfied.';
    return $result;
}

function cradAcademicTermCode(string $academicYear, string $semester): string
{
    $semesterCodes = [
        '1st Semester' => '1',
        '2nd Semester' => '2',
        'Summer' => 'S',
    ];

    if (!isset($semesterCodes[$semester])) {
        throw new InvalidArgumentException('Invalid semester.');
    }

    return $academicYear . '-' . $semesterCodes[$semester];
}

/** @return array<string,mixed>|null */
function cradGetActiveAcademicTerm(PDO $pdo, ?string $semester = null): ?array
{
    $sql = "SELECT * FROM academic_terms WHERE status = 'Active'";
    $params = [];
    if ($semester !== null) {
        $sql .= ' AND semester = ?';
        $params[] = $semester;
    }
    $sql .= ' ORDER BY start_date DESC, id DESC LIMIT 1';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $term = $statement->fetch(PDO::FETCH_ASSOC);

    return $term ?: null;
}

function cradCreateAcademicTerm(
    PDO $pdo,
    string $academicYear,
    string $semester,
    ?string $startDate,
    ?string $endDate,
    ?int $actorUserId
): int {
    $academicYear = trim($academicYear);
    if (!preg_match('/^\d{4}-\d{4}$/', $academicYear)) {
        throw new InvalidArgumentException('Academic year must use YYYY-YYYY format.');
    }

    [$firstYear, $secondYear] = array_map('intval', explode('-', $academicYear));
    if ($secondYear !== $firstYear + 1) {
        throw new InvalidArgumentException('The ending academic year must be the next calendar year.');
    }

    if (!in_array($semester, ['1st Semester', '2nd Semester', 'Summer'], true)) {
        throw new InvalidArgumentException('Invalid semester.');
    }

    $startDate = $startDate !== null && $startDate !== '' ? $startDate : null;
    $endDate = $endDate !== null && $endDate !== '' ? $endDate : null;
    if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
        throw new InvalidArgumentException('End date cannot be earlier than start date.');
    }

    $statement = $pdo->prepare(
        'INSERT INTO academic_terms '
        . '(academic_year, semester, term_code, start_date, end_date, created_by_user) '
        . 'VALUES (?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $academicYear,
        $semester,
        cradAcademicTermCode($academicYear, $semester),
        $startDate,
        $endDate,
        $actorUserId,
    ]);

    return (int) $pdo->lastInsertId();
}

function cradActivateAcademicTerm(PDO $pdo, int $termId): void
{
    $pdo->beginTransaction();
    try {
        $targetStatement = $pdo->prepare('SELECT * FROM academic_terms WHERE id = ? FOR UPDATE');
        $targetStatement->execute([$termId]);
        $target = $targetStatement->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            throw new RuntimeException('Academic term not found.');
        }
        if ($target['status'] === 'Closed') {
            throw new RuntimeException('A closed academic term cannot be reactivated.');
        }

        $activeStatement = $pdo->prepare(
            "SELECT id, term_code FROM academic_terms WHERE status = 'Active' AND id <> ? FOR UPDATE"
        );
        $activeStatement->execute([$termId]);
        $active = $activeStatement->fetch(PDO::FETCH_ASSOC);
        if ($active) {
            throw new RuntimeException(
                'Close the current active term (' . $active['term_code'] . ') before activating another one.'
            );
        }

        $update = $pdo->prepare("UPDATE academic_terms SET status = 'Active' WHERE id = ?");
        $update->execute([$termId]);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function cradCloseAcademicTerm(PDO $pdo, int $termId, ?int $actorUserId): void
{
    $statement = $pdo->prepare(
        "UPDATE academic_terms SET status = 'Closed', closed_by_user = ?, closed_at = NOW() "
        . "WHERE id = ? AND status = 'Active'"
    );
    $statement->execute([$actorUserId, $termId]);
    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Only an active academic term can be closed.');
    }
}

/** @return array<string,mixed>|null */
function cradGetGroupTerm(PDO $pdo, int $researchGroupId, int $academicTermId): ?array
{
    $statement = $pdo->prepare(
        'SELECT * FROM research_group_terms WHERE research_group_id = ? AND academic_term_id = ? LIMIT 1'
    );
    $statement->execute([$researchGroupId, $academicTermId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function cradLogWorkflowEvent(
    PDO $pdo,
    int $researchGroupId,
    ?int $academicTermId,
    ?int $groupTermId,
    string $eventType,
    ?string $fromPhase,
    ?string $toPhase,
    ?int $actorUserId,
    string $actorName,
    ?string $remarks = null,
    ?string $entityType = null,
    ?int $entityId = null,
    array $metadata = []
): int {
    $statement = $pdo->prepare(
        'INSERT INTO research_workflow_events '
        . '(research_group_id, academic_term_id, group_term_id, event_type, from_phase, to_phase, '
        . 'entity_type, entity_id, actor_user_id, actor_name, remarks, metadata_json) '
        . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        $researchGroupId,
        $academicTermId,
        $groupTermId,
        $eventType,
        $fromPhase,
        $toPhase,
        $entityType,
        $entityId,
        $actorUserId,
        trim($actorName),
        $remarks,
        $metadata !== [] ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
    ]);
    $eventId = (int) $pdo->lastInsertId();

    if ($toPhase !== null
        && trim($toPhase) !== ''
        && cradSecondSemesterTableExists($pdo, 'research_plans')
        && cradSecondSemesterColumnExists($pdo, 'research_plans', 'current_stage')) {
        $stageStatement = $pdo->prepare(
            'UPDATE research_plans SET current_stage = ?, updated_at = NOW() WHERE research_group_id = ?'
        );
        $stageStatement->execute([trim($toPhase), $researchGroupId]);
    }

    return $eventId;
}

function cradEnrollGroupInTerm(
    PDO $pdo,
    int $researchGroupId,
    int $academicTermId,
    string $enrollmentType,
    string $startingPhase,
    ?int $actorUserId,
    string $actorName,
    ?string $remarks = null
): int {
    $allowedEnrollmentTypes = ['New', 'Continuing', 'Carry-over', 'Repeat'];
    if (!in_array($enrollmentType, $allowedEnrollmentTypes, true)) {
        throw new InvalidArgumentException('Invalid enrollment type.');
    }
    if (!in_array($startingPhase, CRAD_SECOND_SEMESTER_PHASES, true)) {
        throw new InvalidArgumentException('Invalid starting workflow phase.');
    }

    $pdo->beginTransaction();
    try {
        $existing = cradGetGroupTerm($pdo, $researchGroupId, $academicTermId);
        if ($existing) {
            $pdo->commit();
            return (int) $existing['id'];
        }

        $groupStatement = $pdo->prepare('SELECT id FROM research_groups WHERE id = ? FOR UPDATE');
        $groupStatement->execute([$researchGroupId]);
        if (!$groupStatement->fetchColumn()) {
            throw new RuntimeException('Research group not found.');
        }

        $termStatement = $pdo->prepare('SELECT * FROM academic_terms WHERE id = ? FOR UPDATE');
        $termStatement->execute([$academicTermId]);
        $term = $termStatement->fetch(PDO::FETCH_ASSOC);
        if (!$term || $term['status'] !== 'Active') {
            throw new RuntimeException('The selected academic term must be active.');
        }
        if ((string) $term['semester'] === '2nd Semester') {
            $completion = cradGetFirstSemesterCompletionStatus($pdo, $researchGroupId);
            if (!$completion['complete']) {
                throw new RuntimeException('Second-semester carry-over blocked: ' . $completion['reason']);
            }
        }

        $sourceStatement = $pdo->prepare(
            'SELECT id FROM research_group_terms '
            . 'WHERE research_group_id = ? AND academic_term_id <> ? '
            . 'ORDER BY id DESC LIMIT 1'
        );
        $sourceStatement->execute([$researchGroupId, $academicTermId]);
        $sourceGroupTermId = $sourceStatement->fetchColumn();

        $insert = $pdo->prepare(
            'INSERT INTO research_group_terms '
            . '(research_group_id, academic_term_id, source_group_term_id, enrollment_type, '
            . 'starting_phase, current_phase, transition_confirmed_by, transition_confirmed_at, remarks) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
        );
        $insert->execute([
            $researchGroupId,
            $academicTermId,
            $sourceGroupTermId !== false ? (int) $sourceGroupTermId : null,
            $enrollmentType,
            $startingPhase,
            $startingPhase,
            $actorUserId,
            $remarks,
        ]);
        $groupTermId = (int) $pdo->lastInsertId();

        $groupUpdate = $pdo->prepare(
            'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
        );
        $groupUpdate->execute([$startingPhase, $researchGroupId]);

        cradLogWorkflowEvent(
            $pdo,
            $researchGroupId,
            $academicTermId,
            $groupTermId,
            'TERM_ENROLLMENT',
            null,
            $startingPhase,
            $actorUserId,
            $actorName,
            $remarks,
            'research_group_term',
            $groupTermId,
            ['enrollment_type' => $enrollmentType]
        );

        $pdo->commit();
        return $groupTermId;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function cradWorkflowCanTransition(string $fromPhase, string $toPhase): bool
{
    $transitions = cradSecondSemesterAllowedTransitions();
    return isset($transitions[$fromPhase]) && in_array($toPhase, $transitions[$fromPhase], true);
}

function cradTransitionGroupPhase(
    PDO $pdo,
    int $groupTermId,
    string $toPhase,
    ?int $actorUserId,
    string $actorName,
    ?string $remarks = null,
    ?string $eventType = 'PHASE_TRANSITION',
    ?string $entityType = null,
    ?int $entityId = null
): void {
    if (!in_array($toPhase, CRAD_SECOND_SEMESTER_PHASES, true)) {
        throw new InvalidArgumentException('Invalid target workflow phase.');
    }

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'SELECT gt.*, at.status AS term_status FROM research_group_terms gt '
            . 'INNER JOIN academic_terms at ON at.id = gt.academic_term_id '
            . 'WHERE gt.id = ? FOR UPDATE'
        );
        $statement->execute([$groupTermId]);
        $groupTerm = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$groupTerm) {
            throw new RuntimeException('Research group term record not found.');
        }
        if ($groupTerm['term_status'] !== 'Active') {
            throw new RuntimeException('Workflow transitions are allowed only in the active academic term.');
        }

        $fromPhase = (string) $groupTerm['current_phase'];
        if ($fromPhase === $toPhase) {
            $pdo->commit();
            return;
        }
        if (!cradWorkflowCanTransition($fromPhase, $toPhase)) {
            throw new RuntimeException('Invalid workflow transition from ' . $fromPhase . ' to ' . $toPhase . '.');
        }

        $updateTerm = $pdo->prepare(
            "UPDATE research_group_terms SET current_phase = ?, "
            . "status = CASE WHEN ? = 'Completed' THEN 'Completed' ELSE status END WHERE id = ?"
        );
        $updateTerm->execute([$toPhase, $toPhase, $groupTermId]);

        $updateGroup = $pdo->prepare(
            'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
        );
        $updateGroup->execute([$toPhase, $groupTerm['research_group_id']]);

        cradLogWorkflowEvent(
            $pdo,
            (int) $groupTerm['research_group_id'],
            (int) $groupTerm['academic_term_id'],
            $groupTermId,
            $eventType ?? 'PHASE_TRANSITION',
            $fromPhase,
            $toPhase,
            $actorUserId,
            $actorName,
            $remarks,
            $entityType,
            $entityId
        );

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

/**
 * Aggregate panel decisions with deterministic precedence.
 * FAILED outranks revision; revision outranks pass. No official result is emitted
 * until every assigned panel member has submitted an evaluation.
 *
 * @param array<int,string|array{result:string,overall_score?:float|int|string|null}> $evaluations
 * @return array{complete:bool,official_result:string,provisional_result:string,expected_count:int,submitted_count:int,average_score:?float}
 */
function cradAggregateFinalDefenseResult(array $evaluations, int $expectedCount): array
{
    if ($expectedCount < 1) {
        throw new InvalidArgumentException('Expected panel count must be at least one.');
    }

    $results = [];
    $scores = [];
    foreach ($evaluations as $evaluation) {
        $result = is_array($evaluation) ? (string) ($evaluation['result'] ?? '') : (string) $evaluation;
        $result = strtoupper(trim($result));
        $result = match ($result) {
            'APPROVED', 'PASS', 'PASSED' => 'PASSED',
            'APPROVED WITH REVISION', 'APPROVED WITH REVISIONS',
            'PASS WITH REVISION', 'PASSED WITH REVISIONS' => 'PASSED WITH REVISIONS',
            'FAIL', 'FAILED' => 'FAILED',
            default => throw new InvalidArgumentException('Unknown panel result: ' . $result),
        };
        $results[] = $result;

        if (is_array($evaluation)
            && isset($evaluation['overall_score'])
            && is_numeric($evaluation['overall_score'])) {
            $scores[] = (float) $evaluation['overall_score'];
        }
    }

    $provisional = 'PASSED';
    if (in_array('FAILED', $results, true)) {
        $provisional = 'FAILED';
    } elseif (in_array('PASSED WITH REVISIONS', $results, true)) {
        $provisional = 'PASSED WITH REVISIONS';
    } elseif ($results === []) {
        $provisional = 'UNRESOLVED';
    }

    $complete = count($results) === $expectedCount;
    return [
        'complete' => $complete,
        'official_result' => $complete ? $provisional : 'UNRESOLVED',
        'provisional_result' => $provisional,
        'expected_count' => $expectedCount,
        'submitted_count' => count($results),
        'average_score' => $scores !== [] ? round(array_sum($scores) / count($scores), 2) : null,
    ];
}
