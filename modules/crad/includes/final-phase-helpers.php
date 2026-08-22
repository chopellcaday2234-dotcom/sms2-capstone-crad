<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/uploads.php';
require_once ROOT_PATH . '/modules/faculty/includes/final-defense-evaluation.php';
require_once ROOT_PATH . '/modules/crad/database/second_semester_schema.php';
require_once ROOT_PATH . '/modules/crad/includes/final-readiness-helpers.php';

function finalPhaseEnsureSchema(PDO $crad): void
{
    static $readyConnections = [];
    $connectionId = spl_object_id($crad);
    if (isset($readyConnections[$connectionId])) {
        return;
    }

    finalDefenseEnsureSchema($crad);
    $tables = [
        "CREATE TABLE IF NOT EXISTS final_defense_recommendations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            group_number VARCHAR(40) NOT NULL DEFAULT '',
            adviser_user_id INT UNSIGNED DEFAULT NULL,
            adviser_name VARCHAR(150) NOT NULL DEFAULT '',
            status ENUM('Not Ready','Recommended') NOT NULL DEFAULT 'Not Ready',
            remarks TEXT DEFAULT NULL,
            recommended_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uniq_fdr_group (research_group_id), KEY idx_fdr_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS manuscript_submissions (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            version_number INT UNSIGNED NOT NULL,
            status ENUM('Submitted','Under Review','For Revision','Approved') NOT NULL DEFAULT 'Submitted',
            submitted_by_user INT UNSIGNED DEFAULT NULL,
            submitted_by_name VARCHAR(150) NOT NULL DEFAULT '',
            submitted_by_email VARCHAR(190) NOT NULL DEFAULT '',
            submission_notes TEXT DEFAULT NULL,
            original_name VARCHAR(255) NOT NULL DEFAULT '',
            stored_subdir VARCHAR(180) NOT NULL DEFAULT '',
            stored_name VARCHAR(120) NOT NULL DEFAULT '',
            file_size INT UNSIGNED NOT NULL DEFAULT 0,
            file_mime VARCHAR(120) NOT NULL DEFAULT '',
            submission_token VARCHAR(64) NOT NULL,
            submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME DEFAULT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uniq_manuscript_version (research_group_id, version_number),
            UNIQUE KEY uniq_manuscript_token (submission_token), KEY idx_manuscript_status (status), KEY idx_manuscript_group (research_group_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS manuscript_evaluations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_id INT UNSIGNED NOT NULL,
            research_group_id INT UNSIGNED NOT NULL,
            evaluator_user_id INT UNSIGNED NOT NULL,
            evaluator_name VARCHAR(150) NOT NULL DEFAULT '',
            content_score DECIMAL(5,2) NOT NULL,
            methodology_score DECIMAL(5,2) NOT NULL,
            results_score DECIMAL(5,2) NOT NULL,
            conclusions_score DECIMAL(5,2) NOT NULL,
            recommendations_score DECIMAL(5,2) NOT NULL,
            references_score DECIMAL(5,2) NOT NULL,
            formatting_score DECIMAL(5,2) NOT NULL,
            compliance_score DECIMAL(5,2) NOT NULL,
            remarks TEXT DEFAULT NULL,
            result ENUM('APPROVED','FOR REVISION') NOT NULL,
            overall_score DECIMAL(5,2) NOT NULL,
            evaluated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id), KEY idx_meval_submission (submission_id), KEY idx_meval_group (research_group_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS final_manuscript_approvals (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            defense_schedule_id INT UNSIGNED DEFAULT NULL,
            approved_by_user INT UNSIGNED DEFAULT NULL,
            approved_by_name VARCHAR(150) NOT NULL DEFAULT '',
            status ENUM('Pending','Approved','Returned') NOT NULL DEFAULT 'Pending',
            remarks TEXT DEFAULT NULL,
            approved_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uniq_fma_group (research_group_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS final_manuscript_approval_history (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            approval_id INT UNSIGNED DEFAULT NULL,
            research_group_id INT UNSIGNED NOT NULL,
            decision_sequence INT UNSIGNED NOT NULL,
            decision_status ENUM('Pending','Approved','Returned') NOT NULL,
            actor_user_id INT UNSIGNED DEFAULT NULL,
            actor_name VARCHAR(150) NOT NULL DEFAULT '',
            remarks TEXT DEFAULT NULL,
            decision_at DATETIME NOT NULL,
            defense_schedule_id INT UNSIGNED DEFAULT NULL,
            manuscript_submission_id INT UNSIGNED DEFAULT NULL,
            revision_submission_id INT UNSIGNED DEFAULT NULL,
            defense_attempt_id INT UNSIGNED DEFAULT NULL,
            revision_cycle_id INT UNSIGNED DEFAULT NULL,
            file_checksum CHAR(64) NOT NULL DEFAULT '',
            approval_token VARCHAR(64) NOT NULL DEFAULT '',
            event_source VARCHAR(60) NOT NULL DEFAULT 'WORKFLOW',
            source_updated_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_fmah_group_sequence (research_group_id, decision_sequence),
            KEY idx_fmah_approval (approval_id),
            KEY idx_fmah_group_status (research_group_id, decision_status),
            KEY idx_fmah_evidence (manuscript_submission_id, revision_submission_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS publications (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            title VARCHAR(500) NOT NULL DEFAULT '',
            authors TEXT DEFAULT NULL,
            publication_outlet VARCHAR(255) NOT NULL DEFAULT '',
            publication_date DATE DEFAULT NULL,
            doi_link VARCHAR(500) NOT NULL DEFAULT '',
            status ENUM('Draft','For Publication','Published','Archived') NOT NULL DEFAULT 'Draft',
            notes TEXT DEFAULT NULL,
            created_by_user INT UNSIGNED DEFAULT NULL,
            created_by_name VARCHAR(150) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), KEY idx_pub_group (research_group_id), KEY idx_pub_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS research_revision_cycles (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            defense_schedule_id INT UNSIGNED NOT NULL,
            official_result VARCHAR(60) NOT NULL DEFAULT 'APPROVED WITH REVISION',
            revision_status VARCHAR(60) NOT NULL DEFAULT 'Needs Revision',
            opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id), UNIQUE KEY uniq_revision_group_defense (research_group_id, defense_schedule_id),
            KEY idx_revision_status (revision_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
    foreach ($tables as $sql) {
        try { $crad->exec($sql); } catch (Throwable $e) { error_log('Final phase schema failed: ' . $e->getMessage()); }
    }
    try {
        cradEnsureSecondSemesterSchema($crad);
    } catch (Throwable $e) {
        error_log('Second semester schema failed: ' . $e->getMessage());
    }
    $readyConnections[$connectionId] = true;
}

function fpIsRecommendedForFinalDefense(PDO $crad, int $groupId): bool
{
    $row = fpGetFinalDefenseRecommendation($crad, $groupId);
    return (string) ($row['status'] ?? '') === 'Recommended';
}

function fpSaveFinalDefenseRecommendation(PDO $crad, int $groupId, string $groupNumber, int $adviserUserId, string $adviserName, string $remarks): bool
{
    if ($groupId <= 0 || $adviserUserId <= 0 || trim($adviserName) === '') {
        return false;
    }

    finalPhaseEnsureSchema($crad);
    return frRecommendForFinalDefense(
        $crad,
        $groupId,
        $groupNumber,
        $adviserUserId,
        (string) ($_SESSION['user_email'] ?? ''),
        $adviserName,
        $remarks
    ) > 0;
}

function fpClearFinalDefenseRecommendation(
    PDO $crad,
    int $groupId,
    ?int $actorUserId = null,
    ?string $actorName = null,
    string $reason = 'Revoked by adviser.'
): bool
{
    if ($groupId <= 0) {
        return false;
    }

    finalPhaseEnsureSchema($crad);
    frRevokeFinalDefenseRecommendation(
        $crad,
        $groupId,
        $actorUserId ?? (int) ($_SESSION['user_id'] ?? 0),
        $actorName ?? (function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? 'Adviser')),
        $reason
    );
    return true;
}

function fpGetFinalDefenseRecommendation(PDO $crad, int $groupId): ?array
{
    if ($groupId <= 0) return null;
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare("SELECT * FROM final_defense_recommendations WHERE research_group_id = ? LIMIT 1");
    $stmt->execute([$groupId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row;
    return null;
}

function fpGetLatestManuscriptSubmission(PDO $crad, int $groupId, ?string $purpose = null): ?array
{
    finalPhaseEnsureSchema($crad);
    $sql = 'SELECT * FROM manuscript_submissions WHERE research_group_id = ?';
    $params = [$groupId];
    if ($purpose !== null) {
        $sql .= ' AND purpose = ?';
        $params[] = $purpose;
    }
    $sql .= ' ORDER BY version_number DESC, id DESC LIMIT 1';
    $stmt = $crad->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** @return array<int,array<string,mixed>> */
function fpGetManuscriptSubmissionHistory(PDO $crad, int $groupId, string $purpose = 'Final Defense'): array
{
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare(
        'SELECT ms.*, me.result AS evaluation_result, me.overall_score, me.remarks AS evaluation_remarks, '
        . 'me.evaluated_at FROM manuscript_submissions ms '
        . 'LEFT JOIN manuscript_evaluations me ON me.id = ('
        . ' SELECT me2.id FROM manuscript_evaluations me2 WHERE me2.submission_id = ms.id '
        . ' ORDER BY me2.id DESC LIMIT 1) '
        . 'WHERE ms.research_group_id = ? AND ms.purpose = ? '
        . 'ORDER BY ms.version_number DESC, ms.id DESC'
    );
    $stmt->execute([$groupId, $purpose]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fpRepairLegacyRecommendedManuscriptPhase(PDO $crad, int $groupId): bool
{
    if ($groupId <= 0 || !fpIsRecommendedForFinalDefense($crad, $groupId)) {
        return false;
    }

    $groupTerm = frGetActiveGroupTerm($crad, $groupId);
    if (!$groupTerm || (string) $groupTerm['current_phase'] !== 'Final Defense Scheduling') {
        return false;
    }

    $submissionStatement = $crad->prepare(
        "SELECT COUNT(*) FROM manuscript_submissions WHERE research_group_id = ? AND purpose = 'Final Defense'"
    );
    $submissionStatement->execute([$groupId]);
    if ((int) $submissionStatement->fetchColumn() > 0) {
        return false;
    }

    $scheduleStatement = $crad->prepare(
        "SELECT COUNT(*) FROM research_defense_schedules WHERE research_group_id = ? "
        . "AND LOWER(TRIM(defense_type)) = 'final defense' "
        . "AND defense_datetime IS NOT NULL AND status NOT IN ('Cancelled','Rejected')"
    );
    $scheduleStatement->execute([$groupId]);
    if ((int) $scheduleStatement->fetchColumn() > 0) {
        return false;
    }

    cradTransitionGroupPhase(
        $crad,
        (int) $groupTerm['id'],
        'Final Manuscript Submission',
        null,
        'Phase 3 migration',
        'Restored the required post-recommendation manuscript submission step.',
        'PHASE3_MANUSCRIPT_FLOW_REPAIR'
    );
    return true;
}

/** @param array{original_name:string,stored_subdir:string,stored_name:string,file_size:int,file_mime:string,file_checksum:string} $file */
function fpSubmitFinalManuscript(
    PDO $crad,
    int $groupId,
    int $submittedByUser,
    string $submittedByName,
    string $submittedByEmail,
    array $file,
    string $notes = ''
): int {
    if ($groupId <= 0 || $submittedByUser <= 0) {
        throw new InvalidArgumentException('A valid research group and student account are required.');
    }
    if (trim((string) ($file['stored_name'] ?? '')) === ''
        || trim((string) ($file['file_checksum'] ?? '')) === '') {
        throw new InvalidArgumentException('The official manuscript file metadata is incomplete.');
    }

    finalPhaseEnsureSchema($crad);
    $crad->beginTransaction();
    try {
        $recommendationStatement = $crad->prepare(
            "SELECT * FROM final_defense_recommendations "
            . "WHERE research_group_id = ? AND status = 'Recommended' LIMIT 1 FOR UPDATE"
        );
        $recommendationStatement->execute([$groupId]);
        $recommendation = $recommendationStatement->fetch(PDO::FETCH_ASSOC);
        if (!$recommendation) {
            throw new RuntimeException(
                'Your adviser must formally recommend the group for Final Defense before official manuscript submission.'
            );
        }

        $groupTermStatement = $crad->prepare(
            "SELECT rgt.* FROM research_group_terms rgt "
            . "INNER JOIN academic_terms at ON at.id = rgt.academic_term_id "
            . "WHERE rgt.research_group_id = ? AND rgt.status = 'Active' "
            . "AND at.status = 'Active' AND at.semester = '2nd Semester' "
            . "ORDER BY rgt.id DESC LIMIT 1 FOR UPDATE"
        );
        $groupTermStatement->execute([$groupId]);
        $groupTerm = $groupTermStatement->fetch(PDO::FETCH_ASSOC);
        if (!$groupTerm) {
            throw new RuntimeException('Your group must be enrolled in the active 2nd Semester.');
        }
        if ((int) ($recommendation['academic_term_id'] ?? 0) > 0
            && (int) $recommendation['academic_term_id'] !== (int) $groupTerm['academic_term_id']) {
            throw new RuntimeException('The Final Defense recommendation belongs to a different academic term.');
        }

        $fromPhase = (string) $groupTerm['current_phase'];
        if ($fromPhase !== 'Final Manuscript Submission') {
            throw new RuntimeException('Official manuscript submission is not available at the current workflow phase.');
        }

        $latestStatement = $crad->prepare(
            "SELECT * FROM manuscript_submissions WHERE research_group_id = ? "
            . "AND purpose = 'Final Defense' ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE"
        );
        $latestStatement->execute([$groupId]);
        $latest = $latestStatement->fetch(PDO::FETCH_ASSOC);
        if ($latest && (string) $latest['status'] !== 'For Revision') {
            throw new RuntimeException('The latest official manuscript is still under review or already approved.');
        }

        $versionStatement = $crad->prepare(
            'SELECT COALESCE(MAX(version_number), 0) FROM manuscript_submissions WHERE research_group_id = ?'
        );
        $versionStatement->execute([$groupId]);
        $version = (int) $versionStatement->fetchColumn() + 1;
        $token = bin2hex(random_bytes(32));
        $insert = $crad->prepare(
            "INSERT INTO manuscript_submissions "
            . "(research_group_id, academic_term_id, purpose, supersedes_submission_id, version_number, status, "
            . "submitted_by_user, submitted_by_name, submitted_by_email, submission_notes, original_name, "
            . "stored_subdir, stored_name, file_size, file_mime, file_checksum, submission_token) "
            . "VALUES (?, ?, 'Final Defense', ?, ?, 'Submitted', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert->execute([
            $groupId,
            (int) $groupTerm['academic_term_id'],
            $latest ? (int) $latest['id'] : null,
            $version,
            $submittedByUser,
            trim($submittedByName),
            strtolower(trim($submittedByEmail)),
            trim($notes),
            trim((string) $file['original_name']),
            trim((string) $file['stored_subdir']),
            trim((string) $file['stored_name']),
            (int) $file['file_size'],
            trim((string) $file['file_mime']),
            trim((string) $file['file_checksum']),
            $token,
        ]);
        $submissionId = (int) $crad->lastInsertId();

        $toPhase = 'Final Manuscript Evaluation';
        if (!cradWorkflowCanTransition($fromPhase, $toPhase)) {
            throw new RuntimeException('The group cannot move to manuscript evaluation from its current phase.');
        }
        $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?')
            ->execute([$toPhase, (int) $groupTerm['id']]);
        $crad->prepare('UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?')
            ->execute([$toPhase, $groupId]);

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            (int) $groupTerm['academic_term_id'],
            (int) $groupTerm['id'],
            'FINAL_MANUSCRIPT_SUBMITTED',
            $fromPhase,
            $toPhase,
            $submittedByUser,
            $submittedByName,
            trim($notes) ?: null,
            'manuscript_submission',
            $submissionId,
            ['version_number' => $version, 'file_checksum' => (string) $file['file_checksum']]
        );

        $crad->commit();
    } catch (Throwable $exception) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        throw $exception;
    }

    frNotifyAdviser(
        $crad,
        $groupId,
        'final_manuscript_submitted',
        'Official Chapters 1–5 manuscript submitted',
        'The research group submitted an official manuscript version for CRAD evaluation.',
        'manuscript_submission',
        $submissionId,
        BASE_URL . '/modules/crad/pages/final-manuscript-review.php'
    );
    foreach (['crad_officer', 'research_coordinator'] as $recipientRole) {
        frCreateNotification($crad, [
            'recipient_user_id' => null,
            'recipient_email' => '',
            'recipient_role' => $recipientRole,
            'batch_key' => 'final_manuscript_submitted:' . $submissionId,
            'notification_type' => 'final_manuscript_submitted',
            'title' => 'Official Chapters 1–5 manuscript submitted',
            'body' => 'A recommended research group submitted an official manuscript for evaluation.',
            'related_entity_type' => 'manuscript_submission',
            'related_entity_id' => $submissionId,
            'action_url' => BASE_URL . '/modules/crad/pages/final-manuscript-review.php',
        ]);
    }

    return $submissionId;
}

/**
 * @param array<string,float|int|string> $scores
 * @return array{id:int,result:string,status:string,overall_score:float,to_phase:string}
 */
function fpSaveManuscriptEvaluation(
    PDO $crad,
    int $submissionId,
    int $evaluatorUserId,
    string $evaluatorName,
    array $scores,
    string $action,
    string $remarks = ''
): array {
    if ($submissionId <= 0 || $evaluatorUserId <= 0 || trim($evaluatorName) === '') {
        throw new InvalidArgumentException('A valid submission and evaluator account are required.');
    }
    if (!in_array($action, ['approve', 'revision'], true)) {
        throw new InvalidArgumentException('Invalid manuscript evaluation action.');
    }

    $criteria = [
        'content', 'methodology', 'results', 'conclusions',
        'recommendations', 'references', 'formatting', 'compliance',
    ];
    $normalizedScores = [];
    foreach ($criteria as $criterion) {
        if (!isset($scores[$criterion]) || !is_numeric($scores[$criterion])) {
            throw new InvalidArgumentException('Every manuscript rubric score is required.');
        }
        $score = (float) $scores[$criterion];
        if ($score < 0 || $score > 100) {
            throw new InvalidArgumentException('All manuscript rubric scores must be between 0 and 100.');
        }
        $normalizedScores[$criterion] = $score;
    }

    finalPhaseEnsureSchema($crad);
    $crad->beginTransaction();
    try {
        $submissionStatement = $crad->prepare(
            "SELECT ms.*, ms.academic_term_id AS manuscript_academic_term_id, "
            . "rgt.id AS group_term_id, rgt.academic_term_id AS group_academic_term_id, rgt.current_phase "
            . "FROM manuscript_submissions ms "
            . "INNER JOIN research_group_terms rgt ON rgt.research_group_id = ms.research_group_id "
            . "AND rgt.status = 'Active' "
            . "INNER JOIN academic_terms at ON at.id = rgt.academic_term_id AND at.status = 'Active' "
            . "AND at.semester = '2nd Semester' "
            . "WHERE ms.id = ? AND ms.purpose = 'Final Defense' LIMIT 1 FOR UPDATE"
        );
        $submissionStatement->execute([$submissionId]);
        $submission = $submissionStatement->fetch(PDO::FETCH_ASSOC);
        if (!$submission) {
            throw new RuntimeException('Official manuscript submission not found in the active term.');
        }
        if ((int) ($submission['manuscript_academic_term_id'] ?? 0) > 0
            && (int) $submission['manuscript_academic_term_id'] !== (int) $submission['group_academic_term_id']) {
            throw new RuntimeException('The manuscript belongs to a different academic term.');
        }
        if ((string) $submission['current_phase'] !== 'Final Manuscript Evaluation') {
            throw new RuntimeException('This group is not currently in manuscript evaluation.');
        }
        if (!in_array((string) $submission['status'], ['Submitted', 'Under Review'], true)) {
            throw new RuntimeException('This manuscript version has already received a final evaluation.');
        }

        $latestStatement = $crad->prepare(
            "SELECT id FROM manuscript_submissions WHERE research_group_id = ? "
            . "AND purpose = 'Final Defense' ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE"
        );
        $latestStatement->execute([(int) $submission['research_group_id']]);
        if ((int) $latestStatement->fetchColumn() !== $submissionId) {
            throw new RuntimeException('Only the latest official manuscript version may be evaluated.');
        }

        $existingStatement = $crad->prepare('SELECT COUNT(*) FROM manuscript_evaluations WHERE submission_id = ?');
        $existingStatement->execute([$submissionId]);
        if ((int) $existingStatement->fetchColumn() > 0) {
            throw new RuntimeException('This manuscript version already has an evaluation record.');
        }

        $result = $action === 'approve' ? 'APPROVED' : 'FOR REVISION';
        $status = $action === 'approve' ? 'Approved' : 'For Revision';
        $toPhase = $action === 'approve' ? 'Final Defense Scheduling' : 'Final Manuscript Submission';
        $overall = round(array_sum($normalizedScores) / count($normalizedScores), 2);

        $insert = $crad->prepare(
            "INSERT INTO manuscript_evaluations "
            . "(submission_id, research_group_id, evaluator_user_id, evaluator_name, content_score, "
            . "methodology_score, results_score, conclusions_score, recommendations_score, references_score, "
            . "formatting_score, compliance_score, remarks, result, overall_score) "
            . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert->execute([
            $submissionId,
            (int) $submission['research_group_id'],
            $evaluatorUserId,
            trim($evaluatorName),
            $normalizedScores['content'],
            $normalizedScores['methodology'],
            $normalizedScores['results'],
            $normalizedScores['conclusions'],
            $normalizedScores['recommendations'],
            $normalizedScores['references'],
            $normalizedScores['formatting'],
            $normalizedScores['compliance'],
            trim($remarks),
            $result,
            $overall,
        ]);
        $evaluationId = (int) $crad->lastInsertId();

        $crad->prepare(
            'UPDATE manuscript_submissions SET status = ?, reviewed_at = NOW(), '
            . 'locked_at = IF(? = \'Approved\', NOW(), locked_at), '
            . 'locked_by_user = IF(? = \'Approved\', ?, locked_by_user) WHERE id = ?'
        )->execute([$status, $status, $status, $evaluatorUserId, $submissionId]);

        if (!cradWorkflowCanTransition('Final Manuscript Evaluation', $toPhase)) {
            throw new RuntimeException('The manuscript result cannot move the group to the required next phase.');
        }
        $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?')
            ->execute([$toPhase, (int) $submission['group_term_id']]);
        $crad->prepare('UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?')
            ->execute([$toPhase, (int) $submission['research_group_id']]);

        cradLogWorkflowEvent(
            $crad,
            (int) $submission['research_group_id'],
            (int) $submission['group_academic_term_id'],
            (int) $submission['group_term_id'],
            $action === 'approve' ? 'FINAL_MANUSCRIPT_APPROVED_FOR_DEFENSE' : 'FINAL_MANUSCRIPT_REVISION_REQUESTED',
            'Final Manuscript Evaluation',
            $toPhase,
            $evaluatorUserId,
            $evaluatorName,
            trim($remarks) ?: null,
            'manuscript_evaluation',
            $evaluationId,
            ['submission_id' => $submissionId, 'result' => $result, 'overall_score' => $overall]
        );

        $crad->commit();
    } catch (Throwable $exception) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        throw $exception;
    }

    frNotifyStudentLeader(
        $crad,
        (int) $submission['research_group_id'],
        $action === 'approve' ? 'final_manuscript_approved_for_defense' : 'final_manuscript_revision_requested',
        $action === 'approve' ? 'Official manuscript approved' : 'Official manuscript needs revision',
        $action === 'approve'
            ? 'The official Chapters 1–5 manuscript was approved for the Final Defense scheduling stage.'
            : 'CRAD returned the official Chapters 1–5 manuscript for revision. Submit a new version after addressing the remarks.',
        'manuscript_evaluation',
        $evaluationId,
        BASE_URL . '/modules/student-portal/pages/final-manuscript.php'
    );

    return [
        'id' => $evaluationId,
        'result' => $result,
        'status' => $status,
        'overall_score' => $overall,
        'to_phase' => $toPhase,
    ];
}

function fpGetManuscriptEvaluation(PDO $crad, int $submissionId): ?array
{
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare("SELECT * FROM manuscript_evaluations WHERE submission_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$submissionId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
}

function fpIsManuscriptApproved(PDO $crad, int $groupId): bool
{
    $submission = fpGetLatestManuscriptSubmission($crad, $groupId, 'Final Defense');
    $evaluation = $submission ? fpGetManuscriptEvaluation($crad, (int) $submission['id']) : null;
    return (string) ($submission['status'] ?? '') === 'Approved' && (string) ($evaluation['result'] ?? '') === 'APPROVED';
}

function fpGetFinalDefenseSchedule(PDO $crad, int $groupId): ?array
{
    $stmt = $crad->prepare("SELECT * FROM research_defense_schedules WHERE research_group_id = ? AND LOWER(TRIM(COALESCE(defense_type, ''))) IN ('final defense','re-defense') ORDER BY defense_datetime DESC, id DESC LIMIT 1");
    $stmt->execute([$groupId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
}

function fpGetFinalDefensePanel(PDO $crad, int $groupId): array
{
    $stmt = $crad->prepare("SELECT * FROM research_panel_assignments WHERE research_group_id = ? AND defense_phase = 'Final Defense' AND assignment_status = 'Assigned' ORDER BY id ASC");
    $stmt->execute([$groupId]); return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fpGetLatestFinalDefenseOfficialResult(PDO $crad, int $groupId): ?array
{
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare(
        "SELECT dor.*, da.academic_term_id, da.defense_type, da.attempt_number,
                da.status AS attempt_status, da.defense_schedule_id
         FROM defense_official_results dor
         INNER JOIN defense_attempts da ON da.id = dor.defense_attempt_id
         WHERE da.research_group_id = ?
           AND da.defense_type IN ('Final Defense','Re-Defense')
         ORDER BY da.attempt_number DESC, da.id DESC LIMIT 1"
    );
    $stmt->execute([$groupId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function fpGroupNeedsFinalRevision(PDO $crad, int $groupId): bool
{
    $official = fpGetLatestFinalDefenseOfficialResult($crad, $groupId);
    $needsRevision = (string) ($official['official_result'] ?? '') === 'PASSED WITH REVISIONS';
    if ($needsRevision) {
        $cycle = $crad->prepare("INSERT INTO research_revision_cycles (research_group_id, defense_schedule_id, defense_attempt_id, official_result, revision_status) VALUES (?, ?, ?, 'PASSED WITH REVISIONS', 'Needs Revision') ON DUPLICATE KEY UPDATE defense_attempt_id = VALUES(defense_attempt_id), official_result = VALUES(official_result), revision_status = IF(revision_status = 'Compliant', revision_status, 'Needs Revision')");
        $cycle->execute([$groupId, (int) $official['defense_schedule_id'], (int) $official['defense_attempt_id']]);
    }
    return $needsRevision;
}

function fpGetRevisionCycle(PDO $crad, int $groupId): ?array
{
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare("SELECT * FROM research_revision_cycles WHERE research_group_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$groupId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
}

function fpGetLatestDefenseRevisionSubmission(PDO $crad, int $groupId, ?int $defenseAttemptId = null): ?array
{
    finalPhaseEnsureSchema($crad);
    $sql = 'SELECT * FROM defense_revision_submissions WHERE research_group_id = ?';
    $params = [$groupId];
    if ($defenseAttemptId !== null) {
        $sql .= ' AND defense_attempt_id = ?';
        $params[] = $defenseAttemptId;
    }
    $sql .= ' ORDER BY version_number DESC, id DESC LIMIT 1';
    $stmt = $crad->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function fpGetDefenseRevisionSubmissionHistory(PDO $crad, int $groupId): array
{
    finalPhaseEnsureSchema($crad);
    $stmt = $crad->prepare(
        'SELECT * FROM defense_revision_submissions WHERE research_group_id = ? ORDER BY version_number DESC, id DESC'
    );
    $stmt->execute([$groupId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** @param array<string,mixed> $file */
function fpSubmitDefenseRevision(
    PDO $crad,
    int $groupId,
    int $submittedByUser,
    string $submittedByName,
    array $file,
    string $responseNotes
): int {
    finalPhaseEnsureSchema($crad);
    if ($groupId <= 0 || trim((string) ($file['file_checksum'] ?? '')) === '') {
        throw new InvalidArgumentException('A verified revision file is required.');
    }

    $official = fpGetLatestFinalDefenseOfficialResult($crad, $groupId);
    if (!$official || (string) ($official['official_result'] ?? '') !== 'PASSED WITH REVISIONS') {
        throw new RuntimeException('This group does not have a finalized Final Defense revision requirement.');
    }
    fpGroupNeedsFinalRevision($crad, $groupId);
    $cycle = fpGetRevisionCycle($crad, $groupId);
    if (!$cycle || (int) ($cycle['defense_attempt_id'] ?? 0) !== (int) $official['defense_attempt_id']) {
        throw new RuntimeException('The active Final Defense revision cycle could not be verified.');
    }
    $groupTerm = frGetActiveGroupTerm($crad, $groupId);
    if (!$groupTerm || (string) ($groupTerm['current_phase'] ?? '') !== 'Post-Defense Revision') {
        throw new RuntimeException('Revision evidence can be submitted only during Post-Defense Revision.');
    }

    $crad->beginTransaction();
    try {
        $lock = $crad->prepare('SELECT * FROM research_revision_cycles WHERE id = ? FOR UPDATE');
        $lock->execute([(int) $cycle['id']]);
        $lockedCycle = $lock->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$lockedCycle || (string) $lockedCycle['revision_status'] === 'Compliant') {
            throw new RuntimeException('This revision cycle is already complete.');
        }

        $latest = fpGetLatestDefenseRevisionSubmission($crad, $groupId, (int) $official['defense_attempt_id']);
        if ($latest && !in_array((string) $latest['status'], ['For Resubmission', 'Superseded'], true)) {
            throw new RuntimeException('The latest revision evidence is still under review.');
        }
        $version = (int) ($latest['version_number'] ?? 0) + 1;
        if ($latest) {
            $crad->prepare("UPDATE defense_revision_submissions SET status = 'Superseded', updated_at = NOW() WHERE id = ?")
                ->execute([(int) $latest['id']]);
        }

        $token = bin2hex(random_bytes(32));
        $insert = $crad->prepare(
            "INSERT INTO defense_revision_submissions
                (defense_attempt_id, research_group_id, version_number, submitted_by_user,
                 submitted_by_name, response_notes, original_name, stored_subdir, stored_name,
                 file_size, file_mime, file_checksum, status, submission_token)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted', ?)"
        );
        $insert->execute([
            (int) $official['defense_attempt_id'],
            $groupId,
            $version,
            $submittedByUser ?: null,
            trim($submittedByName),
            trim($responseNotes),
            (string) ($file['original_name'] ?? ''),
            (string) ($file['stored_subdir'] ?? ''),
            (string) ($file['stored_name'] ?? ''),
            (int) ($file['file_size'] ?? 0),
            (string) ($file['file_mime'] ?? ''),
            (string) $file['file_checksum'],
            $token,
        ]);
        $submissionId = (int) $crad->lastInsertId();

        $crad->prepare(
            "UPDATE research_revision_cycles
             SET revision_submission_id = ?, revision_status = 'Under Review', completed_at = NULL,
                 compliance_verified_by = NULL, compliance_verified_at = NULL,
                 compliance_remarks = NULL, updated_at = NOW()
             WHERE id = ?"
        )->execute([$submissionId, (int) $lockedCycle['id']]);
        $crad->prepare(
            "UPDATE defense_revision_items SET status = 'Submitted', updated_at = NOW()
             WHERE defense_attempt_id = ? AND status IN ('Open','Rejected')"
        )->execute([(int) $official['defense_attempt_id']]);
        $crad->commit();
        return $submissionId;
    } catch (Throwable $exception) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        throw $exception;
    }
}

function fpReviewDefenseRevisionSubmission(
    PDO $crad,
    int $groupId,
    string $status,
    int $actorUserId,
    string $actorName,
    string $remarks
): bool {
    if (!in_array($status, ['Needs Revision', 'Under Review', 'Compliant'], true)) {
        return false;
    }
    finalPhaseEnsureSchema($crad);
    fpGroupNeedsFinalRevision($crad, $groupId);
    $cycle = fpGetRevisionCycle($crad, $groupId);
    if (!$cycle) {
        return false;
    }
    $latest = fpGetLatestDefenseRevisionSubmission($crad, $groupId, (int) ($cycle['defense_attempt_id'] ?? 0));
    if (!$latest || in_array((string) $latest['status'], ['For Resubmission', 'Superseded'], true)) {
        return false;
    }
    if (in_array($status, ['Needs Revision', 'Compliant'], true) && trim($remarks) === '') {
        throw new InvalidArgumentException('Review remarks are required for this decision.');
    }

    $submissionStatus = match ($status) {
        'Needs Revision' => 'For Resubmission',
        'Under Review' => 'Under Review',
        'Compliant' => 'Complied',
    };
    $itemStatus = match ($status) {
        'Needs Revision' => 'Rejected',
        'Under Review' => 'Submitted',
        'Compliant' => 'Complied',
    };

    $crad->beginTransaction();
    try {
        $crad->prepare(
            'UPDATE defense_revision_submissions
             SET status = ?, reviewed_by_user = ?, reviewed_at = NOW(), review_remarks = ?, updated_at = NOW()
             WHERE id = ?'
        )->execute([$submissionStatus, $actorUserId ?: null, trim($remarks), (int) $latest['id']]);
        $crad->prepare(
            'UPDATE defense_revision_items
             SET status = ?, verified_by_user = ?, verified_at = NOW(), verification_remarks = ?, updated_at = NOW()
             WHERE defense_attempt_id = ?'
        )->execute([$itemStatus, $actorUserId ?: null, trim($remarks), (int) $cycle['defense_attempt_id']]);
        $crad->prepare(
            "UPDATE research_revision_cycles
             SET revision_submission_id = ?, revision_status = ?,
                 completed_at = IF(? = 'Compliant', NOW(), NULL),
                 compliance_verified_by = IF(? = 'Compliant', ?, NULL),
                 compliance_verified_at = IF(? = 'Compliant', NOW(), NULL),
                 compliance_remarks = ?, updated_at = NOW()
             WHERE id = ?"
        )->execute([
            (int) $latest['id'], $status, $status, $status, $actorUserId ?: null,
            $status, trim($remarks), (int) $cycle['id'],
        ]);

        if ($status === 'Compliant') {
            $groupTerm = frGetActiveGroupTerm($crad, $groupId);
            if ($groupTerm && (string) $groupTerm['current_phase'] === 'Post-Defense Revision') {
                $crad->prepare("UPDATE research_group_terms SET current_phase = 'Final Manuscript Approval' WHERE id = ?")
                    ->execute([(int) $groupTerm['id']]);
                $crad->prepare("UPDATE research_groups SET current_phase = 'Final Manuscript Approval', current_phase_started_at = NOW() WHERE id = ?")
                    ->execute([$groupId]);
                cradLogWorkflowEvent(
                    $crad,
                    $groupId,
                    (int) $groupTerm['academic_term_id'],
                    (int) $groupTerm['id'],
                    'FINAL_DEFENSE_REVISION_COMPLIANT',
                    'Post-Defense Revision',
                    'Final Manuscript Approval',
                    $actorUserId ?: null,
                    trim($actorName),
                    trim($remarks),
                    'defense_revision_submission',
                    (int) $latest['id']
                );
            }
        }
        $crad->commit();
        return true;
    } catch (Throwable $exception) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        throw $exception;
    }
}

function fpSetRevisionStatus(
    PDO $crad,
    int $groupId,
    string $status,
    int $actorUserId = 0,
    string $actorName = '',
    string $remarks = ''
): bool {
    return fpReviewDefenseRevisionSubmission($crad, $groupId, $status, $actorUserId, $actorName, $remarks);
}

function fpIsEligibleForFinalApproval(PDO $crad, int $groupId): bool
{
    $official = fpGetLatestFinalDefenseOfficialResult($crad, $groupId);
    if (!$official || empty($official['finalized_at'])) return false;
    if ((string) $official['official_result'] === 'PASSED WITH REVISIONS') {
        fpGroupNeedsFinalRevision($crad, $groupId);
        return (string) (fpGetRevisionCycle($crad, $groupId)['revision_status'] ?? '') === 'Compliant';
    }
    return (string) $official['official_result'] === 'PASSED';
}

function fpGetFinalDefenseRevisionGroups(PDO $crad, int $adviserUserId, string $adviserEmail): array
{
    finalPhaseEnsureSchema($crad);
    $adviserEmail = strtolower(trim($adviserEmail));
    $stmt = $crad->prepare(
        "SELECT rg.id AS research_group_id, rg.group_number, rg.group_name, rg.research_title,
                rg.academic_year, rds.id AS defense_schedule_id, rds.defense_datetime, rds.venue,
                dor.official_result, da.id AS defense_attempt_id,
                COUNT(DISTINCT rpa.panel_user_id) AS assigned_panel_count,
                COUNT(DISTINCT fde.panel_user_id) AS submitted_eval_count,
                COUNT(DISTINCT CASE WHEN fde.result = 'APPROVED WITH REVISION' THEN fde.panel_user_id END) AS awr_count,
                rc.revision_status, rc.opened_at, rc.updated_at AS revision_updated_at
         FROM research_groups rg
         INNER JOIN research_defense_schedules rds
           ON rds.research_group_id = rg.id
          AND LOWER(TRIM(COALESCE(rds.defense_type, ''))) IN ('final defense','re-defense')
         INNER JOIN defense_attempts da
           ON da.defense_schedule_id = rds.id
         INNER JOIN defense_official_results dor
           ON dor.defense_attempt_id = da.id
          AND dor.official_result = 'PASSED WITH REVISIONS'
         INNER JOIN research_panel_assignments rpa
           ON rpa.research_group_id = rg.id
          AND rpa.defense_phase = 'Final Defense'
          AND rpa.assignment_status = 'Assigned'
         LEFT JOIN final_defense_evaluations fde
           ON fde.defense_schedule_id = rds.id
          AND fde.panel_user_id = rpa.panel_user_id
         LEFT JOIN research_revision_cycles rc
           ON rc.research_group_id = rg.id
          AND rc.defense_schedule_id = rds.id
         WHERE EXISTS (
             SELECT 1
             FROM research_adviser_assignments raa
             WHERE raa.assignment_status IN ('Assigned', 'Confirmed')
               AND (raa.research_group_id = rg.id
                    OR (raa.research_group_id IS NULL AND raa.group_number = rg.group_number))
               AND ((raa.adviser_user_id IS NOT NULL AND raa.adviser_user_id = :adviser_user_id)
                    OR (:adviser_email <> '' AND LOWER(TRIM(COALESCE(raa.adviser_email, ''))) = :adviser_email_match))
         )
         GROUP BY rg.id, rg.group_number, rg.group_name, rg.research_title, rg.academic_year,
                  rds.id, rds.defense_datetime, rds.venue, dor.official_result, da.id,
                  rc.revision_status, rc.opened_at, rc.updated_at
         HAVING assigned_panel_count > 0
            AND submitted_eval_count = assigned_panel_count
         ORDER BY rds.defense_datetime DESC, rg.id DESC"
    );
    $stmt->execute([
        ':adviser_user_id' => $adviserUserId,
        ':adviser_email' => $adviserEmail,
        ':adviser_email_match' => $adviserEmail,
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rows as &$row) {
        $groupId = (int) ($row['research_group_id'] ?? 0);
        fpGroupNeedsFinalRevision($crad, $groupId);
        $cycle = fpGetRevisionCycle($crad, $groupId);
        $row['revision_status'] = (string) (($cycle['revision_status'] ?? '') ?: 'Needs Revision');
        $row['opened_at'] = $cycle['opened_at'] ?? null;
        $row['revision_updated_at'] = $cycle['updated_at'] ?? null;
        $revisionSubmission = fpGetLatestDefenseRevisionSubmission(
            $crad,
            $groupId,
            (int) ($row['defense_attempt_id'] ?? 0)
        );
        $row['revision_submission'] = $revisionSubmission;
        $row['revision_submitted'] = $revisionSubmission !== null
            && !in_array((string) ($revisionSubmission['status'] ?? ''), ['For Resubmission', 'Superseded'], true);
        $row['revision_update_id'] = $revisionSubmission['id'] ?? null;
        $row['revision_update_title'] = $revisionSubmission['original_name'] ?? null;
        $row['revision_submitted_at'] = $revisionSubmission['submitted_at'] ?? null;
        $row['revision_milestone_status'] = $revisionSubmission['status'] ?? null;
    }
    unset($row);
    return $rows;
}

function fpGetFinalDefenseRevisionDetail(PDO $crad, int $groupId, int $adviserUserId, string $adviserEmail): ?array
{
    $groups = fpGetFinalDefenseRevisionGroups($crad, $adviserUserId, $adviserEmail);
    $group = null;
    foreach ($groups as $candidate) {
        if ((int) ($candidate['research_group_id'] ?? 0) === $groupId) {
            $group = $candidate;
            break;
        }
    }
    if (!$group) {
        return null;
    }

    $stmt = $crad->prepare(
        "SELECT fde.panel_name, fde.panel_user_id, fde.content_score, fde.methodology_score,
                fde.references_score, fde.format_score, fde.overall_score, fde.result,
                fde.remarks, fde.submitted_at
         FROM final_defense_evaluations fde
         WHERE fde.defense_schedule_id = ?
         ORDER BY fde.panel_name ASC, fde.panel_user_id ASC"
    );
    $stmt->execute([(int) $group['defense_schedule_id']]);
    $group['panel_evaluations'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $itemStatement = $crad->prepare(
        'SELECT * FROM defense_revision_items WHERE defense_attempt_id = ? ORDER BY id ASC'
    );
    $itemStatement->execute([(int) ($group['defense_attempt_id'] ?? 0)]);
    $group['revision_items'] = $itemStatement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $group['revision_status'] = (string) ($group['revision_status'] ?: 'Needs Revision');
    return $group;
}

function fpSetFinalDefenseRevisionStatus(
    PDO $crad,
    int $groupId,
    int $adviserUserId,
    string $adviserEmail,
    string $status,
    string $adviserName = '',
    string $remarks = ''
): bool
{
    if (!in_array($status, ['Needs Revision', 'Under Review', 'Compliant'], true)) {
        return false;
    }

    $groups = fpGetFinalDefenseRevisionGroups($crad, $adviserUserId, $adviserEmail);
    $group = null;
    foreach ($groups as $candidate) {
        if ((int) ($candidate['research_group_id'] ?? 0) === $groupId) {
            $group = $candidate;
            break;
        }
    }
    if (!$group) {
        return false;
    }

    if (in_array($status, ['Under Review', 'Compliant'], true) && empty($group['revision_submitted'])) {
        return false;
    }

    return fpReviewDefenseRevisionSubmission(
        $crad,
        $groupId,
        $status,
        $adviserUserId,
        $adviserName,
        $remarks
    );
}

function fpGetFinalManuscriptApproval(PDO $crad, int $groupId): ?array
{
    finalPhaseEnsureSchema($crad); $stmt = $crad->prepare("SELECT * FROM final_manuscript_approvals WHERE research_group_id = ? LIMIT 1");
    $stmt->execute([$groupId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
}

/** @return array<int,array<string,mixed>> */
function fpGetFinalManuscriptApprovalHistory(PDO $crad, int $groupId): array
{
    finalPhaseEnsureSchema($crad);
    $statement = $crad->prepare(
        'SELECT * FROM final_manuscript_approval_history '
        . 'WHERE research_group_id = ? ORDER BY decision_sequence ASC, id ASC'
    );
    $statement->execute([$groupId]);
    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** @param array<string,mixed> $approval */
function fpAppendFinalManuscriptApprovalHistory(
    PDO $crad,
    array $approval,
    string $eventSource = 'WORKFLOW'
): int {
    $groupId = (int) ($approval['research_group_id'] ?? 0);
    if ($groupId <= 0) {
        throw new InvalidArgumentException('A research group is required for approval history.');
    }

    $sequenceStatement = $crad->prepare(
        'SELECT COALESCE(MAX(decision_sequence), 0) + 1 '
        . 'FROM final_manuscript_approval_history WHERE research_group_id = ?'
    );
    $sequenceStatement->execute([$groupId]);
    $sequence = (int) $sequenceStatement->fetchColumn();
    $decisionAt = (string) (
        ((string) ($approval['status'] ?? '') === 'Approved' ? ($approval['approved_at'] ?? null) : null)
        ?? $approval['updated_at']
        ?? $approval['created_at']
        ?? date('Y-m-d H:i:s')
    );

    $insert = $crad->prepare(
        "INSERT INTO final_manuscript_approval_history
            (approval_id, research_group_id, decision_sequence, decision_status,
             actor_user_id, actor_name, remarks, decision_at, defense_schedule_id,
             manuscript_submission_id, revision_submission_id, defense_attempt_id,
             revision_cycle_id, file_checksum, approval_token, event_source, source_updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insert->execute([
        (int) ($approval['id'] ?? 0) ?: null,
        $groupId,
        $sequence,
        (string) ($approval['status'] ?? 'Pending'),
        (int) ($approval['approved_by_user'] ?? $approval['actor_user_id'] ?? 0) ?: null,
        trim((string) ($approval['approved_by_name'] ?? $approval['actor_name'] ?? '')),
        $approval['remarks'] ?? null,
        $decisionAt,
        (int) ($approval['defense_schedule_id'] ?? 0) ?: null,
        (int) ($approval['manuscript_submission_id'] ?? 0) ?: null,
        (int) ($approval['revision_submission_id'] ?? 0) ?: null,
        (int) ($approval['defense_attempt_id'] ?? 0) ?: null,
        (int) ($approval['revision_cycle_id'] ?? 0) ?: null,
        strtolower(trim((string) ($approval['file_checksum'] ?? ''))),
        trim((string) ($approval['approval_token'] ?? '')),
        substr(trim($eventSource), 0, 60) ?: 'WORKFLOW',
        $approval['updated_at'] ?? null,
    ]);
    return (int) $crad->lastInsertId();
}

/** @param array<string,mixed> $approval */
function fpArchiveFinalManuscriptApprovalStateIfNeeded(PDO $crad, array $approval): void
{
    $groupId = (int) ($approval['research_group_id'] ?? 0);
    if ($groupId <= 0) {
        return;
    }

    $latestStatement = $crad->prepare(
        'SELECT * FROM final_manuscript_approval_history '
        . 'WHERE research_group_id = ? ORDER BY decision_sequence DESC, id DESC LIMIT 1 FOR UPDATE'
    );
    $latestStatement->execute([$groupId]);
    $latest = $latestStatement->fetch(PDO::FETCH_ASSOC) ?: null;
    $sameSnapshot = $latest
        && (string) ($latest['decision_status'] ?? '') === (string) ($approval['status'] ?? '')
        && (int) ($latest['manuscript_submission_id'] ?? 0) === (int) ($approval['manuscript_submission_id'] ?? 0)
        && (int) ($latest['revision_submission_id'] ?? 0) === (int) ($approval['revision_submission_id'] ?? 0)
        && (int) ($latest['defense_attempt_id'] ?? 0) === (int) ($approval['defense_attempt_id'] ?? 0)
        && (string) ($latest['source_updated_at'] ?? '') === (string) ($approval['updated_at'] ?? '');
    if (!$sameSnapshot) {
        fpAppendFinalManuscriptApprovalHistory($crad, $approval, 'PREVIOUS_STATE_SNAPSHOT');
    }
}

function fpApproveFinalManuscript(
    PDO $crad,
    int $groupId,
    int $actorUserId,
    string $actorName,
    string $remarks
): int {
    finalPhaseEnsureSchema($crad);
    $submission = fpGetLatestManuscriptSubmission($crad, $groupId, 'Final Defense');
    if (!$submission || !fpIsManuscriptApproved($crad, $groupId)) {
        throw new RuntimeException('The latest official manuscript must be approved by CRAD first.');
    }
    $official = fpGetLatestFinalDefenseOfficialResult($crad, $groupId);
    if (!$official || empty($official['finalized_at']) || !fpIsEligibleForFinalApproval($crad, $groupId)) {
        throw new RuntimeException('Final Defense evaluation or required revision compliance is not complete.');
    }
    $groupTerm = frGetActiveGroupTerm($crad, $groupId);
    if (!$groupTerm || !in_array((string) $groupTerm['current_phase'], ['Final Manuscript Approval', 'Publication and Repository'], true)) {
        throw new RuntimeException('The group is not at the Final Manuscript Approval stage.');
    }

    $revisionCycle = null;
    $revisionSubmission = null;
    $evidence = $submission;
    if ((string) $official['official_result'] === 'PASSED WITH REVISIONS') {
        $revisionCycle = fpGetRevisionCycle($crad, $groupId);
        if (!$revisionCycle
            || (int) ($revisionCycle['defense_attempt_id'] ?? 0) !== (int) $official['defense_attempt_id']
            || (string) ($revisionCycle['revision_status'] ?? '') !== 'Compliant') {
            throw new RuntimeException('The matching Final Defense revision cycle is not compliant.');
        }
        $revisionSubmission = fpGetLatestDefenseRevisionSubmission(
            $crad,
            $groupId,
            (int) $official['defense_attempt_id']
        );
        if (!$revisionSubmission
            || (int) ($revisionCycle['revision_submission_id'] ?? 0) !== (int) $revisionSubmission['id']
            || (string) ($revisionSubmission['status'] ?? '') !== 'Complied') {
            throw new RuntimeException('The exact compliant revision submission could not be verified.');
        }
        $evidence = $revisionSubmission;
    }

    $checksum = strtolower(trim((string) ($evidence['file_checksum'] ?? '')));
    if (!preg_match('/^[a-f0-9]{64}$/', $checksum)) {
        throw new RuntimeException('The final manuscript evidence does not have a valid checksum.');
    }
    $subdir = trim((string) ($evidence['stored_subdir'] ?? ''), '/');
    $storedName = basename((string) ($evidence['stored_name'] ?? ''));
    $root = realpath(smsUploadRoot());
    $path = realpath(smsUploadRoot() . '/' . $subdir . '/' . $storedName);
    if (!$root || !$path || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
        throw new RuntimeException('The final manuscript evidence file is missing from secure storage.');
    }
    $actualChecksum = hash_file('sha256', $path);
    if ($actualChecksum === false || !hash_equals($checksum, strtolower($actualChecksum))) {
        throw new RuntimeException('The final manuscript evidence failed its integrity check.');
    }

    $crad->beginTransaction();
    try {
        $existingStatement = $crad->prepare(
            'SELECT * FROM final_manuscript_approvals WHERE research_group_id = ? LIMIT 1 FOR UPDATE'
        );
        $existingStatement->execute([$groupId]);
        $existing = $existingStatement->fetch(PDO::FETCH_ASSOC) ?: null;
        $revisionSubmissionId = $revisionSubmission ? (int) $revisionSubmission['id'] : null;
        if ($existing && (string) $existing['status'] === 'Approved') {
            $sameEvidence = (int) ($existing['manuscript_submission_id'] ?? 0) === (int) $submission['id']
                && (int) ($existing['defense_attempt_id'] ?? 0) === (int) $official['defense_attempt_id']
                && (int) ($existing['revision_submission_id'] ?? 0) === (int) ($revisionSubmissionId ?? 0)
                && hash_equals((string) ($existing['file_checksum'] ?? ''), $checksum)
                && trim((string) ($existing['approval_token'] ?? '')) !== '';
            if (!$sameEvidence) {
                throw new RuntimeException('An immutable final approval already exists for different evidence.');
            }
            $crad->commit();
            return (int) $existing['id'];
        }

        $approvalToken = bin2hex(random_bytes(32));
        if ($existing) {
            fpArchiveFinalManuscriptApprovalStateIfNeeded($crad, $existing);
            $update = $crad->prepare(
                "UPDATE final_manuscript_approvals
                 SET defense_schedule_id = ?, approved_by_user = ?, approved_by_name = ?, status = 'Approved',
                     remarks = ?, approved_at = NOW(), manuscript_submission_id = ?, revision_submission_id = ?,
                     defense_attempt_id = ?, revision_cycle_id = ?, file_checksum = ?, approval_token = ?,
                     superseded_by_approval_id = NULL, superseded_at = NULL, updated_at = NOW()
                 WHERE id = ?"
            );
            $update->execute([
                (int) ($official['defense_schedule_id'] ?? 0) ?: null,
                $actorUserId ?: null,
                trim($actorName),
                trim($remarks),
                (int) $submission['id'],
                $revisionSubmissionId,
                (int) $official['defense_attempt_id'],
                $revisionCycle ? (int) $revisionCycle['id'] : null,
                $checksum,
                $approvalToken,
                (int) $existing['id'],
            ]);
            $approvalId = (int) $existing['id'];
        } else {
            $insert = $crad->prepare(
                "INSERT INTO final_manuscript_approvals
                    (research_group_id, defense_schedule_id, approved_by_user, approved_by_name,
                     status, remarks, approved_at, manuscript_submission_id, revision_submission_id,
                     defense_attempt_id, revision_cycle_id, file_checksum, approval_token)
                 VALUES (?, ?, ?, ?, 'Approved', ?, NOW(), ?, ?, ?, ?, ?, ?)"
            );
            $insert->execute([
                $groupId,
                (int) ($official['defense_schedule_id'] ?? 0) ?: null,
                $actorUserId ?: null,
                trim($actorName),
                trim($remarks),
                (int) $submission['id'],
                $revisionSubmissionId,
                (int) $official['defense_attempt_id'],
                $revisionCycle ? (int) $revisionCycle['id'] : null,
                $checksum,
                $approvalToken,
            ]);
            $approvalId = (int) $crad->lastInsertId();
        }

        $approvedStatement = $crad->prepare(
            'SELECT * FROM final_manuscript_approvals WHERE id = ? LIMIT 1'
        );
        $approvedStatement->execute([$approvalId]);
        $approvedSnapshot = $approvedStatement->fetch(PDO::FETCH_ASSOC);
        if (!$approvedSnapshot) {
            throw new RuntimeException('The final manuscript approval could not be recorded.');
        }
        fpAppendFinalManuscriptApprovalHistory($crad, $approvedSnapshot, 'FINAL_MANUSCRIPT_APPROVED');

        $crad->prepare('UPDATE manuscript_submissions SET locked_at = NOW(), locked_by_user = ? WHERE id = ?')
            ->execute([$actorUserId ?: null, (int) $submission['id']]);
        if ((string) $groupTerm['current_phase'] === 'Final Manuscript Approval') {
            $crad->prepare("UPDATE research_group_terms SET current_phase = 'Publication and Repository' WHERE id = ?")
                ->execute([(int) $groupTerm['id']]);
            $crad->prepare("UPDATE research_groups SET current_phase = 'Publication and Repository', current_phase_started_at = NOW() WHERE id = ?")
                ->execute([$groupId]);
            cradLogWorkflowEvent(
                $crad,
                $groupId,
                (int) $groupTerm['academic_term_id'],
                (int) $groupTerm['id'],
                'FINAL_MANUSCRIPT_APPROVED',
                'Final Manuscript Approval',
                'Publication and Repository',
                $actorUserId ?: null,
                trim($actorName),
                trim($remarks),
                'final_manuscript_approval',
                $approvalId,
                [
                    'manuscript_submission_id' => (int) $submission['id'],
                    'revision_submission_id' => $revisionSubmissionId,
                    'defense_attempt_id' => (int) $official['defense_attempt_id'],
                    'file_checksum' => $checksum,
                ]
            );
        }
        $crad->commit();
        return $approvalId;
    } catch (Throwable $exception) {
        if ($crad->inTransaction()) {
            $crad->rollBack();
        }
        throw $exception;
    }
}

function fpIsFinalManuscriptApproved(PDO $crad, int $groupId): bool
{
    $approval = fpGetFinalManuscriptApproval($crad, $groupId);
    $submission = fpGetLatestManuscriptSubmission($crad, $groupId, 'Final Defense');
    $official = fpGetLatestFinalDefenseOfficialResult($crad, $groupId);
    if (!$approval || !$submission || !$official || (string) $approval['status'] !== 'Approved') {
        return false;
    }
    if ((int) ($approval['manuscript_submission_id'] ?? 0) !== (int) $submission['id']
        || (int) ($approval['defense_attempt_id'] ?? 0) !== (int) $official['defense_attempt_id']
        || trim((string) ($approval['approval_token'] ?? '')) === '') {
        return false;
    }
    $expectedChecksum = (string) ($submission['file_checksum'] ?? '');
    if ((string) $official['official_result'] === 'PASSED WITH REVISIONS') {
        $cycle = fpGetRevisionCycle($crad, $groupId);
        $revision = fpGetLatestDefenseRevisionSubmission($crad, $groupId, (int) $official['defense_attempt_id']);
        if (!$cycle || !$revision
            || (string) ($cycle['revision_status'] ?? '') !== 'Compliant'
            || (int) ($approval['revision_cycle_id'] ?? 0) !== (int) $cycle['id']
            || (int) ($approval['revision_submission_id'] ?? 0) !== (int) $revision['id']) {
            return false;
        }
        $expectedChecksum = (string) ($revision['file_checksum'] ?? '');
    }
    return $expectedChecksum !== '' && hash_equals((string) ($approval['file_checksum'] ?? ''), $expectedChecksum);
}

function fpNotifyFinalManuscriptApproval(PDO $crad, array $submission, string $title, string $body): void
{
    $submissionId = (int) ($submission['id'] ?? 0);
    if ($submissionId <= 0) {
        return;
    }

    try {
        $crad->prepare(
            "INSERT IGNORE INTO chapter_evaluation_notifications
                (event_key, recipient_user_id, recipient_role, recipient_email, submission_id, type, title, body, url)
             VALUES (?, ?, 'student', ?, ?, 'final_manuscript_approved', ?, ?, ?)"
        )->execute([
            'student:final_manuscript_approved:' . $submissionId,
            (int) ($submission['submitted_by_user'] ?? 0) ?: null,
            (string) ($submission['submitted_by_email'] ?? ''),
            $submissionId,
            $title,
            $body,
            BASE_URL . '/modules/student-portal/pages/final-manuscript.php',
        ]);
    } catch (Throwable $e) {
        error_log('Final manuscript approval notification failed: ' . $e->getMessage());
    }
}

function fpResearchGroupSummary(PDO $crad, int $groupId): ?array
{
    $stmt = $crad->prepare("SELECT id, group_number, COALESCE(NULLIF(group_name,''), group_number, 'Research Group') AS group_name, research_title, adviser AS adviser_name, academic_year FROM research_groups WHERE id = ? LIMIT 1");
    $stmt->execute([$groupId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
}

function fpIsAssignedAdviser(PDO $crad, int $groupId, int $userId, string $email): bool
{
    if ($groupId <= 0 || ($userId <= 0 && trim($email) === '')) {
        return false;
    }

    $stmt = $crad->prepare(
        "SELECT COUNT(*)
         FROM research_adviser_assignments
         WHERE research_group_id = ?
           AND assignment_status IN ('Assigned', 'Confirmed')
         AND ((adviser_user_id IS NOT NULL AND adviser_user_id = ?)
             OR (? <> '' AND LOWER(TRIM(COALESCE(adviser_email, ''))) = LOWER(?)))"
    );
    $stmt->execute([$groupId, $userId, trim($email), trim($email)]);
    return (int) $stmt->fetchColumn() > 0;
}
