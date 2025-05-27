<?php
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}
$opcion=$_GET['op']; // 1 modificar, 2 agregar
$id_producto=isset($_GET['i']) ? $_GET['i'] : "";
$nombre_producto=isset($_GET['n']) ? $_GET['n'] : "";
$descripcion_producto=isset($_GET['d']) ? $_GET['d'] : "";
$cantidad_disponible=isset($_GET['c']) ? $_GET['c'] : "";
$precio_producto=isset($_GET['p']) ? $_GET['p'] : "";
$fabricante=isset($_GET['f']) ? $_GET['f'] : "";
$origen=isset($_GET['o']) ? $_GET['o'] : "";
$categoria=isset($_GET['cat']) ? $_GET['cat'] : "";


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "head_html.php";
    $titulo=$opcion==1?"Modificar producto":"Agregar producto";?>
        <title><?= $titulo?></title>
    <!-- icono -->
    <link rel="shortcut icon" href="./img/logo.jpg">
    <!-- normalize -->
    <link rel="preload" href="../css/normalize.css" as="style">
    <link rel="stylesheet" href="../css/normalize.css">
    <!-- estilos -->
    <link rel="preload" href="../css/styles.css" as="style">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="preload" href="../css/styles_mod_crear_prod.css" as="style">
    <link rel="stylesheet" href="../css/styles_mod_crear_prod.css">
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
                    <li class="active">
                        <a href="../php/cerrar_sesion.php"><span class="glyphicon glyphicon-cog"></span> <?= $titulo?></a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                            aria-expanded="false">Modo dios 😎 <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="../php/consultar_historial.php"><span class="glyphicon glyphicon-list"></span> Consultar historial</a></li>
                            <li><a href="../php/modificar_productos.php"><span class="glyphicon glyphicon-cog"></span> Modificar productos</a></li>
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
    <h1><?= $titulo?></h1>
    <?php $directorio=$opcion==1?"hacer_modificacion.php":"hacer_registro.php";?>
    <?php $_SESSION['sesion_personal']['id_producto']=$id_producto;?>

    <form action="<?= $directorio?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nombre_producto">Nombre</label>
        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" value="<?= $nombre_producto?>">
    </div>
    <div class="form-group">
        <label for="descripcion_producto">Descripción</label>
        <textarea class="form-control" rows="3" id="descripcion_producto" name="descripcion_producto" ><?= $descripcion_producto?></textarea>
    </div>
    <div class="form-group">
        <label for="cantidad_disponible">Cantidad</label>
        <input type="number" class="form-control" id="cantidad_disponible" name="cantidad_disponible" value="<?= $cantidad_disponible?>">
    </div>
    <div class="form-group">
        <label for="precio_producto">Precio</label>
        <input type="number" step="any" class="form-control" id="precio_producto" name="precio_producto" value="<?= $precio_producto?>">
    </div>
    <div class="form-group">
        <label for="fabricante">Fabricante</label>
        <input type="text" class="form-control" id="fabricante" name="fabricante" value="<?= $fabricante?>">
    </div>
    <div class="form-group">
        <label for="origen">Origen</label>
        <input type="text" class="form-control" id="origen" name="origen" value="<?= $origen?>">
    </div>
    <div class="form-group">
        <label for="categoria">Categoria</label>
        <input type="text" class="form-control" id="categoria" name="categoria" value="<?= $categoria?>">
    </div>
    <?php if($opcion==2):?>
    <div class="form-group">
        <label for="imagen_producto">Imágen</label>
        <input type="file" id="imagen_producto" name="imagen_producto">
    </div>
    <?php endif;?>
    <button type="submit" class="btn btn-default boton"><?= $titulo?></button>
    </form>
    
</body>

</html>
