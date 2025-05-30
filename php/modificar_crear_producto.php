<?php
session_start();
if (!isset($_SESSION['sesion_personal'])) {
    header("Location: ./iniciar_sesion.php");
}

// Verificar que sea admin
if ($_SESSION['sesion_personal']['super'] != 1) {
    header("Location: ../index.php");
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

$titulo = $opcion==1 ? "Modificar producto" : "Agregar producto";
$_SESSION['sesion_personal']['id_producto'] = $id_producto;
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - <?= $titulo ?></title>
    <!-- icono -->
    <link rel="shortcut icon" href="../img/logo.jpg">
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
                    <span class="text-cyan-400 font-semibold"><?= $titulo ?></span>
                    
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
                            👑 Admin: <span class="font-semibold text-cyan-400"><?=$_SESSION['sesion_personal']['nombre']?></span>
                        </span>
                        
                        <!-- Admin dropdown -->
                        <div class="relative group">
                            <button class="bg-cyan-500 text-white px-4 py-2 rounded-lg font-semibold flex items-center">
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
                    <span class="text-cyan-400 font-semibold">⚙️ <?= $titulo ?></span>
                    <span class="text-cyan-400">👑 Admin: <?=$_SESSION['sesion_personal']['nombre']?></span>
                    <a href="../php/consultar_historial.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📋 Consultar historial</a>
                    <a href="../php/modificar_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">⚙️ Modificar productos</a>
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
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                <?= $titulo ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                <?= $opcion == 1 ? "Actualiza la información del producto" : "Añade un nuevo producto al catálogo" ?>
            </p>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        Información del Producto
                    </h2>
                </div>

                <div class="p-6">
                    <form action="<?= $opcion==1 ? "hacer_modificacion.php" : "hacer_registro.php" ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del producto -->
                            <div class="md:col-span-2">
                                <label for="nombre_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nombre del producto
                                </label>
                                <input 
                                    type="text" 
                                    id="nombre_producto" 
                                    name="nombre_producto" 
                                    value="<?= htmlspecialchars($nombre_producto) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Ingresa el nombre del producto"
                                >
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Descripción
                                </label>
                                <textarea 
                                    id="descripcion_producto" 
                                    name="descripcion_producto" 
                                    rows="4"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200 resize-none"
                                    placeholder="Describe las características y especificaciones del producto"
                                ><?= htmlspecialchars($descripcion_producto) ?></textarea>
                            </div>

                            <!-- Cantidad disponible -->
                            <div>
                                <label for="cantidad_disponible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cantidad disponible
                                </label>
                                <input 
                                    type="number" 
                                    id="cantidad_disponible" 
                                    name="cantidad_disponible" 
                                    value="<?= htmlspecialchars($cantidad_disponible) ?>"
                                    min="0"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="0"
                                >
                            </div>

                            <!-- Precio -->
                            <div>
                                <label for="precio_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Precio (USD)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        id="precio_producto" 
                                        name="precio_producto" 
                                        value="<?= htmlspecialchars($precio_producto) ?>"
                                        min="0"
                                        required
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                        placeholder="0.00"
                                    >
                                </div>
                            </div>

                            <!-- Fabricante -->
                            <div>
                                <label for="fabricante" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Fabricante
                                </label>
                                <input 
                                    type="text" 
                                    id="fabricante" 
                                    name="fabricante" 
                                    value="<?= htmlspecialchars($fabricante) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Ej: Apple, Samsung, ASUS"
                                >
                            </div>

                            <!-- Origen -->
                            <div>
                                <label for="origen" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    País de origen
                                </label>
                                <input 
                                    type="text" 
                                    id="origen" 
                                    name="origen" 
                                    value="<?= htmlspecialchars($origen) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Ej: China, Estados Unidos, Japón"
                                >
                            </div>

                            <!-- Categoría -->
                            <div class="md:col-span-2">
                                <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Categoría
                                </label>
                                <input 
                                    type="text" 
                                    id="categoria" 
                                    name="categoria" 
                                    value="<?= htmlspecialchars($categoria) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Ej: Electrónicos, Accesorios, Componentes"
                                >
                            </div>

                            <!-- Imagen del producto (solo para agregar) -->
                            <?php if($opcion == 2): ?>
                            <div class="md:col-span-2">
                                <label for="imagen_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Imagen del producto
                                </label>
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <div class="mt-4">
                                        <label for="imagen_producto" class="cursor-pointer bg-techblue-600 hover:bg-techblue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                            Seleccionar imagen
                                        </label>
                                        <input type="file" id="imagen_producto" name="imagen_producto" accept="image/*" required class="sr-only">
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        PNG, JPG, GIF hasta 10MB
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                            <button 
                                type="submit"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <?= $titulo ?>
                            </button>
                            
                            <a 
                                href="../php/modificar_productos.php"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 block text-center"
                            >
                                ← Volver a productos
                            </a>
                        </div>
                    </form>
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

        // File input preview
        document.getElementById('imagen_producto')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = e.target.previousElementSibling;
                label.textContent = file.name;
                label.className = 'cursor-pointer bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200';
            }
        });
    </script>
</body>

</html>