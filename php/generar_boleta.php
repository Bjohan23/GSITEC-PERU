<?php
require_once("../config/config.php");
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

// Obtener el ID de la compra más reciente del usuario
$id_usuario = $_SESSION['sesion_personal']['id'];
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Obtener la última compra del usuario
$query = "SELECT DISTINCT fecha_compra FROM historial_compras WHERE id_usuario = $id_usuario ORDER BY fecha_compra DESC LIMIT 1";
$result = mysqli_query($con, $query);
$ultima_fecha = mysqli_fetch_array($result)['fecha_compra'];

// Obtener todos los productos de la última compra
$query = "SELECT hc.*, p.nombre_producto, p.precio_producto, u.nombre_usuario, u.correo, u.direccion 
          FROM historial_compras hc 
          JOIN producto p ON hc.id_producto = p.id_producto 
          JOIN usuario u ON hc.id_usuario = u.id_usuario 
          WHERE hc.id_usuario = $id_usuario AND hc.fecha_compra = '$ultima_fecha' 
          ORDER BY hc.id_producto";
$result = mysqli_query($con, $query);

$productos = [];
$usuario_info = null;
$subtotal = 0;

while ($row = mysqli_fetch_array($result)) {
    if (!$usuario_info) {
        $usuario_info = [
            'nombre' => $row['nombre_usuario'],
            'correo' => $row['correo'],
            'direccion' => $row['direccion']
        ];
    }
    
    $productos[] = [
        'nombre' => $row['nombre_producto'],
        'precio' => $row['precio_producto'],
        'cantidad' => $row['cantidad_comprada'],
        'subtotal' => $row['precio_producto'] * $row['cantidad_comprada']
    ];
    
    $subtotal += $row['precio_producto'] * $row['cantidad_comprada'];
}

// Calcular totales
$envio = $subtotal >= 500 ? 0 : 50;
$impuestos = $subtotal * 0.18;
$total = $subtotal + $envio + $impuestos;

// Generar número de boleta
$numero_boleta = 'B' . date('Y') . '-' . sprintf('%06d', $id_usuario) . '-' . date('md');

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <?php include "head_html.php"?>
    <title>GSITEC PERU - Boleta de Venta</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
        .print-only { display: none; }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Navigation Bar (No print) -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg no-print">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <a href="../index.php" class="text-white text-xl font-bold">GSITEC PERU</a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Inicio</a>
                    <a href="./historial_individual.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Historial</a>
                    <span class="text-cyan-400 font-semibold">Boleta de Venta</span>
                    
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
                            Hola, <span class="font-semibold text-cyan-400"><?=$_SESSION['sesion_personal']['nombre']?></span>
                        </span>
                        <a href="./cerrar_sesion.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Action Buttons (No print) -->
        <div class="flex justify-center space-x-4 mb-8 no-print">
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path>
                </svg>
                Imprimir Boleta
            </button>
            <button onclick="downloadPDF()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Descargar PDF
            </button>
            <a href="./historial_individual.php" class="bg-techblue-600 hover:bg-techblue-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0L3 11.414a1 1 0 010-1.414L6.293 6.707a1 1 0 011.414 1.414L5.414 10.5H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Volver al Historial
            </a>
        </div>

        <!-- Invoice/Receipt -->
        <div id="invoice" class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 text-white p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">GSITEC PERU</h1>
                        <p class="text-techblue-100">Tu tienda de tecnología de confianza</p>
                        <p class="text-sm text-techblue-200 mt-2">RUC: 20123456789</p>
                        <p class="text-sm text-techblue-200">Av. Tecnología 123, Lima - Perú</p>
                    </div>
                    <div class="text-right">
                        <div class="bg-white text-techblue-600 px-4 py-2 rounded-lg font-bold text-lg">
                            BOLETA DE VENTA
                        </div>
                        <div class="mt-2 text-techblue-100">
                            <p class="font-semibold">N° <?= $numero_boleta ?></p>
                            <p class="text-sm">Fecha: <?= date('d/m/Y H:i:s', strtotime($ultima_fecha)) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="p-8 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Datos del Cliente</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nombre:</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($usuario_info['nombre']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Correo:</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($usuario_info['correo']) ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Dirección:</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($usuario_info['direccion']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Detalle de Productos</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900 dark:text-white">Producto</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900 dark:text-white">Cantidad</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Precio Unit.</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-900 dark:text-white">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-4 px-4 text-gray-900 dark:text-white"><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td class="py-4 px-4 text-center text-gray-900 dark:text-white"><?= $producto['cantidad'] ?></td>
                                <td class="py-4 px-4 text-right text-gray-900 dark:text-white">S/ <?= number_format($producto['precio'], 2, '.', ',') ?></td>
                                <td class="py-4 px-4 text-right font-semibold text-gray-900 dark:text-white">S/ <?= number_format($producto['subtotal'], 2, '.', ',') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex justify-end">
                        <div class="w-full max-w-sm space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-gray-900 dark:text-white font-medium">S/ <?= number_format($subtotal, 2, '.', ',') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Envío:</span>
                                <span class="text-gray-900 dark:text-white font-medium">
                                    <?= $envio == 0 ? 'GRATIS' : 'S/ ' . number_format($envio, 2, '.', ',') ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Impuestos (18%):</span>
                                <span class="text-gray-900 dark:text-white font-medium">S/ <?= number_format($impuestos, 2, '.', ',') ?></span>
                            </div>
                            <hr class="border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between">
                                <span class="text-xl font-bold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-2xl font-bold text-green-600 dark:text-green-400">S/ <?= number_format($total, 2, '.', ',') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 p-8 border-t border-gray-200 dark:border-gray-600">
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-400 mb-2">¡Gracias por tu compra!</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        Para consultas o soporte, contáctanos a soporte@gsitecperu.com
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">
                        Este documento es una representación impresa de una boleta de venta electrónica.
                    </p>
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

        // Load dark mode preference
        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });

        // PDF Download functionality
        function downloadPDF() {
            // Simple PDF generation using browser's print to PDF
            const originalTitle = document.title;
            document.title = 'Boleta_' + '<?= $numero_boleta ?>';
            window.print();
            document.title = originalTitle;
        }

        // Auto-print on page load (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // });
    </script>
</body>
</html>