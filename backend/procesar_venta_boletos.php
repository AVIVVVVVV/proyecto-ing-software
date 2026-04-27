<?php
session_start();
require 'config/conexion.php'; 

//Validar seguridad
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o no iniciada.']); 
    exit();
}

//Leer el JSON 
$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos || empty($datos['boletos'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron boletos.']); 
    exit();
}

try {

    $conexion->beginTransaction();

    $id_usuario = $_SESSION['id_usuario']; // El taquillero
    $total_venta = 0;

    // Crea el Ticket general en tu tabla venta_entrada
    $stmtVenta = $conexion->prepare("INSERT INTO venta_entrada (id_usuario, fecha_hora, total) VALUES (?, NOW(), 0)");
    $stmtVenta->execute([$id_usuario]);
    $id_venta_entrada = $conexion->lastInsertId(); // Atrapamos el ID generado

    //  reparar consultas para los detalles
    $stmtPrecio = $conexion->prepare("SELECT precio_actual FROM tarifa_entrada WHERE id_tarifa = ? AND activa = 1");
    
    $stmtDetalle = $conexion->prepare("INSERT INTO detalle_venta_entrada (id_venta_entrada, id_tarifa, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

    // Procesar cada boleto del carrito
    foreach ($datos['boletos'] as $item) {
        $id_tarifa = $item['id_tarifa'];
        $cantidad = (int)$item['cantidad'];

        if ($cantidad <= 0) continue;

        // Validamos el precio real en la BD
        $stmtPrecio->execute([$id_tarifa]);
        $precio_real = $stmtPrecio->fetchColumn();

        if ($precio_real === false) {
            throw new Exception("El boleto seleccionado ya no existe o no está activo.");
        }

        // Calculamos subtotal
        $subtotal = $precio_real * $cantidad;
        $total_venta += $subtotal;

        // Guardamos el detalle de la compra 
        $stmtDetalle->execute([$id_venta_entrada, $id_tarifa, $cantidad, $precio_real, $subtotal]);
    }

    if ($total_venta == 0) {
        throw new Exception("El monto total no puede ser cero.");
    }

    // se actualiza la tabla venta_entrada con el Total Real
    $stmtUpdate = $conexion->prepare("UPDATE venta_entrada SET total = ? WHERE id_venta_entrada = ?");
    $stmtUpdate->execute([$total_venta, $id_venta_entrada]);

  
    $conexion->commit();

    echo json_encode([
        'status' => 'success', 
        'id_venta' => $id_venta_entrada, 
        'total' => number_format((float)$total_venta, 2, '.', '')
    ]);

} catch(Exception $e) {
    $conexion->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>