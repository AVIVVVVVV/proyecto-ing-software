<?php
require 'config/conexion.php';
$empresa = $_POST['nombre_empresa'];
$nombre = $_POST['contacto_nombre'] ?? null;
$apellido = $_POST['contacto_apellido'] ?? null;
$telefono = $_POST['contacto_telefono'] ?? null;

try {
    $stmt = $conexion->prepare("INSERT INTO proveedor (nombre_empresa, contacto_nombre, contacto_apellido, contacto_telefono) VALUES (?, ?, ?, ?)");
    $stmt->execute([$empresa, $nombre, $apellido, $telefono]);
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>