<?php 

require 'backend/verificar_sesion.php'; 


if($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Dueño'){
    header("Location: inicio.php");
    exit();
}


require 'backend/config/conexion.php';

try {
    // Traemos a todos los usuarios uniendo sus roles
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.correo, u.nombre_usuario, u.estado, r.nombre_rol 
            FROM usuario u
            LEFT JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
            LEFT JOIN rol r ON ur.id_rol = r.id_rol
            ORDER BY u.id_usuario ASC"; // Ordenar por ID
            
    $stmt = $conexion->query($sql);
    $lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar usuarios: " . $e->getMessage());
}


include 'frontend/includes/header.php'; 
?>

<div class="container-fluid py-4" style="max-width: 1400px;">
    
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <input type="text" class="form-control" placeholder="Buscar por nombre, usuario o correo...">
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="registrar_usuario.php" class="btn btn-primary px-4 fw-bold shadow-sm">
                AGREGAR NUEVO USUARIO
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="px-4 py-3 text-muted fw-bold" style="font-size: 0.85rem;">ID</th>
                            <th class="py-3 text-muted fw-bold" style="font-size: 0.85rem;">NOMBRE COMPLETO</th>
                            <th class="py-3 text-muted fw-bold" style="font-size: 0.85rem;">USUARIO / CORREO</th>
                            <th class="py-3 text-muted fw-bold" style="font-size: 0.85rem;">ROL</th>
                            <th class="py-3 text-muted fw-bold text-center" style="font-size: 0.85rem;">ESTADO</th>
                            <th class="px-4 py-3 text-muted fw-bold text-end" style="font-size: 0.85rem;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        
                        <?php if(count($lista_usuarios) > 0): ?>
                            <?php foreach($lista_usuarios as $user): ?>
                                
                                <?php 
                                    // LOGICA VISUAL: Asignar un color de etiqueta dependiendo del rol
                                    $color_badge = 'bg-secondary'; // Por defecto
                                    if($user['nombre_rol'] == 'Administrador' || $user['nombre_rol'] == 'Dueño') {
                                        $color_badge = 'bg-primary';
                                    } elseif($user['nombre_rol'] == 'Taquillero') {
                                        $color_badge = 'bg-success';
                                    } elseif($user['nombre_rol'] == 'Vendedor') {
                                        $color_badge = 'bg-info text-dark';
                                    }

                                    // Construimos el nombre completo (Si no tiene apellido, no marca error)
                                    $nombre_completo = $user['nombre'] . ' ' . ($user['apellido_paterno'] ?? '');
                                ?>

                                <tr id="fila-usuario-<?= $user['id_usuario'] ?>">
                                    <td class="px-4 fw-bold text-dark"><?= $user['id_usuario'] ?></td>
                                    
                                    <td class="text-dark fw-semibold"><?= $nombre_completo ?></td>
                                    
                                    <td>
                                        <span class="d-block text-dark fw-bold"><?= $user['nombre_usuario'] ?></span>
                                        <small class="text-muted"><?= $user['correo'] ?></small>
                                    </td>
                                    
                                    <td>
                                        <span class="badge <?= $color_badge ?> rounded-pill px-3 py-2 text-white">
                                            <?= $user['nombre_rol'] ?? 'Sin Rol' ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input fs-5" type="checkbox" style="cursor: pointer;" 
                                                   <?= $user['estado'] == 1 ? 'checked' : '' ?> disabled>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 text-end">
                                        <a href="#" class="text-primary me-3 text-decoration-none" title="Editar" 
                                            onclick="abrirModalEditar(<?= $user['id_usuario'] ?>, '<?= $user['nombre_usuario'] ?>', '<?= $user['nombre_rol'] ?>', <?= $user['estado'] ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
    
                                        <a href="#" class="text-danger text-decoration-none" title="Eliminar" onclick="eliminarUsuario(<?= $user['id_usuario'] ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay usuarios registrados en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      
      <div class="modal-header bg-light border-0">
        <h5 class="modal-title fw-bold text-dark">Editar Perfil de Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4">
        <form id="formEditarUsuario">
            <input type="hidden" id="edit_id_usuario" name="id_usuario">
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small">NOMBRE DE USUARIO</label>
                <input type="text" class="form-control" id="edit_nombre_usuario" name="nombre_usuario" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small">ROL</label>
                <select class="form-select" id="edit_rol" name="nuevo_rol" required>
                    <option value="1">Administrador</option>
                    <option value="2">Taquillero</option>
                    <option value="3">Vendedor</option>
                    <option value="4">Dueño</option>
                </select>
            </div>

            <div class="mb-4 form-check form-switch mt-3">
                <input class="form-check-input fs-5" type="checkbox" id="edit_estado" name="estado" value="1">
                <label class="form-check-label fw-bold mt-1 ms-2 text-dark" for="edit_estado">Cuenta Activa</label>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar Cambios</button>
            </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script src="frontend/js/usuarios.js"></script>
<?php include 'frontend/includes/footer.php'; ?>
