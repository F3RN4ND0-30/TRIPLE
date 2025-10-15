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
$empresas = [
  ["id" => "000066",  "ruc" => "20498189637", "razon" => "AREQUIPA EXPRESO MARVISUR EIRL", "encargado" => "GARCIA PEREZ GONZALO"],
  ["id" => "000065",  "ruc" => "20104624104", "razon" => "CURPISCO S.A.C.", "encargado" => "LOZA CABALLERO LUCIA"],
  ["id" => "000294",  "ruc" => "10702036939", "razon" => "ORELLANA MOYANO CYNTHYA RUBI JHERALDINE", "encargado" => "FLORES PEDRA ALEJANDRO"],
  ["id" => "000293",  "ruc" => "10222917986", "razon" => "CARMEN ANDRADE DE MALDONADO ZORAYA", "encargado" => "GONZALES SOUZA CECILIA"],
  ["id" => "000292",  "ruc" => "10412778044", "razon" => "CASAVILCA CCENTE LIDIA", "encargado" => "SOLAR TELLO LUCIANA"],
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SINADECI — Empresas</title>

  <!-- Frameworks -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Estilos de módulo -->
  <link rel="stylesheet" href="../../backend/css/administrado/empresas.css" />
  <link rel="icon" type="image/png" href="../../backend/img/ICONO-SINADECI.ico" />

  <!-- Libs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="description" content="Gestión de empresas registradas" />
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
                <i class="fas fa-building"></i>
              </div>
            </div>
            <div class="hero-text">
              <h1 class="hero-title">Empresas</h1>
              <p class="hero-subtitle">Listado de empresas registradas</p>
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
        </div>

        <div class="toolbar-content">
          <div class="filters-row">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-1"></i>
                Campo de búsqueda
              </label>
              <select class="form-select" id="filtroCampo">
                <option value="ruc">RUC</option>
                <option value="dni">Razón Social</option>
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
          </div>

          <div class="toolbar-actions">
            <div class="action-buttons">
              <button class="btn btn-outline-exportpdf btn-sm" id="btnExportar">
                <i class="fas fa-file-pdf me-1"></i>
                PDF
              </button>
              <button class="btn btn-outline-exportexcel btn-sm" id="btnExportar">
                <i class="fas fa-file-excel me-1"></i>
                Excel
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
            <h5 class="mb-0">Listado de Empresas</h5>
          </div>
          <div class="listado-stats">
            <span class="stats-badge">
              <i class="fas fa-file-alt me-1"></i>
              <?= count($empresas) ?> empresas
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
                      # Id
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="ruc">
                      RUC
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="dni">
                      Razón Social
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="nombre">
                      Encargado
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($empresas as $c): ?>
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
                        <span class="date-badge"><?= $c['ruc'] ?></span>
                      </td>
                      <td>
                        <span class="razon-text"><?= $c['razon'] ?></span>
                      </td>
                      <td>
                        <span class="ruc-text"><?= $c['encargado'] ?></span>
                      </td>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Vista Cards -->
          <div class="cards-view d-none" id="contenedorCards">
            <div class="cards-grid">
              <?php foreach ($empresas as $c): ?>
                <div class="cert-card" data-id="<?= $c['id'] ?>">
                  <div class="cert-card-body">
                    <h6 class="company-name" title="<?= htmlspecialchars($c['razon']) ?>">
                      <?= htmlspecialchars($c['razon']) ?>
                    </h6>
                    <p class="company-address" title="<?= htmlspecialchars($c['encargado']) ?>">
                      <i class="fas fa-user me-1"></i>
                      <?= htmlspecialchars($c['encargado']) ?>
                    </p>

                    <div class="cert-metrics">
                      <div class="metric">
                        <i class="fas fa-id-card"></i>
                        <span><?= $c['ruc'] ?></span>
                      </div>
                    </div>
                  </div>

                  <div class="cert-card-actions">
                    <button class="btn btn-primary btn-sm flex-fill action-btn" data-action="empresa">
                      <i class="fas fa-plus me-1"></i>
                      Empresas
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
            Mostrando <strong>1-<?= count($empresas) ?></strong> de <strong><?= count($empresas) ?></strong> resultados
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

      <!-- FAB: Nueva empresa -->
      <button class="fab-button" id="fabNueva" title="Nueva Empresa (Ctrl+N)">
        <i class="fas fa-plus"></i>
        <span class="fab-tooltip">Nueva Empresa</span>
      </button>
    </div>
  </main>

  <!-- MODALES -->
  <!-- Modal: Detalle -->
  <div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content modern-modal">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-file-alt me-2"></i>
            Detalle de la Empresa
            <span id="detId" class="text-primary ms-2"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="detail-grid">
            <div class="detail-item">
              <label class="detail-label">Nombre</label>
              <div class="detail-value" id="detNombre">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">DNI</label>
              <div class="detail-value" id="detDni">—</div>
            </div>
            <div class="detail-item">
              <label class="detail-label">RUC</label>
              <div class="detail-value" id="detRuc">—</div>
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

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../backend/js/administrado/empresas.js"></script>
</body>

</html>