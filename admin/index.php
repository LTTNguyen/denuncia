<?php
$page_title = "Admin - Bandeja";
require_once __DIR__ . "/_admin_bootstrap.php";

$db = db_conn();
$admin = admin_require_login($db);

$q = trim((string)($_GET['q'] ?? ''));
$status = strtoupper(trim((string)($_GET['status'] ?? '')));
if ($status === '') $status = 'ALL';

$company_id = (int)($_GET['company_id'] ?? 0);
$group_id = (int)($_GET['group_id'] ?? 0);

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$companies = get_companies($db);
$groups = [];
$resg = $db->query("SELECT id, company_id, name, law_ref, is_active, sort_order
                    FROM portal_category_group
                    WHERE is_active = 1
                    ORDER BY sort_order, name");
while ($row = $resg->fetch_assoc()) $groups[] = $row;

$counts = [];
$resc = $db->query("SELECT status, COUNT(*) cnt FROM portal_report GROUP BY status");
while ($row = $resc->fetch_assoc()) $counts[strtoupper($row['status'])] = (int)$row['cnt'];

$conditions = [];
$params = [];
$types = "";

if ($status !== 'ALL') {
  $conditions[] = "r.status = ?";
  $types .= "s";
  $params[] = $status;
}
if ($company_id > 0) {
  $conditions[] = "r.company_id = ?";
  $types .= "i";
  $params[] = $company_id;
}
if ($group_id > 0) {
  $conditions[] = "g.id = ?";
  $types .= "i";
  $params[] = $group_id;
}
if ($q !== '') {
  $conditions[] = "(r.report_key LIKE ? OR r.subject LIKE ? OR r.description LIKE ?)";
  $like = "%" . $q . "%";
  $types .= "sss";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

$where = $conditions ? ("WHERE " . implode(" AND ", $conditions)) : "";

$sqlCount = "SELECT COUNT(*) AS total
             FROM portal_report r
             JOIN portal_company c ON c.id = r.company_id
             LEFT JOIN portal_category cat ON cat.id = r.category_id
             LEFT JOIN portal_category_group g ON g.id = cat.group_id
             $where";
$stc = $db->prepare($sqlCount);
if ($types !== "") {
  $bind = [];
  $bind[] = $types;
  foreach ($params as $k => $v) $bind[] = &$params[$k];
  call_user_func_array([$stc, 'bind_param'], $bind);
}
$stc->execute();
$total = (int)($stc->get_result()->fetch_assoc()['total'] ?? 0);
$stc->close();

$sql = "SELECT
          r.id, r.report_key, r.subject, r.status, r.is_anonymous,
          r.created_at, r.updated_at,
          c.name AS company_name,
          cat.name AS category_name,
          g.name AS group_name,
          g.law_ref AS law_ref
        FROM portal_report r
        JOIN portal_company c ON c.id = r.company_id
        LEFT JOIN portal_category cat ON cat.id = r.category_id
        LEFT JOIN portal_category_group g ON g.id = cat.group_id
        $where
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?";

$st = $db->prepare($sql);
$types2 = $types . "ii";
$params2 = $params;
$params2[] = $limit;
$params2[] = $offset;

$bind2 = [];
$bind2[] = $types2;
foreach ($params2 as $k => $v) $bind2[] = &$params2[$k];
call_user_func_array([$st, 'bind_param'], $bind2);

$st->execute();
$rs = $st->get_result();
$rows = [];
while ($r = $rs->fetch_assoc()) $rows[] = $r;
$st->close();

$pages = max(1, (int)ceil($total / $limit));

require __DIR__ . "/_layout_top.php";
?>

<section class="hero" style="padding-top:26px">
  <h1>Bandeja de Casos</h1>
  <p class="small">Filtra por empresa, grupo legal y estado. Abre un caso para revisar adjuntos, historial y mensajes.</p>
</section>

<div class="grid" style="grid-template-columns: repeat(3, 1fr);">
  <div class="card">
    <div class="small">Nuevos</div>
    <div style="font-size:26px;font-weight:900"><?= (int)($counts['NEW'] ?? 0) ?></div>
  </div>
  <div class="card">
    <div class="small">En revisión</div>
    <div style="font-size:26px;font-weight:900"><?= (int)($counts['IN_REVIEW'] ?? 0) ?></div>
  </div>
  <div class="card">
    <div class="small">Cerrados</div>
    <div style="font-size:26px;font-weight:900"><?= (int)($counts['CLOSED'] ?? 0) ?></div>
  </div>
</div>

<div class="card">
  <form method="get" class="form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr; align-items:end;">
    <div>
      <label>Buscar</label>
      <input name="q" value="<?= h($q) ?>" placeholder="report_key / asunto / texto...">
    </div>

    <div>
      <label>Estado</label>
      <select name="status">
        <option value="ALL" <?= $status==='ALL'?'selected':'' ?>>Todos</option>
        <?php foreach (admin_statuses() as $s): ?>
          <option value="<?= h($s) ?>" <?= $status===$s?'selected':'' ?>><?= h(status_label($s)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Empresa</label>
      <select name="company_id">
        <option value="0" <?= $company_id===0?'selected':'' ?>>Todas</option>
        <?php foreach ($companies as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $company_id===(int)$c['id']?'selected':'' ?>>
            <?= h($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Grupo (Ley)</label>
      <select name="group_id">
        <option value="0" <?= $group_id===0?'selected':'' ?>>Todos</option>
        <?php foreach ($groups as $g): ?>
          <option value="<?= (int)$g['id'] ?>" <?= $group_id===(int)$g['id']?'selected':'' ?>>
            <?= h($g['name']) ?> (<?= h($g['law_ref']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="grid-column:1/-1;display:flex;gap:10px;align-items:center;margin-top:6px">
      <button class="btn" type="submit">Aplicar</button>
      <a class="btn btn-ghost" href="<?= admin_link('/admin/index.php') ?>">Limpiar</a>
      <div class="small" style="margin-left:auto">
        Total: <strong><?= (int)$total ?></strong>
      </div>
    </div>
  </form>
</div>

<div class="card" style="padding:0; overflow:auto">
  <table class="table">
    <thead>
      <tr>
        <th>Key</th>
        <th>Asunto</th>
        <th>Empresa</th>
        <th>Grupo / Categoría</th>
        <th>Estado</th>
        <th>Creado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="6" class="small" style="padding:16px">No hay resultados.</td></tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <a href="<?= admin_link('/admin/report_view.php?id='.(int)$r['id']) ?>" style="font-weight:800">
              <?= h($r['report_key']) ?>
            </a>
            <?php if ((int)$r['is_anonymous'] === 1): ?>
              <div class="small">Anónimo</div>
            <?php endif; ?>
          </td>
          <td><?= h($r['subject']) ?></td>
          <td><?= h($r['company_name']) ?></td>
          <td>
            <div style="font-weight:700"><?= h($r['group_name'] ?? '-') ?></div>
            <div class="small">
              <?= h($r['category_name'] ?? '-') ?>
              <?php if (!empty($r['law_ref'])): ?>
                · <?= h($r['law_ref']) ?>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <span class="tag <?= h(admin_status_tag_class((string)$r['status'])) ?>">
              <?= h(status_label((string)$r['status'])) ?>
            </span>
          </td>
          <td class="small"><?= h($r['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div style="display:flex;gap:10px;justify-content:flex-end;align-items:center;margin-top:12px">
  <?php
    $base = '/admin/index.php?'.http_build_query([
      'q'=>$q,'status'=>$status,'company_id'=>$company_id,'group_id'=>$group_id
    ]);
  ?>
  <a class="btn btn-ghost" href="<?= admin_link($base.'&page='.max(1,$page-1)) ?>" <?= $page<=1?'style="pointer-events:none;opacity:.5"':'' ?>>
    ← Prev
  </a>
  <div class="small">Página <strong><?= (int)$page ?></strong> / <?= (int)$pages ?></div>
  <a class="btn btn-ghost" href="<?= admin_link($base.'&page='.min($pages,$page+1)) ?>" <?= $page>=$pages?'style="pointer-events:none;opacity:.5"':'' ?>>
    Next →
  </a>
</div>

<?php require __DIR__ . "/_layout_bottom.php"; ?>
