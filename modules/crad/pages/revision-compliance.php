<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/audit.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/final-phase-helpers.php';
requireAuth();
if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'research_coordinator', 'superadmin', 'admin'], true)) { http_response_code(403); exit('Forbidden'); }
$crad = cradDb(); finalPhaseEnsureSchema($crad); $message = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) $error = 'Security check failed.';
    else {
        $groupId = (int) ($_POST['group_id'] ?? 0); $status = (string) ($_POST['revision_status'] ?? '');
        $remarks = trim((string) ($_POST['review_remarks'] ?? ''));
        try {
            if (fpSetRevisionStatus(
                $crad,
                $groupId,
                $status,
                (int) ($_SESSION['user_id'] ?? 0),
                function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? 'CRAD User'),
                $remarks
            )) { logActivity('update', 'Updated final defense revision compliance for group #' . $groupId . ' to ' . $status, 'crad'); $message = 'Revision compliance status updated.'; }
            else $error = 'A dedicated revision file must be submitted before this status can be changed.';
        } catch (Throwable $exception) { $error = $exception->getMessage(); }
    }
}
$rows = $crad->query("SELECT rc.*, rg.group_number, rg.research_title FROM research_revision_cycles rc LEFT JOIN research_groups rg ON rg.id = rc.research_group_id ORDER BY rc.updated_at DESC, rc.id DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
foreach ($rows as &$row) { $row['revision_submission'] = fpGetLatestDefenseRevisionSubmission($crad, (int) $row['research_group_id'], (int) ($row['defense_attempt_id'] ?? 0)); }
unset($row);
$breadcrumbs = [['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'], ['label' => 'Revision & Compliance', 'url' => null]];
require_once ROOT_PATH . '/includes/layout-start.php'; renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard"><div class="glass-board"><div class="glass-panel"><div class="glass-panel-body"><h5 class="glass-panel-title">Final Defense Revision & Compliance</h5><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><?php if (!$rows): ?><p class="text-muted">No Final Defense revision cycles have been opened.</p><?php else: ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Group</th><th>Official Result</th><th>Revision Evidence</th><th>Status</th><th>Opened</th><th>Action</th></tr></thead><tbody><?php foreach ($rows as $row): $submission = $row['revision_submission']; ?><tr><td><?= e((string) $row['group_number']) ?><div class="small text-muted"><?= e((string) $row['research_title']) ?></div></td><td><?= e((string) $row['official_result']) ?></td><td><?php if ($submission): ?><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/defense-revision-document.php?id=<?= (int) $submission['id'] ?>"><?= e((string) $submission['original_name']) ?></a><div class="small text-muted"><?= e((string) $submission['status']) ?></div><?php else: ?><span class="text-muted">Not submitted</span><?php endif; ?></td><td><?= e((string) $row['revision_status']) ?></td><td><?= e((string) $row['opened_at']) ?></td><td><form method="post" class="d-flex gap-2 flex-wrap"><?= csrfField() ?><input type="hidden" name="group_id" value="<?= (int) $row['research_group_id'] ?>"><select class="form-select form-select-sm" name="revision_status" style="max-width:170px"><option <?= $row['revision_status'] === 'Needs Revision' ? 'selected' : '' ?>>Needs Revision</option><option <?= $row['revision_status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option><option <?= $row['revision_status'] === 'Compliant' ? 'selected' : '' ?>>Compliant</option></select><input class="form-control form-control-sm" name="review_remarks" style="max-width:240px" placeholder="Review remarks"><button class="btn btn-primary btn-sm">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div></div></div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
