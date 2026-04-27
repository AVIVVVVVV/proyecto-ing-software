<?php
session_start();
require 'config/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_empresa'] ?? '');

    if (empty($nombre)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre es obligatorio.']); exit();
    }

    try {
        $stmt = $conexion->prepare("INSERT INTO proveedor (nombre_empresa) VALUES (?)");
        $stmt->execute([$nombre]);
        
        // Obtenemos el ID que la BD le acaba de asignar
        $nuevo_id = $conexion->lastInsertId();
        
        echo json_encode(['status' => 'success', 'id' => $nuevo_id, 'nombre' => $nombre]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>