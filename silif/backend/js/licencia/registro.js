function abrirModal() {
    document.getElementById('modalDireccion').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalDireccion').style.display = 'none';
}

// También puedes cerrar con "Esc"
document.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
        cerrarModal();
    }
});
