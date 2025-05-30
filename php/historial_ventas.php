<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];

// Obtener filtros de fecha si se enviaron
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// Construir la query con filtros
$where_conditions = [];
$params = [];

if (!empty($fecha_inicio)) {
    $where_conditions[] = "h.fecha_compra >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $where_conditions[] = "h.fecha_compra <= ?";
    $params[] = $fecha_fin . ' 23:59:59';
}

if (!empty($filtro_usuario)) {
    $where_conditions[] = "u.nombre_usuario LIKE ?";
    $params[] = '%' . $filtro_usuario . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$historial = [];
$estadisticas = [];

if (!mysqli_connect_errno()) {
    // Query principal para el historial
    $query = "SELECT
            h.id_historial,
            h.id_producto,
            u.id_usuario,
            u.nombre_usuario,
            p.nombre_producto,
            p.precio_producto,
            h.cantidad_comprada,
            h.fecha_compra,
            (p.precio_producto * h.cantidad_comprada) as total_venta
        FROM historial_compras AS h
        JOIN usuario AS u ON h.id_usuario = u.id_usuario
        JOIN producto AS p ON p.id_producto = h.id_producto
        $where_clause
        ORDER BY h.fecha_compra DESC";

    $stmt = mysqli_prepare($con, $query);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_array($result)) {
        $historial[] = $row;
    }

    // Calcular estadísticas
    $stats_query = "SELECT 
        COUNT(*) as total_ventas,
        SUM(h.cantidad_comprada) as productos_vendidos,
        SUM(p.precio_producto * h.cantidad_comprada) as ingresos_totales,
        COUNT(DISTINCT u.id_usuario) as clientes_unicos,
        COUNT(DISTINCT p.id_producto) as productos_diferentes,
        AVG(p.precio_producto * h.cantidad_comprada) as venta_promedio
    FROM historial_compras AS h
    JOIN usuario AS u ON h.id_usuario = u.id_usuario
    JOIN producto AS p ON p.id_producto = h.id_producto
    $where_clause";

    $stats_stmt = mysqli_prepare($con, $stats_query);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stats_stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stats_stmt);
    $stats_result = mysqli_stmt_get_result($stats_stmt);
    $estadisticas = mysqli_fetch_array($stats_result);

    // Obtener productos más vendidos
    $top_productos_query = "SELECT 
        p.nombre_producto,
        p.id_producto,
        SUM(h.cantidad_comprada) as total_vendido,
        SUM(p.precio_producto * h.cantidad_comprada) as ingresos_producto
    FROM historial_compras AS h
    JOIN producto AS p ON p.id_producto = h.id_producto
    JOIN usuario AS u ON h.id_usuario = u.id_usuario
    $where_clause
    GROUP BY p.id_producto
    ORDER BY total_vendido DESC
    LIMIT 5";

    $top_stmt = mysqli_prepare($con, $top_productos_query);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($top_stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($top_stmt);
    $top_result = mysqli_stmt_get_result($top_stmt);
    
    $productos_mas_vendidos = [];
    while ($row = mysqli_fetch_array($top_result)) {
        $productos_mas_vendidos[] = $row;
    }

    mysqli_close($con);
}

$n_ventas = count($historial);
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Historial de Ventas</title>
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
                    <a href="./panel_admin.php" class="text-white text-xl font-bold">GSITEC ADMIN</a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <span class="text-cyan-400 font-semibold">📊 Historial de Ventas</span>
                    
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
                    <span class="text-cyan-400 font-semibold">📊 Historial de Ventas</span>
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
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-green-600 to-emerald-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                📊 Historial de Ventas
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Análisis completo de ventas y rendimiento del negocio
            </p>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🔍 Filtros de Búsqueda</h3>
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fecha Inicio
                    </label>
                    <input 
                        type="date" 
                        id="fecha_inicio" 
                        name="fecha_inicio" 
                        value="<?= htmlspecialchars($fecha_inicio) ?>"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fecha Fin
                    </label>
                    <input 
                        type="date" 
                        id="fecha_fin" 
                        name="fecha_fin" 
                        value="<?= htmlspecialchars($fecha_fin) ?>"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cliente
                    </label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        value="<?= htmlspecialchars($filtro_usuario) ?>"
                        placeholder="Nombre del cliente"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div class="flex items-end space-x-2">
                    <button 
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                        Filtrar
                    </button>
                    <a 
                        href="./historial_ventas.php"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                    >
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <?php if ($n_ventas > 0): ?>
        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ingresos Totales</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($estadisticas['ingresos_totales']), 2, '.', ',') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Ventas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($estadisticas['total_ventas']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Average Sale -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Venta Promedio</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($estadisticas['venta_promedio']), 2, '.', ',') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Products Sold -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Productos Vendidos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($estadisticas['productos_vendidos']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Unique Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Clientes Únicos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($estadisticas['clientes_unicos']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Different Products -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15.586 13H14a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Productos Diferentes</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($estadisticas['productos_diferentes']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products Section -->
        <?php if (!empty($productos_mas_vendidos)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">🏆 Productos Más Vendidos</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <?php foreach ($productos_mas_vendidos as $index => $producto): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <div class="flex items-center justify-center mb-2">
                        <img 
                            class="h-12 w-12 rounded-lg object-cover" 
                            src="../img/productos/<?= $producto['id_producto'] ?>.png" 
                            alt="<?= htmlspecialchars($producto['nombre_producto']) ?>"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4IiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iMjQiIHk9IjI4IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iOCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlByb2R1Y3RvPC90ZXh0Pjwvc3ZnPg=='"
                        >
                        <span class="ml-2 text-2xl font-bold text-yellow-500">
                            <?php echo ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'][$index] ?? '' ?>
                        </span>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                        <?= htmlspecialchars(substr($producto['nombre_producto'], 0, 30)) ?><?= strlen($producto['nombre_producto']) > 30 ? '...' : '' ?>
                    </h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        <?= $producto['total_vendido'] ?> vendidos
                    </p>
                    <p class="text-sm font-bold text-green-600 dark:text-green-400">
                        $<?= number_format(floatval($producto['ingresos_producto']), 2, '.', ',') ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sales History Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-emerald-500 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">
                    📊 Detalle de Ventas (<?= $n_ventas ?>)
                </h2>
                <button 
                    onclick="exportToCSV()"
                    class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-sm"
                >
                    📥 Exportar CSV
                </button>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="ventasTable">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID Venta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Producto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Fecha y Hora
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Cantidad
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Precio Unit.
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total Venta
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($historial as $venta): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    #<?= str_pad($venta['id_historial'], 6, '0', STR_PAD_LEFT) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img 
                                            class="h-10 w-10 rounded-lg object-cover" 
                                            src="../img/productos/<?= $venta['id_producto'] ?>.png" 
                                            alt="<?= htmlspecialchars($venta['nombre_producto']) ?>"
                                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iMjAiIHk9IjI0IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iOCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPlByb2R1Y3RvPC90ZXh0Pjwvc3ZnPg=='"
                                        >
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($venta['nombre_producto']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: #<?= $venta['id_producto'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-techblue-100 dark:bg-techblue-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-techblue-600 dark:text-techblue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($venta['nombre_usuario']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: #<?= $venta['id_usuario'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y', strtotime($venta['fecha_compra'])) ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= date('H:i:s', strtotime($venta['fecha_compra'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <?= $venta['cantidad_comprada'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    $<?= number_format(floatval($venta['precio_producto']), 2, '.', ',') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    $<?= number_format(floatval($venta['total_venta']), 2, '.', ',') ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-4 p-4">
                <?php foreach ($historial as $venta): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            Venta #<?= str_pad($venta['id_historial'], 6, '0', STR_PAD_LEFT) ?>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <?= date('d/m/Y H:i', strtotime($venta['fecha_compra'])) ?>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <img 
                            class="h-16 w-16 rounded-lg object-cover flex-shrink-0" 
                            src="../img/productos/<?= $venta['id_producto'] ?>.png" 
                            alt="<?= htmlspecialchars($venta['nombre_producto']) ?>"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iMzIiIHk9IjM3IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                        >
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                <?= htmlspecialchars($venta['nombre_producto']) ?>
                            </h3>
                            <p class="text-sm text-techblue-600 dark:text-techblue-400 mb-2">
                                Cliente: <?= htmlspecialchars($venta['nombre_usuario']) ?>
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-medium">Cantidad:</span>
                                    <?= $venta['cantidad_comprada'] ?>
                                </div>
                                <div>
                                    <span class="font-medium">Precio:</span>
                                    $<?= number_format(floatval($venta['precio_producto']), 2, '.', ',') ?>
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium text-green-600 dark:text-green-400">Total:</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">
                                        $<?= number_format(floatval($venta['total_venta']), 2, '.', ',') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    No hay ventas en el período seleccionado
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Ajusta los filtros de fecha o revisa cuando se realizaron las ventas
                </p>
                <a href="./historial_ventas.php" 
                   class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                    Ver todas las ventas
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="./panel_admin.php" 
               class="inline-flex items-center text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Volver al panel
            </a>
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

        function exportToCSV() {
            const table = document.getElementById('ventasTable');
            const rows = table.querySelectorAll('tr');
            const csvContent = [];

            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach(col => {
                    // Limpiar el texto y remover saltos de línea
                    let text = col.textContent.trim().replace(/\s+/g, ' ');
                    // Escapar comillas dobles
                    text = text.replace(/"/g, '""');
                    // Envolver en comillas si contiene comas
                    if (text.includes(',')) {
                        text = `"${text}"`;
                    }
                    rowData.push(text);
                });
                csvContent.push(rowData.join(','));
            });

            const csv = csvContent.join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `historial_ventas_${new Date().getTime()}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }

            // Auto-set today's date as end date if no filters are set
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            if (!fechaFin.value && !fechaInicio.value) {
                const today = new Date().toISOString().split('T')[0];
                fechaFin.value = today;
                
                // Set start date to 30 days ago
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fechaInicio.value = thirtyDaysAgo.toISOString().split('T')[0];
            }
        });
    </script>
</body>

</html>