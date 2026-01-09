<?php
$host = "192.168.50.10";
$user = "admin_web";
$pass = "password123";
$db   = "consultoria_erp";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error crítico de conexión: " . $conn->connect_error);
}
// Forzar caracteres latinos (tildes, ñ)
$conn->set_charset("utf8");
?>