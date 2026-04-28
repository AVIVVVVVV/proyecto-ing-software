<?php
require 'config/conexion.php';
$id = $_POST['id_proveedor'];
$empresa = $_POST['nombre_empresa'];
$nombre = $_POST['contacto_nombre'] ?? null;
$apellido = $_POST['contacto_apellido'] ?? null;
$telefono = $_POST['contacto_telefono'] ?? null;

try {
    $stmt = $conexion->prepare("UPDATE proveedor SET nombre_empresa=?, contacto_nombre=?, contacto_apellido=?, contacto_telefono=? WHERE id_proveedor=?");
    $stmt->execute([$empresa, $nombre, $apellido, $telefono, $id]);
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>