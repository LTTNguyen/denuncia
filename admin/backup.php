<?php
$page_title = 'Admin - Backup';
require_once __DIR__ . '/_admin_bootstrap.php';

$db = db_conn();
$admin = admin_require_login($db);
admin_require_role($admin, 'ADMIN');

function backup_mysqldump_to_file(string $outFile): void {
  global $DENUNCIA_DB;

  $host = $DENUNCIA_DB['host'] ?? 'localhost';
  $user = $DENUNCIA_DB['user'] ?? 'root';
  $pass = $DENUNCIA_DB['pass'] ?? '';
  $name = $DENUNCIA_DB['name'] ?? '';
  $port = (int)($DENUNCIA_DB['port'] ?? 3306);
  $mysqldump = $DENUNCIA_DB['mysqldump_path'] ?? 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

  if (!is_file($mysqldump)) {
    $mysqldump = 'mysqldump';
  }

  $cmd = escapeshellarg($mysqldump)
    . ' --host=' . escapeshellarg($host)
    . ' --port=' . escapeshellarg((string)$port)
    . ' --user=' . escapeshellarg($user)
    . ' --password=' . escapeshellarg($pass)
    . ' --default-character-set=utf8mb4 --single-transaction --quick --routines --events '
    . escapeshellarg($name)
    . ' > ' . escapeshellarg($outFile);

  $result = 0;
  @system($cmd, $result);

  if ($result !== 0 || !is_file($outFile) || filesize($outFile) < 50) {
    throw new RuntimeException('mysqldump failed. Revisa mysqldump_path o permisos de ejecución.');
  }
}

function backup_zip_dir(ZipArchive $zip, string $dir, string $baseInZip): void {
  $dir = rtrim($dir, '/\\');
  if (!is_dir($dir)) {
    return;
  }

  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($iterator as $file) {
    $path = (string)$file;
    $rel = substr($path, strlen($dir) + 1);
    $rel = str_replace('\\', '/', $rel);
    $zipPath = rtrim($baseInZip, '/') . '/' . $rel;

    if (is_dir($path)) {
      $zip->addEmptyDir($zipPath);
    } else {
      $zip->addFile($path, $zipPath);
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check($_POST['csrf'] ?? null)) {
    admin_flash_set('warn', 'Sesión inválida.');
    redirect('/admin/backup.php');
    exit;
  }

  $includeUploads = (($_POST['include_uploads'] ?? '0') === '1');
  $tmpDir = sys_get_temp_dir();
  $stamp = date('Ymd_His');
  $sqlFile = $tmpDir . DIRECTORY_SEPARATOR . 'denuncias_portal_' . $stamp . '.sql';
  $zipFile = $tmpDir . DIRECTORY_SEPARATOR . 'denuncias_backup_' . $stamp . '.zip';

  try {
    backup_mysqldump_to_file($sqlFile);

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
      throw new RuntimeException('No fue posible crear el archivo ZIP.');
    }

    $zip->addFile($sqlFile, 'db.sql');

    if ($includeUploads) {
      $uploadsPath = realpath(__DIR__ . '/../uploads');
      if ($uploadsPath) {
        backup_zip_dir($zip, $uploadsPath, 'uploads');
      }
    }

    $zip->close();

    admin_audit($db, null, 'ADMIN_BACKUP_DOWNLOAD', 'INVESTIGATOR', (string)$admin['email'], [
      'include_uploads' => $includeUploads,
    ]);

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="denuncias_backup_' . $stamp . '.zip"');
    header('Content-Length: ' . (string)filesize($zipFile));
    header('X-Content-Type-Options: nosniff');
    readfile($zipFile);
  } catch (Throwable $e) {
    admin_flash_set('warn', $e->getMessage());
    redirect('/admin/backup.php');
  } finally {
    if (is_file($sqlFile)) {
      @unlink($sqlFile);
    }
    if (is_file($zipFile)) {
      @unlink($zipFile);
    }
  }
  exit;
}

require __DIR__ . '/_layout_top.php';
?>

<div class="admin-page-head">
  <div>
    <div class="admin-page-head__eyebrow">Respaldo administrativo</div>
    <h1 class="admin-page-head__title">Backup del portal</h1>
    <p class="admin-page-head__desc">Genera un paquete ZIP con la base de datos y, si lo necesitas, adjunta la carpeta de evidencias. Esta operación está restringida a administradores.</p>
  </div>
  <div class="admin-chiprow">
    <span class="admin-chip">Rol requerido: Administrador</span>
    <span class="admin-chip">Descarga directa protegida por sesión</span>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.1fr .9fr;align-items:start">
  <div class="card" style="padding:24px 24px 20px">
    <form method="post" class="form-grid" style="grid-template-columns:1fr;gap:14px">
      <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">

      <label style="display:flex;gap:10px;align-items:flex-start">
        <input type="checkbox" name="include_uploads" value="1">
        <span>
          <strong>Incluir carpeta uploads</strong><br>
          <span class="small">Úsalo solo cuando realmente necesites respaldar adjuntos; el archivo final puede crecer bastante.</span>
        </span>
      </label>

      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn" type="submit">Descargar backup</button>
        <a class="btn btn-ghost" href="<?= h(admin_link('/admin/index.php')) ?>">← Volver</a>
      </div>
    </form>
  </div>

  <div class="card" style="padding:24px 24px 20px">
    <h3 style="margin:0 0 12px;color:#0f172a">Qué incluye</h3>
    <ul class="list" style="margin:0;padding-left:18px;line-height:1.7;color:#475569">
      <li>Dump SQL completo de la base actual.</li>
      <li>Opcionalmente, la carpeta <code>uploads/</code> con adjuntos y evidencias.</li>
      <li>Archivo ZIP temporal generado solo durante la descarga.</li>
    </ul>
    <div class="hr"></div>
    <div class="small">Recomendación: guarda los respaldos en un repositorio seguro fuera del servidor web y controla la distribución del archivo ZIP.</div>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
