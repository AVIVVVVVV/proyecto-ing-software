<?php
session_start();
require 'config/conexion.php'; 

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sin permisos.']); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre_producto'] ?? '');
    $descripcion = $_POST['descripcion'] ?? ''; // La categoría
    $precio = $_POST['precio_venta'] ?? 0;
    $stock_min = (int)($_POST['stock_minimo'] ?? 0);

    if (empty($nombre) || empty($descripcion)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']); 
        exit();
    }

    try {
        // Insertamos el producto. Nota que NO enviamos stock_actual, 
        // la BD le pondrá 0 por defecto como lo configuraste en phpMyAdmin.
        $stmt = $conexion->prepare("INSERT INTO producto (nombre_producto, descripcion, precio_venta, stock_minimo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $stock_min]);

        echo json_encode(['status' => 'success']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
    }
}
?>