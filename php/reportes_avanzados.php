<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];

// Función para obtener datos de API interna
function getAnalyticsData($con, $periodo = '30') {
    $fecha_inicio = date('Y-m-d', strtotime("-$periodo days"));
    $data = [];
    
    // Ventas por día (últimos 30 días)
    $query_ventas_diarias = "
        SELECT 
            DATE(fecha_compra) as fecha,
            COUNT(*) as total_ventas,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY DATE(fecha_compra)
        ORDER BY fecha ASC
    ";
    
    $result = mysqli_query($con, $query_ventas_diarias);
    $ventas_diarias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ventas_diarias[] = $row;
    }
    
    // Ventas por categoría
    $query_categorias = "
        SELECT 
            c.nombre_categoria,
            c.icono_categoria,
            c.color_categoria,
            COUNT(h.id_historial) as total_ventas,
            SUM(h.cantidad_comprada) as productos_vendidos,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY c.id_categoria
        ORDER BY ingresos DESC
    ";
    
    $result = mysqli_query($con, $query_categorias);
    $ventas_categorias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ventas_categorias[] = $row;
    }
    
    // Top productos vendidos
    $query_top_productos = "
        SELECT 
            p.nombre_producto,
            p.id_producto,
            c.nombre_categoria,
            c.icono_categoria,
            SUM(h.cantidad_comprada) as total_vendido,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos_producto,
            AVG(p.precio_producto) as precio_promedio
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY p.id_producto
        ORDER BY total_vendido DESC
        LIMIT 10
    ";
    
    $result = mysqli_query($con, $query_top_productos);
    $top_productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $top_productos[] = $row;
    }
    
    // Clientes más activos
    $query_top_clientes = "
        SELECT 
            u.nombre_usuario,
            u.id_usuario,
            COUNT(h.id_historial) as total_compras,
            SUM(h.cantidad_comprada) as productos_comprados,
            SUM(p.precio_producto * h.cantidad_comprada) as total_gastado,
            MAX(h.fecha_compra) as ultima_compra
        FROM historial_compras h
        JOIN usuario u ON h.id_usuario = u.id_usuario
        JOIN producto p ON h.id_producto = p.id_producto
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY u.id_usuario
        ORDER BY total_gastado DESC
        LIMIT 8
    ";
    
    $result = mysqli_query($con, $query_top_clientes);
    $top_clientes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $top_clientes[] = $row;
    }
    
    return [
        'ventas_diarias' => $ventas_diarias,
        'ventas_categorias' => $ventas_categorias,
        'top_productos' => $top_productos,
        'top_clientes' => $top_clientes
    ];
}

// Obtener estadísticas generales
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$stats = [];
$analytics = [];

if (!mysqli_connect_errno()) {
    // Período seleccionado (por defecto 30 días)
    $periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 30;
    $fecha_inicio = date('Y-m-d', strtotime("-$periodo days"));
    
    // Estadísticas del período
    $query_stats = "
        SELECT 
            COUNT(DISTINCT h.id_historial) as total_ventas,
            COUNT(DISTINCT h.id_usuario) as clientes_activos,
            COUNT(DISTINCT h.id_producto) as productos_vendidos,
            SUM(h.cantidad_comprada) as unidades_vendidas,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos_totales,
            AVG(p.precio_producto * h.cantidad_comprada) as venta_promedio
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        WHERE h.fecha_compra >= '$fecha_inicio'
    ";
    
    $result = mysqli_query($con, $query_stats);
    $stats = mysqli_fetch_assoc($result);
    
    // Obtener datos de analytics
    $analytics = getAnalyticsData($con, $periodo);
    
    // Comparación con período anterior
    $fecha_anterior = date('Y-m-d', strtotime("-" . ($periodo * 2) . " days"));
    $query_anterior = "
        SELECT 
            COUNT(DISTINCT h.id_historial) as total_ventas_anterior,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos_anteriores
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        WHERE h.fecha_compra >= '$fecha_anterior' AND h.fecha_compra < '$fecha_inicio'
    ";
    
    $result = mysqli_query($con, $query_anterior);
    $stats_anterior = mysqli_fetch_assoc($result);
    
    // Calcular porcentajes de cambio
    $stats['crecimiento_ventas'] = 0;
    $stats['crecimiento_ingresos'] = 0;
    
    if ($stats_anterior['total_ventas_anterior'] > 0) {
        $stats['crecimiento_ventas'] = round((($stats['total_ventas'] - $stats_anterior['total_ventas_anterior']) / $stats_anterior['total_ventas_anterior']) * 100, 1);
    }
    
    if ($stats_anterior['ingresos_anteriores'] > 0) {
        $stats['crecimiento_ingresos'] = round((($stats['ingresos_totales'] - $stats_anterior['ingresos_anteriores']) / $stats_anterior['ingresos_anteriores']) * 100, 1);
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Reportes Avanzados</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
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
                    <a href="./panel_admin.php" class="text-white text-xl font-bold">GSITEC ADMIN</a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <a href="./historial_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📊 Ventas</a>
                    <span class="text-cyan-400 font-semibold">📈 Analytics</span>
                    
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
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <a href="./historial_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📊 Ventas</a>
                    <span class="text-cyan-400 font-semibold">📈 Analytics</span>
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
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8">
            <div>
                <div class="mx-auto lg:mx-0 h-16 w-16 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    📈 Analytics & Reportes
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Dashboard avanzado con métricas y tendencias del negocio
                </p>
            </div>
            
            <!-- Period Selector -->
            <div class="mt-6 lg:mt-0">
                <div class="flex flex-wrap gap-2">
                    <a href="?periodo=7" class="px-4 py-2 rounded-lg transition-colors duration-200 <?= (isset($_GET['periodo']) && $_GET['periodo'] == 7) || (!isset($_GET['periodo']) && $periodo == 7) ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900' ?>">
                        7 días
                    </a>
                    <a href="?periodo=30" class="px-4 py-2 rounded-lg transition-colors duration-200 <?= (isset($_GET['periodo']) && $_GET['periodo'] == 30) || (!isset($_GET['periodo']) && $periodo == 30) ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900' ?>">
                        30 días
                    </a>
                    <a href="?periodo=90" class="px-4 py-2 rounded-lg transition-colors duration-200 <?= (isset($_GET['periodo']) && $_GET['periodo'] == 90) || (!isset($_GET['periodo']) && $periodo == 90) ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900' ?>">
                        90 días
                    </a>
                    <a href="?periodo=365" class="px-4 py-2 rounded-lg transition-colors duration-200 <?= (isset($_GET['periodo']) && $_GET['periodo'] == 365) || (!isset($_GET['periodo']) && $periodo == 365) ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900' ?>">
                        1 año
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ingresos Totales</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($stats['ingresos_totales'] ?? 0), 2, '.', ',') ?>
                        </p>
                        <div class="flex items-center mt-2">
                            <?php if (($stats['crecimiento_ingresos'] ?? 0) >= 0): ?>
                                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-green-600 text-sm font-medium">+<?= abs($stats['crecimiento_ingresos'] ?? 0) ?>%</span>
                            <?php else: ?>
                                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-red-600 text-sm font-medium"><?= $stats['crecimiento_ingresos'] ?? 0 ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Ventas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($stats['total_ventas'] ?? 0) ?>
                        </p>
                        <div class="flex items-center mt-2">
                            <?php if (($stats['crecimiento_ventas'] ?? 0) >= 0): ?>
                                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-green-600 text-sm font-medium">+<?= abs($stats['crecimiento_ventas'] ?? 0) ?>%</span>
                            <?php else: ?>
                                <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-red-600 text-sm font-medium"><?= $stats['crecimiento_ventas'] ?? 0 ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Sale -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Venta Promedio</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($stats['venta_promedio'] ?? 0), 2, '.', ',') ?>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            por transacción
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Clientes Activos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($stats['clientes_activos'] ?? 0) ?>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            en este período
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Sales Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📈 Tendencia de Ventas</h3>
                    <div class="flex items-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-gray-600 dark:text-gray-400">Ventas</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-gray-600 dark:text-gray-400">Ingresos</span>
                        </div>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🥧 Ventas por Categoría</h3>
                    <button onclick="toggleChartType()" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        Cambiar vista
                    </button>
                </div>
                <div class="h-80">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products and Customers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">🏆 Productos Más Vendidos</h3>
                <div class="space-y-4">
                    <?php foreach (array_slice($analytics['top_productos'] ?? [], 0, 5) as $index => $producto): ?>
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">#<?= $index + 1 ?></span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <?= htmlspecialchars($producto['nombre_producto']) ?>
                            </h4>
                            <div class="flex items-center mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400 mr-2">
                                    <?= htmlspecialchars($producto['icono_categoria'] ?? '📦') ?> <?= htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría') ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                <?= $producto['total_vendido'] ?> vendidos
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400">
                                $<?= number_format(floatval($producto['ingresos_producto']), 2, '.', ',') ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">👥 Mejores Clientes</h3>
                <div class="space-y-4">
                    <?php foreach (array_slice($analytics['top_clientes'] ?? [], 0, 5) as $index => $cliente): ?>
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">#<?= $index + 1 ?></span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($cliente['nombre_usuario']) ?>
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?= $cliente['total_compras'] ?> compras • <?= $cliente['productos_comprados'] ?> productos
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                $<?= number_format(floatval($cliente['total_gastado']), 2, '.', ',') ?>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <?= date('d/m/Y', strtotime($cliente['ultima_compra'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">📊 Exportar Reportes</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Descarga los datos de analytics en diferentes formatos
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                    <button onclick="exportData('csv')" 
                            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        CSV
                    </button>
                    <button onclick="exportData('pdf')" 
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        PDF
                    </button>
                    <button onclick="exportData('excel')" 
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="./panel_admin.php" 
               class="inline-flex items-center text-purple-600 hover:text-purple-500 dark:text-purple-400 dark:hover:text-purple-300 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Volver al panel
            </a>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Dark mode functionality
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark') ? 'true' : 'false');
            
            // Update charts for dark mode
            setTimeout(() => {
                updateChartsForTheme();
            }, 100);
        }

        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }

        // Data for charts
        const salesData = <?= json_encode($analytics['ventas_diarias'] ?? []) ?>;
        const categoryData = <?= json_encode($analytics['ventas_categorias'] ?? []) ?>;
        
        let categoryChartType = 'doughnut';
        let salesChart, categoryChart;

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
            
            initializeCharts();
        });

        function initializeCharts() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#E5E7EB' : '#374151';
            const gridColor = isDark ? '#374151' : '#E5E7EB';
            
            // Sales Trend Chart
            const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: salesData.map(item => item.fecha),
                    datasets: [{
                        label: 'Ventas',
                        data: salesData.map(item => item.total_ventas),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Ingresos ($)',
                        data: salesData.map(item => item.ingresos),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day',
                                displayFormats: {
                                    day: 'MMM dd'
                                }
                            },
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });

            // Category Chart
            updateCategoryChart();
        }

        function updateCategoryChart() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#E5E7EB' : '#374151';
            
            if (categoryChart) {
                categoryChart.destroy();
            }
            
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(categoryCtx, {
                type: categoryChartType,
                data: {
                    labels: categoryData.map(item => item.icono_categoria + ' ' + item.nombre_categoria),
                    datasets: [{
                        data: categoryData.map(item => item.ingresos),
                        backgroundColor: categoryData.map(item => item.color_categoria),
                        borderWidth: 2,
                        borderColor: isDark ? '#1F2937' : '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: categoryChartType === 'doughnut' ? 'right' : 'top',
                            labels: {
                                color: textColor,
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': $' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    scales: categoryChartType === 'bar' ? {
                        y: {
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: isDark ? '#374151' : '#E5E7EB'
                            }
                        },
                        x: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: isDark ? '#374151' : '#E5E7EB'
                            }
                        }
                    } : {}
                }
            });
        }

        function toggleChartType() {
            categoryChartType = categoryChartType === 'doughnut' ? 'bar' : 'doughnut';
            updateCategoryChart();
        }

        function updateChartsForTheme() {
            if (salesChart && categoryChart) {
                salesChart.destroy();
                categoryChart.destroy();
                initializeCharts();
            }
        }

        function exportData(format) {
            const periodo = <?= $periodo ?>;
            const url = `./exportar_reportes.php?formato=${format}&periodo=${periodo}`;
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="currentColor" viewBox="0 0 20 20"><path d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"></path></svg>Generando...';
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Restore button
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
    </script>
</body>

</html>