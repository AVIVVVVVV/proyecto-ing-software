    <header class="bg-white shadow-sm py-3 mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center px-4">
            <div class="d-flex align-items-center">
                <a class="mb-0 fw-bold ms-2 text-dark text-decoration-none" href="inicio.php">BALNEARIO</a>
                <span class="mx-3 text-muted">|</span>
                <?= $titulo_pagina ?? 'Panel de Control' ?>
            </div>
            <div class="d-flex align-items-center">
                <span class="fw-bold fs-6 d-none d-sm-block text-capitalize me-3">
                    <?= $rol_usuario_actual ?>: <?= $nombre_usuario_actual ?>
                 </span>
                 <?php if($rol_usuario_actual == 'Administrador' || $rol_usuario_actual == 'Dueño'): ?>
                    <a href="gestion_usuarios.php" class="btn btn-primary btn-sm me-3">Gestión de Usuarios</a>
                <?php endif; ?>
                 <div class="rounded-circle bg-secondary" style="width: 40px; height: 40px;"></div>
            </div>
        </div>
    </header>