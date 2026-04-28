document.addEventListener('DOMContentLoaded', () => {

    const carrito = {}; 
    const listaCarrito = document.getElementById('lista-carrito');
    const totalCobrarEl = document.getElementById('total-cobrar');

   
    // BUSCADOR Y FILTROS DE CATEGORÍA
    const buscador = document.getElementById('buscador-productos');
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    const itemsProducto = document.querySelectorAll('.item-producto');

    function filtrarProductos() {
        const textoBuscado = buscador.value.toLowerCase();
        const categoriaActiva = document.querySelector('.btn-filtro.active').getAttribute('data-categoria');

        itemsProducto.forEach(item => {
            const nombre = item.getAttribute('data-nombre');
            const categoria = item.getAttribute('data-categoria');

            const coincideTexto = nombre.includes(textoBuscado);
            const coincideCategoria = categoriaActiva === 'todos' || categoria === categoriaActiva;

            if (coincideTexto && coincideCategoria) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    buscador.addEventListener('input', filtrarProductos);

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Quitar clase activa de todos
            botonesFiltro.forEach(b => {
                b.classList.remove('active', 'btn-dark');
                b.classList.add('btn-outline-secondary');
            });

            // Poner clase activa al que se le dio clic
            e.target.classList.remove('btn-outline-secondary');
            e.target.classList.add('active', 'btn-dark');

            filtrarProductos();
        });
    });


    // 3. CONTROLES DEL CARRITO (+ y -)
    document.querySelectorAll('.btn-sumar, .btn-restar').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.target.getAttribute('data-id');
            const input = document.getElementById('cant-' + id);
            
            const nombre = input.getAttribute('data-nombre');
            const precio = parseFloat(input.getAttribute('data-precio'));
            const stockMaximo = parseInt(input.getAttribute('data-stock')); // ¡Nuevo límite!
            let cantidad = parseInt(input.value);

            if (e.target.classList.contains('btn-sumar')) {
                if (cantidad < stockMaximo) {
                    cantidad++;
                } else {
                    // Si intenta agregar más de lo que hay, lanzamos advertencia rápida
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'warning', 
                        title: 'Stock insuficiente', showConfirmButton: false, timer: 1500
                    });
                    return; // Detenemos la función
                }
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

  
    // 4. CANCELAR Y RENDERIZAR
    document.getElementById('btn-cancelar-venta').addEventListener('click', () => {
        document.querySelectorAll('.input-cantidad').forEach(input => input.value = 0);
        for(let key in carrito) delete carrito[key];
        renderizarCarrito();
    });

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
                    <td class="ps-0 text-dark fs-6">${item.nombre}</td>
                    <td class="text-center text-dark fw-bold fs-6">${item.cantidad}</td>
                    <td class="text-end pe-0 text-dark fw-bold fs-6">$${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                </tr>
            `;
        }

        if (items === 0) {
            listaCarrito.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4">Selecciona productos para comenzar</td></tr>`;
        }

        totalCobrarEl.textContent = `$${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }



   
    // 5. PROCESAR VENTA AL BACKEND
    document.getElementById('btn-confirmar-venta').addEventListener('click', () => {
        const itemsVenta = [];
        for (let id in carrito) {
            itemsVenta.push({
                id_producto: id,
                cantidad: carrito[id].cantidad
            });
        }

        if (itemsVenta.length === 0) {
            Swal.fire('Carrito vacío', 'Debes agregar al menos un producto.', 'warning');
            return;
        }

        const btnConfirmar = document.getElementById('btn-confirmar-venta');
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

        fetch('backend/procesar_venta_productos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productos: itemsVenta })
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
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Recargar la página para que se actualice el stock visualmente
                    window.location.reload(); 
                });
            } else {
                Swal.fire('Error', resultado.message, 'error');
            }
        })
        .catch(err => {
            console.error("Error:", err);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = 'CONFIRMAR VENTA';
            Swal.fire('Error', 'Problema de conexión. Revisa F12.', 'error');
        });
    });
});