<?php
require_once("../config/config.php");
require_once("./verificar_primer_admin.php");

session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];

// Obtener estadísticas generales
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$stats = [];

if (!mysqli_connect_errno()) {
    // Total de productos
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM producto");
    $stats['productos'] = mysqli_fetch_array($result)['total'];
    
    // Total de usuarios registrados
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM usuario");
    $stats['usuarios'] = mysqli_fetch_array($result)['total'];
    
    // Total de ventas realizadas
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM historial_compras");
    $stats['ventas'] = mysqli_fetch_array($result)['total'];
    
    // Ingresos totales
    $result = mysqli_query($con, "SELECT SUM(p.precio_producto * h.cantidad_comprada) as total FROM historial_compras h JOIN producto p ON h.id_producto = p.id_producto");
    $stats['ingresos'] = mysqli_fetch_array($result)['total'] ?? 0;
    
    // Productos con stock bajo (menos de 5)
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM producto WHERE cantidad_disponible < 5 AND cantidad_disponible > 0");
    $stats['stock_bajo'] = mysqli_fetch_array($result)['total'];
    
    // Productos agotados
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM producto WHERE cantidad_disponible = 0");
    $stats['agotados'] = mysqli_fetch_array($result)['total'];
    
    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "./head_html.php"; ?>
    <title>GSITEC PERU - Panel de Administración</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="./panel_admin.php" class="text-white text-xl font-bold">🏠 GSITEC ADMIN</a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🛍️ Ver Tienda</a>
                    
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 716.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-4">
                        <span class="text-white">
                            👑 Admin: <span class="font-semibold text-cyan-400"><?= htmlspecialchars($admin['nombre']) ?></span>
                            <?php if($admin['nivel'] == 2): ?>
                                <span class="text-yellow-300">⭐ Super</span>
                            <?php endif; ?>
                        </span>
                        
                        <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🛍️ Ver Tienda</a>
                    <span class="text-cyan-400">👑 <?= htmlspecialchars($admin['nombre']) ?></span>
                    <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-20 w-20 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                👑 Panel de Administración
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Bienvenido al centro de control de GSITEC PERU
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Productos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($stats['productos']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Usuarios Registrados</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($stats['usuarios']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ventas Realizadas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($stats['ventas']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ingresos Totales</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($stats['ingresos']), 2, '.', ',') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Cards -->
        <?php if($stats['stock_bajo'] > 0 || $stats['agotados'] > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <?php if($stats['stock_bajo'] > 0): ?>
            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-yellow-800 dark:text-yellow-200 font-medium">
                        ⚠️ <?= $stats['stock_bajo'] ?> producto(s) con stock bajo (menos de 5 unidades)
                    </span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($stats['agotados'] > 0): ?>
            <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-red-800 dark:text-red-200 font-medium">
                        🚫 <?= $stats['agotados'] ?> producto(s) agotado(s)
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Main Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Product Management -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        Gestión de Productos
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Administra el inventario y catálogo de productos de la tienda.
                    </p>
                    <div class="space-y-3">
                        <a href="./gestion_productos.php" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            📦 Ver Todos los Productos
                        </a>
                        <a href="./categorias_admin.php" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            🏷️ Gestionar Categorías
                        </a>
                        <a href="./agregar_producto.php" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            ➕ Agregar Nuevo Producto
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sales Management -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-500 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        Gestión de Ventas
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Consulta y analiza las ventas realizadas en la plataforma.
                    </p>
                    <div class="space-y-3">
                        <a href="./historial_ventas.php" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            📊 Historial de Ventas
                        </a>
                        <a href="./reportes_avanzados.php" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            📈 Analytics Avanzados
                        </a>
                        <a href="./reportes_ventas.php" 
                           class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            📅 Reportes Básicos
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                        Gestión de Usuarios
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Administra los usuarios registrados en la plataforma.
                    </p>
                    <div class="space-y-3">
                        <a href="./lista_clientes.php" 
                           class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            👥 Lista de Clientes
                        </a>
                        <?php if($admin['nivel'] == 2): ?>
                        <a href="./gestion_admins.php" 
                           class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                            👑 Gestión de Admins
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            if (html.classList.contains('dark')) {
                localStorage.setItem('darkMode', 'true');
            } else {
                localStorage.setItem('darkMode', 'false');
            }
        }

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>

</html>
