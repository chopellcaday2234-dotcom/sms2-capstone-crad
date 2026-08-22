<?php
declare(strict_types=1);

// Idempotent schema migration for the CRAD second-semester workflow.

const CRAD_SECOND_SEMESTER_SCHEMA_VERSION = '2026.08.22.1';

/** Quote a database identifier after validating that it is safe. */
function cradSecondSemesterIdentifier(string $identifier): string
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
        throw new InvalidArgumentException('Unsafe database identifier: ' . $identifier);
    }

    return '`' . $identifier . '`';
}

function cradSecondSemesterTableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES '
        . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $statement->execute([$table]);

    return (int) $statement->fetchColumn() > 0;
}

function cradSecondSemesterColumnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS '
        . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $statement->execute([$table, $column]);

    return (int) $statement->fetchColumn() > 0;
}

function cradSecondSemesterIndexExists(PDO $pdo, string $table, string $index): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS '
        . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $statement->execute([$table, $index]);

    return (int) $statement->fetchColumn() > 0;
}

function cradSecondSemesterAddColumn(
    PDO $pdo,
    string $table,
    string $column,
    string $definition
): bool {
    if (!cradSecondSemesterTableExists($pdo, $table)
        || cradSecondSemesterColumnExists($pdo, $table, $column)) {
        return false;
    }

    $pdo->exec(
        'ALTER TABLE ' . cradSecondSemesterIdentifier($table)
        . ' ADD COLUMN ' . cradSecondSemesterIdentifier($column) . ' ' . $definition
    );

    return true;
}

function cradSecondSemesterAddIndex(
    PDO $pdo,
    string $table,
    string $index,
    string $columns
): bool {
    if (!cradSecondSemesterTableExists($pdo, $table)
        || cradSecondSemesterIndexExists($pdo, $table, $index)) {
        return false;
    }

    $pdo->exec(
        'ALTER TABLE ' . cradSecondSemesterIdentifier($table)
        . ' ADD INDEX ' . cradSecondSemesterIdentifier($index) . ' (' . $columns . ')'
    );

    return true;
}

/** Repair imported final-phase tables whose IDs were missing AUTO_INCREMENT. */
function cradSecondSemesterRepairAutoIncrement(PDO $pdo, string $table): bool
{
    if (!cradSecondSemesterTableExists($pdo, $table)
        || !cradSecondSemesterColumnExists($pdo, $table, 'id')) {
        return false;
    }

    $statement = $pdo->prepare(
        'SELECT EXTRA FROM information_schema.COLUMNS '
        . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'"
    );
    $statement->execute([$table]);
    $extra = strtolower((string) $statement->fetchColumn());

    if (str_contains($extra, 'auto_increment')) {
        return false;
    }

    $pdo->exec(
        'ALTER TABLE ' . cradSecondSemesterIdentifier($table)
        . ' MODIFY COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT'
    );

    return true;
}

/**
 * Install or repair the second-semester schema without deleting existing data.
 *
 * @return array{version:string,operations:string[]}
 */
function cradEnsureSecondSemesterSchema(PDO $pdo): array
{
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $operations = [];

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `crad_schema_migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration_key` VARCHAR(120) NOT NULL,
            `description` VARCHAR(255) NOT NULL DEFAULT '',
            `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_crad_migration_key` (`migration_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `crad_data_integrity_archive` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `entity_type` VARCHAR(80) NOT NULL,
            `entity_id` BIGINT UNSIGNED NOT NULL,
            `payload_json` LONGTEXT DEFAULT NULL,
            `reason` VARCHAR(190) NOT NULL,
            `archived_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_integrity_archive_entity` (`entity_type`,`entity_id`,`reason`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `academic_terms` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `academic_year` VARCHAR(20) NOT NULL,
            `semester` ENUM('1st Semester','2nd Semester','Summer') NOT NULL,
            `term_code` VARCHAR(40) NOT NULL,
            `start_date` DATE DEFAULT NULL,
            `end_date` DATE DEFAULT NULL,
            `status` ENUM('Draft','Active','Closed') NOT NULL DEFAULT 'Draft',
            `created_by_user` INT UNSIGNED DEFAULT NULL,
            `closed_by_user` INT UNSIGNED DEFAULT NULL,
            `closed_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_academic_term_code` (`term_code`),
            UNIQUE KEY `uniq_academic_year_semester` (`academic_year`,`semester`),
            KEY `idx_academic_term_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `research_group_terms` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED NOT NULL,
            `source_group_term_id` INT UNSIGNED DEFAULT NULL,
            `enrollment_type` ENUM('New','Continuing','Carry-over','Repeat') NOT NULL DEFAULT 'Continuing',
            `starting_phase` VARCHAR(80) NOT NULL DEFAULT 'Second Semester Intake',
            `current_phase` VARCHAR(80) NOT NULL DEFAULT 'Second Semester Intake',
            `status` ENUM('Active','Completed','On Hold','Withdrawn') NOT NULL DEFAULT 'Active',
            `transition_confirmed_by` INT UNSIGNED DEFAULT NULL,
            `transition_confirmed_at` DATETIME DEFAULT NULL,
            `remarks` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_group_academic_term` (`research_group_id`,`academic_term_id`),
            KEY `idx_group_term_phase` (`current_phase`),
            KEY `idx_group_term_status` (`status`),
            KEY `idx_group_term_term` (`academic_term_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `research_workflow_events` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED DEFAULT NULL,
            `group_term_id` INT UNSIGNED DEFAULT NULL,
            `event_type` VARCHAR(80) NOT NULL,
            `from_phase` VARCHAR(80) DEFAULT NULL,
            `to_phase` VARCHAR(80) DEFAULT NULL,
            `entity_type` VARCHAR(80) DEFAULT NULL,
            `entity_id` INT UNSIGNED DEFAULT NULL,
            `actor_user_id` INT UNSIGNED DEFAULT NULL,
            `actor_name` VARCHAR(150) NOT NULL DEFAULT '',
            `remarks` TEXT DEFAULT NULL,
            `metadata_json` LONGTEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_group_created` (`research_group_id`,`created_at`),
            KEY `idx_workflow_term` (`academic_term_id`),
            KEY `idx_workflow_event_type` (`event_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $operations[] = 'Core semester and workflow tables checked';

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `final_readiness_checks` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED NOT NULL,
            `checked_by_user` INT UNSIGNED DEFAULT NULL,
            `checked_by_name` VARCHAR(150) NOT NULL DEFAULT '',
            `chapters_complete` TINYINT(1) NOT NULL DEFAULT 0,
            `adviser_endorsed` TINYINT(1) NOT NULL DEFAULT 0,
            `ethics_clearance_complete` TINYINT(1) NOT NULL DEFAULT 0,
            `similarity_check_complete` TINYINT(1) NOT NULL DEFAULT 0,
            `required_documents_complete` TINYINT(1) NOT NULL DEFAULT 0,
            `overall_status` ENUM('Incomplete','Ready','Revoked') NOT NULL DEFAULT 'Incomplete',
            `remarks` TEXT DEFAULT NULL,
            `checked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `revoked_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_readiness_group_term` (`research_group_id`,`academic_term_id`),
            KEY `idx_readiness_status` (`overall_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `final_draft_submissions` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED NOT NULL,
            `version_number` INT UNSIGNED NOT NULL,
            `supersedes_submission_id` INT UNSIGNED DEFAULT NULL,
            `submitted_by_user` INT UNSIGNED DEFAULT NULL,
            `submitted_by_name` VARCHAR(150) NOT NULL DEFAULT '',
            `original_name` VARCHAR(255) NOT NULL DEFAULT '',
            `stored_subdir` VARCHAR(180) NOT NULL DEFAULT '',
            `stored_name` VARCHAR(180) NOT NULL DEFAULT '',
            `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
            `file_mime` VARCHAR(120) NOT NULL DEFAULT '',
            `file_checksum` CHAR(64) NOT NULL DEFAULT '',
            `status` ENUM('Submitted','Under Adviser Review','Revision Requested','Endorsed','Superseded') NOT NULL DEFAULT 'Submitted',
            `adviser_user_id` INT UNSIGNED DEFAULT NULL,
            `adviser_name` VARCHAR(150) NOT NULL DEFAULT '',
            `adviser_remarks` TEXT DEFAULT NULL,
            `reviewed_at` DATETIME DEFAULT NULL,
            `submission_token` VARCHAR(64) NOT NULL,
            `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_final_draft_version` (`research_group_id`,`academic_term_id`,`version_number`),
            UNIQUE KEY `uniq_final_draft_token` (`submission_token`),
            KEY `idx_final_draft_status` (`status`),
            KEY `idx_final_draft_group` (`research_group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `final_draft_reviews` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `submission_id` INT UNSIGNED NOT NULL,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED NOT NULL,
            `adviser_user_id` INT UNSIGNED NOT NULL,
            `adviser_name` VARCHAR(150) NOT NULL DEFAULT '',
            `chapter_1_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `chapter_2_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `chapter_3_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `chapter_4_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `chapter_5_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `formatting_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `citations_accepted` TINYINT(1) NOT NULL DEFAULT 0,
            `decision` ENUM('Revision Requested','Endorsed','Endorsement Revoked') NOT NULL,
            `remarks` TEXT DEFAULT NULL,
            `is_current` TINYINT(1) NOT NULL DEFAULT 1,
            `reviewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `superseded_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_final_draft_review_submission` (`submission_id`,`is_current`),
            KEY `idx_final_draft_review_group` (`research_group_id`),
            KEY `idx_final_draft_review_adviser` (`adviser_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $operations[] = 'Final-draft readiness foundation checked';

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `defense_attempts` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `research_group_id` INT UNSIGNED NOT NULL,
            `academic_term_id` INT UNSIGNED NOT NULL,
            `defense_type` ENUM('Pre-Oral Defense','Final Defense','Re-Defense') NOT NULL,
            `attempt_number` INT UNSIGNED NOT NULL DEFAULT 1,
            `readiness_check_id` INT UNSIGNED DEFAULT NULL,
            `final_draft_submission_id` INT UNSIGNED DEFAULT NULL,
            `defense_schedule_id` INT UNSIGNED DEFAULT NULL,
            `status` ENUM('Ready for Scheduling','Scheduled','Completed','For Revision','Passed','Failed','Cancelled') NOT NULL DEFAULT 'Ready for Scheduling',
            `created_by_user` INT UNSIGNED DEFAULT NULL,
            `completed_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_defense_attempt` (`research_group_id`,`academic_term_id`,`defense_type`,`attempt_number`),
            UNIQUE KEY `uniq_defense_schedule_attempt` (`defense_schedule_id`),
            KEY `idx_defense_attempt_status` (`status`),
            KEY `idx_defense_attempt_term` (`academic_term_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `defense_official_results` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `defense_attempt_id` INT UNSIGNED NOT NULL,
            `research_group_id` INT UNSIGNED NOT NULL,
            `defense_schedule_id` INT UNSIGNED DEFAULT NULL,
            `assigned_panel_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `submitted_evaluation_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `average_score` DECIMAL(5,2) DEFAULT NULL,
            `official_result` ENUM('UNRESOLVED','PASSED','PASSED WITH REVISIONS','FAILED','VOID') NOT NULL DEFAULT 'UNRESOLVED',
            `result_basis_json` LONGTEXT DEFAULT NULL,
            `finalized_by_user` INT UNSIGNED DEFAULT NULL,
            `finalized_by_name` VARCHAR(150) NOT NULL DEFAULT '',
            `finalized_at` DATETIME DEFAULT NULL,
            `supersedes_result_id` INT UNSIGNED DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_official_result_attempt` (`defense_attempt_id`),
            KEY `idx_official_result_group` (`research_group_id`),
            KEY `idx_official_result_value` (`official_result`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `defense_revision_items` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `defense_attempt_id` INT UNSIGNED NOT NULL,
            `research_group_id` INT UNSIGNED NOT NULL,
            `source_evaluation_id` INT UNSIGNED DEFAULT NULL,
            `requested_by_user` INT UNSIGNED DEFAULT NULL,
            `requested_by_name` VARCHAR(150) NOT NULL DEFAULT '',
            `revision_text` TEXT NOT NULL,
            `status` ENUM('Open','Submitted','Complied','Rejected','Waived') NOT NULL DEFAULT 'Open',
            `verified_by_user` INT UNSIGNED DEFAULT NULL,
            `verified_at` DATETIME DEFAULT NULL,
            `verification_remarks` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_revision_item_attempt` (`defense_attempt_id`),
            KEY `idx_revision_item_group_status` (`research_group_id`,`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `defense_revision_submissions` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `defense_attempt_id` INT UNSIGNED NOT NULL,
            `research_group_id` INT UNSIGNED NOT NULL,
            `version_number` INT UNSIGNED NOT NULL,
            `submitted_by_user` INT UNSIGNED DEFAULT NULL,
            `submitted_by_name` VARCHAR(150) NOT NULL DEFAULT '',
            `response_notes` TEXT DEFAULT NULL,
            `original_name` VARCHAR(255) NOT NULL DEFAULT '',
            `stored_subdir` VARCHAR(180) NOT NULL DEFAULT '',
            `stored_name` VARCHAR(180) NOT NULL DEFAULT '',
            `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
            `file_mime` VARCHAR(120) NOT NULL DEFAULT '',
            `file_checksum` CHAR(64) NOT NULL DEFAULT '',
            `status` ENUM('Submitted','Under Review','For Resubmission','Complied','Superseded') NOT NULL DEFAULT 'Submitted',
            `reviewed_by_user` INT UNSIGNED DEFAULT NULL,
            `reviewed_at` DATETIME DEFAULT NULL,
            `review_remarks` TEXT DEFAULT NULL,
            `submission_token` VARCHAR(64) NOT NULL,
            `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_revision_submission_version` (`defense_attempt_id`,`version_number`),
            UNIQUE KEY `uniq_revision_submission_token` (`submission_token`),
            KEY `idx_revision_submission_group` (`research_group_id`),
            KEY `idx_revision_submission_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `final_manuscript_approval_history` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `approval_id` INT UNSIGNED DEFAULT NULL,
            `research_group_id` INT UNSIGNED NOT NULL,
            `decision_sequence` INT UNSIGNED NOT NULL,
            `decision_status` ENUM('Pending','Approved','Returned') NOT NULL,
            `actor_user_id` INT UNSIGNED DEFAULT NULL,
            `actor_name` VARCHAR(150) NOT NULL DEFAULT '',
            `remarks` TEXT DEFAULT NULL,
            `decision_at` DATETIME NOT NULL,
            `defense_schedule_id` INT UNSIGNED DEFAULT NULL,
            `manuscript_submission_id` INT UNSIGNED DEFAULT NULL,
            `revision_submission_id` INT UNSIGNED DEFAULT NULL,
            `defense_attempt_id` INT UNSIGNED DEFAULT NULL,
            `revision_cycle_id` INT UNSIGNED DEFAULT NULL,
            `file_checksum` CHAR(64) NOT NULL DEFAULT '',
            `approval_token` VARCHAR(64) NOT NULL DEFAULT '',
            `event_source` VARCHAR(60) NOT NULL DEFAULT 'WORKFLOW',
            `source_updated_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_fmah_group_sequence` (`research_group_id`,`decision_sequence`),
            KEY `idx_fmah_approval` (`approval_id`),
            KEY `idx_fmah_group_status` (`research_group_id`,`decision_status`),
            KEY `idx_fmah_evidence` (`manuscript_submission_id`,`revision_submission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $operations[] = 'Defense attempt, result, revision, and final-approval history foundation checked';

    $autoIncrementTables = [
        'final_defense_recommendations',
        'manuscript_submissions',
        'manuscript_evaluations',
        'final_defense_evaluations',
        'final_manuscript_approvals',
        'final_manuscript_approval_history',
        'publications',
        'research_revision_cycles',
    ];
    foreach ($autoIncrementTables as $table) {
        if (cradSecondSemesterRepairAutoIncrement($pdo, $table)) {
            $operations[] = 'Repaired AUTO_INCREMENT on ' . $table . '.id';
        }
    }

    $columnDefinitions = [
        ['research_groups', 'current_phase', "VARCHAR(80) NOT NULL DEFAULT 'Research Development'"],
        ['research_groups', 'current_phase_started_at', 'DATETIME DEFAULT NULL'],

        ['final_defense_recommendations', 'academic_term_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_defense_recommendations', 'readiness_check_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_defense_recommendations', 'final_draft_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_defense_recommendations', 'revoked_by_user', 'INT UNSIGNED DEFAULT NULL'],
        ['final_defense_recommendations', 'revoked_at', 'DATETIME DEFAULT NULL'],
        ['final_defense_recommendations', 'revocation_reason', 'TEXT DEFAULT NULL'],

        ['final_readiness_checks', 'final_draft_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_readiness_checks', 'adviser_review_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_readiness_checks', 'chapter_1_complete', 'TINYINT(1) NOT NULL DEFAULT 0'],
        ['final_readiness_checks', 'chapter_2_complete', 'TINYINT(1) NOT NULL DEFAULT 0'],
        ['final_readiness_checks', 'chapter_3_complete', 'TINYINT(1) NOT NULL DEFAULT 0'],
        ['final_readiness_checks', 'chapter_4_complete', 'TINYINT(1) NOT NULL DEFAULT 0'],
        ['final_readiness_checks', 'chapter_5_complete', 'TINYINT(1) NOT NULL DEFAULT 0'],
        ['final_readiness_checks', 'checklist_version', 'INT UNSIGNED NOT NULL DEFAULT 1'],
        ['final_readiness_checks', 'is_current', 'TINYINT(1) NOT NULL DEFAULT 1'],
        ['final_readiness_checks', 'superseded_at', 'DATETIME DEFAULT NULL'],

        ['final_draft_submissions', 'submission_notes', 'TEXT DEFAULT NULL'],

        ['manuscript_submissions', 'academic_term_id', 'INT UNSIGNED DEFAULT NULL'],
        ['manuscript_submissions', 'purpose', "ENUM('Final Defense','Post-Defense Revision','Final Approved Copy') NOT NULL DEFAULT 'Final Defense'"],
        ['manuscript_submissions', 'supersedes_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['manuscript_submissions', 'file_checksum', "CHAR(64) NOT NULL DEFAULT ''"],
        ['manuscript_submissions', 'locked_at', 'DATETIME DEFAULT NULL'],
        ['manuscript_submissions', 'locked_by_user', 'INT UNSIGNED DEFAULT NULL'],

        ['final_defense_evaluations', 'defense_attempt_id', 'INT UNSIGNED DEFAULT NULL'],
        ['research_defense_schedules', 'defense_type', "VARCHAR(60) NOT NULL DEFAULT 'Pre-Oral Defense'"],
        ['research_defense_schedules', 'academic_term_id', 'INT UNSIGNED DEFAULT NULL'],
        ['research_defense_schedules', 'defense_attempt_id', 'INT UNSIGNED DEFAULT NULL'],

        ['research_revision_cycles', 'defense_attempt_id', 'INT UNSIGNED DEFAULT NULL'],
        ['research_revision_cycles', 'revision_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['research_revision_cycles', 'compliance_verified_by', 'INT UNSIGNED DEFAULT NULL'],
        ['research_revision_cycles', 'compliance_verified_at', 'DATETIME DEFAULT NULL'],
        ['research_revision_cycles', 'compliance_remarks', 'TEXT DEFAULT NULL'],

        ['final_manuscript_approvals', 'manuscript_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_manuscript_approvals', 'revision_submission_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_manuscript_approvals', 'defense_attempt_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_manuscript_approvals', 'revision_cycle_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_manuscript_approvals', 'file_checksum', "CHAR(64) NOT NULL DEFAULT ''"],
        ['final_manuscript_approvals', 'approval_token', "VARCHAR(64) NOT NULL DEFAULT ''"],
        ['final_manuscript_approvals', 'superseded_by_approval_id', 'INT UNSIGNED DEFAULT NULL'],
        ['final_manuscript_approvals', 'superseded_at', 'DATETIME DEFAULT NULL'],

        ['publications', 'final_approval_id', 'INT UNSIGNED DEFAULT NULL'],
        ['publications', 'abstract', 'LONGTEXT DEFAULT NULL'],
        ['publications', 'keywords', 'TEXT DEFAULT NULL'],
        ['publications', 'department', "VARCHAR(160) NOT NULL DEFAULT ''"],
        ['publications', 'adviser_name', "VARCHAR(150) NOT NULL DEFAULT ''"],
        ['publications', 'access_level', "ENUM('Public','Campus Only','Restricted','Embargoed') NOT NULL DEFAULT 'Campus Only'"],
        ['publications', 'embargo_until', 'DATE DEFAULT NULL'],
        ['publications', 'license_name', "VARCHAR(120) NOT NULL DEFAULT ''"],
        ['publications', 'repository_identifier', "VARCHAR(120) NOT NULL DEFAULT ''"],
    ];

    foreach ($columnDefinitions as [$table, $column, $definition]) {
        if (cradSecondSemesterAddColumn($pdo, $table, $column, $definition)) {
            $operations[] = 'Added ' . $table . '.' . $column;
        }
    }

    $approvalBackfillColumns = [
        'research_group_id', 'status', 'approved_by_user', 'approved_by_name', 'remarks',
        'approved_at', 'created_at', 'updated_at', 'defense_schedule_id',
        'manuscript_submission_id', 'revision_submission_id', 'defense_attempt_id',
        'revision_cycle_id', 'file_checksum', 'approval_token',
    ];
    $canBackfillApprovals = cradSecondSemesterTableExists($pdo, 'final_manuscript_approvals');
    foreach ($approvalBackfillColumns as $approvalBackfillColumn) {
        $canBackfillApprovals = $canBackfillApprovals
            && cradSecondSemesterColumnExists($pdo, 'final_manuscript_approvals', $approvalBackfillColumn);
    }
    if ($canBackfillApprovals) {
        $pdo->exec(
            "INSERT INTO final_manuscript_approval_history
                (approval_id, research_group_id, decision_sequence, decision_status,
                 actor_user_id, actor_name, remarks, decision_at, defense_schedule_id,
                 manuscript_submission_id, revision_submission_id, defense_attempt_id,
                 revision_cycle_id, file_checksum, approval_token, event_source, source_updated_at)
             SELECT fma.id, fma.research_group_id, 1, fma.status,
                    fma.approved_by_user, fma.approved_by_name, fma.remarks,
                    COALESCE(fma.approved_at, fma.updated_at, fma.created_at),
                    fma.defense_schedule_id, fma.manuscript_submission_id,
                    fma.revision_submission_id, fma.defense_attempt_id,
                    fma.revision_cycle_id, fma.file_checksum, fma.approval_token,
                    'SCHEMA_BACKFILL', fma.updated_at
             FROM final_manuscript_approvals fma
             WHERE NOT EXISTS (
                 SELECT 1 FROM final_manuscript_approval_history history
                 WHERE history.research_group_id = fma.research_group_id
             )"
        );
    }

    if (cradSecondSemesterTableExists($pdo, 'research_groups')
        && cradSecondSemesterColumnExists($pdo, 'research_groups', 'current_phase')) {
        $defaultStatement = $pdo->prepare(
            "SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'research_groups' AND COLUMN_NAME = 'current_phase'"
        );
        $defaultStatement->execute();
        if ((string) $defaultStatement->fetchColumn() !== 'Research Development') {
            $pdo->exec(
                "ALTER TABLE `research_groups` MODIFY COLUMN `current_phase` VARCHAR(80) NOT NULL DEFAULT 'Research Development'"
            );
            $operations[] = 'Updated research_groups.current_phase initial default';
        }
    }

    // One-time compatibility migration for the old second-semester phase label.
    $pdo->exec(
        "UPDATE research_group_terms SET starting_phase = 'Final Documentation'
         WHERE starting_phase = 'Chapter Development'"
    );
    $pdo->exec(
        "UPDATE research_group_terms SET current_phase = 'Final Documentation'
         WHERE current_phase = 'Chapter Development'"
    );
    $pdo->exec(
        "UPDATE research_groups SET current_phase = 'Final Documentation'
         WHERE current_phase = 'Chapter Development'"
    );

    // Preserve then remove legacy panel rows whose research group no longer exists.
    if (cradSecondSemesterTableExists($pdo, 'research_panel_assignments')) {
        $pdo->exec(
            "INSERT IGNORE INTO crad_data_integrity_archive (entity_type, entity_id, payload_json, reason)
             SELECT 'research_panel_assignment', rpa.id,
                    JSON_OBJECT(
                        'research_group_id', rpa.research_group_id,
                        'panel_user_id', rpa.panel_user_id,
                        'panel_name', rpa.panel_name,
                        'defense_phase', rpa.defense_phase,
                        'assignment_status', rpa.assignment_status
                    ),
                    'Orphaned assignment: research group no longer exists'
             FROM research_panel_assignments rpa
             LEFT JOIN research_groups rg ON rg.id = rpa.research_group_id
             WHERE rg.id IS NULL"
        );
        $removed = $pdo->exec(
            "DELETE rpa FROM research_panel_assignments rpa
             LEFT JOIN research_groups rg ON rg.id = rpa.research_group_id
             WHERE rg.id IS NULL"
        );
        if ((int) $removed > 0) {
            $operations[] = 'Archived and removed ' . (int) $removed . ' orphan panel assignment(s)';
        }
    }

    // Backfill Pre-Oral records that were completed before automatic phase syncing existed.
    if (cradSecondSemesterTableExists($pdo, 'research_defense_schedules')
        && cradSecondSemesterTableExists($pdo, 'preoral_defense_evaluations')
        && cradSecondSemesterTableExists($pdo, 'research_panel_assignments')) {
        $pdo->exec(
            "UPDATE research_defense_schedules rds
             SET rds.status = CASE
                 WHEN EXISTS (
                     SELECT 1 FROM preoral_defense_evaluations failed
                     WHERE failed.defense_schedule_id = rds.id
                       AND failed.status = 'Submitted' AND failed.result = 'FAILED'
                 ) THEN 'Failed'
                 ELSE 'Completed'
             END,
             rds.updated_at = NOW()
             WHERE LOWER(TRIM(COALESCE(rds.defense_type, ''))) IN ('pre-oral','pre-oral defense')
               AND (SELECT COUNT(DISTINCT rpa.panel_user_id)
                    FROM research_panel_assignments rpa
                    WHERE rpa.research_group_id = rds.research_group_id
                      AND rpa.defense_phase = 'Pre-Oral Defense'
                      AND rpa.assignment_status = 'Assigned') > 0
               AND (SELECT COUNT(DISTINCT pde.panel_user_id)
                    FROM preoral_defense_evaluations pde
                    WHERE pde.defense_schedule_id = rds.id
                      AND pde.status = 'Submitted') >=
                   (SELECT COUNT(DISTINCT rpa2.panel_user_id)
                    FROM research_panel_assignments rpa2
                    WHERE rpa2.research_group_id = rds.research_group_id
                      AND rpa2.defense_phase = 'Pre-Oral Defense'
                      AND rpa2.assignment_status = 'Assigned')"
        );

        $pdo->exec(
            "UPDATE research_groups rg
             INNER JOIN (
                 SELECT rds.research_group_id, rds.status
                 FROM research_defense_schedules rds
                 INNER JOIN (
                     SELECT research_group_id, MAX(id) AS latest_id
                     FROM research_defense_schedules
                     WHERE LOWER(TRIM(COALESCE(defense_type, ''))) IN ('pre-oral','pre-oral defense')
                       AND LOWER(TRIM(status)) IN ('completed','failed')
                     GROUP BY research_group_id
                 ) latest ON latest.latest_id = rds.id
             ) preoral ON preoral.research_group_id = rg.id
             SET rg.current_phase = IF(LOWER(preoral.status) = 'failed', 'Pre-Oral Failed', 'Post Pre-Oral Development'),
                 rg.current_phase_started_at = COALESCE(rg.current_phase_started_at, NOW())
             WHERE rg.current_phase IN ('Research Development','Pre-Oral Defense','Pre-Oral Failed','Post Pre-Oral Development')
               AND NOT EXISTS (SELECT 1 FROM research_group_terms rgt WHERE rgt.research_group_id = rg.id)"
        );
    }

    $indexDefinitions = [
        ['research_groups', 'idx_rg_current_phase', '`current_phase`'],
        ['final_defense_recommendations', 'idx_fdr_term', '`academic_term_id`'],
        ['final_defense_recommendations', 'idx_fdr_readiness', '`readiness_check_id`'],
        ['final_defense_recommendations', 'idx_fdr_final_draft', '`final_draft_submission_id`'],
        ['final_readiness_checks', 'idx_readiness_current', '`research_group_id`,`academic_term_id`,`is_current`'],
        ['final_readiness_checks', 'idx_readiness_draft', '`final_draft_submission_id`'],
        ['final_readiness_checks', 'idx_readiness_review', '`adviser_review_id`'],
        ['manuscript_submissions', 'idx_manuscript_term_purpose', '`academic_term_id`,`purpose`'],
        ['manuscript_submissions', 'idx_manuscript_supersedes', '`supersedes_submission_id`'],
        ['final_defense_evaluations', 'idx_final_attempt', '`defense_attempt_id`'],
        ['defense_revision_items', 'idx_revision_item_evaluation', '`defense_attempt_id`,`source_evaluation_id`'],
        ['research_defense_schedules', 'idx_rds_term_type', '`academic_term_id`,`defense_type`'],
        ['research_defense_schedules', 'idx_rds_attempt', '`defense_attempt_id`'],
        ['research_revision_cycles', 'idx_rrc_attempt', '`defense_attempt_id`'],
        ['research_revision_cycles', 'idx_rrc_submission', '`revision_submission_id`'],
        ['final_manuscript_approvals', 'idx_fma_submission', '`manuscript_submission_id`'],
        ['final_manuscript_approvals', 'idx_fma_revision_submission', '`revision_submission_id`'],
        ['final_manuscript_approvals', 'idx_fma_attempt', '`defense_attempt_id`'],
        ['publications', 'idx_pub_approval', '`final_approval_id`'],
        ['publications', 'idx_pub_access', '`access_level`'],
        ['publications', 'idx_pub_repository_identifier', '`repository_identifier`'],
    ];

    foreach ($indexDefinitions as [$table, $index, $columns]) {
        if (cradSecondSemesterAddIndex($pdo, $table, $index, $columns)) {
            $operations[] = 'Added index ' . $index . ' on ' . $table;
        }
    }

    $migration = $pdo->prepare(
        'INSERT INTO crad_schema_migrations (migration_key, description) VALUES (?, ?) '
        . 'ON DUPLICATE KEY UPDATE description = VALUES(description)'
    );
    $migration->execute([
        CRAD_SECOND_SEMESTER_SCHEMA_VERSION,
        'CRAD second-semester workflow with synchronized stages and append-only final approval history',
    ]);

    return [
        'version' => CRAD_SECOND_SEMESTER_SCHEMA_VERSION,
        'operations' => $operations,
    ];
}
