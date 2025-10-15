/* =========================================================
   SINADECI · Usuarios — JS ACOPLADO
   ========================================================= */
const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

/* ------------------ Reloj/fecha ------------------ */
function initClock() {
  const f = new Intl.DateTimeFormat("es-PE", {
    weekday: "long",
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
  const h = new Intl.DateTimeFormat("es-PE", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });
  const fechaEl = $("#currentDate") || $("#cabeceraFecha");
  const horaEl = $("#currentTime") || $("#cabeceraHora");
  const tick = () => {
    const now = new Date();
    if (fechaEl)
      fechaEl.innerHTML =
        (fechaEl.id === "currentDate"
          ? ""
          : '<i class="fa-regular fa-calendar me-1"></i>') + f.format(now);
    if (horaEl)
      horaEl.innerHTML =
        (horaEl.id === "currentTime"
          ? ""
          : '<i class="fa-regular fa-clock me-1"></i>') + h.format(now);
  };
  tick();
  setInterval(tick, 1000);
}

/* ------------------ Offcanvas filtros (estático) ------------------ */
const offcanvasEl = $("#offcanvasFiltros");
const offcanvasFiltros = offcanvasEl
  ? bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl, {
      backdrop: "static",
      keyboard: false,
      scroll: true,
    })
  : null;

// Abrir desde toolbar o desde cabecera de listado
$("#btnFiltrosAvanzados")?.addEventListener("click", () =>
  offcanvasFiltros?.show()
);
$("#btnFiltrosAvanzadosHdr")?.addEventListener("click", () =>
  offcanvasFiltros?.show()
);

/* ------------------ Vista tabla / tarjetas (si lo usas aquí) ------------------ */
const vistaTabla = $("#vistaTabla");
const vistaTarjetas = $("#vistaTarjetas");
const contTabla = $("#contenedorTabla");
const contCards = $("#contenedorCards");

function setVista(tipo) {
  if (!contTabla || !contCards) return;
  if (tipo === "tabla") {
    contTabla.classList.remove("d-none");
    contCards.classList.add("d-none");
    vistaTabla && (vistaTabla.checked = true);
  } else {
    contCards.classList.remove("d-none");
    contTabla.classList.add("d-none");
    vistaTarjetas && (vistaTarjetas.checked = true);
  }
  localStorage.setItem("usuarios_vista", tipo);
}
vistaTabla?.addEventListener("change", () => setVista("tabla"));
vistaTarjetas?.addEventListener("change", () => setVista("tarjetas"));
setVista(localStorage.getItem("usuarios_vista") || "tabla");

/* ------------------ Selección masiva ------------------ */
const checkAll = $("#checkAll");
const filaChecks = () => $$(".row-check, .fila-check");
function actualizarSeleccion() {
  const todos = filaChecks();
  const total = todos.filter((ch) => ch.checked).length;
  const accionesMasivas = $("#accionesMasivas");
  const badgeSel = $("#seleccionadosBadge");
  if (badgeSel) badgeSel.textContent = `${total} seleccionados`;
  if (accionesMasivas) accionesMasivas.classList.toggle("d-none", total === 0);
  if (checkAll) {
    checkAll.checked = total > 0 && total === todos.length;
    checkAll.indeterminate = total > 0 && total < todos.length;
  }
}
checkAll?.addEventListener("change", () => {
  filaChecks().forEach((ch) => (ch.checked = checkAll.checked));
  actualizarSeleccion();
});
document.addEventListener("change", (e) => {
  if (
    e.target.classList.contains("row-check") ||
    e.target.classList.contains("fila-check")
  )
    actualizarSeleccion();
});
$("#btnLimpiarSel")?.addEventListener("click", () => {
  filaChecks().forEach((ch) => (ch.checked = false));
  actualizarSeleccion();
});

/* ------------------ Badges de filtros aplicados ------------------ */
function getQuery() {
  const p = new URLSearchParams(window.location.search);
  return {
    q: p.get("q") || "",
    campo: p.get("campo") || "",
    rol: p.get("rol") || "",
    estado: p.get("estado") || "",
  };
}
function setQuery(q) {
  const p = new URLSearchParams();
  if (q.q) p.set("q", q.q);
  if (q.campo) p.set("campo", q.campo);
  if (q.rol) p.set("rol", q.rol);
  if (q.estado) p.set("estado", q.estado);
  // recarga con los parámetros (el backend ya filtra/contabiliza)
  window.location.search = p.toString();
}

function renderAppliedFilters() {
  const box = $("#appliedFilters");
  if (!box) return;
  box.innerHTML = "";
  const q = getQuery();

  const addBadge = (label, key) => {
    const span = document.createElement("span");
    span.className =
      "badge rounded-pill text-bg-light border d-flex align-items-center gap-2";
    span.innerHTML = `<i class="fa-solid fa-filter"></i> ${label}
      <button class="btn btn-sm btn-link p-0 ms-1 remove-filter" data-key="${key}" aria-label="Quitar filtro">
        <i class="fa-solid fa-xmark"></i>
      </button>`;
    box.appendChild(span);
  };

  if (q.rol) addBadge(`Rol: ${q.rol}`, "rol");
  if (q.estado)
    addBadge(
      `Estado: ${
        q.estado === "1" ? "Activo" : q.estado === "0" ? "Inactivo" : q.estado
      }`,
      "estado"
    );
  if (q.q) {
    const campoTxt = q.campo || "texto";
    addBadge(`Búsqueda (${campoTxt}): “${q.q}”`, "q");
  }

  box.querySelectorAll(".remove-filter").forEach((btn) => {
    btn.addEventListener("click", () => {
      const key = btn.dataset.key;
      const curr = getQuery();
      if (key === "q") {
        delete curr.q;
        delete curr.campo;
      } else delete curr[key];
      setQuery(curr);
    });
  });
}

/* ------------------ Aplicar filtros ------------------ */
$("#btnAplicarFiltros")?.addEventListener("click", () => {
  const curr = getQuery();
  const rol = $("#fRol")?.value?.trim() ?? "";
  const estado = $("#fEstado")?.value?.trim() ?? "";

  const next = { ...curr, rol, estado };
  setQuery(next);
});

$("#btnBuscar")?.addEventListener("click", ejecutarBusqueda);
$("#buscarTexto")?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    ejecutarBusqueda();
  }
});
function ejecutarBusqueda() {
  const q = $("#buscarTexto")?.value?.trim() ?? "";
  const campo = $("#filtroCampo")?.value || "";
  const curr = getQuery();
  const next = { ...curr, q, campo };
  setQuery(next);
}

$("#btnLimpiar")?.addEventListener("click", () => {
  setQuery({}); // borra todos los filtros
});

/* ------------------ Ajustes visuales ------------------ */
function ajustarAlturaTabla() {
  const cont = $("#contenedorTabla");
  const table = $("#tablaUsuarios") || $("#tablaCert");
  if (!cont || !table) return;
  const thead = table.querySelector("thead");
  const tbody = table.querySelector("tbody");
  const rows = tbody ? [...tbody.rows] : [];
  if (rows.length === 0) {
    cont.style.maxHeight = "";
    return;
  }

  // 10 filas “target” si caben, y siempre evitando cortar a 3
  const rowH = rows[0].getBoundingClientRect().height || 52;
  const header = thead?.getBoundingClientRect().height || 48;
  const wanted = header + Math.min(10, rows.length) * rowH + 16;
  const maxViewport = Math.max(320, window.innerHeight - 260);
  cont.style.maxHeight = Math.min(wanted, maxViewport) + "px";
}
window.addEventListener("load", ajustarAlturaTabla);
window.addEventListener("resize", ajustarAlturaTabla);

/* ------------------ Go! ------------------ */
initClock();
renderAppliedFilters();
ajustarAlturaTabla();
