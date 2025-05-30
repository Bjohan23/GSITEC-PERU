<?php
require_once("../config/config.php");

function hayAdministradores() {
    global $db_hostname, $db_username, $db_password, $db_name;
    
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    if (mysqli_connect_errno()) {
        return false;
    }
    
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM administradores WHERE activo = 1");
    $row = mysqli_fetch_array($result);
    mysqli_close($con);
    
    return $row['total'] > 0;
}

function redirigirSiNoHayAdmins() {
    if (!hayAdministradores()) {
        header("Location: ./crear_primer_admin.php");
        exit();
    }
}
?>
