<?php
// backend/editar_usuario.php
session_start();
require 'config/conexion.php';
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Dueño')) {
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'] ?? null;
    $nuevo_nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $nuevo_id_rol = $_POST['nuevo_rol'] ?? null;
    
    // Si el checkbox está marcado llega un "1", si no, no llega nada (le ponemos 0)
    $nuevo_estado = isset($_POST['estado']) ? 1 : 0; 

    if (!$id_usuario || !$nuevo_id_rol || empty($nuevo_nombre_usuario)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit();
    }

    // Regla de Oro: Un administrador no puede desactivarse a sí mismo por accidente
    if ($id_usuario == $_SESSION['id_usuario'] && $nuevo_estado == 0) {
        echo json_encode(['status' => 'error', 'message' => 'No puedes desactivar tu propia cuenta activa.']);
        exit();
    }

    try {
        // Iniciamos la transacción (Todo o nada)
        $conexion->beginTransaction();

        // 1. Actualizamos la tabla principal (usuario)
        $stmt1 = $conexion->prepare("UPDATE usuario SET nombre_usuario = ?, estado = ? WHERE id_usuario = ?");
        $stmt1->execute([$nuevo_nombre_usuario, $nuevo_estado, $id_usuario]);

        // 2. Actualizamos la tabla puente (usuario_rol)
        $stmt2 = $conexion->prepare("UPDATE usuario_rol SET id_rol = ? WHERE id_usuario = ?");
        $stmt2->execute([$nuevo_id_rol, $id_usuario]);

        // Si ambas salieron bien, aplicamos los cambios reales a la BD
        $conexion->commit();

        echo json_encode(['status' => 'success']);

    } catch(PDOException $e) {
        // Si algo explotó, deshacemos cualquier cambio a medias
        $conexion->rollBack();
        
        // Si el error es por nombre de usuario duplicado (código 23000 de MySQL)
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'Ese nombre de usuario ya está ocupado por otra persona.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en BD: ' . $e->getMessage()]);
        }
    }
}
?>