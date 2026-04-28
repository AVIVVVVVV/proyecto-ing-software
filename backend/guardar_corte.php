<?php
session_start();
require 'config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['id_usuario']) || !$datos) exit;

try {
    $id_usuario = $_SESSION['id_usuario'];
    $entradas = $datos['entradas'];
    $ventas = $datos['ventas'];
    $gran_total = $entradas + $ventas;
    $efectivo_fisico = $datos['efectivo_fisico'];
    $diferencia = $efectivo_fisico - $gran_total;
    $justificacion = $datos['justificacion'] ?? '';

    // Inserción exacta a las columnas que me mostraste en la imagen
    $stmt = $conexion->prepare("INSERT INTO corte_caja (id_usuario, fecha_generacion, periodo_inicio, periodo_fin, total_entradas, total_ventas, gran_total, efectivo_fisico, diferencia, justificacion) VALUES (?, NOW(), CURDATE(), CURDATE(), ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$id_usuario, $entradas, $ventas, $gran_total, $efectivo_fisico, $diferencia, $justificacion]);

    echo json_encode(['status' => 'success']);

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>