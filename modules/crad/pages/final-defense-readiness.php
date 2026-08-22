<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/audit.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/final-readiness-helpers.php';

requireAuth();
if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'research_coordinator', 'admin', 'superadmin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$crad = cradDb();
if (!$crad instanceof PDO) {
    throw new RuntimeException('CRAD database is unavailable.');
}
frEnsureSchema($crad);

$message = '';
$error = '';
$actorUserId = (int) ($_SESSION['user_id'] ?? 0);
$actorName = trim(function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? 'CRAD Reviewer'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        try {
            $groupId = (int) ($_POST['research_group_id'] ?? 0);
            $result = frSaveReadinessCheck(
                $crad,
                $groupId,
                $actorUserId,
                $actorName,
                isset($_POST['ethics_clearance']),
                isset($_POST['similarity_check']),
                isset($_POST['required_documents']),
                trim((string) ($_POST['remarks'] ?? ''))
            );
            logActivity('update', 'Saved Final Defense readiness check #' . $result['id'] . ' for group #' . $groupId, 'crad');
            $message = $result['status'] === 'Ready'
                ? 'Readiness confirmed. The adviser may now submit the formal Final Defense recommendation.'
                : 'Checklist saved as Incomplete. Recommendation remains blocked.';
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$activeTerm = cradGetActiveAcademicTerm($crad, '2nd Semester');
$rows = [];
if ($activeTerm) {
    $statement = $crad->prepare(
        "SELECT rg.id, rg.group_number, rg.group_name, rg.research_title, rg.adviser, "
        . "rgt.id AS group_term_id, rgt.current_phase, "
        . "fds.id AS draft_id, fds.version_number, fds.original_name, fds.file_checksum, fds.status AS draft_status, "
        . "fdr.id AS review_id, fdr.decision AS review_decision, fdr.chapter_1_accepted, fdr.chapter_2_accepted, "
        . "fdr.chapter_3_accepted, fdr.chapter_4_accepted, fdr.chapter_5_accepted, "
        . "fdr.formatting_accepted, fdr.citations_accepted, "
        . "frc.id AS check_id, frc.checklist_version, frc.overall_status, frc.ethics_clearance_complete, "
        . "frc.similarity_check_complete, frc.required_documents_complete, frc.remarks AS readiness_remarks, "
        . "frec.status AS recommendation_status, frec.recommended_at "
        . "FROM research_group_terms rgt "
        . "INNER JOIN research_groups rg ON rg.id = rgt.research_group_id "
        . "LEFT JOIN final_draft_submissions fds ON fds.id = ("
        . " SELECT fds2.id FROM final_draft_submissions fds2 "
        . " WHERE fds2.research_group_id = rg.id AND fds2.academic_term_id = rgt.academic_term_id "
        . " ORDER BY fds2.version_number DESC, fds2.id DESC LIMIT 1) "
        . "LEFT JOIN final_draft_reviews fdr ON fdr.submission_id = fds.id AND fdr.is_current = 1 "
        . "LEFT JOIN final_readiness_checks frc ON frc.research_group_id = rg.id "
        . " AND frc.academic_term_id = rgt.academic_term_id AND frc.is_current = 1 "
        . " AND frc.final_draft_submission_id = fds.id AND frc.adviser_review_id = fdr.id "
        . "LEFT JOIN final_defense_recommendations frec ON frec.research_group_id = rg.id "
        . "WHERE rgt.academic_term_id = ? "
        . "AND rgt.current_phase IN ('Final Defense Readiness','Final Manuscript Submission',"
        . "'Final Manuscript Evaluation','Final Defense Scheduling') "
        . "ORDER BY (frc.overall_status = 'Ready') ASC, rg.group_number ASC"
    );
    $statement->execute([(int) $activeTerm['id']]);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$pageTitle = 'Final Defense Readiness';
$activeModule = 'crad';
$activePage = 'final-defense-readiness';
$breadcrumbs = [
    ['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'],
    ['label' => 'Final Defense Readiness', 'url' => null],
];
require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard"><div class="glass-board">
    <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <div class="glass-panel mb-4"><div class="glass-panel-body">
        <h5 class="glass-panel-title">Final Defense Readiness Verification</h5>
        <?php if ($activeTerm): ?>
            <p class="mb-1"><strong>Active Term:</strong> <?= e((string) $activeTerm['term_code']) ?></p>
            <p class="text-muted mb-0">Chapter completion and adviser endorsement are read-only evidence. CRAD verifies ethics, similarity, and required documents.</p>
        <?php else: ?><div class="alert alert-warning mb-0">No active 2nd Semester term.</div><?php endif; ?>
    </div></div>

    <?php if ($activeTerm && $rows === []): ?>
        <div class="alert alert-info">No adviser-endorsed groups are waiting for readiness verification.</div>
    <?php endif; ?>

    <?php foreach ($rows as $row): ?>
        <?php
        $chaptersAccepted = [];
        foreach ([1, 2, 3, 4, 5] as $chapter) { $chaptersAccepted[$chapter] = !empty($row['chapter_' . $chapter . '_accepted']); }
        $isRecommended = ($row['recommendation_status'] ?? '') === 'Recommended';
        ?>
        <div class="glass-panel mb-4"><div class="glass-panel-body">
            <div class="d-flex justify-content-between gap-3 flex-wrap mb-3">
                <div><h5 class="mb-1"><?= e((string) $row['group_number']) ?> — <?= e((string) $row['research_title']) ?></h5><div class="text-muted small">Adviser: <?= e((string) ($row['adviser'] ?: 'Not assigned')) ?> · Phase: <?= e((string) $row['current_phase']) ?></div></div>
                <div><span class="badge text-bg-<?= $isRecommended ? 'primary' : (($row['overall_status'] ?? '') === 'Ready' ? 'success' : 'warning') ?>"><?= $isRecommended ? 'Recommended' : e((string) ($row['overall_status'] ?? 'Pending Check')) ?></span></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <h6>Adviser Evidence</h6>
                    <p><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/final-draft-document.php?id=<?= (int) $row['draft_id'] ?>"><i class="fas fa-file-alt me-1"></i>Final Draft v<?= (int) $row['version_number'] ?> — <?= e((string) $row['original_name']) ?></a></p>
                    <div class="row g-2 small">
                        <?php foreach ($chaptersAccepted as $chapter => $accepted): ?><div class="col-6"><i class="fas fa-<?= $accepted ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Chapter <?= $chapter ?></div><?php endforeach; ?>
                        <div class="col-6"><i class="fas fa-<?= !empty($row['formatting_accepted']) ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Formatting</div>
                        <div class="col-6"><i class="fas fa-<?= !empty($row['citations_accepted']) ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Citations</div>
                        <div class="col-12"><i class="fas fa-<?= ($row['review_decision'] ?? '') === 'Endorsed' ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Formal adviser endorsement</div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">SHA-256: <code class="text-break"><?= e((string) $row['file_checksum']) ?></code></p>
                </div>
                <div class="col-lg-6">
                    <h6>CRAD Compliance Checklist</h6>
                    <?php if (!$isRecommended && $row['current_phase'] === 'Final Defense Readiness'): ?>
                        <form method="post">
                            <?= csrfField() ?><input type="hidden" name="research_group_id" value="<?= (int) $row['id'] ?>">
                            <label class="form-check border rounded p-2 ps-5 mb-2"><input class="form-check-input" type="checkbox" name="ethics_clearance" <?= !empty($row['ethics_clearance_complete']) ? 'checked' : '' ?>> <span class="form-check-label">Ethics clearance complete</span></label>
                            <label class="form-check border rounded p-2 ps-5 mb-2"><input class="form-check-input" type="checkbox" name="similarity_check" <?= !empty($row['similarity_check_complete']) ? 'checked' : '' ?>> <span class="form-check-label">Similarity/plagiarism check complete</span></label>
                            <label class="form-check border rounded p-2 ps-5 mb-2"><input class="form-check-input" type="checkbox" name="required_documents" <?= !empty($row['required_documents_complete']) ? 'checked' : '' ?>> <span class="form-check-label">All required supporting documents complete</span></label>
                            <textarea class="form-control mb-2" name="remarks" rows="3" placeholder="Verification notes or missing requirements"><?= e((string) ($row['readiness_remarks'] ?? '')) ?></textarea>
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Readiness Check</button>
                        </form>
                    <?php else: ?>
                        <ul class="list-unstyled small mb-2">
                            <li><i class="fas fa-<?= !empty($row['ethics_clearance_complete']) ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Ethics clearance</li>
                            <li><i class="fas fa-<?= !empty($row['similarity_check_complete']) ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Similarity check</li>
                            <li><i class="fas fa-<?= !empty($row['required_documents_complete']) ? 'check-circle text-success' : 'times-circle text-danger' ?> me-1"></i>Required documents</li>
                        </ul>
                        <?php if ($isRecommended): ?><div class="alert alert-success mb-0">Formal recommendation submitted <?= e((string) ($row['recommended_at'] ?? '')) ?>.</div><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div></div>
    <?php endforeach; ?>
</div></div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
