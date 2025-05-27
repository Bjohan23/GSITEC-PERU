<?php
require_once("../config/config.php");
session_start();

if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

// Crear una conexión
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
// verificar connection con la BD
if (mysqli_connect_errno()) :
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
else:
    $arreglo_de_productos=[];
    $result = mysqli_query($con, "SELECT p.id_producto,p.nombre_producto,p.precio_producto,p.cantidad_disponible,c.cantidad_seleccionada,c.id_carrito FROM carrito as c INNER JOIN producto as p ON c.id_producto = p.id_producto WHERE c.id_usuario=".$_SESSION['sesion_personal']['id'].";");
    $n_productos=mysqli_num_rows($result);
    while ($row = mysqli_fetch_array($result)):
        array_push($arreglo_de_productos, array(
            "id_carrito"=>$row['id_carrito'],
            "id"=>$row['id_producto'],
            "nombre"=>$row['nombre_producto'],
            "precio"=>$row['precio_producto'],
            "disponibles"=>$row['cantidad_disponible'],
            "cantidad"=>$row['cantidad_seleccionada'],
        ));
    endwhile;

    // cerrar conexión
    mysqli_close($con);
endif;
$suma=0;
$arreglo_para_comprar=array();
?>

<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php" ?>
    <title>GSITEC PERU - Carrito de Compras</title>
    <!-- icono -->
    <link rel="shortcut icon" href="../img/logo.jpg">
    
    <!-- JS -->
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

                        <span class="bg-cyan-500 text-white px-4 py-2 rounded-lg font-semibold flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                            </svg>
                            Carrito (<?= $n_productos ?>)
                        </span>
                        
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
                    <span class="text-cyan-400">Hola, <?=$_SESSION['sesion_personal']['nombre']?></span>
                    <?php if($_SESSION['sesion_personal']['super']==1): ?>
                    <a href="../php/consultar_historial.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📋 Consultar historial</a>
                    <a href="../php/modificar_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">⚙️ Modificar productos</a>
                    <?php endif; ?>
                    <span class="text-cyan-400 font-semibold">🛒 Carrito (<?= $n_productos ?>)</span>
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
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                🛒 Carrito de Compras
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?= $n_productos > 0 ? "Tienes $n_productos producto(s) en tu carrito" : "Tu carrito está vacío" ?>
            </p>
        </div>

        <?php if ($n_productos == 0): ?>
        <!-- Empty Cart -->
        <div class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Tu carrito está vacío
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Explora nuestro catálogo y encuentra los mejores componentes para tu setup
                </p>
                <a href="../index.php" 
                   class="inline-flex items-center bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Seguir comprando
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Cart Items -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Products List -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($arreglo_de_productos as $producto): 
                array_push($arreglo_para_comprar,($producto["cantidad"].",".$producto["id"].""));
                ?>
                <!-- Product Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden product-card">
                    <div class="flex flex-col sm:flex-row">
                        <!-- Product Image -->
                        <div class="w-full sm:w-32 h-32 flex-shrink-0">
                            <img 
                                src="../img/productos/<?= $producto["id"] ?>.png" 
                                alt="<?= htmlspecialchars($producto["nombre"]) ?>"
                                class="w-full h-full object-cover"
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgZmlsbD0iIzNCODJGNiIvPjx0ZXh0IHg9IjY0IiB5PSI3MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+UHJvZHVjdG88L3RleHQ+PC9zdmc+'"
                            >
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        <?= htmlspecialchars($producto["nombre"]) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        Disponibles: <?= $producto["disponibles"] ?> unidades
                                    </p>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-xl font-bold text-techblue-600 dark:text-techblue-400">
                                            $<?= number_format(floatval($producto["precio"]), 2, '.', ',') ?>
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            c/u
                                        </span>
                                    </div>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center justify-between sm:justify-end sm:flex-col sm:items-end space-y-4 mt-4 sm:mt-0">
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <a href="modificar_producto_carrito.php?signo=0&id_carrito=<?=$producto['id_carrito']?>&disp=<?=$producto["disponibles"]?>&cant=<?=$producto["cantidad"]?>" 
                                           class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-techblue-600 dark:hover:text-techblue-400 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                        <span class="w-12 text-center font-semibold text-gray-900 dark:text-white">
                                            <?= $producto["cantidad"] ?>
                                        </span>
                                        <a href="modificar_producto_carrito.php?signo=1&id_carrito=<?=$producto['id_carrito']?>&disp=<?=$producto["disponibles"]?>&cant=<?=$producto["cantidad"]?>" 
                                           class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-techblue-600 dark:hover:text-techblue-400 transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                                            $<?= number_format(floatval(floatval($producto["precio"])*((int) $producto["cantidad"])), 2, '.', ',') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $suma+=floatval(floatval($producto["precio"])*((int) $producto["cantidad"])); ?>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sticky top-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                        Resumen del pedido
                    </h3>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Productos (<?= $n_productos ?>)</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                $<?= number_format(floatval($suma), 2, '.', ',') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Envío</span>
                            <span class="text-green-600 dark:text-green-400 font-medium">
                                <?= $suma >= 500 ? 'GRATIS' : '$50.00' ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Impuestos</span>
                            <span class="text-gray-900 dark:text-white font-medium">
                                $<?= number_format(floatval($suma * 0.18), 2, '.', ',') ?>
                            </span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total</span>
                            <span class="text-2xl font-bold text-techblue-600 dark:text-techblue-400">
                                $<?= number_format(floatval($suma + ($suma >= 500 ? 0 : 50) + ($suma * 0.18)), 2, '.', ',') ?>
                            </span>
                        </div>
                    </div>

                    <?php if($suma >= 500): ?>
                    <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-green-800 dark:text-green-200 font-medium">
                                ¡Envío gratis por compras mayores a $500!
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <script>
                        var arreglo_de_productos = JSON.parse('<?= json_encode($arreglo_para_comprar); ?>');
                        </script>
                        
                        <button 
                            onclick="enviarAPantallaDeCompraMuchos(arreglo_de_productos)"
                            class="w-full bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zM8 6a2 2 0 114 0v1H8V6zm0 3a1 1 0 012 0 1 1 0 11-2 0zm4 0a1 1 0 012 0 1 1 0 11-2 0z" clip-rule="evenodd"></path>
                            </svg>
                            Proceder al Pago
                        </button>
                        
                        <a href="vaciar_carrito.php" 
                           class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] block text-center"
                           onclick="return confirm('¿Estás seguro de que quieres vaciar el carrito?')"
                        >
                            🗑️ Vaciar Carrito
                        </a>
                        
                        <a href="../index.php" 
                           class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold py-3 px-6 rounded-lg transition-all duration-200 block text-center"
                        >
                            ← Seguir comprando
                        </a>
                    </div>

                    <!-- Security Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Compra 100% segura
                        </div>
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Garantía incluida
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif ?>
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