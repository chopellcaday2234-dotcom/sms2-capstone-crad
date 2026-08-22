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
$role = getCurrentUserRoleKey();
if (!in_array($role, ['crad_officer','research_coordinator','adviser','superadmin','admin'], true)) { http_response_code(403); exit('Forbidden'); }
$crad = cradDb(); finalPhaseEnsureSchema($crad); $message = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        try {
            $submissionId = (int) ($_POST['submission_id'] ?? 0);
            $action = (string) ($_POST['review_action'] ?? '');
            $stmt = $crad->prepare(
                "SELECT ms.* FROM manuscript_submissions ms WHERE ms.id = ? AND ms.purpose = 'Final Defense' LIMIT 1"
            );
            $stmt->execute([$submissionId]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$submission) {
                throw new RuntimeException('Official manuscript submission not found.');
            }
            if ($role === 'adviser' && !fpIsAssignedAdviser(
                $crad,
                (int) $submission['research_group_id'],
                (int) ($_SESSION['user_id'] ?? 0),
                (string) ($_SESSION['user_email'] ?? '')
            )) {
                throw new RuntimeException('You are not assigned to this research group.');
            }

            $scores = [];
            foreach (['content','methodology','results','conclusions','recommendations','references','formatting','compliance'] as $key) {
                $scores[$key] = $_POST[$key . '_score'] ?? null;
            }
            $result = fpSaveManuscriptEvaluation(
                $crad,
                $submissionId,
                (int) ($_SESSION['user_id'] ?? 0),
                function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? ''),
                $scores,
                $action,
                trim((string) ($_POST['remarks'] ?? ''))
            );
            logActivity('update', $result['result'] . ' official manuscript submission #' . $submissionId, 'crad');
            $message = $result['result'] === 'APPROVED'
                ? 'Official manuscript approved. The group may now proceed to Final Defense scheduling.'
                : 'Revision requested. The student may now submit a new official manuscript version.';
        } catch (Throwable $exception) {
            error_log('Final manuscript review failed: ' . $exception->getMessage());
            $error = $exception->getMessage();
        }
    }
}
$listSql = "SELECT ms.*, rg.group_number, rg.group_name, rg.research_title, "
    . "me.result AS evaluation_result, me.overall_score "
    . "FROM manuscript_submissions ms "
    . "INNER JOIN research_groups rg ON rg.id = ms.research_group_id "
    . "INNER JOIN (SELECT research_group_id, MAX(version_number) version_number FROM manuscript_submissions "
    . "WHERE purpose = 'Final Defense' GROUP BY research_group_id) latest "
    . "ON latest.research_group_id = ms.research_group_id AND latest.version_number = ms.version_number "
    . "LEFT JOIN manuscript_evaluations me ON me.id = (SELECT me2.id FROM manuscript_evaluations me2 "
    . "WHERE me2.submission_id = ms.id ORDER BY me2.id DESC LIMIT 1) "
    . "INNER JOIN academic_terms at ON at.id = ms.academic_term_id AND at.status = 'Active' "
    . "AND at.semester = '2nd Semester' WHERE ms.purpose = 'Final Defense'";
$listParams = [];
if ($role === 'adviser') {
    $listSql .= " AND EXISTS (SELECT 1 FROM research_adviser_assignments raa WHERE raa.research_group_id = ms.research_group_id AND raa.assignment_status IN ('Assigned', 'Confirmed') AND ((raa.adviser_user_id IS NOT NULL AND raa.adviser_user_id = ?) OR (? <> '' AND LOWER(TRIM(COALESCE(raa.adviser_email, ''))) = LOWER(?))))";
    $listParams = [(int) ($_SESSION['user_id'] ?? 0), (string) ($_SESSION['user_email'] ?? ''), (string) ($_SESSION['user_email'] ?? '')];
}
$listSql .= ' ORDER BY ms.submitted_at DESC';
$stmt = $crad->prepare($listSql); $stmt->execute($listParams); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$breadcrumbs = [['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'], ['label' => 'Final Manuscript Review', 'url' => null]];
require_once ROOT_PATH . '/includes/layout-start.php'; renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard"><div class="glass-board"><div class="glass-panel"><div class="glass-panel-body"><h5 class="glass-panel-title">Official Chapters 1–5 Manuscript Evaluation</h5><p class="glass-panel-sub">Only the latest post-recommendation version may receive the formal CRAD evaluation.</p><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><?php if (!$rows): ?><p class="text-muted">No official manuscript submissions are waiting for evaluation.</p><?php else: ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Group</th><th>Version</th><th>File</th><th>Status</th><th>Result / Score</th><th>Action</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['group_number']) ?><div class="small text-muted"><?= e((string) $row['research_title']) ?></div></td><td>v<?= (int) $row['version_number'] ?></td><td><a href="<?= BASE_URL ?>/modules/crad/api/final-manuscript-document.php?id=<?= (int) $row['id'] ?>" target="_blank"><?= e((string) $row['original_name']) ?></a></td><td><?= e((string) $row['status']) ?></td><td><?= e((string) ($row['evaluation_result'] ?? 'Pending')) ?><?php if (isset($row['overall_score'])): ?><div class="small text-muted"><?= e(number_format((float) $row['overall_score'], 2)) ?>%</div><?php endif; ?></td><td><?php if (in_array((string) $row['status'], ['Submitted', 'Under Review'], true)): ?><details><summary class="btn btn-sm btn-primary">Evaluate</summary><form method="post" class="mt-3" style="min-width:320px"><?= csrfField() ?><input type="hidden" name="submission_id" value="<?= (int) $row['id'] ?>"><?php foreach (['content','methodology','results','conclusions','recommendations','references','formatting','compliance'] as $key): ?><input class="form-control form-control-sm mb-2" type="number" name="<?= $key ?>_score" min="0" max="100" step="0.01" placeholder="<?= ucfirst($key) ?> score" required><?php endforeach; ?><textarea class="form-control form-control-sm mb-2" name="remarks" placeholder="Evaluation remarks"></textarea><button class="btn btn-success btn-sm" name="review_action" value="approve">Approve</button> <button class="btn btn-warning btn-sm" name="review_action" value="revision">Request Revision</button></form></details><?php elseif ($row['status'] === 'For Revision'): ?>Waiting for new version<?php else: ?>Approved for scheduling<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div></div></div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
