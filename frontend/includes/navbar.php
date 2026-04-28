<header class="bg-white shadow-sm py-3 mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center px-4">
            
            <div class="d-flex align-items-center">
                <a class="mb-0 fw-bold ms-2 text-dark text-decoration-none" href="inicio.php">BALNEARIO</a>
                <span class="mx-3 text-muted">|</span>
                <span class="fs-5 text-secondary">
                    <?= htmlspecialchars($titulo_pagina ?? 'Panel Principal') ?>
                </span>
            </div>

            <div class="d-flex align-items-center">
                
                <?php 
                $rol_usuario_actual = $_SESSION['rol'] ?? '';
                if($rol_usuario_actual === 'Administrador' || $rol_usuario_actual === 'Dueño'): 
                ?>
                    <a href="gestion_usuarios.php" class="btn btn-primary btn-sm me-3">Gestión de Usuarios</a>
                <?php endif; ?>

                <span class="fw-bold fs-6 d-none d-sm-block text-capitalize me-3 text-dark">
                    <?= htmlspecialchars($_SESSION['rol'] ?? 'Usuario') ?>: 
                    <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                </span>
                
                <div class="dropdown">
                    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-secondary d-inline-block shadow-sm" style="width: 40px; height: 40px;"></div>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="dropdownUser">
                        <li><a class="dropdown-item py-2" href="inicio.php"><i class="bi bi-grid text-muted me-2"></i>Menú Principal</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger fw-bold" href="backend/cerrar_sesion.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </header>