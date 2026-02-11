<?php
$page_title = "Canal de Denuncias";
require_once __DIR__ . "/_header.php";

$db = db_conn();

// SINGLE COMPANY (TyM) – company_id fixed by _bootstrap.php
$company_id = current_company_id();

// Optional (safe): load company row if you want to use name/logo from DB later
$company = null;
try {
  $companies = get_companies($db);
  $company = ($company_id > 0) ? portal_find_company($companies, $company_id) : null;
} catch (Throwable $e) {
  // ignore
}

$resources = ($company_id > 0) ? portal_get_resources($db, $company_id) : [];

$base = rtrim(base_url(), '/');

$hero_url = $base . "/images/servicios_elctricos_tym_ltda_cover.jpg"; // hero image
$logo_tym = $base . "/images/tym_logo.png";

// external website for logo click
$link_tym = "https://www.tymelectricos.cl/";

// demo links (when DB has no resources)
$demo_docs = [
  ["title" => "Notas internas", "url" => $base . "/blank.php?doc=codigo_conducta"],
];
?>

<!-- TOP BAR (logos left, language right) -->
<div class="home-topbar">
  <div class="home-logos">
    <a class="home-logo-link" href="<?= h($link_tym) ?>" target="_blank" rel="noopener" aria-label="Servicios Eléctricos TyM">
      <img src="<?= h($logo_tym) ?>" alt="TyM">
    </a>
  </div>

  <div class="home-lang">Español</div>
</div>

<!-- HERO IMAGE -->
<div class="home-hero" style="background-image:url('<?= h($hero_url) ?>');"></div>

<div class="home-wrap">

  <!-- 3 big tiles -->
  <div class="home-tiles">
    <a class="home-tile" href="<?= h($base) ?>/reportar.php">
      <div class="icon">!</div>
      <div class="title">Presentar una denuncia</div>
    </a>

    <a class="home-tile" href="<?= h($base) ?>/seguimiento.php">
      <div class="icon">↻</div>
      <div class="title">Seguimiento</div>
    </a>

    <a class="home-tile" href="<?= h($base) ?>/faq.php">
      <div class="icon">?</div>
      <div class="title">Preguntas frecuentes</div>
    </a>
  </div>

  <!-- Content (2 columns with divider) -->
  <div class="home-content">
    <div class="home-box home-left">
      <h3>Archivos disponibles para consulta</h3>

      <?php if (empty($resources)): ?>
        <div class="small">No hay documentos disponibles (demo). Enlaces de ejemplo:</div>
        <ul class="home-links">
          <?php foreach ($demo_docs as $d): ?>
            <li>
              <a href="<?= h($d['url']) ?>" target="_blank" rel="noopener"><?= h($d['title']) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <ul class="home-links">
          <?php foreach ($resources as $r): ?>
            <li>
              <a href="<?= h($r['url']) ?>" target="_blank" rel="noopener"><?= h($r['title']) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="home-box home-right">
      <p style="margin:0; color:#111827; line-height:1.65;">
        Este canal permite reportar situaciones relacionadas con ética, cumplimiento, seguridad, fraude u otros temas.
        Al finalizar, el sistema entrega una <b>Clave de Reporte</b> y una <b>Contraseña</b> para realizar seguimiento y
        aportar información adicional posteriormente.
      </p>

      <div class="home-divider"></div>

      <p style="margin:0; color:#374151; line-height:1.65;">
        Si tu denuncia requiere atención inmediata por una emergencia, utiliza los canales de emergencia correspondientes
        (por ejemplo, autoridades locales o protocolos internos). Este portal está orientado a reportes que serán revisados
        y gestionados por el equipo responsable.
      </p>
    </div>
  </div>

  <div class="home-warning">
    <b>Este NO es un servicio de emergencia.</b> No utilice este sitio para denunciar amenazas inmediatas a la vida,
    al medioambiente o a la propiedad.
  </div>

  <div class="home-footer">
    <div>Copyright © <?= date('Y') ?> Servicios Eléctricos TyM LTDA. All rights reserved.</div>
    <div style="display:flex; gap:14px; flex-wrap:wrap;">
      <a href="<?= h($base) ?>/blank.php?doc=privacy" target="_blank" rel="noopener">Privacy</a>
      <a href="<?= h($base) ?>/blank.php?doc=acceptable_use" target="_blank" rel="noopener">Acceptable Use</a>
      <a href="<?= h($base) ?>/blank.php?doc=cookies" target="_blank" rel="noopener">Cookies</a>
      <a href="<?= h($base) ?>/blank.php?doc=contact" target="_blank" rel="noopener">Contact</a>
    </div>
  </div>

</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
