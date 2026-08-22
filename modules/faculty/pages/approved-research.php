<?php
$isFacultyAjaxRequest = !empty($_GET['faculty_ajax']);
if ($isFacultyAjaxRequest) {
    // Keep PHP notices/warnings from corrupting the JSON response consumed by
    // the adviser approval dialog. The dispatcher clears this buffer before
    // emitting its response.
    if (ob_get_level() === 0) {
        ob_start();
    }
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
// Authenticate first so JSON endpoints are never exposed without a session.
requireAuth();

// Include the shared faculty handler BEFORE any output: for ?faculty_ajax=...
// requests it returns clean JSON (no HTML prefix) and exits early.
require_once ROOT_PATH . '/modules/faculty/includes/faculty-account-page.php';

$pageTitle  = 'Approved Research';
$activeModule = 'faculty';
$activePage = 'approved-research';
require_once ROOT_PATH . '/includes/layout-start.php';
renderFacultyAccountPage($pageTitle, $activePage, 'approved-research');
require_once ROOT_PATH . '/includes/layout-end.php';
