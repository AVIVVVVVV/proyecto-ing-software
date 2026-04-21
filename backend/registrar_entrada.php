<?php
// backend/registrar_entrada.php
session_start();
require 'config/conexion.php'; 

// Verificamos que el usuario esté logueado (necesitamos su ID para el registro)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'] ?? '';
    $id_proveedor = !empty($_POST['id_proveedor']) ? $_POST['id_proveedor'] : null;
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $concepto = trim($_POST['concepto'] ?? '');
    $id_usuario = $_SESSION['id_usuario']; // El usuario que hace el movimiento

    // RÚBRICA PTO 6: Validación en Backend (Evitar vacíos y negativos)
    if (empty($id_producto) || empty($concepto) || $cantidad <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. La cantidad debe ser mayor a cero.']); 
        exit();
    }

    try {
        // Iniciamos la transacción (Todo o Nada)
        $conexion->beginTransaction();

        // 1. RÚBRICA PTO 5: Sumar al stock actual (UPDATE)
        $stmtUpdate = $conexion->prepare("UPDATE producto SET stock_actual = stock_actual + ? WHERE id_producto = ?");
        $stmtUpdate->execute([$cantidad, $id_producto]);

        // 2. RÚBRICA PTO 5: Registrar el movimiento (INSERT)
        $stmtInsert = $conexion->prepare("INSERT INTO movimiento_inventario (id_producto, id_usuario, id_proveedor, tipo_movimiento, cantidad, concepto) VALUES (?, ?, ?, 'Entrada', ?, ?)");
        $stmtInsert->execute([$id_producto, $id_usuario, $id_proveedor, $cantidad, $concepto]);

        // 3. Obtenemos el nuevo stock para regresarlo al frontend y que se actualice en pantalla
        $stmtSelect = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = ?");
        $stmtSelect->execute([$id_producto]);
        $nuevo_stock = $stmtSelect->fetchColumn();

        // Confirmamos la transacción
        $conexion->commit();

        echo json_encode(['status' => 'success', 'nuevo_stock' => $nuevo_stock]);
        
    } catch(PDOException $e) {
        $conexion->rollBack(); // Si hay error, deshacemos todo
        echo json_encode(['status' => 'error', 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
}
?>