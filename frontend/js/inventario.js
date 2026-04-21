// frontend/js/inventario.js
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Buscador en tiempo real
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

    // 2. Procesar el formulario de Entrada de Mercancía
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
                    // Actualizamos el badge de stock visualmente sin recargar
                    const idProd = document.getElementById('entrada_id_producto').value;
                    const badgeStock = document.getElementById('stock-val-' + idProd);
                    
                    if(badgeStock) {
                        badgeStock.textContent = resultado.nuevo_stock;
                        // Si el stock sube y ya no es peligroso, le quitamos lo rojo
                        badgeStock.classList.remove('bg-danger');
                        badgeStock.classList.add('bg-success');
                    }

                    bootstrap.Modal.getInstance(document.getElementById('modalEntrada')).hide();
                    
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', 
                        title: 'Entrada registrada exitosamente', 
                        showConfirmButton: false, timer: 2000
                    });
                    
                    formEntrada.reset(); // Limpiamos el formulario
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(err => console.error("Error:", err));
        });
    }
});

// Función para abrir el modal y llenarlo con los datos del producto
function abrirModalEntrada(idProducto, nombreProducto) {
    document.getElementById('formEntradaMercancia').reset();
    document.getElementById('entrada_id_producto').value = idProducto;
    document.getElementById('entrada_nombre_producto').value = nombreProducto;
    
    new bootstrap.Modal(document.getElementById('modalEntrada')).show();
}


// 3. Procesar el formulario de NUEVO PRODUCTO
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
                        icon: 'success',
                        title: '¡Producto Creado!',
                        text: 'El producto se agregó al catálogo.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        location.reload(); // Recargamos para ver el nuevo producto en la tabla
                    });
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            }).catch(err => console.error("Error:", err));
        });
    }