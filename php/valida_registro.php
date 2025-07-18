<?php
require_once("../config/config.php");

// Variables que contendrán un posible mensaje de error
$nombreErr = $contraErr = $fechanacimientoErr = $correoErr = $ntarjetaErr = $addressErr = "";
// Variables que guardan el contenido de los campos del formulario
$nombre = $contra = $correo = $ntarjeta = $address = "";
$fechanacimiento = "1969-12-31";
$hay_errores = false;

function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function checkemail($str){
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
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
        // Hashear la contraseña
        $contra_hash = password_hash($contra, PASSWORD_DEFAULT);
    }
    
    date_default_timezone_set("America/Mexico_City");
    if (($_POST["fnac"]) == "1969-12-31") {
        $fechanacimientoErr = "* Fecha requerida";
        $hay_errores = true;
    } else {
        $fechanacimiento = date("Y-m-d", strtotime($_POST["fnac"]));
    }
    
    if (empty($_POST["correo"])) {
        $correoErr = "* Email requerido";
        $hay_errores = true;
    } else {
        $correo = test_input($_POST["correo"]);
        if (!checkemail($correo)) {
            $correoErr = "* Email inválido";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["numero_tarjeta"])) {
        $ntarjetaErr = "* Número de tarjeta requerido";
        $hay_errores = true;
    } else {
        $ntarjeta = test_input($_POST["numero_tarjeta"]);
    }
    
    if (empty($_POST["direccion"])) {
        $addressErr = "* Dirección requerida";
        $hay_errores = true;
    } else {
        $address = test_input($_POST["direccion"]);
    }
    
    if (!$hay_errores) {
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            $nombreErr = "Error de conexión a la base de datos";
            $hay_errores = true;
        } else {
            // Verificar si el usuario ya existe
            $stmt = mysqli_prepare($con, "SELECT id_usuario FROM usuario WHERE nombre_usuario = ? OR correo = ?");
            mysqli_stmt_bind_param($stmt, "ss", $nombre, $correo);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_fetch_array($result)) {
                $nombreErr = "* El usuario o correo ya existe";
                $hay_errores = true;
            } else {
                // Registrar usuario
                $stmt = mysqli_prepare($con, "INSERT INTO usuario (nombre_usuario, fecha_nacimiento, correo, contrasena, numero_tarjeta, direccion) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssssss", $nombre, $fechanacimiento, $correo, $contra_hash, $ntarjeta, $address);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Obtener datos del usuario recién creado
                    $stmt2 = mysqli_prepare($con, "SELECT id_usuario, super_usuario, nombre_usuario FROM usuario WHERE nombre_usuario = ?");
                    mysqli_stmt_bind_param($stmt2, "s", $nombre);
                    mysqli_stmt_execute($stmt2);
                    $result2 = mysqli_stmt_get_result($stmt2);
                    
                    if ($row = mysqli_fetch_array($result2)) {
                        $id = $row['id_usuario'];
                        $super = $row['super_usuario'];
                        $nombre_usuario = $row['nombre_usuario'];
                        
                        // Iniciar sesión solo si no está ya iniciada
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        // Crear sesión
                        $_SESSION['sesion_personal'] = array();
                        $_SESSION['sesion_personal']['id'] = $id;
                        $_SESSION['sesion_personal']['nombre'] = $nombre_usuario;
                        $_SESSION['sesion_personal']['super'] = $super;
                        
                        // Cerrar conexión
                        mysqli_close($con);
                        
                        // Redirigir a index
                        header("Location: ../index.php");
                        exit();
                    }
                } else {
                    $nombreErr = "Error al registrar el usuario";
                    $hay_errores = true;
                }
            }
            
            // Cerrar conexión
            mysqli_close($con);
        }
    }
}
?>