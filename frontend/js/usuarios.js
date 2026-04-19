// frontend/js/usuarios.js

// ==========================================
// 1. ELIMINAR (Desactivar) USUARIO
// ==========================================
function eliminarUsuario(idUsuario) {
    // Usamos SweetAlert2 en lugar del feo confirm()
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
        // Si el usuario le dio clic a "Sí"
        if (result.isConfirmed) {
            
            fetch('backend/eliminar_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idUsuario })
            })
            .then(res => res.json())
            .then(resultado => {
                if(resultado.status === 'success') {
                    
                    // ¡MAGIA DOM! En lugar de recargar, buscamos el switch de esa fila y lo apagamos
                    const fila = document.getElementById('fila-usuario-' + idUsuario);
                    const interruptor = fila.querySelector('.form-check-input');
                    if(interruptor) {
                        interruptor.checked = false; // Apagamos el switch visualmente
                    }

                    // Alerta de éxito bonita
                    Swal.fire('¡Desactivado!', 'El usuario ha sido desactivado exitosamente.', 'success');
                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            })
            .catch(error => console.error("Error:", error));
        }
    })
}

// ==========================================
// 2. EDITAR ROL DE USUARIO
// ==========================================

// A) Abrir el modal
function abrirModalEditar(id, nombreUsuario, nombreRolActual) {
    document.getElementById('edit_id_usuario').value = id;
    document.getElementById('edit_nombre_usuario').value = nombreUsuario;
    
    const selectRol = document.getElementById('edit_rol');
    for(let i=0; i < selectRol.options.length; i++) {
        if(selectRol.options[i].text === nombreRolActual) {
            selectRol.selectedIndex = i;
            break;
        }
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
}

// B) Guardar cambios sin recargar
const formEditar = document.getElementById('formEditarUsuario');

if(formEditar) { // Solo si estamos en la página que tiene este formulario
    formEditar.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const datosFormulario = new FormData(this);
        const idUsuario = document.getElementById('edit_id_usuario').value;
        const selectRol = document.getElementById('edit_rol');
        const textoNuevoRol = selectRol.options[selectRol.selectedIndex].text; // Ej: "Taquillero"
        
        fetch('backend/editar_usuario.php', {
            method: 'POST',
            body: datosFormulario
        })
        .then(res => res.json())
        .then(resultado => {
            if(resultado.status === 'success') {
                
                // ¡MAGIA DOM! Ocultamos el modal de Bootstrap
                const modalElement = document.getElementById('modalEditarUsuario');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                modalInstance.hide();

                // Actualizamos la etiqueta de rol en la tabla visualmente
                actualizarEtiquetaRol(idUsuario, textoNuevoRol);

                // Notificación Toast discreta (esquina superior derecha)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Rol actualizado',
                    showConfirmButton: false,
                    timer: 2000
                });

            } else {
                Swal.fire('Error', resultado.message, 'error');
            }
        })
        .catch(err => console.error("Error:", err));
    });
}

// C) Función auxiliar para cambiar el color de la etiqueta sin recargar
function actualizarEtiquetaRol(idUsuario, nuevoRolTexto) {
    const fila = document.getElementById('fila-usuario-' + idUsuario);
    // Buscamos la etiqueta (badge) dentro de esa fila
    const badge = fila.querySelector('td:nth-child(4) .badge'); 
    
    if(badge) {
        // Le ponemos el nuevo texto
        badge.textContent = nuevoRolTexto;
        
        // Limpiamos los colores viejos
        badge.className = 'badge rounded-pill px-3 py-2 text-white'; 
        
        // Le aplicamos el color nuevo según tu lógica
        if(nuevoRolTexto === 'Administrador' || nuevoRolTexto === 'Dueño') {
            badge.classList.add('bg-primary');
        } else if(nuevoRolTexto === 'Taquillero') {
            badge.classList.add('bg-success');
        } else if(nuevoRolTexto === 'Vendedor') {
            badge.classList.add('bg-info', 'text-dark');
            badge.classList.remove('text-white');
        } else {
            badge.classList.add('bg-secondary');
        }
    }
}

// ==========================================
// 2. EDITAR USUARIO (Perfil Completo)
// ==========================================

// A) Abrir el modal
function abrirModalEditar(id, nombreUsuario, nombreRolActual, estadoActual) {
    document.getElementById('edit_id_usuario').value = id;
    document.getElementById('edit_nombre_usuario').value = nombreUsuario;
    
    // Encendemos o apagamos el switch del modal dependiendo del estado
    document.getElementById('edit_estado').checked = (estadoActual == 1);
    
    const selectRol = document.getElementById('edit_rol');
    for(let i=0; i < selectRol.options.length; i++) {
        if(selectRol.options[i].text === nombreRolActual) {
            selectRol.selectedIndex = i;
            break;
        }
    }
    
    var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
}

// B) Guardar cambios (Blindado para que siempre escuche el clic)
document.addEventListener('DOMContentLoaded', function() {
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
                    // Si todo salió bien, cerramos el modal y recargamos 
                    // (Recargamos la página completa esta vez para que se reflejen todos los cambios: nombre, color de rol y switch de la tabla principal)
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Los cambios se guardaron correctamente.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); 
                    });

                } else {
                    Swal.fire('Error', resultado.message, 'error');
                }
            })
            .catch(err => console.error("Error:", err));
        });
    }
});