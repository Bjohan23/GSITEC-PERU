<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

unset($_SESSION['sesion_personal']['id_producto']);
$nombre_producto=$_POST['nombre_producto'];
$descripcion_producto=$_POST['descripcion_producto'];
$cantidad_disponible=$_POST['cantidad_disponible'];
$precio_producto=$_POST['precio_producto'];
$fabricante=$_POST['fabricante'];
$origen=$_POST['origen'];
$categoria=$_POST['categoria'];

echo "agregar<br>";
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";
echo "<br>";
echo "<br>";

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $query="INSERT INTO producto (nombre_producto,descripcion_producto,cantidad_disponible,precio_producto,fabricante,origen,categoria)
            VALUES ('$nombre_producto','$descripcion_producto',$cantidad_disponible,$precio_producto,'$fabricante','$origen','$categoria')
            ;";
    mysqli_query($con, $query);
    $id = mysqli_query($con, "SELECT LAST_INSERT_ID();");
    mysqli_close($con);

    $iddos = $id->fetch_array(MYSQLI_NUM);
    
    $nombre_imagen=$iddos[0];
    $ruta_imagen=$_FILES["imagen_producto"]["tmp_name"];
    $ruta_a_subir="../img/productos/$nombre_imagen.png";
    move_uploaded_file($ruta_imagen,$ruta_a_subir);


    header('Location: ./modificar_productos.php');
endif;