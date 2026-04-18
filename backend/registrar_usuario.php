<?php

require 'config/conexion.php'; //Lo conecta a la base de datos

$mensajeExito = '';
$mensajeError = '';

// Si el formulario fue enviado...
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario
    $nombre_completo = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $usuario = trim($_POST['usuario']);
    $rol_texto = trim($_POST['rol']); 

    // Dividimos el "Nombre Completo" en Nombre y Apellido Paterno para que encaje en tu tabla
    $partes_nombre = explode(' ', $nombre_completo, 2);
    $nombre_pila = $partes_nombre[0];
    $apellido_p = isset($partes_nombre[1]) ? $partes_nombre[1] : ''; // Si no puso apellido, se va vacío

    // --- MAPEO DE ROLES ---
    // Asignamos el número de ID correspondiente a cada rol
    $mapa_roles = [
        'Administrador' => 1,
        'Taquillero' => 2,
        'Cajero' => 3,
        'Gerente' => 4
    ];
    $id_rol = $mapa_roles[$rol_texto];

    // Generamos y encriptamos la contraseña aleatoria (8 caracteres)
    $password_plana = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

    try {
        // Iniciamos una TRANSACCIÓN para proteger las dos tablas
        $conexion->beginTransaction();

        // 1. Insertamos en la tabla `usuario`
        $stmt = $conexion->prepare("INSERT INTO usuario (nombre, apellido_paterno, apellido_materno, correo, nombre_usuario, contrasena, estado) VALUES (?, ?, NULL, ?, ?, ?, 1)");
        $stmt->execute([$nombre_pila, $apellido_p, $correo, $usuario, $password_hash]);

        // Sacamos el ID (id_usuario) que MySQL le acaba de asignar a esta persona
        $id_nuevo_usuario = $conexion->lastInsertId();

        // 2. Insertamos la relación en la tabla `usuario_rol`
        $stmt_rol = $conexion->prepare("INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (?, ?)");
        $stmt_rol->execute([$id_nuevo_usuario, $id_rol]);

        // Todo salió bien, confirmamos los cambios en la base de datos
        $conexion->commit();

        // Preparamos el mensaje de éxito para mostrar en la pantalla
        $mensajeExito = "
            <h5 class='alert-heading fw-bold mb-2'>¡Usuario creado exitosamente!</h5>
            <p class='mb-2'>Copia estas credenciales y entrégaselas al empleado:</p>
            <ul class='mb-0'>
                <li><strong>Usuario:</strong> $usuario</li>
                <li><strong>Correo:</strong> $correo</li>
                <li><strong>Contraseña:</strong> <span class='fs-5 font-monospace bg-white px-2 rounded border border-success'>$password_plana</span></li>
            </ul>
        ";

    } catch(PDOException $e) {
        // Si algo sale mal, cancelamos la transacción
        $conexion->rollBack();
        
        // Error 23000 usualmente es porque el correo o usuario ya existen
        if($e->getCode() == 23000) {
            $mensajeError = "Ese Nombre de Usuario o Correo ya están en uso. Intenta con otros.";
        } else {
            $mensajeError = "Hubo un error al registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario - Balneario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #e2e2e2; min-height: 100vh; } 
        .navbar-custom { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card-custom { border-radius: 12px; }
        .btn-primary-custom { background-color: #007bff; color: white; border: none; font-weight: 800; border-radius: 8px; }
        .btn-primary-custom:hover { background-color: #0069d9; color: white; }
        .btn-secondary-custom { background-color: #dbdbdb; color: #000; border: none; font-weight: 800; border-radius: 8px; }
        .btn-secondary-custom:hover { background-color: #c4c4c4; }
        .custom-label { font-weight: 700; font-size: 0.9rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" href="#">Balneario</a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold fs-6 d-none d-sm-block">Administrador</span>
                <div class="rounded-circle" style="width: 45px; height: 45px; background-color: #d1d1d1;"></div>
            </div>
        </div>
    </nav>

    <div class="container d-flex justify-content-center" style="margin-top: 50px;">
        <div class="card card-custom shadow border-0 p-5" style="max-width: 650px; width: 100%;">
            
            <h3 class="text-center fw-bold mb-4 pb-2">Registrar Nuevo Usuario</h3>

            <?php if($mensajeExito): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <?= $mensajeExito ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if($mensajeError): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <?= $mensajeError ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="registrar_usuario.php" method="POST">
                <div class="mb-4">
                    <label class="form-label custom-label">Nombre Completo</label>
                    <input type="text" class="form-control form-control-lg bg-light fs-6" name="nombre" placeholder="Nombre completo" required>
                </div>

                <div class="mb-4">
                    <label class="form-label custom-label">Correo Electrónico</label>
                    <input type="email" class="form-control form-control-lg bg-light fs-6" name="correo" placeholder="Correo electronico" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <label class="form-label custom-label">Nombre de Usuario</label>
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="usuario" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label custom-label">Rol</label>
                        <select class="form-select form-select-lg bg-light fs-6" name="rol" required>
                            <option selected disabled value="">Rol</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Taquillero">Taquillero</option>
                            <option value="Cajero">Cajero</option>
                            <option value="Gerente">Gerente</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-7">
                        <button type="submit" class="btn btn-primary-custom w-100 py-3">CREAR USUARIO</button>
                    </div>
                    <div class="col-5">
                        <a href="inicio.html" class="btn btn-secondary-custom w-100 py-3 text-center text-decoration-none d-block">CANCELAR</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>