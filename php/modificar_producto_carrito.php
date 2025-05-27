<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

print_r($_GET);
$signo=(int) $_GET['signo']; //1 0
$id_carrito=(int) $_GET['id_carrito']; 
$disponibles=(int) $_GET['disp']; 
$cantidad=(int) $_GET['cant'];

if((($cantidad+1) > ($disponibles)) && ($signo == 1)){ // rebasa hacia arriba
    header('Location: carrito.php');
}else if( (($cantidad-1) == 0) && ($signo == 0)){ // llega a 0
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    if (mysqli_connect_errno()) :
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    else:
        $result = mysqli_query($con, "DELETE FROM carrito WHERE id_carrito=$id_carrito;");
        mysqli_close($con);
        header('Location: ./carrito.php');
    endif;
}else{ // disminuir o aumentar bien
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    if (mysqli_connect_errno()) :
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    else:
        $nueva_cantidad = ($signo == 1) ? ($cantidad+1) : ($cantidad-1);
        $result = mysqli_query($con, "UPDATE carrito SET cantidad_seleccionada=$nueva_cantidad WHERE id_carrito=$id_carrito;");
        mysqli_close($con);
        header('Location: ./carrito.php');
    endif;
}