// Mostrar el formulario adecuado
document.querySelectorAll('input[name="tipo_cliente"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const tipo = radio.value;
        document.getElementById('form-persona').classList.remove('activo');
        document.getElementById('form-empresa').classList.remove('activo');

        if (tipo === 'persona') {
            document.getElementById('form-persona').classList.add('activo');
        } else {
            document.getElementById('form-empresa').classList.add('activo');
        }
    });
});

// Simulación de datos de ubicación (deberías reemplazar esto con una API o base de datos real)
const ubigeo = {
    'Lima': {
        'Lima': ['Miraflores', 'San Isidro', 'Surco'],
        'Callao': ['Bellavista', 'La Perla']
    },
    'Arequipa': {
        'Arequipa': ['Cercado', 'Yanahuara'],
        'Camaná': ['Mariscal Cáceres']
    }
};

// Función para poblar departamentos
function poblarDepartamentos(selectId) {
    const select = document.getElementById(selectId);
    Object.keys(ubigeo).forEach(dep => {
        const option = document.createElement('option');
        option.value = dep;
        option.textContent = dep;
        select.appendChild(option);
    });
}

// Función para poblar provincias según departamento
function poblarProvincias(depSelectId, provSelectId, distSelectId) {
    const depSelect = document.getElementById(depSelectId);
    const provSelect = document.getElementById(provSelectId);
    const distSelect = document.getElementById(distSelectId);

    depSelect.addEventListener('change', () => {
        const departamento = depSelect.value;
        provSelect.innerHTML = '<option value="">Seleccione Provincia</option>';
        distSelect.innerHTML = '<option value="">Seleccione Distrito</option>';
        provSelect.disabled = true;
        distSelect.disabled = true;

        if (departamento && ubigeo[departamento]) {
            Object.keys(ubigeo[departamento]).forEach(prov => {
                const option = document.createElement('option');
                option.value = prov;
                option.textContent = prov;
                provSelect.appendChild(option);
            });
            provSelect.disabled = false;
        }
    });

    provSelect.addEventListener('change', () => {
        const departamento = depSelect.value;
        const provincia = provSelect.value;
        distSelect.innerHTML = '<option value="">Seleccione Distrito</option>';
        distSelect.disabled = true;

        if (departamento && provincia && ubigeo[departamento][provincia]) {
            ubigeo[departamento][provincia].forEach(dist => {
                const option = document.createElement('option');
                option.value = dist;
                option.textContent = dist;
                distSelect.appendChild(option);
            });
            distSelect.disabled = false;
        }
    });
}

// Inicializar selects dependientes
poblarDepartamentos('dep-persona');
poblarDepartamentos('dep-empresa');
poblarProvincias('dep-persona', 'prov-persona', 'dist-persona');
poblarProvincias('dep-empresa', 'prov-empresa', 'dist-empresa');
