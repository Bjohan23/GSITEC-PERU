<?php
session_start();
// borrar las variables de sesión
unset($_SESSION['sesion_personal']);

if(session_destroy()){
    header("Location: ../index.php");
}
