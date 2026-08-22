<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/second-semester-workflow.php';
require_once __DIR__ . '/../includes/research-progress-helpers.php';

$databaseName = 'crad_transition_gate_test_' . getmypid();
$quotedDatabase = '`' . $databaseName . '`';

try {
    $server = new PDO(
        'mysql:host=' . CRAD_DB_HOST . ';charset=' . CRAD_DB_CHARSET,
        CRAD_DB_USER,
        CRAD_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $exception) {
    echo '[SKIP] First-semester transition gate integration test requires valid CRAD MySQL credentials.' . PHP_EOL;
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
    $server->exec('CREATE TABLE research_groups (id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)) ENGINE=InnoDB');
    $server->exec(
        "CREATE TABLE research_plans (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            current_stage VARCHAR(100) NOT NULL DEFAULT 'Planning',
            overall_progress DECIMAL(5,2) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB"
    );
    $server->exec(
        "CREATE TABLE research_milestones (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_plan_id INT UNSIGNED NOT NULL,
            milestone_name VARCHAR(200) NOT NULL,
            milestone_order TINYINT UNSIGNED NOT NULL,
            progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
            status VARCHAR(40) NOT NULL DEFAULT 'Not Started',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB"
    );
    $server->exec(
        "CREATE TABLE research_defense_schedules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            defense_type VARCHAR(60) NOT NULL DEFAULT 'Pre-Oral Defense',
            defense_datetime DATETIME DEFAULT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'Finalized',
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB"
    );
    $server->exec(
        "CREATE TABLE research_panel_assignments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            research_group_id INT UNSIGNED NOT NULL,
            panel_user_id INT UNSIGNED NOT NULL,
            panel_name VARCHAR(150) NOT NULL DEFAULT '',
            defense_phase VARCHAR(60) NOT NULL,
            assignment_status VARCHAR(40) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB"
    );
    $server->exec(
        "CREATE TABLE preoral_defense_evaluations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            defense_schedule_id INT UNSIGNED NOT NULL,
            panel_user_id INT UNSIGNED NOT NULL,
            result VARCHAR(60) NOT NULL,
            status VARCHAR(40) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB"
    );
    cradEnsureSecondSemesterSchema($server);

    $server->exec('INSERT INTO research_groups (id) VALUES (1), (2)');
    $server->exec('INSERT INTO research_plans (id, research_group_id) VALUES (1, 1), (2, 2)');
    $names = ['Chapter 1', 'Chapter 2', 'Chapter 3', 'Chapter 4', 'Chapter 5', 'System Development', 'Testing', 'Documentation'];
    $milestoneInsert = $server->prepare(
        "INSERT INTO research_milestones
            (research_plan_id, milestone_name, milestone_order, progress_percentage, status)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($names as $index => $name) {
        $milestoneInsert->execute([1, $name, $index + 1, 100, 'Approved']);
        $milestoneInsert->execute([2, $name, $index + 1, $index === 7 ? 90 : 100, $index === 7 ? 'In Progress' : 'Approved']);
    }
    $server->exec(
        "INSERT INTO research_defense_schedules
            (id, research_group_id, defense_type, defense_datetime, status)
         VALUES (1, 1, 'Pre-Oral Defense', NOW(), 'Completed'),
                (2, 2, 'Pre-Oral Defense', NOW(), 'Completed')"
    );
    for ($groupId = 1; $groupId <= 2; $groupId++) {
        for ($panelId = 101; $panelId <= 103; $panelId++) {
            $server->prepare(
                "INSERT INTO research_panel_assignments
                    (research_group_id, panel_user_id, panel_name, defense_phase, assignment_status)
                 VALUES (?, ?, 'Panel', 'Pre-Oral Defense', 'Assigned')"
            )->execute([$groupId, $panelId]);
            $server->prepare(
                "INSERT INTO preoral_defense_evaluations
                    (defense_schedule_id, panel_user_id, result, status)
                 VALUES (?, ?, 'APPROVED', 'Submitted')"
            )->execute([$groupId, $panelId]);
        }
    }

    $complete = cradGetFirstSemesterCompletionStatus($server, 1);
    $assert($complete['complete'], 'all eight approved milestones plus completed Pre-Oral should pass');
    $assert($complete['completed_milestones'] === 8, 'completion evidence should count all eight milestones');

    rpRecalculateOverallProgress($server, 1);
    $assert(
        (string) $server->query('SELECT current_stage FROM research_plans WHERE id = 1')->fetchColumn()
            === 'First Semester Completion',
        'a completed first-semester plan must not remain at Planning'
    );
    $incomplete = cradGetFirstSemesterCompletionStatus($server, 2);
    $assert(!$incomplete['complete'], 'an incomplete Documentation milestone should block carry-over');
    rpRecalculateOverallProgress($server, 2);
    $assert(
        (string) $server->query('SELECT current_stage FROM research_plans WHERE id = 2')->fetchColumn()
            === 'Research Development',
        'an active incomplete plan should synchronize to Research Development'
    );

    $server->exec("UPDATE research_groups SET current_phase = 'First Semester Completion' WHERE id = 1");
    cradEnsureSecondSemesterSchema($server);
    $assert(
        (string) $server->query('SELECT current_phase FROM research_groups WHERE id = 1')->fetchColumn()
            === 'First Semester Completion',
        'schema repair must not regress a completed first-semester group to post-Pre-Oral development'
    );

    $server->exec(
        "INSERT INTO academic_terms (academic_year, semester, term_code, status)
         VALUES ('2026-2027', '2nd Semester', '2026-2027-2', 'Active')"
    );
    $termId = (int) $server->lastInsertId();
    $groupTermId = cradEnrollGroupInTerm(
        $server,
        1,
        $termId,
        'Carry-over',
        'Second Semester Intake',
        900,
        'CRAD Test User',
        'Verified completion'
    );
    $assert($groupTermId > 0, 'a fully completed group should be enrolled');
    $assert(
        (string) $server->query('SELECT current_stage FROM research_plans WHERE id = 1')->fetchColumn()
            === 'Second Semester Intake',
        'second-semester enrollment should immediately synchronize the research plan stage'
    );

    $blocked = false;
    try {
        cradEnrollGroupInTerm(
            $server,
            2,
            $termId,
            'Carry-over',
            'Second Semester Intake',
            900,
            'CRAD Test User'
        );
    } catch (RuntimeException $exception) {
        $blocked = str_contains($exception->getMessage(), 'carry-over blocked');
    }
    $assert($blocked, 'server-side enrollment must reject incomplete first-semester evidence');

    $server->exec("UPDATE preoral_defense_evaluations SET result = 'FAILED' WHERE defense_schedule_id = 1 AND panel_user_id = 103");
    $failed = cradGetFirstSemesterCompletionStatus($server, 1);
    $assert(!$failed['complete'] && $failed['preoral_result'] === 'FAILED', 'a failed Pre-Oral result must block completion');

    echo '[OK] ' . $assertions . ' first-semester transition gate integration assertions passed.' . PHP_EOL;
} finally {
    $server->exec('USE information_schema');
    $server->exec('DROP DATABASE IF EXISTS ' . $quotedDatabase);
}
