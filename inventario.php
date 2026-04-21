<?php
// 1. SEGURIDAD Y BASE DE DATOS
require 'backend/verificar_sesion.php';
require 'backend/config/conexion.php'; 

try {
    // Obtenemos los productos para la tabla principal
    $stmt = $conexion->query("SELECT * FROM producto ORDER BY nombre_producto ASC");
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtenemos los proveedores para el modal de entrada
    $stmtProv = $conexion->query("SELECT * FROM proveedor ORDER BY nombre_empresa ASC");
    $proveedores = $stmtProv->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error al cargar inventario: " . $e->getMessage());
}

// 2. CABECERA
include 'frontend/includes/header.php';
?>

<main class="container-fluid px-4 mt-4">
    <div class="row">
        <section class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="input-group w-50">
                            <span class="input-group-text bg-white custom-input border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="inputBusquedaInv" class="form-control custom-input border-start-0 ps-0" placeholder="Buscar producto por nombre...">
                        </div>
                        <button class="btn btn-outline-primary fw-bold ms-3" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                            <i class="bi bi-plus-lg me-2"></i>NUEVO PRODUCTO
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-table" id="tablaInventario">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>ID</th>
                                    <th>Categoría</th>
                                    <th>Nombre del Producto</th>
                                    <th>Precio Venta</th>
                                    <th class="text-center">Stock Actual</th>
                                    <th class="text-end px-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if(count($inventario) > 0): ?>
                                    <?php foreach($inventario as $item): ?>
                                        <tr id="fila-inv-<?= $item['id_producto'] ?>" class="fila-producto">
                                            <td class="fw-bold text-muted"><?= $item['id_producto'] ?></td>
                                            <td><span class="badge bg-secondary rounded-pill px-3 py-1"><?= htmlspecialchars($item['descripcion']) ?></span></td>
                                            <td class="text-dark fw-semibold"><?= htmlspecialchars($item['nombre_producto']) ?></td>
                                            <td class="text-success fw-bold">$<?= number_format($item['precio_venta'], 2) ?></td>
                                            
                                            <td class="text-center">
                                                <?php 
                                                    $colorStock = 'bg-success';
                                                    if($item['stock_actual'] <= $item['stock_minimo']) $colorStock = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $colorStock ?> fs-6 rounded-pill px-3" id="stock-val-<?= $item['id_producto'] ?>">
                                                    <?= $item['stock_actual'] ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-end px-4">
                                                <button class="btn btn-sm btn-primary fw-bold shadow-sm" title="Dar de alta mercancía" 
                                                   onclick="abrirModalEntrada(<?= $item['id_producto'] ?>, '<?= htmlspecialchars($item['nombre_producto'], ENT_QUOTES) ?>')">
                                                    <i class="bi bi-box-arrow-in-down me-1"></i> ENTRADA
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay productos en el inventario.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Dar de Alta Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="formNuevoProducto">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">NOMBRE DEL PRODUCTO</label>
                        <input type="text" name="nombre_producto" class="form-control custom-input" placeholder="Ej. Bloqueador Solar" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">CATEGORÍA</label>
                        <select name="descripcion" class="form-select custom-input" required>
                            <option value="">Selecciona una categoría...</option>
                            <option value="Alimento">Alimento</option>
                            <option value="Bebida">Bebida</option>
                            <option value="Servicio">Servicio</option>
                            <option value="Articulo">Artículo de Tienda</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">PRECIO DE VENTA ($)</label>
                            <input type="number" name="precio_venta" class="form-control custom-input" step="0.50" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">STOCK MÍNIMO (Alerta)</label>
                            <input type="number" name="stock_minimo" class="form-control custom-input" min="0" value="5" required>
                            <div class="form-text small">Para alertas en rojo</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">GUARDAR PRODUCTO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'frontend/includes/footer.php'; ?>
<script src="frontend/js/inventario.js?v=1"></script>
