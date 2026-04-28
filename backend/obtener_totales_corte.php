<?php
session_start();
require 'config/conexion.php';

try {
    // 1. Recibimos la fecha del calendario (si no mandan nada, usamos hoy)
    $fecha_consulta = $_GET['fecha'] ?? date('Y-m-d');

    // 2. Suma de Entradas (Boletos) de ese día en específico
    $stmtEntradas = $conexion->prepare("SELECT SUM(total) FROM venta_entrada WHERE DATE(fecha_hora) = ?");
    $stmtEntradas->execute([$fecha_consulta]);
    $total_entradas = $stmtEntradas->fetchColumn() ?: 0;

    // 3. Suma de Tiendita de ese día en específico
    $stmtVentas = $conexion->prepare("SELECT SUM(total) FROM venta_producto WHERE DATE(fecha_hora) = ?");
    $stmtVentas->execute([$fecha_consulta]);
    $total_ventas = $stmtVentas->fetchColumn() ?: 0;

    echo json_encode([
        'status' => 'success',
        'entradas' => $total_entradas,
        'ventas' => $total_ventas,
        'fecha_consultada' => $fecha_consulta // Solo por si quieres usarlo después
    ]);

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>