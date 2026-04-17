// Variable global para guardar todos los datos y poder filtrarlos después
let datosPrecios = [];

// Cuando la página termine de cargar, ejecutamos las funciones principales
document.addEventListener('DOMContentLoaded', () => {
    cargarPreciosDesdeBD();
    configurarBotonesFiltro(); // ¡NUEVO! Inicializamos los filtros
    configurarBuscador(); // Inicializamos la barra de busqueda
    configurarGuardado(); //Inicializar botón de guardar
});

// 1. Petición al Backend (Fetch)
function cargarPreciosDesdeBD() {
    // Le agregamos la hora actual al enlace para engañar a la caché del navegador
    const urlSinCache = 'obtener_precios.php?t=' + new Date().getTime();

    fetch(urlSinCache, { cache: 'no-store' }) // <--- ESTA LÍNEA ES LA CLAVE
        .then(respuesta => respuesta.json())
        .then(resultado => {
            if (resultado.status === 'success') {
                datosPrecios = resultado.data;

                // Si teníamos una categoría seleccionada en el menú lateral, respetamos ese filtro
                const botonActivo = document.querySelector('.custom-sidebar .nav-link.active');
                if (botonActivo && botonActivo.textContent.trim() !== 'Todos') {
                    const categoria = botonActivo.textContent.trim();
                    const filtrados = datosPrecios.filter(item => item.categoria === categoria);
                    renderizarTabla(filtrados);
                } else {
                    renderizarTabla(datosPrecios);
                }
            } else {
                console.error("Error del servidor:", resultado.message);
                alert("Hubo un problema al cargar los precios.");
            }
        })
        .catch(error => console.error('Error de red:', error));
}

// 2. Lógica para los botones de la izquierda (Sidebar)
function configurarBotonesFiltro() {
    // Seleccionamos todos los botones del menú lateral
    const botones = document.querySelectorAll('.custom-sidebar .nav-link');

    botones.forEach(boton => {
        boton.addEventListener('click', (evento) => {
            // A) Efecto visual: Quitar el color oscuro (active) de todos y ponérselo al que clickeamos
            botones.forEach(b => b.classList.remove('active'));
            const botonClickeado = evento.currentTarget;
            botonClickeado.classList.add('active');

            // B) Obtener el texto del botón (ej. "Alimentos", "Entradas", "Todos")
            // Usamos textContent y quitamos espacios en blanco de los lados
            const categoriaSeleccionada = botonClickeado.textContent.trim();

            // C) Filtrar los datos
            if (categoriaSeleccionada === 'Todos') {
                // Si es "Todos", pasamos la lista original completa
                renderizarTabla(datosPrecios);
            } else {
                // Si es otra, creamos una nueva lista solo con los que coincidan
                const datosFiltrados = datosPrecios.filter(item => item.categoria === categoriaSeleccionada);
                renderizarTabla(datosFiltrados);
            }
        });
    });
}

// 3. Lógica de la barra de busquedas
function configurarBuscador() {
    const inputBusqueda = document.getElementById('inputBusqueda');

    // El evento 'input' se dispara cada vez que tecleas o borras una letra
    inputBusqueda.addEventListener('input', (evento) => {
        // Convertimos lo que escribe el usuario a minúsculas y le quitamos espacios extra
        const termino = evento.target.value.toLowerCase().trim();

        // Si el buscador está vacío, mostramos todos los datos de nuevo
        if (termino === '') {
            renderizarTabla(datosPrecios);

            // Regresamos la selección visual al botón de "Todos"
            document.querySelectorAll('.custom-sidebar .nav-link').forEach(b => b.classList.remove('active'));
            document.querySelector('.custom-sidebar .nav-link').classList.add('active');
            return;
        }

        // Filtramos buscando coincidencias en el Nombre o en el ID
        const resultadosBusqueda = datosPrecios.filter(item => {
            const coincideNombre = item.nombre.toLowerCase().includes(termino);
            const coincideID = item.id.toString().includes(termino);

            return coincideNombre || coincideID;
        });

        // Dibujamos la tabla solo con los que coincidieron
        renderizarTabla(resultadosBusqueda);
    });
}


// 4. Dibujar la tabla dinámicamente
function renderizarTabla(datos) {
    const tbody = document.getElementById('tablaPreciosBody');
    tbody.innerHTML = ''; // Limpiar la tabla antes de dibujar los nuevos resultados

    if (datos.length === 0) {
        const inputBusqueda = document.getElementById('inputBusqueda');
        const terminoBusqueda = inputBusqueda ? inputBusqueda.value.trim() : '';
        
        let mensajeVacio = "No hay elementos en esta categoría.";
        if(terminoBusqueda !== '') {
            mensajeVacio = `No se encontraron resultados para "${terminoBusqueda}".`;
        }
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">${mensajeVacio}</td></tr>`;
        return;
    }

    datos.forEach(item => {
        const precioFormateado = new Intl.NumberFormat('es-MX', {
            style: 'currency', currency: 'MXN'
        }).format(item.precio);

        const fechaHoy = new Date().toISOString().split('T')[0];

        // Protección extra por si alguna categoría viene vacía
        const categoriaSegura = item.categoria ? item.categoria.toString() : 'OTR';
        const prefijo = categoriaSegura.substring(0, 3).toUpperCase();
        const idVisual = `${prefijo}-${item.id}`;

        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td class="text-muted fw-bold">${idVisual}</td>
            <td><span class="badge bg-secondary opacity-75">${categoriaSegura}</span></td>
            <td class="fw-medium">${item.nombre}</td>
            <td class="fw-bold text-success">${precioFormateado}</td>
            <td class="text-muted small">${fechaHoy}</td>
            <td class="text-center">
                <button class="btn btn-sm text-primary" title="Editar" onclick="abrirEdicion(${item.id}, '${categoriaSegura}')"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-sm text-danger" title="Eliminar" onclick="eliminarPrecio(${item.id}, '${categoriaSegura}')"><i class="bi bi-trash3-fill"></i></button>
            </td>
        `;
        tbody.appendChild(fila);
    });
}


// 5. Lógica para guardar un nuevo precio o editar uno existente (Fetch POST)
function configurarGuardado() {
    const btnGuardar = document.getElementById('btnGuardarPrecio');
    const mensajeError = document.getElementById('mensajeModalError');

    btnGuardar.addEventListener('click', () => {
        // A) Obtener los valores de los inputs
        const idEdicion = document.getElementById('idItemEdicion').value; // <-- AQUÍ CAPTURAMOS EL ID OCULTO
        const categoria = document.getElementById('categoriaItem').value;
        const nombre = document.getElementById('nombreItem').value.trim();
        const precio = document.getElementById('precioItem').value;

        // Ocultar errores previos
        mensajeError.classList.add('d-none');

        // B) Validar que no envíen cosas vacías o en ceros
        if (categoria === '' || nombre === '' || precio === '') {
            mensajeError.textContent = 'Por favor, completa todos los campos.';
            mensajeError.classList.remove('d-none');
            return;
        }

        if (precio <= 0) {
            mensajeError.textContent = 'El precio debe ser mayor a $0.00.';
            mensajeError.classList.remove('d-none');
            return;
        }

        // C) Cambiar el botón para que el usuario sepa que está cargando
        const textoOriginal = btnGuardar.textContent;
        btnGuardar.textContent = 'GUARDANDO...';
        btnGuardar.disabled = true;

        // D) Enviar los datos al PHP
        fetch('guardar_precio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: idEdicion,       // <-- AQUÍ SE LO ENVIAMOS A PHP
                categoria: categoria,
                nombre: nombre,
                precio: parseFloat(precio)
            })
        })
            .then(respuesta => respuesta.json())
            .then(resultado => {
                if (resultado.status === 'success') {
                    // Limpiar el formulario y el ID
                    document.getElementById('idItemEdicion').value = '';
                    document.getElementById('categoriaItem').value = '';
                    document.getElementById('nombreItem').value = '';
                    document.getElementById('precioItem').value = '';

                    // Cerrar el modal usando la lógica de Bootstrap
                    const modal = bootstrap.Modal.getInstance(document.getElementById('precioModal'));
                    modal.hide();

                    // ¡Recargar la tabla para ver los cambios al instante!
                    cargarPreciosDesdeBD();
                } else {
                    // Mostrar error del servidor en el modal
                    mensajeError.textContent = resultado.message;
                    mensajeError.classList.remove('d-none');
                }
            })
            .catch(error => {
                mensajeError.textContent = 'Error de conexión con el servidor.';
                mensajeError.classList.remove('d-none');
            })
            .finally(() => {
                // Devolver el botón a la normalidad
                btnGuardar.textContent = textoOriginal;
                btnGuardar.disabled = false;
            });
    });
}


// Abrir modal para Editar
function abrirEdicion(id, categoria) {
    // El "chismoso": Imprimirá en la consola qué botón tocaste
    console.log("Clic en Editar -> ID:", id, "| Categoría:", categoria);

    // Usamos == (doble igual) en vez de === para evitar problemas de Texto vs Número
    const item = datosPrecios.find(p => p.id == id && p.categoria === categoria);
    
    if (item) {
        console.log("¡Ítem encontrado en la memoria!", item); // Confirmación
        
        // Llenamos el formulario oculto
        document.getElementById('idItemEdicion').value = item.id;
        document.getElementById('categoriaItem').value = item.categoria;
        document.getElementById('nombreItem').value = item.nombre;
        document.getElementById('precioItem').value = item.precio;
        document.getElementById('precioModalLabel').textContent = 'EDITAR PRECIO';
        
        // Abrimos el modal
        const modalElement = document.getElementById('precioModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modalInstance.show();
    } else {
        console.error("Error: No se encontró el ítem en la lista de datosPrecios.");
        alert("Hubo un problema al intentar editar este ítem. Revisa la consola (F12).");
    }
}


// Limpiar modal si presionamos "Nuevo Precio" (Añade id="btnNuevoPrecio" a este botón en tu HTML)
document.querySelector('[data-bs-target="#precioModal"]').addEventListener('click', () => {
    document.getElementById('idItemEdicion').value = '';
    document.getElementById('precioModalLabel').textContent = 'NUEVO PRECIO';
    document.getElementById('categoriaItem').value = '';
    document.getElementById('nombreItem').value = '';
    document.getElementById('precioItem').value = '';
});

// Eliminar
function eliminarPrecio(id, categoria) {
    if (confirm(`¿Estás seguro de eliminar este ítem?`)) {
        fetch('eliminar_precio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, categoria: categoria })
        })
            .then(res => res.json())
            .then(resultado => {
                if (resultado.status === 'success') {
                    cargarPreciosDesdeBD(); // Recargar tabla
                } else {
                    alert(resultado.message);
                }
            });
    }
}