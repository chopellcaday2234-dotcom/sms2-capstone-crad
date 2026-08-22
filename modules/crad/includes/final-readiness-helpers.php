<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/second-semester-workflow.php';
require_once __DIR__ . '/research-progress-helpers.php';

function frEnsureSchema(PDO $crad): void
{
    static $readyConnections = [];
    $connectionId = spl_object_id($crad);
    if (isset($readyConnections[$connectionId])) {
        return;
    }
    cradEnsureSecondSemesterSchema($crad);
    rpMigrateLegacyFinalDefenseRecommendations($crad);
    $readyConnections[$connectionId] = true;
}

/** @return array<string,mixed>|null */
function frGetActiveGroupTerm(PDO $crad, int $groupId): ?array
{
    frEnsureSchema($crad);
    $statement = $crad->prepare(
        "SELECT rgt.*, at.academic_year, at.semester, at.term_code, at.status AS academic_term_status "
        . "FROM research_group_terms rgt "
        . "INNER JOIN academic_terms at ON at.id = rgt.academic_term_id "
        . "WHERE rgt.research_group_id = ? AND rgt.status = 'Active' "
        . "AND at.status = 'Active' AND at.semester = '2nd Semester' "
        . "ORDER BY rgt.id DESC LIMIT 1"
    );
    $statement->execute([$groupId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/** @return array<string,mixed>|null */
function frGetLatestFinalDraft(PDO $crad, int $groupId, ?int $academicTermId = null): ?array
{
    frEnsureSchema($crad);
    $sql = 'SELECT * FROM final_draft_submissions WHERE research_group_id = ?';
    $params = [$groupId];
    if ($academicTermId !== null) {
        $sql .= ' AND academic_term_id = ?';
        $params[] = $academicTermId;
    }
    $sql .= ' ORDER BY version_number DESC, id DESC LIMIT 1';

    $statement = $crad->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/** @return array<int,array<string,mixed>> */
function frGetFinalDraftHistory(PDO $crad, int $groupId, ?int $academicTermId = null): array
{
    $sql = 'SELECT fds.*, fdr.decision AS review_decision, fdr.remarks AS review_remarks '
        . 'FROM final_draft_submissions fds '
        . 'LEFT JOIN final_draft_reviews fdr ON fdr.submission_id = fds.id AND fdr.is_current = 1 '
        . 'WHERE fds.research_group_id = ?';
    $params = [$groupId];
    if ($academicTermId !== null) {
        $sql .= ' AND fds.academic_term_id = ?';
        $params[] = $academicTermId;
    }
    $sql .= ' ORDER BY fds.version_number DESC, fds.id DESC';

    $statement = $crad->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** @return array<string,mixed>|null */
function frGetCurrentFinalDraftReview(PDO $crad, int $submissionId): ?array
{
    $statement = $crad->prepare(
        'SELECT * FROM final_draft_reviews WHERE submission_id = ? AND is_current = 1 ORDER BY id DESC LIMIT 1'
    );
    $statement->execute([$submissionId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/** @return array<string,mixed>|null */
function frGetCurrentReadinessCheck(PDO $crad, int $groupId, int $academicTermId): ?array
{
    $statement = $crad->prepare(
        'SELECT * FROM final_readiness_checks '
        . 'WHERE research_group_id = ? AND academic_term_id = ? AND is_current = 1 '
        . 'ORDER BY id DESC LIMIT 1'
    );
    $statement->execute([$groupId, $academicTermId]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function frIsAssignedAdviser(PDO $crad, int $groupId, int $userId, string $email): bool
{
    if ($groupId <= 0 || ($userId <= 0 && trim($email) === '')) {
        return false;
    }

    $statement = $crad->prepare(
        "SELECT COUNT(*) FROM research_adviser_assignments "
        . "WHERE research_group_id = ? AND assignment_status IN ('Assigned','Confirmed') "
        . "AND ((adviser_user_id IS NOT NULL AND adviser_user_id = ?) "
        . "OR (? <> '' AND LOWER(TRIM(COALESCE(adviser_email, ''))) = LOWER(?)))"
    );
    $statement->execute([$groupId, $userId, trim($email), trim($email)]);
    return (int) $statement->fetchColumn() > 0;
}

/** @return array{requirements:array<string,bool>,missing:string[],ready:bool} */
function frEvaluateReadinessRequirements(?array $draft, ?array $review, ?array $check): array
{
    $requirements = [
        'chapter_1' => !empty($review['chapter_1_accepted']),
        'chapter_2' => !empty($review['chapter_2_accepted']),
        'chapter_3' => !empty($review['chapter_3_accepted']),
        'chapter_4' => !empty($review['chapter_4_accepted']),
        'chapter_5' => !empty($review['chapter_5_accepted']),
        'adviser_endorsed' => ($review['decision'] ?? '') === 'Endorsed'
            && ($draft['status'] ?? '') === 'Endorsed',
        'formatting_accepted' => !empty($review['formatting_accepted']),
        'citations_accepted' => !empty($review['citations_accepted']),
        'ethics_clearance' => !empty($check['ethics_clearance_complete']),
        'similarity_check' => !empty($check['similarity_check_complete']),
        'required_documents' => !empty($check['required_documents_complete']),
    ];

    $labels = [
        'chapter_1' => 'Chapter 1 adviser acceptance',
        'chapter_2' => 'Chapter 2 adviser acceptance',
        'chapter_3' => 'Chapter 3 adviser acceptance',
        'chapter_4' => 'Chapter 4 adviser acceptance',
        'chapter_5' => 'Chapter 5 adviser acceptance',
        'adviser_endorsed' => 'Formal adviser endorsement',
        'formatting_accepted' => 'Formatting review',
        'citations_accepted' => 'Citation/reference review',
        'ethics_clearance' => 'Ethics clearance',
        'similarity_check' => 'Similarity/plagiarism check',
        'required_documents' => 'Required supporting documents',
    ];
    $missing = [];
    foreach ($requirements as $key => $complete) {
        if (!$complete) {
            $missing[] = $labels[$key];
        }
    }

    return [
        'requirements' => $requirements,
        'missing' => $missing,
        'ready' => $missing === [] && ($check['overall_status'] ?? '') === 'Ready',
    ];
}

/**
 * @return array{
 *   group_term:?array,final_draft:?array,adviser_review:?array,readiness_check:?array,recommendation:?array,
 *   requirements:array<string,bool>,missing:string[],ready:bool
 * }
 */
function frGetReadinessSnapshot(PDO $crad, int $groupId): array
{
    frEnsureSchema($crad);
    $groupTerm = frGetActiveGroupTerm($crad, $groupId);
    $termId = (int) ($groupTerm['academic_term_id'] ?? 0);
    $draft = $termId > 0 ? frGetLatestFinalDraft($crad, $groupId, $termId) : null;
    $review = $draft ? frGetCurrentFinalDraftReview($crad, (int) $draft['id']) : null;
    $check = $termId > 0 ? frGetCurrentReadinessCheck($crad, $groupId, $termId) : null;

    if ($check && (
        (int) ($check['final_draft_submission_id'] ?? 0) !== (int) ($draft['id'] ?? 0)
        || (int) ($check['adviser_review_id'] ?? 0) !== (int) ($review['id'] ?? 0)
    )) {
        $check = null;
    }

    $recommendationStatement = $crad->prepare(
        'SELECT * FROM final_defense_recommendations WHERE research_group_id = ? LIMIT 1'
    );
    $recommendationStatement->execute([$groupId]);
    $recommendation = $recommendationStatement->fetch(PDO::FETCH_ASSOC) ?: null;

    $evaluation = frEvaluateReadinessRequirements($draft, $review, $check);

    return [
        'group_term' => $groupTerm,
        'final_draft' => $draft,
        'adviser_review' => $review,
        'readiness_check' => $check,
        'recommendation' => $recommendation,
        'requirements' => $evaluation['requirements'],
        'missing' => $evaluation['missing'],
        'ready' => $evaluation['ready'],
    ];
}

function frCreateNotification(PDO $crad, array $notification): void
{
    try {
        if (!cradSecondSemesterTableExists($crad, 'research_progress_notifications')) {
            return;
        }
        rpCreateNotification($crad, $notification);
    } catch (Throwable $exception) {
        error_log('Final readiness notification failed: ' . $exception->getMessage());
    }
}

function frNotifyAdviser(
    PDO $crad,
    int $groupId,
    string $notificationType,
    string $title,
    string $body,
    string $entityType,
    int $entityId,
    string $actionUrl
): void {
    try {
        $statement = $crad->prepare(
            "SELECT adviser_user_id, adviser_email FROM research_adviser_assignments "
            . "WHERE research_group_id = ? AND assignment_status IN ('Assigned','Confirmed') "
            . "ORDER BY (assignment_status = 'Confirmed') DESC, id DESC LIMIT 1"
        );
        $statement->execute([$groupId]);
        $adviser = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$adviser) {
            return;
        }
        frCreateNotification($crad, [
            'recipient_user_id' => (int) ($adviser['adviser_user_id'] ?? 0) ?: null,
            'recipient_email' => (string) ($adviser['adviser_email'] ?? ''),
            'recipient_role' => 'adviser',
            'batch_key' => $notificationType . ':' . $entityId,
            'notification_type' => $notificationType,
            'title' => $title,
            'body' => $body,
            'related_entity_type' => $entityType,
            'related_entity_id' => $entityId,
            'action_url' => $actionUrl,
        ]);
    } catch (Throwable $exception) {
        error_log('Final readiness adviser notification failed: ' . $exception->getMessage());
    }
}

function frNotifyStudentLeader(
    PDO $crad,
    int $groupId,
    string $notificationType,
    string $title,
    string $body,
    string $entityType,
    int $entityId,
    string $actionUrl
): void {
    try {
        $groupStatement = $crad->prepare('SELECT leader_id, leader_email FROM research_groups WHERE id = ? LIMIT 1');
        $groupStatement->execute([$groupId]);
        $group = $groupStatement->fetch(PDO::FETCH_ASSOC);
        if (!$group) {
            return;
        }

        $recipientUserId = null;
        $recipientEmail = (string) ($group['leader_email'] ?? '');
        $leaderId = trim((string) ($group['leader_id'] ?? ''));
        if ($leaderId !== '') {
            $userStatement = $crad->prepare(
                'SELECT id, email FROM sms2_db.users WHERE student_id = ? OR id = ? ORDER BY (student_id = ?) DESC LIMIT 1'
            );
            $userStatement->execute([$leaderId, ctype_digit($leaderId) ? (int) $leaderId : 0, $leaderId]);
            $user = $userStatement->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $recipientUserId = (int) $user['id'];
                $recipientEmail = (string) ($user['email'] ?? $recipientEmail);
            }
        }

        frCreateNotification($crad, [
            'recipient_user_id' => $recipientUserId,
            'recipient_email' => $recipientEmail,
            'recipient_role' => 'student',
            'batch_key' => $notificationType . ':' . $entityId,
            'notification_type' => $notificationType,
            'title' => $title,
            'body' => $body,
            'related_entity_type' => $entityType,
            'related_entity_id' => $entityId,
            'action_url' => $actionUrl,
        ]);
    } catch (Throwable $exception) {
        error_log('Final readiness student notification failed: ' . $exception->getMessage());
    }
}

/** @param array{original_name:string,stored_subdir:string,stored_name:string,file_size:int,file_mime:string,file_checksum:string} $file */
function frSubmitFinalDraft(
    PDO $crad,
    int $groupId,
    int $submittedByUser,
    string $submittedByName,
    array $file,
    string $notes = ''
): int {
    if ($groupId <= 0 || $submittedByUser <= 0) {
        throw new InvalidArgumentException('A valid research group and student account are required.');
    }
    if (trim($file['stored_name'] ?? '') === '' || trim($file['file_checksum'] ?? '') === '') {
        throw new InvalidArgumentException('The final-draft file metadata is incomplete.');
    }

    frEnsureSchema($crad);
    $crad->beginTransaction();
    try {
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
            throw new RuntimeException('Your group must be enrolled in the active 2nd Semester before submitting a final draft.');
        }

        $fromPhase = (string) $groupTerm['current_phase'];
        if (!in_array($fromPhase, ['Final Documentation', 'Final Draft Adviser Review'], true)) {
            throw new RuntimeException('Final-draft submission is not available at the current workflow phase.');
        }

        $latestStatement = $crad->prepare(
            'SELECT * FROM final_draft_submissions '
            . 'WHERE research_group_id = ? AND academic_term_id = ? '
            . 'ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE'
        );
        $latestStatement->execute([$groupId, (int) $groupTerm['academic_term_id']]);
        $latest = $latestStatement->fetch(PDO::FETCH_ASSOC);
        if ($latest && !in_array((string) $latest['status'], ['Revision Requested', 'Superseded'], true)) {
            throw new RuntimeException('The latest final draft is still under review or already endorsed.');
        }

        $version = (int) ($latest['version_number'] ?? 0) + 1;
        $token = bin2hex(random_bytes(32));
        $insert = $crad->prepare(
            "INSERT INTO final_draft_submissions "
            . "(research_group_id, academic_term_id, version_number, supersedes_submission_id, "
            . "submitted_by_user, submitted_by_name, submission_notes, original_name, stored_subdir, stored_name, "
            . "file_size, file_mime, file_checksum, status, submission_token) "
            . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted', ?)"
        );
        $insert->execute([
            $groupId,
            (int) $groupTerm['academic_term_id'],
            $version,
            $latest ? (int) $latest['id'] : null,
            $submittedByUser,
            trim($submittedByName),
            trim($notes),
            trim($file['original_name']),
            trim($file['stored_subdir']),
            trim($file['stored_name']),
            (int) $file['file_size'],
            trim($file['file_mime']),
            trim($file['file_checksum']),
            $token,
        ]);
        $submissionId = (int) $crad->lastInsertId();

        $toPhase = 'Final Draft Adviser Review';
        if ($fromPhase !== $toPhase) {
            if (!cradWorkflowCanTransition($fromPhase, $toPhase)) {
                throw new RuntimeException('The group cannot move to Final Draft Adviser Review from its current phase.');
            }
            $termUpdate = $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?');
            $termUpdate->execute([$toPhase, (int) $groupTerm['id']]);
            $groupUpdate = $crad->prepare(
                'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
            );
            $groupUpdate->execute([$toPhase, $groupId]);
        }

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            (int) $groupTerm['academic_term_id'],
            (int) $groupTerm['id'],
            'FINAL_DRAFT_SUBMITTED',
            $fromPhase,
            $toPhase,
            $submittedByUser,
            $submittedByName,
            trim($notes) ?: null,
            'final_draft_submission',
            $submissionId,
            ['version_number' => $version, 'file_checksum' => $file['file_checksum']]
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
        'final_draft_submitted',
        'Final draft submitted for adviser review',
        'A consolidated Chapters 1-5 draft is ready for your formal review.',
        'final_draft_submission',
        $submissionId,
        BASE_URL . '/modules/faculty/pages/final-draft-review.php'
    );

    return $submissionId;
}

/**
 * @param array{chapter_1:bool,chapter_2:bool,chapter_3:bool,chapter_4:bool,chapter_5:bool,formatting:bool,citations:bool} $criteria
 */
function frSaveAdviserReview(
    PDO $crad,
    int $submissionId,
    int $adviserUserId,
    string $adviserEmail,
    string $adviserName,
    string $decision,
    array $criteria,
    string $remarks
): int {
    if (!in_array($decision, ['Revision Requested', 'Endorsed'], true)) {
        throw new InvalidArgumentException('Invalid adviser review decision.');
    }
    if ($decision === 'Revision Requested' && trim($remarks) === '') {
        throw new InvalidArgumentException('Revision remarks are required when returning a final draft.');
    }

    $requiredCriteria = ['chapter_1', 'chapter_2', 'chapter_3', 'chapter_4', 'chapter_5', 'formatting', 'citations'];
    foreach ($requiredCriteria as $criterion) {
        $criteria[$criterion] = !empty($criteria[$criterion]);
    }
    if ($decision === 'Endorsed') {
        foreach ($requiredCriteria as $criterion) {
            if (!$criteria[$criterion]) {
                throw new RuntimeException('Every chapter, formatting, and citation criterion must be accepted before endorsement.');
            }
        }
    }

    frEnsureSchema($crad);
    $crad->beginTransaction();
    try {
        $submissionStatement = $crad->prepare(
            'SELECT * FROM final_draft_submissions WHERE id = ? LIMIT 1 FOR UPDATE'
        );
        $submissionStatement->execute([$submissionId]);
        $submission = $submissionStatement->fetch(PDO::FETCH_ASSOC);
        if (!$submission) {
            throw new RuntimeException('Final-draft submission not found.');
        }
        $groupId = (int) $submission['research_group_id'];
        $termId = (int) $submission['academic_term_id'];
        if (!frIsAssignedAdviser($crad, $groupId, $adviserUserId, $adviserEmail)) {
            throw new RuntimeException('This research group is not assigned to your adviser account.');
        }
        if (!in_array((string) $submission['status'], ['Submitted', 'Under Adviser Review'], true)) {
            throw new RuntimeException('This final-draft version already has a completed adviser decision.');
        }

        $latestStatement = $crad->prepare(
            'SELECT id FROM final_draft_submissions '
            . 'WHERE research_group_id = ? AND academic_term_id = ? '
            . 'ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE'
        );
        $latestStatement->execute([$groupId, $termId]);
        if ((int) $latestStatement->fetchColumn() !== $submissionId) {
            throw new RuntimeException('Only the latest final-draft version can be reviewed.');
        }

        $groupTermStatement = $crad->prepare(
            "SELECT rgt.* FROM research_group_terms rgt "
            . "INNER JOIN academic_terms at ON at.id = rgt.academic_term_id "
            . "WHERE rgt.research_group_id = ? AND rgt.academic_term_id = ? "
            . "AND rgt.status = 'Active' AND at.status = 'Active' LIMIT 1 FOR UPDATE"
        );
        $groupTermStatement->execute([$groupId, $termId]);
        $groupTerm = $groupTermStatement->fetch(PDO::FETCH_ASSOC);
        if (!$groupTerm || (string) $groupTerm['current_phase'] !== 'Final Draft Adviser Review') {
            throw new RuntimeException('The group is not currently in Final Draft Adviser Review.');
        }

        $supersede = $crad->prepare(
            'UPDATE final_draft_reviews SET is_current = 0, superseded_at = NOW() '
            . 'WHERE submission_id = ? AND is_current = 1'
        );
        $supersede->execute([$submissionId]);

        $reviewInsert = $crad->prepare(
            'INSERT INTO final_draft_reviews '
            . '(submission_id, research_group_id, academic_term_id, adviser_user_id, adviser_name, '
            . 'chapter_1_accepted, chapter_2_accepted, chapter_3_accepted, chapter_4_accepted, chapter_5_accepted, '
            . 'formatting_accepted, citations_accepted, decision, remarks) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $reviewInsert->execute([
            $submissionId,
            $groupId,
            $termId,
            $adviserUserId,
            trim($adviserName),
            (int) $criteria['chapter_1'],
            (int) $criteria['chapter_2'],
            (int) $criteria['chapter_3'],
            (int) $criteria['chapter_4'],
            (int) $criteria['chapter_5'],
            (int) $criteria['formatting'],
            (int) $criteria['citations'],
            $decision,
            trim($remarks),
        ]);
        $reviewId = (int) $crad->lastInsertId();

        $submissionUpdate = $crad->prepare(
            'UPDATE final_draft_submissions SET status = ?, adviser_user_id = ?, adviser_name = ?, '
            . 'adviser_remarks = ?, reviewed_at = NOW() WHERE id = ?'
        );
        $submissionUpdate->execute([
            $decision,
            $adviserUserId,
            trim($adviserName),
            trim($remarks),
            $submissionId,
        ]);

        $fromPhase = 'Final Draft Adviser Review';
        $toPhase = $decision === 'Endorsed' ? 'Final Defense Readiness' : 'Final Documentation';
        if (!cradWorkflowCanTransition($fromPhase, $toPhase)) {
            throw new RuntimeException('The adviser decision cannot be applied to the current workflow.');
        }
        $termUpdate = $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?');
        $termUpdate->execute([$toPhase, (int) $groupTerm['id']]);
        $groupUpdate = $crad->prepare(
            'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
        );
        $groupUpdate->execute([$toPhase, $groupId]);

        $invalidateChecks = $crad->prepare(
            'UPDATE final_readiness_checks SET is_current = 0, superseded_at = NOW(), '
            . "overall_status = IF(overall_status = 'Ready', 'Revoked', overall_status) "
            . 'WHERE research_group_id = ? AND academic_term_id = ? AND is_current = 1'
        );
        $invalidateChecks->execute([$groupId, $termId]);

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            $termId,
            (int) $groupTerm['id'],
            $decision === 'Endorsed' ? 'FINAL_DRAFT_ENDORSED' : 'FINAL_DRAFT_REVISION_REQUESTED',
            $fromPhase,
            $toPhase,
            $adviserUserId,
            $adviserName,
            trim($remarks) ?: null,
            'final_draft_review',
            $reviewId,
            ['submission_id' => $submissionId, 'decision' => $decision]
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
        $groupId,
        $decision === 'Endorsed' ? 'final_draft_endorsed' : 'final_draft_revision_requested',
        $decision === 'Endorsed' ? 'Final draft endorsed by adviser' : 'Final draft returned for revision',
        $decision === 'Endorsed'
            ? 'Your adviser accepted Chapters 1-5. The draft is now for CRAD readiness verification.'
            : 'Review the adviser remarks and submit a new final-draft version.',
        'final_draft_review',
        $reviewId,
        BASE_URL . '/modules/student-portal/pages/final-manuscript.php'
    );

    if ($decision === 'Endorsed') {
        foreach (['crad_officer', 'research_coordinator'] as $recipientRole) {
            frCreateNotification($crad, [
                'recipient_user_id' => null,
                'recipient_email' => '',
                'recipient_role' => $recipientRole,
                'batch_key' => 'final_draft_ready_for_crad:' . $reviewId . ':' . $recipientRole,
                'notification_type' => 'final_draft_ready_for_crad',
                'title' => 'Adviser-endorsed final draft needs readiness verification',
                'body' => 'An adviser endorsed all Chapters 1-5. Complete the CRAD compliance checklist.',
                'related_entity_type' => 'final_draft_review',
                'related_entity_id' => $reviewId,
                'action_url' => BASE_URL . '/modules/crad/pages/final-defense-readiness.php',
            ]);
        }
    }

    return $reviewId;
}

/** @return array{id:int,status:string} */
function frSaveReadinessCheck(
    PDO $crad,
    int $groupId,
    int $checkedByUser,
    string $checkedByName,
    bool $ethicsClearanceComplete,
    bool $similarityCheckComplete,
    bool $requiredDocumentsComplete,
    string $remarks
): array {
    if ($groupId <= 0 || $checkedByUser <= 0 || trim($checkedByName) === '') {
        throw new InvalidArgumentException('A valid group and CRAD reviewer are required.');
    }

    frEnsureSchema($crad);
    $crad->beginTransaction();
    try {
        $groupTermStatement = $crad->prepare(
            "SELECT rgt.* FROM research_group_terms rgt "
            . "INNER JOIN academic_terms at ON at.id = rgt.academic_term_id "
            . "WHERE rgt.research_group_id = ? AND rgt.status = 'Active' "
            . "AND at.status = 'Active' AND at.semester = '2nd Semester' "
            . "ORDER BY rgt.id DESC LIMIT 1 FOR UPDATE"
        );
        $groupTermStatement->execute([$groupId]);
        $groupTerm = $groupTermStatement->fetch(PDO::FETCH_ASSOC);
        if (!$groupTerm || (string) $groupTerm['current_phase'] !== 'Final Defense Readiness') {
            throw new RuntimeException('The group must be in Final Defense Readiness before checklist verification.');
        }
        $termId = (int) $groupTerm['academic_term_id'];

        $draftStatement = $crad->prepare(
            'SELECT * FROM final_draft_submissions '
            . 'WHERE research_group_id = ? AND academic_term_id = ? '
            . 'ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE'
        );
        $draftStatement->execute([$groupId, $termId]);
        $draft = $draftStatement->fetch(PDO::FETCH_ASSOC);
        if (!$draft || (string) $draft['status'] !== 'Endorsed') {
            throw new RuntimeException('The latest final draft has not been formally endorsed by the adviser.');
        }

        $reviewStatement = $crad->prepare(
            'SELECT * FROM final_draft_reviews '
            . 'WHERE submission_id = ? AND is_current = 1 ORDER BY id DESC LIMIT 1 FOR UPDATE'
        );
        $reviewStatement->execute([(int) $draft['id']]);
        $review = $reviewStatement->fetch(PDO::FETCH_ASSOC);
        if (!$review || (string) $review['decision'] !== 'Endorsed') {
            throw new RuntimeException('A current adviser endorsement record is required.');
        }

        $chapterValues = [];
        foreach ([1, 2, 3, 4, 5] as $chapter) {
            $chapterValues[$chapter] = !empty($review['chapter_' . $chapter . '_accepted']);
        }
        $chaptersComplete = !in_array(false, $chapterValues, true);
        $adviserEndorsed = $chaptersComplete
            && !empty($review['formatting_accepted'])
            && !empty($review['citations_accepted']);
        $ready = $adviserEndorsed
            && $ethicsClearanceComplete
            && $similarityCheckComplete
            && $requiredDocumentsComplete;
        $status = $ready ? 'Ready' : 'Incomplete';

        $versionStatement = $crad->prepare(
            'SELECT COALESCE(MAX(checklist_version), 0) FROM final_readiness_checks '
            . 'WHERE research_group_id = ? AND academic_term_id = ?'
        );
        $versionStatement->execute([$groupId, $termId]);
        $checklistVersion = (int) $versionStatement->fetchColumn() + 1;

        $supersede = $crad->prepare(
            'UPDATE final_readiness_checks SET is_current = 0, superseded_at = NOW(), '
            . "overall_status = IF(overall_status = 'Ready', 'Revoked', overall_status) "
            . 'WHERE research_group_id = ? AND academic_term_id = ? AND is_current = 1'
        );
        $supersede->execute([$groupId, $termId]);

        $insert = $crad->prepare(
            'INSERT INTO final_readiness_checks '
            . '(research_group_id, academic_term_id, final_draft_submission_id, adviser_review_id, '
            . 'checked_by_user, checked_by_name, chapter_1_complete, chapter_2_complete, chapter_3_complete, '
            . 'chapter_4_complete, chapter_5_complete, chapters_complete, adviser_endorsed, '
            . 'ethics_clearance_complete, similarity_check_complete, required_documents_complete, '
            . 'overall_status, remarks, checklist_version, is_current) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $insert->execute([
            $groupId,
            $termId,
            (int) $draft['id'],
            (int) $review['id'],
            $checkedByUser,
            trim($checkedByName),
            (int) $chapterValues[1],
            (int) $chapterValues[2],
            (int) $chapterValues[3],
            (int) $chapterValues[4],
            (int) $chapterValues[5],
            (int) $chaptersComplete,
            (int) $adviserEndorsed,
            (int) $ethicsClearanceComplete,
            (int) $similarityCheckComplete,
            (int) $requiredDocumentsComplete,
            $status,
            trim($remarks),
            $checklistVersion,
        ]);
        $checkId = (int) $crad->lastInsertId();

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            $termId,
            (int) $groupTerm['id'],
            $ready ? 'FINAL_READINESS_CONFIRMED' : 'FINAL_READINESS_INCOMPLETE',
            'Final Defense Readiness',
            'Final Defense Readiness',
            $checkedByUser,
            $checkedByName,
            trim($remarks) ?: null,
            'final_readiness_check',
            $checkId,
            [
                'checklist_version' => $checklistVersion,
                'ethics_clearance' => $ethicsClearanceComplete,
                'similarity_check' => $similarityCheckComplete,
                'required_documents' => $requiredDocumentsComplete,
            ]
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
        $ready ? 'final_readiness_confirmed' : 'final_readiness_incomplete',
        $ready ? 'Group is ready for Final Defense recommendation' : 'Final Defense readiness is incomplete',
        $ready
            ? 'CRAD completed all readiness checks. You may now submit the formal Final Defense recommendation.'
            : 'CRAD saved an incomplete readiness check. Review the pending requirements before recommendation.',
        'final_readiness_check',
        $checkId,
        BASE_URL . '/modules/faculty/pages/research-progress-monitoring.php'
    );

    return ['id' => $checkId, 'status' => $status];
}

function frRecommendForFinalDefense(
    PDO $crad,
    int $groupId,
    string $groupNumber,
    int $adviserUserId,
    string $adviserEmail,
    string $adviserName,
    string $remarks
): int {
    if (!frIsAssignedAdviser($crad, $groupId, $adviserUserId, $adviserEmail)) {
        throw new RuntimeException('This research group is not assigned to your adviser account.');
    }

    $snapshot = frGetReadinessSnapshot($crad, $groupId);
    if (!$snapshot['ready']) {
        $missing = $snapshot['missing'];
        throw new RuntimeException(
            'Final Defense recommendation is blocked. Missing: '
            . ($missing !== [] ? implode(', ', $missing) : 'a current CRAD readiness approval') . '.'
        );
    }

    $groupTerm = $snapshot['group_term'];
    $draft = $snapshot['final_draft'];
    $check = $snapshot['readiness_check'];
    if (!$groupTerm || !$draft || !$check) {
        throw new RuntimeException('The current readiness records are incomplete.');
    }

    $crad->beginTransaction();
    try {
        $lockTerm = $crad->prepare('SELECT * FROM research_group_terms WHERE id = ? FOR UPDATE');
        $lockTerm->execute([(int) $groupTerm['id']]);
        $lockedGroupTerm = $lockTerm->fetch(PDO::FETCH_ASSOC);
        if (!$lockedGroupTerm || (string) $lockedGroupTerm['current_phase'] !== 'Final Defense Readiness') {
            throw new RuntimeException('The group is no longer in Final Defense Readiness.');
        }

        $lockCheck = $crad->prepare(
            "SELECT * FROM final_readiness_checks WHERE id = ? AND is_current = 1 AND overall_status = 'Ready' FOR UPDATE"
        );
        $lockCheck->execute([(int) $check['id']]);
        if (!$lockCheck->fetch(PDO::FETCH_ASSOC)) {
            throw new RuntimeException('The CRAD readiness approval is no longer current.');
        }

        $save = $crad->prepare(
            "INSERT INTO final_defense_recommendations "
            . "(research_group_id, group_number, academic_term_id, readiness_check_id, final_draft_submission_id, "
            . "adviser_user_id, adviser_name, status, remarks, recommended_at, revoked_by_user, revoked_at, revocation_reason) "
            . "VALUES (?, ?, ?, ?, ?, ?, ?, 'Recommended', ?, NOW(), NULL, NULL, NULL) "
            . "ON DUPLICATE KEY UPDATE group_number = VALUES(group_number), academic_term_id = VALUES(academic_term_id), "
            . "readiness_check_id = VALUES(readiness_check_id), final_draft_submission_id = VALUES(final_draft_submission_id), "
            . "adviser_user_id = VALUES(adviser_user_id), adviser_name = VALUES(adviser_name), status = 'Recommended', "
            . "remarks = VALUES(remarks), recommended_at = NOW(), revoked_by_user = NULL, revoked_at = NULL, revocation_reason = NULL"
        );
        $save->execute([
            $groupId,
            trim($groupNumber),
            (int) $groupTerm['academic_term_id'],
            (int) $check['id'],
            (int) $draft['id'],
            $adviserUserId,
            trim($adviserName),
            trim($remarks),
        ]);

        $recommendationStatement = $crad->prepare(
            'SELECT id FROM final_defense_recommendations WHERE research_group_id = ? LIMIT 1'
        );
        $recommendationStatement->execute([$groupId]);
        $recommendationId = (int) $recommendationStatement->fetchColumn();

        $latestManuscriptStatement = $crad->prepare(
            "SELECT status FROM manuscript_submissions WHERE research_group_id = ? "
            . "AND purpose = 'Final Defense' ORDER BY version_number DESC, id DESC LIMIT 1 FOR UPDATE"
        );
        $latestManuscriptStatement->execute([$groupId]);
        $latestManuscriptStatus = (string) ($latestManuscriptStatement->fetchColumn() ?: '');
        $toPhase = match ($latestManuscriptStatus) {
            'Submitted', 'Under Review' => 'Final Manuscript Evaluation',
            'Approved' => 'Final Defense Scheduling',
            default => 'Final Manuscript Submission',
        };
        $termUpdate = $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?');
        $termUpdate->execute([$toPhase, (int) $groupTerm['id']]);
        $groupUpdate = $crad->prepare(
            'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
        );
        $groupUpdate->execute([$toPhase, $groupId]);

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            (int) $groupTerm['academic_term_id'],
            (int) $groupTerm['id'],
            'FINAL_DEFENSE_RECOMMENDED',
            'Final Defense Readiness',
            $toPhase,
            $adviserUserId,
            $adviserName,
            trim($remarks) ?: null,
            'final_defense_recommendation',
            $recommendationId,
            ['readiness_check_id' => (int) $check['id'], 'final_draft_submission_id' => (int) $draft['id']]
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
        $groupId,
        'final_defense_recommended',
        'Recommended for Final Defense',
        'Your adviser submitted the formal Final Defense recommendation after completion of all readiness checks.',
        'final_defense_recommendation',
        $recommendationId,
        BASE_URL . '/modules/student-portal/pages/my-research.php'
    );

    return $recommendationId;
}

function frRevokeFinalDefenseRecommendation(
    PDO $crad,
    int $groupId,
    int $actorUserId,
    string $actorName,
    string $reason
): void {
    if (trim($reason) === '') {
        throw new InvalidArgumentException('A revocation reason is required.');
    }

    frEnsureSchema($crad);
    $scheduledStatement = $crad->prepare(
        "SELECT COUNT(*) FROM research_defense_schedules "
        . "WHERE research_group_id = ? AND LOWER(TRIM(defense_type)) = 'final defense' "
        . "AND defense_datetime IS NOT NULL AND status NOT IN ('Cancelled','Rejected')"
    );
    $scheduledStatement->execute([$groupId]);
    if ((int) $scheduledStatement->fetchColumn() > 0) {
        throw new RuntimeException('The recommendation cannot be revoked after an active Final Defense schedule exists.');
    }

    $crad->beginTransaction();
    try {
        $recommendationStatement = $crad->prepare(
            "SELECT * FROM final_defense_recommendations "
            . "WHERE research_group_id = ? AND status = 'Recommended' LIMIT 1 FOR UPDATE"
        );
        $recommendationStatement->execute([$groupId]);
        $recommendation = $recommendationStatement->fetch(PDO::FETCH_ASSOC);
        if (!$recommendation) {
            throw new RuntimeException('There is no active Final Defense recommendation to revoke.');
        }

        $groupTermStatement = $crad->prepare(
            'SELECT * FROM research_group_terms WHERE research_group_id = ? AND academic_term_id = ? LIMIT 1 FOR UPDATE'
        );
        $groupTermStatement->execute([$groupId, (int) $recommendation['academic_term_id']]);
        $groupTerm = $groupTermStatement->fetch(PDO::FETCH_ASSOC);
        if (!$groupTerm) {
            throw new RuntimeException('The active second-semester workflow record was not found.');
        }
        $fromPhase = (string) $groupTerm['current_phase'];
        if (!in_array($fromPhase, [
            'Final Manuscript Submission',
            'Final Manuscript Evaluation',
            'Final Defense Scheduling',
        ], true)) {
            throw new RuntimeException('Recommendation revocation is not allowed at the current workflow phase.');
        }

        $update = $crad->prepare(
            "UPDATE final_defense_recommendations SET status = 'Not Ready', revoked_by_user = ?, "
            . 'revoked_at = NOW(), revocation_reason = ? WHERE id = ?'
        );
        $update->execute([$actorUserId, trim($reason), (int) $recommendation['id']]);

        $toPhase = 'Final Defense Readiness';
        $termUpdate = $crad->prepare('UPDATE research_group_terms SET current_phase = ? WHERE id = ?');
        $termUpdate->execute([$toPhase, (int) $groupTerm['id']]);
        $groupUpdate = $crad->prepare(
            'UPDATE research_groups SET current_phase = ?, current_phase_started_at = NOW() WHERE id = ?'
        );
        $groupUpdate->execute([$toPhase, $groupId]);

        cradLogWorkflowEvent(
            $crad,
            $groupId,
            (int) $groupTerm['academic_term_id'],
            (int) $groupTerm['id'],
            'FINAL_DEFENSE_RECOMMENDATION_REVOKED',
            $fromPhase,
            $toPhase,
            $actorUserId,
            $actorName,
            trim($reason),
            'final_defense_recommendation',
            (int) $recommendation['id']
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
        $groupId,
        'final_defense_recommendation_revoked',
        'Final Defense recommendation revoked',
        'The recommendation was revoked. Reason: ' . trim($reason),
        'final_defense_recommendation',
        (int) $recommendation['id'],
        BASE_URL . '/modules/student-portal/pages/my-research.php'
    );
}
