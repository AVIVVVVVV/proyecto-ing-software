<?php
require 'backend/verificar_sesion.php';

include 'frontend/includes/header.php';
?>
    
    <main class="container-fluid px-4">
        <div class="row">

            <aside class="col-md-2 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-2">
                        <div class="nav flex-column nav-pills custom-sidebar" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <button class="nav-link active text-start mb-1" data-bs-toggle="pill" type="button"><i
                                    class="bi bi-collection me-2"></i> Todos</button>
                            <button class="nav-link text-start mb-1" data-bs-toggle="pill" type="button"><i
                                    class="bi bi-ticket-perforated me-2"></i> Entradas</button>
                            <button class="nav-link text-start mb-1" data-bs-toggle="pill" type="button"><i
                                    class="bi bi-cup-hot me-2"></i> Alimentos</button>
                            <button class="nav-link text-start mb-1" data-bs-toggle="pill" type="button"><i
                                    class="bi bi-cup-straw me-2"></i> Bebidas</button>
                            <button class="nav-link text-start" data-bs-toggle="pill" type="button"><i
                                    class="bi bi-tools me-2"></i> Servicios</button>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="col-md-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="input-group w-50">
                                <span class="input-group-text bg-white custom-input border-end-0"><i
                                        class="bi bi-search"></i></span>
                                <input type="text" id="inputBusqueda"
                                    class="form-control custom-input border-start-0 ps-0"
                                    placeholder="Buscar por nombre o ID...">
                            </div>
                            <button class="btn custom-btn-primary" data-bs-toggle="modal" data-bs-target="#precioModal">
                                AGREGAR NUEVO PRECIO
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Categoría</th>
                                        <th>Nombre del Ítem</th>
                                        <th>Precio Actual ($)</th>
                                        <th>Última Actualización</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPreciosBody">
                                    <tr>
                                        <td>1</td>
                                        <td>Entradas</td>
                                        <td>Entrada General Adulto</td>
                                        <td>$250.00</td>
                                        <td>2024-05-15</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm text-primary"><i
                                                    class="bi bi-pencil-fill"></i></button>
                                            <button class="btn btn-sm text-danger"><i
                                                    class="bi bi-trash3-fill"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Entradas</td>
                                        <td>Entrada Infantil</td>
                                        <td>$180.00</td>
                                        <td>2024-05-15</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm text-primary"><i
                                                    class="bi bi-pencil-fill"></i></button>
                                            <button class="btn btn-sm text-danger"><i
                                                    class="bi bi-trash3-fill"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="precioModal" tabindex="-1" aria-labelledby="precioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="precioModalLabel">NUEVO PRECIO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-4">
                    <div class="mb-3">
                        <input type="hidden" id="idItemEdicion" value="">
                        <label class="form-label custom-label">Categoría</label>
                        <select id="categoriaItem" class="form-select custom-input">
                            <option value="" selected>Selecciona una categoría</option>
                            <option value="Entradas">Entradas</option>
                            <option value="Alimentos">Alimentos</option>
                            <option value="Bebidas">Bebidas</option>
                            <option value="Servicios">Servicios</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label custom-label">Nombre del Ítem</label>
                        <input type="text" id="nombreItem" class="form-control custom-input"
                            placeholder="Ej. Entrada General">
                    </div>
                    <div class="mb-4">
                        <label class="form-label custom-label">Precio Actual ($)</label>
                        <input type="number" id="precioItem" class="form-control custom-input" placeholder="0.00"
                            step="0.01" min="0">
                    </div>

                    <div id="mensajeModalError" class="alert alert-danger d-none py-2 small" role="alert"></div>

                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" id="btnGuardarPrecio" class="btn custom-btn-primary w-50">GUARDAR
                            CAMBIOS</button>
                        <button type="button" class="btn btn-light custom-input w-50"
                            data-bs-dismiss="modal">CANCELAR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/precios.js"></script>
</body>

</html>