<?php
require_once("../config/config.php");
session_start();

$mensaje_a_mostrar = null;

if (isset($_SESSION['mensaje_gestion_flash'])) {
    $mensaje_a_mostrar = $_SESSION['mensaje_gestion_flash'];
    unset($_SESSION['mensaje_gestion_flash']);
}

if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

// SUPER ADMIN ES NIVEL 2, y la clave en sesión es 'nivel'
if (!isset($_SESSION['sesion_admin']['nivel']) || $_SESSION['sesion_admin']['nivel'] != 2) {
    $_SESSION['mensaje_panel_flash'] = ['tipo' => 'error', 'texto' => 'Acceso denegado. Se requiere nivel de Super Administrador.'];
    header("Location: ./panel_admin.php");
    exit();
}

$admin_actual = $_SESSION['sesion_admin']; // sesión: ['id'], ['nivel'], ['nombre']


if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['agregar_admin']) || isset($_POST['editar_admin']))) {
    $con_accion = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    $mensaje_operacion = null;

    if (!$con_accion) {
        $mensaje_operacion = "Error de conexión: " . mysqli_connect_error();
    } else {
        mysqli_set_charset($con_accion, "utf8mb4");

        // El campo del formulario para el nombre debe ser 'nombre_usuario'
        $nombre_usuario_form = trim($_POST['nombre_usuario']);
        $correo_form = trim($_POST['correo']);
        $contrasena_form = $_POST['contrasena'];
        $nivel_admin_form = isset($_POST['nivel_admin']) ? (int)$_POST['nivel_admin'] : 1;
        if ($nivel_admin_form != 1 && $nivel_admin_form != 2) {
             $nivel_admin_form = 1;
        }
        $id_admin_editar_form = isset($_POST['id_admin_editar']) ? (int)$_POST['id_admin_editar'] : null;

        if (empty($nombre_usuario_form) || empty($correo_form) || ($id_admin_editar_form === null && empty($contrasena_form))) {
            $mensaje_operacion = "Campos obligatorios incompletos.";
        } elseif (!filter_var($correo_form, FILTER_VALIDATE_EMAIL)) {
            $mensaje_operacion = "Correo no válido.";
        } else {
            if ($id_admin_editar_form) {
                $sql_check = "SELECT id_administrador FROM administradores WHERE (nombre_usuario = ? OR correo = ?) AND id_administrador != ?";
                $stmt_check = mysqli_prepare($con_accion, $sql_check);
                mysqli_stmt_bind_param($stmt_check, "ssi", $nombre_usuario_form, $correo_form, $id_admin_editar_form);
            } else {
                $sql_check = "SELECT id_administrador FROM administradores WHERE nombre_usuario = ? OR correo = ?";
                $stmt_check = mysqli_prepare($con_accion, $sql_check);
                mysqli_stmt_bind_param($stmt_check, "ss", $nombre_usuario_form, $correo_form);
            }

            if ($stmt_check) {
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                if (mysqli_num_rows($result_check) > 0) {
                    $mensaje_operacion = "Nombre de usuario o correo ya existe.";
                }
                mysqli_stmt_close($stmt_check);
            } else {
                $mensaje_operacion = "Error DB (check): " . mysqli_error($con_accion);
            }
            
            if ($mensaje_operacion === null) {
                if ($id_admin_editar_form) {
                    if ($id_admin_editar_form == $admin_actual['id'] && $admin_actual['nivel'] == 2 && $nivel_admin_form != 2) {
                        $stmt_count_super = mysqli_prepare($con_accion, "SELECT COUNT(*) as total_super FROM administradores WHERE nivel_admin = 2");
                        mysqli_stmt_execute($stmt_count_super);
                        $result_count_super = mysqli_stmt_get_result($stmt_count_super);
                        $row_count_super = mysqli_fetch_assoc($result_count_super);
                        mysqli_stmt_close($stmt_count_super);
                        if ($row_count_super && $row_count_super['total_super'] <= 1) {
                            $mensaje_operacion = "No puedes cambiar el nivel del único Super Administrador.";
                        }
                    }

                    if($mensaje_operacion === null) {
                        if (!empty($contrasena_form)) {
                            $hash_contrasena = password_hash($contrasena_form, PASSWORD_DEFAULT);
                            $sql_update = "UPDATE administradores SET nombre_usuario = ?, correo = ?, contrasena = ?, nivel_admin = ? WHERE id_administrador = ?";
                            $stmt_update = mysqli_prepare($con_accion, $sql_update);
                            mysqli_stmt_bind_param($stmt_update, "ssssi", $nombre_usuario_form, $correo_form, $hash_contrasena, $nivel_admin_form, $id_admin_editar_form);
                        } else {
                            $sql_update = "UPDATE administradores SET nombre_usuario = ?, correo = ?, nivel_admin = ? WHERE id_administrador = ?";
                            $stmt_update = mysqli_prepare($con_accion, $sql_update);
                            mysqli_stmt_bind_param($stmt_update, "ssii", $nombre_usuario_form, $correo_form, $nivel_admin_form, $id_admin_editar_form);
                        }
                        if ($stmt_update && mysqli_stmt_execute($stmt_update)) {
                            $mensaje_operacion = "Admin actualizado.";
                            if ($id_admin_editar_form == $admin_actual['id']) {
                                if ($admin_actual['nivel'] != $nivel_admin_form) $_SESSION['sesion_admin']['nivel'] = $nivel_admin_form;
                                // Actualizar nombre en sesión si se cambió
                                if ($admin_actual['nombre'] != $nombre_usuario_form) $_SESSION['sesion_admin']['nombre'] = $nombre_usuario_form;
                            }
                        } else {
                            $mensaje_operacion = "Error DB (update): " . ($stmt_update ? mysqli_stmt_error($stmt_update) : mysqli_error($con_accion));
                        }
                        if($stmt_update) mysqli_stmt_close($stmt_update);
                    }
                } else {
                    $hash_contrasena = password_hash($contrasena_form, PASSWORD_DEFAULT);
                    $sql_insert = "INSERT INTO administradores (nombre_usuario, correo, contrasena, nivel_admin) VALUES (?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($con_accion, $sql_insert);
                    mysqli_stmt_bind_param($stmt_insert, "sssi", $nombre_usuario_form, $correo_form, $hash_contrasena, $nivel_admin_form);
                    if ($stmt_insert && mysqli_stmt_execute($stmt_insert)) {
                        $mensaje_operacion = "Admin agregado.";
                    } else {
                        $mensaje_operacion = "Error DB (insert): " . ($stmt_insert ? mysqli_stmt_error($stmt_insert) : mysqli_error($con_accion));
                    }
                    if($stmt_insert) mysqli_stmt_close($stmt_insert);
                }
            }
        }
        mysqli_close($con_accion);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Solo redirigir y poner en sesión si fue un POST
        $_SESSION['mensaje_gestion_flash'] = $mensaje_operacion;
        $page_param = isset($_GET['page']) && is_numeric($_GET['page']) ? '?page='.(int)$_GET['page'] : '';
        header("Location: gestion_admins.php" . $page_param);
        exit();
    }
}

if (isset($_GET['eliminar_admin']) && isset($_GET['id_admin'])) {
    $id_admin_eliminar = (int)$_GET['id_admin'];
    $mensaje_operacion = null;

    if ($id_admin_eliminar == $admin_actual['id']) {
        $mensaje_operacion = "No puedes eliminar tu propia cuenta.";
    } else {
        $con_eliminar = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (!$con_eliminar) {
            $mensaje_operacion = "Error de conexión.";
        } else {
            mysqli_set_charset($con_eliminar, "utf8mb4");
            $es_super_admin_a_eliminar = false;
            $stmt_check_nivel = mysqli_prepare($con_eliminar, "SELECT nivel_admin FROM administradores WHERE id_administrador = ?");
            mysqli_stmt_bind_param($stmt_check_nivel, "i", $id_admin_eliminar);
            mysqli_stmt_execute($stmt_check_nivel);
            $result_check_nivel = mysqli_stmt_get_result($stmt_check_nivel);
            if ($row_nivel = mysqli_fetch_assoc($result_check_nivel)) {
                if ($row_nivel['nivel_admin'] == 2) {
                    $es_super_admin_a_eliminar = true;
                }
            }
            mysqli_stmt_close($stmt_check_nivel);

            $puede_eliminar = true;
            if ($es_super_admin_a_eliminar) {
                $stmt_count_super = mysqli_prepare($con_eliminar, "SELECT COUNT(*) as total_super FROM administradores WHERE nivel_admin = 2");
                mysqli_stmt_execute($stmt_count_super);
                $result_count_super = mysqli_stmt_get_result($stmt_count_super);
                $row_count_super = mysqli_fetch_assoc($result_count_super);
                mysqli_stmt_close($stmt_count_super);
                if ($row_count_super && $row_count_super['total_super'] <= 1) {
                    $mensaje_operacion = "No se puede eliminar el único Super Administrador.";
                    $puede_eliminar = false;
                }
            }

            if ($puede_eliminar) {
                $sql_delete = "DELETE FROM administradores WHERE id_administrador = ?";
                $stmt_delete = mysqli_prepare($con_eliminar, $sql_delete);
                mysqli_stmt_bind_param($stmt_delete, "i", $id_admin_eliminar);
                if ($stmt_delete && mysqli_stmt_execute($stmt_delete)) {
                    if (mysqli_stmt_affected_rows($stmt_delete) > 0) $mensaje_operacion = "Admin eliminado.";
                    else $mensaje_operacion = "Admin no encontrado o ya eliminado.";
                } else {
                    $mensaje_operacion = "Error DB (delete): " . ($stmt_delete ? mysqli_stmt_error($stmt_delete) : mysqli_error($con_eliminar));
                }
                if($stmt_delete) mysqli_stmt_close($stmt_delete);
            }
            mysqli_close($con_eliminar);
        }
    }
    $_SESSION['mensaje_gestion_flash'] = $mensaje_operacion;
    $page_param = isset($_GET['page']) && is_numeric($_GET['page']) ? '?page='.(int)$_GET['page'] : '';
    header("Location: gestion_admins.php" . $page_param);
    exit();
}

$administradores = [];
$admin_a_editar = null;
$items_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$total_admins = 0;
$total_pages = 1;

$con_lista = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if (!$con_lista) {
    if ($mensaje_a_mostrar === null) $mensaje_a_mostrar = "Error de conexión al listar: " . mysqli_connect_error();
} else {
    mysqli_set_charset($con_lista, "utf8mb4");
    $total_admins_query = mysqli_query($con_lista, "SELECT COUNT(*) as total FROM administradores");
    if ($total_admins_query) {
        $total_admins_row = mysqli_fetch_assoc($total_admins_query);
        $total_admins = (int)($total_admins_row['total'] ?? 0);
    } elseif ($mensaje_a_mostrar === null) $mensaje_a_mostrar = "Error DB (count list): " . mysqli_error($con_lista);
    
    if ($total_admins > 0 && $items_per_page > 0) $total_pages = ceil($total_admins / $items_per_page);
    if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
    if ($current_page < 1) $current_page = 1;
    if ($total_pages == 0) $total_pages = 1; 
    $offset = ($current_page - 1) * $items_per_page;

    $sql_select_admins = "SELECT id_administrador, nombre_usuario, correo, nivel_admin, fecha_creacion, ultimo_acceso, activo 
                          FROM administradores ORDER BY nombre_usuario ASC LIMIT ? OFFSET ?";
    $stmt_select = mysqli_prepare($con_lista, $sql_select_admins);
    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "ii", $items_per_page, $offset);
        mysqli_stmt_execute($stmt_select);
        $result_admins = mysqli_stmt_get_result($stmt_select);
        while ($row = mysqli_fetch_assoc($result_admins)) $administradores[] = $row;
        mysqli_stmt_close($stmt_select);
    } elseif ($mensaje_a_mostrar === null) $mensaje_a_mostrar = "Error DB (select list): " . mysqli_error($con_lista);

    if (isset($_GET['editar_id']) && is_numeric($_GET['editar_id'])) {
        $id_editar_get = (int)$_GET['editar_id'];
        $sql_get_admin = "SELECT id_administrador, nombre_usuario, correo, nivel_admin FROM administradores WHERE id_administrador = ?";
        $stmt_get = mysqli_prepare($con_lista, $sql_get_admin);
        if ($stmt_get) {
            mysqli_stmt_bind_param($stmt_get, "i", $id_editar_get);
            mysqli_stmt_execute($stmt_get);
            $result_get = mysqli_stmt_get_result($stmt_get);
            $admin_a_editar = mysqli_fetch_assoc($result_get);
            mysqli_stmt_close($stmt_get);
            if (!$admin_a_editar && $mensaje_a_mostrar === null) $mensaje_a_mostrar = "Admin a editar no encontrado.";
        } elseif ($mensaje_a_mostrar === null) $mensaje_a_mostrar = "Error DB (get edit): " . mysqli_error($con_lista);
    }
    mysqli_close($con_lista);
}
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <?php include "head_html.php"; // Asegúrate que la ruta a head_html.php es correcta ?>
    <title>GSITEC PERU - Gestión de Administradores</title>
    <link rel="shortcut icon" href="../img/logo.jpg"> <!-- Asegúrate que la ruta al logo es correcta -->
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
                    <a href="./reportes_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📊 Reporte Ventas</a>
                    <span class="text-cyan-400 font-semibold">👤 Admins</span>
                    <button onclick="toggleDarkMode()" class="text-white hover:text-cyan-400 transition-colors duration-200 p-2 rounded-lg">
                        <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg>
                    </button>
                    <div class="flex items-center space-x-4">
                        <!-- CORRECCIÓN AQUÍ: Usar $admin_actual['nivel'] y $admin_actual['nombre'] -->
                        <span class="text-white">👑 <?= isset($admin_actual['nivel']) && $admin_actual['nivel'] == 2 ? 'Super Admin' : 'Admin' ?>: <span class="font-semibold text-cyan-400"><?= isset($admin_actual['nombre']) ? htmlspecialchars($admin_actual['nombre']) : 'Admin' ?></span></span>
                        <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <a href="./reportes_ventas.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📊 Reporte Ventas</a>
                    <span class="text-cyan-400 font-semibold">👤 Admins</span>
                    <!-- CORRECCIÓN AQUÍ: Usar $admin_actual['nombre'] -->
                    <span class="text-cyan-400">👑 <?= isset($admin_actual['nombre']) ? htmlspecialchars($admin_actual['nombre']) : 'Admin' ?></span>
                    <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">👤 Gestión de Administradores</h1>
            <p class="text-gray-600 dark:text-gray-400">Administra los usuarios con acceso al panel de administración.</p>
        </div>

        <!-- CORRECCIÓN AQUÍ: Usar $mensaje_a_mostrar -->
        <?php if ($mensaje_a_mostrar): ?>
        <div class="<?= (strpos(strtolower($mensaje_a_mostrar), 'error') !== false || strpos(strtolower($mensaje_a_mostrar), 'no puedes') !== false || strpos(strtolower($mensaje_a_mostrar), 'no válido') !== false || strpos(strtolower($mensaje_a_mostrar), 'denegado') !== false) ? 'bg-red-50 dark:bg-red-900 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200' : 'bg-green-50 dark:bg-green-900 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200' ?> border rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <?php if (strpos(strtolower($mensaje_a_mostrar), 'error') !== false || strpos(strtolower($mensaje_a_mostrar), 'no puedes') !== false || strpos(strtolower($mensaje_a_mostrar), 'no válido') !== false || strpos(strtolower($mensaje_a_mostrar), 'denegado') !== false): ?>
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <?php else: ?>
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <?php endif; ?>
                <span class="font-medium"><?= htmlspecialchars($mensaje_a_mostrar) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4"><?= $admin_a_editar ? 'Editar Administrador' : 'Agregar Nuevo Administrador' ?></h2>
            <!-- La acción del formulario debe ser esta misma página -->
            <form method="POST" action="gestion_admins.php<?= isset($_GET['page']) ? '?page='.(int)$_GET['page'] : '' ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($admin_a_editar): ?>
                    <input type="hidden" name="id_admin_editar" value="<?= $admin_a_editar['id_administrador'] ?>">
                <?php endif; ?>
                
                <div>
                    <!-- CORRECCIÓN AQUÍ: name="nombre_usuario" y value usa $admin_a_editar['nombre_usuario'] -->
                    <label for="nombre_usuario_form" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario_form" name="nombre_usuario" value="<?= htmlspecialchars($admin_a_editar['nombre_usuario'] ?? '') ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="correo_form" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico:</label>
                    <input type="email" id="correo_form" name="correo" value="<?= htmlspecialchars($admin_a_editar['correo'] ?? '') ?>" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="contrasena_form" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contraseña: <?= $admin_a_editar ? '<span class="text-xs">(Dejar en blanco para no cambiar)</span>' : '' ?></label>
                    <input type="password" id="contrasena_form" name="contrasena" <?= !$admin_a_editar ? 'required' : '' ?> class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="nivel_admin_form" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nivel:</label>
                    <!-- CORRECCIÓN AQUÍ: values 1 para Admin, 2 para Super Admin -->
                    <select id="nivel_admin_form" name="nivel_admin" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-techblue-500 focus:border-techblue-500 dark:bg-gray-700 dark:text-white">
                        <option value="1" <?= (isset($admin_a_editar['nivel_admin']) && $admin_a_editar['nivel_admin'] == 1) || (!isset($admin_a_editar) && !isset($admin_a_editar['nivel_admin'])) ? 'selected' : '' ?>>Administrador</option>
                        <option value="2" <?= (isset($admin_a_editar['nivel_admin']) && $admin_a_editar['nivel_admin'] == 2) ? 'selected' : '' ?>>Super Administrador</option>
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex items-end space-x-3">
                    <button type="submit" name="<?= $admin_a_editar ? 'editar_admin' : 'agregar_admin' ?>" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        <?= $admin_a_editar ? 'Guardar Cambios' : 'Agregar Administrador' ?>
                    </button>
                    <?php if ($admin_a_editar): ?>
                    <a href="gestion_admins.php<?= isset($_GET['page']) ? '?page='.(int)$_GET['page'] : '' ?>" class="inline-flex items-center justify-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200">
                        Cancelar Edición
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($total_admins > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Listado de Administradores (<?= $total_admins ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Correo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nivel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Creación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Último Acceso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($administradores as $admin_item): ?>
                        <!-- CORRECCIÓN AQUÍ: Usar $admin_actual['id'] para el ID del admin logueado -->
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 <?= ($admin_item['id_administrador'] == $admin_actual['id']) ? 'bg-techblue-50 dark:bg-techblue-900/50' : '' ?>">
                            <!-- CORRECCIÓN AQUÍ: $admin_item['nombre_usuario'] de la BD -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($admin_item['nombre_usuario']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($admin_item['correo']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- CORRECCIÓN AQUÍ: Lógica para nivel_admin de BD (2 es Super Admin) -->
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $admin_item['nivel_admin'] == 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' ?>">
                                    <?= $admin_item['nivel_admin'] == 2 ? 'Super Admin' : 'Admin' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars(date("d/m/Y H:i", strtotime($admin_item['fecha_creacion']))) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $admin_item['ultimo_acceso'] ? htmlspecialchars(date("d/m/Y H:i", strtotime($admin_item['ultimo_acceso']))) : 'Nunca' ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!-- CORRECCIÓN AQUÍ: Usar $admin_actual['id'] para el ID del admin logueado -->
                                <?php if ($admin_item['id_administrador'] != $admin_actual['id']): ?>
                                <a href="gestion_admins.php?editar_id=<?= $admin_item['id_administrador'] ?>&page=<?= $current_page ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Editar</a>
                                <a href="gestion_admins.php?eliminar_admin=1&id_admin=<?= $admin_item['id_administrador'] ?>&page=<?= $current_page ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar a este administrador? Esta acción no se puede deshacer.');" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Eliminar</a>
                                <?php else: ?>
                                <span class="text-gray-400 dark:text-gray-500">(Tú)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <nav class="flex justify-between items-center">
                    <div class="text-sm text-gray-700 dark:text-gray-400">
                        Mostrando <span class="font-medium"><?= count($administradores) ?></span> de <span class="font-medium"><?= $total_admins ?></span> resultados
                    </div>
                    <div class="flex space-x-1">
                        <?php $base_url_pagination_admins = 'gestion_admins.php?'; ?>
                        <?php if ($current_page > 1): ?>
                            <a href="<?= $base_url_pagination_admins ?>page=<?= $current_page - 1 ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">Anterior</a>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-600 dark:border-gray-500">Anterior</span>
                        <?php endif; ?>
                        <?php
                        $num_links_to_show_admins = 3;
                        $start_loop_admins = max(1, $current_page - floor($num_links_to_show_admins / 2));
                        $end_loop_admins = min($total_pages, $start_loop_admins + $num_links_to_show_admins - 1);
                        if ($end_loop_admins - $start_loop_admins + 1 < $num_links_to_show_admins && $start_loop_admins > 1) {
                            $start_loop_admins = max(1, $end_loop_admins - $num_links_to_show_admins + 1);
                        }
                        if ($start_loop_admins > 1): ?>
                            <a href="<?= $base_url_pagination_admins ?>page=1" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">1</a>
                            <?php if ($start_loop_admins > 2): ?><span class="px-3 py-1 text-sm font-medium text-gray-500 dark:text-gray-400">...</span><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($i = $start_loop_admins; $i <= $end_loop_admins; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="px-3 py-1 text-sm font-medium text-white bg-techblue-600 border border-techblue-600 rounded-md dark:bg-techblue-700 dark:border-techblue-700 z-10"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $base_url_pagination_admins ?>page=<?= $i ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($end_loop_admins < $total_pages): ?>
                            <?php if ($end_loop_admins < $total_pages - 1): ?><span class="px-3 py-1 text-sm font-medium text-gray-500 dark:text-gray-400">...</span><?php endif; ?>
                            <a href="<?= $base_url_pagination_admins ?>page=<?= $total_pages ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"><?= $total_pages ?></a>
                        <?php endif; ?>
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?= $base_url_pagination_admins ?>page=<?= $current_page + 1 ?>" class="px-3 py-1 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">Siguiente</a>
                        <?php else: ?>
                            <span class="px-3 py-1 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-600 dark:border-gray-500">Siguiente</span>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif ($mensaje_a_mostrar === null && $total_admins === 0): // Modificado para mostrar solo si no hay mensaje y no hay admins ?>
        <div class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-.699A5.002 5.002 0 0010 18a5.002 5.002 0 00-1.503-.699A6.97 6.97 0 007.002 16c0 .34.024.673.07 1H3a1 1 0 01-1-1V5a1 1 0 011-1h14a1 1 0 011 1v11a1 1 0 01-1 1h-4.07z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No hay administradores</h3>
                <p class="text-gray-600 dark:text-gray-400">Aún no se han agregado administradores al sistema.</p>
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