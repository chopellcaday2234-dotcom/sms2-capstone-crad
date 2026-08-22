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
if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'research_coordinator', 'admin', 'superadmin'], true)) {
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
$actorName = function_exists('getCurrentUserName') ? getCurrentUserName() : (string) ($_SESSION['user_name'] ?? 'CRAD User');
$activeTerm = cradGetActiveAcademicTerm($crad, '2nd Semester');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please refresh and try again.';
    } elseif (!$activeTerm) {
        $error = 'There is no active 2nd Semester term. Ask a CRAD administrator to create and activate one.';
    } else {
        try {
            $action = (string) ($_POST['action'] ?? '');
            $groupId = (int) ($_POST['research_group_id'] ?? 0);
            if ($groupId <= 0) {
                throw new InvalidArgumentException('Select a valid research group.');
            }

            if ($action === 'enroll') {
                if ((string) ($_POST['confirm_transition'] ?? '') !== '1') {
                    throw new InvalidArgumentException('Confirm that the group is ready for second-semester carry-over.');
                }
                $groupTermId = cradEnrollGroupInTerm(
                    $crad,
                    $groupId,
                    (int) $activeTerm['id'],
                    (string) ($_POST['enrollment_type'] ?? 'Carry-over'),
                    'Second Semester Intake',
                    $actorUserId,
                    $actorName,
                    trim((string) ($_POST['remarks'] ?? '')) ?: null
                );
                logActivity('update', 'Enrolled research group #' . $groupId . ' in second-semester term #' . $activeTerm['id'], 'crad');
                $message = 'Group carried over successfully. It is now at Second Semester Intake.';
            } elseif ($action === 'start_final_documentation') {
                $groupTermId = (int) ($_POST['group_term_id'] ?? 0);
                cradTransitionGroupPhase(
                    $crad,
                    $groupTermId,
                    'Final Documentation',
                    $actorUserId,
                    $actorName,
                    trim((string) ($_POST['remarks'] ?? '')) ?: 'Second-semester intake confirmed.'
                );
                logActivity('update', 'Started final documentation for research group #' . $groupId, 'crad');
                $message = 'Final Documentation started for the selected group.';
            } else {
                throw new InvalidArgumentException('Unknown semester-transition action.');
            }
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$groups = [];
if ($activeTerm) {
    $groupStatement = $crad->prepare(
        'SELECT rg.id, rg.group_number, rg.group_name, rg.research_title, rg.adviser, rg.academic_year, rg.status, '
        . 'rgt.id AS group_term_id, rgt.enrollment_type, rgt.current_phase, rgt.status AS term_status, '
        . '(SELECT COUNT(*) FROM research_workflow_events rwe '
        . ' WHERE rwe.research_group_id = rg.id AND rwe.academic_term_id = ?) AS event_count '
        . 'FROM research_groups rg '
        . 'LEFT JOIN research_group_terms rgt ON rgt.research_group_id = rg.id AND rgt.academic_term_id = ? '
        . "WHERE rg.status IN ('Approved','Active','Ongoing','Completed') "
        . 'ORDER BY (rgt.id IS NULL) DESC, rg.group_number ASC'
    );
    $groupStatement->execute([(int) $activeTerm['id'], (int) $activeTerm['id']]);
    $groups = $groupStatement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($groups as &$group) {
        $group['first_semester_completion'] = cradGetFirstSemesterCompletionStatus($crad, (int) $group['id']);
    }
    unset($group);
}

$pageTitle = 'Second Semester Transition';
$activeModule = 'crad';
$activePage = 'semester-transition';
$breadcrumbs = [
    ['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'],
    ['label' => 'Second Semester Transition', 'url' => null],
];

require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard">
    <div class="glass-board">
        <?php if ($message !== ''): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <div class="glass-panel mb-4">
            <div class="glass-panel-body">
                <h5 class="glass-panel-title">Second-Semester Intake</h5>
                <?php if ($activeTerm): ?>
                    <p class="mb-1"><strong>Active Term:</strong> <?= e((string) $activeTerm['term_code']) ?> &mdash; <?= e((string) $activeTerm['academic_year']) ?>, <?= e((string) $activeTerm['semester']) ?></p>
                    <p class="text-muted mb-0">Carry over only groups confirmed for continuation. Every change is recorded in the workflow history.</p>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">No active 2nd Semester term. A CRAD administrator must create and activate the term before groups can be carried over.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($activeTerm): ?>
            <div class="glass-panel">
                <div class="glass-panel-body">
                    <h5 class="glass-panel-title">Research Groups</h5>
                    <?php if ($groups === []): ?>
                        <p class="text-muted">No eligible research groups found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Group</th><th>Research</th><th>Semester Status</th><th>Workflow</th><th>Action</th></tr></thead>
                                <tbody>
                                <?php foreach ($groups as $group): ?>
                                    <tr>
                                        <td><strong><?= e((string) $group['group_number']) ?></strong><div class="small text-muted"><?= e((string) $group['group_name']) ?></div></td>
                                        <td><?= e((string) $group['research_title']) ?><div class="small text-muted">Adviser: <?= e((string) ($group['adviser'] ?: 'Not assigned')) ?></div></td>
                                        <td>
                                            <?php if ($group['group_term_id']): ?>
                                                <span class="badge text-bg-success">Enrolled</span>
                                                <div class="small text-muted"><?= e((string) $group['enrollment_type']) ?></div>
                                            <?php else: ?><span class="badge text-bg-secondary">Not carried over</span><?php endif; ?>
                                        </td>
                                        <td>
                                            <?= e((string) ($group['current_phase'] ?? 'Research Development')) ?>
                                            <?php if ($group['group_term_id']): ?><div class="small text-muted"><?= (int) $group['event_count'] ?> logged event(s)</div><?php endif; ?>
                                        </td>
                                        <td style="min-width:280px">
                                            <?php if (!$group['group_term_id'] && !empty($group['first_semester_completion']['complete'])): ?>
                                                <details>
                                                    <summary class="btn btn-sm btn-primary">Carry Over</summary>
                                                    <form method="post" class="mt-3">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="enroll">
                                                        <input type="hidden" name="research_group_id" value="<?= (int) $group['id'] ?>">
                                                        <select class="form-select form-select-sm mb-2" name="enrollment_type">
                                                            <option selected>Carry-over</option><option>Continuing</option><option>Repeat</option>
                                                        </select>
                                                        <textarea class="form-control form-control-sm mb-2" name="remarks" placeholder="Transition notes"></textarea>
                                                        <label class="form-check small mb-2"><input class="form-check-input" type="checkbox" name="confirm_transition" value="1" required> <span class="form-check-label">I confirm this group is ready for second-semester intake.</span></label>
                                                        <button class="btn btn-success btn-sm">Confirm Carry-over</button>
                                                    </form>
                                                </details>
                                            <?php elseif (!$group['group_term_id']): ?>
                                                <span class="badge text-bg-warning">First semester incomplete</span>
                                                <div class="small text-muted mt-1"><?= e((string) ($group['first_semester_completion']['reason'] ?? 'Completion requirements are pending.')) ?></div>
                                            <?php elseif ($group['current_phase'] === 'Second Semester Intake'): ?>
                                                <form method="post" onsubmit="return confirm('Start Final Documentation for this group?');">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="start_final_documentation">
                                                    <input type="hidden" name="research_group_id" value="<?= (int) $group['id'] ?>">
                                                    <input type="hidden" name="group_term_id" value="<?= (int) $group['group_term_id'] ?>">
                                                    <button class="btn btn-sm btn-success">Start Final Documentation</button>
                                                </form>
                                            <?php else: ?><span class="text-muted">Transition complete</span><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
