<?php
$page_title = 'Admin - Bandeja';
require_once __DIR__ . '/_admin_bootstrap.php';

$db = db_conn();
$admin = admin_require_login($db);

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 160) {
  $q = mb_substr($q, 0, 160);
}

$status = strtoupper(trim((string)($_GET['status'] ?? 'ALL')));
if ($status === '' || ($status !== 'ALL' && !admin_status_exists($status))) {
  $status = 'ALL';
}

$companyId = max(0, (int)($_GET['company_id'] ?? 0));
$groupId = max(0, (int)($_GET['group_id'] ?? 0));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$companies = get_companies($db);
$groups = [];
$resGroups = $db->query("SELECT id, company_id, name, law_ref, is_active, sort_order
                         FROM portal_category_group
                         WHERE is_active = 1
                         ORDER BY company_id, sort_order, name");
if ($resGroups) {
  while ($row = $resGroups->fetch_assoc()) {
    if ($companyId > 0 && (int)$row['company_id'] !== $companyId) {
      continue;
    }
    $groups[] = $row;
  }
  $resGroups->free();
}

$baseConditions = [];
$baseParams = [];
$baseTypes = '';

if ($companyId > 0) {
  $baseConditions[] = 'r.company_id = ?';
  $baseTypes .= 'i';
  $baseParams[] = $companyId;
}
if ($groupId > 0) {
  $baseConditions[] = 'g.id = ?';
  $baseTypes .= 'i';
  $baseParams[] = $groupId;
}
if ($q !== '') {
  $baseConditions[] = "(r.report_key LIKE ? OR r.subject LIKE ? OR r.description LIKE ? OR COALESCE(r.reporter_name, '') LIKE ? OR COALESCE(r.reporter_email, '') LIKE ?)";
  $like = '%' . $q . '%';
  $baseTypes .= 'sssss';
  $baseParams[] = $like;
  $baseParams[] = $like;
  $baseParams[] = $like;
  $baseParams[] = $like;
  $baseParams[] = $like;
}

$baseWhere = $baseConditions ? ('WHERE ' . implode(' AND ', $baseConditions)) : '';
$conditions = $baseConditions;
$params = $baseParams;
$types = $baseTypes;
if ($status !== 'ALL') {
  $conditions[] = 'r.status = ?';
  $types .= 's';
  $params[] = $status;
}
$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$sqlBaseFrom = "FROM portal_report r
                JOIN portal_company c ON c.id = r.company_id
                LEFT JOIN portal_category cat ON cat.id = r.category_id
                LEFT JOIN portal_category_group g ON g.id = cat.group_id";

$sqlCount = "SELECT COUNT(*) AS total $sqlBaseFrom $where";
$stCount = $db->prepare($sqlCount);
if (!$stCount) {
  throw new RuntimeException('No fue posible preparar la consulta de conteo.');
}
admin_bind_params($stCount, $types, $params);
$stCount->execute();
$total = (int)($stCount->get_result()->fetch_assoc()['total'] ?? 0);
$stCount->close();

$sqlFilteredStatus = "SELECT r.status, COUNT(*) AS cnt $sqlBaseFrom $baseWhere GROUP BY r.status";
$stFiltered = $db->prepare($sqlFilteredStatus);
$filteredCountMap = [];
if ($stFiltered) {
  admin_bind_params($stFiltered, $baseTypes, $baseParams);
  $stFiltered->execute();
  $rsFiltered = $stFiltered->get_result();
  while ($row = $rsFiltered->fetch_assoc()) {
    $filteredCountMap[strtoupper((string)$row['status'])] = (int)$row['cnt'];
  }
  $stFiltered->close();
}

$sql = "SELECT
          r.id,
          r.report_key,
          r.subject,
          r.description,
          r.status,
          r.is_anonymous,
          r.reporter_name,
          r.reporter_email,
          r.created_at,
          r.updated_at,
          c.name AS company_name,
          cat.name AS category_name,
          g.name AS group_name,
          g.law_ref AS law_ref
        $sqlBaseFrom
        $where
        ORDER BY COALESCE(r.updated_at, r.created_at) DESC, r.id DESC
        LIMIT ? OFFSET ?";
$st = $db->prepare($sql);
if (!$st) {
  throw new RuntimeException('No fue posible preparar la consulta de casos.');
}
$params2 = $params;
$params2[] = $limit;
$params2[] = $offset;
$types2 = $types . 'ii';
admin_bind_params($st, $types2, $params2);
$st->execute();
$rs = $st->get_result();
$rows = [];
while ($row = $rs->fetch_assoc()) {
  $rows[] = $row;
}
$st->close();

$pages = max(1, (int)ceil($total / $limit));
if ($page > $pages) {
  $page = $pages;
}

$firstRow = $total > 0 ? (($page - 1) * $limit) + 1 : 0;
$lastRow = min($total, $page * $limit);
$selectedCompany = $companyId > 0 ? portal_find_company($companies, $companyId) : null;
$showingGroupName = '';
foreach ($groups as $item) {
  if ((int)$item['id'] === $groupId) {
    $showingGroupName = (string)$item['name'];
    break;
  }
}

$statusPresets = [
  ['label' => 'Nuevos', 'status' => 'NEW', 'helper' => 'Pendientes de triage', 'icon' => 'N'],
  ['label' => 'En revisión', 'status' => 'IN_REVIEW', 'helper' => 'Investigación en curso', 'icon' => 'R'],
  ['label' => 'Esperando denunciante', 'status' => 'WAITING_REPORTER', 'helper' => 'Falta respuesta del denunciante', 'icon' => 'E'],
  ['label' => 'Resueltos / cerrados', 'status' => 'RESOLVED', 'helper' => 'Incluye resueltos, cerrados y archivados', 'icon' => '✓'],
];

require __DIR__ . '/_layout_top.php';
?>

<div class="admin-page-head">
  <div>
    <div class="admin-page-head__eyebrow">Operación diaria</div>
    <h1 class="admin-page-head__title">Bandeja de casos</h1>
    <p class="admin-page-head__desc">Consulta, prioriza y abre denuncias desde un solo panel. Los filtros se aplican sobre empresa, grupo legal y texto libre.</p>
  </div>
  <div class="admin-chiprow">
    <span class="admin-chip">Rol activo: <?= h(admin_role_label($admin['role'] ?? null)) ?></span>
    <?php if ($selectedCompany): ?>
      <span class="admin-chip">Empresa: <?= h($selectedCompany['name']) ?></span>
    <?php endif; ?>
    <?php if ($showingGroupName !== ''): ?>
      <span class="admin-chip">Grupo: <?= h($showingGroupName) ?></span>
    <?php endif; ?>
  </div>
</div>

<section class="admin-stat-grid">
  <?php foreach ($statusPresets as $card): ?>
    <?php
      if ($card['status'] === 'RESOLVED') {
        $countValue = (int)($filteredCountMap['RESOLVED'] ?? 0) + (int)($filteredCountMap['CLOSED'] ?? 0) + (int)($filteredCountMap['ARCHIVED'] ?? 0);
      } else {
        $countValue = (int)($filteredCountMap[$card['status']] ?? 0);
      }
    ?>
    <article class="admin-stat">
      <div class="admin-stat__top">
        <div class="admin-stat__label"><?= h($card['label']) ?></div>
        <div class="admin-stat__icon"><?= h($card['icon']) ?></div>
      </div>
      <div>
        <div class="admin-stat__value"><?= $countValue ?></div>
        <div class="admin-stat__hint"><?= h($card['helper']) ?></div>
      </div>
    </article>
  <?php endforeach; ?>
</section>

<section class="admin-toolbar">
  <form method="get" class="admin-toolbar__form">
    <div class="admin-field">
      <label>Buscar</label>
      <input class="admin-control" name="q" value="<?= h($q) ?>" placeholder="Key, asunto, descripción, denunciante o email...">
    </div>

    <div class="admin-field">
      <label>Estado</label>
      <select class="admin-select" name="status">
        <option value="ALL" <?= $status === 'ALL' ? 'selected' : '' ?>>Todos</option>
        <?php foreach (admin_statuses() as $item): ?>
          <option value="<?= h($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= h(admin_status_label($item)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="admin-field">
      <label>Empresa</label>
      <select class="admin-select" name="company_id">
        <option value="0" <?= $companyId === 0 ? 'selected' : '' ?>>Todas</option>
        <?php foreach ($companies as $company): ?>
          <option value="<?= (int)$company['id'] ?>" <?= $companyId === (int)$company['id'] ? 'selected' : '' ?>>
            <?= h($company['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="admin-field">
      <label>Grupo legal</label>
      <select class="admin-select" name="group_id">
        <option value="0" <?= $groupId === 0 ? 'selected' : '' ?>>Todos</option>
        <?php foreach ($groups as $group): ?>
          <option value="<?= (int)$group['id'] ?>" <?= $groupId === (int)$group['id'] ? 'selected' : '' ?>>
            <?= h($group['name']) ?><?php if (!empty($group['law_ref'])): ?> (<?= h($group['law_ref']) ?>)<?php endif; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="admin-toolbar__actions">
      <button class="admin-btn" type="submit">Aplicar filtros</button>
      <a class="admin-btn-secondary" href="<?= admin_link('/admin/index.php') ?>">Limpiar</a>
    </div>
  </form>

  <div class="admin-divider"></div>
  <div class="admin-toolbar__actions">
    <span class="admin-badge-soft">Mostrando <strong><?= (int)$firstRow ?></strong>–<strong><?= (int)$lastRow ?></strong> de <strong><?= (int)$total ?></strong> caso(s)</span>
    <?php if ($q !== ''): ?><span class="admin-badge-soft">Búsqueda: <?= h($q) ?></span><?php endif; ?>
    <?php if ($selectedCompany): ?><span class="admin-badge-soft">Empresa: <?= h($selectedCompany['name']) ?></span><?php endif; ?>
    <?php if ($showingGroupName !== ''): ?><span class="admin-badge-soft">Grupo: <?= h($showingGroupName) ?></span><?php endif; ?>
  </div>
</section>

<nav class="admin-segmented" aria-label="Estados">
  <a class="admin-segment <?= $status === 'ALL' ? 'is-active' : '' ?>" href="<?= admin_link('/admin/index.php?' . http_build_query(['q' => $q, 'company_id' => $companyId, 'group_id' => $groupId, 'status' => 'ALL'])) ?>">
    Todos
    <span class="admin-segment__count"><?= (int)$total ?></span>
  </a>
  <?php foreach (admin_statuses() as $statusItem): ?>
    <?php $active = ($status === $statusItem); ?>
    <a class="admin-segment <?= $active ? 'is-active' : '' ?>" href="<?= admin_link('/admin/index.php?' . http_build_query(['q' => $q, 'company_id' => $companyId, 'group_id' => $groupId, 'status' => $statusItem])) ?>">
      <?= h(admin_status_label($statusItem)) ?>
      <span class="admin-segment__count"><?= (int)($filteredCountMap[$statusItem] ?? 0) ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<?php if (!$rows): ?>
  <section class="admin-empty">
    <div class="admin-empty__title">No hay resultados para los filtros actuales.</div>
    <div class="admin-empty__text">Prueba quitar algunos filtros o ampliar el texto de búsqueda para encontrar más casos.</div>
  </section>
<?php else: ?>
  <section class="admin-list">
    <div class="admin-list__head">
      <div>Key / denunciante</div>
      <div>Asunto</div>
      <div>Empresa / grupo</div>
      <div>Estado</div>
      <div>Última actividad</div>
      <div>Acción</div>
    </div>

    <?php foreach ($rows as $row): ?>
      <?php
        $lastActivity = trim((string)($row['updated_at'] ?: $row['created_at']));
        $reporterLine = ((int)$row['is_anonymous'] === 1)
          ? 'Anónimo'
          : trim((string)($row['reporter_name'] ?? '') . ((string)($row['reporter_email'] ?? '') !== '' ? ' · ' . (string)$row['reporter_email'] : ''));
        if ($reporterLine === '') {
          $reporterLine = 'Sin identificación';
        }
      ?>
      <article class="admin-row">
        <div>
          <div class="admin-row__title admin-row__key">
            <a href="<?= admin_link('/admin/report_view.php?id=' . (int)$row['id']) ?>"><?= h($row['report_key']) ?></a>
          </div>
          <div class="admin-row__sub"><?= h($reporterLine) ?></div>
          <div class="admin-row__meta">
            <?php if ((int)$row['is_anonymous'] === 1): ?><span class="admin-badge-soft">Anónimo</span><?php endif; ?>
            <span class="admin-badge-soft">Creado <?= h((string)$row['created_at']) ?></span>
          </div>
        </div>

        <div>
          <div class="admin-row__title"><?= h($row['subject']) ?></div>
          <div class="admin-row__sub"><?= h(admin_excerpt((string)($row['description'] ?? ''), 155)) ?></div>
        </div>

        <div>
          <div class="admin-row__title"><?= h($row['company_name']) ?></div>
          <div class="admin-row__sub"><?= h($row['group_name'] ?? '-') ?></div>
          <div class="admin-row__sub"><?= h($row['category_name'] ?? '-') ?><?php if (!empty($row['law_ref'])): ?> · <?= h($row['law_ref']) ?><?php endif; ?></div>
        </div>

        <div>
          <span class="tag <?= h(admin_status_tag_class((string)$row['status'])) ?>">
            <?= h(admin_status_label((string)$row['status'])) ?>
          </span>
        </div>

        <div>
          <div class="admin-row__title" style="font-size:16px"><?= h(admin_relative_time($lastActivity)) ?></div>
          <div class="admin-row__sub">Actualizado <?= h((string)($row['updated_at'] ?: $row['created_at'])) ?></div>
        </div>

        <div>
          <a class="admin-btn-secondary" href="<?= admin_link('/admin/report_view.php?id=' . (int)$row['id']) ?>">Abrir caso</a>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<div class="admin-pager">
  <div class="small">Página <strong><?= (int)$page ?></strong> de <strong><?= (int)$pages ?></strong></div>
  <div class="admin-pager__nav">
    <?php $base = '/admin/index.php?' . http_build_query([
      'q' => $q,
      'status' => $status,
      'company_id' => $companyId,
      'group_id' => $groupId,
    ]); ?>
    <a class="admin-btn-ghost" href="<?= admin_link($base . '&page=' . max(1, $page - 1)) ?>" <?= $page <= 1 ? 'style="pointer-events:none;opacity:.5"' : '' ?>>← Anterior</a>
    <a class="admin-btn-ghost" href="<?= admin_link($base . '&page=' . min($pages, $page + 1)) ?>" <?= $page >= $pages ? 'style="pointer-events:none;opacity:.5"' : '' ?>>Siguiente →</a>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
