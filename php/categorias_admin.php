<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];
$mensaje = null;
$tipo_mensaje = null;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
    if (!mysqli_connect_errno()) {
        if (isset($_POST['accion'])) {
            switch ($_POST['accion']) {
                case 'crear':
                    $nombre = mysqli_real_escape_string($con, trim($_POST['nombre_categoria']));
                    $descripcion = mysqli_real_escape_string($con, trim($_POST['descripcion_categoria']));
                    $icono = mysqli_real_escape_string($con, $_POST['icono_categoria']);
                    $color = mysqli_real_escape_string($con, $_POST['color_categoria']);
                    $orden = (int)$_POST['orden_visualizacion'];
                    
                    if (!empty($nombre)) {
                        $stmt = mysqli_prepare($con, "INSERT INTO categorias (nombre_categoria, descripcion_categoria, icono_categoria, color_categoria, orden_visualizacion) VALUES (?, ?, ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $descripcion, $icono, $color, $orden);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $mensaje = "Categoría '$nombre' creada exitosamente";
                            $tipo_mensaje = "success";
                        } else {
                            $mensaje = "Error al crear la categoría: " . mysqli_error($con);
                            $tipo_mensaje = "error";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    break;
                    
                case 'editar':
                    $id = (int)$_POST['id_categoria'];
                    $nombre = mysqli_real_escape_string($con, trim($_POST['nombre_categoria']));
                    $descripcion = mysqli_real_escape_string($con, trim($_POST['descripcion_categoria']));
                    $icono = mysqli_real_escape_string($con, $_POST['icono_categoria']);
                    $color = mysqli_real_escape_string($con, $_POST['color_categoria']);
                    $orden = (int)$_POST['orden_visualizacion'];
                    $activa = isset($_POST['activa']) ? 1 : 0;
                    
                    if (!empty($nombre) && $id > 0) {
                        $stmt = mysqli_prepare($con, "UPDATE categorias SET nombre_categoria=?, descripcion_categoria=?, icono_categoria=?, color_categoria=?, orden_visualizacion=?, activa=? WHERE id_categoria=?");
                        mysqli_stmt_bind_param($stmt, "ssssiii", $nombre, $descripcion, $icono, $color, $orden, $activa, $id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $mensaje = "Categoría actualizada exitosamente";
                            $tipo_mensaje = "success";
                        } else {
                            $mensaje = "Error al actualizar la categoría: " . mysqli_error($con);
                            $tipo_mensaje = "error";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    break;
                    
                case 'toggle_estado':
                    $id = (int)$_POST['id_categoria'];
                    if ($id > 0) {
                        $stmt = mysqli_prepare($con, "UPDATE categorias SET activa = !activa WHERE id_categoria = ?");
                        mysqli_stmt_bind_param($stmt, "i", $id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $mensaje = "Estado de la categoría actualizado";
                            $tipo_mensaje = "success";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    break;
            }
        }
        mysqli_close($con);
    }
}

// Procesar eliminación
if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id_categoria = (int)$_GET['id'];
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
    
    if (!mysqli_connect_errno()) {
        // Verificar si hay productos asociados
        $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM producto WHERE id_categoria = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_categoria);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_fetch_array($result)['count'];
        mysqli_stmt_close($stmt);
        
        if ($count > 0) {
            $mensaje = "No se puede eliminar la categoría porque tiene $count producto(s) asociado(s). Desactívala en su lugar.";
            $tipo_mensaje = "error";
        } else {
            $stmt = mysqli_prepare($con, "DELETE FROM categorias WHERE id_categoria = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_categoria);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Categoría eliminada exitosamente";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al eliminar la categoría";
                $tipo_mensaje = "error";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($con);
    }
}

// Obtener todas las categorías
$categorias = [];
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

if (!mysqli_connect_errno()) {
    $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria) as total_productos 
              FROM categorias c 
              ORDER BY c.orden_visualizacion, c.nombre_categoria";
    $result = mysqli_query($con, $query);
    
    while ($row = mysqli_fetch_array($result)) {
        $categorias[] = $row;
    }
    mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Gestión de Categorías</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-techblue-600 dark:bg-techblue-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-3" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <a href="./panel_admin.php" class="text-white text-xl font-bold">GSITEC ADMIN</a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <span class="text-cyan-400 font-semibold">🏷️ Categorías</span>
                    
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
                            👑 Admin: <span class="font-semibold text-cyan-400"><?= htmlspecialchars($admin['nombre']) ?></span>
                        </span>
                        <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <a href="./gestion_productos.php" class="text-white hover:text-cyan-400 transition-colors duration-200">📦 Productos</a>
                    <span class="text-cyan-400 font-semibold">🏷️ Categorías</span>
                    <span class="text-cyan-400">👑 <?= htmlspecialchars($admin['nombre']) ?></span>
                    <a href="./cerrar_sesion_admin.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 text-center">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    🏷️ Gestión de Categorías
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Organiza y administra las categorías de productos de la tienda
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button onclick="abrirModalCrear()" 
                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Nueva Categoría
                </button>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($mensaje): ?>
        <div class="mb-6 p-4 rounded-lg <?= $tipo_mensaje == 'success' ? 'bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700' : 'bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700' ?>">
            <div class="flex items-center">
                <svg class="w-5 h-5 <?= $tipo_mensaje == 'success' ? 'text-green-500' : 'text-red-500' ?> mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <?php if ($tipo_mensaje == 'success'): ?>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    <?php else: ?>
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    <?php endif; ?>
                </svg>
                <span class="<?= $tipo_mensaje == 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' ?> font-medium">
                    <?= htmlspecialchars($mensaje) ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categories Grid -->
        <?php if (!empty($categorias)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    🏷️ Categorías Actuales (<?= count($categorias) ?>)
                </h2>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Productos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Orden</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($categorias as $categoria): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-lg flex items-center justify-center text-2xl" 
                                         style="background-color: <?= htmlspecialchars($categoria['color_categoria']) ?>20; color: <?= htmlspecialchars($categoria['color_categoria']) ?>;">
                                        <?= htmlspecialchars($categoria['icono_categoria']) ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                            <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($categoria['descripcion_categoria']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <?= $categoria['total_productos'] ?> productos
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    #<?= $categoria['orden_visualizacion'] ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($categoria['activa']): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Activa
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Inactiva
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
                                <div class="flex flex-col space-y-2">
                                    <button onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($categoria)) ?>)" 
                                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                        Editar
                                    </button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="accion" value="toggle_estado">
                                        <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                                        <button type="submit" 
                                                class="inline-flex items-center bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-xs w-full">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                            </svg>
                                            <?= $categoria['activa'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <?php if($categoria['total_productos'] == 0): ?>
                                    <button onclick="confirmarEliminacion(<?= $categoria['id_categoria'] ?>, '<?= htmlspecialchars($categoria['nombre_categoria'], ENT_QUOTES) ?>')"
                                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Eliminar
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden space-y-4 p-4">
                <?php foreach ($categorias as $categoria): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 h-16 w-16 rounded-lg flex items-center justify-center text-3xl" 
                             style="background-color: <?= htmlspecialchars($categoria['color_categoria']) ?>20; color: <?= htmlspecialchars($categoria['color_categoria']) ?>;">
                            <?= htmlspecialchars($categoria['icono_categoria']) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white capitalize mb-1">
                                <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <?= htmlspecialchars($categoria['descripcion_categoria']) ?>
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <div>
                                    <span class="font-medium">Productos:</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold"><?= $categoria['total_productos'] ?></span>
                                </div>
                                <div>
                                    <span class="font-medium">Orden:</span>
                                    #<?= $categoria['orden_visualizacion'] ?>
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium">Estado:</span>
                                    <?php if($categoria['activa']): ?>
                                        <span class="text-green-600 dark:text-green-400 font-semibold">Activa</span>
                                    <?php else: ?>
                                        <span class="text-red-600 dark:text-red-400 font-semibold">Inactiva</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($categoria)) ?>)"
                                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                    Editar
                                </button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="accion" value="toggle_estado">
                                    <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                                    <button type="submit"
                                            class="inline-flex items-center bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-sm">
                                        <?= $categoria['activa'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>
                                <?php if($categoria['total_productos'] == 0): ?>
                                <button onclick="confirmarEliminacion(<?= $categoria['id_categoria'] ?>, '<?= htmlspecialchars($categoria['nombre_categoria'], ENT_QUOTES) ?>')"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-sm">
                                    Eliminar
                                </button>
                                <?php endif; ?>
                            </div>
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
                        <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    No hay categorías
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Comienza creando categorías para organizar tus productos
                </p>
                <button onclick="abrirModalCrear()" 
                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Crear primera categoría
                </button>
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

    <!-- Modal Crear/Editar Categoría -->
    <div id="modalCategoria" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900 dark:text-white text-center mb-6">
                    Crear Nueva Categoría
                </h3>
                <form id="formCategoria" method="POST">
                    <input type="hidden" id="accion" name="accion" value="crear">
                    <input type="hidden" id="id_categoria" name="id_categoria" value="">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nombre_categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre de la Categoría
                            </label>
                            <input type="text" id="nombre_categoria" name="nombre_categoria" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div>
                            <label for="descripcion_categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Descripción
                            </label>
                            <textarea id="descripcion_categoria" name="descripcion_categoria" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="icono_categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Icono (Emoji)
                                </label>
                                <input type="text" id="icono_categoria" name="icono_categoria" value="📦" maxlength="2"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-center text-xl">
                            </div>
                            
                            <div>
                                <label for="color_categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Color
                                </label>
                                <input type="color" id="color_categoria" name="color_categoria" value="#3B82F6"
                                       class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="orden_visualizacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Orden de Visualización
                            </label>
                            <input type="number" id="orden_visualizacion" name="orden_visualizacion" value="0" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        
                        <div id="estadoDiv" class="hidden">
                            <label class="flex items-center">
                                <input type="checkbox" id="activa" name="activa" class="mr-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Categoría activa</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-center space-x-4 px-4 py-3 mt-6">
                        <button type="button" onclick="cerrarModal()" 
                                class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-lg shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-300">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Eliminar Categoría</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="confirmMessage">
                        ¿Estás seguro de que quieres eliminar esta categoría?
                    </p>
                </div>
                <div class="flex justify-center space-x-4 px-4 py-3">
                    <button onclick="cerrarModalConfirm()" 
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-lg shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <a id="confirmBtn" href="#" 
                       class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark') ? 'true' : 'false');
        }

        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }

        function abrirModalCrear() {
            document.getElementById('modalTitle').textContent = 'Crear Nueva Categoría';
            document.getElementById('accion').value = 'crear';
            document.getElementById('formCategoria').reset();
            document.getElementById('estadoDiv').classList.add('hidden');
            document.getElementById('modalCategoria').classList.remove('hidden');
        }

        function abrirModalEditar(categoria) {
            document.getElementById('modalTitle').textContent = 'Editar Categoría';
            document.getElementById('accion').value = 'editar';
            document.getElementById('id_categoria').value = categoria.id_categoria;
            document.getElementById('nombre_categoria').value = categoria.nombre_categoria;
            document.getElementById('descripcion_categoria').value = categoria.descripcion_categoria || '';
            document.getElementById('icono_categoria').value = categoria.icono_categoria;
            document.getElementById('color_categoria').value = categoria.color_categoria;
            document.getElementById('orden_visualizacion').value = categoria.orden_visualizacion;
            document.getElementById('activa').checked = categoria.activa == 1;
            document.getElementById('estadoDiv').classList.remove('hidden');
            document.getElementById('modalCategoria').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modalCategoria').classList.add('hidden');
        }

        function confirmarEliminacion(id, nombre) {
            document.getElementById('confirmMessage').innerHTML = 
                `¿Estás seguro de que quieres eliminar la categoría "<strong>${nombre}</strong>"?<br><br>
                <small class="text-red-600">Esta acción no se puede deshacer.</small>`;
            document.getElementById('confirmBtn').href = `./categorias_admin.php?eliminar=1&id=${id}`;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function cerrarModalConfirm() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
            
            // Cerrar modales al hacer clic fuera
            document.getElementById('modalCategoria').addEventListener('click', function(e) {
                if (e.target === this) cerrarModal();
            });
            
            document.getElementById('confirmModal').addEventListener('click', function(e) {
                if (e.target === this) cerrarModalConfirm();
            });
        });
    </script>
</body>

</html>