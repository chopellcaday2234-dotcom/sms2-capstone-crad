<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$relativePage = trim((string) ($argv[1] ?? ''), '/\\');
$role = (string) ($argv[2] ?? 'student');
$userId = (int) ($argv[3] ?? 0);
$email = (string) ($argv[4] ?? '');
$name = (string) ($argv[5] ?? 'Smoke Test User');
$studentId = (string) ($argv[6] ?? '');

if ($relativePage === '' || str_contains($relativePage, '..') || !str_ends_with($relativePage, '.php')) {
    throw new InvalidArgumentException('A safe PHP page path is required.');
}

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/config/session.php';

$absolutePage = ROOT_PATH . '/' . str_replace('\\', '/', $relativePage);
$resolvedRoot = realpath(ROOT_PATH);
$resolvedPage = realpath($absolutePage);
if (!$resolvedRoot || !$resolvedPage || !str_starts_with($resolvedPage, $resolvedRoot . DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('Smoke-test page was not found inside the application root.');
}

$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = $role;
$_SESSION['user_role_key'] = $role;
$_SESSION['user_email'] = $email;
$_SESSION['student_id'] = $studentId;
$_SESSION['last_activity'] = time();
$_SESSION['login_at'] = time();
$_SESSION['must_change_password'] = 0;
unset($_SESSION['presence_touched_at']);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8088';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/' . $relativePage;
$_SERVER['SCRIPT_NAME'] = '/' . $relativePage;
$_SERVER['PHP_SELF'] = '/' . $relativePage;

$initialBufferLevel = ob_get_level();
$reported = false;
$report = static function (string $html, ?Throwable $exception = null) use ($relativePage): bool {
    $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $details = [];
    if ($exception !== null) {
        $details[] = get_class($exception) . ': ' . $exception->getMessage();
    }
    if (preg_match_all(
        '/(?:Fatal error|Parse error|Uncaught\s+[^\r\n]*|(?:Warning|Notice|Deprecated):|Undefined (?:variable|array key))[\s\S]{0,1200}?\bin\s+[A-Za-z]:[\\\\\/][\s\S]{1,500}?\bon line\s+\d+/i',
        $plain,
        $matches
    )) {
        foreach (array_slice(array_values(array_unique($matches[0])), 0, 5) as $match) {
            $details[] = trim(preg_replace('/\s+/', ' ', $match) ?? $match);
        }
    }

    $runtimeMarkers = $details !== [];
    echo 'OK|' . $relativePage . '|bytes=' . strlen($html) . '|runtime_markers=' . ($runtimeMarkers ? '1' : '0');
    if ($runtimeMarkers) {
        echo '|details=' . json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    echo PHP_EOL;
    return $runtimeMarkers;
};

register_shutdown_function(static function () use (&$reported, $initialBufferLevel, $relativePage, $report): void {
    if ($reported) {
        return;
    }
    $html = '';
    while (ob_get_level() > $initialBufferLevel) {
        $chunk = ob_get_clean();
        $html = (string) $chunk . $html;
    }
    $lastError = error_get_last();
    $exception = null;
    if (is_array($lastError) && in_array((int) ($lastError['type'] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $exception = new RuntimeException((string) ($lastError['message'] ?? 'Fatal PHP error'));
    }
    $failed = $report($html, $exception);
    $reported = true;
    if ($failed) {
        exit(1);
    }
});

ob_start();
try {
    require $resolvedPage;
    $html = (string) ob_get_clean();
} catch (Throwable $exception) {
    $html = '';
    while (ob_get_level() > $initialBufferLevel) {
        $chunk = ob_get_clean();
        $html = (string) $chunk . $html;
    }
    $reported = true;
    $report($html, $exception);
    exit(1);
}

$reported = true;
$runtimeMarkers = $report($html);
exit($runtimeMarkers ? 1 : 0);
