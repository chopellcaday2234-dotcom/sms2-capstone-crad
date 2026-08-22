<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/second_semester_schema.php';

$databaseName = 'crad_phase1_test_' . getmypid();
if (!preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
    throw new RuntimeException('Unsafe temporary database name.');
}
$quotedDatabase = '`' . $databaseName . '`';

try {
    $server = new PDO(
        'mysql:host=' . CRAD_DB_HOST . ';charset=' . CRAD_DB_CHARSET,
        CRAD_DB_USER,
        CRAD_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $exception) {
    echo '[SKIP] Schema integration test requires valid CRAD MySQL credentials.' . PHP_EOL;
    exit(0);
}

$assertions = 0;
try {
    $server->exec('CREATE DATABASE ' . $quotedDatabase . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $server->exec('USE ' . $quotedDatabase);

    $legacyTables = [
        'research_groups',
        'final_defense_recommendations',
        'manuscript_submissions',
        'manuscript_evaluations',
        'final_defense_evaluations',
        'final_manuscript_approvals',
        'publications',
        'research_revision_cycles',
        'research_defense_schedules',
    ];
    foreach ($legacyTables as $legacyTable) {
        $server->exec(
            'CREATE TABLE `' . $legacyTable . '` ('
            . '`id` INT UNSIGNED NOT NULL, PRIMARY KEY (`id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    $firstRun = cradEnsureSecondSemesterSchema($server);
    $secondRun = cradEnsureSecondSemesterSchema($server);

    $checks = [
        [cradSecondSemesterTableExists($server, 'academic_terms'), 'academic_terms table exists'],
        [cradSecondSemesterTableExists($server, 'research_group_terms'), 'research_group_terms table exists'],
        [cradSecondSemesterTableExists($server, 'research_workflow_events'), 'workflow event table exists'],
        [cradSecondSemesterTableExists($server, 'final_draft_reviews'), 'final-draft review table exists'],
        [cradSecondSemesterTableExists($server, 'defense_attempts'), 'defense attempt table exists'],
        [cradSecondSemesterTableExists($server, 'final_manuscript_approval_history'), 'append-only final approval history exists'],
        [cradSecondSemesterColumnExists($server, 'research_groups', 'current_phase'), 'research_groups phase column exists'],
        [cradSecondSemesterColumnExists($server, 'manuscript_submissions', 'academic_term_id'), 'manuscript term column exists'],
        [cradSecondSemesterColumnExists($server, 'final_manuscript_approvals', 'manuscript_submission_id'), 'approval version pin exists'],
        [cradSecondSemesterColumnExists($server, 'final_manuscript_approvals', 'revision_submission_id'), 'approval revision evidence pin exists'],
        [cradSecondSemesterColumnExists($server, 'final_manuscript_approval_history', 'decision_sequence'), 'approval decision sequence exists'],
        [cradSecondSemesterTableExists($server, 'crad_data_integrity_archive'), 'recoverable integrity archive exists'],
        [cradSecondSemesterColumnExists($server, 'final_readiness_checks', 'chapter_5_complete'), 'Chapter 5 readiness evidence exists'],
        [cradSecondSemesterColumnExists($server, 'final_readiness_checks', 'is_current'), 'current readiness version marker exists'],
        [$firstRun['version'] === CRAD_SECOND_SEMESTER_SCHEMA_VERSION, 'first migration version matches'],
        [$secondRun['version'] === CRAD_SECOND_SEMESTER_SCHEMA_VERSION, 'second migration run is safe'],
    ];
    foreach ($checks as [$passed, $description]) {
        $assertions++;
        if (!$passed) {
            throw new RuntimeException('Integration assertion failed: ' . $description);
        }
    }

    $extraStatement = $server->query(
        "SELECT EXTRA FROM information_schema.COLUMNS "
        . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'manuscript_submissions' AND COLUMN_NAME = 'id'"
    );
    $assertions++;
    if (!str_contains(strtolower((string) $extraStatement->fetchColumn()), 'auto_increment')) {
        throw new RuntimeException('Integration assertion failed: legacy AUTO_INCREMENT was not repaired.');
    }

    $migrationCount = (int) $server->query('SELECT COUNT(*) FROM crad_schema_migrations')->fetchColumn();
    $assertions++;
    if ($migrationCount !== 1) {
        throw new RuntimeException('Integration assertion failed: migration should be recorded exactly once.');
    }

    echo '[OK] ' . $assertions . ' schema integration assertions passed.' . PHP_EOL;
} finally {
    $server->exec('USE information_schema');
    $server->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
}
