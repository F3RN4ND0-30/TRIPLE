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
?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SINADECI — Estadísticas</title>

  <!-- Frameworks -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Estilos de módulo -->
  <link rel="stylesheet" href="../../backend/css/reportes/estadisticas.css" />
  <link rel="icon" type="image/png" href="../../backend/img/ICONO-SINADECI.ico" />

  <!-- Libs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="description" content="Estadísticas de reportes" />
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
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
            <div class="hero-text">
              <h1 class="hero-title">Estadísticas</h1>
              <p class="hero-subtitle">Gráficos y Análisis de Datos</p>
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
  <div class="container-fluid">
    <div class="row g-3 mb-4">
      <div class="col-xl-6 col-lg-6 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="container">
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-lg-6 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="graphLine">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-xl-5 col-lg-12 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="pieChart">
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-7 col-lg-6 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="areaChart">
            </div>
          </div>
        </div>
      </div>
      
    </div>
    <div class="row g-3 mb-4">
    <div class="col-xl-6 col-lg-6 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="stackedChart">
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-lg-6 col-md-12">
        <div class="card h-100">
          <div class="card-body">
            <div id="radarChart">
            </div>
          </div>
        </div>
  </div>

<!-- SCRIPTS DE HIGHCHARTS -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://code.highcharts.com/themes/sand-signika.js"></script>

<script>
// GRÁFICO DE BARRAS
Highcharts.chart('container', {
    chart: { type: 'column' },
    title: { text: 'Certificaciones por mes' },
    xAxis: { categories: ['Ene','Feb','Mar','Abr','May','Jun'] },
    yAxis: { title: { text: 'Cantidad' } },
    //poner 2 series
    series: [
        { name: 'Certificaciones', data: [5,7,3,4,6,8], color:'#0e7d63ff'},
        { name: 'Rechazos', data: [2,3,2,1,4,3], color:'#120957ff'},
    ],
    credits: { enabled:false }
});

// GRÁFICO LINEAL
Highcharts.chart('graphLine', {
    chart: { type: 'line' },
    title: { text: 'Tendencia mensual' },
    xAxis: { categories: ['Ene','Feb','Mar','Abr','May','Jun'] },
    yAxis: { title: { text: 'Cantidad' } },
    series: [{ name:'Certificaciones', data:[5,7,3,4,6,8], color:'#3f9276ff' }],
    credits:{enabled:false}
});

// GRÁFICO PASTEL
Highcharts.chart('pieChart', {
    chart:{type:'pie'},
    title:{text:'Distribución por tipo'},
    series:[{name:'Certificaciones', colorByPoint:true, data:[
        {name:'Tipo A',y:10, color:'#0e7d63ff'},{name:'Tipo B',y:15, color:'#120957ff'},{name:'Tipo C',y:5, color:'#28826cff'}
    ]}],
    credits:{enabled:false}
});

// GRÁFICO DE ÁREA
Highcharts.chart('areaChart', {
    chart:{type:'area'},
    title:{text:'Tendencia acumulada'},
    xAxis:{categories:['Ene','Feb','Mar','Abr','May','Jun']},
    yAxis:{title:{text:'Cantidad'}},
    series:[{name:'Certificaciones',data:[5,12,15,19,25,33], color:'#2a8a83ff'}],
    credits:{enabled:false}
});

// GRÁFICO COLUMNAS APILADAS
Highcharts.chart('stackedChart', {
    chart:{type:'column'},
    title:{text:'Columnas apiladas por categoría'},
    xAxis:{categories:['Ene','Feb','Mar','Abr','May','Jun']},
    yAxis:{min:0,title:{text:'Cantidad'},stackLabels:{enabled:true}},
    plotOptions:{column:{stacking:'normal',dataLabels:{enabled:true}}},
    series:[
        {name:'Tipo A',data:[5,3,4,7,2,3],color:'#419112ff'},
        {name:'Tipo B',data:[2,2,3,2,1,4],color:'#2848a7ff'},
        {name:'Tipo C',data:[3,4,4,2,5,2],color:'#28826cff'}
    ],
    credits:{enabled:false}
});
Highcharts.chart('radarChart', {
    chart: { polar: true, type: 'line' },
    title: { text: 'Rendimiento por categoría' },
    xAxis: { categories: ['Categoría A', 'Categoría B', 'Categoría C', 'Categoría D', 'Categoría E'], tickmarkPlacement: 'on', lineWidth: 0 },
    yAxis: { gridLineInterpolation: 'polygon', lineWidth: 0, min: 0 },
    series: [{ name: 'Certificaciones', data: [80, 90, 70, 85, 75], pointPlacement: 'on', color:'#17a2b8' },
        { name: 'Rechazos', data: [20, 10, 30, 15, 25], pointPlacement: 'on', color:'#3575dcff' }
    ],
    credits: { enabled:false }
});


</script>
    <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../backend/js/reportes/estadisticas.js"></script>
</body>

</html>

