<?php
require_once __DIR__ . '/_admin_bootstrap.php';
$db = db_conn();
admin_require_login($db);
admin_require_role('ADMIN');

$csrf = admin_csrf_token();

function backup_mysqldump_to_file(string $outFile): void {
  global $DENUNCIA_DB;

  $host = $DENUNCIA_DB['host'] ?? 'localhost';
  $user = $DENUNCIA_DB['user'] ?? 'root';
  $pass = $DENUNCIA_DB['pass'] ?? '';
  $name = $DENUNCIA_DB['name'] ?? '';
  $port = (int)($DENUNCIA_DB['port'] ?? 3306);

  // XAMPP Windows default (đổi nếu cần)
  $mysqldump = $DENUNCIA_DB['mysqldump_path'] ?? 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

  if (!is_file($mysqldump)) {
    // fallback: thử gọi mysqldump trong PATH
    $mysqldump = 'mysqldump';
  }

  $cmd = escapeshellarg($mysqldump)
    . " --host=" . escapeshellarg($host)
    . " --port=" . escapeshellarg((string)$port)
    . " --user=" . escapeshellarg($user)
    . " --password=" . escapeshellarg($pass)
    . " --default-character-set=utf8mb4 --single-transaction --quick --routines --events "
    . escapeshellarg($name)
    . " > " . escapeshellarg($outFile);

  $ret = 0;
  @system($cmd, $ret);

  if ($ret !== 0 || !is_file($outFile) || filesize($outFile) < 50) {
    throw new Exception("mysqldump failed. Hãy kiểm tra đường dẫn mysqldump_path hoặc quyền chạy system().");
  }
}

function zip_dir(ZipArchive $zip, string $dir, string $baseInZip): void {
  $dir = rtrim($dir, '/\\');
  if (!is_dir($dir)) return;

  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($it as $file) {
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

if (isset($_GET['do']) && $_GET['do'] === 'download') {
  if (!admin_csrf_check($_GET['csrf'] ?? null)) {
    http_response_code(403);
    exit("CSRF invalid");
  }

  $include_uploads = (($_GET['uploads'] ?? '1') === '1');

  $tmp = sys_get_temp_dir();
  $sqlFile = $tmp . '/denuncias_portal_' . date('Ymd_His') . '.sql';
  $zipFile = $tmp . '/denuncias_backup_' . date('Ymd_His') . '.zip';

  backup_mysqldump_to_file($sqlFile);

  $zip = new ZipArchive();
  if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new Exception("Cannot create zip");
  }

  $zip->addFile($sqlFile, 'db.sql');

  if ($include_uploads) {
    $root = realpath(__DIR__ . '/..');
    $uploads = $root . DIRECTORY_SEPARATOR . 'uploads';
    zip_dir($zip, $uploads, 'uploads');
  }

  $zip->close();

  audit_log($db, null, 'ADMIN_BACKUP_DOWNLOAD', 'SYSTEM', admin_user()['email'] ?? null, ['uploads'=>$include_uploads]);

  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="denuncias_backup_' . date('Ymd_His') . '.zip"');
  header('Content-Length: ' . filesize($zipFile));
  readfile($zipFile);

  @unlink($sqlFile);
  @unlink($zipFile);
  exit;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Backup</title>
  <style>
    body{font-family:Arial;padding:24px;background:#f5f6f8}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px;margin-top:14px}
    .btn{display:inline-block;padding:10px 12px;border-radius:10px;border:1px solid #d1d5db;text-decoration:none;color:#111827;font-weight:800}
  </style>
</head>
<body>
  <h2>Backup</h2>
  <div class="card">
    <div style="margin-bottom:10px">
      Tải backup dạng <b>ZIP</b> gồm <b>db.sql</b> và (tuỳ chọn) <b>uploads/</b>.
    </div>

    <a class="btn" href="<?= h(admin_link('/admin/backup.php?do=download&uploads=1&csrf='.$csrf)) ?>">Download DB + Uploads</a>
    <a class="btn" href="<?= h(admin_link('/admin/backup.php?do=download&uploads=0&csrf='.$csrf)) ?>">Download DB only</a>

    <div style="margin-top:12px;font-size:12px;color:#6b7280">
      Lưu ý: khi deploy thật, nên giới hạn truy cập /admin bằng IP hoặc BasicAuth + HTTPS.
    </div>
  </div>

  <div class="card">
    <a class="btn" href="<?= h(admin_link('/admin/index.php')) ?>">← Back</a>
  </div>
</body>
</html>
