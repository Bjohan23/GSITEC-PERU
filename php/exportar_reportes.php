<?php
require_once("../config/config.php");
session_start();

// Verificar que el administrador esté logueado
if (!isset($_SESSION['sesion_admin'])) {
    header("Location: ./iniciar_sesion_admin.php");
    exit();
}

// Obtener parámetros
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'csv';
$periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 30;
$fecha_inicio = date('Y-m-d', strtotime("-$periodo days"));

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

if (mysqli_connect_errno()) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Obtener datos para exportar
function obtenerDatosExportacion($con, $fecha_inicio) {
    $datos = [];
    
    // Datos generales de ventas
    $query_ventas = "
        SELECT 
            h.id_historial,
            h.fecha_compra,
            u.nombre_usuario,
            u.correo,
            p.nombre_producto,
            c.nombre_categoria,
            c.icono_categoria,
            h.cantidad_comprada,
            p.precio_producto,
            (h.cantidad_comprada * p.precio_producto) as total_venta
        FROM historial_compras h
        JOIN usuario u ON h.id_usuario = u.id_usuario
        JOIN producto p ON h.id_producto = p.id_producto
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE h.fecha_compra >= '$fecha_inicio'
        ORDER BY h.fecha_compra DESC
    ";
    
    $result = mysqli_query($con, $query_ventas);
    $datos['ventas'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $datos['ventas'][] = $row;
    }
    
    // Resumen por categorías
    $query_categorias = "
        SELECT 
            c.nombre_categoria,
            c.icono_categoria,
            COUNT(h.id_historial) as total_ventas,
            SUM(h.cantidad_comprada) as productos_vendidos,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos_categoria,
            AVG(p.precio_producto * h.cantidad_comprada) as venta_promedio
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY c.id_categoria
        ORDER BY ingresos_categoria DESC
    ";
    
    $result = mysqli_query($con, $query_categorias);
    $datos['categorias'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $datos['categorias'][] = $row;
    }
    
    // Top productos
    $query_productos = "
        SELECT 
            p.nombre_producto,
            c.nombre_categoria,
            SUM(h.cantidad_comprada) as total_vendido,
            SUM(p.precio_producto * h.cantidad_comprada) as ingresos_producto,
            COUNT(DISTINCT h.id_usuario) as clientes_unicos
        FROM historial_compras h
        JOIN producto p ON h.id_producto = p.id_producto
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE h.fecha_compra >= '$fecha_inicio'
        GROUP BY p.id_producto
        ORDER BY total_vendido DESC
        LIMIT 20
    ";
    
    $result = mysqli_query($con, $query_productos);
    $datos['productos'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $datos['productos'][] = $row;
    }
    
    return $datos;
}

$datos = obtenerDatosExportacion($con, $fecha_inicio);
$fecha_reporte = date('Y-m-d_H-i-s');
$periodo_texto = $periodo . '_dias';

switch ($formato) {
    case 'csv':
        exportarCSV($datos, $fecha_reporte, $periodo_texto);
        break;
    case 'excel':
        exportarExcel($datos, $fecha_reporte, $periodo_texto);
        break;
    case 'pdf':
        exportarPDF($datos, $fecha_reporte, $periodo_texto, $periodo);
        break;
    default:
        exportarCSV($datos, $fecha_reporte, $periodo_texto);
}

mysqli_close($con);

function exportarCSV($datos, $fecha_reporte, $periodo_texto) {
    $filename = "reporte_ventas_{$periodo_texto}_{$fecha_reporte}.csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Hoja 1: Ventas detalladas
    fputcsv($output, ['=== REPORTE DE VENTAS DETALLADO ==='], ';');
    fputcsv($output, ['Generado el:', date('d/m/Y H:i:s')], ';');
    fputcsv($output, ['Período:', $periodo_texto], ';');
    fputcsv($output, [], ';');
    
    fputcsv($output, [
        'ID Venta',
        'Fecha',
        'Cliente',
        'Email Cliente',
        'Producto',
        'Categoría',
        'Cantidad',
        'Precio Unitario',
        'Total Venta'
    ], ';');
    
    foreach ($datos['ventas'] as $venta) {
        fputcsv($output, [
            $venta['id_historial'],
            date('d/m/Y H:i', strtotime($venta['fecha_compra'])),
            $venta['nombre_usuario'],
            $venta['correo'],
            $venta['nombre_producto'],
            ($venta['icono_categoria'] ?? '') . ' ' . ($venta['nombre_categoria'] ?? 'Sin categoría'),
            $venta['cantidad_comprada'],
            '$' . number_format($venta['precio_producto'], 2, '.', ','),
            '$' . number_format($venta['total_venta'], 2, '.', ',')
        ], ';');
    }
    
    // Hoja 2: Resumen por categorías
    fputcsv($output, [], ';');
    fputcsv($output, ['=== RESUMEN POR CATEGORÍAS ==='], ';');
    fputcsv($output, [
        'Categoría',
        'Total Ventas',
        'Productos Vendidos',
        'Ingresos Totales',
        'Venta Promedio'
    ], ';');
    
    foreach ($datos['categorias'] as $categoria) {
        fputcsv($output, [
            ($categoria['icono_categoria'] ?? '') . ' ' . $categoria['nombre_categoria'],
            $categoria['total_ventas'],
            $categoria['productos_vendidos'],
            '$' . number_format($categoria['ingresos_categoria'], 2, '.', ','),
            '$' . number_format($categoria['venta_promedio'], 2, '.', ',')
        ], ';');
    }
    
    // Hoja 3: Top productos
    fputcsv($output, [], ';');
    fputcsv($output, ['=== TOP 20 PRODUCTOS MÁS VENDIDOS ==='], ';');
    fputcsv($output, [
        'Producto',
        'Categoría',
        'Unidades Vendidas',
        'Ingresos Generados',
        'Clientes Únicos'
    ], ';');
    
    foreach ($datos['productos'] as $producto) {
        fputcsv($output, [
            $producto['nombre_producto'],
            $producto['nombre_categoria'] ?? 'Sin categoría',
            $producto['total_vendido'],
            '$' . number_format($producto['ingresos_producto'], 2, '.', ','),
            $producto['clientes_unicos']
        ], ';');
    }
    
    fclose($output);
    exit();
}

function exportarExcel($datos, $fecha_reporte, $periodo_texto) {
    // Para una implementación completa de Excel, necesitarías PhpSpreadsheet
    // Por ahora, exportamos como CSV con formato Excel
    $filename = "reporte_ventas_{$periodo_texto}_{$fecha_reporte}.xls";
    
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"';
    echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
    echo ' xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet><x:Name>Ventas Detalladas</x:Name>';
    echo '<x:WorksheetSource HRef="' . $filename . '"/></x:ExcelWorksheet>';
    echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '</head>';
    echo '<body>';
    
    // Información del reporte
    echo '<h2>REPORTE DE VENTAS GSITEC PERU</h2>';
    echo '<p><strong>Generado:</strong> ' . date('d/m/Y H:i:s') . '</p>';
    echo '<p><strong>Período:</strong> ' . str_replace('_', ' ', $periodo_texto) . '</p>';
    echo '<br>';
    
    // Tabla de ventas
    echo '<h3>Ventas Detalladas</h3>';
    echo '<table border="1">';
    echo '<tr style="background-color: #4F46E5; color: white;">';
    echo '<th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Email</th>';
    echo '<th>Producto</th><th>Categoría</th><th>Cantidad</th>';
    echo '<th>Precio Unit.</th><th>Total</th>';
    echo '</tr>';
    
    foreach ($datos['ventas'] as $venta) {
        echo '<tr>';
        echo '<td>' . $venta['id_historial'] . '</td>';
        echo '<td>' . date('d/m/Y H:i', strtotime($venta['fecha_compra'])) . '</td>';
        echo '<td>' . htmlspecialchars($venta['nombre_usuario']) . '</td>';
        echo '<td>' . htmlspecialchars($venta['correo']) . '</td>';
        echo '<td>' . htmlspecialchars($venta['nombre_producto']) . '</td>';
        echo '<td>' . htmlspecialchars(($venta['icono_categoria'] ?? '') . ' ' . ($venta['nombre_categoria'] ?? 'Sin categoría')) . '</td>';
        echo '<td>' . $venta['cantidad_comprada'] . '</td>';
        echo '<td>$' . number_format($venta['precio_producto'], 2, '.', ',') . '</td>';
        echo '<td style="background-color: #D1FAE5;">$' . number_format($venta['total_venta'], 2, '.', ',') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br><br>';
    
    // Resumen por categorías
    echo '<h3>Resumen por Categorías</h3>';
    echo '<table border="1">';
    echo '<tr style="background-color: #059669; color: white;">';
    echo '<th>Categoría</th><th>Ventas</th><th>Productos</th><th>Ingresos</th><th>Promedio</th>';
    echo '</tr>';
    
    foreach ($datos['categorias'] as $categoria) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars(($categoria['icono_categoria'] ?? '') . ' ' . $categoria['nombre_categoria']) . '</td>';
        echo '<td>' . $categoria['total_ventas'] . '</td>';
        echo '<td>' . $categoria['productos_vendidos'] . '</td>';
        echo '<td style="background-color: #D1FAE5;">$' . number_format($categoria['ingresos_categoria'], 2, '.', ',') . '</td>';
        echo '<td>$' . number_format($categoria['venta_promedio'], 2, '.', ',') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '</body></html>';
    exit();
}

function exportarPDF($datos, $fecha_reporte, $periodo_texto, $periodo) {
    // Crear un PDF simple con HTML/CSS
    $filename = "reporte_ventas_{$periodo_texto}_{$fecha_reporte}.pdf";
    
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    // Para un PDF real, necesitarías una librería como TCPDF o FPDF
    // Por ahora, creamos un HTML que simula un PDF
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Ventas</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .company-name { font-size: 24px; font-weight: bold; color: #2563eb; }
            .report-title { font-size: 18px; margin: 10px 0; }
            .report-info { background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            .section { margin-bottom: 30px; }
            .section-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #2563eb; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
            th { background-color: #2563eb; color: white; }
            .currency { text-align: right; }
            .summary-box { background-color: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            .summary-item { display: inline-block; margin-right: 30px; }
            .summary-label { font-weight: bold; color: #1e40af; }
            .summary-value { font-size: 18px; color: #059669; font-weight: bold; }
            @media print {
                body { margin: 0; }
                .header { page-break-after: avoid; }
                table { page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company-name">GSITEC PERU</div>
            <div class="report-title">Reporte de Ventas y Analytics</div>
        </div>
        
        <div class="report-info">
            <strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
            <strong>Período analizado:</strong> Últimos ' . $periodo . ' días<br>
            <strong>Generado por:</strong> ' . htmlspecialchars($_SESSION['sesion_admin']['nombre']) . '
        </div>';
    
    // Calcular totales
    $total_ventas = count($datos['ventas']);
    $total_ingresos = array_sum(array_column($datos['ventas'], 'total_venta'));
    $promedio_venta = $total_ventas > 0 ? $total_ingresos / $total_ventas : 0;
    
    $html .= '
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-label">Total de Ventas:</div>
                <div class="summary-value">' . number_format($total_ventas) . '</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Ingresos Totales:</div>
                <div class="summary-value">$' . number_format($total_ingresos, 2, '.', ',') . '</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Venta Promedio:</div>
                <div class="summary-value">$' . number_format($promedio_venta, 2, '.', ',') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">📊 Resumen por Categorías</div>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Ventas</th>
                        <th>Productos Vendidos</th>
                        <th>Ingresos</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($datos['categorias'] as $categoria) {
        $html .= '<tr>
            <td>' . htmlspecialchars(($categoria['icono_categoria'] ?? '') . ' ' . $categoria['nombre_categoria']) . '</td>
            <td>' . $categoria['total_ventas'] . '</td>
            <td>' . $categoria['productos_vendidos'] . '</td>
            <td class="currency">$' . number_format($categoria['ingresos_categoria'], 2, '.', ',') . '</td>
            <td class="currency">$' . number_format($categoria['venta_promedio'], 2, '.', ',') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></div>
        
        <div class="section">
            <div class="section-title">🏆 Top 10 Productos Más Vendidos</div>
            <table>
                <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Unidades Vendidas</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach (array_slice($datos['productos'], 0, 10) as $index => $producto) {
        $html .= '<tr>
            <td>' . ($index + 1) . '</td>
            <td>' . htmlspecialchars($producto['nombre_producto']) . '</td>
            <td>' . htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría') . '</td>
            <td>' . $producto['total_vendido'] . '</td>
            <td class="currency">$' . number_format($producto['ingresos_producto'], 2, '.', ',') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></div>
        
        <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
            <p>Este reporte fue generado automáticamente por el sistema GSITEC PERU</p>
            <p>© ' . date('Y') . ' GSITEC PERU - Todos los derechos reservados</p>
        </div>
    </body>
    </html>';
    
    echo $html;
    exit();
}
?>