<?php
// Iniciar sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once("./config/config.php");
?>

<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include("./php/head_html.php"); ?>
    <title>GSITEC PERU - Página de inicio</title>
    <!-- icono -->
    <link rel="shortcut icon" href="./img/logo.jpg">
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'techblue': {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        'cyan': {
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Bootstrap solo para JavaScript del carrusel -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <!-- Font Awesome para iconos -->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    

</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Navigation Bar -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo/Brand -->
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="#" class="text-white text-xl font-bold">GSITEC PERU</a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <?php if (!isset($_SESSION['sesion_personal'])): ?>
                    <!-- Not logged in menu -->
                    <a href="./php/registro.php" class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        Registrarse
                    </a>
                    <a href="./php/iniciar_sesion.php" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Ingresar
                    </a>
                    <?php else: ?>
                    <!-- Logged in menu -->
                    <div class="flex items-center space-x-4">
                        <span class="text-white">
                            Hola, <span class="font-semibold text-cyan-400"><?=$_SESSION['sesion_personal']['nombre']?></span>
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
                                <a href="./php/consultar_historial.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-techblue-50 dark:hover:bg-techblue-900 rounded-t-lg">
                                    📋 Consultar historial
                                </a>
                                <a href="./php/modificar_productos.php" class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-techblue-50 dark:hover:bg-techblue-900 rounded-b-lg">
                                    ⚙️ Modificar productos
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <a href="./php/perfil.php" class="text-white hover:text-cyan-400 transition-colors duration-200">
                            👤 Perfil
                        </a>
                        <a href="./php/carrito.php" class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                            </svg>
                            Carrito
                        </a>
                        <a href="./php/cerrar_sesion.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            Cerrar sesión
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="#" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    
                    <?php if (!isset($_SESSION['sesion_personal'])): ?>
                    <a href="./php/registro.php" class="text-white hover:text-cyan-400 transition-colors duration-200">👤 Registrarse</a>
                    <a href="./php/iniciar_sesion.php" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        Ingresar
                    </a>
                    <?php else: ?>
                    <span class="text-cyan-400">Hola, <?=$_SESSION['sesion_personal']['nombre']?></span>
                    <?php if($_SESSION['sesion_personal']['super']==1): ?>
                    <a href="./php/consultar_historial.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📋 Consultar historial</a>
                    <a href="./php/modificar_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">⚙️ Modificar productos</a>
                    <?php endif; ?>
                    <a href="./php/perfil.php" class="text-white hover:text-cyan-400 transition-colors duration-200">👤 Perfil</a>
                    <a href="./php/carrito.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🛒 Carrito</a>
                    <a href="./php/cerrar_sesion.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        Cerrar sesión
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <div class="mt-16 relative">
        <div id="myCarousel" class="carousel slide relative" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
                <li data-target="#myCarousel" data-slide-to="0" class="active w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 cursor-pointer"></li>
                <li data-target="#myCarousel" data-slide-to="1" class="w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 cursor-pointer"></li>
                <li data-target="#myCarousel" data-slide-to="2" class="w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 cursor-pointer"></li>
                <li data-target="#myCarousel" data-slide-to="3" class="w-3 h-3 bg-white rounded-full opacity-70 hover:opacity-100 cursor-pointer"></li>
            </ol>
            
            <!-- Slides -->
            <div class="carousel-inner relative w-full overflow-hidden">
                <div class="item active relative">
                    <img src="./img/carrusel/b.jpg" alt="setup1" class="w-full h-64 md:h-96 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h3 class="text-2xl md:text-4xl font-bold mb-2">Monitores</h3>
                            <p class="text-lg md:text-xl">y accesorios</p>
                        </div>
                    </div>
                </div>
                <div class="item relative">
                    <img src="./img/carrusel/a.jpg" alt="setup2" class="w-full h-64 md:h-96 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h3 class="text-2xl md:text-4xl font-bold mb-2">Comodidad</h3>
                            <p class="text-lg md:text-xl">y confiabilidad</p>
                        </div>
                    </div>
                </div>
                <div class="item relative">
                    <img src="./img/carrusel/c.jpg" alt="setup3" class="w-full h-64 md:h-96 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h3 class="text-2xl md:text-4xl font-bold mb-2">Al mejor precio</h3>
                            <p class="text-lg md:text-xl">ofertas todos los días</p>
                        </div>
                    </div>
                </div>
                <div class="item relative">
                    <img src="./img/carrusel/d.jpg" alt="setup4" class="w-full h-64 md:h-96 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h3 class="text-2xl md:text-4xl font-bold mb-2">Bienvenido</h3>
                            <p class="text-lg md:text-xl">a una tienda como tú</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Controls -->
            <a class="left carousel-control absolute top-1/2 left-4 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-2 text-white cursor-pointer z-10" href="#myCarousel" data-slide="prev">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </a>
            <a class="right carousel-control absolute top-1/2 right-4 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-2 text-white cursor-pointer z-10" href="#myCarousel" data-slide="next">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Title Section -->
    <div class="py-8 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-center text-techblue-600 dark:text-techblue-400">
                Lista de Artículos
            </h1>
            <p class="text-center text-gray-600 dark:text-gray-400 mt-2">
                Los mejores componentes y accesorios para tu setup
            </p>
        </div>
    </div>

    <!-- Products Grid -->
    <main class="py-8 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php
                // Crear una conexión
                $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
                    
                // verificar connection con la BD
                if (mysqli_connect_errno()) :
                    echo '<div class="col-span-full text-center text-red-500 py-12">';
                    echo '<p class="text-xl">Error de conexión: ' . mysqli_connect_error() . '</p>';
                    echo '</div>';
                else:
                    $result = mysqli_query($con, "SELECT * FROM producto;");
                    $productos_mostrados = 0;
                    
                    while ($row = mysqli_fetch_array($result)): 
                        if($row['cantidad_disponible']==0){
                            continue;
                        }
                        $productos_mostrados++;
                        ?>
                        <!-- Product Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group product-card fade-in">
                            <div class="relative overflow-hidden">
                                <img 
                                    class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" 
                                    src="./img/productos/<?= $row['id_producto'] ?>.png" 
                                    alt="<?= htmlspecialchars($row['nombre_producto']) ?>"
                                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE5MiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE5MiIgZmlsbD0iIzNCODJGNiIvPjx0ZXh0IHg9IjEwMCIgeT0iMTA1IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                                >
                                <div class="absolute top-2 right-2 bg-techblue-600 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    Stock: <?= $row['cantidad_disponible'] ?>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <div class="h-20 flex items-center justify-center mb-4">
                                    <h3 class="text-gray-800 dark:text-gray-200 font-semibold text-center text-sm leading-tight">
                                        <?= htmlspecialchars($row['nombre_producto']) ?>
                                    </h3>
                                </div>
                                
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <p class="text-2xl font-bold text-techblue-600 dark:text-techblue-400 text-center mb-4">
                                        $<?= number_format(floatval($row['precio_producto']), 2, '.', ',') ?>
                                    </p>
                                    
                                    <?php if (isset($_SESSION['sesion_personal'])): ?>
                                        <a href="./php/info_producto.php?id=<?= $row['id_producto'] ?>" 
                                           class="w-full bg-techblue-600 hover:bg-cyan-500 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center transform hover:scale-105 btn-primary">
                                            Comprar
                                        </a>
                                    <?php else: ?>
                                        <a href="./php/iniciar_sesion.php" 
                                           class="w-full bg-gray-500 hover:bg-techblue-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 block text-center">
                                            Iniciar Sesión
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    
                    // Si no hay productos
                    if ($productos_mostrados == 0):
                        ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 max-w-md mx-auto">
                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    No hay productos disponibles
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Vuelve pronto para ver nuestros nuevos productos
                                </p>
                            </div>
                        </div>
                        <?php
                    endif;
                    
                    // cerrar conexión
                    mysqli_close($con);
                endif;
                ?>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-techblue-800 dark:bg-techblue-900 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="mb-4">
                <h3 class="text-xl font-bold mb-2">GSITEC PERU</h3>
                <p class="text-techblue-300">Tu tienda de tecnología de confianza</p>
            </div>
            <div class="border-t border-techblue-700 pt-4">
                <p class="text-sm text-techblue-300">
                    COPYRIGHT © 2024<br>
                    DISEÑADO POR: GRUPO 04
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Dark Mode Toggle
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            
            // Guardar preferencia
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

        // Cargar preferencia de modo oscuro (por defecto claro)
        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });

        // Smooth scrolling para enlaces
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

    <!-- Custom Bootstrap Carousel CSS -->
    <style>
        .carousel-inner > .item {
            display: none;
        }
        .carousel-inner > .active {
            display: block;
        }
        .carousel-indicators .active {
            opacity: 1;
            background-color: #22d3ee;
        }
    </style>
</body>

</html>
