<?php
declare(strict_types=1);

ini_set('session.save_path', sys_get_temp_dir());

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/final-phase-helpers.php';

$databaseName = 'crad_phase3_test_' . getmypid();
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
    echo '[SKIP] Phase 3 database integration test requires valid CRAD MySQL credentials.' . PHP_EOL;
    exit(0);
}

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException('Integration assertion failed: ' . $message);
    }
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
            leader_id VARCHAR(80) NOT NULL DEFAULT '',
            leader_email VARCHAR(190) NOT NULL DEFAULT '',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $server->exec(
        "CREATE TABLE research_defense_schedules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            defense_type VARCHAR(60) NOT NULL DEFAULT '',
            defense_datetime DATETIME DEFAULT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'Pending',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $server->exec(
        "CREATE TABLE research_adviser_assignments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED DEFAULT NULL,
            adviser_user_id INT UNSIGNED DEFAULT NULL,
            adviser_email VARCHAR(190) NOT NULL DEFAULT '',
            assignment_status VARCHAR(40) NOT NULL DEFAULT 'Assigned',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    finalPhaseEnsureSchema($server);

    $server->exec(
        "INSERT INTO academic_terms (academic_year, semester, term_code, status) "
        . "VALUES ('2026-2027', '2nd Semester', '2026-2027-2', 'Active')"
    );
    $termId = (int) $server->lastInsertId();
    $server->exec(
        "INSERT INTO research_groups (group_number, group_name, research_title, leader_id, leader_email, current_phase) "
        . "VALUES ('G-001', 'Phase 3 Test Group', 'Phase 3 Manuscript Flow', '', "
        . "'phase3@example.test', 'Final Manuscript Submission')"
    );
    $groupId = (int) $server->lastInsertId();
    $groupTermStatement = $server->prepare(
        "INSERT INTO research_group_terms "
        . "(research_group_id, academic_term_id, enrollment_type, starting_phase, current_phase, status) "
        . "VALUES (?, ?, 'Continuing', 'Final Documentation', 'Final Manuscript Submission', 'Active')"
    );
    $groupTermStatement->execute([$groupId, $termId]);

    $recommendationStatement = $server->prepare(
        "INSERT INTO final_defense_recommendations "
        . "(research_group_id, group_number, academic_term_id, adviser_user_id, adviser_name, status, recommended_at) "
        . "VALUES (?, 'G-001', ?, 11, 'Test Adviser', 'Recommended', NOW())"
    );
    $recommendationStatement->execute([$groupId, $termId]);

    $fileV1 = [
        'original_name' => 'official-v1.pdf',
        'stored_subdir' => 'phase3-test',
        'stored_name' => 'official-v1.pdf',
        'file_size' => 1024,
        'file_mime' => 'application/pdf',
        'file_checksum' => hash('sha256', 'official-v1'),
    ];
    $submissionV1 = fpSubmitFinalManuscript(
        $server,
        $groupId,
        21,
        'Test Student',
        'phase3@example.test',
        $fileV1,
        'Initial official submission'
    );
    $assert($submissionV1 > 0, 'first official manuscript submission is created');
    $assert(
        (string) $server->query('SELECT current_phase FROM research_group_terms LIMIT 1')->fetchColumn()
            === 'Final Manuscript Evaluation',
        'official submission advances to manuscript evaluation'
    );

    $scores = array_fill_keys([
        'content', 'methodology', 'results', 'conclusions',
        'recommendations', 'references', 'formatting', 'compliance',
    ], 85);
    $revision = fpSaveManuscriptEvaluation(
        $server,
        $submissionV1,
        31,
        'CRAD Evaluator',
        $scores,
        'revision',
        'Address the evaluation remarks.'
    );
    $assert($revision['result'] === 'FOR REVISION', 'revision evaluation is recorded');
    $assert(
        (string) $server->query('SELECT current_phase FROM research_group_terms LIMIT 1')->fetchColumn()
            === 'Final Manuscript Submission',
        'revision returns the workflow to manuscript submission'
    );

    $fileV2 = $fileV1;
    $fileV2['original_name'] = 'official-v2.pdf';
    $fileV2['stored_name'] = 'official-v2.pdf';
    $fileV2['file_checksum'] = hash('sha256', 'official-v2');
    $submissionV2 = fpSubmitFinalManuscript(
        $server,
        $groupId,
        21,
        'Test Student',
        'phase3@example.test',
        $fileV2,
        'Revised official submission'
    );
    $assert($submissionV2 > $submissionV1, 'a new immutable manuscript version is created');
    $latest = fpGetLatestManuscriptSubmission($server, $groupId, 'Final Defense');
    $assert((int) ($latest['version_number'] ?? 0) === 2, 'official manuscript version increments');
    $assert((int) ($latest['supersedes_submission_id'] ?? 0) === $submissionV1, 'new version links to the prior version');

    $approved = fpSaveManuscriptEvaluation(
        $server,
        $submissionV2,
        31,
        'CRAD Evaluator',
        $scores,
        'approve',
        'Approved for Final Defense.'
    );
    $assert($approved['result'] === 'APPROVED', 'approval evaluation is recorded');
    $assert(fpIsManuscriptApproved($server, $groupId), 'latest official manuscript passes the approval gate');
    $assert(
        (string) $server->query('SELECT current_phase FROM research_group_terms LIMIT 1')->fetchColumn()
            === 'Final Defense Scheduling',
        'only approval advances the workflow to scheduling'
    );
    $assert(count(fpGetManuscriptSubmissionHistory($server, $groupId)) === 2, 'both immutable versions remain in history');
    $assert(rpIsFinalDefenseRecommended($server, $groupId), 'all recommendation readers use the dedicated record');
    $assert(
        (int) $server->query('SELECT COUNT(*) FROM research_workflow_events')->fetchColumn() === 4,
        'submission and evaluation transitions are audited'
    );

    echo '[OK] ' . $assertions . ' Phase 3 database integration assertions passed.' . PHP_EOL;
} finally {
    $server->exec('USE information_schema');
    $server->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
}
