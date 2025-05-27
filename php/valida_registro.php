<!-- valicación de datos -->
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
    }
    date_default_timezone_set("America/Mexico_City");
    if (($_POST["fnac"]) == "1969-12-31") { // esta vacio ?
        $fechanacimientoErr = "* Fecha requerida";
        $hay_errores = true;
    } else {
        $fechanacimiento = date("Y-m-d", strtotime($_POST["fnac"]));
    }
    if (empty($_POST["correo"])) {
        $correoErr = "* Email requerido";
        if (!checkemail(test_input($_POST["correo"]))) {
            $correoErr = "* Email invalido";
            $hay_errores = true;
        }
    } else {
        $correo = test_input($_POST["correo"]);
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
    if (!$hay_errores) { // si no hay errores
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        } else {
            // registrar usuario
            $query="insert into usuario (nombre_usuario, fecha_nacimiento, correo, contrasena, numero_tarjeta, direccion) values ('$nombre','$fechanacimiento','$correo','$contra','$ntarjeta','$address');";
            mysqli_query($con, $query);
            //obtener id
            $result = mysqli_query($con, "SELECT id_usuario, super_usuario, nombre_usuario FROM usuario where nombre_usuario='$nombre' and contrasena='$contra' ;");
            while ($row = mysqli_fetch_array($result)) {
                $id=$row['id_usuario'];
                $super=$row['super_usuario'];
                $nombre=$row['nombre_usuario'];
            }

            //cerrar conexión
            mysqli_close($con);
            //session
            session_start();
            $_SESSION['sesion_personal']=array();
            $_SESSION['sesion_personal']['id']=$id;
            $_SESSION['sesion_personal']['nombre']=$nombre;
            $_SESSION['sesion_personal']['super']=$super;
            //enviar a index
            header("Location: ../index.php");
        }
    }
}
?>