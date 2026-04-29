<?php
// 1. SEGURIDAD Y BASE DE DATOS
require 'backend/verificar_sesion.php';
require 'backend/config/conexion.php'; 

$lista_precios = [];

try {
    // A) Extraemos los Boletos (De la tabla tarifa_entrada)
    $stmtEntradas = $conexion->query("SELECT id_tarifa, nombre_tarifa, precio_actual FROM tarifa_entrada WHERE activa = 1");
    $entradas = $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);

    foreach($entradas as $ent) {
        $lista_precios[] = [
            'id_real' => $ent['id_tarifa'],
            'id_ui' => 'ENT-' . $ent['id_tarifa'], // Generamos el código visual (Ej. ENT-1)
            'tipo_tabla' => 'entrada',             // Etiqueta secreta para que el Backend sepa qué tabla actualizar
            'categoria' => 'Entradas',
            'nombre' => $ent['nombre_tarifa'],
            'precio' => $ent['precio_actual']
        ];
    }

    // B) Extraemos los Productos (De la tabla producto)
    $stmtProductos = $conexion->query("SELECT id_producto, nombre_producto, descripcion, precio_venta FROM producto");
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    foreach($productos as $prod) {
        // Tomamos la primera letra de la descripción para hacer el prefijo (Ej. Alimento -> ALI)
        $cat = $prod['descripcion']; // En tu BD dice "Alimento", "Bebida", "Servicio"
        $prefijo = strtoupper(substr($cat, 0, 3)); 

        $lista_precios[] = [
            'id_real' => $prod['id_producto'],
            'id_ui' => $prefijo . '-' . $prod['id_producto'],
            'tipo_tabla' => 'producto',
            'categoria' => $cat . 's', // Le agregamos la 's' para que diga "Alimentos" en vez de "Alimento"
            'nombre' => $prod['nombre_producto'],
            'precio' => $prod['precio_venta']
        ];
    }

} catch(PDOException $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}


include 'frontend/includes/header.php';
?>  
    
    <main class="container-fluid px-4 mt-4">
        <div class="row">

            <aside class="col-md-2 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-2">
                        <div class="nav flex-column nav-pills custom-sidebar" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active text-start mb-1 btn-filtro" data-filtro="Todos" data-bs-toggle="pill" type="button"><i class="bi bi-collection me-2"></i> Todos</button>
                            <button class="nav-link text-start mb-1 btn-filtro" data-filtro="Entradas" data-bs-toggle="pill" type="button"><i class="bi bi-ticket-perforated me-2"></i> Entradas</button>
                            <button class="nav-link text-start mb-1 btn-filtro" data-filtro="Alimentos" data-bs-toggle="pill" type="button"><i class="bi bi-cup-hot me-2"></i> Alimentos</button>
                            <button class="nav-link text-start mb-1 btn-filtro" data-filtro="Bebidas" data-bs-toggle="pill" type="button"><i class="bi bi-cup-straw me-2"></i> Bebidas</button>
                            <button class="nav-link text-start btn-filtro" data-filtro="Servicios" data-bs-toggle="pill" type="button"><i class="bi bi-tools me-2"></i> Servicios</button>
                            <button class="nav-link text-start btn-filtro" data-filtro="Articulos" data-bs-toggle="pill" type="button"><i class="bi bi-tools me-2"></i> Articulos</button>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="col-md-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="input-group w-50">
                                <span class="input-group-text bg-white custom-input border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="inputBusqueda" class="form-control custom-input border-start-0 ps-0" placeholder="Buscar por nombre o ID...">
                            </div>
                            <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalEditarPrecio">
                                AGREGAR NUEVO PRECIO
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table">
                                <thead class="table-light text-muted">
                                    <tr>
                                        <th>ID</th>
                                        <th>Categoría</th>
                                        <th>Nombre del Ítem</th>
                                        <th>Precio Actual ($)</th>
                                        <th class="text-end px-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    
                                    <?php if(count($lista_precios) > 0): ?>
                                        <?php foreach($lista_precios as $precio): ?>
                                            
                                        <tr id="fila-precio-<?= $precio['id_ui'] ?>" class="fila-articulo" data-categoria="<?= $precio['categoria'] ?>">
                                            <td class="fw-bold text-muted"><?= $precio['id_ui'] ?></td>
    
                                            <td>
                                                <span class="badge bg-secondary rounded-pill px-3 py-1"><?= $precio['categoria'] ?></span>
                                            </td>
    
                                            <td class="text-dark fw-semibold"><?= $precio['nombre'] ?></td>
    
                                            <td class="text-success fw-bold">$<?= number_format($precio['precio'], 2) ?></td>
    
                                            <td class="text-end px-4">
                                                <a href="#" class="text-primary me-3 text-decoration-none" title="Editar" 
                                                onclick="abrirModalEditarPrecio('<?= $precio['id_real'] ?>', '<?= $precio['tipo_tabla'] ?>', '<?= $precio['id_ui'] ?>', '<?= htmlspecialchars($precio['nombre'], ENT_QUOTES) ?>', '<?= $precio['categoria'] ?>', <?= $precio['precio'] ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
        
                                                <a href="#" class="text-danger text-decoration-none" title="Eliminar" 
                                                    onclick="eliminarPrecio('<?= $precio['id_real'] ?>', '<?= $precio['tipo_tabla'] ?>', '<?= $precio['id_ui'] ?>')">
                                                        <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No hay precios registrados.</td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="modalEditarPrecio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold text-dark">Editar Precio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <form id="formEditarPrecio">
                        
                        <input type="hidden" id="edit_id_precio" name="id_precio">
                        <input type="hidden" id="edit_id_ui">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">CATEGORÍA</label>
                            <select id="edit_categoria_precio" name="categoria" class="form-select custom-input" required>
                                <option value="Entradas">Entradas</option>
                                <option value="Alimentos">Alimentos</option>
                                <option value="Bebidas">Bebidas</option>
                                <option value="Servicios">Servicios</option>
                                <option value="Articulos">Articulos</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">NOMBRE DEL ÍTEM</label>
                            <input type="text" id="edit_nombre_precio" name="nombre_precio" class="form-control custom-input" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">PRECIO ACTUAL ($)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" id="edit_valor_precio" name="precio" class="form-control custom-input" step="0.01" min="0" required>
                            </div>
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

<?php 
include 'frontend/includes/footer.php'; 
?>
<script src="frontend/js/precios.js?v=1"></script>