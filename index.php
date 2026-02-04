<?php
$page_title = "Denuncias Portal";
require_once __DIR__ . "/_header.php";

$db = db_conn();
$companies = get_companies($db);

$company_id = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
if ($company_id <= 0 && !empty($companies)) $company_id = (int)$companies[0]['id'];

$company = portal_find_company($companies, $company_id);

$resources = $company_id > 0 ? portal_get_resources($db, $company_id) : [];

?>

<div class="grid">
  <div class="card hero">
    <h2>Canal de Denuncias</h2>
    <p>
      Puedes enviar una denuncia relacionada con ética, cumplimiento, seguridad, fraude u otros temas.
      El sistema entregará <b>Clave de Reporte</b> + <b>Password</b> para hacer seguimiento y añadir información posteriormente.
    </p>

    <div class="hr"></div>

    <form method="get" action="">
  <div class="field">
    <label>Seleccionar empresa</label>

    <select id="company_id" name="company_id" onchange="this.form.submit()">
          <?php foreach ($companies as $c): $cid = (int)$c['id']; ?>
            <option value="<?= $cid ?>" <?= ($cid === (int)$company_id ? 'selected' : '') ?>>
              <?= h($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="small">Las categorías y documentos cambian según la empresa.</div>
        <div class="small" style="opacity:.75">Cargando <?= count($companies) ?> empresas.</div>
        <!-- companies_loaded: <?= count($companies) ?> -->
  </div>
</form>

    <div class="actions">
      <a class="action" href="<?= h(base_url()) ?>/reportar.php?company_id=<?= (int)$company_id ?>">
        <div class="t">Reportar</div>
        <div class="d">Crear una denuncia y recibir una Clave para seguimiento.</div>
      </a>

      <a class="action" href="<?= h(base_url()) ?>/seguimiento.php">
        <div class="t">Seguimiento</div>
        <div class="d">Ver estado y enviar información adicional.</div>
      </a>

      <a class="action" href="<?= h(base_url()) ?>/faq.php">
        <div class="t">FAQ</div>
        <div class="d">Guía sobre anonimato, confidencialidad y proceso.</div>
      </a>
    </div>
  </div>

  <div class="card side">
    <h3>Documentos de referencia</h3>
    <?php if (empty($resources)): ?>
      <div class="small">No hay documentos para esta empresa (demo).</div>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($resources as $r): ?>
          <li>
            <a href="<?= h($r['url']) ?>" target="_blank" rel="noopener">
              <?= h($r['title']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <div class="hr"></div>
    <div class="small">
      * Demo. En producción (Opción A) se recomienda subdominio + HTTPS + hardening.
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
