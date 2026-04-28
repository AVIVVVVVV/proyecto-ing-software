document.addEventListener('DOMContentLoaded', () => {
    let granTotalSistema = 0;
    let totalEntradas = 0;
    let totalVentas = 0;

    // 1. OBTENER TOTALES (BOTÓN GENERAR)
    document.getElementById('btn-generar-corte').addEventListener('click', () => {
        
        const fechaElegida = document.getElementById('input-fecha-corte').value;
        fetch(`backend/obtener_totales_corte.php?fecha=${fechaElegida}`)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                totalEntradas = parseFloat(data.entradas);
                totalVentas = parseFloat(data.ventas);
                granTotalSistema = totalEntradas + totalVentas;

                // Mostrar en interfaz
                document.getElementById('txt-entradas').textContent = `$${totalEntradas.toFixed(2)}`;
                document.getElementById('txt-ventas').textContent = `$${totalVentas.toFixed(2)}`;
                document.getElementById('txt-gran-total').textContent = `$${granTotalSistema.toFixed(2)}`;
                
                // Mostrar la tarjeta animada
                document.getElementById('tarjeta-corte').style.display = 'flex';
            }
        });
    });

    // 2. CÁLCULO DE DIFERENCIA Y JUSTIFICACIÓN EN TIEMPO REAL
    const inputEfectivo = document.getElementById('input-efectivo');
    const cajaDiferencia = document.getElementById('caja-diferencia');
    const badgeDiferencia = document.getElementById('badge-diferencia');
    const cajaJustificacion = document.getElementById('caja-justificacion');
    const btnGuardar = document.getElementById('btn-guardar-corte');

    inputEfectivo.addEventListener('input', () => {
        const efectivoFisico = parseFloat(inputEfectivo.value) || 0;
        const diferencia = efectivoFisico - granTotalSistema;

        cajaDiferencia.style.display = 'block';
        badgeDiferencia.textContent = `Diferencia: $${diferencia.toFixed(2)}`;

        // Si falta dinero (negativo)
        if (diferencia < 0) {
            badgeDiferencia.className = 'badge bg-danger fs-6 rounded-pill px-3 py-2';
            cajaJustificacion.style.display = 'block';
            btnGuardar.disabled = true; // Bloqueamos hasta que justifique
        } else {
            // Sobra dinero o está exacto
            badgeDiferencia.className = diferencia === 0 ? 'badge bg-success fs-6 rounded-pill px-3 py-2' : 'badge bg-warning text-dark fs-6 rounded-pill px-3 py-2';
            cajaJustificacion.style.display = 'none';
            btnGuardar.disabled = false;
        }
    });

    // Validar que escriba la justificación para desbloquear el botón
    document.getElementById('input-justificacion').addEventListener('input', (e) => {
        btnGuardar.disabled = e.target.value.trim().length < 5; // Mínimo 5 letras
    });

    // 3. GUARDAR CORTE EN BD
    btnGuardar.addEventListener('click', () => {
        const efectivo = parseFloat(inputEfectivo.value) || 0;
        const justificacion = document.getElementById('input-justificacion').value;

        fetch('backend/guardar_corte.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                entradas: totalEntradas,
                ventas: totalVentas,
                efectivo_fisico: efectivo,
                justificacion: justificacion
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire('Corte Guardado', 'El cierre de caja se realizó correctamente.', 'success')
                .then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });
});