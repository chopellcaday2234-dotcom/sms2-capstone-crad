<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/audit.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/second-semester-workflow.php';

requireAuth();
if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'admin', 'superadmin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$crad = cradDb();
if (!$crad instanceof PDO) {
    throw new RuntimeException('CRAD database is unavailable.');
}
cradEnsureSecondSemesterSchema($crad);

$message = '';
$error = '';
$actorUserId = (int) ($_SESSION['user_id'] ?? 0) ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        try {
            $action = (string) ($_POST['action'] ?? '');
            if ($action === 'create') {
                $termId = cradCreateAcademicTerm(
                    $crad,
                    (string) ($_POST['academic_year'] ?? ''),
                    (string) ($_POST['semester'] ?? ''),
                    (string) ($_POST['start_date'] ?? ''),
                    (string) ($_POST['end_date'] ?? ''),
                    $actorUserId
                );
                logActivity('create', 'Created CRAD academic term #' . $termId, 'crad');
                $message = 'Academic term created as Draft.';
            } elseif ($action === 'activate') {
                $termId = (int) ($_POST['term_id'] ?? 0);
                cradActivateAcademicTerm($crad, $termId);
                logActivity('update', 'Activated CRAD academic term #' . $termId, 'crad');
                $message = 'Academic term activated. Semester transition is now available.';
            } elseif ($action === 'close') {
                $termId = (int) ($_POST['term_id'] ?? 0);
                cradCloseAcademicTerm($crad, $termId, $actorUserId);
                logActivity('update', 'Closed CRAD academic term #' . $termId, 'crad');
                $message = 'Academic term closed. Its workflow records remain available for history and reports.';
            } else {
                throw new InvalidArgumentException('Unknown academic-term action.');
            }
        } catch (Throwable $exception) {
            $error = $exception instanceof PDOException && (string) $exception->getCode() === '23000'
                ? 'That academic year and semester already exist.'
                : $exception->getMessage();
        }
    }
}

$terms = $crad->query(
    'SELECT at.*, COUNT(rgt.id) AS group_count '
    . 'FROM academic_terms at '
    . 'LEFT JOIN research_group_terms rgt ON rgt.academic_term_id = at.id '
    . 'GROUP BY at.id ORDER BY at.academic_year DESC, FIELD(at.semester, "2nd Semester", "1st Semester", "Summer"), at.id DESC'
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$currentYear = (int) date('Y');
$defaultAcademicYear = $currentYear . '-' . ($currentYear + 1);
$pageTitle = 'Academic Term Management';
$activeModule = 'crad';
$activePage = 'academic-term-management';
$breadcrumbs = [
    ['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'],
    ['label' => 'Academic Term Management', 'url' => null],
];

require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard">
    <div class="glass-board">
        <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-panel h-100">
                    <div class="glass-panel-body">
                        <h5 class="glass-panel-title">Create Academic Term</h5>
                        <p class="text-muted small">Create it first as Draft. Only one term may be Active at a time.</p>
                        <form method="post">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="create">
                            <label class="form-label">Academic Year</label>
                            <input class="form-control mb-3" name="academic_year" value="<?= e($defaultAcademicYear) ?>" pattern="\d{4}-\d{4}" required>
                            <label class="form-label">Semester</label>
                            <select class="form-select mb-3" name="semester" required>
                                <option>1st Semester</option>
                                <option selected>2nd Semester</option>
                                <option>Summer</option>
                            </select>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Start Date</label>
                                    <input class="form-control" type="date" name="start_date">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">End Date</label>
                                    <input class="form-control" type="date" name="end_date">
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Create Draft Term</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="glass-panel">
                    <div class="glass-panel-body">
                        <h5 class="glass-panel-title">Academic Terms</h5>
                        <?php if ($terms === []): ?>
                            <p class="text-muted">No academic terms have been created.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th>Term</th><th>Dates</th><th>Groups</th><th>Status</th><th>Action</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($terms as $term): ?>
                                        <tr>
                                            <td><strong><?= e((string) $term['term_code']) ?></strong><div class="small text-muted"><?= e((string) $term['semester']) ?></div></td>
                                            <td><?= e((string) ($term['start_date'] ?: 'Not set')) ?> &ndash; <?= e((string) ($term['end_date'] ?: 'Not set')) ?></td>
                                            <td><?= (int) $term['group_count'] ?></td>
                                            <td><span class="badge text-bg-<?= $term['status'] === 'Active' ? 'success' : ($term['status'] === 'Closed' ? 'secondary' : 'warning') ?>"><?= e((string) $term['status']) ?></span></td>
                                            <td>
                                                <?php if ($term['status'] === 'Draft'): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Activate this academic term?');">
                                                        <?= csrfField() ?><input type="hidden" name="action" value="activate"><input type="hidden" name="term_id" value="<?= (int) $term['id'] ?>">
                                                        <button class="btn btn-sm btn-success">Activate</button>
                                                    </form>
                                                <?php elseif ($term['status'] === 'Active'): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Close this active term? Existing records will be retained.');">
                                                        <?= csrfField() ?><input type="hidden" name="action" value="close"><input type="hidden" name="term_id" value="<?= (int) $term['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger">Close</button>
                                                    </form>
                                                <?php else: ?>Closed<?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
