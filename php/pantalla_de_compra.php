<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}
$id_usuario=$_SESSION['sesion_personal']['id'];
$vaciar_carrito=$_GET['v'];
$arreglo=array(); // arreglo de productos con sus cantidad y id pe [0]=1, 2
foreach ($_GET['datos'] as $value) {
    $subarreglo=explode(",",$value);
    array_push($arreglo,$subarreglo);
}

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $usuario=[];
    $result = mysqli_query($con, "SELECT * FROM usuario WHERE id_usuario=".$id_usuario.";");
    while ($row = mysqli_fetch_array($result)):
        array_push($usuario, array(
            "correo"=>$row['correo'],
            "n_tarjeta"=>$row['numero_tarjeta'],
            "direccion"=>$row['direccion']
        ));
    endwhile;

    // recorrer el arreglo de productos para hacer un arreglo de productos mas detallado
    $producto=[];
    foreach ($arreglo as $indice => $valor) {
        $cantidad=$valor[0];  //  el primer [0] es el primero producto
        $id_producto=$valor[1];
        /// AQUI
        $result = mysqli_query($con, "SELECT * FROM producto WHERE id_producto=".$id_producto.";");
        while ($row = mysqli_fetch_array($result)) {
            array_push($producto, array(
                "nombre"=>$row['nombre_producto'],
                "precio"=>$row['precio_producto'],
                "cantidad"=>$cantidad,
                "id_producto"=>$id_producto
            ));
        }
    }
    
    mysqli_close($con);
endif;

// Calcular totales
$subtotal = 0;
foreach ($producto as $value) {
    $subtotal += floatval($value['cantidad'] * floatval($value['precio']));
}
$envio = $subtotal >= 500 ? 0 : 50;
$impuestos = $subtotal * 0.18;
$total = $subtotal + $envio + $impuestos;
?>

<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"?>
    <title>GSITEC PERU - Confirmar Compra</title>
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
                    <span class="text-cyan-400 font-semibold">Confirmar Compra</span>
                    
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
                    <span class="text-cyan-400 font-semibold">🛒 Confirmar Compra</span>
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

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-green-600 to-emerald-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zm0 3a1 1 0 012 0 1 1 0 11-2 0zm4 0a1 1 0 012 0 1 1 0 11-2 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                Confirmar Compra
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Revisa tu pedido antes de finalizar la compra
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Billing Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v2H4V6zm0 4h12v4H4v-4z" clip-rule="evenodd"></path>
                            </svg>
                            Información de Facturación
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Address -->
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-4 mt-1">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Dirección de envío</p>
                                <p class="text-lg text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($usuario[0]['direccion']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Correo electrónico</p>
                                <p class="text-lg text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($usuario[0]['correo']) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Credit Card -->
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v2H4V6zm0 4h12v4H4v-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Método de pago</p>
                                <p class="text-lg text-gray-900 dark:text-white">
                                    **** **** **** <?= substr($usuario[0]['n_tarjeta'], -4) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products List -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            Productos a Comprar (<?= count($producto) ?>)
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($producto as $value): ?>
                        <div class="p-6">
                            <div class="flex items-center space-x-4">
                                <!-- Product Image -->
                                <div class="flex-shrink-0 w-20 h-20">
                                    <img 
                                        src="../img/productos/<?= $value['id_producto'] ?>.png" 
                                        alt="<?= htmlspecialchars($value['nombre']) ?>"
                                        class="w-full h-full object-cover rounded-lg"
                                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iNDAiIHk9IjQ1IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                                    >
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        <?= htmlspecialchars($value['nombre']) ?>
                                    </h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Precio unitario:</span>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                $<?= number_format(floatval($value['precio']), 2, '.', ',') ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Cantidad:</span>
                                            <p class="font-semibold text-techblue-600 dark:text-techblue-400">
                                                <?= $value['cantidad'] ?>
                                            </p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                            <p class="font-bold text-lg text-green-600 dark:text-green-400">
                                                $<?= number_format(floatval($value['cantidad'] * floatval($value['precio'])), 2, '.', ',') ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sticky top-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                        Resumen del Pedido
                    </h3>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                $<?= number_format(floatval($subtotal), 2, '.', ',') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Envío</span>
                            <span class="<?= $envio == 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' ?> font-medium">
                                <?= $envio == 0 ? 'GRATIS' : '$' . number_format($envio, 2, '.', ',') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Impuestos (18%)</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                $<?= number_format(floatval($impuestos), 2, '.', ',') ?>
                            </span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total</span>
                            <span class="text-2xl font-bold text-techblue-600 dark:text-techblue-400">
                                $<?= number_format(floatval($total), 2, '.', ',') ?>
                            </span>
                        </div>
                    </div>

                    <?php if($envio == 0): ?>
                    <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-green-800 dark:text-green-200 font-medium">
                                ¡Envío gratis incluido!
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Security Features -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6 space-y-3">
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
                            Pago 100% seguro
                        </div>
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-purple-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                            </svg>
                            Entrega en 3-5 días hábiles
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <script>
                        var arreglo_de_productos = JSON.parse('<?= json_encode($arreglo); ?>');
                        </script>
                        
                        <button 
                            onclick="comprar(arreglo_de_productos,<?=(int) $vaciar_carrito?>)"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                        >
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Confirmar Compra
                        </button>
                        
                        <button 
                            onclick="window.location.replace('../index.php')"
                            class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 block text-center"
                        >
                            ← Cancelar y volver al inicio
                        </button>
                        
                        <a href="../php/carrito.php" 
                           class="w-full bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 block text-center"
                        >
                            🛒 Volver al carrito
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
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