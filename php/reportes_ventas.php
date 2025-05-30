<?php
require_once("../config/config.php");
session_start();

if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];
$mensaje_global = null;

$items_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';

$fecha_inicio_valida = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $filtro_fecha_inicio) ? $filtro_fecha_inicio : null;
$fecha_fin_valida = preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $filtro_fecha_fin) ? $filtro_fecha_fin : null;

if ($fecha_inicio_valida && !$fecha_fin_valida) {
    $fecha_fin_valida = $fecha_inicio_valida;
} elseif (!$fecha_inicio_valida && $fecha_fin_valida) {
    $fecha_inicio_valida = $fecha_fin_valida;
}

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$ventas = [];
$total_ventas_filtradas = 0;
$ingresos_totales_filtrados = 0.0;

if (!$con) {
    $mensaje_global = "Error de conexión: " . mysqli_connect_error();
} else {
    mysqli_set_charset($con, "utf8mb4");

    $sql_base_from_join = "FROM historial_compras hc
                           JOIN producto p ON hc.id_producto = p.id_producto
                           JOIN usuario u ON hc.id_usuario = u.id_usuario";
    
    $where_clauses = [];
    $params = [];
    $types = "";

    if ($fecha_inicio_valida && $fecha_fin_valida) {
        $where_clauses[] = "hc.fecha_compra BETWEEN ? AND ?";
        $params[] = $fecha_inicio_valida;
        $params[] = $fecha_fin_valida;
        $types .= "ss";
    }

    $sql_where_clause = "";
    if (!empty($where_clauses)) {
        $sql_where_clause = " WHERE " . implode(" AND ", $where_clauses);
    }

    // Contar total y sumar ingresos para el filtro actual
    $sql_count_sum = "SELECT COUNT(hc.id_historial) as total_count, SUM(hc.cantidad_comprada * p.precio_producto) as total_sum " . $sql_base_from_join . $sql_where_clause;
    $stmt_count_sum = mysqli_prepare($con, $sql_count_sum);

    if ($stmt_count_sum) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt_count_sum, $types, ...$params);
        }
        mysqli_stmt_execute($stmt_count_sum);
        $result_count_sum = mysqli_stmt_get_result($stmt_count_sum);
        $row_count_sum = mysqli_fetch_assoc($result_count_sum);
        $total_ventas_filtradas = (int)($row_count_sum['total_count'] ?? 0);
        $ingresos_totales_filtrados = (float)($row_count_sum['total_sum'] ?? 0.0);
        mysqli_stmt_close($stmt_count_sum);
    } else {
        $mensaje_global = "Error al preparar conteo: " . mysqli_error($con);
    }

    $total_pages = 1;
    if ($total_ventas_filtradas > 0 && $items_per_page > 0) {
        $total_pages = ceil($total_ventas_filtradas / $items_per_page);
    }
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }
    if ($current_page < 1) $current_page = 1;
    if ($total_pages == 0) $total_pages = 1;

    $offset = ($current_page - 1) * $items_per_page;

    if ($mensaje_global === null && $total_ventas_filtradas > 0) {
        $sql_select_fields = "SELECT hc.id_historial, hc.fecha_compra, u.nombre_usuario, p.nombre_producto, 
                                     hc.cantidad_comprada, p.precio_producto, 
                                     (hc.cantidad_comprada * p.precio_producto) as subtotal ";
        $sql_ventas = $sql_select_fields . $sql_base_from_join . $sql_where_clause . " ORDER BY hc.fecha_compra DESC, hc.id_historial DESC LIMIT ? OFFSET ?";
        
        $params_select = $params; // Reutiliza los parámetros de los filtros
        $types_select = $types;   // Reutiliza los tipos de los filtros
        
        $params_select[] = $items_per_page;
        $params_select[] = $offset;
        $types_select .= "ii";

        $stmt_ventas = mysqli_prepare($con, $sql_ventas);
        if ($stmt_ventas) {
            if (!empty($params_select)) {
                 mysqli_stmt_bind_param($stmt_ventas, $types_select, ...$params_select);
            }
            mysqli_stmt_execute($stmt_ventas);
            $result_ventas = mysqli_stmt_get_result($stmt_ventas);
            while ($row = mysqli_fetch_assoc($result_ventas)) {
                $ventas[] = $row;
            }
            mysqli_stmt_close($stmt_ventas);
        } else {
            $mensaje_global = "Error al obtener ventas: " . mysqli_error($con);
        }
    }
    mysqli_close($con);
}

if (isset($_GET['mensaje_reporte'])) {
    $mensaje_global = htmlspecialchars($_GET['mensaje_reporte']);
}
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Reporte de Ventas</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
    <style>
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            filter: invert(0.6) sepia(0.6) saturate(5) hue-rotate(175deg);
        }
        /* Para Firefox, el icono es más difícil de estilizar directamente */
        input[type="date"] {
            appearance: none; /* Puede ayudar a que los estilos personalizados tengan más efecto */
            -moz-appearance: none;
            -webkit-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>');
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2rem; /* Espacio para el icono personalizado */
        }
        /* Ocultar el icono nativo si es posible, aunque esto puede ser inconsistente entre navegadores */
        input[type="date"]::-webkit-inner-spin-button,
        input[type="date"]::-webkit-clear-button {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <a href="./panel_admin.php" class="text-white text-xl font-bold">GSITEC ADMIN</a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <span class="text-cyan-400 font-semibold">📊 Reporte Ventas</span>
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
                    </button>
                    <div class="flex items-center space-x-4">
                        <span class="text-white">👑 Admin: <span class="font-semibold text-cyan-400"><?= htmlspecialchars($admin['nombre_usuario']) // Ajusta si el nombre del admin está en otra variable ?></span></span>
                        <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <span class="text-cyan-400 font-semibold">📊 Reporte Ventas</span>
                    <span class="text-cyan-400">👑 <?= htmlspecialchars($admin['nombre_usuario']) ?></span>
                    <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">📊 Reporte de Ventas</h1>
                <p class="text-gray-600 dark:text-gray-400">Visualiza el historial de compras y filtra por fechas.</p>
            </div>
        </div>

        <?php if ($mensaje_global): ?>
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span class="text-red-800 dark:text-red-200 font-medium"><?= $mensaje_global ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Filtrar Ventas</h2>
            <form method="GET" action="reportes_ventas.php" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($filtro_fecha_inicio) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($filtro_fecha_fin) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex space-x-2">
                     <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                        Filtrar
                    </button>
                    <a href="reportes_ventas.php" class="w-full md:w-auto inline-flex items-center justify-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path></svg>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        <?php if ($total_ventas_filtradas > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Historial de Ventas (<?= $total_ventas_filtradas ?>)</h2>
                <div class="text-right">
                    <span class="text-sm text-cyan-200 block">Ingresos Totales del Periodo:</span>
                    <span class="text-2xl font-bold text-white">$<?= number_format($ingresos_totales_filtrados, 2, '.', ',') ?></span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID Venta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($ventas as $venta): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">#<?= $venta['id_historial'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars(date("d/m/Y", strtotime($venta['fecha_compra']))) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($venta['nombre_usuario']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($venta['nombre_producto']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right"><?= $venta['cantidad_comprada'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400 text-right">$<?= number_format($venta['precio_producto'], 2, '.', ',') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-700 dark:text-green-300 text-right">$<?= number_format($venta['subtotal'], 2, '.', ',') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <nav class="flex justify-between items-center">
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        Mostrando <span class="font-medium"><?= count($ventas) ?></span> de <span class="font-medium"><?= $total_ventas_filtradas ?></span> resultados
                    </div>
                    <div class="flex space-x-1">
                        <?php
                        // Construir query string para paginación manteniendo filtros
                        $query_params_pagination = [];
                        if ($filtro_fecha_inicio) $query_params_pagination['fecha_inicio'] = $filtro_fecha_inicio;
                        if ($filtro_fecha_fin) $query_params_pagination['fecha_fin'] = $filtro_fecha_fin;
                        $base_url_pagination = 'reportes_ventas.php?' . http_build_query($query_params_pagination);
                        $separator = empty($query_params_pagination) ? '?' : '&';
                        ?>

                        <?php if ($current_page > 1): ?>
                            <a href="<?= $base_url_pagination . $separator ?>page=<?= $current_page - 1 ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">Anterior</a>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-600 dark:border-gray-500">Anterior</span>
                        <?php endif; ?>

                        <?php
                        $num_links_to_show = 3;
                        $start_loop = max(1, $current_page - floor($num_links_to_show / 2));
                        $end_loop = min($total_pages, $start_loop + $num_links_to_show - 1);
                        if ($end_loop - $start_loop + 1 < $num_links_to_show && $start_loop > 1) {
                            $start_loop = max(1, $end_loop - $num_links_to_show + 1);
                        }

                        if ($start_loop > 1): ?>
                            <a href="<?= $base_url_pagination . $separator ?>page=1" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">1</a>
                            <?php if ($start_loop > 2): ?><span class="px-3 py-1 text-sm font-medium text-gray-500 dark:text-gray-400">...</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_loop; $i <= $end_loop; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="px-3 py-1 text-sm font-medium text-white bg-techblue-600 border border-techblue-600 rounded-md dark:bg-techblue-700 dark:border-techblue-700 z-10"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $base_url_pagination . $separator ?>page=<?= $i ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end_loop < $total_pages): ?>
                            <?php if ($end_loop < $total_pages - 1): ?><span class="px-3 py-1 text-sm font-medium text-gray-500 dark:text-gray-400">...</span><?php endif; ?>
                            <a href="<?= $base_url_pagination . $separator ?>page=<?= $total_pages ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"><?= $total_pages ?></a>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?= $base_url_pagination . $separator ?>page=<?= $current_page + 1 ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">Siguiente</a>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-600 dark:border-gray-500">Siguiente</span>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif ($mensaje_global === null): ?>
        <div class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM5.05 5.05a.75.75 0 011.06 0l1.062 1.06a.75.75 0 11-1.06 1.06L5.05 6.11a.75.75 0 010-1.06zm9.9 0a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM3 10a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 013 10zm13.25 0a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM5.05 14.95a.75.75 0 010-1.06l1.06-1.062a.75.75 0 011.06 1.06l-1.06 1.06a.75.75 0 01-1.06 0zm9.9 0a.75.75 0 01-1.06 0l-1.062-1.06a.75.75 0 111.06-1.06l1.06 1.06a.75.75 0 010 1.06zM10 18a.75.75 0 01-.75-.75v-1.5a.75.75 0 011.5 0v1.5A.75.75 0 0110 18z"></path><path d="M7 11.25a2.75 2.75 0 105.5 0 2.75 2.75 0 00-5.5 0z"></path><path fill-rule="evenodd" d="M4.505 1.728A.75.75 0 003.62 2.397L.066 8.148a.75.75 0 00.18 1.035L4.5 12.272V15A2.75 2.75 0 007.25 17.75h5.5A2.75 2.75 0 0015.5 15v-2.728l4.254-3.09a.75.75 0 00.18-1.035L16.381 2.397a.75.75 0 00-.886-.67L10 3.182 4.505 1.728zM5.25 15V6.728l4.75 1.727 4.75-1.727V15H5.25z" clip-rule="evenodd"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No hay ventas registradas</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    <?php if ($filtro_fecha_inicio || $filtro_fecha_fin): ?>
                        No se encontraron ventas para el periodo seleccionado.
                    <?php else: ?>
                        Aún no se han realizado ventas en la tienda.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="./panel_admin.php" class="inline-flex items-center text-techblue-600 hover:text-techblue-500 dark:text-techblue-400 dark:hover:text-techblue-300 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                Volver al panel
            </a>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark') ? 'true' : 'false');
        }

        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>
</html>