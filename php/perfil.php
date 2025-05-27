<?php
require_once("../config/config.php");
session_start();

if(!isset($_SESSION['sesion_personal'])){
    header("Location: ./iniciar_sesion.php");
}
include "./head_html.php";
$id_usuario=$_SESSION['sesion_personal']['id'];
$nombre_usuario=$_SESSION['sesion_personal']['nombre'];

// Creación de la lista del información del usuario
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
// verificar connection con la BD
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $usuario=[];
    $result = mysqli_query($con, "SELECT * FROM usuario WHERE id_usuario=".$id_usuario.";");
    while ($row = mysqli_fetch_array($result)):
        array_push($usuario, array(
            "correo"=>$row['correo'],
            "n_tarjeta"=>$row['numero_tarjeta'],
            "direccion"=>$row['direccion'],
            "fechanac"=>$row['fecha_nacimiento'],
            "contrasena"=>$row['contrasena']
        ));
    endwhile;

    // cerrar conexión
    mysqli_close($con);
endif;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Historial de compra</title>
    <!-- icono -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- normalize -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- estilos -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-perfil.css" as="style">
    <link rel="stylesheet" href="../css/styles-perfil.css">
</head>
<!-- barra de navegación -->
<header>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <!-- responsividad del header, marca -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <!-- marca -->
                <a class="navbar-brand" href="../index.php">GSITEC PERU</a>
            </div>

            <div class="collapse navbar-collapse" id="myNavbar">
                <!-- menú izquierdo-->
                <ul class="nav navbar-nav">
                    <li><a href="../index.php">Lista de productos</a></li>
                    <li class="navbar-text active">
                        <a href="#" class="navbar-link">
                            Sesión iniciada como
                            <u><?=$_SESSION['sesion_personal']['nombre']?></u>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($_SESSION['sesion_personal']['super']==1): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">Modo dios 😎 <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="../php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span>
                                    Consultar historial</a></li>
                            <li><a href="../php/modificar_productos.php"><span class="glyphicon glyphicon-cog"></span>
                                    Modificar productos</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="../php/cerrar_sesion.php"><span class="glyphicon glyphicon-log-out"></span> Cerrar
                            sesión</a>
                    </li>
                    <li>
                        <a href="../php/carrito.php"><span class="glyphicon glyphicon-shopping-cart"></span> Carrito
                            de compras</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<body class="container">
    <h1>Perfil de usuario</h1>
    <div class="padre">
        <section>
            <p><b>Dirección:</b> <?= $usuario[0]['direccion'];?></p>
            <p><b>Número de tarjeta:</b> <?= $usuario[0]['n_tarjeta'];?></p>
            <p><b>Correo:</b> <?= $usuario[0]['correo'];?></p>
        </section>
        <section>
            <p><b>Nombre:</b> <?= $nombre_usuario;?></p>
            <p><b>Fecha nacimiento:</b> <?= $usuario[0]['fechanac'];?></p>
        </section>
    </div>
    <a href="historial_individual.php"><input type="submit" class="btn btn-default boton"
            value="Historial de compras "></a>
</body>

</html>