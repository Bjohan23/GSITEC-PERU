<?php
// Configuración de output buffering para evitar problemas con headers
if (!ob_get_level()) {
    ob_start();
}

$db_hostname="localhost:3307";
$db_username="root";
$db_password="root";
$db_name="tienda_online";

?>