<?php
require_once("../config/config.php");
session_start();
$id_producto=$_GET["id"];

if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

// Crear una conexión
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// verificar connection con la BD
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $info_del_producto=[];
    $result = mysqli_query($con, "SELECT * FROM producto WHERE id_producto=".$id_producto.";");
    $n_productos=mysqli_num_rows($result);
    while ($row = mysqli_fetch_array($result)):
        array_push($info_del_producto, array(
            "id"=>$row['id_producto'],
            "nombre"=>$row['nombre_producto'],
            "descripcion"=>$row['descripcion_producto'],
            "disponibles"=>$row['cantidad_disponible'],
            "precio"=>$row['precio_producto'],
            "fabricante"=>$row['fabricante'],
            "origen"=>$row['origen'],
            "categoria"=>$row['categoria'],
        ));
    endwhile;
    // cerrar conexión
    mysqli_close($con);
endif;
?>

<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"?>
    <title>GSITEC PERU - <?= isset($info_del_producto[0]) ? $info_del_producto[0]["nombre"] : 'Producto' ?></title>
    <!-- icono -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    
    <!-- JavaScript -->
    <script type="text/javascript" src="../js/comprar_agregarcarrito.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="../index.php" class="text-white text-xl font-bold">GSITEC PERU</a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    <span class="text-cyan-400 font-semibold">Información del producto</span>
                    
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-4">
                        <span class="text-white">
                            Hola, <a href="../php/perfil.php" class="font-semibold text-cyan-400 hover:text-cyan-300 transition-colors duration-200"><?=$_SESSION['sesion_personal']['nombre']?></a>
                        </span>
                        
                        <?php if($_SESSION['sesion_personal']['super']==1): ?>
                        <!-- Admin dropdown -->
                        <div class="relative group">
                            <button class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                                Modo dios 😎
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <a href="../php/consultar_historial.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-techblue-50 dark:hover:bg-techblue-900 rounded-t-lg">
                                    📋 Consultar historial
                                </a>
                                <a href="../php/modificar_productos.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-techblue-50 dark:hover:bg-techblue-900 rounded-b-lg">
                                    ⚙️ Modificar productos
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <a href="../php/carrito.php" class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                            </svg>
                            Carrito
                        </a>
                        <a href="../php/cerrar_sesion.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    <span class="text-cyan-400 font-semibold">📋 Información del producto</span>
                    <span class="text-cyan-400">Hola, <?=$_SESSION['sesion_personal']['nombre']?></span>
                    <?php if($_SESSION['sesion_personal']['super']==1): ?>
                    <a href="../php/consultar_historial.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📋 Consultar historial</a>
                    <a href="../php/modificar_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">⚙️ Modificar productos</a>
                    <?php endif; ?>
                    <a href="../php/carrito.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🛒 Carrito</a>
                    <a href="../php/cerrar_sesion.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if(isset($info_del_producto[0])): ?>
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="../index.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-techblue-600 dark:text-gray-400 dark:hover:text-techblue-400">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                            <?= $info_del_producto[0]["categoria"] ?>
                        </span>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400 truncate">
                            <?= $info_del_producto[0]["nombre"] ?>
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="aspect-square relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                        <img 
                            src="../img/productos/<?= $info_del_producto[0]["id"] ?>.png" 
                            alt="<?= htmlspecialchars($info_del_producto[0]["nombre"]) ?>"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgZmlsbD0iIzNCODJGNiIvPjx0ZXh0IHg9IjIwMCIgeT0iMjEwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                        >
                        <!-- Stock Badge -->
                        <div class="absolute top-4 right-4">
                            <?php if($info_del_producto[0]["disponibles"] > 10): ?>
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    ✅ En Stock
                                </span>
                            <?php elseif($info_del_producto[0]["disponibles"] > 0): ?>
                                <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    ⚠️ Pocas unidades
                                </span>
                            <?php else: ?>
                                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    ❌ Agotado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        <?= htmlspecialchars($info_del_producto[0]["nombre"]) ?>
                    </h1>
                    
                    <!-- Price -->
                    <div class="flex items-baseline space-x-2 mb-6">
                        <span class="text-4xl font-bold text-techblue-600 dark:text-techblue-400">
                            $<?= number_format(floatval($info_del_producto[0]["precio"]), 2, '.', ',') ?>
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 line-through text-xl">
                            $<?= number_format(floatval($info_del_producto[0]["precio"]) * 1.2, 2, '.', ',') ?>
                        </span>
                        <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                            17% OFF
                        </span>
                    </div>

                    <!-- Stock Info -->
                    <div class="bg-techblue-50 dark:bg-techblue-900 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-techblue-800 dark:text-techblue-200 font-medium">
                                Disponibles: <?= $info_del_producto[0]["disponibles"] ?> unidades
                            </span>
                            <svg class="w-5 h-5 text-techblue-600 dark:text-techblue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h2zm4-3a1 1 0 00-1 1v1h2V4a1 1 0 00-1-1zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <?php if($info_del_producto[0]["disponibles"] > 0): ?>
                <!-- Quantity Selector -->
                <div class="space-y-4">
                    <label for="cantidad_seleccionada" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Seleccionar cantidad:
                    </label>
                    <select 
                        id="cantidad_seleccionada" 
                        class="w-full md:w-32 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                    >
                        <?php for ($i=1; $i <= min($info_del_producto[0]["disponibles"], 10); $i++): ?>
                        <option value="<?=$i?>"><?=$i?></option>
                        <?php endfor ?>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <button 
                        onclick="enviarAPantallaDeCompraUno(<?=$id_producto?>)" 
                        class="w-full bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zm0 3a1 1 0 012 0 1 1 0 11-2 0zm4 0a1 1 0 012 0 1 1 0 11-2 0z" clip-rule="evenodd"></path>
                        </svg>
                        Comprar Ahora
                    </button>
                    
                    <button 
                        onclick="agregarAlCarrito(<?=$id_producto?>)" 
                        class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                        </svg>
                        Agregar al Carrito
                    </button>
                </div>
                <?php else: ?>
                <!-- Out of Stock -->
                <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-red-800 dark:text-red-200 font-medium">
                            Producto agotado - Te notificaremos cuando esté disponible
                        </span>
                    </div>
                    <button class="mt-3 bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed opacity-50" disabled>
                        No disponible
                    </button>
                </div>
                <?php endif; ?>

                <!-- Security Features -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Garantía de 12 meses
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        Compra 100% segura
                    </div>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-purple-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        Envío gratis por compras > $500
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                Descripción detallada
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Description -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Descripción del producto
                        </h3>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            <?= nl2br(htmlspecialchars($info_del_producto[0]["descripcion"])) ?>
                        </p>
                    </div>
                </div>

                <!-- Specifications -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Especificaciones
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Fabricante:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    <?= htmlspecialchars($info_del_producto[0]["fabricante"]) ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Origen:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    <?= htmlspecialchars($info_del_producto[0]["origen"]) ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Categoría:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    <?= htmlspecialchars($info_del_producto[0]["categoria"]) ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">ID Producto:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    #<?= $info_del_producto[0]["id"] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Product Not Found -->
    <div class="container mx-auto px-4 py-16">
        <div class="text-center">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 max-w-md mx-auto">
                <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Producto no encontrado
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    El producto que buscas no existe o no está disponible
                </p>
                <a href="../index.php" class="bg-techblue-600 hover:bg-techblue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    Volver al catálogo
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript -->
    <script>
        let id_del_producto = <?=$id_producto?>;

        // Dark Mode Toggle
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            if (html.classList.contains('dark')) {
                localStorage.setItem('darkMode', 'true');
            } else {
                localStorage.setItem('darkMode', 'false');
            }
        }

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        // Load dark mode preference
        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>

</html>