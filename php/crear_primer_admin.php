<?php
require_once("../config/config.php");
require_once("./verificar_primer_admin.php");

// Si ya hay administradores, redirigir al login normal
if (hayAdministradores()) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

// Procesar formulario
$nombreErr = $correoErr = $contraErr = $confirmarErr = "";
$nombre = $correo = $contra = $confirmar = "";
$hay_errores = false;

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function checkemail($str) {
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nombre"])) {
        $nombreErr = "* Nombre de usuario requerido";
        $hay_errores = true;
    } else {
        $nombre = test_input($_POST["nombre"]);
        if (strlen($nombre) < 3) {
            $nombreErr = "* El nombre debe tener al menos 3 caracteres";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["correo"])) {
        $correoErr = "* Correo electrónico requerido";
        $hay_errores = true;
    } else {
        $correo = test_input($_POST["correo"]);
        if (!checkemail($correo)) {
            $correoErr = "* Email inválido";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["contrasena"])) {
        $contraErr = "* Contraseña requerida";
        $hay_errores = true;
    } else {
        $contra = test_input($_POST["contrasena"]);
        if (strlen($contra) < 6) {
            $contraErr = "* La contraseña debe tener al menos 6 caracteres";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["confirmar_contrasena"])) {
        $confirmarErr = "* Confirmar contraseña requerido";
        $hay_errores = true;
    } else {
        $confirmar = test_input($_POST["confirmar_contrasena"]);
        if ($contra !== $confirmar) {
            $confirmarErr = "* Las contraseñas no coinciden";
            $hay_errores = true;
        }
    }
    
    if (!$hay_errores) {
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            $nombreErr = "Error de conexión a la base de datos";
            $hay_errores = true;
        } else {
            // Verificar una vez más que no hay administradores
            if (!hayAdministradores()) {
                // Hashear la contraseña
                $contra_hash = password_hash($contra, PASSWORD_DEFAULT);
                
                $stmt = mysqli_prepare($con, "INSERT INTO administradores (nombre_usuario, correo, contrasena, nivel_admin) VALUES (?, ?, ?, 2)");
                mysqli_stmt_bind_param($stmt, "sss", $nombre, $correo, $contra_hash);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_close($con);
                    // Redirigir al login de admin con mensaje de éxito
                    header("Location: ./iniciar_sesion_admin.php?mensaje=admin_creado");
                    exit();
                } else {
                    $nombreErr = "Error al crear el administrador";
                    $hay_errores = true;
                }
            } else {
                header("Location: ./iniciar_sesion_admin.php");
                exit();
            }
            mysqli_close($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "./head_html.php"; ?>
    <title>GSITEC PERU - Crear Primer Administrador</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <a href="../index.php" class="text-white text-xl font-bold">GSITEC PERU</a>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-20 w-20 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    🔐 Configuración Inicial
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    Bienvenido a GSITEC PERU. Para comenzar, necesitas crear la cuenta del primer administrador del sistema.
                </p>
                <div class="mt-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>📝 Importante:</strong> Esta cuenta tendrá privilegios de Super Administrador y podrá gestionar todos los aspectos del sistema.
                    </p>
                </div>
            </div>

            <!-- Creation Form -->
            <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-2xl rounded-xl border border-gray-200 dark:border-gray-700">
                <form class="space-y-6" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <!-- Username Field -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre de Usuario Administrador
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="nombre"
                                value="<?= htmlspecialchars($nombre) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="admin_principal"
                            >
                        </div>
                        <?php if($nombreErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $nombreErr ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Correo Electrónico
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
                                value="<?= htmlspecialchars($correo) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="admin@gsitecperu.com"
                            >
                        </div>
                        <?php if($correoErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $correoErr ?></p>
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
                                value="<?= htmlspecialchars($contra) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Mínimo 6 caracteres"
                            >
                        </div>
                        <?php if($contraErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $contraErr ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirmar_contrasena" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirmar Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="confirmar_contrasena" 
                                id="confirmar_contrasena"
                                value="<?= htmlspecialchars($confirmar) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Repetir contraseña"
                            >
                        </div>
                        <?php if($confirmarErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $confirmarErr ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            🚀 Crear Administrador Principal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Info -->
            <div class="text-center">
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm text-green-800 dark:text-green-200 font-medium">
                            🔐 Esta configuración se realiza solo una vez
                        </span>
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

        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>

</html>
