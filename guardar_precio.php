<?php
// guardar_precio.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'conexion.php';

// Recibir los datos enviados por JavaScript en formato JSON
$datos = json_decode(file_get_contents('php://input'), true);

// 1. Validar que no falten datos
if (!isset($datos['categoria']) || !isset($datos['nombre']) || !isset($datos['precio'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
    exit;
}

$categoria = trim($datos['categoria']);
$nombre = trim($datos['nombre']);
$precio = floatval($datos['precio']);

// ... (arriba se queda igual validando datos)
$id_editar = isset($datos['id']) && $datos['id'] !== '' ? intval($datos['id']) : null;
$id_usuario = $_SESSION['id_usuario'] ?? 1; 

try {
    $conexion->beginTransaction();

    if ($id_editar) {
        // === MODO EDICIÓN ===
        if ($categoria === 'Entradas') {
            // Sacar precio viejo para historial
            $stmtOld = $conexion->prepare("SELECT precio_actual FROM tarifa_entrada WHERE id_tarifa = ?");
            $stmtOld->execute([$id_editar]);
            $precioAnterior = $stmtOld->fetchColumn();

            $stmt = $conexion->prepare("UPDATE tarifa_entrada SET nombre_tarifa = ?, precio_actual = ? WHERE id_tarifa = ?");
            $stmt->execute([$nombre, $precio, $id_editar]);

            $stmtHist = $conexion->prepare("INSERT INTO historial_tarifa (id_tarifa, id_usuario, precio_anterior, precio_nuevo) VALUES (?, ?, ?, ?)");
            $stmtHist->execute([$id_editar, $id_usuario, $precioAnterior, $precio]);
        } else {
            $stmt = $conexion->prepare("UPDATE producto SET nombre_producto = ?, descripcion = ?, precio_venta = ? WHERE id_producto = ?");
            $stmt->execute([$nombre, $categoria, $precio, $id_editar]);
        }
    } else {
        // === MODO CREACIÓN (El que ya tenías) ===
        if ($categoria === 'Entradas') {
            $stmt = $conexion->prepare("INSERT INTO tarifa_entrada (nombre_tarifa, precio_actual, activa) VALUES (?, ?, 1)");
            $stmt->execute([$nombre, $precio]);
            $id_tarifa = $conexion->lastInsertId();

            $stmtHist = $conexion->prepare("INSERT INTO historial_tarifa (id_tarifa, id_usuario, precio_anterior, precio_nuevo) VALUES (?, ?, 0, ?)");
            $stmtHist->execute([$id_tarifa, $id_usuario, $precio]);
        } else {
            $stmt = $conexion->prepare("INSERT INTO producto (nombre_producto, descripcion, precio_venta, stock_actual, stock_minimo) VALUES (?, ?, ?, 0, 0)");
            $stmt->execute([$nombre, $categoria, $precio]);
        }
    }

    $conexion->commit();
    echo json_encode(['status' => 'success', 'message' => 'Guardado correctamente.']);

} catch (PDOException $e) {
// ... (abajo se queda igual el catch)
}
?>