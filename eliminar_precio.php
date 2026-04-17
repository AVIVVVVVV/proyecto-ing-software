<?php
// eliminar_precio.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require 'conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($datos['id']) || !isset($datos['categoria'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos para eliminar.']);
    exit;
}

$id = intval($datos['id']);
$categoria = $datos['categoria'];

try {
    if ($categoria === 'Entradas') {
        $stmt = $conexion->prepare("DELETE FROM tarifa_entrada WHERE id_tarifa = ?");
    } else {
        $stmt = $conexion->prepare("DELETE FROM producto WHERE id_producto = ?");
    }
    
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Eliminado correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>