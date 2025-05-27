<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}
$id_usuario=$_SESSION['sesion_personal']['id'];
$vaciar_carrito=$_GET['v'];

$arreglo=array(); // arreglo de productos con sus cantidad y id pe [0]=1, 2
foreach ($_GET['datos'] as $value) {
    $subarreglo=explode(",",$value);
    array_push($arreglo,$subarreglo);
}

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    foreach ($arreglo as $indice => $valor) {
        $cantidad_seleccionada=$valor[0];  //  el primer [0] es el primero producto
        $id_producto=$valor[1];
        
        // disminuir el numero de elementos
        $result=mysqli_query($con, "SELECT cantidad_disponible FROM producto WHERE id_producto=$id_producto;");
        while ($row = mysqli_fetch_array($result)){
            $nueva_cantidad=((int)$row['cantidad_disponible']) - $cantidad_seleccionada;
        }
        $actualizacion_de_cantidad=mysqli_query($con,"UPDATE producto	SET cantidad_disponible=$nueva_cantidad WHERE id_producto=$id_producto;");
        
        // registrar la compra en el historial de compras
        date_default_timezone_set("America/Mexico_City");
        $fecha_actual=date("Y-m-d");
        $query="INSERT INTO historial_compras (id_usuario,id_producto,cantidad_comprada,fecha_compra) 
            VALUES ($id_usuario,$id_producto,$cantidad_seleccionada,'$fecha_actual');";
        $otro=mysqli_query($con, $query);
    }

    if((int)$vaciar_carrito){
        mysqli_query($con, "DELETE FROM carrito;");
    }

    mysqli_close($con);
    header('Location: ./historial_individual.php');
endif;
