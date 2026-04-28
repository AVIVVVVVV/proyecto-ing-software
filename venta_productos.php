<?php

require 'backend/verificar_sesion.php';
require 'backend/config/conexion.php'; 

try {
    // Obtenemos solo los productos que tienen stock mayor a 0 (No puedes vender lo que no tienes)
    $stmt = $conexion->query("SELECT * FROM producto WHERE stock_actual > 0 ORDER BY nombre_producto ASC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtenemos las categorías únicas para los botones de filtro
    $stmtCat = $conexion->query("SELECT DISTINCT descripcion FROM producto WHERE stock_actual > 0");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

} catch(PDOException $e) {
    die("Error al cargar los productos: " . $e->getMessage());
}


$titulo_pagina = "Punto de Venta - Tienda";
include 'frontend/includes/header.php';
?>

<main class="container-fluid px-4 mt-4">
    <div class="row gx-4">
        
        <section class="col-lg-7 mb-4">
            
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="buscador-productos" class="form-control border-start-0 bg-light" placeholder="Buscar producto por nombre..." style="border-radius: 0 8px 8px 0;">
                    </div>
                    
                    <div class="d-flex flex-wrap gap-2" id="contenedor-categorias">
                        <button class="btn btn-dark btn-sm rounded-pill px-3 btn-filtro active" data-categoria="todos">Todos</button>
                        <?php foreach($categorias as $cat): ?>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-filtro" data-categoria="<?= htmlspecialchars($cat) ?>">
                                <?= htmlspecialchars($cat) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="row g-3" id="lista-productos">
                <?php if(count($productos) > 0): ?>
                    <?php foreach($productos as $prod): ?>
                        <div class="col-md-6 col-xl-6 item-producto" data-categoria="<?= htmlspecialchars($prod['descripcion']) ?>" data-nombre="<?= strtolower(htmlspecialchars($prod['nombre_producto'])) ?>">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; transition: transform 0.2s;">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    
                                    <div class="mb-2">
                                        <span class="badge bg-light text-secondary mb-2"><?= htmlspecialchars($prod['descripcion']) ?></span>
                                        <h5 class="fw-bold text-dark mb-1 lh-sm"><?= htmlspecialchars($prod['nombre_producto']) ?></h5>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="text-success mb-0 fw-bold">$<?= number_format($prod['precio_venta'], 2) ?></h4>
                                            <small class="text-muted fw-semibold">Stock: <span id="stock-disp-<?= $prod['id_producto'] ?>"><?= $prod['stock_actual'] ?></span></small>
                                        </div>
                                    </div>
                                    
                                    <div class="input-group input-group-sm mt-auto" style="box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
                                        <button class="btn btn-light border btn-restar fw-bold px-3" type="button" data-id="<?= $prod['id_producto'] ?>">-</button>
                                        
                                        <input type="text" class="form-control text-center fw-bold input-cantidad bg-white" 
                                               id="cant-<?= $prod['id_producto'] ?>" 
                                               data-id="<?= $prod['id_producto'] ?>" 
                                               data-nombre="<?= htmlspecialchars($prod['nombre_producto'], ENT_QUOTES) ?>" 
                                               data-precio="<?= $prod['precio_venta'] ?>" 
                                               data-stock="<?= $prod['stock_actual'] ?>"
                                               value="0" readonly>
                                               
                                        <button class="btn btn-light border btn-sumar fw-bold px-3" type="button" data-id="<?= $prod['id_producto'] ?>">+</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No hay productos en inventario para vender.</div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px; border-radius: 15px;">
                <div class="card-body p-4 p-lg-5">
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="border-bottom border-2">
                                    <th class="ps-0 text-dark fw-bold fs-6">Nombre</th>
                                    <th class="text-center text-dark fw-bold fs-6">Cantidad</th>
                                    <th class="text-end pe-0 text-dark fw-bold fs-6">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="lista-carrito">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Selecciona productos para comenzar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr class="border-secondary opacity-25 mb-4">
                    
                    <div class="text-center mb-5">
                        <p class="text-muted mb-1 fs-5">Total a cobrar</p>
                        <h1 class="display-4 fw-bolder text-dark mb-0" id="total-cobrar">$0.00</h1>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <button class="btn btn-primary btn-lg fw-bold py-3 fs-5 shadow-sm" id="btn-confirmar-venta" style="border-radius: 10px;">
                            CONFIRMAR VENTA
                        </button>
                        <button class="btn btn-light btn-lg fw-bold text-muted border py-3 fs-5" id="btn-cancelar-venta" style="border-radius: 10px; background-color: #e9ecef;">
                            CANCELAR VENTA
                        </button>
                    </div>

                </div>
            </div>
        </section>

    </div>
</main>

<?php include 'frontend/includes/footer.php'; ?>
<script src="frontend/js/venta_productos.js?v=1"></script>