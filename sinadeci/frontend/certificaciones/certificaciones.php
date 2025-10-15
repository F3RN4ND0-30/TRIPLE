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

/* ---------- Datos de usuario ---------- */
$nombre_usuario = $_SESSION['sinadeci_nombre'] ?? 'Usuario';
$tipo_usuario   = $_SESSION['sinadeci_tipo']   ?? 'Usuario';

/* ---------- Datos de demostración (solo visual) ---------- */
$certificaciones = [
  ["id" => "000066", "emision" => "22/09/2025", "ruc" => "20498189637", "razon" => "AREQUIPA EXPRESO MARVISUR EIRL", "direccion" => "AV. LAS AMÉRICAS N°1915", "area" => "320.00", "aforo" => "15", "riesgo" => "MEDIO"],
  ["id" => "000065", "emision" => "22/09/2025", "ruc" => "20104624104", "razon" => "CURPISCO S.A.C.", "direccion" => "AV. FERMÍN TANGUIS N°790", "area" => "10000.00", "aforo" => "65", "riesgo" => "ALTO"],
  ["id" => "000294", "emision" => "22/09/2025", "ruc" => "10702036939", "razon" => "ORELLANA MOYANO CYNTHYA RUBI JHERALDINE", "direccion" => "CALLE MUELLE N°220", "area" => "40.00", "aforo" => "10", "riesgo" => "BAJO"],
  ["id" => "000293", "emision" => "22/09/2025", "ruc" => "10222917986", "razon" => "CARMEN ANDRADE DE MALDONADO ZORAYA", "direccion" => "AV. LAS AMÉRICAS MZ. C LT.9", "area" => "18.00", "aforo" => "4", "riesgo" => "BAJO"],
  ["id" => "000292", "emision" => "22/09/2025", "ruc" => "10412778044", "razon" => "CASAVILCA CCENTE LIDIA", "direccion" => "PASAJE LOS LIRIOS N°130", "area" => "20.00", "aforo" => "6", "riesgo" => "BAJO"],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SINADECI — Certificaciones</title>

  <!-- Frameworks -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Estilos de módulo -->
  <link rel="stylesheet" href="../../backend/css/certificaciones/certificaciones.css" />
  <link rel="icon" type="image/png" href="../sinadeci/backend/img/ICONO-SINADECI.ico" />

  <!-- Libs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="description" content="Listado y gestión de certificaciones SINADECI" />
</head>

<body class="dashboard-body">

  <?php include '../navbar/navbar.php'; ?>

  <!-- Espaciador para navbar fijo -->
  <div class="navbar-spacer"></div>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="main-container">
    <div class="content-wrapper">

      <!-- HERO SECTION -->
      <section class="hero-section">
        <div class="hero-content">
          <div class="hero-info">
            <div class="hero-icon-wrapper">
              <div class="hero-icon">
                <i class="fas fa-award"></i>
              </div>
            </div>
            <div class="hero-text">
              <h1 class="hero-title">Certificaciones</h1>
              <p class="hero-subtitle">Gestión y emisión de certificaciones de seguridad</p>
            </div>
          </div>

          <div class="hero-actions">
            <div class="user-info">
              <div class="user-details">
                <span class="user-name">
                  <i class="fas fa-user-circle me-2"></i>
                  <?= htmlspecialchars($nombre_usuario) ?>
                </span>
                <span class="user-role badge bg-primary">
                  <?= htmlspecialchars($tipo_usuario) ?>
                </span>
              </div>
            </div>
            <div class="datetime-info">
              <div class="datetime-item">
                <i class="far fa-calendar me-2"></i>
                <span id="currentDate">—</span>
              </div>
              <div class="datetime-item">
                <i class="far fa-clock me-2"></i>
                <span id="currentTime">—</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TOOLBAR SECTION -->
      <section class="toolbar-section">
        <div class="toolbar-header">
          <div class="toolbar-title">
            <i class="fas fa-filter me-2"></i>
            <h6 class="mb-0">Herramientas de Filtrado</h6>
          </div>
          <button class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros">
            <i class="fas fa-sliders me-1"></i>
            Filtros Avanzados
          </button>
        </div>

        <div class="toolbar-content">
          <div class="filters-row">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-1"></i>
                Campo de búsqueda
              </label>
              <select class="form-select" id="filtroCampo">
                <option value="razon">Razón Social</option>
                <option value="ruc">RUC</option>
                <option value="direccion">Dirección</option>
                <option value="id"># Certificado</option>
              </select>
            </div>

            <div class="filter-group search-group">
              <label class="filter-label">
                <i class="fas fa-magnifying-glass me-1"></i>
                Buscar
              </label>
              <div class="search-input-group">
                <input type="text" class="form-control" id="buscarTexto" placeholder="Escribe para buscar...">
                <button class="btn btn-primary" id="btnBuscar">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="far fa-calendar me-1"></i>
                Fecha desde
              </label>
              <input type="date" class="form-control" id="fechaDesde">
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="far fa-calendar-check me-1"></i>
                Fecha hasta
              </label>
              <input type="date" class="form-control" id="fechaHasta">
            </div>
          </div>

          <div class="toolbar-actions">
            <div class="action-buttons">
              <button class="btn btn-outline-secondary btn-sm" id="btnColumnas">
                <i class="fas fa-table-columns me-1"></i>
                Columnas
              </button>
              <button class="btn btn-outline-secondary btn-sm" id="btnExportar">
                <i class="fas fa-file-export me-1"></i>
                Exportar
              </button>
              <button class="btn btn-outline-danger btn-sm" id="btnLimpiar">
                <i class="fas fa-broom me-1"></i>
                Limpiar
              </button>
            </div>

            <div class="view-toggle">
              <span class="view-label">Vista:</span>
              <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="vista" id="vistaTabla" checked>
                <label class="btn btn-outline-primary" for="vistaTabla">
                  <i class="fas fa-table"></i>
                </label>
                <input type="radio" class="btn-check" name="vista" id="vistaTarjetas">
                <label class="btn btn-outline-primary" for="vistaTarjetas">
                  <i class="fas fa-grip"></i>
                </label>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- LISTADO SECTION -->
      <section class="listado-section">
        <div class="listado-header">
          <div class="listado-title">
            <i class="fas fa-list me-2"></i>
            <h5 class="mb-0">Listado de Certificaciones</h5>
          </div>
          <div class="listado-stats">
            <span class="stats-badge">
              <i class="fas fa-file-alt me-1"></i>
              <?= count($certificaciones) ?> certificaciones
            </span>
            <span class="stats-badge">
              <i class="fas fa-eye me-1"></i>
              Página 1 de 1
            </span>
          </div>
        </div>

        <div class="listado-content">
          <!-- Vista Tabla -->
          <div class="table-view" id="contenedorTabla">
            <div class="table-container">
              <table class="table table-hover modern-table" id="tablaCert">
                <thead class="table-header">
                  <tr>
                    <th class="checkbox-col">
                      <input class="form-check-input" type="checkbox" id="checkAll">
                    </th>
                    <th class="sortable" data-sort="id">
                      # Certificado
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="emision">
                      Emisión
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="ruc">
                      RUC
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="razon">
                      Razón Social
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th>Dirección</th>
                    <th class="text-end sortable" data-sort="area">
                      Área (m²)
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="text-end sortable" data-sort="aforo">
                      Aforo
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th>Riesgo</th>
                    <th class="actions-col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($certificaciones as $c): ?>
                    <tr class="table-row" data-id="<?= $c['id'] ?>">
                      <td class="checkbox-col">
                        <input class="form-check-input row-check" type="checkbox">
                      </td>
                      <td>
                        <a href="#" class="cert-link" data-id="<?= $c['id'] ?>">
                          <span class="cert-number"><?= $c['id'] ?></span>
                        </a>
                      </td>
                      <td>
                        <span class="date-badge"><?= $c['emision'] ?></span>
                      </td>
                      <td>
                        <span class="ruc-text"><?= $c['ruc'] ?></span>
                      </td>
                      <td>
                        <div class="company-info">
                          <span class="company-name" title="<?= htmlspecialchars($c['razon']) ?>">
                            <?= htmlspecialchars($c['razon']) ?>
                          </span>
                        </div>
                      </td>
                      <td>
                        <span class="address-text" title="<?= htmlspecialchars($c['direccion']) ?>">
                          <?= htmlspecialchars($c['direccion']) ?>
                        </span>
                      </td>
                      <td class="text-end">
                        <span class="metric-value"><?= $c['area'] ?></span>
                      </td>
                      <td class="text-end">
                        <span class="metric-value"><?= $c['aforo'] ?></span>
                      </td>
                      <td>
                        <?php
                        $riesgo = strtoupper($c['riesgo']);
                        $badge_class = $riesgo === 'ALTO' ? 'danger' : ($riesgo === 'MEDIO' ? 'warning' : 'success');
                        ?>
                        <span class="risk-badge risk-<?= strtolower($riesgo) ?>">
                          <?= $riesgo ?>
                        </span>
                      </td>
                      <td class="actions-col">
                        <div class="action-buttons-mini">
                          <button class="btn btn-primary btn-sm action-btn" data-action="inspeccion" title="Agregar Inspección">
                            <i class="fas fa-plus"></i>
                          </button>
                          <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                              <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item action-item" data-action="detalle"><i class="fas fa-eye me-2"></i>Ver Detalle</a></li>
                              <li><a class="dropdown-item action-item" data-action="ver-inspecciones"><i class="fas fa-search me-2"></i>Ver Inspecciones</a></li>
                              <li><a class="dropdown-item action-item" data-action="agregar-obs"><i class="fas fa-comment me-2"></i>Observaciones</a></li>
                              <li>
                                <hr class="dropdown-divider">
                              </li>
                              <li><a class="dropdown-item action-item" data-action="emitir"><i class="fas fa-file-signature me-2"></i>Emitir</a></li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Vista Cards -->
          <div class="cards-view d-none" id="contenedorCards">
            <div class="cards-grid">
              <?php foreach ($certificaciones as $c): ?>
                <div class="cert-card" data-id="<?= $c['id'] ?>">
                  <div class="cert-card-header">
                    <div class="cert-info">
                      <span class="cert-id">#<?= $c['id'] ?></span>
                      <span class="cert-date">
                        <i class="far fa-calendar me-1"></i>
                        <?= $c['emision'] ?>
                      </span>
                    </div>
                    <?php
                    $riesgo = strtoupper($c['riesgo']);
                    $badge_class = $riesgo === 'ALTO' ? 'danger' : ($riesgo === 'MEDIO' ? 'warning' : 'success');
                    ?>
                    <span class="risk-badge risk-<?= strtolower($riesgo) ?>">
                      <?= $riesgo ?>
                    </span>
                  </div>

                  <div class="cert-card-body">
                    <h6 class="company-name" title="<?= htmlspecialchars($c['razon']) ?>">
                      <?= htmlspecialchars($c['razon']) ?>
                    </h6>
                    <p class="company-address" title="<?= htmlspecialchars($c['direccion']) ?>">
                      <i class="fas fa-map-marker-alt me-1"></i>
                      <?= htmlspecialchars($c['direccion']) ?>
                    </p>

                    <div class="cert-metrics">
                      <div class="metric">
                        <i class="fas fa-id-card"></i>
                        <span><?= $c['ruc'] ?></span>
                      </div>
                      <div class="metric">
                        <i class="fas fa-expand"></i>
                        <span><?= $c['area'] ?> m²</span>
                      </div>
                      <div class="metric">
                        <i class="fas fa-users"></i>
                        <span><?= $c['aforo'] ?> pers.</span>
                      </div>
                    </div>
                  </div>

                  <div class="cert-card-actions">
                    <button class="btn btn-primary btn-sm flex-fill action-btn" data-action="inspeccion">
                      <i class="fas fa-plus me-1"></i>
                      Inspección
                    </button>
                    <div class="dropdown">
                      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item action-item" data-action="detalle"><i class="fas fa-eye me-2"></i>Ver Detalle</a></li>
                        <li><a class="dropdown-item action-item" data-action="ver-inspecciones"><i class="fas fa-search me-2"></i>Ver Inspecciones</a></li>
                        <li><a class="dropdown-item action-item" data-action="agregar-obs"><i class="fas fa-comment me-2"></i>Observaciones</a></li>
                        <li>
                          <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item action-item" data-action="emitir"><i class="fas fa-file-signature me-2"></i>Emitir</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div class="listado-footer">
          <div class="pagination-info">
            Mostrando <strong>1-<?= count($certificaciones) ?></strong> de <strong><?= count($certificaciones) ?></strong> resultados
          </div>
          <nav class="pagination-nav">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled">
                <span class="page-link">
                  <i class="fas fa-angle-double-left"></i>
                </span>
              </li>
              <li class="page-item disabled">
                <span class="page-link">
                  <i class="fas fa-angle-left"></i>
                </span>
              </li>
              <li class="page-item active">
                <span class="page-link">1</span>
              </li>
              <li class="page-item disabled">
                <span class="page-link">
                  <i class="fas fa-angle-right"></i>
                </span>
              </li>
              <li class="page-item disabled">
                <span class="page-link">
                  <i class="fas fa-angle-double-right"></i>
                </span>
              </li>
            </ul>
          </nav>
        </div>
      </section>

      <!-- FAB: Nueva certificación -->
      <button class="fab-button" id="fabNueva" title="Nueva certificación (Ctrl+N)">
        <i class="fas fa-plus"></i>
        <span class="fab-tooltip">Nueva certificación</span>
      </button>
    </div>
  </main>

  <!-- OFFCANVAS: Filtros avanzados -->
  <div class="offcanvas offcanvas-end modern-offcanvas" tabindex="-1" id="offcanvasFiltros">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">
        <i class="fas fa-sliders me-2"></i>
        Filtros Avanzados
      </h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div class="filter-section">
        <label class="form-label">Nivel de Riesgo</label>
        <select class="form-select">
          <option value="">Todos los niveles</option>
          <option>BAJO</option>
          <option>MEDIO</option>
          <option>ALTO</option>
        </select>
      </div>
      <div class="filter-section">
        <label class="form-label">Aforo mínimo</label>
        <input type="number" class="form-control" placeholder="Ej. 10 personas">
      </div>
      <div class="filter-section">
        <label class="form-label">Área mínima (m²)</label>
        <input type="number" class="form-control" placeholder="Ej. 100 m²">
      </div>
      <div class="filter-section">
        <label class="form-label">Estado</label>
        <select class="form-select">
          <option value="">Todos los estados</option>
          <option>Activo</option>
          <option>Pendiente</option>
          <option>Vencido</option>
        </select>
      </div>
      <div class="offcanvas-actions">
        <button class="btn btn-primary w-100 mb-2">
          <i class="fas fa-filter me-1"></i>
          Aplicar Filtros
        </button>
        <button class="btn btn-outline-secondary w-100" data-bs-dismiss="offcanvas">
          Cancelar
        </button>
      </div>
    </div>
  </div>

  <!-- MODALES -->
  <!-- Modal: Detalle -->
  <div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content modern-modal">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-file-alt me-2"></i>
            Detalle de Certificación
            <span id="detId" class="text-primary ms-2"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="detail-grid">
            <div class="detail-item">
              <label class="detail-label">Razón Social</label>
              <div class="detail-value" id="detRazon">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">RUC</label>
              <div class="detail-value" id="detRuc">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">Fecha de Emisión</label>
              <div class="detail-value" id="detEmision">—</div>
            </div>
            <div class="detail-item detail-full">
              <label class="detail-label">Dirección</label>
              <div class="detail-value" id="detDireccion">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">Área (m²)</label>
              <div class="detail-value" id="detArea">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">Aforo</label>
              <div class="detail-value" id="detAforo">—</div>
            </div>
          </div>

          <div class="modal-actions">
            <h6 class="mb-3">Acciones Disponibles</h6>
            <div class="action-buttons-grid">
              <button class="btn btn-outline-primary action-btn" data-action="inspeccion">
                <i class="fas fa-plus-circle me-1"></i>
                Agregar Inspección
              </button>
              <button class="btn btn-outline-secondary action-btn" data-action="ver-inspecciones">
                <i class="fas fa-search me-1"></i>
                Ver Inspecciones
              </button>
              <button class="btn btn-outline-secondary action-btn" data-action="agregar-obs">
                <i class="fas fa-comment me-1"></i>
                Observaciones
              </button>
              <button class="btn btn-success action-btn" data-action="emitir">
                <i class="fas fa-file-signature me-1"></i>
                Emitir Certificado
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Inspección -->
  <div class="modal fade" id="modalInspeccion" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content modern-modal">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-calendar-plus me-2"></i>
            Programar Inspección
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Certificación</label>
            <input type="text" class="form-control" id="inspCert" readonly>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Fecha de Inspección</label>
              <input type="date" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Hora</label>
              <input type="time" class="form-control" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Inspector Asignado</label>
            <select class="form-select">
              <option>Seleccionar inspector...</option>
              <option>Inspector 1 - Juan Pérez</option>
              <option>Inspector 2 - María García</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Notas Adicionales</label>
            <textarea class="form-control" rows="3" placeholder="Observaciones para la inspección..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-save me-1"></i>
            Programar
          </button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Observaciones -->
  <div class="modal fade" id="modalObs" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content modern-modal">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-comments me-2"></i>
            Gestionar Observaciones
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Certificación</label>
            <input type="text" class="form-control" id="obsCert" readonly>
          </div>
          <div class="form-group">
            <label class="form-label">Nueva Observación</label>
            <textarea class="form-control" rows="3" placeholder="Describe la observación..."></textarea>
          </div>
          <div class="observations-history">
            <h6 class="mb-2">Historial de Observaciones</h6>
            <div class="obs-list" id="obsHistorial">
              <div class="obs-item">
                <div class="obs-content">No hay observaciones registradas</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-plus me-1"></i>
            Agregar Observación
          </button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
            Cerrar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Emitir -->
  <div class="modal fade" id="modalEmitir" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content modern-modal">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-file-signature me-2"></i>
            Emitir Certificado
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Certificación</label>
            <input type="text" class="form-control" id="emitCert" readonly>
          </div>
          <div class="form-group">
            <label class="form-label">Fecha de Emisión</label>
            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Archivo PDF (Opcional)</label>
            <input type="file" class="form-control" accept="application/pdf">
            <div class="form-text">Adjunta el documento del certificado en formato PDF</div>
          </div>
          <div class="form-check-group">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="emitFirmar">
              <label class="form-check-label" for="emitFirmar">
                <i class="fas fa-signature me-1"></i>
                Firmar digitalmente
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="emitNotificar" checked>
              <label class="form-check-label" for="emitNotificar">
                <i class="fas fa-envelope me-1"></i>
                Notificar por correo electrónico
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">
            <i class="fas fa-file-signature me-1"></i>
            Emitir Certificado
          </button>
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../backend/js/certificaciones/certificaciones.js"></script>
</body>

</html>