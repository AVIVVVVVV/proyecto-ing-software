<?php
// Aquí sigue el session_start() y tu código...
// procesar_login.php
session_start(); // Iniciar la sesión para guardar los datos del usuario
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identificador = trim($_POST['identificador']); // Puede ser correo o usuario
    $password = $_POST['password'];

    try {
        // Buscar al usuario por correo o nombre de usuario
        $stmt = $conexion->prepare("SELECT u.id_usuario, u.nombre, u.contrasena, r.nombre_rol 
                                    FROM usuario u
                                    JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                                    JOIN rol r ON ur.id_rol = r.id_rol
                                    WHERE (u.correo = ? OR u.nombre_usuario = ?) AND u.estado = 1");
        $stmt->execute([$identificador, $identificador]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe y la contraseña encriptada coincide
        if ($usuario && password_verify($password, $usuario['contrasena'])) {
            
            // Guardar datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['nombre_rol'];

            // ¡AQUÍ ESTÁ EL CAMBIO! Redirigir a inicio.html
            header("Location: inicio.html");
            exit();

        } else {
            // Credenciales incorrectas: Redirigir de vuelta al login
            header("Location: index.html?error=1");
            exit();
        }

    } catch(PDOException $e) {
        die("Error en la consulta: " . $e->getMessage());
    }
}
?>