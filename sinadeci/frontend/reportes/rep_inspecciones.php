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
  ["id" => "000066", "emision" => "22/09/2025", "razon" => "AREQUIPA EXPRESO MARVISUR EIRL", "direccion" => "AV. LAS AMÉRICAS N°1915", "area" => "320.00", "aforo" => "15", "riesgo" => "MEDIO", "n_expediente"=>"00020261","aprobado" => "", "recibo"=> "0324732024", "costo"=>"180.00"],
  ["id" => "000065", "emision" => "22/09/2025", "razon" => "CURPISCO S.A.C.", "direccion" => "AV. FERMÍN TANGUIS N°790", "area" => "10000.00", "aforo" => "65", "riesgo" => "ALTO", "n_expediente"=>"00020261", "aprobado" => "", "recibo"=> "0564732024", "costo"=>"100.00"],
  ["id" => "000294", "emision" => "22/09/2025", "razon" => "ORELLANA MOYANO CYNTHYA RUBI JHERALDINE", "direccion" => "CALLE MUELLE N°220", "area" => "40.00", "aforo" => "10", "riesgo" => "BAJO", "n_expediente"=>"00020261", "aprobado" => "", "recibo"=> "0324852024", "costo"=>"280.00"],
  ["id" => "000293", "emision" => "22/09/2025", "razon" => "CARMEN ANDRADE DE MALDONADO ZORAYA", "direccion" => "AV. LAS AMÉRICAS MZ. C LT.9", "area" => "18.00", "aforo" => "4", "riesgo" => "BAJO", "n_expediente"=>"00020261", "aprobado" => "", "recibo"=> "0294752024", "costo"=>"120.00"],
  ["id" => "000292", "emision" => "22/09/2025", "razon" => "CASAVILCA CCENTE LIDIA", "direccion" => "PASAJE LOS LIRIOS N°130", "area" => "20.00", "aforo" => "6", "riesgo" => "BAJO", "n_expediente"=>"00020261", "aprobado" => "", "recibo"=> "0363732024", "costo"=>"980.00"],
];
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SINADECI — Reporte de Inspecciones</title>

  <!-- Frameworks -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Estilos de módulo -->
  <link rel="stylesheet" href="../../backend/css/reportes/rep_inspecciones.css" />
  <link rel="icon" type="image/png" href="../../backend/img/ICONO-SINADECI.ico" />

  <!-- Libs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="description" content="Listado y gestión de inspecciones SINADECI" />
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
                <i class="fas fa-file "></i>
              </div>
            </div>
            <div class="hero-text">
              <h1 class="hero-title">Reporte de Inspecciones</h1>
              <p class="hero-subtitle">Generar reporte de inspecciones realizadas</p>
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
                <option value="id"># Inspección</option>
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
              <span class="view-label">Estado:</span>
              <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="estado" id="estadoTodos" checked>
                <label class="btn btn-outline-primary" for="estadoTodos">
                  Todos
                </label>
                <input type="radio" class="btn-check" name="estado" id="estadoActivo" checked>
                <label class="btn btn-outline-primary" for="estadoActivo">
                  Activo
                </label>
                <input type="radio" class="btn-check" name="estado" id="estadoInactivo">
                <label class="btn btn-outline-primary" for="estadoInactivo">
                  Anulado
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
            <h5 class="mb-0">Reporte de Inspecciones</h5>
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
                      # Inspecciones
                      <i class="fas fa-sort ms-1"></i>
                    </th>
                    <th class="sortable" data-sort="emision">
                      Fecha
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
                    <th>N° Expediente</th>
                    <th>Aprobado</th>
                    <th>Recibo</th>
                    <th>Costo</th>
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
                      <td class="text-end">
                        <span class="metric-value"><?= $c['n_expediente'] ?></span>
                      </td>
                      <td class="text-end">
                        <span class="metric-value"><?= $c['aprobado'] ?></span>
                      </td>
                      <td class="text-end">
                        <span class="metric-value"><?= $c['recibo'] ?></span>
                      </td>
                      <td class="text-end">
                        <span class="metric-value"><?= $c['costo'] ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
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
            Detalle de Inspección
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
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../backend/js/certificaciones/certificaciones.js"></script>
</body>

</html>
