<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

$admin = $_SESSION['sesion_admin'];

// Verificar que se haya enviado un ID de producto
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ./gestion_productos.php");
    exit();
}

$id_producto = (int)$_GET['id'];

// Obtener datos del producto
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
$producto = null;

if (!mysqli_connect_errno()) {
    $result = mysqli_query($con, "SELECT * FROM producto WHERE id_producto = $id_producto");
    if (mysqli_num_rows($result) > 0) {
        $producto = mysqli_fetch_array($result);
    } else {
        mysqli_close($con);
        header("Location: ./gestion_productos.php?mensaje=" . urlencode("Producto no encontrado"));
        exit();
    }
}

// Variables para manejo de errores y campos
$nombreErr = $descripcionErr = $cantidadErr = $precioErr = $fabricanteErr = $origenErr = $categoriaErr = $imagenErr = "";
$nombre = $producto['nombre_producto'];
$descripcion = $producto['descripcion_producto'];
$cantidad = $producto['cantidad_disponible'];
$precio = $producto['precio_producto'];
$fabricante = $producto['fabricante'];
$origen = $producto['origen'];
$categoria = $producto['categoria'];
$hay_errores = false;

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos
    if (empty($_POST["nombre_producto"])) {
        $nombreErr = "* Nombre del producto requerido";
        $hay_errores = true;
    } else {
        $nombre = test_input($_POST["nombre_producto"]);
    }
    
    if (empty($_POST["descripcion_producto"])) {
        $descripcionErr = "* Descripción requerida";
        $hay_errores = true;
    } else {
        $descripcion = test_input($_POST["descripcion_producto"]);
    }
    
    if (empty($_POST["cantidad_disponible"])) {
        $cantidadErr = "* Cantidad requerida";
        $hay_errores = true;
    } else {
        $cantidad = (int)$_POST["cantidad_disponible"];
        if ($cantidad < 0) {
            $cantidadErr = "* La cantidad no puede ser negativa";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["precio_producto"])) {
        $precioErr = "* Precio requerido";
        $hay_errores = true;
    } else {
        $precio = floatval($_POST["precio_producto"]);
        if ($precio <= 0) {
            $precioErr = "* El precio debe ser mayor que 0";
            $hay_errores = true;
        }
    }
    
    if (empty($_POST["fabricante"])) {
        $fabricanteErr = "* Fabricante requerido";
        $hay_errores = true;
    } else {
        $fabricante = test_input($_POST["fabricante"]);
    }
    
    if (empty($_POST["origen"])) {
        $origenErr = "* Origen requerido";
        $hay_errores = true;
    } else {
        $origen = test_input($_POST["origen"]);
    }
    
    if (empty($_POST["categoria"])) {
        $categoriaErr = "* Categoría requerida";
        $hay_errores = true;
    } else {
        $categoria = test_input($_POST["categoria"]);
    }
    
    // Validar imagen (solo si se subió una nueva)
    if (isset($_FILES["imagen_producto"]) && $_FILES["imagen_producto"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES["imagen_producto"]["type"];
        $file_size = $_FILES["imagen_producto"]["size"];
        
        if (!in_array($file_type, $allowed_types)) {
            $imagenErr = "* Solo se permiten imágenes JPG, PNG y GIF";
            $hay_errores = true;
        } elseif ($file_size > 5000000) { // 5MB
            $imagenErr = "* La imagen no puede superar los 5MB";
            $hay_errores = true;
        }
    }
    
    if (!$hay_errores) {
        if (!mysqli_connect_errno()) {
            // Actualizar producto
            $stmt = mysqli_prepare($con, "UPDATE producto SET nombre_producto = ?, descripcion_producto = ?, cantidad_disponible = ?, precio_producto = ?, fabricante = ?, origen = ?, categoria = ? WHERE id_producto = ?");
            mysqli_stmt_bind_param($stmt, "ssidsssi", $nombre, $descripcion, $cantidad, $precio, $fabricante, $origen, $categoria, $id_producto);
            
            if (mysqli_stmt_execute($stmt)) {
                // Si se subió una nueva imagen, moverla
                if (isset($_FILES["imagen_producto"]) && $_FILES["imagen_producto"]["error"] == 0) {
                    $ruta_imagen = $_FILES["imagen_producto"]["tmp_name"];
                    $ruta_destino = "../img/productos/$id_producto.png";
                    
                    // Eliminar imagen anterior si existe
                    if (file_exists($ruta_destino)) {
                        unlink($ruta_destino);
                    }
                    
                    move_uploaded_file($ruta_imagen, $ruta_destino);
                }
                
                mysqli_close($con);
                header("Location: ./gestion_productos.php?mensaje=" . urlencode("Producto actualizado exitosamente"));
                exit();
            } else {
                $nombreErr = "Error al actualizar el producto";
                $hay_errores = true;
            }
        } else {
            $nombreErr = "Error de conexión a la base de datos";
            $hay_errores = true;
        }
    }
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <?php include "head_html.php"; ?>
    <title>GSITEC PERU - Editar Producto</title>
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
                    <span class="text-cyan-400 font-semibold">✏️ Editar</span>
                    
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
                    <span class="text-cyan-400 font-semibold">✏️ Editar Producto</span>
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
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                ✏️ Editar Producto
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Modifica la información del producto: <span class="font-semibold text-techblue-600 dark:text-techblue-400"><?= htmlspecialchars($producto['nombre_producto']) ?></span>
            </p>
        </div>

        <!-- Product Preview -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center space-x-4">
                    <img 
                        class="h-20 w-20 rounded-lg object-cover" 
                        src="../img/productos/<?= $id_producto ?>.png" 
                        alt="<?= htmlspecialchars($producto['nombre_producto']) ?>"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjM0I4MkY2Ii8+PHRleHQgeD0iNDAiIHk9IjQ1IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='"
                    >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Producto ID: #<?= $id_producto ?>
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Última actualización: <?= date('d/m/Y H:i') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        Actualizar Información
                    </h2>
                </div>

                <div class="p-6">
                    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>?id=<?= $id_producto ?>" method="post" enctype="multipart/form-data" class="space-y-6">
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
                                    value="<?= htmlspecialchars($nombre) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                >
                                <?php if($nombreErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $nombreErr ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Descripción detallada
                                </label>
                                <textarea 
                                    id="descripcion_producto" 
                                    name="descripcion_producto" 
                                    rows="4"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 resize-none"
                                ><?= htmlspecialchars($descripcion) ?></textarea>
                                <?php if($descripcionErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $descripcionErr ?></p>
                                <?php endif; ?>
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
                                    value="<?= htmlspecialchars($cantidad) ?>"
                                    min="0"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                >
                                <?php if($cantidadErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $cantidadErr ?></p>
                                <?php endif; ?>
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
                                        value="<?= htmlspecialchars($precio) ?>"
                                        min="0.01"
                                        required
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                    >
                                </div>
                                <?php if($precioErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $precioErr ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Fabricante -->
                            <div>
                                <label for="fabricante" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Fabricante/Marca
                                </label>
                                <input 
                                    type="text" 
                                    id="fabricante" 
                                    name="fabricante" 
                                    value="<?= htmlspecialchars($fabricante) ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                >
                                <?php if($fabricanteErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $fabricanteErr ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Origen -->
                            <div>
                                <label for="origen" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    País de origen
                                </label>
                                <select 
                                    id="origen" 
                                    name="origen" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                >
                                    <option value="">Seleccionar país</option>
                                    <option value="China" <?= $origen === 'China' ? 'selected' : '' ?>>China</option>
                                    <option value="Estados Unidos" <?= $origen === 'Estados Unidos' ? 'selected' : '' ?>>Estados Unidos</option>
                                    <option value="Japón" <?= $origen === 'Japón' ? 'selected' : '' ?>>Japón</option>
                                    <option value="Corea del Sur" <?= $origen === 'Corea del Sur' ? 'selected' : '' ?>>Corea del Sur</option>
                                    <option value="Taiwán" <?= $origen === 'Taiwán' ? 'selected' : '' ?>>Taiwán</option>
                                    <option value="Alemania" <?= $origen === 'Alemania' ? 'selected' : '' ?>>Alemania</option>
                                    <option value="Otro" <?= $origen === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                                <?php if($origenErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $origenErr ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Categoría -->
                            <div class="md:col-span-2">
                                <label for="categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Categoría
                                </label>
                                <select 
                                    id="categoria" 
                                    name="categoria" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200"
                                >
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Monitores" <?= $categoria === 'Monitores' ? 'selected' : '' ?>>Monitores</option>
                                    <option value="Teclados" <?= $categoria === 'Teclados' ? 'selected' : '' ?>>Teclados</option>
                                    <option value="Ratones" <?= $categoria === 'Ratones' ? 'selected' : '' ?>>Ratones</option>
                                    <option value="Auriculares" <?= $categoria === 'Auriculares' ? 'selected' : '' ?>>Auriculares</option>
                                    <option value="Componentes PC" <?= $categoria === 'Componentes PC' ? 'selected' : '' ?>>Componentes PC</option>
                                    <option value="Laptops" <?= $categoria === 'Laptops' ? 'selected' : '' ?>>Laptops</option>
                                    <option value="Accesorios" <?= $categoria === 'Accesorios' ? 'selected' : '' ?>>Accesorios</option>
                                    <option value="Gaming" <?= $categoria === 'Gaming' ? 'selected' : '' ?>>Gaming</option>
                                    <option value="Oficina" <?= $categoria === 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                                </select>
                                <?php if($categoriaErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $categoriaErr ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Imagen del producto (opcional) -->
                            <div class="md:col-span-2">
                                <label for="imagen_producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cambiar imagen del producto (opcional)
                                </label>
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <div class="mt-4">
                                        <label for="imagen_producto" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                            <span id="fileLabel">Cambiar imagen</span>
                                        </label>
                                        <input type="file" id="imagen_producto" name="imagen_producto" accept="image/*" class="sr-only">
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        PNG, JPG, GIF hasta 5MB (deja vacío para mantener la actual)
                                    </p>
                                </div>
                                <?php if($imagenErr): ?>
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400"><?= $imagenErr ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                            <button 
                                type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Guardar Cambios
                            </button>
                            
                            <a 
                                href="./gestion_productos.php"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 block text-center"
                            >
                                ← Volver sin guardar
                            </a>
                        </div>
                    </form>
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

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') {
                document.documentElement.classList.add('dark');
            }
        });

        // File input preview
        document.getElementById('imagen_producto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = document.getElementById('fileLabel');
            
            if (file) {
                label.textContent = `Nuevo: ${file.name}`;
                label.parentElement.className = 'cursor-pointer bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200';
            } else {
                label.textContent = 'Cambiar imagen';
                label.parentElement.className = 'cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200';
            }
        });
    </script>
</body>

</html>
