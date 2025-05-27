<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}
$id_producto=$_SESSION['sesion_personal']['id_producto'];
unset($_SESSION['sesion_personal']['id_producto']);
$nombre_producto=$_POST['nombre_producto'];
$descripcion_producto=$_POST['descripcion_producto'];
$cantidad_disponible=$_POST['cantidad_disponible'];
$precio_producto=$_POST['precio_producto'];
$fabricante=$_POST['fabricante'];
$origen=$_POST['origen'];
$categoria=$_POST['categoria'];

echo "modificar<br>";
echo "id: ".$id_producto;
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "<br>";
echo "<br>";


$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $query="UPDATE producto
            SET precio_producto=$precio_producto,fabricante='$fabricante',origen='$origen',nombre_producto='$nombre_producto',cantidad_disponible=$cantidad_disponible,descripcion_producto='$descripcion_producto',categoria='$categoria'
            WHERE id_producto=$id_producto
            ;";
    mysqli_query($con, $query);

    header('Location: ./modificar_productos.php');
endif;