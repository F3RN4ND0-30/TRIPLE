const selectBusqueda = document.getElementById('criterio-busqueda');
const inputBusqueda = document.getElementById('input-busqueda');
const tbody = document.querySelector('#tabla-clientes tbody');

inputBusqueda.addEventListener('input', handleBusqueda);
selectBusqueda.addEventListener('change', () => {
    inputBusqueda.value = '';
    resetTabla();
});

function handleBusqueda() {
    const criterio = selectBusqueda.value;
    const valor = inputBusqueda.value.trim().toLowerCase();

    // Autodisparo para DNI (8 dígitos) y RUC (11 dígitos)
    if (criterio === 'documento-dni' && valor.length === 8) {
        filtrar('documento', valor);
    } else if (criterio === 'documento-ruc' && valor.length === 11) {
        filtrar('documento', valor);
    } else if (criterio === 'cliente-nombre' && valor.length > 2) {
        filtrar('cliente', valor);
    } else if (criterio === 'cliente-razon' && valor.length > 2) {
        filtrar('cliente', valor);
    } else if (valor === '') {
        resetTabla();
    }
}

function filtrar(tipo, valor) {
    const filas = tbody.querySelectorAll('tr');

    filas.forEach(fila => {
        const columnas = fila.querySelectorAll('td');
        const documento = columnas[0].textContent.toLowerCase();
        const cliente = columnas[1].textContent.toLowerCase();

        let visible = false;

        if (tipo === 'documento' && documento.includes(valor)) {
            visible = true;
        }

        if (tipo === 'cliente' && cliente.includes(valor)) {
            visible = true;
        }

        fila.style.display = visible ? '' : 'none';
    });
}

function resetTabla() {
    const filas = tbody.querySelectorAll('tr');
    filas.forEach(fila => fila.style.display = '');
}
