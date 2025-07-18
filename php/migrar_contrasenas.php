<?php
/**
 * Script de migración para convertir contraseñas en texto plano a hash
 * IMPORTANTE: Ejecutar este script una sola vez después de implementar el sistema de hash
 */

require_once("../config/config.php");

// Función para verificar si una contraseña ya está hasheada
function esHasheada($password) {
    // Las contraseñas hasheadas con password_hash() tienen una longitud de 60 caracteres
    // y comienzan con $2y$ (bcrypt)
    return strlen($password) === 60 && strpos($password, '$2y$') === 0;
}

echo "=== MIGRACIÓN DE CONTRASEÑAS ===\n\n";

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

if (mysqli_connect_errno()) {
    echo "Error de conexión: " . mysqli_connect_error() . "\n";
    exit();
}

// Migrar contraseñas de usuarios
echo "1. Migrando contraseñas de usuarios...\n";

$result_usuarios = mysqli_query($con, "SELECT id_usuario, nombre_usuario, contrasena FROM usuario");
$usuarios_migrados = 0;
$usuarios_ya_hasheados = 0;

while ($row = mysqli_fetch_array($result_usuarios)) {
    $id = $row['id_usuario'];
    $nombre = $row['nombre_usuario'];
    $contrasena = $row['contrasena'];
    
    if (!esHasheada($contrasena)) {
        // Hashear la contraseña
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // Actualizar en la base de datos
        $stmt = mysqli_prepare($con, "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($stmt, "si", $hash_contrasena, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "  ✓ Usuario migrado: $nombre\n";
            $usuarios_migrados++;
        } else {
            echo "  ✗ Error al migrar usuario: $nombre\n";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "  → Usuario ya hasheado: $nombre\n";
        $usuarios_ya_hasheados++;
    }
}

echo "   Usuarios migrados: $usuarios_migrados\n";
echo "   Usuarios ya hasheados: $usuarios_ya_hasheados\n\n";

// Migrar contraseñas de administradores
echo "2. Migrando contraseñas de administradores...\n";

$result_admins = mysqli_query($con, "SELECT id_administrador, nombre_usuario, contrasena FROM administradores");
$admins_migrados = 0;
$admins_ya_hasheados = 0;

while ($row = mysqli_fetch_array($result_admins)) {
    $id = $row['id_administrador'];
    $nombre = $row['nombre_usuario'];
    $contrasena = $row['contrasena'];
    
    if (!esHasheada($contrasena)) {
        // Hashear la contraseña
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // Actualizar en la base de datos
        $stmt = mysqli_prepare($con, "UPDATE administradores SET contrasena = ? WHERE id_administrador = ?");
        mysqli_stmt_bind_param($stmt, "si", $hash_contrasena, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "  ✓ Administrador migrado: $nombre\n";
            $admins_migrados++;
        } else {
            echo "  ✗ Error al migrar administrador: $nombre\n";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "  → Administrador ya hasheado: $nombre\n";
        $admins_ya_hasheados++;
    }
}

echo "   Administradores migrados: $admins_migrados\n";
echo "   Administradores ya hasheados: $admins_ya_hasheados\n\n";

mysqli_close($con);

echo "=== MIGRACIÓN COMPLETADA ===\n";
echo "IMPORTANTE: Elimina este archivo después de ejecutarlo por seguridad.\n";
?>