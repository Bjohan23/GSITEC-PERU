<?php
require_once("../config/config.php");

// Variables que contendrán un posible mensaje de error
$nombreErr = $contraErr = "";
// Variables que guardan el contenido de los campos del formulario
$nombre = $contra = "";
$hay_errores = false;

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
    if (!$hay_errores) {
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            $nombreErr = "Error de conexión a la base de datos";
            $hay_errores = true;
        } else {
            // Obtener datos del usuario (incluir contraseña hasheada para verificar)
            $stmt = mysqli_prepare($con, "SELECT id_usuario, super_usuario, nombre_usuario, contrasena FROM usuario WHERE nombre_usuario = ?");
            mysqli_stmt_bind_param($stmt, "s", $nombre);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_array($result)) {
                // Verificar contraseña hasheada
                if (password_verify($contra, $row['contrasena'])) {
                    $id = $row['id_usuario'];
                    $super = $row['super_usuario'];
                    $nombre_usuario = $row['nombre_usuario'];
                    
                    // Iniciar sesión solo si no está ya iniciada
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Crear sesión de usuario
                    $_SESSION['sesion_personal'] = array();
                    $_SESSION['sesion_personal']['id'] = $id;
                    $_SESSION['sesion_personal']['super'] = $super;
                    $_SESSION['sesion_personal']['nombre'] = $nombre_usuario;
                    
                    // Cerrar conexión
                    mysqli_close($con);
                    
                    // Redirigir a index
                    header("Location: ../index.php");
                    exit();
                } else {
                    $nombreErr = "Usuario o contraseña incorrectos";
                    $hay_errores = true;
                }
            } else {
                $nombreErr = "Usuario o contraseña incorrectos";
                $hay_errores = true;
            }
            
            // Cerrar conexión
            mysqli_close($con);
        }
    }
}
?>