/**
 * SINADECI - Sistema de Login Optimizado
 * Login con SweetAlert2 y validaciones avanzadas
 */

// Variables globales
let formularioLogin = null;
let botonEnviar = null;
let campoUsuario = null;
let campoPassword = null;
let togglePassword = null;
let enviandoFormulario = false;
let intentosFallidos = 0;

// Inicialización del sistema
document.addEventListener("DOMContentLoaded", function () {
  inicializarSistemaLogin();
});

function inicializarSistemaLogin() {
  if (!obtenerElementosDOM()) {
    mostrarError(
      "Error crítico: No se encontraron los elementos necesarios del formulario"
    );
    return;
  }

  configurarEventosFormulario();
  inicializarEstadoFormulario();
  iniciarRelojTiempoReal();

  console.log("SINADECI: Sistema de login inicializado correctamente");
}

// Obtener elementos del DOM
function obtenerElementosDOM() {
  formularioLogin = document.getElementById("formularioAuth");
  botonEnviar = document.getElementById("botonEnviar");
  campoUsuario = document.getElementById("username");
  campoPassword = document.getElementById("password");
  togglePassword = document.getElementById("revelarPassword");

  return formularioLogin && botonEnviar && campoUsuario && campoPassword;
}

// Configurar eventos del formulario
function configurarEventosFormulario() {
  // Evento principal del formulario
  formularioLogin.addEventListener("submit", manejarEnvioFormulario);

  // Toggle de contraseña
  togglePassword?.addEventListener("click", alternarVisibilidadPassword);

  // Eventos de campos de entrada
  campoUsuario.addEventListener("input", validarCampoEnTiempoReal);
  campoPassword.addEventListener("input", validarCampoEnTiempoReal);

  campoUsuario.addEventListener("focus", () => marcarCampoActivo(campoUsuario));
  campoPassword.addEventListener("focus", () =>
    marcarCampoActivo(campoPassword)
  );

  campoUsuario.addEventListener("blur", () =>
    desmarcarCampoActivo(campoUsuario)
  );
  campoPassword.addEventListener("blur", () =>
    desmarcarCampoActivo(campoPassword)
  );

  // Prevenir ataques por arrastrar y soltar
  formularioLogin.addEventListener("dragover", (e) => e.preventDefault());
  formularioLogin.addEventListener("drop", (e) => e.preventDefault());

  // Atajos de teclado
  document.addEventListener("keydown", manejarAtajosTeclado);
}

// Inicializar estado del formulario
function inicializarEstadoFormulario() {
  campoUsuario.focus();

  // Recuperar usuario guardado si existe
  const usuarioGuardado = localStorage.getItem("sinadeci_usuario_guardado");
  if (usuarioGuardado) {
    campoUsuario.value = usuarioGuardado;
    marcarCampoConValor(campoUsuario);
    campoPassword.focus();
  }

  // Aplicar estilos a campos con valor
  [campoUsuario, campoPassword].forEach((campo) => {
    if (campo.value.trim()) {
      marcarCampoConValor(campo);
    }
  });
}

// Manejar envío del formulario
async function manejarEnvioFormulario(evento) {
  evento.preventDefault();

  if (enviandoFormulario) {
    return;
  }

  const validacion = validarFormularioCompleto();
  if (!validacion.esValido) {
    mostrarErrorValidacion(validacion.mensaje, validacion.campo);
    return;
  }

  await procesarLogin(validacion.datos);
}

// Validación completa del formulario
function validarFormularioCompleto() {
  const usuario = campoUsuario.value.trim();
  const password = campoPassword.value;

  // Limpiar errores previos
  limpiarErroresCampos();

  // Validación de campos vacíos
  if (!usuario) {
    return {
      esValido: false,
      mensaje: "El campo usuario es obligatorio",
      campo: campoUsuario,
    };
  }

  if (!password) {
    return {
      esValido: false,
      mensaje: "El campo contraseña es obligatorio",
      campo: campoPassword,
    };
  }

  // Validación de longitud mínima
  if (usuario.length < 3) {
    return {
      esValido: false,
      mensaje: "El usuario debe tener al menos 3 caracteres",
      campo: campoUsuario,
    };
  }

  if (password.length < 4) {
    return {
      esValido: false,
      mensaje: "La contraseña debe tener al menos 4 caracteres",
      campo: campoPassword,
    };
  }

  // Validación de seguridad
  if (contienePeligrosos(usuario) || contienePeligrosos(password)) {
    return {
      esValido: false,
      mensaje: "Se detectaron caracteres no válidos en los datos ingresados",
    };
  }

  return {
    esValido: true,
    datos: { usuario, password },
  };
}

// Validación en tiempo real
function validarCampoEnTiempoReal(evento) {
  const campo = evento.target;
  const valor = campo.value.trim();

  // Limpiar error del campo
  limpiarErrorCampo(campo);

  // Marcar campo con valor
  if (valor) {
    marcarCampoConValor(campo);
  } else {
    desmarcarCampoConValor(campo);
  }

  // Validación específica por campo
  if (campo === campoUsuario && valor.length > 0 && valor.length < 3) {
    mostrarErrorCampo(campo, "Mínimo 3 caracteres");
  } else if (campo === campoPassword && valor.length > 0 && valor.length < 4) {
    mostrarErrorCampo(campo, "Mínimo 4 caracteres");
  }
}

// Procesar login
async function procesarLogin(datos) {
  establecerEstadoCargando(true);

  try {
    const datosFormulario = new FormData();
    datosFormulario.append("username", datos.usuario);
    datosFormulario.append("password", datos.password);
    datosFormulario.append("ajax", "1");

    // Recordar usuario si está marcado
    const recordar = document.getElementById("remember")?.checked;
    if (recordar) {
      datosFormulario.append("remember", "1");
    }

    const respuesta = await fetch(window.location.pathname, {
      method: "POST",
      body: datosFormulario,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!respuesta.ok) {
      throw new Error(
        `Error del servidor: ${respuesta.status} ${respuesta.statusText}`
      );
    }

    const resultado = await respuesta.json();
    procesarRespuestaLogin(resultado, datos.usuario, recordar);
  } catch (error) {
    console.error("Error en login SINADECI:", error);
    manejarErrorConexion(error);
  }
}

// Procesar respuesta del login
function procesarRespuestaLogin(resultado, usuario, recordar) {
  if (resultado.success) {
    manejarLoginExitoso(resultado, usuario, recordar);
  } else {
    manejarLoginFallido(resultado);
  }
}

// Manejar login exitoso
function manejarLoginExitoso(resultado, usuario, recordar) {
  establecerEstadoCargando(false);
  establecerEstadoExitoso(true);

  // Guardar usuario si está marcado recordar
  if (recordar) {
    localStorage.setItem("sinadeci_usuario_guardado", usuario);
  } else {
    localStorage.removeItem("sinadeci_usuario_guardado");
  }

  // Toast de éxito
  mostrarExitoLogin(`¡Bienvenido, ${resultado.usuario || usuario}!`);

  // Redirigir después de mostrar mensaje
  setTimeout(() => {
    window.location.href = resultado.redirect || "sisvis/escritorio.php";
  }, 2000);
}

// Manejar login fallido
function manejarLoginFallido(resultado) {
  establecerEstadoCargando(false);
  intentosFallidos++;

  if (resultado.bloqueado) {
    manejarCuentaBloqueada(resultado);
  } else {
    mostrarErrorLogin(resultado.error || "Credenciales incorrectas");

    // Mostrar intentos restantes si aplica
    if (resultado.restantes && resultado.restantes > 0) {
      setTimeout(() => {
        mostrarAdvertenciaIntentos(resultado.restantes);
      }, 1500);
    }
  }

  // Limpiar contraseña y enfocar
  campoPassword.value = "";
  desmarcarCampoConValor(campoPassword);
  campoPassword.focus();
}

// Manejar cuenta bloqueada
function manejarCuentaBloqueada(resultado) {
  const mensaje =
    resultado.tipo === "ip"
      ? "Su dirección IP ha sido bloqueada temporalmente por seguridad"
      : "Su cuenta ha sido bloqueada por múltiples intentos fallidos";

  mostrarBloqueoSistema(mensaje);
  deshabilitarFormulario(true);
}

// Manejar errores de conexión
function manejarErrorConexion(error) {
  establecerEstadoCargando(false);

  let mensaje = "Error de conexión. Verifique su conexión a internet";

  if (error.name === "TypeError" || error.message.includes("Failed to fetch")) {
    mensaje = "No se pudo conectar con el servidor. Intente nuevamente";
  } else if (error.message.includes("Error del servidor")) {
    mensaje =
      "Error interno del servidor. Contacte al administrador del sistema";
  }

  mostrarErrorConexion(mensaje);
}

// Estados visuales del formulario
function establecerEstadoCargando(cargando) {
  enviandoFormulario = cargando;

  if (cargando) {
    botonEnviar.classList.add("cargando");
    botonEnviar.disabled = true;
    [campoUsuario, campoPassword].forEach((campo) => (campo.disabled = true));
  } else {
    botonEnviar.classList.remove("cargando");
    botonEnviar.disabled = false;
    [campoUsuario, campoPassword].forEach((campo) => (campo.disabled = false));
  }
}

function establecerEstadoExitoso(exitoso) {
  if (exitoso) {
    botonEnviar.classList.add("exitoso");
  }
}

function deshabilitarFormulario(deshabilitado) {
  const elementos = formularioLogin.querySelectorAll("input, button");
  elementos.forEach((elemento) => (elemento.disabled = deshabilitado));
}

// Manejo visual de campos
function marcarCampoActivo(campo) {
  const wrapper = campo.closest(".envolvedor-input");
  wrapper?.classList.add("enfocado");
}

function desmarcarCampoActivo(campo) {
  const wrapper = campo.closest(".envolvedor-input");
  wrapper?.classList.remove("enfocado");
}

function marcarCampoConValor(campo) {
  campo.classList.add("tiene-valor");
}

function desmarcarCampoConValor(campo) {
  campo.classList.remove("tiene-valor");
}

function mostrarErrorCampo(campo, mensaje) {
  const wrapper = campo.closest(".envolvedor-input");
  const feedback = wrapper?.querySelector(".retroalimentacion-input");

  if (feedback) {
    feedback.textContent = mensaje;
    feedback.classList.add("mostrar");
  }

  wrapper?.classList.add("error");
}

function limpiarErrorCampo(campo) {
  const wrapper = campo.closest(".envolvedor-input");
  const feedback = wrapper?.querySelector(".retroalimentacion-input");

  if (feedback) {
    feedback.classList.remove("mostrar");
  }

  wrapper?.classList.remove("error");
}

function limpiarErroresCampos() {
  [campoUsuario, campoPassword].forEach((campo) => limpiarErrorCampo(campo));
}

// Toggle de contraseña
function alternarVisibilidadPassword() {
  const esPassword = campoPassword.type === "password";
  campoPassword.type = esPassword ? "text" : "password";

  const icono = togglePassword.querySelector("i");
  if (icono) {
    icono.className = esPassword ? "fas fa-eye-slash" : "fas fa-eye";
  }

  // Mantener foco en el campo
  campoPassword.focus();
}

// Atajos de teclado
function manejarAtajosTeclado(evento) {
  // Enter para enviar formulario
  if (evento.key === "Enter" && formularioLogin.contains(evento.target)) {
    evento.preventDefault();
    formularioLogin.requestSubmit();
  }

  // Escape para limpiar errores
  if (evento.key === "Escape") {
    limpiarErroresCampos();
    Swal.close();
  }

  // Ctrl+L para limpiar formulario
  if (evento.ctrlKey && evento.key === "l") {
    evento.preventDefault();
    limpiarFormulario();
  }
}

// Limpiar formulario
function limpiarFormulario() {
  campoUsuario.value = "";
  campoPassword.value = "";
  desmarcarCampoConValor(campoUsuario);
  desmarcarCampoConValor(campoPassword);
  limpiarErroresCampos();
  campoUsuario.focus();
}

// Sistema de notificaciones con SweetAlert2
function mostrarExitoLogin(mensaje) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });

  Toast.fire({
    icon: "success",
    title: "Ingreso Exitoso",
    text: mensaje,
    background: "#d4edda",
    color: "#155724",
  });
}

function mostrarErrorLogin(mensaje) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });

  Toast.fire({
    icon: "error",
    title: "Error de Acceso",
    text: mensaje,
    background: "#f8d7da",
    color: "#721c24",
  });
}

function mostrarAdvertenciaIntentos(restantes) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  Toast.fire({
    icon: "warning",
    title: "Advertencia de Seguridad",
    text: `Le quedan ${restantes} intentos antes del bloqueo`,
    background: "#fff3cd",
    color: "#856404",
  });
}

function mostrarBloqueoSistema(mensaje) {
  Swal.fire({
    icon: "error",
    title: "Acceso Bloqueado",
    text: mensaje,
    confirmButtonText: "Entendido",
    confirmButtonColor: "#dc3545",
    allowOutsideClick: false,
    allowEscapeKey: false,
  });
}

function mostrarErrorConexion(mensaje) {
  Swal.fire({
    icon: "error",
    title: "Error de Conexión",
    text: mensaje,
    confirmButtonText: "Reintentar",
    confirmButtonColor: "#007bff",
    showCancelButton: true,
    cancelButtonText: "Cancelar",
  }).then((resultado) => {
    if (resultado.isConfirmed) {
      location.reload();
    }
  });
}

function mostrarErrorValidacion(mensaje, campo = null) {
  if (campo) {
    mostrarErrorCampo(campo, mensaje);
  }

  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  Toast.fire({
    icon: "warning",
    title: "Error de Validación",
    text: mensaje,
    background: "#fff3cd",
    color: "#856404",
  });
}

function mostrarError(mensaje) {
  Swal.fire({
    icon: "error",
    title: "Error del Sistema",
    text: mensaje,
    confirmButtonText: "Recargar Página",
    confirmButtonColor: "#dc3545",
  }).then(() => {
    location.reload();
  });
}

// Funciones de seguridad
function contienePeligrosos(texto) {
  const patronesPeligrosos = [
    /<script|javascript:|on\w+\s*=/i,
    /(<\s*\w+[^>]*>)|(<\/\s*\w+\s*>)/i,
    /(\bselect\b|\bunion\b|\binsert\b|\bdelete\b|\bupdate\b|\bdrop\b)/i,
  ];

  return patronesPeligrosos.some((patron) => patron.test(texto));
}

// Reloj en tiempo real
function iniciarRelojTiempoReal() {
  const actualizarReloj = () => {
    const elementoTiempo = document.getElementById("tiempoActual");
    if (elementoTiempo) {
      const ahora = new Date();
      elementoTiempo.textContent = ahora.toLocaleTimeString("es-PE", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      });
    }
  };

  actualizarReloj();
  setInterval(actualizarReloj, 1000);
}

// Funciones globales para PHP
window.mostrarNotificacion = function (mensaje, tipo = "info") {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });

  const iconos = {
    success: "success",
    error: "error",
    warning: "warning",
    info: "info",
  };

  Toast.fire({
    icon: iconos[tipo] || "info",
    title: mensaje,
  });
};

window.mostrarAlertaBloqueado = function (mensaje, tipo) {
  mostrarBloqueoSistema(mensaje);
  deshabilitarFormulario(true);
};
