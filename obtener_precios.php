<?php
// obtener_precios.php
header('Content-Type: application/json; charset=utf-8');
require 'conexion.php';

try {
    $items = [];

    // 1. Consultar Entradas
    $stmtEntradas = $conexion->prepare("SELECT id_tarifa AS id, 'Entradas' AS categoria, nombre_tarifa AS nombre, precio_actual AS precio FROM tarifa_entrada WHERE activa = 1");
    $stmtEntradas->execute();
    $entradas = $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);
    foreach ($entradas as $entrada) {
        $items[] = $entrada;
    }

    // 2. Consultar Productos (AQUÍ ESTÁ LA MAGIA PARA QUE NO SALGAN EN "OTROS")
    $stmtProductos = $conexion->prepare("SELECT id_producto AS id, 
                                                CASE 
                                                    WHEN descripcion IN ('Alimento', 'Alimentos') THEN 'Alimentos'
                                                    WHEN descripcion IN ('Bebida', 'Bebidas') THEN 'Bebidas'
                                                    WHEN descripcion IN ('Servicio', 'Servicios') THEN 'Servicios'
                                                    ELSE 'Otros'
                                                END AS categoria, 
                                                nombre_producto AS nombre, 
                                                precio_venta AS precio 
                                         FROM producto");
    $stmtProductos->execute();
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
    foreach ($productos as $producto) {
        $items[] = $producto;
    }

    echo json_encode(['status' => 'success', 'data' => $items]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>