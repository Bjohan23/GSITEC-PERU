<?php
require_once("../config/config.php");
// Iniciar sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Procesar validación de inicio de sesión ANTES de enviar cualquier HTML
include "./valida_inicio_sesion.php";
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "./head_html.php"; ?>
    <title>GSITEC PERU - Iniciar Sesión</title>
    <!-- icono -->
    <link rel="shortcut icon" href="./../img/logo.jpg">
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
                    <a href="./../index.php" class="text-white text-xl font-bold">GSITEC PERU</a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="./../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <a href="./registro.php" class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        Registrarse
                    </a>
                    <span class="bg-cyan-500 text-white px-4 py-2 rounded-lg font-semibold">
                        Ingresar
                    </span>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    <a href="./registro.php" class="text-white hover:text-cyan-400 transition-colors duration-200">👤 Registrarse</a>
                    <span class="text-cyan-400 font-semibold">📝 Ingresar</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-techblue-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Iniciar Sesión
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Accede a tu cuenta de GSITEC PERU
                </p>
            </div>

            <!-- Login Form -->
            <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-xl rounded-xl">
                <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <!-- Username Field -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre de usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="nombre"
                                autocomplete="username"
                                value="<?= isset($nombre) ? htmlspecialchars($nombre) : '' ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Ingresa tu nombre de usuario"
                            >
                        </div>
                        <?php if(isset($nombreErr) && $nombreErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <?php echo htmlspecialchars($nombreErr); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="contrasena" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="contrasena" 
                                id="contrasena"
                                autocomplete="current-password"
                                value="<?= isset($contra) ? htmlspecialchars($contra) : '' ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Ingresa tu contraseña"
                            >
                        </div>
                        <?php if(isset($contraErr) && $contraErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <?php echo htmlspecialchars($contraErr); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-techblue-600 hover:bg-techblue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-techblue-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Iniciar Sesión
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ¿No tienes cuenta? 
                            <a href="./registro.php" class="font-medium text-techblue-600 hover:text-techblue-500 dark:text-techblue-400 dark:hover:text-techblue-300 transition-colors duration-200">
                                Registrarse aquí
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="./../index.php" class="inline-flex items-center text-techblue-600 hover:text-techblue-500 dark:text-techblue-400 dark:hover:text-techblue-300 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>

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
    </script>
</body>

</html>