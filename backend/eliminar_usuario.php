<?php
session_start();
require 'config/conexion.php'; 

// Filtro de seguridad: Solo Admin o Dueño pueden hacer esto
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Dueño')) {
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para esta acción.']);
    exit();
}

// Leer el ID que nos mandó JavaScript
$datos = json_decode(file_get_contents("php://input"), true);
$id_a_eliminar = $datos['id'] ?? null;

if (!$id_a_eliminar) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió un ID válido.']);
    exit();
}

// Evitar que el administrador se borre (desactive) a sí mismo por accidente
if ($id_a_eliminar == $_SESSION['id_usuario']) {
    echo json_encode(['status' => 'error', 'message' => 'Protocolo de seguridad: No puedes desactivar tu propia cuenta.']);
    exit();
}

try {
    // Ejecutar el Borrado Lógico (Cambiar estado a 0)
    $stmt = $conexion->prepare("UPDATE usuario SET estado = 0 WHERE id_usuario = ?");
    $stmt->execute([$id_a_eliminar]);

    // Responder que todo salió perfecto
    echo json_encode(['status' => 'success']);

} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>