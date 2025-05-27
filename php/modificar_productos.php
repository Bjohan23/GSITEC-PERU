<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

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
    <link rel="preload" href="../css/styles-modificar_productos.css" as="style">
    <link rel="stylesheet" href="../css/styles-modificar_productos.css">
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
                            <li><a href="../php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span> Consultar historial</a></li>
                            <li><a href="#"><span class="glyphicon glyphicon-cog"></span> Modificar productos</a></li>
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
    <div class="mismo-nivel">
        <h1>Modificar productos</h1>
        <a href="modificar_crear_producto.php?op=2"><input type="submit" value="Agregar Producto" class="btn btn-default boton"></a>
    </div>
    <!-- lista de productos -->
    <main>
        <!-- lista de productos automatica -->
        <?php
        // Crear una conexión
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
            
        // verificar connection con la BD
        if (mysqli_connect_errno()) :
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        else:
            $result = mysqli_query($con, "SELECT * FROM producto ORDER BY id_producto DESC;");
            $n_productos=mysqli_num_rows($result);
            if($n_productos>0):?>
                <div class="table-responsive">
                <table  class="table table-hover table-bordered">
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Fabricante</th>
                    <th>Origen</th>
                    <th>Categoria</th>
                    <th></th>
                </tr>
                <?php
                while ($row = mysqli_fetch_array($result)): ?>
                    <tr>
                        <td><img class="imagen" src="../img/productos/<?= $row['id_producto'] ?>.png" alt="<?= $row['nombre_producto']?>"></td>
                        <td><?= $row['nombre_producto']?></td>
                        <td><?= $row['descripcion_producto']?></td>
                        <td><?= $row['cantidad_disponible']?></td>
                        <td><?= $row['precio_producto']?></td>
                        <td><?= $row['fabricante']?></td>
                        <td><?= $row['origen']?></td>
                        <td><?= $row['categoria']?></td>
                        <td>
                            <a href="modificar_crear_producto.php?op=1&i=<?= urlencode($row['id_producto']) ?>&n=<?= urlencode($row['nombre_producto'])?>&d=<?= urlencode($row['descripcion_producto'])?>&c=<?= urlencode($row['cantidad_disponible'])?>&p=<?= urlencode($row['precio_producto'])?>&f=<?= urlencode($row['fabricante'])?>&o=<?= urlencode($row['origen'])?>&cat=<?= urlencode($row['categoria'])?>">
                                <input type="submit" value="Cambiar" class="btn btn-default btn-sm">
                            </a>
                        </td>
                    </tr>
                    <?php
                endwhile;?>
                
                <?php
            else:?>
                <h1>NO HAY PRODUTOS EXISTENTES</h1>
            <?php
            endif;
            mysqli_close($con);
        endif;
        ?>
    </main>

</body>

</html>