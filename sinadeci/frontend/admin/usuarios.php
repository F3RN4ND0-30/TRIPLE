<?php
session_start();

/* ---------- Guardas de sesión ---------- */
if (!isset($_SESSION['sinadeci_id'])) {
    header('Location: ../login.php?timeout=1');
    exit;
}
if (isset($_SESSION['sinadeci_last_activity']) && (time() - $_SESSION['sinadeci_last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}
$_SESSION['sinadeci_last_activity'] = time();

/* ---------- Conexión ---------- */
require_once '../../../db/conexion.php';

/* ---------- Datos del usuario logueado ---------- */
$nombre_usuario = $_SESSION['sinadeci_nombre'] ?? 'Usuario';
$tipo_usuario   = $_SESSION['sinadeci_tipo']   ?? 'Usuario';

/* ---------- Carga de usuarios desde MySQL (según tu esquema real) ---------- */
$usuarios = [];
$sql = "
  SELECT 
    u.idUsuario              AS id,
    u.usuario                AS usuario,
    u.estado                 AS estado_raw,
    u.estadoPass             AS estadoPass_raw,
    CONCAT(p.nombres, ' ', p.apePat, ' ', p.apeMat) AS nombres,
    tu.nombre                AS rol
  FROM USUARIOS u
  INNER JOIN PERSONAS p       ON p.idPersona = u.idPersona
  INNER JOIN TIPO_USUARIOS tu ON tu.idTipo   = u.idTipoUsuario
  ORDER BY u.idUsuario DESC
  LIMIT 200
";
$res = ejecutarConsulta($sql);
while ($row = obtenerFila($res)) {
    $usuarios[] = [
        "id"        => (int)$row['id'],
        "usuario"   => $row['usuario'],
        "nombres"   => $row['nombres'],
        "rol"       => $row['rol'],
        "estado"    => ($row['estado_raw'] === '1' || strtoupper($row['estado_raw']) === 'ACTIVO') ? 'Activo' : 'Suspendido',
        "estadoPass" => ($row['estadoPass_raw'] ?? '') === '1' ? 'Válida' : 'Requiere cambio',
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SINADECI — Usuarios</title>

    <!-- Frameworks -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Estilos del módulo Usuarios (propio) + base del listado -->
    <link rel="stylesheet" href="../../backend/css/certificaciones/certificaciones.css" />
    <link rel="stylesheet" href="../../backend/css/usuarios/usuarios.css" />

    <link rel="icon" type="image/png" href="../../backend/img/ICONO-SINADECI.ico" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="description" content="Gestión de usuarios del sistema SINADECI" />
</head>

<body class="dashboard-body">

    <?php include '../navbar/navbar.php'; ?>
    <div class="navbar-spacer"></div>

    <main class="main-container">
        <div class="content-wrapper">

            <!-- HERO -->
            <section class="hero-section">
                <div class="hero-content">
                    <div class="hero-info">
                        <div class="hero-icon-wrapper">
                            <div class="hero-icon"><i class="fas fa-users-cog"></i></div>
                        </div>
                        <div class="hero-text">
                            <h1 class="hero-title">Usuarios</h1>
                            <p class="hero-subtitle">Gestión de cuentas, roles y accesos del sistema</p>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <div class="user-info">
                            <div class="user-details">
                                <span class="user-name"><i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($nombre_usuario) ?></span>
                                <span class="user-role badge bg-primary"><?= htmlspecialchars($tipo_usuario) ?></span>
                            </div>
                        </div>
                        <div class="datetime-info">
                            <div class="datetime-item"><i class="far fa-calendar me-2"></i><span id="currentDate">—</span></div>
                            <div class="datetime-item"><i class="far fa-clock me-2"></i><span id="currentTime">—</span></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TOOLBAR -->
            <section class="toolbar-section">
                <div class="toolbar-header">
                    <div class="toolbar-title">
                        <i class="fas fa-filter me-2"></i>
                        <h6 class="mb-0">Herramientas de Filtrado</h6>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros">
                        <i class="fas fa-sliders me-1"></i> Filtros Avanzados
                    </button>
                </div>

                <div class="toolbar-content">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-search me-1"></i>Campo de búsqueda</label>
                            <select class="form-select" id="filtroCampo">
                                <option value="usuario">Usuario</option>
                                <option value="nombres">Nombre</option>
                                <option value="rol">Rol</option>
                            </select>
                        </div>

                        <div class="filter-group search-group">
                            <label class="filter-label"><i class="fas fa-magnifying-glass me-1"></i>Buscar</label>
                            <div class="search-input-group">
                                <input type="text" class="form-control" id="buscarTexto" placeholder="Escribe para buscar...">
                                <button class="btn btn-primary" id="btnBuscar"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-user-shield me-1"></i>Rol</label>
                            <select class="form-select" id="filtroRol">
                                <option value="">Todos</option>
                                <option>Administrador</option>
                                <option>Inspector</option>
                                <option>Operador</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos</option>
                                <option>Activo</option>
                                <option>Suspendido</option>
                            </select>
                        </div>
                    </div>

                    <div class="toolbar-actions">
                        <div class="action-buttons">
                            <button class="btn btn-outline-secondary btn-sm" id="btnColumnas"><i class="fas fa-table-columns me-1"></i> Columnas</button>
                            <button class="btn btn-outline-secondary btn-sm" id="btnExportar"><i class="fas fa-file-export me-1"></i> Exportar</button>
                            <button class="btn btn-outline-danger btn-sm" id="btnLimpiar"><i class="fas fa-broom me-1"></i> Limpiar</button>
                        </div>

                        <div class="view-toggle">
                            <span class="view-label">Vista:</span>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="vista" id="vistaTabla" checked>
                                <label class="btn btn-outline-primary" for="vistaTabla"><i class="fas fa-table"></i></label>
                                <input type="radio" class="btn-check" name="vista" id="vistaTarjetas">
                                <label class="btn btn-outline-primary" for="vistaTarjetas"><i class="fas fa-grip"></i></label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- LISTADO -->
            <section class="listado-section">
                <div class="listado-header">
                    <div class="listado-title">
                        <i class="fas fa-list me-2"></i>
                        <h5 class="mb-0">Listado de Usuarios</h5>
                    </div>
                    <div class="listado-stats">
                        <span class="stats-badge"><i class="fas fa-user me-1"></i><?= count($usuarios) ?> usuarios</span>
                        <span class="stats-badge"><i class="fas fa-eye me-1"></i>Página 1 de 1</span>
                    </div>
                </div>

                <div class="listado-content">
                    <!-- TABLA -->
                    <div class="table-view" id="contenedorTabla">
                        <div class="table-container">
                            <table class="table table-hover modern-table" id="tablaUsuarios">
                                <thead class="table-header">
                                    <tr>
                                        <th class="checkbox-col"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                                        <th class="sortable" data-sort="usuario">Usuario <i class="fas fa-sort ms-1"></i></th>
                                        <th class="sortable" data-sort="nombres">Nombre <i class="fas fa-sort ms-1"></i></th>
                                        <th class="sortable" data-sort="rol">Rol <i class="fas fa-sort ms-1"></i></th>
                                        <th class="sortable" data-sort="estado">Estado <i class="fas fa-sort ms-1"></i></th>
                                        <th class="sortable" data-sort="estadoPass">Estado de clave <i class="fas fa-sort ms-1"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr class="table-row fila-user" data-id="<?= $u['id'] ?>">
                                            <td class="checkbox-col"><input class="form-check-input row-check" type="checkbox"></td>
                                            <td>
                                                <a href="#" class="cert-link enlace-detalle" data-id="<?= $u['id'] ?>">
                                                    <span class="cert-number">@<?= htmlspecialchars($u['usuario']) ?></span>
                                                </a>
                                            </td>
                                            <td><span class="company-name razon" title="<?= htmlspecialchars($u['nombres']) ?>"><?= htmlspecialchars($u['nombres']) ?></span></td>
                                            <td><span class="metric-value"><?= htmlspecialchars($u['rol']) ?></span></td>
                                            <td>
                                                <?php $activo = strtoupper($u['estado']) === 'ACTIVO'; ?>
                                                <span class="risk-badge <?= $activo ? 'risk-bajo' : 'risk-alto' ?>"><?= htmlspecialchars($u['estado']) ?></span>
                                            </td>
                                            <td>
                                                <span class="date-badge"><?= htmlspecialchars($u['estadoPass']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- CARDS -->
                    <div class="cards-view d-none" id="contenedorCards">
                        <div class="cards-grid">
                            <?php foreach ($usuarios as $u): ?>
                                <div class="cert-card card-user" data-id="<?= $u['id'] ?>">
                                    <div class="cert-card-header">
                                        <div class="cert-info">
                                            <span class="cert-id">@<?= htmlspecialchars($u['usuario']) ?></span>
                                            <span class="cert-date"><i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($u['rol']) ?></span>
                                        </div>
                                        <?php $activo = strtoupper($u['estado']) === 'ACTIVO'; ?>
                                        <span class="risk-badge <?= $activo ? 'risk-bajo' : 'risk-alto' ?>"><?= htmlspecialchars($u['estado']) ?></span>
                                    </div>
                                    <div class="cert-card-body">
                                        <h6 class="company-name"><?= htmlspecialchars($u['nombres']) ?></h6>
                                        <div class="cert-metrics">
                                            <div class="metric"><i class="fas fa-key"></i><span><?= htmlspecialchars($u['estadoPass']) ?></span></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Paginación (estática demo) -->
                <div class="listado-footer">
                    <div class="pagination-info">Mostrando <strong>1-<?= count($usuarios) ?></strong> de <strong><?= count($usuarios) ?></strong> resultados</div>
                    <nav class="pagination-nav">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-double-left"></i></span></li>
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>
                            <li class="page-item active"><span class="page-link">1</span></li>
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-double-right"></i></span></li>
                        </ul>
                    </nav>
                </div>
            </section>

            <!-- FAB: nuevo usuario -->
            <button class="fab-button" id="fabNueva" title="Nuevo usuario (N)">
                <i class="fas fa-user-plus"></i>
                <span class="fab-tooltip">Nuevo usuario</span>
            </button>
        </div>
    </main>

    <!-- OFFCANVAS filtros -->
    <div class="offcanvas offcanvas-end modern-offcanvas" tabindex="-1" id="offcanvasFiltros" data-bs-backdrop="static">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title"><i class="fas fa-sliders me-2"></i>Filtros Avanzados</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="filter-section">
                <label class="form-label">Rol</label>
                <select class="form-select">
                    <option value="">Todos</option>
                    <option>Administrador</option>
                    <option>Inspector</option>
                    <option>Operador</option>
                </select>
            </div>
            <div class="filter-section">
                <label class="form-label">Estado</label>
                <select class="form-select">
                    <option value="">Todos</option>
                    <option>Activo</option>
                    <option>Suspendido</option>
                </select>
            </div>
            <div class="offcanvas-actions">
                <button class="btn btn-primary w-100 mb-2"><i class="fas fa-filter me-1"></i>Aplicar Filtros</button>
                <button class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- MENÚ CONTEXTUAL (click derecho) -->
    <div id="ctxMenuUser" class="position-fixed d-none" style="z-index:2000;">
        <div class="dropdown-menu show shadow" style="display:block; min-width:260px;">
            <a class="dropdown-item ctx-action" data-action="detalle"><i class="far fa-eye me-2"></i> Ver detalle</a>
            <a class="dropdown-item ctx-action" data-action="editar"><i class="far fa-edit me-2"></i> Editar</a>
            <a class="dropdown-item ctx-action" data-action="reset-pass"><i class="fas fa-key me-2"></i> Restablecer contraseña</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item ctx-action" data-action="toggle-estado"><i class="fas fa-toggle-on me-2"></i> Activar / Desactivar</a>
            <a class="dropdown-item ctx-action" data-action="ver-logs"><i class="fas fa-clipboard-list me-2"></i> Ver logs</a>
        </div>
    </div>

    <!-- MODALES -->
    <!-- Detalle -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-id-card me-2"></i>Detalle de Usuario <span id="detId" class="text-primary ms-2"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Usuario</span>
                            <div class="detail-value" id="detUsuario">—</div>
                        </div>
                        <div class="detail-item"><span class="detail-label">Nombre</span>
                            <div class="detail-value" id="detNombre">—</div>
                        </div>
                        <div class="detail-item"><span class="detail-label">Rol</span>
                            <div class="detail-value" id="detRol">—</div>
                        </div>
                        <div class="detail-item"><span class="detail-label">Estado</span>
                            <div class="detail-value" id="detEstado">—</div>
                        </div>
                        <div class="detail-item"><span class="detail-label">Estado de clave</span>
                            <div class="detail-value" id="detEstadoPass">—</div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <div class="action-buttons-grid">
                            <button class="btn btn-outline-primary accion-modal" data-action="editar"><i class="far fa-edit me-1"></i>Editar</button>
                            <button class="btn btn-outline-secondary accion-modal" data-action="reset-pass"><i class="fas fa-key me-1"></i>Restablecer contraseña</button>
                            <button class="btn btn-success accion-modal" data-action="toggle-estado"><i class="fas fa-toggle-on me-1"></i>Activar / Desactivar</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button></div>
            </div>
        </div>
    </div>

    <!-- Crear/Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="far fa-edit me-2"></i>Gestionar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Usuario</label><input type="text" class="form-control" id="usrUsuario" required></div>
                    <div class="form-group"><label class="form-label">Nombre</label><input type="text" class="form-control" id="usrNombre" required></div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="usrRol" required>
                                <option>Administrador</option>
                                <option>Inspector</option>
                                <option>Operador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="usrEstado" required>
                                <option>Activo</option>
                                <option>Suspendido</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i>Guardar</button>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset contraseña -->
    <div class="modal fade" id="modalReset" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Restablecer contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label class="form-label">Usuario</label><input type="text" class="form-control" id="resetUsuario" readonly></div>
                    <div class="form-group"><label class="form-label">Nueva contraseña</label><input type="password" class="form-control" id="resetPass1" required></div>
                    <div class="form-group"><label class="form-label">Confirmar contraseña</label><input type="password" class="form-control" id="resetPass2" required></div>
                    <div class="form-text">El guardado se hará con hashing seguro (bcrypt) desde PHP.</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-check me-1"></i>Aplicar</button>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../backend/js/admin/usuarios.js"></script>
</body>

</html>