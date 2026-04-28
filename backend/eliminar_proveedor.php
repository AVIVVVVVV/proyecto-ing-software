<?php
require 'config/conexion.php';
$id = $_POST['id_proveedor'];

try {
    $stmt = $conexion->prepare("DELETE FROM proveedor WHERE id_proveedor=?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'No puedes borrar un proveedor que ya tiene movimientos de inventario registrados.']);
}
?>