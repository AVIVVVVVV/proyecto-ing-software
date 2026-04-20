<?php
session_start();
require 'config/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_real = $_POST['id_precio'] ?? '';
    $nombre = $_POST['nombre_precio'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $precio = $_POST['precio'] ?? 0;

    if (empty($nombre) || empty($categoria)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']); 
        exit();
    }

    $tipo_tabla = ($categoria === 'Entradas') ? 'entrada' : 'producto';
    $accion = empty($id_real) ? 'crear' : 'editar'; // Saber si insertamos o actualizamos

    try {
        if ($accion === 'crear') {
            if ($tipo_tabla === 'entrada') {
                $stmt = $conexion->prepare("INSERT INTO tarifa_entrada (nombre_tarifa, precio_actual, activa) VALUES (?, ?, 1)");
                $stmt->execute([$nombre, $precio]);
            } else {
                $desc = rtrim($categoria, 's'); 
                $stmt = $conexion->prepare("INSERT INTO producto (nombre_producto, descripcion, precio_venta) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $desc, $precio]);
            }
            $id_real = $conexion->lastInsertId(); // Atrapamos el ID recién creado

        } else {
            if ($tipo_tabla === 'entrada') {
                $stmt = $conexion->prepare("UPDATE tarifa_entrada SET nombre_tarifa = ?, precio_actual = ? WHERE id_tarifa = ?");
                $stmt->execute([$nombre, $precio, $id_real]);
            } else {
                $desc = rtrim($categoria, 's'); 
                $stmt = $conexion->prepare("UPDATE producto SET nombre_producto = ?, descripcion = ?, precio_venta = ? WHERE id_producto = ?");
                $stmt->execute([$nombre, $desc, $precio, $id_real]);
            }
        }
        
        // Armamos el ID visual (Ej: ALI-15) para que JS sepa cómo dibujarlo
        $prefijo = ($tipo_tabla === 'entrada') ? 'ENT' : strtoupper(substr(rtrim($categoria, 's'), 0, 3));
        $id_ui = $prefijo . '-' . $id_real;
        
        // Respondemos con Éxito y le mandamos los datos frescos a JavaScript
        echo json_encode([
            'status' => 'success',
            'accion' => $accion,
            'data' => [
                'id_real' => $id_real,
                'id_ui' => $id_ui,
                'tipo_tabla' => $tipo_tabla,
                'categoria' => $categoria,
                'nombre' => $nombre,
                'precio' => number_format((float)$precio, 2, '.', '')
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
    }
}
?>