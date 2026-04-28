<?php
// 1. SEGURIDAD
require 'backend/verificar_sesion.php';
require 'backend/config/conexion.php';

// Validar roles
$roles_permitidos = ['Administrador', 'Dueño', 'Vendedor'];
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: inicio.php");
    exit();
}

$titulo_pagina = "Gestión de Proveedores";
include 'frontend/includes/header.php';

// Obtener la lista de proveedores
try {
    $stmt = $conexion->query("SELECT * FROM proveedor ORDER BY nombre_empresa ASC");
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

<main class="container-fluid px-4 mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="input-group w-50 shadow-sm" style="border-radius: 8px; overflow: hidden;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="buscador-proveedores" class="form-control border-start-0 py-2" placeholder="Buscar por empresa o nombre...">
        </div>
        
        <button class="btn btn-primary fw-bold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarProveedor">
            AGREGAR PROVEEDOR
        </button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th class="py-3">Empresa</th>
                            <th class="py-3">Contacto</th>
                            <th class="py-3">Teléfono</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($proveedores) > 0): ?>
                            <?php foreach($proveedores as $prov): ?>
                                <tr>
                                    <td class="ps-4 text-secondary fw-bold"><?= $prov['id_proveedor'] ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($prov['nombre_empresa']) ?></td>
                                    <td><?= htmlspecialchars($prov['contacto_nombre'] . ' ' . $prov['contacto_apellido']) ?></td>
                                    <td>
                                        <?php if($prov['contacto_telefono']): ?>
                                            <a href="tel:<?= htmlspecialchars($prov['contacto_telefono']) ?>" class="text-decoration-none"><i class="bi bi-telephone-fill me-2 text-success"></i><?= htmlspecialchars($prov['contacto_telefono']) ?></a>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Sin teléfono</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm text-primary me-2 shadow-sm" onclick="abrirModalEditarProv(<?= $prov['id_proveedor'] ?>, '<?= htmlspecialchars($prov['nombre_empresa'], ENT_QUOTES) ?>', '<?= htmlspecialchars($prov['contacto_nombre'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($prov['contacto_apellido'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($prov['contacto_telefono'] ?? '', ENT_QUOTES) ?>')">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm text-danger shadow-sm" onclick="eliminarProveedor(<?= $prov['id_proveedor'] ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No hay proveedores registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<div class="modal fade" id="modalAgregarProveedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAgregarProveedor">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nombre de la Empresa *</label>
                        <input type="text" name="nombre_empresa" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nombre del Contacto</label>
                            <input type="text" name="contacto_nombre" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Apellido</label>
                            <input type="text" name="contacto_apellido" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Teléfono</label>
                        <input type="text" name="contacto_telefono" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProveedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarProveedor">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id_proveedor" name="id_proveedor">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nombre de la Empresa *</label>
                        <input type="text" id="edit_empresa" name="nombre_empresa" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nombre del Contacto</label>
                            <input type="text" id="edit_nombre" name="contacto_nombre" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Apellido</label>
                            <input type="text" id="edit_apellido" name="contacto_apellido" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Teléfono</label>
                        <input type="text" id="edit_telefono" name="contacto_telefono" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'frontend/includes/footer.php'; ?>
<script src="frontend/js/proveedores.js?v=1"></script>