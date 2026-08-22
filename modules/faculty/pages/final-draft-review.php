<?php
declare(strict_types=1);

$pageTitle = 'Final Draft Adviser Review';
$activeModule = 'faculty';
$activePage = 'final-draft-review';
$hideModulePageBanner = true;

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/audit.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/research-progress-helpers.php';
require_once ROOT_PATH . '/modules/crad/includes/final-readiness-helpers.php';

requireAuth();
$crad = cradDb();
if (!$crad instanceof PDO) {
    throw new RuntimeException('CRAD database is unavailable.');
}
frEnsureSchema($crad);

$adviserUserId = (int) ($_SESSION['user_id'] ?? 0);
$adviserEmail = rpCurrentUserEmail();
$adviserName = trim(function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? 'Adviser'));
$groupContext = rpResolveAdviserResearchGroupContext(
    $crad,
    $adviserUserId,
    $adviserEmail,
    $_GET['group'] ?? null
);

$message = '';
$error = '';
$researchGroup = $groupContext['status'] === 'ok' ? ($groupContext['group'] ?? null) : null;
$groupId = (int) ($researchGroup['id'] ?? 0);
$groupNumber = (string) ($researchGroup['group_number'] ?? '');

if ($groupId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        try {
            $criteria = [
                'chapter_1' => isset($_POST['chapter_1']),
                'chapter_2' => isset($_POST['chapter_2']),
                'chapter_3' => isset($_POST['chapter_3']),
                'chapter_4' => isset($_POST['chapter_4']),
                'chapter_5' => isset($_POST['chapter_5']),
                'formatting' => isset($_POST['formatting']),
                'citations' => isset($_POST['citations']),
            ];
            $reviewId = frSaveAdviserReview(
                $crad,
                (int) ($_POST['submission_id'] ?? 0),
                $adviserUserId,
                $adviserEmail,
                $adviserName,
                (string) ($_POST['decision'] ?? ''),
                $criteria,
                trim((string) ($_POST['remarks'] ?? ''))
            );
            logActivity('update', 'Completed final-draft adviser review #' . $reviewId . ' for group #' . $groupId, 'crad');
            $message = (string) ($_POST['decision'] ?? '') === 'Endorsed'
                ? 'Final draft endorsed. The group is now ready for CRAD compliance verification.'
                : 'Final draft returned for revision. The group may submit a new version.';
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$groupTerm = $groupId > 0 ? frGetActiveGroupTerm($crad, $groupId) : null;
$termId = (int) ($groupTerm['academic_term_id'] ?? 0);
$latestDraft = $groupId > 0 && $termId > 0 ? frGetLatestFinalDraft($crad, $groupId, $termId) : null;
$latestReview = $latestDraft ? frGetCurrentFinalDraftReview($crad, (int) $latestDraft['id']) : null;
$history = $groupId > 0 && $termId > 0 ? frGetFinalDraftHistory($crad, $groupId, $termId) : [];

$breadcrumbs = [
    ['label' => 'Faculty', 'url' => BASE_URL . '/modules/faculty/index.php'],
    ['label' => 'My Research Groups', 'url' => BASE_URL . '/modules/faculty/pages/my-research-groups.php'],
    ['label' => 'Final Draft Adviser Review', 'url' => null],
];

require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<?php if ($groupContext['status'] === 'no_groups'): ?>
    <?php rpRenderAdviserNoGroupsState(); ?>
<?php elseif ($groupContext['status'] === 'needs_selection'): ?>
    <?php rpRenderAdviserGroupSelector($groupContext['groups'], 'Select Research Group', 'Choose the assigned group whose consolidated final draft you want to review.'); ?>
<?php elseif (!$researchGroup): ?>
    <?php rpRenderAdviserGroupAccessDenied(); ?>
<?php else: ?>
<div class="glass-dashboard">
    <div class="glass-board">
        <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <div class="glass-panel mb-4">
            <div class="glass-panel-body">
                <h5 class="glass-panel-title">Final Draft Adviser Review</h5>
                <p class="mb-1"><strong><?= e($groupNumber) ?></strong> &mdash; <?= e((string) $researchGroup['research_title']) ?></p>
                <p class="text-muted mb-0">Term: <?= e((string) ($groupTerm['term_code'] ?? 'No active 2nd Semester')) ?> · Phase: <?= e((string) ($groupTerm['current_phase'] ?? 'Not enrolled')) ?></p>
            </div>
        </div>

        <?php if (!$latestDraft): ?>
            <div class="alert alert-info">No consolidated Chapters 1–5 final draft has been submitted for the active term.</div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="glass-panel h-100"><div class="glass-panel-body">
                        <h5 class="glass-panel-title">Latest Version: v<?= (int) $latestDraft['version_number'] ?></h5>
                        <p><a class="btn btn-outline-primary btn-sm" target="_blank" href="<?= BASE_URL ?>/modules/crad/api/final-draft-document.php?id=<?= (int) $latestDraft['id'] ?>"><i class="fas fa-file-download me-1"></i><?= e((string) $latestDraft['original_name']) ?></a></p>
                        <dl class="row small mb-0">
                            <dt class="col-5">Status</dt><dd class="col-7"><?= e((string) $latestDraft['status']) ?></dd>
                            <dt class="col-5">Submitted by</dt><dd class="col-7"><?= e((string) $latestDraft['submitted_by_name']) ?></dd>
                            <dt class="col-5">Submitted</dt><dd class="col-7"><?= e((string) $latestDraft['submitted_at']) ?></dd>
                            <dt class="col-5">File checksum</dt><dd class="col-7 text-break"><code><?= e((string) $latestDraft['file_checksum']) ?></code></dd>
                        </dl>
                        <?php if (trim((string) ($latestDraft['submission_notes'] ?? '')) !== ''): ?><hr><p class="small mb-0"><?= nl2br(e((string) $latestDraft['submission_notes'])) ?></p><?php endif; ?>
                    </div></div>
                </div>
                <div class="col-lg-7">
                    <div class="glass-panel"><div class="glass-panel-body">
                        <h5 class="glass-panel-title">Formal Adviser Checklist</h5>
                        <?php if (in_array((string) $latestDraft['status'], ['Submitted', 'Under Adviser Review'], true)): ?>
                            <form method="post">
                                <?= csrfField() ?>
                                <input type="hidden" name="submission_id" value="<?= (int) $latestDraft['id'] ?>">
                                <p class="small text-muted">Check every accepted component. Endorsement is blocked until all seven criteria are checked.</p>
                                <div class="row g-2 mb-3">
                                    <?php foreach ([1, 2, 3, 4, 5] as $chapter): ?>
                                        <div class="col-sm-6"><label class="form-check border rounded p-2 ps-5"><input class="form-check-input" type="checkbox" name="chapter_<?= $chapter ?>"> <span class="form-check-label">Chapter <?= $chapter ?> complete and acceptable</span></label></div>
                                    <?php endforeach; ?>
                                    <div class="col-sm-6"><label class="form-check border rounded p-2 ps-5"><input class="form-check-input" type="checkbox" name="formatting"> <span class="form-check-label">Formatting accepted</span></label></div>
                                    <div class="col-sm-6"><label class="form-check border rounded p-2 ps-5"><input class="form-check-input" type="checkbox" name="citations"> <span class="form-check-label">Citations and references accepted</span></label></div>
                                </div>
                                <label class="form-label">Review Remarks</label>
                                <textarea class="form-control mb-3" name="remarks" rows="4" placeholder="Required when requesting revision; optional for endorsement."></textarea>
                                <button class="btn btn-warning" name="decision" value="Revision Requested"><i class="fas fa-undo me-1"></i>Request Revision</button>
                                <button class="btn btn-success" name="decision" value="Endorsed"><i class="fas fa-check-double me-1"></i>Endorse Final Draft</button>
                            </form>
                        <?php elseif ($latestReview): ?>
                            <div class="alert alert-<?= $latestReview['decision'] === 'Endorsed' ? 'success' : 'warning' ?>">
                                <strong><?= e((string) $latestReview['decision']) ?></strong><br>
                                <span class="small"><?= nl2br(e((string) ($latestReview['remarks'] ?? ''))) ?></span>
                            </div>
                            <p class="text-muted small mb-0">This decision is recorded. A revision request requires the student to upload a new immutable version.</p>
                        <?php else: ?>
                            <div class="alert alert-secondary">This version is not open for adviser review.</div>
                        <?php endif; ?>
                    </div></div>
                </div>
            </div>

            <?php if ($history !== []): ?>
                <div class="glass-panel mt-4"><div class="glass-panel-body">
                    <h5 class="glass-panel-title">Version and Review History</h5>
                    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Version</th><th>File</th><th>Status</th><th>Decision</th><th>Submitted</th></tr></thead><tbody>
                    <?php foreach ($history as $row): ?><tr>
                        <td>v<?= (int) $row['version_number'] ?></td>
                        <td><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/final-draft-document.php?id=<?= (int) $row['id'] ?>"><?= e((string) $row['original_name']) ?></a></td>
                        <td><?= e((string) $row['status']) ?></td><td><?= e((string) ($row['review_decision'] ?? 'Pending')) ?></td><td><?= e((string) $row['submitted_at']) ?></td>
                    </tr><?php endforeach; ?>
                    </tbody></table></div>
                </div></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
