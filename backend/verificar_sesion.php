<?php

session_start();

if (!isset($_SESSION['id_usuario'])) { //Si no existe un ID usuario en la sesión actual
    //Entonces significa que entro directamente a través de la ruta y se saltó el login
    header("Location: index.html?error=auth"); //Así que lo envia de regreso al login con un mensaje de error
    exit();
}

//Estas variables son para guardar nombre y rol de el usuario que está actualmente activo y usar esos datos mas facil en la interfaz
$nombre_usuario_actual = $_SESSION['nombre'];
$rol_usuario_actual = $_SESSION['rol'];
?>