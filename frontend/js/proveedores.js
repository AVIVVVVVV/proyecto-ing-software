document.addEventListener('DOMContentLoaded', () => {

    // 1. BUSCADOR
    const buscador = document.getElementById('buscador-proveedores');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const textoBuscado = this.value.toLowerCase();
            const filas = document.querySelectorAll('tbody tr');
            
            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(textoBuscado) ? '' : 'none';
            });
        });
    }

    // ==========================================
    // VALIDACIÓN DE TELÉFONO EN TIEMPO REAL
    // ==========================================
    const inputsTelefono = document.querySelectorAll('input[name="contacto_telefono"]');
    
    inputsTelefono.forEach(input => {
        input.addEventListener('input', function() {
            // Esta expresión regular borra cualquier cosa que NO sea:
            // Números (0-9), el signo de más (+), guiones (-), paréntesis () o espacios (\s)
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    });

    // 2. AGREGAR PROVEEDOR
    const formAgregar = document.getElementById('formAgregarProveedor');
    if(formAgregar) {
        formAgregar.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('backend/guardar_proveedor.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Guardado', text: 'Proveedor agregado.', showConfirmButton: false, timer: 1500})
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    }

    // 3. EDITAR PROVEEDOR
    const formEditar = document.getElementById('formEditarProveedor');
    if(formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('backend/editar_proveedor.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Actualizado', text: 'Los datos se guardaron.', showConfirmButton: false, timer: 1500})
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    }
});

// FUNCIÓN PARA ABRIR EL MODAL DE EDICIÓN CON DATOS
function abrirModalEditarProv(id, empresa, nombre, apellido, telefono) {
    document.getElementById('edit_id_proveedor').value = id;
    document.getElementById('edit_empresa').value = empresa;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_apellido').value = apellido;
    document.getElementById('edit_telefono').value = telefono;
    
    new bootstrap.Modal(document.getElementById('modalEditarProveedor')).show();
}

// FUNCIÓN PARA ELIMINAR PROVEEDOR
function eliminarProveedor(id) {
    Swal.fire({
        title: '¿Eliminar proveedor?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_proveedor', id);

            fetch('backend/eliminar_proveedor.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('Eliminado', 'El proveedor ha sido borrado.', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}