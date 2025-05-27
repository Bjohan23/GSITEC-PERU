<?php
require_once("../config/config.php");
session_start();

if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

// Crear una conexión
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// verificar connection con la BD
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $result = mysqli_query($con, "DELETE FROM carrito;");

    // cerrar conexión
    mysqli_close($con);
    header('Location: ./carrito.php');
endif;