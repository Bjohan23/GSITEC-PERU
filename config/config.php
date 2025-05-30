<?php
// Configuración de output buffering para evitar problemas con headers
if (!ob_get_level()) {
    ob_start();
}

$db_hostname="localhost:3307";
$db_username="root";
$db_password="root";
$db_name="tienda_online";
// Detectar automáticamente la URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = '/PAF/';
define('BASE_URL', $protocol . '://' . $host . $path);
?>