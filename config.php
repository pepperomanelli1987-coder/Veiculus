<?php
$host = "mysql-222f42a0-pepperomanelli1987-28e8.k.aivencloud.com";
$port = 13039;
$user = "avnadmin";
$password = "DB_PASSWORD";
$database = "defaultdb";

// SSL
$ssl_ca = __DIR__ . "/ca.pem";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);
?>
