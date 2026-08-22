<?php
declare(strict_types=1);

require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/second-semester-workflow.php';

function finalDefenseEnsureSchema(PDO $crad): void
{
    static $readyConnections = [];
    $connectionId = spl_object_id($crad);
    if (isset($readyConnections[$connectionId])) {
        return;
    }

    $crad->exec(
        "CREATE TABLE IF NOT EXISTS final_defense_evaluations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            defense_schedule_id INT UNSIGNED NOT NULL,
            research_group_id INT UNSIGNED DEFAULT NULL,
            panel_user_id INT UNSIGNED NOT NULL,
            panel_name VARCHAR(150) NOT NULL DEFAULT '',
            content_score DECIMAL(5,2) NOT NULL,
            methodology_score DECIMAL(5,2) NOT NULL,
            references_score DECIMAL(5,2) NOT NULL,
            format_score DECIMAL(5,2) NOT NULL,
            remarks TEXT DEFAULT NULL,
            result ENUM('APPROVED','APPROVED WITH REVISION','FAILED') NOT NULL,
            overall_score DECIMAL(5,2) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'Submitted',
            submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_final_panel_submission (defense_schedule_id, panel_user_id),
            KEY idx_final_group (research_group_id),
            KEY idx_final_panel (panel_user_id),
            KEY idx_final_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    cradEnsureSecondSemesterSchema($crad);
    $readyConnections[$connectionId] = true;
    finalDefenseBackfillOfficialResults($crad);
}

function finalDefenseRequirePanelMember(): void
{
    requireAuth();
    if (getCurrentUserRoleKey() !== 'panel') {
        http_response_code(403);
        exit('Forbidden');
    }
}

function finalDefenseDb(): ?PDO
{
    return function_exists('cradDb') ? cradDb() : null;
}

function finalDefenseRubric(): array
{
    return [
        ['key' => 'content', 'label' => 'Content', 'min' => 0, 'max' => 100],
        ['key' => 'methodology', 'label' => 'Methodology', 'min' => 0, 'max' => 100],
        ['key' => 'references', 'label' => 'References', 'min' => 0, 'max' => 100],
        ['key' => 'format', 'label' => 'Format', 'min' => 0, 'max' => 100],
    ];
}

function finalDefenseAssignedSchedule(PDO $crad, int $scheduleId): ?array
{
    if ($scheduleId <= 0) {
        return null;
    }

    $stmt = $crad->prepare(
        "SELECT rds.id, rds.research_group_id, rds.group_number, rds.research_group,
                rds.research_title, rds.adviser_name, rds.venue,
                rds.defense_datetime, rds.defense_end_datetime, rds.status,
                rds.defense_type,
                rpa.panel_name,
                (SELECT fde.id FROM final_defense_evaluations fde
                 WHERE fde.defense_schedule_id = rds.id
                   AND fde.panel_user_id = :panel_id_check
                 LIMIT 1) AS evaluation_id
         FROM research_defense_schedules rds
         INNER JOIN research_panel_assignments rpa
           ON rpa.research_group_id = rds.research_group_id
          AND rpa.panel_user_id = :panel_id
          AND rpa.defense_phase = 'Final Defense'
          AND rpa.assignment_status = 'Assigned'
         WHERE rds.id = :schedule_id
           AND LOWER(TRIM(COALESCE(rds.defense_type, ''))) IN ('final defense', 're-defense')
           AND rds.defense_datetime IS NOT NULL
           AND rds.defense_datetime <= NOW()
           AND LOWER(TRIM(COALESCE(rds.status, ''))) IN ('scheduled', 'finalized', 'final', 'completed', 'passed', 'failed')
         LIMIT 1"
    );
    $panelId = (int) getCurrentUserId();
    $stmt->execute([
        ':panel_id_check' => $panelId,
        ':panel_id' => $panelId,
        ':schedule_id' => $scheduleId,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function finalDefenseRows(PDO $crad, bool $history = false): array
{
    finalDefenseEnsureSchema($crad);
    $panelId = (int) getCurrentUserId();
    $evaluationFilter = $history ? 'fde.id IS NOT NULL' : 'fde.id IS NULL';
    if (!$history) {
        $evaluationFilter .= " AND NOT EXISTS (
            SELECT 1 FROM defense_attempts final_da
            INNER JOIN defense_official_results final_dor ON final_dor.defense_attempt_id = final_da.id
            WHERE final_da.defense_schedule_id = rds.id
              AND final_dor.official_result <> 'UNRESOLVED'
              AND final_dor.finalized_at IS NOT NULL
        )";
    }

    $stmt = $crad->prepare(
        "SELECT rds.id, rds.research_group_id, rds.group_number, rds.research_group,
                rds.research_title, rds.adviser_name, rds.venue,
                rds.defense_datetime, rds.defense_end_datetime, rds.status,
                rds.defense_type, fde.id AS evaluation_id,
                fde.result AS panel_result, fde.overall_score AS panel_score,
                fde.submitted_at, da.attempt_number, dor.official_result,
                dor.assigned_panel_count, dor.submitted_evaluation_count,
                dor.average_score AS official_average_score
         FROM research_defense_schedules rds
         INNER JOIN research_panel_assignments rpa
           ON rpa.research_group_id = rds.research_group_id
          AND rpa.panel_user_id = :panel_id
          AND rpa.defense_phase = 'Final Defense'
          AND rpa.assignment_status = 'Assigned'
         LEFT JOIN final_defense_evaluations fde
           ON fde.defense_schedule_id = rds.id
          AND fde.panel_user_id = :panel_id_eval
         LEFT JOIN defense_attempts da ON da.defense_schedule_id = rds.id
         LEFT JOIN defense_official_results dor ON dor.defense_attempt_id = da.id
         WHERE LOWER(TRIM(COALESCE(rds.defense_type, ''))) IN ('final defense', 're-defense')
           AND rds.defense_datetime IS NOT NULL
           AND rds.defense_datetime <= NOW()
           AND LOWER(rds.status) IN ('scheduled', 'finalized', 'final', 'completed', 'passed', 'failed')
           AND {$evaluationFilter}
         ORDER BY rds.defense_datetime DESC, rds.id DESC"
    );
    $stmt->execute([
        ':panel_id' => $panelId,
        ':panel_id_eval' => $panelId,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** @return array<string,mixed> */
function finalDefenseActiveGroupTerm(PDO $crad, int $groupId): array
{
    $stmt = $crad->prepare(
        "SELECT rgt.* FROM research_group_terms rgt
         INNER JOIN academic_terms at ON at.id = rgt.academic_term_id
         WHERE rgt.research_group_id = ? AND rgt.status = 'Active'
           AND at.status = 'Active' AND at.semester = '2nd Semester'
         ORDER BY rgt.id DESC LIMIT 1 FOR UPDATE"
    );
    $stmt->execute([$groupId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new RuntimeException('The research group is not enrolled in the active 2nd Semester.');
    }
    return $row;
}

/** @param array<string,mixed> $groupTerm */
function finalDefenseMoveWorkflow(
    PDO $crad,
    array $groupTerm,
    string $toPhase,
    string $eventType,
    int $actorUserId,
    string $actorName,
    string $entityType,
    int $entityId,
    array $metadata = []
): array {
    $fromPhase = (string) $groupTerm['current_phase'];
    if ($fromPhase === $toPhase) {
        return $groupTerm;
    }
    if (!cradWorkflowCanTransition($fromPhase, $toPhase)) {
        throw new RuntimeException('Invalid Final Defense workflow transition from ' . $fromPhase . ' to ' . $toPhase . '.');
    }

    $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?')
        ->execute([$toPhase, (int) $groupTerm['id']]);
    $crad->prepare('UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?')
        ->execute([$toPhase, (int) $groupTerm['research_group_id']]);
    cradLogWorkflowEvent(
        $crad,
        (int) $groupTerm['research_group_id'],
        (int) $groupTerm['academic_term_id'],
        (int) $groupTerm['id'],
        $eventType,
        $fromPhase,
        $toPhase,
        $actorUserId,
        $actorName,
        null,
        $entityType,
        $entityId,
        $metadata
    );
    $groupTerm['current_phase'] = $toPhase;
    return $groupTerm;
}

/**
 * Link a scheduler-owned schedule to an immutable CRAD defense attempt.
 * The first linked schedule is Final Defense; later schedules are Re-Defense attempts.
 *
 * @param array<string,mixed> $schedule
 * @param array<string,mixed> $groupTerm
 * @return array<string,mixed>
 */
function finalDefenseEnsureAttempt(
    PDO $crad,
    array $schedule,
    array $groupTerm,
    int $actorUserId
): array {
    $scheduleId = (int) $schedule['id'];
    $groupId = (int) $schedule['research_group_id'];
    $termId = (int) $groupTerm['academic_term_id'];
    $scheduleTermId = (int) ($schedule['academic_term_id'] ?? 0);
    if ($scheduleTermId > 0 && $scheduleTermId !== $termId) {
        throw new RuntimeException('The Final Defense schedule belongs to a different academic term.');
    }

    $attempt = null;
    $scheduleAttemptId = (int) ($schedule['defense_attempt_id'] ?? 0);
    if ($scheduleAttemptId > 0) {
        $stmt = $crad->prepare('SELECT * FROM defense_attempts WHERE id = ? FOR UPDATE');
        $stmt->execute([$scheduleAttemptId]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    if (!$attempt) {
        $stmt = $crad->prepare('SELECT * FROM defense_attempts WHERE defense_schedule_id = ? LIMIT 1 FOR UPDATE');
        $stmt->execute([$scheduleId]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    if (!$attempt) {
        $placeholder = $crad->prepare(
            "SELECT * FROM defense_attempts
             WHERE research_group_id = ? AND academic_term_id = ? AND defense_schedule_id IS NULL
               AND defense_type IN ('Final Defense','Re-Defense')
               AND status IN ('Ready for Scheduling','Scheduled')
             ORDER BY attempt_number DESC, id DESC LIMIT 1 FOR UPDATE"
        );
        $placeholder->execute([$groupId, $termId]);
        $attempt = $placeholder->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if (!$attempt) {
        $numberStatement = $crad->prepare(
            "SELECT COALESCE(MAX(attempt_number), 0) FROM defense_attempts
             WHERE research_group_id = ? AND academic_term_id = ?
               AND defense_type IN ('Final Defense','Re-Defense')"
        );
        $numberStatement->execute([$groupId, $termId]);
        $attemptNumber = (int) $numberStatement->fetchColumn() + 1;
        $attemptType = $attemptNumber === 1 ? 'Final Defense' : 'Re-Defense';
        $insert = $crad->prepare(
            "INSERT INTO defense_attempts
                (research_group_id, academic_term_id, defense_type, attempt_number,
                 defense_schedule_id, status, created_by_user)
             VALUES (?, ?, ?, ?, ?, 'Scheduled', ?)"
        );
        $insert->execute([$groupId, $termId, $attemptType, $attemptNumber, $scheduleId, $actorUserId ?: null]);
        $attemptId = (int) $crad->lastInsertId();
        $stmt = $crad->prepare('SELECT * FROM defense_attempts WHERE id = ?');
        $stmt->execute([$attemptId]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if (!$attempt
        || (int) $attempt['research_group_id'] !== $groupId
        || (int) $attempt['academic_term_id'] !== $termId) {
        throw new RuntimeException('The linked Final Defense attempt is invalid.');
    }

    $crad->prepare(
        "UPDATE defense_attempts SET defense_schedule_id = ?,
         status = IF(status = 'Ready for Scheduling', 'Scheduled', status) WHERE id = ?"
    )->execute([$scheduleId, (int) $attempt['id']]);
    $crad->prepare('UPDATE research_defense_schedules SET academic_term_id = ?, defense_attempt_id = ? WHERE id = ?')
        ->execute([$termId, (int) $attempt['id'], $scheduleId]);
    $attempt['defense_schedule_id'] = $scheduleId;
    if ((string) $attempt['status'] === 'Ready for Scheduling') {
        $attempt['status'] = 'Scheduled';
    }
    return $attempt;
}

/**
 * Recalculate and persist the official result after a panel submission.
 * Must be called inside the same transaction as the evaluation insert.
 *
 * @return array<string,mixed>
 */
function finalDefenseRefreshOfficialResult(
    PDO $crad,
    int $scheduleId,
    int $actorUserId,
    string $actorName
): array {
    $scheduleStatement = $crad->prepare(
        "SELECT * FROM research_defense_schedules WHERE id = ?
         AND LOWER(TRIM(COALESCE(defense_type, ''))) IN ('final defense','re-defense')
         LIMIT 1 FOR UPDATE"
    );
    $scheduleStatement->execute([$scheduleId]);
    $schedule = $scheduleStatement->fetch(PDO::FETCH_ASSOC);
    if (!$schedule || (int) ($schedule['research_group_id'] ?? 0) <= 0) {
        throw new RuntimeException('Final Defense schedule not found.');
    }

    $groupId = (int) $schedule['research_group_id'];
    $groupTerm = finalDefenseActiveGroupTerm($crad, $groupId);
    $attempt = finalDefenseEnsureAttempt($crad, $schedule, $groupTerm, $actorUserId);
    $attemptId = (int) $attempt['id'];

    $existingStatement = $crad->prepare(
        'SELECT * FROM defense_official_results WHERE defense_attempt_id = ? LIMIT 1 FOR UPDATE'
    );
    $existingStatement->execute([$attemptId]);
    $existing = $existingStatement->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($existing
        && (string) ($existing['official_result'] ?? '') !== 'UNRESOLVED'
        && !empty($existing['finalized_at'])) {
        return [
            'complete' => true,
            'official_result' => (string) $existing['official_result'],
            'provisional_result' => (string) $existing['official_result'],
            'expected_count' => (int) $existing['assigned_panel_count'],
            'submitted_count' => (int) $existing['submitted_evaluation_count'],
            'average_score' => $existing['average_score'] !== null ? (float) $existing['average_score'] : null,
            'defense_attempt_id' => $attemptId,
            'official_result_id' => (int) $existing['id'],
            'next_defense_attempt_id' => null,
        ];
    }

    if ((string) $groupTerm['current_phase'] === 'Final Defense Scheduling') {
        $groupTerm = finalDefenseMoveWorkflow(
            $crad,
            $groupTerm,
            'Final Defense',
            'FINAL_DEFENSE_EVALUATION_STARTED',
            $actorUserId,
            $actorName,
            'defense_attempt',
            $attemptId,
            ['defense_schedule_id' => $scheduleId, 'attempt_number' => (int) $attempt['attempt_number']]
        );
    }

    $panelCountStatement = $crad->prepare(
        "SELECT COUNT(DISTINCT panel_user_id) FROM research_panel_assignments
         WHERE research_group_id = ? AND defense_phase = 'Final Defense'
           AND assignment_status = 'Assigned'"
    );
    $panelCountStatement->execute([$groupId]);
    $assignedPanelCount = (int) $panelCountStatement->fetchColumn();
    if ($assignedPanelCount < 1) {
        throw new RuntimeException('No assigned Final Defense panel members were found.');
    }

    $evaluationsStatement = $crad->prepare(
        "SELECT fde.id, fde.panel_user_id, fde.panel_name, fde.result,
                fde.overall_score, fde.remarks
         FROM final_defense_evaluations fde
         INNER JOIN research_panel_assignments rpa
           ON rpa.research_group_id = fde.research_group_id
          AND rpa.panel_user_id = fde.panel_user_id
          AND rpa.defense_phase = 'Final Defense'
          AND rpa.assignment_status = 'Assigned'
         WHERE fde.defense_schedule_id = ?
         ORDER BY fde.panel_user_id ASC FOR UPDATE"
    );
    $evaluationsStatement->execute([$scheduleId]);
    $evaluations = $evaluationsStatement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $aggregate = cradAggregateFinalDefenseResult($evaluations, $assignedPanelCount);

    $basis = json_encode([
        'precedence' => ['FAILED', 'PASSED WITH REVISIONS', 'PASSED'],
        'attempt_number' => (int) $attempt['attempt_number'],
        'evaluations' => $evaluations,
    ], JSON_THROW_ON_ERROR);
    $isComplete = (bool) $aggregate['complete'];

    $save = $crad->prepare(
        "INSERT INTO defense_official_results
            (defense_attempt_id, research_group_id, defense_schedule_id, assigned_panel_count,
             submitted_evaluation_count, average_score, official_result, result_basis_json,
             finalized_by_user, finalized_by_name, finalized_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IF(? = 1, NOW(), NULL))
         ON DUPLICATE KEY UPDATE defense_schedule_id = VALUES(defense_schedule_id),
             assigned_panel_count = VALUES(assigned_panel_count),
             submitted_evaluation_count = VALUES(submitted_evaluation_count),
             average_score = VALUES(average_score), official_result = VALUES(official_result),
             result_basis_json = VALUES(result_basis_json),
             finalized_by_user = VALUES(finalized_by_user), finalized_by_name = VALUES(finalized_by_name),
             finalized_at = IF(VALUES(official_result) = 'UNRESOLVED', NULL, COALESCE(finalized_at, NOW()))"
    );
    $save->execute([
        $attemptId,
        $groupId,
        $scheduleId,
        $assignedPanelCount,
        (int) $aggregate['submitted_count'],
        $aggregate['average_score'],
        (string) $aggregate['official_result'],
        $basis,
        $isComplete ? ($actorUserId ?: null) : null,
        $isComplete ? trim($actorName) : '',
        $isComplete ? 1 : 0,
    ]);
    $resultStatement = $crad->prepare('SELECT * FROM defense_official_results WHERE defense_attempt_id = ?');
    $resultStatement->execute([$attemptId]);
    $official = $resultStatement->fetch(PDO::FETCH_ASSOC);
    if (!$official) {
        throw new RuntimeException('Unable to persist the official Final Defense result.');
    }

    $crad->prepare('UPDATE final_defense_evaluations SET defense_attempt_id = ? WHERE defense_schedule_id = ?')
        ->execute([$attemptId, $scheduleId]);

    $alreadyFinalized = $existing
        && (string) ($existing['official_result'] ?? '') !== 'UNRESOLVED'
        && !empty($existing['finalized_at']);
    if (!$isComplete || $alreadyFinalized) {
        return array_merge($aggregate, [
            'defense_attempt_id' => $attemptId,
            'official_result_id' => (int) $official['id'],
            'next_defense_attempt_id' => null,
        ]);
    }

    $officialResult = (string) $aggregate['official_result'];
    $attemptStatus = match ($officialResult) {
        'PASSED' => 'Passed',
        'PASSED WITH REVISIONS' => 'For Revision',
        'FAILED' => 'Failed',
        default => throw new RuntimeException('Unsupported official Final Defense result.'),
    };
    $crad->prepare('UPDATE defense_attempts SET status = ?, completed_at = NOW() WHERE id = ?')
        ->execute([$attemptStatus, $attemptId]);

    $nextAttemptId = null;
    if ($officialResult === 'PASSED WITH REVISIONS') {
        $cycle = $crad->prepare(
            "INSERT INTO research_revision_cycles
                (research_group_id, defense_schedule_id, defense_attempt_id, official_result, revision_status)
             VALUES (?, ?, ?, 'PASSED WITH REVISIONS', 'Needs Revision')
             ON DUPLICATE KEY UPDATE defense_attempt_id = VALUES(defense_attempt_id),
                 official_result = VALUES(official_result),
                 revision_status = IF(revision_status = 'Compliant', revision_status, 'Needs Revision')"
        );
        $cycle->execute([$groupId, $scheduleId, $attemptId]);
        $revisionInsert = $crad->prepare(
            "INSERT INTO defense_revision_items
                (defense_attempt_id, research_group_id, source_evaluation_id,
                 requested_by_user, requested_by_name, revision_text, status)
             SELECT ?, ?, ?, ?, ?, ?, 'Open'
             WHERE NOT EXISTS (
                 SELECT 1 FROM defense_revision_items
                 WHERE defense_attempt_id = ? AND source_evaluation_id = ?
             )"
        );
        foreach ($evaluations as $evaluation) {
            if (strtoupper(trim((string) $evaluation['result'])) !== 'APPROVED WITH REVISION') {
                continue;
            }
            $revisionInsert->execute([
                $attemptId,
                $groupId,
                (int) $evaluation['id'],
                (int) $evaluation['panel_user_id'],
                (string) $evaluation['panel_name'],
                trim((string) $evaluation['remarks']),
                $attemptId,
                (int) $evaluation['id'],
            ]);
        }
    } elseif ($officialResult === 'FAILED') {
        $nextAttemptNumber = (int) $attempt['attempt_number'] + 1;
        $next = $crad->prepare(
            "INSERT INTO defense_attempts
                (research_group_id, academic_term_id, defense_type, attempt_number, status, created_by_user)
             VALUES (?, ?, 'Re-Defense', ?, 'Ready for Scheduling', ?)
             ON DUPLICATE KEY UPDATE status = IF(status = 'Cancelled', 'Ready for Scheduling', status)"
        );
        $next->execute([$groupId, (int) $groupTerm['academic_term_id'], $nextAttemptNumber, $actorUserId ?: null]);
        $nextIdStatement = $crad->prepare(
            "SELECT id FROM defense_attempts WHERE research_group_id = ? AND academic_term_id = ?
             AND defense_type = 'Re-Defense' AND attempt_number = ? LIMIT 1"
        );
        $nextIdStatement->execute([$groupId, (int) $groupTerm['academic_term_id'], $nextAttemptNumber]);
        $nextAttemptId = (int) $nextIdStatement->fetchColumn() ?: null;
    }

    $targetPhase = match ($officialResult) {
        'PASSED' => 'Final Manuscript Approval',
        'PASSED WITH REVISIONS' => 'Post-Defense Revision',
        'FAILED' => 'Final Defense Scheduling',
    };
    $groupTerm = finalDefenseMoveWorkflow(
        $crad,
        $groupTerm,
        $targetPhase,
        'FINAL_DEFENSE_RESULT_FINALIZED',
        $actorUserId,
        $actorName,
        'defense_official_result',
        (int) $official['id'],
        [
            'defense_attempt_id' => $attemptId,
            'defense_schedule_id' => $scheduleId,
            'attempt_number' => (int) $attempt['attempt_number'],
            'official_result' => $officialResult,
            'assigned_panel_count' => $assignedPanelCount,
            'submitted_evaluation_count' => (int) $aggregate['submitted_count'],
            'average_score' => $aggregate['average_score'],
            'next_defense_attempt_id' => $nextAttemptId,
        ]
    );

    return array_merge($aggregate, [
        'defense_attempt_id' => $attemptId,
        'official_result_id' => (int) $official['id'],
        'next_defense_attempt_id' => $nextAttemptId,
    ]);
}

/** Backfill legacy Final Defense evaluations into official attempt/result records. */
function finalDefenseBackfillOfficialResults(PDO $crad): void
{
    if ($crad->inTransaction()) {
        return;
    }
    try {
        $scheduleIds = $crad->query(
            "SELECT DISTINCT rds.id
             FROM research_defense_schedules rds
             INNER JOIN final_defense_evaluations fde ON fde.defense_schedule_id = rds.id
             LEFT JOIN defense_attempts da ON da.defense_schedule_id = rds.id
             LEFT JOIN defense_official_results dor ON dor.defense_attempt_id = da.id
             WHERE LOWER(TRIM(COALESCE(rds.defense_type, ''))) IN ('final defense','re-defense')
               AND (dor.id IS NULL OR dor.official_result = 'UNRESOLVED')
             ORDER BY rds.id ASC"
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (Throwable $exception) {
        error_log('Final Defense result backfill lookup failed: ' . $exception->getMessage());
        return;
    }

    foreach ($scheduleIds as $scheduleId) {
        try {
            $crad->beginTransaction();
            finalDefenseRefreshOfficialResult($crad, (int) $scheduleId, 0, 'Phase 4 migration');
            $crad->commit();
        } catch (Throwable $exception) {
            if ($crad->inTransaction()) {
                $crad->rollBack();
            }
            error_log('Final Defense result backfill skipped schedule #' . (int) $scheduleId . ': ' . $exception->getMessage());
        }
    }
}

function finalDefenseSubmitEvaluation(PDO $crad, int $scheduleId, array $data): array
{
    finalDefenseEnsureSchema($crad);
    $defense = finalDefenseAssignedSchedule($crad, $scheduleId);
    if (!$defense) {
        return ['ok' => false, 'error' => 'This Final Defense is not assigned to your panel account.'];
    }
    if (!empty($defense['evaluation_id'])) {
        return ['ok' => false, 'error' => 'This Final Defense already has your evaluation.'];
    }

    $scores = [];
    foreach (finalDefenseRubric() as $criterion) {
        $raw = trim((string) ($data[$criterion['key'] . '_score'] ?? ''));
        if ($raw === '' || !is_numeric($raw)) {
            return ['ok' => false, 'error' => 'Please enter a valid score for ' . $criterion['label'] . '.'];
        }
        $score = (float) $raw;
        if ($score < $criterion['min'] || $score > $criterion['max']) {
            return ['ok' => false, 'error' => $criterion['label'] . ' score must be between 0 and 100.'];
        }
        $scores[$criterion['key']] = $score;
    }

    $result = strtoupper(trim((string) ($data['result'] ?? '')));
    if (!in_array($result, ['APPROVED', 'APPROVED WITH REVISION', 'FAILED'], true)) {
        return ['ok' => false, 'error' => 'Please select a valid result.'];
    }
    $remarks = trim((string) ($data['remarks'] ?? ''));
    if ($result !== 'APPROVED' && $remarks === '') {
        return ['ok' => false, 'error' => 'Remarks are required for revision or failed results.'];
    }

    try {
        $crad->beginTransaction();
        $scheduleLock = $crad->prepare('SELECT id FROM research_defense_schedules WHERE id = ? FOR UPDATE');
        $scheduleLock->execute([$scheduleId]);
        if (!$scheduleLock->fetchColumn()) {
            throw new RuntimeException('Final Defense schedule not found.');
        }
        $defense = finalDefenseAssignedSchedule($crad, $scheduleId);
        if (!$defense || !empty($defense['evaluation_id'])) {
            throw new RuntimeException('This Final Defense is no longer available for your evaluation.');
        }
        $finalizedCheck = $crad->prepare(
            "SELECT COUNT(*) FROM defense_attempts da
             INNER JOIN defense_official_results dor ON dor.defense_attempt_id = da.id
             WHERE da.defense_schedule_id = ? AND dor.official_result <> 'UNRESOLVED'
               AND dor.finalized_at IS NOT NULL"
        );
        $finalizedCheck->execute([$scheduleId]);
        if ((int) $finalizedCheck->fetchColumn() > 0) {
            throw new RuntimeException('The official Final Defense result has already been finalized.');
        }
        $stmt = $crad->prepare(
            "INSERT INTO final_defense_evaluations
                (defense_schedule_id, research_group_id, panel_user_id, panel_name,
                 content_score, methodology_score, references_score, format_score,
                 remarks, result, overall_score, status, submitted_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted', NOW(), NOW())"
        );
        $stmt->execute([
            $scheduleId,
            (int) ($defense['research_group_id'] ?? 0) ?: null,
            (int) getCurrentUserId(),
            getCurrentUserName(),
            $scores['content'],
            $scores['methodology'],
            $scores['references'],
            $scores['format'],
            $remarks,
            $result,
            round(array_sum($scores) / count($scores), 2),
        ]);
        $aggregation = finalDefenseRefreshOfficialResult(
            $crad,
            $scheduleId,
            (int) getCurrentUserId(),
            getCurrentUserName()
        );
        $crad->commit();
        $message = !empty($aggregation['complete'])
            ? 'Evaluation saved. Official Final Defense result: ' . (string) $aggregation['official_result'] . '.'
            : 'Evaluation saved. Official result is pending ('
                . (int) $aggregation['submitted_count'] . '/' . (int) $aggregation['expected_count'] . ' panel submissions).';
        return ['ok' => true, 'message' => $message, 'aggregation' => $aggregation];
    } catch (PDOException $e) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        if (($e->errorInfo[1] ?? 0) === 1062) {
            return ['ok' => false, 'error' => 'This Final Defense already has your evaluation.'];
        }
        error_log('Final Defense evaluation submit failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Unable to submit Final Defense evaluation.'];
    } catch (Throwable $e) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        error_log('Final Defense evaluation workflow failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
