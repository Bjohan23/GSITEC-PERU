<?php
require_once("../config/config.php");
// Variables que contendrán un posible mensaje de error
$nombreErr = $contraErr = "";
// Variables que guardan el contenido de los campos del formulario
$nombre = $contra = "";
$hay_errores=false;

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nombre"])) {
        $nombreErr = "* Nombre requerido";
        $hay_errores = true;
    } else {
        $nombre = test_input($_POST["nombre"]);
    }
    if (empty($_POST["contrasena"])) {
        $contraErr = "* Contraseña requerida";
        $hay_errores = true;
    } else {
        $contra = test_input($_POST["contrasena"]);
    }

    // verificacion de errores y creacion de sesion
    if (!$hay_errores) { // si no hay errores
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        } else {
            //obtener id
            $result = mysqli_query($con, "SELECT id_usuario, super_usuario, nombre_usuario FROM usuario where nombre_usuario='$nombre' and contrasena='$contra' ;");
            $id=$super=$nombre="";
            while ($row = mysqli_fetch_array($result)) {
                $id=$row['id_usuario'];
                $super=$row['super_usuario'];
                $nombre=$row['nombre_usuario'];
            }

            //cerrar conexión
            mysqli_close($con);
            if ($id!=="") {
                //session
                session_start();
                $_SESSION['sesion_personal']=array();
                $_SESSION['sesion_personal']['id']=$id;
                $_SESSION['sesion_personal']['super']=$super;
                $_SESSION['sesion_personal']['nombre']=$nombre;
                //enviar a index
                header("Location: ../index.php");
            }else{
                $nombreErr="ALGUN ERROR";
            }
        }
    }
}
