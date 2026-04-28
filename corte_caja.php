<?php
// 1. SEGURIDAD Y CABECERA
require 'backend/verificar_sesion.php';
$titulo_pagina = "Corte de Caja Diario";
include 'frontend/includes/header.php';
?>

<main class="container px-4 mt-5">
    
    <div class="d-flex justify-content-center mb-4">
        <div class="input-group w-auto shadow-sm">
            <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
            <input type="date" id="input-fecha-corte" class="form-control bg-white text-center fw-bold" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-primary fw-bold px-4" id="btn-generar-corte">GENERAR CORTE</button>
        </div>
    </div>

    <div class="row justify-content-center" id="tarjeta-corte" style="display: none;">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg" style="border-radius: 20px;">
                <div class="card-body p-5 text-center">
                    <h3 class="fw-bold mb-4 text-dark">Corte de Caja Diario</h3>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted mb-1">Ingresos por Entradas</p>
                            <h4 class="fw-bold text-primary" id="txt-entradas">$0.00</h4>
                        </div>
                        <div class="col-6 border-start">
                            <p class="text-muted mb-1">Ingresos por Ventas</p>
                            <h4 class="fw-bold text-success" id="txt-ventas">$0.00</h4>
                        </div>
                    </div>

                    <p class="text-muted mb-1 fs-5">Gran Total Sistema</p>
                    <h1 class="display-4 fw-bolder text-dark mb-4" id="txt-gran-total">$0.00</h1>

                    <hr class="mb-4">

                    <div class="mb-3 w-75 mx-auto">
                        <label class="form-label fw-bold text-muted">Efectivo Físico en Caja</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-light">$</span>
                            <input type="number" id="input-efectivo" class="form-control text-center fw-bold fs-4" step="0.50" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3" id="caja-diferencia" style="display: none;">
                        <span class="badge fs-6 rounded-pill px-3 py-2" id="badge-diferencia">Diferencia: $0.00</span>
                    </div>

                    <div class="mb-4 w-75 mx-auto" id="caja-justificacion" style="display: none;">
                        <label class="form-label fw-bold text-danger small"><i class="bi bi-exclamation-triangle-fill me-1"></i>Justificación por Faltante</label>
                        <textarea id="input-justificacion" class="form-control" rows="2" placeholder="Ej. Pago a proveedor de agua, error al dar cambio..."></textarea>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button class="btn btn-dark fw-bold px-4" id="btn-guardar-corte" disabled>GUARDAR CORTE</button>
                        <button class="btn btn-outline-secondary fw-bold px-4" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'frontend/includes/footer.php'; ?>
<script src="frontend/js/corte_caja.js?v=1"></script>