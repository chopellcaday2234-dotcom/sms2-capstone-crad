<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/uploads.php';
require_once ROOT_PATH . '/includes/audit.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/research-progress-helpers.php';
require_once ROOT_PATH . '/modules/crad/includes/final-phase-helpers.php';

requireAuth();
if (getCurrentUserRoleKey() !== 'student') {
    http_response_code(403);
    exit('Forbidden');
}

$crad = cradDb();
if (!$crad instanceof PDO) {
    throw new RuntimeException('CRAD database is unavailable.');
}
finalPhaseEnsureSchema($crad);

$group = rpGetRegisteredResearchGroup(
    $crad,
    trim((string) ($_SESSION['student_id'] ?? '')),
    (int) ($_SESSION['user_id'] ?? 0)
);
$message = '';
$error = '';
if (!$group) {
    $error = 'Your research group is not officially registered yet.';
}

$groupId = (int) ($group['id'] ?? 0);
if ($groupId > 0) {
    try {
        fpRepairLegacyRecommendedManuscriptPhase($crad, $groupId);
    } catch (Throwable $exception) {
        error_log('Phase 3 manuscript workflow repair failed: ' . $exception->getMessage());
    }
}

$groupTerm = $groupId > 0 ? frGetActiveGroupTerm($crad, $groupId) : null;
$termId = (int) ($groupTerm['academic_term_id'] ?? 0);
$latestDraft = $groupId > 0 && $termId > 0 ? frGetLatestFinalDraft($crad, $groupId, $termId) : null;
$latestOfficial = $groupId > 0 ? fpGetLatestManuscriptSubmission($crad, $groupId, 'Final Defense') : null;
$revisionCycle = null;
if ($groupId > 0 && fpGroupNeedsFinalRevision($crad, $groupId)) {
    $revisionCycle = fpGetRevisionCycle($crad, $groupId);
}

if ($groupId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionStage = (string) ($_POST['submission_stage'] ?? '');
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } elseif (!$groupTerm) {
        $error = 'Your group must be carried over into the active 2nd Semester first.';
    } elseif ($submissionStage === 'final_draft') {
        if (!in_array((string) $groupTerm['current_phase'], ['Final Documentation', 'Final Draft Adviser Review'], true)) {
            $error = 'Final-draft submission is not available at the current workflow phase.';
        } elseif ($latestDraft && !in_array((string) $latestDraft['status'], ['Revision Requested', 'Superseded'], true)) {
            $error = 'The latest final draft is still under review or already endorsed.';
        } elseif (!isset($_FILES['manuscript_file'])) {
            $error = 'Please select the consolidated Chapters 1–5 draft.';
        } else {
            $uploadSubdir = 'final-drafts/g' . $groupId . '/t' . $termId;
            $allowedTypes = array_intersect_key(smsUploadAllowedDocuments(), array_flip(['pdf', 'doc', 'docx']));
            $upload = smsSecureUpload($_FILES['manuscript_file'], [
                'subdir' => $uploadSubdir,
                'max_bytes' => 20 * 1024 * 1024,
                'allowed' => $allowedTypes,
                'required' => true,
            ]);
            if (empty($upload['ok'])) {
                $error = (string) ($upload['error'] ?? 'Upload failed.');
            } else {
                try {
                    $checksum = hash_file('sha256', (string) $upload['path']);
                    if ($checksum === false) {
                        throw new RuntimeException('Unable to verify the uploaded file checksum.');
                    }
                    $submissionId = frSubmitFinalDraft(
                        $crad,
                        $groupId,
                        (int) ($_SESSION['user_id'] ?? 0),
                        function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? ''),
                        [
                            'original_name' => (string) ($upload['original_name'] ?? ''),
                            'stored_subdir' => $uploadSubdir,
                            'stored_name' => (string) ($upload['stored_name'] ?? basename((string) ($upload['path'] ?? ''))),
                            'file_size' => (int) ($upload['size'] ?? 0),
                            'file_mime' => (string) ($upload['mime'] ?? ''),
                            'file_checksum' => $checksum,
                        ],
                        trim((string) ($_POST['submission_notes'] ?? ''))
                    );
                    logActivity('create', 'Submitted consolidated final draft #' . $submissionId . ' for research group #' . $groupId, 'crad');
                    $message = 'Consolidated final draft submitted for formal adviser review.';
                } catch (Throwable $exception) {
                    $storedPath = (string) ($upload['path'] ?? '');
                    if ($storedPath !== '' && is_file($storedPath)) {
                        @unlink($storedPath);
                    }
                    $error = $exception->getMessage();
                }
            }
        }
    } elseif ($submissionStage === 'official_manuscript') {
        if (!fpIsRecommendedForFinalDefense($crad, $groupId)) {
            $error = 'Final Defense Recommendation is required before the official Chapters 1–5 submission.';
        } elseif ((string) $groupTerm['current_phase'] !== 'Final Manuscript Submission') {
            $error = 'Official manuscript submission is not available at the current workflow phase.';
        } elseif ($latestOfficial && (string) $latestOfficial['status'] !== 'For Revision') {
            $error = 'The latest official manuscript is still under review or already approved.';
        } elseif (!isset($_FILES['manuscript_file'])) {
            $error = 'Please select the official Chapters 1–5 manuscript.';
        } else {
            $uploadSubdir = 'manuscripts/g' . $groupId . '/t' . $termId;
            $allowedTypes = array_intersect_key(smsUploadAllowedDocuments(), array_flip(['pdf', 'doc', 'docx']));
            $upload = smsSecureUpload($_FILES['manuscript_file'], [
                'subdir' => $uploadSubdir,
                'max_bytes' => 20 * 1024 * 1024,
                'allowed' => $allowedTypes,
                'required' => true,
            ]);
            if (empty($upload['ok'])) {
                $error = (string) ($upload['error'] ?? 'Upload failed.');
            } else {
                try {
                    $checksum = hash_file('sha256', (string) $upload['path']);
                    if ($checksum === false) {
                        throw new RuntimeException('Unable to verify the uploaded file checksum.');
                    }
                    $submissionId = fpSubmitFinalManuscript(
                        $crad,
                        $groupId,
                        (int) ($_SESSION['user_id'] ?? 0),
                        function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? ''),
                        (string) ($_SESSION['user_email'] ?? ''),
                        [
                            'original_name' => (string) ($upload['original_name'] ?? ''),
                            'stored_subdir' => $uploadSubdir,
                            'stored_name' => (string) ($upload['stored_name'] ?? basename((string) ($upload['path'] ?? ''))),
                            'file_size' => (int) ($upload['size'] ?? 0),
                            'file_mime' => (string) ($upload['mime'] ?? ''),
                            'file_checksum' => $checksum,
                        ],
                        trim((string) ($_POST['submission_notes'] ?? ''))
                    );
                    logActivity('create', 'Submitted official final manuscript #' . $submissionId . ' for research group #' . $groupId, 'crad');
                    $message = 'Official Chapters 1–5 manuscript submitted for CRAD evaluation.';
                } catch (Throwable $exception) {
                    $storedPath = (string) ($upload['path'] ?? '');
                    if ($storedPath !== '' && is_file($storedPath)) {
                        @unlink($storedPath);
                    }
                    $error = $exception->getMessage();
                }
            }
        }
    } elseif ($submissionStage === 'defense_revision') {
        if ((string) ($groupTerm['current_phase'] ?? '') !== 'Post-Defense Revision') {
            $error = 'Final Defense revision evidence is not available at the current workflow phase.';
        } elseif (!$revisionCycle) {
            $error = 'No active Final Defense revision cycle was found.';
        } elseif (!isset($_FILES['manuscript_file'])) {
            $error = 'Please select the revised manuscript or compliance evidence.';
        } else {
            $attemptId = (int) ($revisionCycle['defense_attempt_id'] ?? 0);
            $uploadSubdir = 'defense-revisions/g' . $groupId . '/a' . $attemptId;
            $allowedTypes = array_intersect_key(smsUploadAllowedDocuments(), array_flip(['pdf', 'doc', 'docx']));
            $upload = smsSecureUpload($_FILES['manuscript_file'], [
                'subdir' => $uploadSubdir,
                'max_bytes' => 20 * 1024 * 1024,
                'allowed' => $allowedTypes,
                'required' => true,
            ]);
            if (empty($upload['ok'])) {
                $error = (string) ($upload['error'] ?? 'Upload failed.');
            } else {
                try {
                    $checksum = hash_file('sha256', (string) $upload['path']);
                    if ($checksum === false) {
                        throw new RuntimeException('Unable to verify the uploaded file checksum.');
                    }
                    $submissionId = fpSubmitDefenseRevision(
                        $crad,
                        $groupId,
                        (int) ($_SESSION['user_id'] ?? 0),
                        function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? ''),
                        [
                            'original_name' => (string) ($upload['original_name'] ?? ''),
                            'stored_subdir' => $uploadSubdir,
                            'stored_name' => (string) ($upload['stored_name'] ?? basename((string) ($upload['path'] ?? ''))),
                            'file_size' => (int) ($upload['size'] ?? 0),
                            'file_mime' => (string) ($upload['mime'] ?? ''),
                            'file_checksum' => $checksum,
                        ],
                        trim((string) ($_POST['submission_notes'] ?? ''))
                    );
                    logActivity('create', 'Submitted Final Defense revision evidence #' . $submissionId . ' for research group #' . $groupId, 'crad');
                    $message = 'Final Defense revision evidence submitted for compliance review.';
                } catch (Throwable $exception) {
                    $storedPath = (string) ($upload['path'] ?? '');
                    if ($storedPath !== '' && is_file($storedPath)) {
                        @unlink($storedPath);
                    }
                    $error = $exception->getMessage();
                }
            }
        }
    } else {
        $error = 'Invalid manuscript submission stage.';
    }
}

$groupTerm = $groupId > 0 ? frGetActiveGroupTerm($crad, $groupId) : null;
$termId = (int) ($groupTerm['academic_term_id'] ?? 0);
$latestDraft = $groupId > 0 && $termId > 0 ? frGetLatestFinalDraft($crad, $groupId, $termId) : null;
$draftSubmissions = $groupId > 0 && $termId > 0 ? frGetFinalDraftHistory($crad, $groupId, $termId) : [];
$readinessSnapshot = $groupId > 0 ? frGetReadinessSnapshot($crad, $groupId) : null;
$recommendation = $readinessSnapshot['recommendation'] ?? null;
$isRecommended = (string) ($recommendation['status'] ?? '') === 'Recommended';
$latestOfficial = $groupId > 0 ? fpGetLatestManuscriptSubmission($crad, $groupId, 'Final Defense') : null;
$officialSubmissions = $groupId > 0 ? fpGetManuscriptSubmissionHistory($crad, $groupId, 'Final Defense') : [];
$revisionCycle = $groupId > 0 ? fpGetRevisionCycle($crad, $groupId) : null;
$revisionSubmissions = $groupId > 0 ? fpGetDefenseRevisionSubmissionHistory($crad, $groupId) : [];
$latestRevisionSubmission = $revisionSubmissions[0] ?? null;

$canUploadDraft = $groupTerm
    && in_array((string) $groupTerm['current_phase'], ['Final Documentation', 'Final Draft Adviser Review'], true)
    && (!$latestDraft || in_array((string) $latestDraft['status'], ['Revision Requested', 'Superseded'], true));
$canUploadOfficial = $groupTerm
    && $isRecommended
    && (string) $groupTerm['current_phase'] === 'Final Manuscript Submission'
    && (!$latestOfficial || (string) $latestOfficial['status'] === 'For Revision');
$canUploadRevision = $groupTerm
    && (string) $groupTerm['current_phase'] === 'Post-Defense Revision'
    && $revisionCycle
    && (!$latestRevisionSubmission || in_array((string) $latestRevisionSubmission['status'], ['For Resubmission', 'Superseded'], true));

$breadcrumbs = [
    ['label' => 'Student Portal', 'url' => BASE_URL . '/modules/student-portal/pages/dashboard.php'],
    ['label' => 'Final Documentation', 'url' => null],
];
require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard">
    <div class="glass-board">
        <div class="glass-panel mb-4"><div class="glass-panel-body">
            <h5 class="glass-panel-title">Final Documentation — Chapters 1–5</h5>
            <p class="glass-panel-sub">Final Draft → Adviser Review → CRAD Readiness → Final Defense Recommendation → Official Manuscript → CRAD Evaluation</p>
            <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($groupId > 0): ?>
                <div class="row g-3 small">
                    <div class="col-md-4"><strong>Group</strong><div><?= e((string) ($group['group_number'] ?? '')) ?></div></div>
                    <div class="col-md-4"><strong>Active Term</strong><div><?= e((string) ($groupTerm['term_code'] ?? 'Not enrolled')) ?></div></div>
                    <div class="col-md-4"><strong>Current Stage</strong><div><?= e((string) ($groupTerm['current_phase'] ?? 'Not enrolled')) ?></div></div>
                </div>
            <?php endif; ?>
        </div></div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-panel h-100"><div class="glass-panel-body">
                    <h5 class="glass-panel-title">1. Pre-Recommendation Final Draft</h5>
                    <p class="small text-muted">This working draft is reviewed and endorsed by the adviser before CRAD readiness verification.</p>
                    <?php if ($canUploadDraft): ?>
                        <form method="post" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <input type="hidden" name="submission_stage" value="final_draft">
                            <div class="mb-3"><label class="form-label">Consolidated Chapters 1–5 Draft</label><input class="form-control" type="file" name="manuscript_file" accept=".pdf,.doc,.docx" required><div class="form-text">PDF, DOC, or DOCX; maximum 20 MB.</div></div>
                            <div class="mb-3"><label class="form-label">Submission Notes</label><textarea class="form-control" name="submission_notes" rows="3" placeholder="Summarize the draft or revision changes."></textarea></div>
                            <button class="btn btn-primary" type="submit"><i class="fas fa-upload me-1"></i>Submit for Adviser Review</button>
                        </form>
                    <?php elseif (!$groupTerm): ?>
                        <div class="alert alert-warning">Your group is not enrolled in the active 2nd Semester.</div>
                    <?php elseif ($latestDraft): ?>
                        <div class="alert alert-info mb-0">Latest final draft: <strong><?= e((string) $latestDraft['status']) ?></strong>.</div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">This step is closed at the current workflow stage.</div>
                    <?php endif; ?>
                </div></div>
            </div>

            <div class="col-lg-7">
                <div class="glass-panel h-100"><div class="glass-panel-body">
                    <h5 class="glass-panel-title">Readiness and Recommendation</h5>
                    <?php if (!$readinessSnapshot || !$readinessSnapshot['final_draft']): ?>
                        <p class="text-muted">Submit the consolidated final draft to begin the readiness process.</p>
                    <?php else: ?>
                        <?php $requirements = $readinessSnapshot['requirements']; ?>
                        <div class="row g-2">
                            <?php foreach ([1, 2, 3, 4, 5] as $chapter): $done = !empty($requirements['chapter_' . $chapter]); ?>
                                <div class="col-sm-6"><div class="border rounded p-2"><i class="fas fa-<?= $done ? 'check-circle text-success' : 'clock text-warning' ?> me-1"></i>Chapter <?= $chapter ?> adviser acceptance</div></div>
                            <?php endforeach; ?>
                            <?php foreach (['adviser_endorsed' => 'Formal adviser endorsement', 'ethics_clearance' => 'Ethics clearance', 'similarity_check' => 'Similarity check', 'required_documents' => 'Required documents'] as $key => $label): $done = !empty($requirements[$key]); ?>
                                <div class="col-sm-6"><div class="border rounded p-2"><i class="fas fa-<?= $done ? 'check-circle text-success' : 'clock text-warning' ?> me-1"></i><?= e($label) ?></div></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-<?= $isRecommended ? 'success' : ($readinessSnapshot['ready'] ? 'info' : 'secondary') ?> mt-3 mb-0">
                            <?php if ($isRecommended): ?>
                                <strong>Final Defense Recommended.</strong> Official manuscript submission is now unlocked.
                            <?php elseif ($readinessSnapshot['ready']): ?>
                                All readiness requirements are complete. Waiting for the formal adviser recommendation.
                            <?php else: ?>
                                Readiness verification is still in progress.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div></div>
            </div>
        </div>

        <div class="glass-panel mt-4"><div class="glass-panel-body">
            <h5 class="glass-panel-title">2. Official Chapters 1–5 Manuscript</h5>
            <p class="small text-muted">This is the official submission for CRAD evaluation after the adviser’s Final Defense Recommendation.</p>
            <?php if ($canUploadOfficial): ?>
                <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                    <?= csrfField() ?>
                    <input type="hidden" name="submission_stage" value="official_manuscript">
                    <div class="col-lg-5"><label class="form-label">Official Chapters 1–5 File</label><input class="form-control" type="file" name="manuscript_file" accept=".pdf,.doc,.docx" required><div class="form-text">PDF, DOC, or DOCX; maximum 20 MB.</div></div>
                    <div class="col-lg-5"><label class="form-label">Submission / Revision Notes</label><textarea class="form-control" name="submission_notes" rows="2" placeholder="Summarize this official version."></textarea></div>
                    <div class="col-lg-2"><button class="btn btn-success w-100" type="submit"><i class="fas fa-file-circle-check me-1"></i>Submit Official</button></div>
                </form>
            <?php elseif (!$isRecommended): ?>
                <div class="alert alert-warning mb-0"><strong>Locked:</strong> Final Defense Recommendation is required first.</div>
            <?php elseif (($latestOfficial['status'] ?? '') === 'Approved'): ?>
                <div class="alert alert-success mb-0">The latest official manuscript is approved. The group may proceed to the Final Defense scheduling stage.</div>
            <?php elseif (in_array((string) ($latestOfficial['status'] ?? ''), ['Submitted', 'Under Review'], true)): ?>
                <div class="alert alert-info mb-0">Official manuscript v<?= (int) ($latestOfficial['version_number'] ?? 0) ?> is under CRAD evaluation.</div>
            <?php else: ?>
                <div class="alert alert-secondary mb-0">Official submission is unavailable at the current workflow stage.</div>
            <?php endif; ?>
        </div></div>

        <?php if ($revisionCycle): ?>
            <div class="glass-panel mt-4"><div class="glass-panel-body">
                <h5 class="glass-panel-title">3. Final Defense Revision Evidence</h5>
                <p class="small text-muted">Submit the revised manuscript and explain how the Panel remarks were addressed. This is separate from ordinary progress updates.</p>
                <?php if ($canUploadRevision): ?>
                    <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                        <?= csrfField() ?>
                        <input type="hidden" name="submission_stage" value="defense_revision">
                        <div class="col-lg-5"><label class="form-label">Revised Manuscript / Evidence</label><input class="form-control" type="file" name="manuscript_file" accept=".pdf,.doc,.docx" required><div class="form-text">PDF, DOC, or DOCX; maximum 20 MB.</div></div>
                        <div class="col-lg-5"><label class="form-label">Response to Panel Remarks</label><textarea class="form-control" name="submission_notes" rows="2" required placeholder="Summarize the revisions and addressed remarks."></textarea></div>
                        <div class="col-lg-2"><button class="btn btn-primary w-100" type="submit"><i class="fas fa-upload me-1"></i>Submit Revision</button></div>
                    </form>
                <?php elseif ($latestRevisionSubmission): ?>
                    <div class="alert alert-<?= (string) $latestRevisionSubmission['status'] === 'Complied' ? 'success' : 'info' ?> mb-0">
                        Latest revision v<?= (int) $latestRevisionSubmission['version_number'] ?>: <strong><?= e((string) $latestRevisionSubmission['status']) ?></strong>.
                        <?= e((string) ($latestRevisionSubmission['review_remarks'] ?? 'Waiting for compliance review.')) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">Revision submission is unavailable at the current workflow stage.</div>
                <?php endif; ?>

                <?php if ($revisionSubmissions): ?>
                    <div class="table-responsive mt-3"><table class="table align-middle"><thead><tr><th>Version</th><th>File</th><th>Status</th><th>Submitted</th><th>Review Remarks</th></tr></thead><tbody>
                    <?php foreach ($revisionSubmissions as $row): ?><tr>
                        <td>v<?= (int) $row['version_number'] ?></td>
                        <td><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/defense-revision-document.php?id=<?= (int) $row['id'] ?>"><?= e((string) $row['original_name']) ?></a></td>
                        <td><?= e((string) $row['status']) ?></td>
                        <td><?= e((string) $row['submitted_at']) ?></td>
                        <td><?= e((string) (($row['review_remarks'] ?? '') ?: ($row['response_notes'] ?? ''))) ?></td>
                    </tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </div></div>
        <?php endif; ?>

        <?php if ($officialSubmissions): ?>
            <div class="glass-panel mt-4"><div class="glass-panel-body">
                <h5 class="glass-panel-title">Official Manuscript Version History</h5>
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Version</th><th>File</th><th>Status</th><th>Evaluation</th><th>Score</th><th>Submitted</th><th>Remarks</th></tr></thead><tbody>
                <?php foreach ($officialSubmissions as $row): ?><tr>
                    <td>v<?= (int) $row['version_number'] ?></td>
                    <td><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/final-manuscript-document.php?id=<?= (int) $row['id'] ?>"><?= e((string) $row['original_name']) ?></a></td>
                    <td><?= e((string) $row['status']) ?></td>
                    <td><?= e((string) ($row['evaluation_result'] ?? 'Pending')) ?></td>
                    <td><?= isset($row['overall_score']) ? e(number_format((float) $row['overall_score'], 2)) : '—' ?></td>
                    <td><?= e((string) $row['submitted_at']) ?></td>
                    <td><?= e((string) (($row['evaluation_remarks'] ?? '') ?: ($row['submission_notes'] ?? ''))) ?></td>
                </tr><?php endforeach; ?>
                </tbody></table></div>
            </div></div>
        <?php endif; ?>

        <?php if ($draftSubmissions): ?>
            <div class="glass-panel mt-4"><div class="glass-panel-body">
                <h5 class="glass-panel-title">Final Draft Version History</h5>
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Version</th><th>File</th><th>Status</th><th>Adviser Decision</th><th>Submitted</th><th>Notes</th></tr></thead><tbody>
                <?php foreach ($draftSubmissions as $row): ?><tr>
                    <td>v<?= (int) $row['version_number'] ?></td>
                    <td><a target="_blank" href="<?= BASE_URL ?>/modules/crad/api/final-draft-document.php?id=<?= (int) $row['id'] ?>"><?= e((string) $row['original_name']) ?></a></td>
                    <td><?= e((string) $row['status']) ?></td>
                    <td><?= e((string) ($row['review_decision'] ?? 'Pending')) ?></td>
                    <td><?= e((string) $row['submitted_at']) ?></td>
                    <td><?= e((string) ($row['submission_notes'] ?? '')) ?></td>
                </tr><?php endforeach; ?>
                </tbody></table></div>
            </div></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
