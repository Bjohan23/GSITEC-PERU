<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];

// Obtener lista de clientes con estadísticas
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$clientes = [];

if (!mysqli_connect_errno()) {
    $query = "
        SELECT 
            u.id_usuario,
            u.nombre_usuario,
            u.correo,
            u.fecha_nacimiento,
            u.direccion,
            COUNT(DISTINCT h.id_historial) as total_compras,
            SUM(h.cantidad_comprada) as productos_comprados,
            SUM(p.precio_producto * h.cantidad_comprada) as total_gastado,
            MAX(h.fecha_compra) as ultima_compra
        FROM usuario u
        LEFT JOIN historial_compras h ON u.id_usuario = h.id_usuario
        LEFT JOIN producto p ON h.id_producto = p.id_producto
        GROUP BY u.id_usuario
        ORDER BY total_compras DESC, u.nombre_usuario ASC
    ";
    
    $result = mysqli_query($con, $query);
    while ($row = mysqli_fetch_array($result)) {
        $clientes[] = array(
            "id" => $row['id_usuario'],
            "nombre" => $row['nombre_usuario'],
            "correo" => $row['correo'],
            "fecha_nacimiento" => $row['fecha_nacimiento'],
            "direccion" => $row['direccion'],
            "total_compras" => $row['total_compras'] ?: 0,
            "productos_comprados" => $row['productos_comprados'] ?: 0,
            "total_gastado" => $row['total_gastado'] ?: 0,
            "ultima_compra" => $row['ultima_compra']
        );
    }
    
    mysqli_close($con);
}

// Estadísticas generales
$total_clientes = count($clientes);
$clientes_activos = 0;
$ingresos_totales = 0;

foreach ($clientes as $cliente) {
    if ($cliente['total_compras'] > 0) {
        $clientes_activos++;
    }
    $ingresos_totales += $cliente['total_gastado'];
}
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Lista de Clientes</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <!-- Navigation -->
    <?php include './admin_nav.php'; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                👥 Lista de Clientes
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gestión de usuarios registrados en GSITEC PERU
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Clientes</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($total_clientes) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Clientes Activos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?= number_format($clientes_activos) ?>
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
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ingresos Generados</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            $<?= number_format(floatval($ingresos_totales), 2, '.', ',') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($total_clientes > 0): ?>
        <!-- Customers Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    👥 Listado Detallado de Clientes
                </h2>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Información Personal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estadísticas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($clientes as $cliente): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-techblue-100 dark:bg-techblue-900 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-5 h-5 text-techblue-600 dark:text-techblue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($cliente['nombre']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: #<?= $cliente['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    📧 <?= htmlspecialchars($cliente['correo']) ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    🎂 <?= date('d/m/Y', strtotime($cliente['fecha_nacimiento'])) ?> 
                                    (<?= date('Y') - date('Y', strtotime($cliente['fecha_nacimiento'])) ?> años)
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    📍 <?= htmlspecialchars(substr($cliente['direccion'], 0, 30)) ?><?= strlen($cliente['direccion']) > 30 ? '...' : '' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Compras:</span>
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                                            <?= $cliente['total_compras'] ?>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Productos:</span>
                                        <span class="font-semibold text-purple-600 dark:text-purple-400">
                                            <?= $cliente['productos_comprados'] ?>
                                        </span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-gray-600 dark:text-gray-400">Total gastado:</span>
                                        <span class="font-bold text-green-600 dark:text-green-400">
                                            $<?= number_format(floatval($cliente['total_gastado']), 2, '.', ',') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($cliente['total_compras'] > 0): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        ✅ Cliente Activo
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Última: <?= date('d/m/Y', strtotime($cliente['ultima_compra'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        😴 Sin Compras
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Registrado
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-4 p-4">
                <?php foreach ($clientes as $cliente): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-techblue-100 dark:bg-techblue-900 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-techblue-600 dark:text-techblue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                📧 <?= htmlspecialchars($cliente['correo']) ?>
                            </p>
                            
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <div>
                                    <span class="font-medium">Edad:</span><br>
                                    <?= date('Y') - date('Y', strtotime($cliente['fecha_nacimiento'])) ?> años
                                </div>
                                <div>
                                    <span class="font-medium">Compras:</span><br>
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">
                                        <?= $cliente['total_compras'] ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium">Productos:</span><br>
                                    <span class="text-purple-600 dark:text-purple-400 font-semibold">
                                        <?= $cliente['productos_comprados'] ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium">Total gastado:</span><br>
                                    <span class="text-green-600 dark:text-green-400 font-bold">
                                        $<?= number_format(floatval($cliente['total_gastado']), 2, '.', ',') ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($cliente['total_compras'] > 0): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✅ Cliente Activo
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                    Última compra: <?= date('d/m/Y', strtotime($cliente['ultima_compra'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    😴 Sin compras
                                </span>
                            <?php endif; ?>
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
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    No hay clientes registrados
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Los nuevos registros de usuarios aparecerán aquí
                </p>
                <a href="../index.php" 
                   class="inline-flex items-center bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Ver tienda pública
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mt-8 text-center">
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
