<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/includes/authentication.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/breadcrumbs.php';
require_once ROOT_PATH . '/modules/crad/config/config.php';
require_once ROOT_PATH . '/modules/crad/includes/final-phase-helpers.php';

requireAuth();
if (!in_array(getCurrentUserRoleKey(), ['crad_officer', 'research_coordinator', 'superadmin', 'admin'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$crad = cradDb();
finalPhaseEnsureSchema($crad);

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$accessFilter = trim((string) ($_GET['access'] ?? ''));
$allowedStatuses = ['For Publication', 'Published', 'Archived'];
$allowedAccessLevels = ['Public', 'Campus Only', 'Restricted', 'Embargoed'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}
if (!in_array($accessFilter, $allowedAccessLevels, true)) {
    $accessFilter = '';
}

$metricsStatement = $crad->query(
    "SELECT COUNT(*) AS total, "
    . "SUM(status = 'For Publication') AS for_publication, "
    . "SUM(status = 'Published') AS published, SUM(status = 'Archived') AS archived "
    . "FROM publications WHERE status IN ('For Publication','Published','Archived')"
);
$repositoryMetrics = $metricsStatement->fetch(PDO::FETCH_ASSOC) ?: [
    'total' => 0,
    'for_publication' => 0,
    'published' => 0,
    'archived' => 0,
];

$sql = "SELECT p.*, rg.group_number, rg.group_name, rg.research_title "
    . "FROM publications p LEFT JOIN research_groups rg ON rg.id = p.research_group_id "
    . "WHERE p.status IN ('For Publication','Published','Archived')";
$params = [];
if ($search !== '') {
    $sql .= ' AND (p.title LIKE ? OR p.authors LIKE ? OR p.keywords LIKE ? '
        . 'OR p.repository_identifier LIKE ? OR rg.group_number LIKE ?)';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term, $term, $term];
}
if ($statusFilter !== '') {
    $sql .= ' AND p.status = ?';
    $params[] = $statusFilter;
}
if ($accessFilter !== '') {
    $sql .= ' AND p.access_level = ?';
    $params[] = $accessFilter;
}
$sql .= ' ORDER BY p.updated_at DESC, p.id DESC';
$repositoryStatement = $crad->prepare($sql);
$repositoryStatement->execute($params);
$repositoryRows = $repositoryStatement->fetchAll(PDO::FETCH_ASSOC) ?: [];

$pageTitle = 'Research Repository';
$activeModule = 'crad';
$activePage = 'research-repository';
$breadcrumbs = [
    ['label' => 'CRAD', 'url' => BASE_URL . '/modules/crad/index.php'],
    ['label' => 'Research Repository', 'url' => null],
];
require_once ROOT_PATH . '/includes/layout-start.php';
renderBreadcrumbs($breadcrumbs);
?>
<div class="glass-dashboard"><div class="glass-board">
    <div class="row g-3 mb-4">
        <?php foreach ([
            ['Catalogued Records', 'total'],
            ['For Publication', 'for_publication'],
            ['Published', 'published'],
            ['Archived', 'archived'],
        ] as [$label, $key]): ?>
            <div class="col-md-3"><div class="glass-panel p-3"><small><?= e($label) ?></small><h3><?= (int) ($repositoryMetrics[$key] ?? 0) ?></h3></div></div>
        <?php endforeach; ?>
    </div>

    <div class="glass-panel"><div class="glass-panel-body">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div><h5 class="glass-panel-title mb-1">Research Repository</h5><p class="text-muted mb-0">Live catalogue of publication records backed by approved CRAD research outputs.</p></div>
            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/modules/crad/pages/publication-create.php"><i class="fas fa-plus me-1"></i>Create Publication Record</a>
        </div>

        <form method="get" class="row g-2 mb-4">
            <div class="col-lg-5"><label class="form-label small" for="repository-search">Search</label><input class="form-control" id="repository-search" name="q" value="<?= e($search) ?>" placeholder="Title, author, keyword, repository ID, or group"></div>
            <div class="col-lg-3"><label class="form-label small" for="repository-status">Status</label><select class="form-select" id="repository-status" name="status"><option value="">All statuses</option><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2"><label class="form-label small" for="repository-access">Access</label><select class="form-select" id="repository-access" name="access"><option value="">All access</option><?php foreach ($allowedAccessLevels as $access): ?><option value="<?= e($access) ?>" <?= $accessFilter === $access ? 'selected' : '' ?>><?= e($access) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2 d-flex align-items-end gap-2"><button class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i>Filter</button><a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/modules/crad/pages/research-repository.php" aria-label="Clear filters"><i class="fas fa-undo"></i></a></div>
        </form>

        <?php if (!$repositoryRows): ?>
            <p class="text-muted mb-0">No real repository records match the selected filters.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Repository ID</th><th>Research Title</th><th>Authors / Keywords</th><th>Access</th><th>Status</th><th>Publication</th><th>DOI / Link</th></tr></thead><tbody>
            <?php foreach ($repositoryRows as $row): ?>
                <?php
                $doiLink = trim((string) ($row['doi_link'] ?? ''));
                $doiScheme = strtolower((string) parse_url($doiLink, PHP_URL_SCHEME));
                $safeDoiLink = filter_var($doiLink, FILTER_VALIDATE_URL)
                    && in_array($doiScheme, ['http', 'https'], true)
                    ? $doiLink
                    : '';
                ?>
                <tr>
                    <td><?= e((string) (($row['repository_identifier'] ?? '') ?: 'REP-' . (int) $row['id'])) ?></td>
                    <td><strong><?= e((string) $row['title']) ?></strong><div class="small text-muted"><?= e((string) ($row['group_number'] ?? '')) ?><?= !empty($row['group_name']) ? ' · ' . e((string) $row['group_name']) : '' ?></div></td>
                    <td><?= e((string) (($row['authors'] ?? '') ?: 'Not recorded')) ?><div class="small text-muted text-truncate" style="max-width:260px"><?= e((string) (($row['keywords'] ?? '') ?: 'No keywords')) ?></div></td>
                    <td><?= e((string) ($row['access_level'] ?? 'Campus Only')) ?><?php if (($row['access_level'] ?? '') === 'Embargoed' && !empty($row['embargo_until'])): ?><div class="small text-muted">Until <?= e((string) $row['embargo_until']) ?></div><?php endif; ?></td>
                    <td><span class="badge text-bg-<?= $row['status'] === 'Published' ? 'success' : ($row['status'] === 'Archived' ? 'secondary' : 'warning') ?>"><?= e((string) $row['status']) ?></span></td>
                    <td><?= e((string) (($row['publication_outlet'] ?? '') ?: 'Not recorded')) ?><div class="small text-muted"><?= e((string) (($row['publication_date'] ?? '') ?: 'No date')) ?></div></td>
                    <td><?php if ($safeDoiLink !== ''): ?><a href="<?= e($safeDoiLink) ?>" target="_blank" rel="noopener noreferrer">Open record</a><?php else: ?>Not recorded<?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div></div>
</div></div>
<?php require_once ROOT_PATH . '/includes/layout-end.php'; ?>
