<?php
session_start();

define('SES_PREFIX', 'triple_');

// Validación de sesión y que sea administrador
if (!isset($_SESSION[SES_PREFIX . 'id'])) {
    header('Location: login.php?timeout=1');
    exit;
}

// Actualizar actividad
if (isset($_SESSION[SES_PREFIX . 'last_activity']) && (time() - $_SESSION[SES_PREFIX . 'last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION[SES_PREFIX . 'last_activity'] = time();

// Datos del usuario
$nombre_usuario = $_SESSION[SES_PREFIX . 'nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIPLE - Seleccione Sistema</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="css/menu.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="sinadeci/backend/img/ICONO-SINADECI.ico">
</head>

<body>
    <!-- Contenedor Principal -->
    <main class="menu-container">
        <div class="menu-content">

            <!-- Header Simple -->
            <header class="menu-header">
                <h1 class="menu-title">Seleccione el sistema al que desea acceder</h1>
            </header>

            <!-- Grid de Sistemas -->
            <div class="systems-grid">

                <!-- SILIF -->
                <a href="silif/frontend/sisvis/escritorio.php" class="system-card silif-card">
                    <div class="card-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="card-badge">SILIF</div>
                    <h2 class="card-title">Licencia de Funcionamiento</h2>
                    <p class="card-description">Gestión y revisión de licencias comerciales emitidas</p>
                    <ul class="card-features">
                        <li><i class="fas fa-check"></i> Emisión de licencias</li>
                        <li><i class="fas fa-check"></i> Seguimiento de trámites</li>
                        <li><i class="fas fa-check"></i> Reportes estadísticos</li>
                    </ul>
                    <div class="card-action">
                        <span>Acceder al sistema</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <!-- MPARTES -->
                <a href="mpartes/frontend/sisvis/escritorio.php" class="system-card mpartes-card">
                    <div class="card-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="card-badge">MPARTES</div>
                    <h2 class="card-title">Mesa de Partes</h2>
                    <p class="card-description">Ingreso, seguimiento y asignación de expedientes documentarios</p>
                    <ul class="card-features">
                        <li><i class="fas fa-check"></i> Registro de documentos</li>
                        <li><i class="fas fa-check"></i> Derivación de expedientes</li>
                        <li><i class="fas fa-check"></i> Control de plazos</li>
                    </ul>
                    <div class="card-action">
                        <span>Acceder al sistema</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <!-- SINADECI -->
                <a href="sinadeci/frontend/sisvis/escritorio.php" class="system-card sinadeci-card">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="card-badge">SINADECI</div>
                    <h2 class="card-title">Defensa Civil</h2>
                    <p class="card-description">Supervisión de seguridad y control de riesgos de desastres</p>
                    <ul class="card-features">
                        <li><i class="fas fa-check"></i> Certificaciones ITSDC</li>
                        <li><i class="fas fa-check"></i> Inspecciones técnicas</li>
                        <li><i class="fas fa-check"></i> Gestión de riesgos</li>
                    </ul>
                    <div class="card-action">
                        <span>Acceder al sistema</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

            </div>
        </div>
    </main>

    <!-- Script para animaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.system-card');

            cards.forEach((card, index) => {
                // Animación de entrada con delay
                card.style.animationDelay = `${index * 0.15}s`;

                // Efectos hover mejorados
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>

</html>