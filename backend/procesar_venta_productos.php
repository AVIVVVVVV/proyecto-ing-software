<?php
session_start();
require 'config/conexion.php'; 

//Validar seguridad
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o no iniciada.']); 
    exit();
}

$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos || empty($datos['productos'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron productos.']); 
    exit();
}

try {
   
    $conexion->beginTransaction();

    $id_usuario = $_SESSION['id_usuario'];
    $total_venta = 0;

    //Crear el "Ticket" general en venta_producto
    $stmtVenta = $conexion->prepare("INSERT INTO venta_producto (id_usuario, fecha_hora, total) VALUES (?, NOW(), 0)");
    $stmtVenta->execute([$id_usuario]);
    $id_venta_producto = $conexion->lastInsertId();

    // se preparan todas las consultas que se usan en el ciclo
    
    // El "FOR UPDATE" bloquea la fila por milisegundos para que 2 taquilleros no vendan la misma bolsa de papas al mismo tiempo
    $stmtProducto = $conexion->prepare("SELECT nombre_producto, precio_venta, stock_actual FROM producto WHERE id_producto = ? FOR UPDATE"); 
    
    $stmtDetalle = $conexion->prepare("INSERT INTO detalle_venta_producto (id_venta_producto, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    $stmtDescontarStock = $conexion->prepare("UPDATE producto SET stock_actual = stock_actual - ? WHERE id_producto = ?");
    
    // En proveedor ponemos NULL porque es una salida, no una entrada de un proveedor
    $stmtMovimiento = $conexion->prepare("INSERT INTO movimiento_inventario (id_producto, id_usuario, id_proveedor, tipo_movimiento, cantidad, fecha_hora, concepto) VALUES (?, ?, NULL, 'Salida', ?, NOW(), 'Venta en Tienda')");

    // Procesar cada producto del carrito
    foreach ($datos['productos'] as $item) {
        $id_producto = $item['id_producto'];
        $cantidad = (int)$item['cantidad'];

        if ($cantidad <= 0) continue;

        // Revisar precio actual y stock en tiempo real
        $stmtProducto->execute([$id_producto]);
        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("El producto ID $id_producto ya no existe.");
        }

        // Validación extra de seguridad en el backend
        if ($producto['stock_actual'] < $cantidad) {
            throw new Exception("Stock insuficiente para el producto: " . $producto['nombre_producto'] . " (Solo quedan " . $producto['stock_actual'] . ").");
        }

        // Cálculos
        $precio_real = $producto['precio_venta'];
        $subtotal = $precio_real * $cantidad;
        $total_venta += $subtotal;

        // A) Insertar el detalle de la venta
        $stmtDetalle->execute([$id_venta_producto, $id_producto, $cantidad, $precio_real, $subtotal]);

        // B) Descontar el stock físico de la base de datos
        $stmtDescontarStock->execute([$cantidad, $id_producto]);

        // C) Registrar la trazabilidad en movimientos de inventario
        $stmtMovimiento->execute([$id_producto, $id_usuario, $cantidad]);
    }

    if ($total_venta == 0) {
        throw new Exception("El monto total no puede ser cero.");
    }

    // Actualizar la venta principal con el Total Real cobrado
    $stmtUpdateTotal = $conexion->prepare("UPDATE venta_producto SET total = ? WHERE id_venta_producto = ?");
    $stmtUpdateTotal->execute([$total_venta, $id_venta_producto]);

    // Todo salió perfecto, guardamos los cambios de forma permanente
    $conexion->commit();

    echo json_encode([
        'status' => 'success', 
        'total' => number_format((float)$total_venta, 2, '.', '')
    ]);

} catch(Exception $e) {
    // Si hubo cualquier error (falta de stock, error de sintaxis), deshacemos todo
    $conexion->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>