<?php

require_once '../pdo.php';

$pdo = new madPDOFramework( 'mysql:dbname=mad;host=localhost', 'root' );
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$conn = $pdo;

$pdo->query( 'lol' );

?>
