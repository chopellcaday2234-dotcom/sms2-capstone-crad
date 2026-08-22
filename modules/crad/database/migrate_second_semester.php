<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once __DIR__ . '/second_semester_schema.php';

$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
    require_once ROOT_PATH . '/includes/authentication.php';
    require_once ROOT_PATH . '/includes/security.php';
    require_once ROOT_PATH . '/includes/audit.php';
    requireAuth();

    if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'admin', 'superadmin'], true)) {
        http_response_code(403);
        exit('Forbidden');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ?><!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>CRAD Second-Semester Migration</title>
            <style>
                body { font-family: system-ui, sans-serif; max-width: 720px; margin: 3rem auto; padding: 0 1rem; }
                .card { border: 1px solid #dbe2ea; border-radius: 12px; padding: 1.5rem; }
                button { border: 0; border-radius: 8px; background: #1d4ed8; color: white; padding: .7rem 1rem; }
            </style>
        </head>
        <body>
        <div class="card">
            <h1>CRAD Second-Semester Migration</h1>
            <p>This safely adds or repairs the current Phase 1–2 database foundation. Existing CRAD records are retained.</p>
            <form method="post">
                <?= csrfField() ?>
                <button type="submit">Run Safe Migration</button>
            </form>
        </div>
        </body>
        </html><?php
        exit;
    }

    if (!csrfVerify()) {
        http_response_code(419);
        exit('Security check failed. Please refresh and try again.');
    }
}

try {
    $pdo = getCradDatabaseConnection();
    $result = cradEnsureSecondSemesterSchema($pdo);

    if ($isCli) {
        echo '[OK] CRAD second-semester schema ' . $result['version'] . PHP_EOL;
        foreach ($result['operations'] as $operation) {
            echo ' - ' . $operation . PHP_EOL;
        }
        exit(0);
    }

    logActivity('update', 'Applied CRAD second-semester schema ' . $result['version'], 'crad');
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CRAD Migration Complete</title>
        <style>
            body { font-family: system-ui, sans-serif; max-width: 720px; margin: 3rem auto; padding: 0 1rem; }
            .ok { background: #ecfdf5; color: #065f46; border-radius: 12px; padding: 1.5rem; }
        </style>
    </head>
    <body><div class="ok">
        <h1>Migration complete</h1>
        <p>Schema version <?= htmlspecialchars($result['version']) ?> is ready.</p>
        <ul><?php foreach ($result['operations'] as $operation): ?>
            <li><?= htmlspecialchars($operation) ?></li>
        <?php endforeach; ?></ul>
        <p><a href="<?= BASE_URL ?>/modules/crad/index.php?page=academic-term-management">Open Academic Term Management</a></p>
    </div></body></html><?php
} catch (Throwable $exception) {
    if ($isCli) {
        fwrite(STDERR, '[ERROR] ' . $exception->getMessage() . PHP_EOL);
        exit(1);
    }

    http_response_code(500);
    echo '<h1>Migration failed</h1><p>' . htmlspecialchars($exception->getMessage()) . '</p>';
}
