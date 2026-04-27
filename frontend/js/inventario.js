document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Buscador
    const inputBusquedaInv = document.getElementById('inputBusquedaInv');
    if(inputBusquedaInv) {
        inputBusquedaInv.addEventListener('keyup', function() {
            const texto = this.value.toLowerCase();
            document.querySelectorAll('.fila-producto').forEach(fila => {
                const contenido = fila.textContent.toLowerCase();
                fila.style.display = contenido.includes(texto) ? '' : 'none';
            });
        });
    }

    // 2. Procesar ENTRADA DE MERCANCÍA
    const formEntrada = document.getElementById('formEntradaMercancia');
    if(formEntrada) {
        formEntrada.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const datosFormulario = new FormData(this);
            
            fetch('backend/registrar_entrada.php', {
                method: 'POST',
                body: datosFormulario
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    // Actualizar el stock visualmente
                    const idProd = document.getElementById('entrada_id_producto').value;
                    const badgeStock = document.getElementById('stock-val-' + idProd);
                    
                    if(badgeStock) {
                        badgeStock.textContent = resultado.nuevo_stock;
                        badgeStock.classList.remove('bg-danger');
                        badgeStock.classList.add('bg-success');
                    }

                    bootstrap.Modal.getInstance(document.getElementById('modalEntrada')).hide();
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', 
                        title: 'Entrada registrada', showConfirmButton: false, timer: 2000
                    });
                    formEntrada.reset(); 
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(err => console.error("Error:", err));
        });
    }

    // ==========================================
    // NUEVO: ABRIR MODAL DE ENTRADA SIN ONCLICK
    // ==========================================
    document.querySelectorAll('.btn-abrir-entrada').forEach(boton => {
        boton.addEventListener('click', function() {
            const idProducto = this.getAttribute('data-id');
            const nombreProducto = this.getAttribute('data-nombre');
            
            // Llenamos el modal
            document.getElementById('formEntradaMercancia').reset();
            document.getElementById('entrada_id_producto').value = idProducto;
            document.getElementById('entrada_nombre_producto').value = nombreProducto;
            
            // Mostramos el modal
            new bootstrap.Modal(document.getElementById('modalEntrada')).show();
        });
    });

    // 3. Procesar NUEVO PRODUCTO
    const formNuevoProducto = document.getElementById('formNuevoProducto');
    if(formNuevoProducto) {
        formNuevoProducto.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const datosFormulario = new FormData(this);
            
            fetch('backend/crear_producto.php', {
                method: 'POST',
                body: datosFormulario
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('modalNuevoProducto')).hide();
                    Swal.fire({
                        icon: 'success', title: '¡Producto Creado!', 
                        text: 'El producto se agregó al catálogo.', showConfirmButton: false, timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(err => console.error("Error:", err));
        });
    }
});

// ==========================================
// FUNCIONES GLOBALES (Debe ir afuera)
// ==========================================
function abrirModalEntrada(idProducto, nombreProducto) {
    document.getElementById('formEntradaMercancia').reset();
    document.getElementById('entrada_id_producto').value = idProducto;
    document.getElementById('entrada_nombre_producto').value = nombreProducto;
    new bootstrap.Modal(document.getElementById('modalEntrada')).show();
}


// AGREGAR PROVEEDOR AL VUELO
function agregarProveedorRapido() {
    Swal.fire({
        title: 'Nuevo Proveedor',
        input: 'text',
        inputLabel: 'Nombre de la Empresa',
        inputPlaceholder: 'Ej. Coca-Cola, Sabritas...',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        target: document.getElementById('modalEntrada'),
        inputValidator: (value) => {
            if (!value) return '¡Necesitas escribir un nombre!'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mandamos el nombre a PHP
            const formData = new FormData();
            formData.append('nombre_empresa', result.value);

            fetch('backend/crear_proveedor.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // Agregamos el nuevo proveedor al Select y lo dejamos seleccionado
                    const select = document.getElementById('selectProveedor');
                    const nuevaOpcion = new Option(data.nombre, data.id, true, true);
                    select.add(nuevaOpcion);
                    
                    Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Proveedor agregado', showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}