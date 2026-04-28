document.addEventListener('DOMContentLoaded', () => {
    //VARIABLES GLOBALES DEL CARRITO
    const carrito = {}; 
    const listaCarrito = document.getElementById('lista-carrito');
    const totalCobrarEl = document.getElementById('total-cobrar');

    // CONTROLES DE + Y -
    document.querySelectorAll('.btn-sumar, .btn-restar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.target.getAttribute('data-id');
            const input = document.getElementById('cant-' + id);
            
            const nombre = input.getAttribute('data-nombre');
            const precio = parseFloat(input.getAttribute('data-precio'));
            let cantidad = parseInt(input.value);

            if (e.target.classList.contains('btn-sumar')) {
                cantidad++;
            } else {
                if (cantidad > 0) cantidad--;
            }

            input.value = cantidad;
            
            if (cantidad > 0) {
                carrito[id] = { nombre, precio, cantidad };
            } else {
                delete carrito[id];
            }
            
            renderizarCarrito();
        });
    });

   // BOTÓN CANCELAR VENTA
    document.getElementById('btn-cancelar-venta').addEventListener('click', () => {
        document.querySelectorAll('.input-cantidad').forEach(input => input.value = 0);
        for(let key in carrito) delete carrito[key];
        renderizarCarrito();
    });

    // FUNCIÓN PARA DIBUJAR LA TABLA
    function renderizarCarrito() {
        listaCarrito.innerHTML = '';
        let total = 0;
        let items = 0;

        for (let id in carrito) {
            const item = carrito[id];
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            items++;

            listaCarrito.innerHTML += `
                <tr>
                    <td class="ps-0 text-dark fs-5">${item.nombre}</td>
                    <td class="text-center text-dark fw-bold fs-5">${item.cantidad}</td>
                    <td class="text-end pe-0 text-dark fw-bold fs-5">$${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        }

        if (items === 0) {
            listaCarrito.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4 fs-5">Selecciona boletos para comenzar</td></tr>`;
        }

        totalCobrarEl.textContent = `$${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }

    // BOTÓN CONFIRMAR VENTA 
    document.getElementById('btn-confirmar-venta').addEventListener('click', () => {
        const itemsVenta = [];
        for (let id in carrito) {
            itemsVenta.push({
                id_tarifa: id,
                cantidad: carrito[id].cantidad
            });
        }

        if (itemsVenta.length === 0) {
            Swal.fire('Carrito vacío', 'Debes agregar al menos un boleto para cobrar.', 'warning');
            return;
        }

        const btnConfirmar = document.getElementById('btn-confirmar-venta');
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

        fetch('backend/procesar_venta_boletos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ boletos: itemsVenta })
        })
        .then(res => res.json())
        .then(resultado => {
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = 'CONFIRMAR VENTA';

            if (resultado.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Venta Exitosa!',
                    text: `El cobro total fue de $${resultado.total}`,
                    confirmButtonText: 'Nueva Venta'
                }).then(() => {
                    document.getElementById('btn-cancelar-venta').click();
                });
            } else {
                Swal.fire('Error', resultado.message, 'error');
            }
        })
        .catch(err => {
            console.error("Error:", err);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = 'CONFIRMAR VENTA';
            Swal.fire('Error', 'Hubo un problema de conexión. Revisa la consola F12.', 'error');
        });
    });

}); 