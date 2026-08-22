<?php
declare(strict_types=1);

ini_set('session.save_path', sys_get_temp_dir());

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/final-phase-helpers.php';

$databaseName = 'crad_phase4_test_' . getmypid();
if (!preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
    throw new RuntimeException('Unsafe temporary database name.');
}
$quotedDatabase = '`' . $databaseName . '`';

try {
    $server = new PDO(
        'mysql:host=' . CRAD_DB_HOST . ';charset=' . CRAD_DB_CHARSET,
        CRAD_DB_USER,
        CRAD_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $exception) {
    echo '[SKIP] Phase 4 database integration test requires valid CRAD MySQL credentials.' . PHP_EOL;
    exit(0);
}

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException('Phase 4 integration assertion failed: ' . $message);
    }
};

$testUploadSubdir = 'phase4_integration_' . getmypid();
$testUploadDirectory = rtrim(smsUploadRoot(), '/\\') . DIRECTORY_SEPARATOR . $testUploadSubdir;
$createdUploadFiles = [];

/** @return array{submission_id:int,checksum:string,stored_name:string} */
$createApprovedManuscript = static function (
    PDO $server,
    int $groupId,
    int $termId,
    string $label
) use ($testUploadSubdir, $testUploadDirectory, &$createdUploadFiles): array {
    if (!is_dir($testUploadDirectory) && !mkdir($testUploadDirectory, 0775, true) && !is_dir($testUploadDirectory)) {
        throw new RuntimeException('Could not create the Phase 4 secure-storage test directory.');
    }
    $storedName = bin2hex(random_bytes(8)) . '.docx';
    $absolutePath = $testUploadDirectory . DIRECTORY_SEPARATOR . $storedName;
    $content = 'Phase 4 verified manuscript evidence: ' . $label . '|' . bin2hex(random_bytes(12));
    if (file_put_contents($absolutePath, $content) === false) {
        throw new RuntimeException('Could not create verified manuscript evidence.');
    }
    $createdUploadFiles[] = $absolutePath;
    $checksum = hash_file('sha256', $absolutePath);
    if ($checksum === false) {
        throw new RuntimeException('Could not hash verified manuscript evidence.');
    }

    $versionStatement = $server->prepare(
        "SELECT COALESCE(MAX(version_number), 0) + 1 FROM manuscript_submissions WHERE research_group_id = ?"
    );
    $versionStatement->execute([$groupId]);
    $version = (int) $versionStatement->fetchColumn();
    $insert = $server->prepare(
        "INSERT INTO manuscript_submissions
            (research_group_id, academic_term_id, version_number, purpose, status,
             submitted_by_user, submitted_by_name, submitted_by_email, submission_notes,
             original_name, stored_subdir, stored_name, file_size, file_mime,
             file_checksum, submission_token, reviewed_at)
         VALUES (?, ?, ?, 'Final Defense', 'Approved', 9001, 'Integration Student',
                 'integration.student@example.test', 'Verified test submission', ?, ?, ?, ?,
                 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', ?, ?, NOW())"
    );
    $insert->execute([
        $groupId,
        $termId,
        $version,
        $label . '.docx',
        $testUploadSubdir,
        $storedName,
        filesize($absolutePath),
        $checksum,
        bin2hex(random_bytes(32)),
    ]);
    $submissionId = (int) $server->lastInsertId();
    $server->prepare(
        "INSERT INTO manuscript_evaluations
            (submission_id, research_group_id, evaluator_user_id, evaluator_name,
             content_score, methodology_score, results_score, conclusions_score,
             recommendations_score, references_score, formatting_score, compliance_score,
             remarks, result, overall_score)
         VALUES (?, ?, 3, 'CRAD Integration Reviewer', 95, 95, 95, 95, 95, 95, 95, 95,
                 'Approved for integration verification.', 'APPROVED', 95)"
    )->execute([$submissionId, $groupId]);

    return ['submission_id' => $submissionId, 'checksum' => $checksum, 'stored_name' => $storedName];
};

/** @return array{group_id:int,schedule_id:int} */
$createDefense = static function (
    PDO $server,
    int $termId,
    string $groupNumber,
    string $title,
    array $panelIds
): array {
    $group = $server->prepare(
        "INSERT INTO research_groups
            (group_number, group_name, research_title, current_phase)
         VALUES (?, ?, ?, 'Final Defense Scheduling')"
    );
    $group->execute([$groupNumber, $groupNumber . ' Group', $title]);
    $groupId = (int) $server->lastInsertId();
    $groupTerm = $server->prepare(
        "INSERT INTO research_group_terms
            (research_group_id, academic_term_id, enrollment_type, starting_phase, current_phase, status)
         VALUES (?, ?, 'Continuing', 'Final Documentation', 'Final Defense Scheduling', 'Active')"
    );
    $groupTerm->execute([$groupId, $termId]);

    $schedule = $server->prepare(
        "INSERT INTO research_defense_schedules
            (research_group_id, group_number, research_group, research_title, adviser_name,
             venue, defense_datetime, defense_end_datetime, defense_type, status)
         VALUES (?, ?, ?, ?, 'Test Adviser', 'CRAD Room', DATE_SUB(NOW(), INTERVAL 1 HOUR),
                 NOW(), 'Final Defense', 'Scheduled')"
    );
    $schedule->execute([$groupId, $groupNumber, $groupNumber . ' Group', $title]);
    $scheduleId = (int) $server->lastInsertId();

    $assignment = $server->prepare(
        "INSERT INTO research_panel_assignments
            (research_group_id, defense_schedule_id, panel_user_id, panel_name,
             assignment_status, defense_phase)
         VALUES (?, ?, ?, ?, 'Assigned', 'Final Defense')"
    );
    foreach ($panelIds as $panelId) {
        $assignment->execute([$groupId, $scheduleId, $panelId, 'Panel ' . $panelId]);
    }
    return ['group_id' => $groupId, 'schedule_id' => $scheduleId];
};

$submitEvaluation = static function (
    PDO $server,
    int $scheduleId,
    int $panelId,
    string $result,
    string $remarks = ''
): array {
    $_SESSION['user_id'] = $panelId;
    $_SESSION['user_name'] = 'Panel ' . $panelId;
    $_SESSION['user_role_key'] = 'panel';
    return finalDefenseSubmitEvaluation($server, $scheduleId, [
        'content_score' => 90,
        'methodology_score' => 88,
        'references_score' => 86,
        'format_score' => 84,
        'result' => $result,
        'remarks' => $remarks,
    ]);
};

try {
    $server->exec('CREATE DATABASE ' . $quotedDatabase . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $server->exec('USE ' . $quotedDatabase);
    $server->exec(
        "CREATE TABLE research_groups (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            group_number VARCHAR(40) NOT NULL DEFAULT '',
            group_name VARCHAR(160) NOT NULL DEFAULT '',
            research_title VARCHAR(500) NOT NULL DEFAULT '',
            academic_year VARCHAR(20) NOT NULL DEFAULT '',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $server->exec(
        "CREATE TABLE research_defense_schedules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED DEFAULT NULL,
            group_number VARCHAR(40) NOT NULL DEFAULT '',
            research_group VARCHAR(160) NOT NULL DEFAULT '',
            research_title VARCHAR(500) NOT NULL DEFAULT '',
            adviser_name VARCHAR(160) NOT NULL DEFAULT '',
            venue VARCHAR(160) NOT NULL DEFAULT '',
            defense_datetime DATETIME DEFAULT NULL,
            defense_end_datetime DATETIME DEFAULT NULL,
            defense_type VARCHAR(60) NOT NULL DEFAULT 'Final Defense',
            status VARCHAR(40) NOT NULL DEFAULT 'Scheduled',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $server->exec(
        "CREATE TABLE research_panel_assignments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            defense_schedule_id INT UNSIGNED DEFAULT NULL,
            panel_user_id INT UNSIGNED NOT NULL,
            panel_name VARCHAR(150) NOT NULL DEFAULT '',
            assignment_status VARCHAR(40) NOT NULL DEFAULT 'Assigned',
            defense_phase VARCHAR(60) NOT NULL DEFAULT 'Final Defense',
            PRIMARY KEY (id),
            UNIQUE KEY uniq_panel_phase (research_group_id, panel_user_id, defense_phase)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $server->exec(
        "CREATE TABLE research_adviser_assignments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED DEFAULT NULL,
            group_number VARCHAR(40) NOT NULL DEFAULT '',
            adviser_user_id INT UNSIGNED DEFAULT NULL,
            adviser_email VARCHAR(190) NOT NULL DEFAULT '',
            assignment_status VARCHAR(40) NOT NULL DEFAULT 'Assigned',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    finalPhaseEnsureSchema($server);
    $server->exec(
        "INSERT INTO academic_terms (academic_year, semester, term_code, status)
         VALUES ('2026-2027', '2nd Semester', '2026-2027-2', 'Active')"
    );
    $termId = (int) $server->lastInsertId();

    $revisionDefense = $createDefense(
        $server,
        $termId,
        'G-MIXED',
        'Mixed Final Defense Result',
        [101, 102, 103]
    );
    $first = $submitEvaluation($server, $revisionDefense['schedule_id'], 101, 'APPROVED');
    $assert(!empty($first['ok']), 'first panel evaluation is accepted');
    $assert(($first['aggregation']['official_result'] ?? '') === 'UNRESOLVED', 'partial panel results remain unresolved');
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 'Final Defense',
        'the first panel evaluation opens the Final Defense phase'
    );

    $second = $submitEvaluation(
        $server,
        $revisionDefense['schedule_id'],
        102,
        'APPROVED WITH REVISION',
        'Correct the discussion and presentation findings.'
    );
    $assert(!empty($second['ok']), 'revision panel evaluation is accepted with remarks');
    $assert(($second['aggregation']['official_result'] ?? '') === 'UNRESOLVED', 'two of three results stay unresolved');

    $third = $submitEvaluation($server, $revisionDefense['schedule_id'], 103, 'APPROVED');
    $assert(!empty($third['ok']), 'third panel evaluation is accepted');
    $assert(($third['aggregation']['official_result'] ?? '') === 'PASSED WITH REVISIONS', 'mixed approval/revision aggregates correctly');
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 'Post-Defense Revision',
        'passed-with-revisions routes to post-defense revision'
    );
    $assert(
        (int) $server->query(
            'SELECT COUNT(*) FROM defense_revision_items WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 1,
        'the revision panel remark becomes a dedicated revision item'
    );
    $assert(fpGroupNeedsFinalRevision($server, $revisionDefense['group_id']), 'downstream revision gate reads the official mixed result');
    $assert(!fpIsEligibleForFinalApproval($server, $revisionDefense['group_id']), 'revision group is blocked before compliance');

    $revisionManuscript = $createApprovedManuscript(
        $server,
        $revisionDefense['group_id'],
        $termId,
        'Mixed Final Defense Official Manuscript'
    );
    $revisionEvidenceName = bin2hex(random_bytes(8)) . '.docx';
    $revisionEvidencePath = $testUploadDirectory . DIRECTORY_SEPARATOR . $revisionEvidenceName;
    if (file_put_contents($revisionEvidencePath, 'Verified defense revision evidence|' . bin2hex(random_bytes(12))) === false) {
        throw new RuntimeException('Could not create Final Defense revision evidence.');
    }
    $createdUploadFiles[] = $revisionEvidencePath;
    $revisionChecksum = hash_file('sha256', $revisionEvidencePath);
    if ($revisionChecksum === false) {
        throw new RuntimeException('Could not hash Final Defense revision evidence.');
    }
    $revisionSubmissionId = fpSubmitDefenseRevision(
        $server,
        $revisionDefense['group_id'],
        9001,
        'Integration Student',
        [
            'original_name' => 'final-defense-revision.docx',
            'stored_subdir' => $testUploadSubdir,
            'stored_name' => $revisionEvidenceName,
            'file_size' => filesize($revisionEvidencePath),
            'file_mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_checksum' => $revisionChecksum,
        ],
        'Addresses every panel revision item.'
    );
    $assert($revisionSubmissionId > 0, 'dedicated Final Defense revision evidence is stored');
    $assert(
        (string) $server->query(
            'SELECT revision_status FROM research_revision_cycles WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 'Under Review',
        'revision evidence opens a traceable review state'
    );
    $assert(
        fpReviewDefenseRevisionSubmission(
            $server,
            $revisionDefense['group_id'],
            'Compliant',
            54,
            'Integration Adviser',
            'All required panel revisions are verified in the submitted evidence.'
        ),
        'adviser can mark the exact revision submission compliant'
    );
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 'Final Manuscript Approval',
        'verified revision evidence advances to final manuscript approval'
    );
    $revisionApprovalId = fpApproveFinalManuscript(
        $server,
        $revisionDefense['group_id'],
        3,
        'CRAD Integration Reviewer',
        'Final manuscript and revision evidence verified.'
    );
    $revisionApproval = fpGetFinalManuscriptApproval($server, $revisionDefense['group_id']);
    $assert($revisionApprovalId > 0, 'revision-path final manuscript approval is created');
    $assert(
        (int) ($revisionApproval['manuscript_submission_id'] ?? 0) === (int) $revisionManuscript['submission_id']
        && (int) ($revisionApproval['revision_submission_id'] ?? 0) === $revisionSubmissionId
        && hash_equals((string) ($revisionApproval['file_checksum'] ?? ''), $revisionChecksum)
        && trim((string) ($revisionApproval['approval_token'] ?? '')) !== '',
        'revision-path approval is pinned to the exact manuscript, revision, checksum, and token'
    );
    $assert(fpIsFinalManuscriptApproved($server, $revisionDefense['group_id']), 'revision-path final approval validates after persistence');
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $revisionDefense['group_id']
        )->fetchColumn() === 'Publication and Repository',
        'revision-path approval advances to publication and repository'
    );

    $failedDefense = $createDefense(
        $server,
        $termId,
        'G-FAILED',
        'Failed Final Defense Result',
        [201, 202, 203]
    );
    $submitEvaluation($server, $failedDefense['schedule_id'], 201, 'APPROVED');
    $missingFailureRemarks = $submitEvaluation($server, $failedDefense['schedule_id'], 202, 'FAILED');
    $assert(empty($missingFailureRemarks['ok']), 'failed results are rejected when panel remarks are missing');
    $submitEvaluation(
        $server,
        $failedDefense['schedule_id'],
        202,
        'FAILED',
        'Major methodology failure requires re-defense.'
    );
    $failed = $submitEvaluation(
        $server,
        $failedDefense['schedule_id'],
        203,
        'APPROVED WITH REVISION',
        'Revise the implementation evidence.'
    );
    $assert(($failed['aggregation']['official_result'] ?? '') === 'FAILED', 'any failed panel result has precedence');
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $failedDefense['group_id']
        )->fetchColumn() === 'Final Defense Scheduling',
        'failed result returns only to the scheduling handoff'
    );
    $assert(
        (int) $server->query(
            "SELECT COUNT(*) FROM defense_attempts WHERE research_group_id = "
            . (int) $failedDefense['group_id']
            . " AND defense_type = 'Re-Defense' AND attempt_number = 2 AND status = 'Ready for Scheduling'"
        )->fetchColumn() === 1,
        'failed result creates one immutable re-defense handoff attempt'
    );
    $assert(!fpIsEligibleForFinalApproval($server, $failedDefense['group_id']), 'failed group is blocked from final approval');

    $passedDefense = $createDefense(
        $server,
        $termId,
        'G-PASSED',
        'Passed Final Defense Result',
        [301, 302, 303]
    );
    $submitEvaluation($server, $passedDefense['schedule_id'], 301, 'APPROVED');
    $submitEvaluation($server, $passedDefense['schedule_id'], 302, 'APPROVED');
    $passed = $submitEvaluation($server, $passedDefense['schedule_id'], 303, 'APPROVED');
    $assert(($passed['aggregation']['official_result'] ?? '') === 'PASSED', 'unanimous approval aggregates to passed');
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $passedDefense['group_id']
        )->fetchColumn() === 'Final Manuscript Approval',
        'passed result routes to final manuscript approval'
    );
    $assert(fpIsEligibleForFinalApproval($server, $passedDefense['group_id']), 'official passed result enables final approval eligibility');

    $passedManuscript = $createApprovedManuscript(
        $server,
        $passedDefense['group_id'],
        $termId,
        'Passed Final Defense Official Manuscript'
    );
    $passedApprovalId = fpApproveFinalManuscript(
        $server,
        $passedDefense['group_id'],
        3,
        'CRAD Integration Reviewer',
        'Final manuscript and defense result verified.'
    );
    $passedApproval = fpGetFinalManuscriptApproval($server, $passedDefense['group_id']);
    $assert($passedApprovalId > 0, 'passed-path final manuscript approval is created');
    $assert(
        (int) ($passedApproval['manuscript_submission_id'] ?? 0) === (int) $passedManuscript['submission_id']
        && empty($passedApproval['revision_submission_id'])
        && hash_equals((string) ($passedApproval['file_checksum'] ?? ''), (string) $passedManuscript['checksum'])
        && trim((string) ($passedApproval['approval_token'] ?? '')) !== '',
        'passed-path approval is pinned to the exact manuscript, checksum, and token'
    );
    $assert(fpIsFinalManuscriptApproved($server, $passedDefense['group_id']), 'passed-path final approval validates after persistence');
    $assert(
        fpApproveFinalManuscript(
            $server,
            $passedDefense['group_id'],
            3,
            'CRAD Integration Reviewer',
            'Repeated verification must remain idempotent.'
        ) === $passedApprovalId,
        'repeated approval of the same immutable evidence is idempotent'
    );
    $initialHistory = fpGetFinalManuscriptApprovalHistory($server, $passedDefense['group_id']);
    $initialApprovalToken = (string) ($passedApproval['approval_token'] ?? '');
    $assert(
        count($initialHistory) === 1
        && (string) ($initialHistory[0]['decision_status'] ?? '') === 'Approved'
        && hash_equals((string) ($initialHistory[0]['approval_token'] ?? ''), $initialApprovalToken),
        'the original approval is stored once in append-only history'
    );

    $server->prepare(
        "UPDATE final_manuscript_approvals
         SET status = 'Returned', remarks = 'Legacy return requiring corrections',
             approved_at = NULL, updated_at = NOW()
         WHERE research_group_id = ?"
    )->execute([$passedDefense['group_id']]);
    $reapprovedId = fpApproveFinalManuscript(
        $server,
        $passedDefense['group_id'],
        3,
        'CRAD Integration Reviewer',
        'Corrections verified after return.'
    );
    $approvalHistory = fpGetFinalManuscriptApprovalHistory($server, $passedDefense['group_id']);
    $assert($reapprovedId === $passedApprovalId, 'reapproval keeps the current-record identity for compatibility');
    $assert(
        array_column($approvalHistory, 'decision_status') === ['Approved', 'Returned', 'Approved'],
        'return and reapproval append decisions without erasing the original approval'
    );
    $assert(
        hash_equals((string) ($approvalHistory[0]['approval_token'] ?? ''), $initialApprovalToken)
        && (string) ($approvalHistory[1]['remarks'] ?? '') === 'Legacy return requiring corrections',
        'historical approval evidence and return remarks remain preserved'
    );
    $assert(
        (string) $server->query(
            'SELECT current_phase FROM research_group_terms WHERE research_group_id = '
            . (int) $passedDefense['group_id']
        )->fetchColumn() === 'Publication and Repository',
        'passed-path approval advances to publication and repository'
    );

    $assert(
        (int) $server->query("SELECT COUNT(*) FROM defense_official_results WHERE official_result <> 'UNRESOLVED'")->fetchColumn() === 3,
        'one official finalized result is stored per completed defense attempt'
    );
    $assert(
        (int) $server->query("SELECT COUNT(*) FROM research_workflow_events WHERE event_type = 'FINAL_DEFENSE_RESULT_FINALIZED'")->fetchColumn() === 3,
        'each official result has one workflow audit event'
    );

    echo '[OK] ' . $assertions . ' Phase 4 Final Defense integration assertions passed.' . PHP_EOL;
} finally {
    $server->exec('USE information_schema');
    $server->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
    foreach ($createdUploadFiles as $createdUploadFile) {
        if (is_file($createdUploadFile)) {
            unlink($createdUploadFile);
        }
    }
    if (is_dir($testUploadDirectory)) {
        rmdir($testUploadDirectory);
    }
}
