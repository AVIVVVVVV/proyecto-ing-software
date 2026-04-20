<?php
session_start();
require 'config/conexion.php'; 

$datos = json_decode(file_get_contents("php://input"), true);
$id_real = $datos['id_real'] ?? null;
$tipo_tabla = $datos['tipo_tabla'] ?? null;

if (!$id_real || !$tipo_tabla) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos.']); exit();
}

try {
    if ($tipo_tabla === 'entrada') {
        // En boletos tienes la columna 'activa', usamos borrado lógico
        $stmt = $conexion->prepare("UPDATE tarifa_entrada SET activa = 0 WHERE id_tarifa = ?");
    } else {
        // En productos borramos directo
        $stmt = $conexion->prepare("DELETE FROM producto WHERE id_producto = ?");
    }
    $stmt->execute([$id_real]);
    echo json_encode(['status' => 'success']);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>