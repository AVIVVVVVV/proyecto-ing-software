<?php

require 'backend/verificar_sesion.php';
require 'backend/config/conexion.php'; 

try {
    $stmt = $conexion->query("SELECT * FROM tarifa_entrada WHERE activa = 1 ORDER BY precio_actual DESC"); // Las tarifas activas en la tabla de tarifa_entrada y su respectivo precio
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch(PDOException $e) {
    die("Error al cargar las tarifas: " . $e->getMessage());
}


$titulo_pagina = "Venta de Boletos";
include 'frontend/includes/header.php';
?>

<main class="container-fluid px-4 mt-4">
    <div class="row gx-4">
        
        <section class="col-lg-7 mb-4">
            <?php if(count($tarifas) > 0): ?>
                <?php foreach($tarifas as $tarifa): ?>
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center">
                            
                            <div>
                                <h3 class="fw-bold mb-1 text-dark text-uppercase"><?= htmlspecialchars($tarifa['nombre_tarifa']) ?></h3>
                                <h4 class="text-success mb-0 fw-bold">$<?= number_format($tarifa['precio_actual'], 2) ?></h4>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <div class="input-group input-group-lg" style="width: 160px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
                                    <button class="btn btn-light border btn-restar fw-bold fs-4 px-3" type="button" data-id="<?= $tarifa['id_tarifa'] ?>">-</button>
                                    
                                    <input type="text" class="form-control text-center fw-bold fs-4 input-cantidad bg-white" 
                                           id="cant-<?= $tarifa['id_tarifa'] ?>" 
                                           data-id="<?= $tarifa['id_tarifa'] ?>" 
                                           data-nombre="<?= htmlspecialchars($tarifa['nombre_tarifa'], ENT_QUOTES) ?>" 
                                           data-precio="<?= $tarifa['precio_actual'] ?>" 
                                           value="0" readonly>
                                           
                                    <button class="btn btn-light border btn-sumar fw-bold fs-4 px-3" type="button" data-id="<?= $tarifa['id_tarifa'] ?>">+</button>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No hay tarifas activas en el sistema.</div>
            <?php endif; ?>
        </section>

        <section class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px; border-radius: 15px;">
                <div class="card-body p-4 p-lg-5">
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="border-bottom border-2">
                                    <th class="ps-0 text-dark fw-bold fs-5">Nombre</th>
                                    <th class="text-center text-dark fw-bold fs-5">Cantidad</th>
                                    <th class="text-end pe-0 text-dark fw-bold fs-5">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="lista-carrito">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4 fs-5">Selecciona boletos para comenzar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr class="border-secondary opacity-25 mb-4">
                    
                    <div class="text-center mb-5">
                        <p class="text-muted mb-1 fs-4">Total a cobrar</p>
                        <h1 class="display-3 fw-bolder text-dark mb-0" id="total-cobrar">$0.00</h1>
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
<script src="frontend/js/venta_boletos.js"></script>