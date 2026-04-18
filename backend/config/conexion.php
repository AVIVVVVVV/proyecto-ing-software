<?php
$host = 'localhost';
$dbname = 'balneario_db';
$username = 'root'; // Por defecto el usuario en PHP es root
$password = ''; // La contraseña por defecto está vacía en xamp

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>