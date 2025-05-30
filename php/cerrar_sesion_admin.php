<?php
session_start();

// Borrar las variables de sesión de administrador
unset($_SESSION['sesion_admin']);

if(session_destroy()){
    header("Location: ./iniciar_sesion_admin.php");
} else {
    header("Location: ./panel_admin.php");
}
exit();
?>
