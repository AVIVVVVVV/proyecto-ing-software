// frontend/js/precios.js

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // A. FILTROS (SIDEBAR Y BUSCADOR)
    // ==========================================
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const filasArticulos = document.querySelectorAll('.fila-articulo');
    const inputBusqueda = document.getElementById('inputBusqueda');

    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            document.querySelectorAll('.fila-articulo').forEach(fila => {
                if (filtro === 'Todos' || fila.getAttribute('data-categoria') === filtro) {
                    fila.style.display = ''; 
                } else {
                    fila.style.display = 'none'; 
                }
            });
        });
    });

    if(inputBusqueda) {
        inputBusqueda.addEventListener('keyup', function() {
            const texto = this.value.toLowerCase();
            document.querySelectorAll('.fila-articulo').forEach(fila => {
                const contenidoFila = fila.textContent.toLowerCase();
                fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
            });
        });
    }

    // ==========================================
    // B. LIMPIAR MODAL AL AGREGAR NUEVO
    // ==========================================
    const btnAgregar = document.querySelector('button[data-bs-target="#modalEditarPrecio"]');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', () => {
            document.getElementById('formEditarPrecio').reset();
            document.getElementById('edit_id_precio').value = '';
            document.getElementById('edit_id_ui').value = '';
            const inputTipoTabla = document.getElementById('edit_tipo_tabla');
            if(inputTipoTabla) inputTipoTabla.value = '';
            document.querySelector('#modalEditarPrecio .modal-title').textContent = 'Nuevo Precio';
        });
    }

    // ==========================================
    // C. GUARDAR CAMBIOS (SIN RECARGAR PÁGINA)
    // ==========================================
    const formEditarPrecio = document.getElementById('formEditarPrecio');
    if(formEditarPrecio) { 
        formEditarPrecio.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const datosFormulario = new FormData(this);
            
            fetch('backend/guardar_precio.php', {
                method: 'POST',
                body: datosFormulario
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    const data = resultado.data;
                    
                    // 1. Armamos el código HTML de la fila "al vuelo"
                    const rowHTML = `
                        <td class="fw-bold text-muted">${data.id_ui}</td>
                        <td><span class="badge bg-secondary rounded-pill px-3 py-1">${data.categoria}</span></td>
                        <td class="text-dark fw-semibold">${data.nombre}</td>
                        <td class="text-success fw-bold">$${data.precio}</td>
                        <td class="text-end px-4">
                            <a href="#" class="text-primary me-3 text-decoration-none" title="Editar" 
                               onclick="abrirModalEditarPrecio('${data.id_real}', '${data.tipo_tabla}', '${data.id_ui}', '${data.nombre.replace(/'/g, "\\'")}', '${data.categoria}', ${data.precio})">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="#" class="text-danger text-decoration-none" title="Eliminar" 
                               onclick="eliminarPrecio('${data.id_real}', '${data.tipo_tabla}', '${data.id_ui}')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    `;

                    // 2. ¿Editamos o Creamos?
                    if (resultado.accion === 'editar') {
                        // Buscamos la fila vieja usando el ID oculto y le metemos el nuevo HTML
                        const oldIdUi = document.getElementById('edit_id_ui').value;
                        const fila = document.getElementById('fila-precio-' + oldIdUi);
                        if (fila) {
                            fila.id = 'fila-precio-' + data.id_ui; // Por si cambió de categoría
                            fila.setAttribute('data-categoria', data.categoria);
                            fila.innerHTML = rowHTML;
                        }
                    } else {
                        // Es NUEVO: Creamos una etiqueta <tr>, le metemos el HTML, y la agregamos a la tabla
                        const tbody = document.querySelector('.custom-table tbody');
                        
                        // Quitamos el mensaje de "No hay precios" si estaba
                        const rowNoData = tbody.querySelector('td[colspan]');
                        if (rowNoData) rowNoData.parentElement.remove(); 

                        const nuevaFila = document.createElement('tr');
                        nuevaFila.id = 'fila-precio-' + data.id_ui;
                        nuevaFila.className = 'fila-articulo';
                        nuevaFila.setAttribute('data-categoria', data.categoria);
                        nuevaFila.innerHTML = rowHTML;
                        tbody.appendChild(nuevaFila);
                    }

                    // 3. Cerramos Modal y Alerta Toast (Sin recargar location.reload)
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarPrecio')).hide();
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', 
                        title: resultado.accion === 'editar' ? 'Actualizado' : 'Creado', 
                        showConfirmButton: false, timer: 1500
                    });
                    
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(err => console.error("Error:", err));
        });
    }
});

// ==========================================
// D. FUNCIONES GLOBALES 
// ==========================================
function abrirModalEditarPrecio(idReal, tipoTabla, idUi, nombre, categoria, precio) {
    document.getElementById('edit_id_precio').value = idReal;
    document.getElementById('edit_id_ui').value = idUi; // Guardamos el ID visual
    document.getElementById('edit_nombre_precio').value = nombre;
    document.getElementById('edit_valor_precio').value = precio;
    
    let inputTipoTabla = document.getElementById('edit_tipo_tabla');
    if(!inputTipoTabla) {
        inputTipoTabla = document.createElement('input');
        inputTipoTabla.type = 'hidden';
        inputTipoTabla.id = 'edit_tipo_tabla';
        inputTipoTabla.name = 'tipo_tabla';
        document.getElementById('formEditarPrecio').appendChild(inputTipoTabla);
    }
    inputTipoTabla.value = tipoTabla;
    
    const selectCat = document.getElementById('edit_categoria_precio');
    for(let i=0; i < selectCat.options.length; i++) {
        if(selectCat.options[i].value === categoria) {
            selectCat.selectedIndex = i; break;
        }
    }
    
    document.querySelector('#modalEditarPrecio .modal-title').textContent = 'Editar Precio';
    new bootstrap.Modal(document.getElementById('modalEditarPrecio')).show();
}

function eliminarPrecio(idReal, tipoTabla, idUI) {
    Swal.fire({
        title: '¿Eliminar este artículo?',
        text: "No podrás revertir esto.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('backend/eliminar_precio.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_real: idReal, tipo_tabla: tipoTabla })
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    const fila = document.getElementById('fila-precio-' + idUI);
                    if(fila) fila.remove();
                    Swal.fire('¡Eliminado!', 'El registro fue borrado.', 'success');
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(error => console.error("Error:", error));
        }
    })
}