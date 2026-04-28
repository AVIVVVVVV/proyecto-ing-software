document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. BUSCADOR EN TIEMPO REAL
    // ==========================================
    const buscador = document.querySelector('input[placeholder="Buscar por nombre, usuario o correo..."]');
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
    // 2. GUARDAR CAMBIOS AL EDITAR
    // ==========================================
    const formEditar = document.getElementById('formEditarUsuario');
    if(formEditar) { 
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            const datosFormulario = new FormData(this);
            
            fetch('backend/editar_usuario.php', {
                method: 'POST',
                body: datosFormulario
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Los cambios se guardaron correctamente.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // Recargamos para ver los cambios visuales
                    });
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            })
            .catch(err => console.error("Error:", err));
        });
    }
});

// ==========================================
// 3. ABRIR MODAL EDITAR (Versión Única)
// ==========================================
function abrirModalEditar(id, nombreUsuario, nombreRolActual, estadoActual) {
    document.getElementById('edit_id_usuario').value = id;
    
    // Validamos el id del input por si lo llamaste distinto en el HTML
    if(document.getElementById('edit_nombre')) document.getElementById('edit_nombre').value = nombreUsuario;
    if(document.getElementById('edit_nombre_usuario')) document.getElementById('edit_nombre_usuario').value = nombreUsuario;
    
    // Estado (Switch)
    const checkboxEstado = document.getElementById('edit_estado');
    if(checkboxEstado) checkboxEstado.checked = (estadoActual == 1 || estadoActual === 'activo');
    
    // Rol
    const selectRol = document.getElementById('edit_rol');
    if(selectRol) {
        for(let i=0; i < selectRol.options.length; i++) {
            if(selectRol.options[i].text === nombreRolActual) {
                selectRol.selectedIndex = i;
                break;
            }
        }
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
}

// ==========================================
// 4. ELIMINAR / DESACTIVAR USUARIO
// ==========================================
function eliminarUsuario(idUsuario) {
    Swal.fire({
        title: '¿Desactivar usuario?',
        text: "El usuario ya no podrá iniciar sesión en el sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // Usamos FormData para que PHP lo reciba correctamente con $_POST
            const formData = new FormData();
            formData.append('id_usuario', idUsuario); 

            fetch('backend/eliminar_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    Swal.fire('¡Desactivado!', 'El usuario ha sido desactivado exitosamente.', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            })
            .catch(error => console.error("Error:", error));
        }
    });
}