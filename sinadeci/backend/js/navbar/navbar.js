/**
 * NAVBAR SINADECI - JavaScript
 * Maneja dropdowns (desktop) y sidebar móvil
 * Requiere: Bootstrap 5 (bundle) y SweetAlert2
 */

// ---- Logout (SweetAlert2) ----
function confirmarLogout() {
  Swal.fire({
    title: "¿Cerrar Sesión?",
    text: "Se cerrará su sesión actual del sistema",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Sí, cerrar sesión",
    cancelButtonText: "Cancelar",
    reverseButtons: true,
  }).then((r) => {
    if (r.isConfirmed) window.location.href = "../../../logout.php";
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const DESKTOP_BP = 1200;

  // ================== DROPDOWNS DESKTOP ==================
  const dropdownTriggers = document.querySelectorAll('[data-bs-toggle="dropdown"]');

  dropdownTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function () {
      // Cierra otros dropdowns abiertos
      dropdownTriggers.forEach((other) => {
        if (other !== trigger) {
          const inst = bootstrap.Dropdown.getInstance(other);
          if (inst) inst.hide();
        }
      });

      // Toggle del actual
      const dd = bootstrap.Dropdown.getOrCreateInstance(trigger);
      dd.toggle();
    });
  });

  // Cerrar dropdown al hacer click fuera (solo desktop)
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".dropdown")) {
      dropdownTriggers.forEach((trigger) => {
        const inst = bootstrap.Dropdown.getInstance(trigger);
        if (inst) inst.hide();
      });
    }
  });

  // No cerrar mega-menu al interactuar dentro
  document.querySelectorAll(".mega-menu").forEach((menu) => {
    menu.addEventListener("click", function (e) {
      if (!e.target.closest(".dropdown-item")) e.stopPropagation();
    });
  });

  // ================== SIDEBAR MÓVIL ==================
  const sidebarToggle   = document.getElementById("sidebarToggle");
  const sidebarClose    = document.getElementById("sidebarClose");
  const mobileSidebar   = document.getElementById("mobileSidebar");
  const sidebarOverlay  = document.getElementById("sidebarOverlay");

  function openSidebar() {
    if (!mobileSidebar || !sidebarOverlay) return;
    mobileSidebar.classList.add("show");
    sidebarOverlay.classList.add("show");
    document.body.style.overflow = "hidden";
    document.body.classList.add("sidebar-open");
  }

  function closeSidebar() {
    if (!mobileSidebar || !sidebarOverlay) return;
    mobileSidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
    document.body.style.overflow = "";
    document.body.classList.remove("sidebar-open");
  }

  sidebarToggle?.addEventListener("click", (e) => { e.preventDefault(); openSidebar(); });
  sidebarClose?.addEventListener("click",  (e) => { e.preventDefault(); closeSidebar(); });
  sidebarOverlay?.addEventListener("click",(e) => { e.preventDefault(); closeSidebar(); });

  // Cerrar con ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && mobileSidebar?.classList.contains("show")) closeSidebar();
  });

  // Cerrar si paso a desktop
  window.addEventListener("resize", () => {
    if (window.innerWidth >= DESKTOP_BP && mobileSidebar?.classList.contains("show")) closeSidebar();
  });

  // ================== SUBMENÚS SIDEBAR (collapse) ==================
  const collapseButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');

  collapseButtons.forEach((button) => {
    const targetId = button.getAttribute("data-bs-target");
    if (!targetId) return;

    let targetElement = null;
    try {
      targetElement = document.querySelector(targetId);
    } catch { /* ignore */ }

    if (!targetElement) return;

    targetElement.addEventListener("show.bs.collapse", function () {
      const arrow = button.querySelector(".sidebar-menu-arrow i");
      if (arrow) {
        arrow.style.transition = "transform 0.3s ease";
        arrow.style.transform = "rotate(180deg)";
      }
      button.setAttribute("aria-expanded", "true");
    });

    targetElement.addEventListener("hide.bs.collapse", function () {
      const arrow = button.querySelector(".sidebar-menu-arrow i");
      if (arrow) {
        arrow.style.transition = "transform 0.3s ease";
        arrow.style.transform = "rotate(0deg)";
      }
      button.setAttribute("aria-expanded", "false");
    });
  });

  // Cerrar sidebar al navegar (links)
  document.querySelectorAll(".sidebar-submenu-item, .sidebar-menu-link:not([data-bs-toggle])").forEach((link) => {
    link.addEventListener("click", () => setTimeout(closeSidebar, 150));
  });

  // ================== NOTIFICACIONES MÓVIL (demo) ==================
  const mobileNotificationBtn = document.querySelector(".mobile-notification-btn");
  mobileNotificationBtn?.addEventListener("click", function (e) {
    e.preventDefault();
    Swal.fire({
      title: "Notificaciones",
      html: `
        <div class="text-start">
          <div class="alert alert-warning mb-2">
            <strong>Inspección pendiente</strong><br>
            <small>Certificación #12345 requiere inspección</small>
          </div>
          <div class="alert alert-success mb-2">
            <strong>Certificación completada</strong><br>
            <small>Cert. #12344 aprobada</small>
          </div>
        </div>`,
      confirmButtonText: "Cerrar",
      width: "90%",
      customClass: { container: "swal-mobile-notifications" },
    });
  });

  // ================== PÁGINA ACTIVA ==================
  function setActivePage() {
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split("/").pop() || "escritorio.php";

    document
      .querySelectorAll(".nav-link-custom, .sidebar-menu-link, .sidebar-submenu-item")
      .forEach((el) => el.classList.remove("active"));

    // Navbar desktop
    document.querySelectorAll(".nav-link-custom").forEach((link) => {
      const href = link.getAttribute("href");
      if (href && href === currentPage) link.classList.add("active");
    });

    // Sidebar
    document.querySelectorAll(".sidebar-menu-link, .sidebar-submenu-item").forEach((link) => {
      const href = link.getAttribute("href");
      if (
        href &&
        (href === currentPage || currentPath.includes(href.replace(".php", "")))
      ) {
        link.classList.add("active");
        if (link.classList.contains("sidebar-submenu-item")) {
          const submenu = link.closest(".sidebar-submenu");
          if (submenu) {
            submenu.classList.add("show");
            const button = submenu.previousElementSibling;
            const arrow  = button?.querySelector(".sidebar-menu-arrow i");
            if (arrow) arrow.style.transform = "rotate(180deg)";
            button?.setAttribute("aria-expanded", "true");
          }
        }
      }
    });
  }

  setActivePage();
  console.log("Navbar SINADECI OK");
});

// ================== Helpers globales ==================
function updateNotificationBadge(count) {
  document.querySelectorAll(".notification-badge, .mobile-notification-badge").forEach((b) => {
    if (count > 0) {
      b.textContent = count > 99 ? "99+" : count;
      b.style.display = "flex";
    } else {
      b.style.display = "none";
    }
  });
}

function toggleDarkMode() {
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", document.body.classList.contains("dark-mode"));
}

function closeAllDropdowns() {
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((t) => {
    const dd = bootstrap.Dropdown.getInstance(t);
    if (dd) dd.hide();
  });
}

function openMobileSidebar() {
  document.getElementById("sidebarToggle")?.dispatchEvent(new Event("click"));
}

// Export (si usas módulos)
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    confirmarLogout,
    updateNotificationBadge,
    toggleDarkMode,
    closeAllDropdowns,
    openMobileSidebar,
  };
}
