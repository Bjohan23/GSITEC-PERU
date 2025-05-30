<?php
require_once("../config/config.php");
require_once("./verificar_primer_admin.php");

// Si no hay administradores, redirigir al formulario de creación
if (!hayAdministradores()) {
    header("Location: ./crear_primer_admin.php");
    exit();
}

// Iniciar sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado como admin, redirigir al panel
if (isset($_SESSION['sesion_admin'])) {
    header("Location: ./panel_admin.php");
    exit();
}

// Variables para manejo de errores y campos
$nombreErr = $contraErr = "";
$nombre = $contra = "";
$hay_errores = false;
$mensaje_info = "";

// Mensaje de éxito si viene de crear primer admin
if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'admin_creado') {
    $mensaje_info = "¡Administrador principal creado exitosamente! Ahora puedes iniciar sesión.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nombre"])) {
        $nombreErr = "* Nombre requerido";
        $hay_errores = true;
    } else {
        $nombre = test_input($_POST["nombre"]);
    }
    
    if (empty($_POST["contrasena"])) {
        $contraErr = "* Contraseña requerida";
        $hay_errores = true;
    } else {
        $contra = test_input($_POST["contrasena"]);
    }

    if (!$hay_errores) {
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            $nombreErr = "Error de conexión a la base de datos";
            $hay_errores = true;
        } else {
            $stmt = mysqli_prepare($con, "SELECT id_administrador, nivel_admin, nombre_usuario FROM administradores WHERE (nombre_usuario = ? OR correo = ?) AND contrasena = ? AND activo = 1");
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $nombre, $contra);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_array($result)) {
                $id = $row['id_administrador'];
                $nivel = $row['nivel_admin'];
                $nombre_usuario = $row['nombre_usuario'];
                
                // Actualizar último acceso
                $stmt_update = mysqli_prepare($con, "UPDATE administradores SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id_administrador = ?");
                mysqli_stmt_bind_param($stmt_update, "i", $id);
                mysqli_stmt_execute($stmt_update);
                
                // Crear sesión de administrador
                $_SESSION['sesion_admin'] = array();
                $_SESSION['sesion_admin']['id'] = $id;
                $_SESSION['sesion_admin']['nivel'] = $nivel;
                $_SESSION['sesion_admin']['nombre'] = $nombre_usuario;
                
                mysqli_close($con);
                header("Location: ./panel_admin.php");
                exit();
            } else {
                $nombreErr = "Credenciales incorrectas o cuenta inactiva";
                $hay_errores = true;
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
    <title>GSITEC PERU - Acceso Administrador</title>
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
                            <path d="M17.293 13.293A8 8 0 716.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <a href="./iniciar_sesion.php" class="text-white hover:text-cyan-400 transition-colors duration-200">
                        🙋‍♂️ Acceso Cliente
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    👑 Acceso Administrador
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Panel de administración de GSITEC PERU
                </p>
            </div>

            <!-- Success Message -->
            <?php if($mensaje_info): ?>
            <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-green-800 dark:text-green-200 font-medium">
                        <?= htmlspecialchars($mensaje_info) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-xl rounded-xl">
                <form class="space-y-6" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <!-- Username Field -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Usuario o Correo
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
                                autocomplete="username"
                                value="<?= htmlspecialchars($nombre) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Usuario administrador"
                            >
                        </div>
                        <?php if($nombreErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <?= htmlspecialchars($nombreErr) ?>
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
                                value="<?= htmlspecialchars($contra) ?>"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-200"
                                placeholder="Contraseña del administrador"
                            >
                        </div>
                        <?php if($contraErr): ?>
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                <?= htmlspecialchars($contraErr) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-[1.02]"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Acceder al Panel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="../index.php" class="inline-flex items-center text-purple-600 hover:text-purple-500 dark:text-purple-400 dark:hover:text-purple-300 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Volver a la tienda
                </a>
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
