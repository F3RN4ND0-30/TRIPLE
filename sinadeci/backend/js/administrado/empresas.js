/* =========================================================
   SINADECI · Certificaciones — JS ACOPLADO
   (Mantiene tu API pero corrige ids/clases y agrega robustez)
   ========================================================= */

/* ------------------ Utilitarios ------------------ */
const $  = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

/* ------------------ Reloj/fecha del header ------------------ */
/* Soporta: #currentDate/#currentTime y #cabeceraFecha/#cabeceraHora */
function initClock() {
  const f = new Intl.DateTimeFormat("es-PE", {
    weekday: "long", day: "2-digit", month: "long", year: "numeric",
  });
  const h = new Intl.DateTimeFormat("es-PE", {
    hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false,
  });

  // detectar ids disponibles
  const fechaEl = $("#currentDate") || $("#cabeceraFecha");
  const horaEl  = $("#currentTime") || $("#cabeceraHora");

  const tick = () => {
    const now = new Date();
    if (fechaEl) {
      const icon = '<i class="fa-regular fa-calendar me-1"></i>';
      fechaEl.innerHTML = (fechaEl.id === "currentDate") ? f.format(now) : icon + f.format(now);
    }
    if (horaEl) {
      const icon = '<i class="fa-regular fa-clock me-1"></i>';
      horaEl.innerHTML = (horaEl.id === "currentTime") ? h.format(now) : icon + h.format(now);
    }
  };
  tick();
  setInterval(tick, 1000);
}

/* ------------------ Vista: tabla vs tarjetas ------------------ */
const vistaTabla   = $("#vistaTabla");
const vistaTarjetas= $("#vistaTarjetas");
const contTabla    = $("#contenedorTabla");
const contCards    = $("#contenedorCards");

function setVista(tipo) {
  if (!contTabla || !contCards) return;
  if (tipo === "tabla") {
    contTabla.classList.remove("d-none");
    contCards.classList.add("d-none");
    if (vistaTabla) vistaTabla.checked = true;
  } else {
    contCards.classList.remove("d-none");
    contTabla.classList.add("d-none");
    if (vistaTarjetas) vistaTarjetas.checked = true;
  }
  localStorage.setItem("cert_vista", tipo);
}
if (vistaTabla)   vistaTabla.addEventListener("change", () => setVista("tabla"));
if (vistaTarjetas)vistaTarjetas.addEventListener("change", () => setVista("tarjetas"));
setVista(localStorage.getItem("cert_vista") || "tabla");

/* ------------------ Selección masiva ------------------ */
/* Compatibilidad: .row-check (nuevo) y .fila-check (antiguo) */
const checkAll = $("#checkAll");
const filaChecks = () => $$(".row-check, .fila-check");
function actualizarSeleccion() {
  const todos = filaChecks();
  const total = todos.filter(ch => ch.checked).length;
  const accionesMasivas = $("#accionesMasivas");
  const badgeSel        = $("#seleccionadosBadge");
  if (badgeSel) badgeSel.textContent = `${total} seleccionados`;
  if (accionesMasivas) accionesMasivas.classList.toggle("d-none", total === 0);
  if (checkAll) {
    checkAll.checked = total > 0 && total === todos.length;
    checkAll.indeterminate = total > 0 && total < todos.length;
  }
}
if (checkAll) {
  checkAll.addEventListener("change", () => {
    filaChecks().forEach(ch => (ch.checked = checkAll.checked));
    actualizarSeleccion();
  });
}
document.addEventListener("change", (e) => {
  if (e.target.classList.contains("row-check") || e.target.classList.contains("fila-check")) {
    actualizarSeleccion();
  }
});
const btnLimpiarSel = $("#btnLimpiarSel");
if (btnLimpiarSel) btnLimpiarSel.addEventListener("click", () => {
  filaChecks().forEach(ch => (ch.checked = false));
  actualizarSeleccion();
});

/* ------------------ Modales (instancia segura) ------------------ */
const modalDetalle    = (() => { const el = $("#modalDetalle");    return el ? bootstrap.Modal.getOrCreateInstance(el) : null; })();
const modalInspeccion = (() => { const el = $("#modalInspeccion"); return el ? bootstrap.Modal.getOrCreateInstance(el) : null; })();
const modalObs        = (() => { const el = $("#modalObs");        return el ? bootstrap.Modal.getOrCreateInstance(el) : null; })();
const modalEmitir     = (() => { const el = $("#modalEmitir");     return el ? bootstrap.Modal.getOrCreateInstance(el) : null; })();

/* ------------------ Helpers de fila ------------------ */
/* Compatibilidad: nombre en .company-name (nuevo) o .razon (antiguo) */
/* Compatibilidad de filas: .table-row (nuevo) o .fila-cert (antiguo) */
function getRowData(tr) {
  if (!tr) return null;
  return {
    id:        tr.dataset.id,
    fecha:   tr.children[2]?.textContent.trim() ?? "",
    nombre:     (tr.querySelector(".company-name")?.textContent ?? tr.querySelector(".nombre")?.textContent ?? "").trim(),
    ruc:       tr.children[3]?.textContent.trim() ?? "",
    dni:     tr.children[8]?.textContent.trim() ?? "",
  };
}

function abrirDetalle(data) {
  if (!data) return;
  const set = (id, v) => { const el = $(id); if (el) el.textContent = v; };
  set("#detId",        `#${data.id}`);
  set("#detNombre",     data.nombre);
  set("#detRuc",       data.ruc);
  set("#detDni",   data.dni);
  modalDetalle?.show();
}

/* ------------------ Clicks de acciones ------------------ */
document.addEventListener("click", (e) => {
  // Enlace detalle (compatibilidad)
  if (e.target.closest(".enlace-detalle, .cert-link")) {
    e.preventDefault();
    const tr = e.target.closest("tr");
    abrirDetalle(getRowData(tr));
    return;
  }
  // Botón en card
  if (e.target.closest(".accion-card")) {
    const card = e.target.closest(".card-cert, [data-id]");
    const id   = card?.dataset.id;
    const tr   = id ? document.querySelector(`tr[data-id="${id}"]`) : null;
    const data = tr ? getRowData(tr) : { id, fecha: "", nombre: "",dni: "", ruc: "" };
    const action = e.target.closest(".accion-card").dataset.action;
    despacharAccion(action, data);
    return;
  }
  // Items del dropdown en tabla
  if (e.target.closest(".accion-item")) {
    e.preventDefault();
    const tr = e.target.closest("tr");
    const action = e.target.closest(".accion-item").dataset.action;
    despacharAccion(action, getRowData(tr));
  }
});

function despacharAccion(action, data) {
  if (!data) return;
  switch (action) {
    case "detalle":
      abrirDetalle(data);
      break;
    case "inspeccion":
      const i1 = $("#inspCert"); if (i1) i1.value = `#${data.id} · ${data.razon}`;
      modalInspeccion?.show();
      break;
    case "agregar-obs":
      const i2 = $("#obsCert");  if (i2) i2.value = `#${data.id} · ${data.razon}`;
      modalObs?.show();
      break;
    case "emitir":
      const i3 = $("#emitCert"); if (i3) i3.value = `#${data.id} · ${data.razon}`;
      modalEmitir?.show();
      break;
    case "ver-inspecciones":
      Swal.fire("Inspecciones", "Aquí listarías las inspecciones de esta certificación.", "info");
      break;
    case "ver-obs":
      Swal.fire("Observaciones", "Aquí listarías las observaciones de esta certificación.", "info");
      break;
    case "eliminar":
      Swal.fire({
        icon: "warning",
        title: "Eliminar certificación",
        text: `¿Eliminar la certificación #${data.id}?`,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      }).then((r) => {
        if (r.isConfirmed) Swal.fire("Eliminado", "(Demo) Eliminado visualmente.", "success");
      });
      break;
  }
}

/* ------------------ Menú contextual (click derecho) ------------------ */
/* Tu código esperaba #ctxMenu con .dropdown-menu. Si no existe, lo creo. */
function ensureCtxMenu() {
  let cm = $("#ctxMenu");
  if (!cm) {
    cm = document.createElement("div");
    cm.id = "ctxMenu";
    cm.className = "ctx-menu d-none";
    cm.innerHTML = `
      <div class="dropdown-menu show" style="position:fixed; left:0; top:0;">
        <a class="dropdown-item ctx-action" data-action="detalle"><i class="fa-solid fa-eye me-2"></i>Ver detalle</a>
        <a class="dropdown-item ctx-action" data-action="inspeccion"><i class="fa-solid fa-plus me-2"></i>Agregar inspección</a>
        <a class="dropdown-item ctx-action" data-action="ver-inspecciones"><i class="fa-solid fa-search me-2"></i>Ver inspecciones</a>
        <a class="dropdown-item ctx-action" data-action="agregar-obs"><i class="fa-solid fa-comment me-2"></i>Observaciones</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item ctx-action" data-action="emitir"><i class="fa-solid fa-file-signature me-2"></i>Emitir certificado</a>
      </div>`;
    document.body.appendChild(cm);
  }
  return cm;
}

const ctxMenu = ensureCtxMenu();
let ctxRowData = null;

document.addEventListener("contextmenu", (e) => {
  // Compatibilidad de filas
  const tr = e.target.closest("tr.table-row, tr.fila-cert");
  if (!tr) return;
  e.preventDefault();
  ctxRowData = getRowData(tr);

  const menu = ctxMenu.querySelector(".dropdown-menu");
  const pad = 8;
  let x = e.clientX, y = e.clientY;

  // Mostrar y medir
  menu.style.visibility = "hidden";
  ctxMenu.classList.remove("d-none");

  // Calcular límites para no salir del viewport
  const { width, height } = menu.getBoundingClientRect();
  const maxX = window.innerWidth  - width  - pad;
  const maxY = window.innerHeight - height - pad;
  x = Math.min(x, maxX);
  y = Math.min(y, maxY);
  menu.style.left = x + "px";
  menu.style.top  = y + "px";
  menu.style.visibility = "visible";

  document.addEventListener("click", hideCtxOnce, { once: true });
});
function hideCtxOnce() { ctxMenu.classList.add("d-none"); }
ctxMenu.addEventListener("click", (e) => {
  const a = e.target.closest(".ctx-action");
  if (!a) return;
  e.preventDefault();
  ctxMenu.classList.add("d-none");
  despacharAccion(a.dataset.action, ctxRowData);
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") ctxMenu.classList.add("d-none");
});

/* ------------------ Exportar / Columnas (demo) ------------------ */
const btnExportar = $("#btnExportar");
if (btnExportar)
  btnExportar.addEventListener("click", () => {
    Swal.fire("Exportar", "Aquí exportas a Excel/PDF las certificaciones filtradas.", "info");
  });
const btnColumnas = $("#btnColumnas");
if (btnColumnas)
  btnColumnas.addEventListener("click", () => {
    Swal.fire("Columnas", "Aquí activas/desactivas columnas visibles de la tabla.", "info");
  });

/* ------------------ Buscar (demo) ------------------ */
const btnBuscar = $("#btnBuscar");
const txtBuscar = $("#buscarTexto");
function ejecutarBusqueda() {
  const campo = $("#filtroCampo")?.value ?? "";
  const texto = (txtBuscar?.value ?? "").trim();
  if (!texto) return;
  Swal.fire("Buscar", `Campo: ${campo}\nTexto: ${texto}`, "info");
}
if (btnBuscar) btnBuscar.addEventListener("click", ejecutarBusqueda);
if (txtBuscar) txtBuscar.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    ejecutarBusqueda();
  }
});

/* ------------------ FAB ------------------ */
const fab = $("#fabNueva");
if (fab) {
  fab.addEventListener("click", () => {
    Swal.fire("Nueva Certificación", "Funcionalidad para crear nueva certificación", "info");
  });
  document.addEventListener("keydown", (e) => {
    if (e.key.toLowerCase() === "n" && !e.ctrlKey && !e.metaKey && !e.altKey) {
      e.preventDefault();
      fab.click();
    }
  });
}

/* ------------------ Ajuste FAB vs scroll horizontal ------------------ */
function adjustFabForScroll() {
  if (!fab) return;
  const el = $("#contenedorTabla");
  if (!el) return;
  const overflowX = el.scrollWidth > el.clientWidth;
  document.documentElement.style.setProperty("--scrollbar-x", overflowX ? "16px" : "0px");
}
window.addEventListener("resize", adjustFabForScroll);
window.addEventListener("load", adjustFabForScroll);
adjustFabForScroll();

/* ------------------ Altura dinámica para ver todas las filas ------------------ */
/* Si hay pocas filas (ej. 5), evita que el contenedor corte a 3 */
function ajustarAlturaTabla() {
  const cont = $("#contenedorTabla");
  const table = $("#tablaCert");
  if (!cont || !table) return;

  const thead = table.querySelector("thead");
  const tbody = table.querySelector("tbody");
  const rows  = tbody ? [...tbody.rows] : [];
  if (rows.length === 0) return;

  // Altura estimada: header + filas + margen
  const headerH = thead?.getBoundingClientRect().height || 48;
  const rowH    = rows[0].getBoundingClientRect().height || 52;
  const totalH  = headerH + rows.length * rowH + 16; // padding extra

  // Límite superior: viewport – marcos de la página (similar a tu CSS)
  const maxViewport = Math.max(320, window.innerHeight - 260);
  cont.style.maxHeight = Math.min(totalH, maxViewport) + "px";
}
window.addEventListener("load", ajustarAlturaTabla);
window.addEventListener("resize", ajustarAlturaTabla);
ajustarAlturaTabla();

/* ------------------ Go! ------------------ */
initClock();

