<?php
require_once("../config/config.php");
// Iniciar sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Procesar validación de registro ANTES de enviar cualquier HTML
include "./valida_registro.php";
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "./head_html.php"; ?>
    <title>GSITEC PERU - Registro</title>
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

                    <span class="bg-cyan-500 text-white px-4 py-2 rounded-lg font-semibold">
                        Registrarse
                    </span>
                    <a href="./iniciar_sesion.php" class="text-white hover:text-cyan-400 transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Ingresar
                    </a>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./../index.php" class="text-white hover:text-cyan-400 transition-colors duration-200">Lista de productos</a>
                    <span class="text-cyan-400 font-semibold">👤 Registrarse</span>
                    <a href="./iniciar_sesion.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📝 Ingresar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-techblue-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Crear Cuenta
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Únete a la comunidad GSITEC PERU
                </p>
            </div>

            <!-- Registration Form -->
            <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-xl rounded-xl">
                
                <form class="space-y-6" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"])?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Username Field -->
                        <div class="md:col-span-2">
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
                                    value="<?= isset($nombre) ? $nombre : '' ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Ingresa tu nombre de usuario"
                                >
                            </div>
                            <?php if(isset($nombreErr) && $nombreErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $nombreErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Password Field -->
                        <div class="md:col-span-2">
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
                                    value="<?= isset($contra) ? $contra : '' ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Crea una contraseña segura"
                                >
                            </div>
                            <?php if(isset($contraErr) && $contraErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $contraErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Birth Date Field -->
                        <div>
                            <label for="fnac" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fecha de nacimiento
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="date" 
                                    name="fnac" 
                                    id="fnac"
                                    value="<?= isset($fechanacimiento) ? $fechanacimiento : '' ?>"
                                    max="2006-01-01"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                >
                            </div>
                            <?php if(isset($fechanacimientoErr) && $fechanacimientoErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $fechanacimientoErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="correo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Correo electrónico
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="email" 
                                    name="correo" 
                                    id="correo"
                                    autocomplete="email"
                                    value="<?= isset($correo) ? $correo : '' ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="ejemplo@correo.com"
                                >
                            </div>
                            <?php if(isset($correoErr) && $correoErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $correoErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Credit Card Field -->
                        <div>
                            <label for="numero_tarjeta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de tarjeta
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v2H4V6zm0 4h12v4H4v-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="numero_tarjeta" 
                                    id="numero_tarjeta"
                                    value="<?= isset($ntarjeta) ? $ntarjeta : '' ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="1234 5678 9012 3456"
                                >
                            </div>
                            <?php if(isset($ntarjetaErr) && $ntarjetaErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $ntarjetaErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Address Field -->
                        <div>
                            <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dirección
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="direccion" 
                                    id="direccion"
                                    autocomplete="address-level1"
                                    value="<?= isset($address) ? $address : '' ?>"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-techblue-500 focus:border-transparent transition-colors duration-200"
                                    placeholder="Tu dirección completa"
                                >
                            </div>
                            <?php if(isset($addressErr) && $addressErr): ?>
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    <?php echo $addressErr; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-techblue-600 hover:bg-techblue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-techblue-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                            </svg>
                            Crear mi cuenta
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            ¿Ya tienes cuenta? 
                            <a href="./iniciar_sesion.php" class="font-medium text-techblue-600 hover:text-techblue-500 dark:text-techblue-400 dark:hover:text-techblue-300 transition-colors duration-200">
                                Inicia sesión aquí
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-8">
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

        // Formatear número de tarjeta mientras se escribe
        document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedInputValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedInputValue;
        });
    </script>
</body>

</html>