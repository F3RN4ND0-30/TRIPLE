<?php
// Verificar que la sesión esté iniciada
if (!isset($_SESSION['sinadeci_id'])) {
    return;
}

$usuario_nombre = $_SESSION['sinadeci_nombre'] ?? 'Usuario';
$tipo_usuario = $_SESSION['sinadeci_tipo'] ?? 'Usuario';
?>

<!-- CSS del navbar (incluir automáticamente) -->
<link rel="stylesheet" href="../../backend/css/navbar/navbar.css">
<link rel="stylesheet" href="../../backend/css/navbar/navbar-responsive.css">

<!-- ==============================
     NAVBAR ESCRITORIO (DESKTOP)
     ============================== -->
<nav class="navbar navbar-expand-xl navbar-dark sticky-top">
    <div class="container-fluid">
        <!-- Logo y nombre del sistema -->
        <div class="navbar-brand d-flex align-items-center">
            <div class="brand-logo-container">
                <img src="../../backend/img/ICONO-SINADECI.ico" alt="SINADECI" class="navbar-logo">
            </div>
            <div class="brand-text d-none d-md-block">
                <div class="brand-name">SINADECI</div>
                <div class="brand-subtitle d-none d-lg-block">Sistema Nacional de Defensa Civil</div>
            </div>
        </div>

        <!-- Notificaciones en móvil (solo mostrar en móvil) -->
        <div class="d-block d-xl-none mobile-notifications">
            <button class="mobile-notification-btn" type="button">
                <i class="fas fa-bell"></i>
                <span class="mobile-notification-badge">3</span>
            </button>
        </div>

        <!-- Toggle para móviles -->
        <button class="navbar-toggler custom-toggler d-block d-xl-none" type="button" id="sidebarToggle">
            <span class="toggler-line"></span>
            <span class="toggler-line"></span>
            <span class="toggler-line"></span>
        </button>

        <!-- Menú de navegación DESKTOP (solo XL+) -->
        <div class="collapse navbar-collapse d-none d-lg-block" id="navbarNav">
            <!-- Menú principal - Izquierda -->
            <ul class="navbar-nav me-auto">
                <!-- Escritorio -->
                <li class="nav-item">
                    <a class="nav-link nav-link-custom active" href="../sisvis/escritorio.php">
                        <div class="nav-icon-wrapper">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">Escritorio</span>
                    </a>
                </li>

                <!-- GESTIÓN DE CERTIFICACIONES - Mega menú -->
                <li class="nav-item dropdown mega-dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="certificacionesDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <div class="nav-icon-wrapper">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <span class="nav-text">Certificaciones</span>
                    </a>
                    <div class="dropdown-menu mega-menu" aria-labelledby="certificacionesDropdown">
                        <div class="mega-menu-content">
                            <!-- Columna 1: Gestión -->
                            <div class="mega-menu-column">
                                <h6 class="mega-menu-header">
                                    <i class="fas fa-tasks"></i>
                                    Gestión
                                </h6>
                                <a class="dropdown-item dropdown-item-custom" href="../certificaciones/certificaciones.php">
                                    <i class="fas fa-list dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Listar Certificaciones</span>
                                        <small class="dropdown-desc">Ver todas las certificaciones</small>
                                    </div>
                                </a>
                                <!-- <a class="dropdown-item dropdown-item-custom featured" href="certificaciones/nueva.php">
                                    <i class="fas fa-plus dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Nueva Certificación</span>
                                        <small class="dropdown-desc">Crear nueva certificación</small>
                                    </div>
                                    <span class="featured-badge">Popular</span>
                                </a>
                                <a class="dropdown-item dropdown-item-custom" href="certificaciones/buscar.php">
                                    <i class="fas fa-search dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Buscar</span>
                                        <small class="dropdown-desc">Buscar certificaciones</small>
                                    </div>
                                </a> -->
                            </div>

                            <!-- Columna 2: Inspecciones -->
                            <div class="mega-menu-column">
                                <h6 class="mega-menu-header">
                                    <i class="fas fa-search"></i>
                                    Inspecciones
                                </h6>
                                <a class="dropdown-item dropdown-item-custom" href="../inspecciones/listar_inspecciones.php">
                                    <i class="fas fa-clipboard-list dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Lista de Inspecciones</span>
                                        <small class="dropdown-desc">Ver inspecciones programadas</small>
                                    </div>
                                </a>
                                <a class="dropdown-item dropdown-item-custom" href="inspecciones/programar.php">
                                    <i class="fas fa-calendar-plus dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Programar Inspección</span>
                                        <small class="dropdown-desc">Agendar nueva inspección</small>
                                    </div>
                                </a>
                                <a class="dropdown-item dropdown-item-custom" href="inspecciones/pendientes.php">
                                    <i class="fas fa-exclamation-circle dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Pendientes</span>
                                        <small class="dropdown-desc">Inspecciones por realizar</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- REPORTES Y ESTADÍSTICAS -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="reportesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="nav-icon-wrapper">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="nav-text">Reportes</span>
                    </a>
                    <div class="dropdown-menu dropdown-custom" aria-labelledby="reportesDropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-section-title">
                                <span>Reportes Principales</span>
                            </div>
                            <a class="dropdown-item dropdown-item-custom" href="../reportes/rep_certificaciones.php">
                                <i class="fas fa-file-pdf dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Certificaciones</span>
                                    <small class="dropdown-desc">Reportes de certificaciones</small>
                                </div>
                            </a>
                            <a class="dropdown-item dropdown-item-custom" href="../reportes/rep_inspecciones.php">
                                <i class="fas fa-file-alt dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Inspecciones</span>
                                    <small class="dropdown-desc">Reportes de inspecciones</small>
                                </div>
                            </a>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-section">
                            <div class="dropdown-section-title">
                                <span>Análisis</span>
                            </div>
                            <a class="dropdown-item dropdown-item-custom" href="../reportes/estadisticas.php">
                                <i class="fas fa-chart-line dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Estadísticas</span>
                                    <small class="dropdown-desc">Gráficos y análisis</small>
                                </div>
                            </a>
                            <a class="dropdown-item dropdown-item-custom" href="../reportes/dashboard.php">
                                <i class="fas fa-chart-pie dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Dashboard Avanzado</span>
                                    <small class="dropdown-desc">Métricas en tiempo real</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </li>

                <!-- LICENCIAS -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="licenciasDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="nav-icon-wrapper">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <span class="nav-text">Licencias</span>
                    </a>
                    <ul class="dropdown-menu dropdown-custom" aria-labelledby="licenciasDropdown">
                        <li><a class="dropdown-item dropdown-item-custom" href="licencias/lista.php">
                                <i class="fas fa-list dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Listar Licencias</span>
                                    <small class="dropdown-desc">Ver todas las licencias</small>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item dropdown-item-custom" href="licencias/nueva.php">
                                <i class="fas fa-plus dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Nueva Licencia</span>
                                    <small class="dropdown-desc">Crear nueva licencia</small>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item dropdown-item-custom" href="licencias/renovaciones.php">
                                <i class="fas fa-sync dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Renovaciones</span>
                                    <small class="dropdown-desc">Gestionar renovaciones</small>
                                </div>
                            </a></li>
                    </ul>
                </li>

                <!-- ADMINISTRADO -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="administradoDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="nav-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Administrado</span>
                    </a>
                    <ul class="dropdown-menu dropdown-custom" aria-labelledby="administradoDropdown">
                        <li><a class="dropdown-item dropdown-item-custom" href="../administrado/personas.php">
                                <i class="fas fa-user dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Personas</span>
                                    <small class="dropdown-desc">Gestión de personas registradas</small>
                                </div>
                            </a>
                        </li>
                        <li><a class="dropdown-item dropdown-item-custom" href="../administrado/empresas.php">
                                <i class="fas fa-building dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Empresas</span>
                                    <small class="dropdown-desc">Gestión de empresas vinculadas</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- ADMINISTRACIÓN (solo ADMINISTRADOR) -->
                <?php if ($tipo_usuario == 'ADMINISTRADOR'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-custom dropdown-toggle admin-menu" href="#" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-icon-wrapper">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <span class="admin-badge bg-primary text-white px-2 py-1 rounded">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-custom admin-dropdown" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item dropdown-item-custom" href="../admin/usuarios.php">
                                    <i class="fas fa-users-cog dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Usuarios</span>
                                        <small class="dropdown-desc">Gestionar usuarios</small>
                                    </div>
                                </a>
                            </li>
                            <li><a class="dropdown-item dropdown-item-custom" href="admin/sistema.php">
                                    <i class="fas fa-cog dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Sistema</span>
                                        <small class="dropdown-desc">Configuración general</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item dropdown-item-custom" href="admin/logs.php">
                                    <i class="fas fa-file-alt dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Logs del Sistema</span>
                                        <small class="dropdown-desc">Auditoría y registros</small>
                                    </div>
                                </a>
                            </li>
                            <li><a class="dropdown-item dropdown-item-custom" href="admin/backup.php">
                                    <i class="fas fa-database dropdown-icon"></i>
                                    <div class="dropdown-content">
                                        <span class="dropdown-title">Respaldo</span>
                                        <small class="dropdown-desc">Backup de datos</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Menú de usuario - Derecha (solo desktop) -->
            <ul class="navbar-nav">
                <!-- Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link notification-link position-relative" href="#" id="notificacionesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="notification-icon-wrapper">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificacionesDropdown">
                        <li class="notification-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="notification-title">Notificaciones</h6>
                                <button class="btn btn-sm btn-outline-primary mark-read-btn">Leer</button>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="notification-content">
                                    <div class="notification-icon warning">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="notification-text">
                                        <strong>Inspección pendiente</strong>
                                        <p>Certificación #12345 requiere inspección</p>
                                        <small>Hace 2 horas</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item notification-item" href="#">
                                <div class="notification-content">
                                    <div class="notification-icon success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="notification-text">
                                        <strong>Certificación completada</strong>
                                        <p>Cert. #12344 aprobada</p>
                                        <small>Hace 4 horas</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="notification-footer">
                            <a class="dropdown-item text-center view-all-btn" href="notificaciones/">
                                Ver todas
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Perfil de usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle user-profile-link" href="#" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-profile-wrapper">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info d-none d-lg-block">
                                <div class="user-name"><?= htmlspecialchars(explode(' ', $usuario_nombre)[0]) ?></div>
                                <div class="user-role"><?= htmlspecialchars($tipo_usuario) ?></div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="perfilDropdown">
                        <li class="user-dropdown-header">
                            <div class="user-profile-info">
                                <div class="user-avatar-large">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="user-details">
                                    <h6 class="user-full-name"><?= htmlspecialchars($usuario_nombre) ?></h6>
                                    <span class="user-type-badge"><?= htmlspecialchars($tipo_usuario) ?></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-custom" href="perfil/">
                                <i class="fas fa-user-cog dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Mi Perfil</span>
                                    <small class="dropdown-desc">Configurar perfil</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-custom" href="perfil/password.php">
                                <i class="fas fa-key dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Cambiar Contraseña</span>
                                    <small class="dropdown-desc">Actualizar credenciales</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-custom" href="configuracion/">
                                <i class="fas fa-cog dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Configuración</span>
                                    <small class="dropdown-desc">Preferencias del sistema</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-custom logout-item" href="logout.php" onclick="confirmarLogout()">
                                <i class="fas fa-sign-out-alt dropdown-icon"></i>
                                <div class="dropdown-content">
                                    <span class="dropdown-title">Cerrar Sesión</span>
                                    <small class="dropdown-desc">Salir del sistema</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ==============================
     NAVBAR MÓVIL (SIDEBAR)
     ============================== -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="mobile-sidebar" id="mobileSidebar">
    <!-- Header del sidebar -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-logo-container">
                <img src="../../backend/img/ICONO-SINADECI.ico" alt="SINADECI" class="navbar-logo">
            </div>
            <span class="sidebar-brand-text">SINADECI</span>
        </div>
        <button class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Perfil en sidebar -->
    <div class="sidebar-profile">
        <div class="sidebar-profile-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-profile-name"><?= htmlspecialchars($usuario_nombre) ?></div>
        <div class="sidebar-profile-role"><?= htmlspecialchars($tipo_usuario) ?></div>
    </div>

    <!-- Menú del sidebar -->
    <div class="sidebar-menu">
        <!-- Escritorio -->
        <div class="sidebar-menu-item">
            <a href="escritorio.php" class="sidebar-menu-link active">
                <div class="sidebar-menu-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <span class="sidebar-menu-text">Escritorio</span>
            </a>
        </div>

        <!-- Certificaciones -->
        <div class="sidebar-menu-item">
            <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#certSubmenu" aria-expanded="false">
                <div class="sidebar-menu-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <span class="sidebar-menu-text">Certificaciones</span>
                <div class="sidebar-menu-arrow">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
            <div class="sidebar-submenu collapse" id="certSubmenu">
                <a href="certificaciones/lista.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-list"></i></div>
                    Listar Certificaciones
                </a>
<!--                 <a href="certificaciones/nueva.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-plus"></i></div>
                    Nueva Certificación
                </a>
                <a href="certificaciones/buscar.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-search"></i></div>
                    Buscar
                </a> -->
                <a href="inspecciones/lista.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-clipboard-list"></i></div>
                    Lista de Inspecciones
                </a>
                <a href="inspecciones/programar.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-calendar-plus"></i></div>
                    Programar Inspección
                </a>
                <a href="inspecciones/pendientes.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-exclamation-circle"></i></div>
                    Pendientes
                </a>
            </div>
        </div>

        <!-- Reportes -->
        <div class="sidebar-menu-item">
            <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#reportesSubmenu" aria-expanded="false">
                <div class="sidebar-menu-icon"><i class="fas fa-chart-bar"></i></div>
                <span class="sidebar-menu-text">Reportes</span>
                <div class="sidebar-menu-arrow"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="sidebar-submenu collapse" id="reportesSubmenu">
                <a href="reportes/certificaciones.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-file-pdf"></i></div>
                    Certificaciones
                </a>
                <a href="reportes/inspecciones.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-file-alt"></i></div>
                    Inspecciones
                </a>
                <a href="reportes/estadisticas.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-chart-line"></i></div>
                    Estadísticas
                </a>
                <a href="reportes/dashboard.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-chart-pie"></i></div>
                    Dashboard Avanzado
                </a>
            </div>
        </div>

        <!-- Licencias -->
        <div class="sidebar-menu-item">
            <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#licenciasSubmenu" aria-expanded="false">
                <div class="sidebar-menu-icon"><i class="fas fa-file-contract"></i></div>
                <span class="sidebar-menu-text">Licencias</span>
                <div class="sidebar-menu-arrow"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="sidebar-submenu collapse" id="licenciasSubmenu">
                <a href="licencias/lista.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-list"></i></div>
                    Listar Licencias
                </a>
                <a href="licencias/nueva.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-plus"></i></div>
                    Nueva Licencia
                </a>
                <a href="licencias/renovaciones.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-sync"></i></div>
                    Renovaciones
                </a>
            </div>
        </div>

        <!-- Administrado -->
        <div class="sidebar-menu-item">
            <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#administradoSubmenu" aria-expanded="false">
                <div class="sidebar-menu-icon"><i class="fas fa-users"></i></div>
                <span class="sidebar-menu-text">Administrado</span>
                <div class="sidebar-menu-arrow"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="sidebar-submenu collapse" id="administradoSubmenu">
                <a href="administrado/personas.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-user"></i></div>
                    Personas
                </a>
                <a href="administrado/empresas.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-building"></i></div>
                    Empresas
                </a>
            </div>
        </div>

        <!-- Admin (solo ADMINISTRADOR) -->
        <?php if ($tipo_usuario == 'ADMINISTRADOR'): ?>
            <div class="sidebar-menu-item">
                <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#adminSubmenu" aria-expanded="false">
                    <div class="sidebar-menu-icon"><i class="fas fa-cogs"></i></div>
                    <span class="sidebar-menu-text">Administración</span>
                    <div class="sidebar-menu-arrow"><i class="fas fa-chevron-down"></i></div>
                </button>
                <div class="sidebar-submenu collapse" id="adminSubmenu">
                    <a href="admin/usuarios.php" class="sidebar-submenu-item">
                        <div class="sidebar-submenu-icon"><i class="fas fa-users-cog"></i></div>
                        Usuarios
                    </a>
                    <a href="admin/sistema.php" class="sidebar-submenu-item">
                        <div class="sidebar-submenu-icon"><i class="fas fa-cog"></i></div>
                        Sistema
                    </a>
                    <a href="admin/logs.php" class="sidebar-submenu-item">
                        <div class="sidebar-submenu-icon"><i class="fas fa-file-alt"></i></div>
                        Logs del Sistema
                    </a>
                    <a href="admin/backup.php" class="sidebar-submenu-item">
                        <div class="sidebar-submenu-icon"><i class="fas fa-database"></i></div>
                        Respaldo
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Perfil -->
        <div class="sidebar-menu-item">
            <button class="sidebar-menu-link" data-bs-toggle="collapse" data-bs-target="#perfilSubmenu" aria-expanded="false">
                <div class="sidebar-menu-icon"><i class="fas fa-user"></i></div>
                <span class="sidebar-menu-text">Mi Perfil</span>
                <div class="sidebar-menu-arrow"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="sidebar-submenu collapse" id="perfilSubmenu">
                <a href="perfil/" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-user-cog"></i></div>
                    Configurar Perfil
                </a>
                <a href="perfil/password.php" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-key"></i></div>
                    Cambiar Contraseña
                </a>
                <a href="configuracion/" class="sidebar-submenu-item">
                    <div class="sidebar-submenu-icon"><i class="fas fa-cog"></i></div>
                    Configuración
                </a>
            </div>
        </div>
    </div>

    <!-- Footer del sidebar -->
    <div class="sidebar-footer">
        <a href="#" class="sidebar-logout" onclick="confirmarLogout()">
            <i class="fas fa-sign-out-alt"></i>
            Cerrar Sesión
        </a>
    </div>
</div>

<!-- JavaScript del navbar (incluir automáticamente) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ==============================
    // Fix: Acordeón en sidebar móvil
    // ==============================
    document.addEventListener("DOMContentLoaded", () => {
        const toggles = document.querySelectorAll(".sidebar-menu-link[data-bs-toggle='collapse']");

        toggles.forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                const targetId = this.getAttribute("data-bs-target");
                const target = document.querySelector(targetId);

                // Si ya está abierto, se cierra
                if (target.classList.contains("show")) {
                    const bsTarget = bootstrap.Collapse.getInstance(target);
                    if (bsTarget) bsTarget.hide();
                    return;
                }

                // Si abro uno, cierro los demás
                document.querySelectorAll(".sidebar-submenu.collapse.show").forEach(openMenu => {
                    if (openMenu.id !== targetId.replace("#", "")) {
                        const bsCollapse = bootstrap.Collapse.getInstance(openMenu);
                        if (bsCollapse) bsCollapse.hide();
                    }
                });

                // Abrir el menú actual
                const bsTarget = bootstrap.Collapse.getOrCreateInstance(target);
                bsTarget.show();
            });
        });
    });
</script>

<script src="../../backend/js/navbar/navbar.js"></script>