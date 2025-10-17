<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['sinadeci_id'])) {
    header('Location: ../login.php?timeout=1');
    exit;
}

// Verificar sesión activa
if (
    isset($_SESSION['sinadeci_last_activity']) &&
    (time() - $_SESSION['sinadeci_last_activity'] > 1800)
) {
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}

$_SESSION['sinadeci_last_activity'] = time();

require_once '../../../db/conexion.php';

// Obtener datos del usuario
$usuario_id = $_SESSION['sinadeci_id'];
$nombre_usuario = $_SESSION['sinadeci_nombre'];
$tipo_usuario = $_SESSION['sinadeci_tipo'];

// Función para obtener estadísticas del dashboard
function obtenerEstadisticasDashboard($usuario_id)
{
    // Total de certificaciones
    $sql_cert = "SELECT COUNT(*) as total FROM CERTIFICACIONES WHERE estado = '1'";
    $result_cert = ejecutarConsulta($sql_cert);
    $total_certificaciones = obtenerFila($result_cert)['total'] ?? 0;

    // Certificaciones del mes actual
    $sql_cert_mes = "SELECT COUNT(*) as total FROM CERTIFICACIONES 
                     WHERE estado = '1' AND MONTH(fechaEmision) = MONTH(GETDATE()) 
                     AND YEAR(fechaEmision) = YEAR(GETDATE())";
    $result_cert_mes = ejecutarConsulta($sql_cert_mes);
    $cert_mes = obtenerFila($result_cert_mes)['total'] ?? 0;

    // Total de inspecciones
    $sql_insp = "SELECT COUNT(*) as total FROM INSPECCIONES WHERE estado = '1'";
    $result_insp = ejecutarConsulta($sql_insp);
    $total_inspecciones = obtenerFila($result_insp)['total'] ?? 0;

    // Inspecciones pendientes (fecha futura o hoy)
    $sql_insp_pend = "SELECT COUNT(*) as total FROM INSPECCIONES 
                      WHERE estado = '1' AND CAST(fechaInspeccion AS DATE) >= CAST(GETDATE() AS DATE)";
    $result_insp_pend = ejecutarConsulta($sql_insp_pend);
    $insp_pendientes = obtenerFila($result_insp_pend)['total'] ?? 0;

    // Total de clientes
    $sql_clientes = "SELECT COUNT(*) as total FROM CLIENTES WHERE estado = '1'";
    $result_clientes = ejecutarConsulta($sql_clientes);
    $total_clientes = obtenerFila($result_clientes)['total'] ?? 0;

    // Licencias emitidas este año
    $sql_licencias = "SELECT COUNT(*) as total FROM LICENCIAS 
                      WHERE estado = '1' AND YEAR(FechaLic) = YEAR(GETDATE())";
    $result_licencias = ejecutarConsulta($sql_licencias);
    $licencias_año = obtenerFila($result_licencias)['total'] ?? 0;

    return [
        'total_certificaciones' => $total_certificaciones,
        'cert_mes' => $cert_mes,
        'total_inspecciones' => $total_inspecciones,
        'insp_pendientes' => $insp_pendientes,
        'total_clientes' => $total_clientes,
        'licencias_año' => $licencias_año
    ];
}

// Obtener actividad reciente
function obtenerActividadReciente()
{
    $sql = "SELECT TOP 5 
                c.idCerti,
                c.NmrCertificado,
                c.fechaEmision,
                CONCAT(p.nombres, ' ', p.apePat, ' ', p.apeMat) as cliente,
                r.Nombre as riesgo
            FROM CERTIFICACIONES c
            INNER JOIN CLIENTES cl ON c.idCliente = cl.idCliente
            INNER JOIN PERSONAS p ON cl.idPersona = p.idPersona
            INNER JOIN RIESGOS r ON c.idRiesgo = r.idRiesgo
            WHERE c.estado = '1'
            ORDER BY c.fechaEmision DESC";

    return ejecutarConsulta($sql);
}

$estadisticas = obtenerEstadisticasDashboard($usuario_id);
$actividad_reciente = obtenerActividadReciente();
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SINADECI - Escritorio</title>

    <!-- CSS Framework y fuentes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="../../backend/css/sisvis/escritorio.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="../../backend/img/ICONO-SINADECI.ico" />

    <meta name="description" content="Sistema Nacional de Defensa Civil - Escritorio Administrativo">
</head>

<body class="dashboard-body">
    <!-- Navbar -->
    <?php include '../navbar/navbar.php'; ?>

    <!-- Contenido principal -->
    <main class="contenido-principal">
        <div class="container-fluid">
            <!-- Header del dashboard -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-header d-flex justify-content-between align-items-start">
                        <div class="header-content">
                            <h1 class="page-title">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Escritorio SINADECI
                            </h1>
                            <p class="page-subtitle">
                                Bienvenido, <strong><?= htmlspecialchars($nombre_usuario) ?></strong>
                                <span class="badge bg-primary ms-2"><?= htmlspecialchars($tipo_usuario) ?></span>
                            </p>
                        </div>
                        <div class="header-actions">
                            <span class="fecha-actual">
                                <i class="fas fa-calendar-day me-1"></i>
                                <?= date('d/m/Y') ?>
                            </span>
                            <span class="hora-actual" id="horaActual">
                                <i class="fas fa-clock me-1"></i>
                                <?= date('H:i:s') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card estadistica-card certificaciones">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3 class="stat-number"><?= number_format($estadisticas['total_certificaciones']) ?></h3>
                                    <p class="stat-label">Total Certificaciones</p>
                                    <small class="stat-detail">
                                        <i class="fas fa-calendar-month me-1"></i>
                                        <?= $estadisticas['cert_mes'] ?> este mes
                                    </small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card estadistica-card inspecciones">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3 class="stat-number"><?= number_format($estadisticas['total_inspecciones']) ?></h3>
                                    <p class="stat-label">Total Inspecciones</p>
                                    <small class="stat-detail">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        <?= $estadisticas['insp_pendientes'] ?> pendientes
                                    </small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card estadistica-card clientes">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3 class="stat-number"><?= number_format($estadisticas['total_clientes']) ?></h3>
                                    <p class="stat-label">Total Clientes</p>
                                    <small class="stat-detail">
                                        <i class="fas fa-user-plus me-1"></i>
                                        Registrados
                                    </small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                    <div class="card estadistica-card licencias">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3 class="stat-number"><?= number_format($estadisticas['licencias_año']) ?></h3>
                                    <p class="stat-label">Licencias <?= date('Y') ?></p>
                                    <small class="stat-detail">
                                        <i class="fas fa-file-contract me-1"></i>
                                        Este año
                                    </small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y actividad reciente -->
            <div class="row">
                <!-- Gráfico de certificaciones -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Certificaciones por Mes
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" data-period="6">6M</button>
                                <button class="btn btn-outline-primary" data-period="12">1A</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="chartCertificaciones"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2"></i>
                                Actividad Reciente
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if ($actividad_reciente && mysqli_num_rows($actividad_reciente) > 0): ?>
                                    <?php while ($actividad = obtenerFila($actividad_reciente)): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Cert. #<?= htmlspecialchars($actividad['NmrCertificado']) ?></h6>
                                                <small><?= date('d/m/Y', strtotime($actividad['fechaEmision'])) ?></small>
                                            </div>
                                            <p class="mb-1"><?= htmlspecialchars($actividad['cliente']) ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($actividad['riesgo']) ?>
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No hay actividad reciente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                Ver todas las actividades
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Accesos Rápidos
                            </h5>
                        </div>
                        <div class="card-body accesos-rapidos">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="certificaciones/nueva.php" class="btn btn-outline-success w-100">
                                        <i class="fas fa-plus-circle"></i>
                                        Nueva Certificación
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="inspecciones/programar.php" class="btn btn-outline-info w-100">
                                        <i class="fas fa-calendar-plus"></i>
                                        Programar Inspección
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="reportes/generar.php" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-file-pdf"></i>
                                        Generar Reporte
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="clientes/registro.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-user-plus"></i>
                                        Registrar Cliente
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../backend/js/escritorio.js"></script>

    <script>
        // Inicializar escritorio al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            inicializarEscritorio();
            iniciarRelojEscritorio();
            inicializarGraficos();
        });
    </script>
</body>

</html>