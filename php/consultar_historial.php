<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $historial=[];
    $query="SELECT
            h.id_producto,
            u.nombre_usuario,
            p.nombre_producto,
            p.precio_producto,
            h.cantidad_comprada,
            h.fecha_compra
        FROM historial_compras AS h
        JOIN usuario AS u
            ON h.id_usuario = u.id_usuario
        JOIN producto AS p
            ON p.id_producto = h.id_producto;";
    $result = mysqli_query($con, $query);
    $n_productos=mysqli_num_rows($result);
    while ($row = mysqli_fetch_array($result)):
        $precio=$row['precio_producto'];
        $cantidad=$row['cantidad_comprada'];
        $total=$precio*$cantidad;
        array_push($historial, array(
            "id_producto"=>$row['id_producto'],
            "nombre_usuario"=>$row['nombre_usuario'],
            "nombre_producto"=>$row['nombre_producto'],
            "precio_producto"=>$precio,
            "cantidad_comprada"=>$cantidad,
            "total"=>$total,
            "fecha"=>$row['fecha_compra'],
        ));
    endwhile;
    mysqli_close($con);
endif;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "head_html.php";?>
    <title>Modo dios</title>
    <!-- icono -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- normalize -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- estilos -->
    <link rel="preload" href="../css/estilo_generico.css" as="style">
    <link rel="stylesheet" href="../css/estilo_generico.css">
    <link rel="preload" href="../css/styles-consultar_historial.css" as="style">
    <link rel="stylesheet" href="../css/styles-consultar_historial.css">
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
                    <li>
                        <span class="navbar-text">Sesión iniciada como
                            <a href="../php/perfil.php"
                                class="navbar-link"><u><?=$_SESSION['sesion_personal']['nombre']?></u>
                            </a>
                        </span>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($_SESSION['sesion_personal']['super']==1): ?>

                    <li class="dropdown active">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">Modo dios 😎 <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><span class="glyphicon glyphicon-list"></span> Consultar historial</a></li>
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
    <?php if ($n_productos==0) :?>
    <h1 class="h1">NO HAY COMPRAS HECHAS AÚN</h1>
    <?php else: ?>
    <h1>Historial de compras general</h1>
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>Imagen producto</th>
                <th>Nombre usuario</th>
                <th>Nombre producto</th>
                <th>Precio producto</th>
                <th>Cantidad comprada</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
            <?php foreach ($historial as $producto): ?>
            <tr>
                <td>
                    <img src="../img/productos/<?= $producto["id_producto"]; ?>.png" alt="producto <?= $producto["nombre_producto"]; ?>" class="imagen">
                </td>
                <td><?=$producto['nombre_usuario']; ?></td>
                <td><?=$producto['nombre_producto']; ?></td>
                <td>$<?= number_format(floatval($producto['precio_producto'])); ?></td>
                <td><?=$producto['cantidad_comprada']?></td>
                <td>$<?= number_format(floatval($producto['total'])); ?></td>
                <td><?=$producto['fecha'];?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?><br>
</body>

</html>