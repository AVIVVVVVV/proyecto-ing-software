  /*
    Este es un seed script. Se ejecuta una vez para dejar la base de datos lista sin incersiones "manuales"
  */
<?php
require 'config/conexion.php';

try {
    $conexion->exec("INSERT IGNORE INTO rol (id_rol, nombre_rol, descripcion) VALUES 
        (1, 'Administrador', 'Control total del sistema'),
        (2, 'Taquillero', 'Módulo de venta de boletos'),
        (3, 'Vendedor', 'Módulo de punto de venta tienda'),
        (4, 'Dueño', 'Acceso a reportes')");

    $nombre = 'Admin';
    $apellido_p = 'Balneario';
    $correo = 'admin@elarco.com';
    $usuario = 'admin_arco';
    $password_plana = 'admin123'; // La contraseña que usarás para entrar

    //Encripta la contraseña 
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

    //Inserta el usuario
    $stmt = $conexion->prepare("INSERT INTO usuario (nombre, apellido_paterno, correo, nombre_usuario, contrasena, estado) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([$nombre, $apellido_p, $correo, $usuario, $password_hash]);
    
    // Obtener el ID del usuario recién creado y asignarle el rol 1 (Administrador)
    $id_usuario = $conexion->lastInsertId();
    $stmtRol = $conexion->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?, 1)");
    $stmtRol->execute([$id_usuario]);

    echo "¡Administrador creado con éxito! Ya puedes borrar este archivo setup.php";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>