<?php
// conexion.php
$host = 'localhost';
$dbname = 'balneario_db';
$username = 'root'; // Tu usuario de MySQL (por defecto en XAMPP es root)
$password = ''; // Tu contraseña de MySQL (por defecto en XAMPP está vacía)

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que lance excepciones en caso de error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>