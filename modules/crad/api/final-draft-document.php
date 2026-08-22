<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/uploads.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/final-readiness-helpers.php';

requireAuth();
$crad = cradDb();
if (!$crad instanceof PDO) {
    http_response_code(503);
    exit('CRAD database unavailable.');
}
frEnsureSchema($crad);

$documentId = (int) ($_GET['id'] ?? 0);
$statement = $crad->prepare(
    'SELECT fds.*, rg.leader_id FROM final_draft_submissions fds '
    . 'INNER JOIN research_groups rg ON rg.id = fds.research_group_id '
    . 'WHERE fds.id = ? LIMIT 1'
);
$statement->execute([$documentId]);
$document = $statement->fetch(PDO::FETCH_ASSOC);
if (!$document) {
    http_response_code(404);
    exit('Document not found.');
}

$role = getCurrentUserRoleKey();
$allowed = in_array($role, ['crad_officer', 'research_coordinator', 'admin', 'superadmin'], true);
if (in_array($role, ['adviser', 'faculty'], true)) {
    $allowed = frIsAssignedAdviser(
        $crad,
        (int) $document['research_group_id'],
        (int) ($_SESSION['user_id'] ?? 0),
        (string) ($_SESSION['user_email'] ?? '')
    );
} elseif ($role === 'student') {
    $sessionStudentId = trim((string) ($_SESSION['student_id'] ?? ''));
    $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
    $leaderId = trim((string) ($document['leader_id'] ?? ''));
    $allowed = ($sessionStudentId !== '' && hash_equals($leaderId, $sessionStudentId))
        || ($sessionUserId > 0 && ctype_digit($leaderId) && (int) $leaderId === $sessionUserId);
}
if (!$allowed) {
    http_response_code(403);
    exit('Forbidden');
}

$subdir = trim((string) $document['stored_subdir'], '/');
$storedName = basename((string) $document['stored_name']);
$uploadRoot = realpath(smsUploadRoot());
$path = realpath(smsUploadRoot() . '/' . $subdir . '/' . $storedName);
if (!$uploadRoot || !$path || strpos($path, $uploadRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('Document file not found.');
}

$downloadName = str_replace(["\r", "\n", '"'], '', basename((string) $document['original_name']));
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . ((string) $document['file_mime'] ?: 'application/octet-stream'));
header('Content-Length: ' . (string) filesize($path));
header('Content-Disposition: inline; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
readfile($path);
