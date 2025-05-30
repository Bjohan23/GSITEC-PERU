<?php
    require_once("../config/config.php");
    session_start();

    // Verificar que el administrador esté logueado
    if (!isset($_SESSION['sesion_admin'])) {
        header("Location: ./iniciar_sesion_admin.php");
        exit();
    }

    $admin = $_SESSION['sesion_admin'];
    $mensaje = null; // Inicializar mensaje para evitar नोटिस si no se establece

    // Procesar eliminación si se solicita
    if (isset($_GET['eliminar']) && isset($_GET['id'])) {
        $id_producto_eliminar = (int)$_GET['id'];
        $pagina_actual_para_redirect = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Para volver a la misma página
        
        $con_eliminar = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if ($con_eliminar) {
            // Verificar si el producto existe en carritos o historial
            $stmt_carrito = mysqli_prepare($con_eliminar, "SELECT COUNT(*) as count FROM carrito WHERE id_producto = ?");
            mysqli_stmt_bind_param($stmt_carrito, "i", $id_producto_eliminar);
            mysqli_stmt_execute($stmt_carrito);
            $result_carrito = mysqli_stmt_get_result($stmt_carrito);
            $count_carrito = mysqli_fetch_array($result_carrito)['count'];
            mysqli_stmt_close($stmt_carrito);
            
            $stmt_historial = mysqli_prepare($con_eliminar, "SELECT COUNT(*) as count FROM historial_compras WHERE id_producto = ?");
            mysqli_stmt_bind_param($stmt_historial, "i", $id_producto_eliminar);
            mysqli_stmt_execute($stmt_historial);
            $result_historial = mysqli_stmt_get_result($stmt_historial);
            $count_historial = mysqli_fetch_array($result_historial)['count'];
            mysqli_stmt_close($stmt_historial);
            
            if ($count_carrito > 0 || $count_historial > 0) {
                // No eliminar, solo desactivar (cambiar cantidad a 0)
                $stmt_update = mysqli_prepare($con_eliminar, "UPDATE producto SET cantidad_disponible = 0 WHERE id_producto = ?");
                mysqli_stmt_bind_param($stmt_update, "i", $id_producto_eliminar);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
                $mensaje = "Producto desactivado exitosamente (hay registros en carritos/historial).";
            } else {
                // Eliminar completamente
                $stmt_delete = mysqli_prepare($con_eliminar, "DELETE FROM producto WHERE id_producto = ?");
                mysqli_stmt_bind_param($stmt_delete, "i", $id_producto_eliminar);
                mysqli_stmt_execute($stmt_delete);
                
                // Intentar eliminar la imagen
                $imagen_path = "../img/productos/" . $id_producto_eliminar . ".png"; // Concatenación segura
                if (file_exists($imagen_path)) {
                    @unlink($imagen_path); // Usar @ para suprimir errores si el archivo no se puede eliminar por permisos, etc.
                }
                mysqli_stmt_close($stmt_delete);
                $mensaje = "Producto eliminado completamente.";
            }
            
            mysqli_close($con_eliminar);

        } else {
            $mensaje = "Error de conexión al intentar eliminar el producto: " . mysqli_connect_error();
        }

        // Construir URL de redirección
        $redirect_url = "./gestion_productos.php";
        $query_params = [];
        if ($pagina_actual_para_redirect > 1) { // Solo añadir 'page' si no es la página 1 (para URLs más limpias)
            $query_params['page'] = $pagina_actual_para_redirect;
        }
        if (isset($mensaje)) {
            $query_params['mensaje'] = urlencode($mensaje);
        }

        if (!empty($query_params)) {
            $redirect_url .= "?" . http_build_query($query_params);
        }
        header("Location: " . $redirect_url);
        exit();
    }

    // --- Lógica de Paginación ---
    $items_per_page = 10; // Número de productos por página
    $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($current_page < 1) {
        $current_page = 1;
    }

    $total_productos = 0;
    $total_pages = 1; // Valor por defecto

    $con_temp_count = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

    if ($con_temp_count) { // Verifica si la conexión fue exitosa
        $total_result_query = mysqli_query($con_temp_count, "SELECT COUNT(*) as total FROM producto");
        if ($total_result_query) {
            $total_row = mysqli_fetch_assoc($total_result_query);
            $total_productos = (int)$total_row['total']; // Asegurar que es un entero
            mysqli_free_result($total_result_query); // Liberar el resultado
        } else {
            // Opcional: Registrar el error si la consulta COUNT falla
            error_log("Error en la consulta COUNT para paginación: " . mysqli_error($con_temp_count));
            // Si la consulta falla, $total_productos permanece en 0.
        }
        mysqli_close($con_temp_count);
    } else {
        // La conexión $con_temp_count falló. Registrar el error de conexión específico.
        error_log("Error al conectar a la BD para contar productos (paginación): " . mysqli_connect_error());
        // $total_productos permanece en 0, $total_pages permanecerá en 1.
        // Esto significa que si la conexión falla aquí, la paginación podría no mostrarse correctamente.
        // Podrías mostrar un mensaje de error al usuario si esto es crítico.
    }

    // Calcular el total de páginas
    if ($total_productos > 0 && $items_per_page > 0) { // Asegurar que items_per_page no sea 0
        $total_pages = ceil($total_productos / $items_per_page);
    } else {
        $total_pages = 1; // Si no hay productos o items_per_page es 0, solo hay una "página"
    }

    // Asegurarse de que la página actual no sea mayor que el total de páginas
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }
    // Si $total_pages es 0 (lo que no debería pasar con la lógica anterior, pero por si acaso)
    // o si no hay productos, $current_page podría necesitar ser 1.
    if ($total_pages == 0) $total_pages = 1; // Evitar $total_pages = 0
    if ($current_page > $total_pages) $current_page = $total_pages;


    $offset = ($current_page - 1) * $items_per_page;
    // --- FIN Lógica de Paginación ---

    // Si hay un mensaje de $_GET (por ejemplo, de la redirección de eliminación), usarlo.
    if (isset($_GET['mensaje']) && !isset($mensaje)) { // Solo si $mensaje no fue establecido por el proceso de eliminación en ESTA carga de página
        $mensaje = htmlspecialchars($_GET['mensaje']);
    }

?>

<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Gestión de Productos</title>
    <link rel="shortcut icon" href="../img/logo.jpg">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
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
                    <span class="text-cyan-400 font-semibold">📦 Productos</span>
                    
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

            <div id="mobileMenu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="./panel_admin.php" class="text-white hover:text-cyan-400 transition-colors duration-200">🏠 Dashboard</a>
                    <span class="text-cyan-400 font-semibold">📦 Productos</span>
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    📦 Gestión de Productos
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Administra el inventario y catálogo de productos
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="./agregar_producto.php" 
                   class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Agregar Producto
                </a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-800 dark:text-green-200 font-medium">
                    ✅ <?= htmlspecialchars($_GET['mensaje']) ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Products Management -->
        <?php
        $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()):
            echo '<div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4 mb-6">';
            echo '<p class="text-red-800 dark:text-red-200">Error de conexión: ' . mysqli_connect_error() . '</p>';
            echo '</div>';
        else:
            // MODIFICADO: Consulta SQL con LIMIT y OFFSET
            $sql_productos = "SELECT * FROM producto ORDER BY id_producto DESC LIMIT $items_per_page OFFSET $offset";
            $result = mysqli_query($con, $sql_productos);
            // $n_productos ahora se refiere al total de productos, no solo los de la página actual
            // Lo hemos llamado $total_productos antes.
            
            if($total_productos > 0): // MODIFICADO: Usar $total_productos para la condición
        ?>
        <!-- Products Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-techblue-600 to-cyan-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">
                    📦 Inventario de Productos (<?= $total_productos ?>) <!-- MODIFICADO: Mostrar total -->
                </h2>
            </div>
            
            <!-- Desktop Table (el bucle while mostrará solo los productos de la página actual) -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fabricante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php while ($row = mysqli_fetch_array($result)): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        <img 
                                            class="h-16 w-16 rounded-lg object-cover" 
                                            src="../img/productos/<?= $row['id_producto'] ?>.png" 
                                            alt="<?= htmlspecialchars($row['nombre_producto']) ?>"
                                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iMzIiIHk9IjM3IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTAiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                                        >
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($row['nombre_producto']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: #<?= $row['id_producto'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($row['cantidad_disponible'] > 10): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <?= $row['cantidad_disponible'] ?> unidades
                                    </span>
                                <?php elseif($row['cantidad_disponible'] > 0): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <?= $row['cantidad_disponible'] ?> unidades
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Agotado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    $<?= number_format(floatval($row['precio_producto']), 2, '.', ',') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($row['fabricante']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-techblue-100 text-techblue-800 dark:bg-techblue-900 dark:text-techblue-200">
                                    <?= htmlspecialchars($row['categoria']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-2">
                                <div class="flex flex-col space-y-2">
                                    <a href="./editar_producto.php?id=<?= $row['id_producto'] ?>&page=<?= $current_page ?>" 
                                       class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                        Editar
                                    </a>
                                    <button onclick="confirmarEliminacion(<?= $row['id_producto'] ?>, '<?= htmlspecialchars($row['nombre_producto'], ENT_QUOTES) ?>', <?= $current_page ?>)"
                                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (el bucle while mostrará solo los productos de la página actual) -->
            <div class="lg:hidden space-y-4 p-4">
                <?php
                mysqli_data_seek($result, 0); // Reiniciar el puntero del resultado para el bucle de tarjetas móviles
                while ($row = mysqli_fetch_array($result)): 
                ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <img 
                            class="h-20 w-20 rounded-lg object-cover flex-shrink-0" 
                            src="../img/productos/<?= $row['id_producto'] ?>.png" 
                            alt="<?= htmlspecialchars($row['nombre_producto']) ?>"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iNDAiIHk9IjQ1IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                        >
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                <?= htmlspecialchars($row['nombre_producto']) ?>
                            </h3>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <div>
                                    <span class="font-medium">Stock:</span>
                                    <?php if($row['cantidad_disponible'] > 10): ?><span class="text-green-600 dark:text-green-400 font-semibold"><?= $row['cantidad_disponible'] ?></span>
                                    <?php elseif($row['cantidad_disponible'] > 0): ?><span class="text-yellow-600 dark:text-yellow-400 font-semibold"><?= $row['cantidad_disponible'] ?></span>
                                    <?php else: ?><span class="text-red-600 dark:text-red-400 font-semibold">Agotado</span><?php endif; ?>
                                </div>
                                <div><span class="font-medium">Precio:</span><span class="text-green-600 dark:text-green-400 font-bold">$<?= number_format(floatval($row['precio_producto']), 2, '.', ',') ?></span></div>
                                <div><span class="font-medium">Fabricante:</span><br><?= htmlspecialchars($row['fabricante']) ?></div>
                                <div><span class="font-medium">Categoría:</span><br><?= htmlspecialchars($row['categoria']) ?></div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="./editar_producto.php?id=<?= $row['id_producto'] ?>&page=<?= $current_page ?>"
                                   class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                    Editar
                                </a>
                                <button onclick="confirmarEliminacion(<?= $row['id_producto'] ?>, '<?= htmlspecialchars($row['nombre_producto'], ENT_QUOTES) ?>', <?= $current_page ?>)"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition-colors duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- NUEVO: Enlaces de Paginación -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center items-center space-x-1">
            <!-- Botón Anterior -->
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                    Anterior
                </a>
            <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-700 dark:border-gray-600">
                    Anterior
                </span>
            <?php endif; ?>

            <!-- Números de Página -->
            <?php
            $num_links_to_show = 5; // Cantidad de enlaces de página a mostrar alrededor de la actual
            $start_page = max(1, $current_page - floor($num_links_to_show / 2));
            $end_page = min($total_pages, $start_page + $num_links_to_show - 1);
            
            // Ajustar si $end_page está cerca del final y no muestra suficientes enlaces
            if ($end_page - $start_page + 1 < $num_links_to_show && $start_page > 1) {
                $start_page = max(1, $end_page - $num_links_to_show + 1);
            }

            if ($start_page > 1): ?>
                <a href="?page=1" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <span class="px-4 py-2 text-sm font-medium text-white bg-techblue-600 border border-techblue-600 rounded-md dark:bg-techblue-700 dark:border-techblue-700 z-10">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400">...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_pages ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700"><?= $total_pages ?></a>
            <?php endif; ?>

            <!-- Botón Siguiente -->
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                    Siguiente
                </a>
            <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed dark:text-gray-500 dark:bg-gray-700 dark:border-gray-600">
                    Siguiente
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <!-- FIN Enlaces de Paginación -->

        <?php else: ?>
        <!-- Empty State (sin cambios) -->
        <div class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No hay productos</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">Comienza agregando productos al catálogo de la tienda</p>
                <a href="./agregar_producto.php" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                    Agregar primer producto
                </a>
            </div>
        </div>
        <?php endif; mysqli_close($con); endif; ?>

        <!-- Back Button (sin cambios) -->
        <div class="mt-8 text-center">
            <a href="./panel_admin.php" class="inline-flex items-center text-techblue-600 hover:text-techblue-500 dark:text-techblue-400 dark:hover:text-techblue-300 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                Volver al panel
            </a>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Eliminar Producto</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="confirmMessage">
                        ¿Estás seguro de que quieres eliminar este producto?
                    </p>
                </div>
                <div class="flex justify-center space-x-4 px-4 py-3">
                    <button id="cancelBtn" onclick="cerrarModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-lg shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancelar</button>
                    <a id="confirmBtn" href="#" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            if (html.classList.contains('dark')) localStorage.setItem('darkMode', 'true');
            else localStorage.setItem('darkMode', 'false');
        }

        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }

        // MODIFICADO: La función ahora acepta `currentPage`
        function confirmarEliminacion(id, nombre, currentPage) {
            document.getElementById('confirmMessage').innerHTML = 
                `¿Estás seguro de que quieres eliminar el producto "<strong>${nombre}</strong>"?<br><br>
                <small class="text-red-600">Esta acción no se puede deshacer.</small>`;
            // MODIFICADO: Añadir el parámetro `page` a la URL de eliminación
            document.getElementById('confirmBtn').href = `./gestion_productos.php?eliminar=1&id=${id}&page=${currentPage}`;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</body>
</html>