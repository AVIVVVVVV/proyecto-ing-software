<?php
// 1. SEGURIDAD
require 'backend/verificar_sesion.php';

// Atrapamos el rol del usuario
$rol_usuario = $_SESSION['rol'] ?? 'Invitado'; 
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';

// 2. CABECERA
$titulo_pagina = "Menú Principal";
include 'frontend/includes/header.php';
?>

<style>
    /* Efecto moderno para las tarjetas al pasar el mouse */
    .modulo-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        text-decoration: none;
        color: inherit;
        border: 2px solid transparent;
    }
    .modulo-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        border-color: #0d6efd; /* Azul de Bootstrap */
    }
    .icon-container {
        width: 65px;
        height: 65px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
</style>

<main class="container px-4 mt-5">
    
    <div class="mb-5 text-center">
        <h2 class="fw-bolder text-dark display-6">¡Hola, <?= htmlspecialchars($nombre_usuario) ?>!</h2>
        <p class="text-muted fs-5">Selecciona el módulo con el que deseas trabajar hoy.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño', 'Taquillero'])): ?>
        <div class="col">
            <a href="venta_boletos.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Taquilla</h4>
                    <p class="text-muted mb-0 small">Venta rápida de boletos y promociones de entrada.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño', 'Vendedor'])): ?>
        <div class="col">
            <a href="venta_productos.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-success bg-opacity-10 text-success mx-auto mb-3">
                        <i class="bi bi-shop"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Tienda</h4>
                    <p class="text-muted mb-0 small">Venta de alimentos, bebidas y artículos del balneario.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño', 'Vendedor'])): ?>
        <div class="col">
            <a href="inventario.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Inventario</h4>
                    <p class="text-muted mb-0 small">Gestión de stock, entradas de mercancía y proveedores.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño', 'Taquillero', 'Vendedor'])): ?>
        <div class="col">
            <a href="corte_caja.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-info bg-opacity-10 text-info mx-auto mb-3">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Corte de Caja</h4>
                    <p class="text-muted mb-0 small">Cierre de turno, conteo de efectivo y justificaciones.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño'])): ?>
        <div class="col">
            <a href="precios.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Precios</h4>
                    <p class="text-muted mb-0 small">Administración de catálogos y tarifas del sistema.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if(in_array($rol_usuario, ['Administrador', 'Dueño'])): ?>
        <div class="col">
            <a href="gestion_usuarios.php" class="card bg-white shadow-sm h-100 modulo-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-container bg-dark bg-opacity-10 text-dark mx-auto mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Usuarios</h4>
                    <p class="text-muted mb-0 small">Control de empleados, contraseñas y permisos.</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'frontend/includes/footer.php'; ?>