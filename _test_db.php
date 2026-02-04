<?php
require_once __DIR__ . "/config_denuncia.php";
$h=$DENUNCIA_DB['host']; $u=$DENUNCIA_DB['user']; $p=$DENUNCIA_DB['pass']; $n=$DENUNCIA_DB['name']; $port=$DENUNCIA_DB['port'];

$conn = mysqli_connect($h,$u,$p,$n,$port);
if(!$conn) die("FAIL: ".mysqli_connect_error());
echo "OK - connected to port $port";
