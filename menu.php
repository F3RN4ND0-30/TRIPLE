<?php
session_start();
// Aquí podrías validar si es administrador
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>

    <div class="dashboard-container">
        <h1>Panel de Administración</h1>
        <p>Bienvenido, elige una sección para continuar:</p>

        <div class="card-grid">
            <a href="silif/frontend/sisvis/escritorio.php" class="card">
                <h2>Licencia de Funcionamiento</h2>
                <p>Gestión y revisión de licencias emitidas.</p>
            </a>

            <a href="mpartes/frontend/sisvis/escritorio.php" class="card">
                <h2>Mesa de Partes</h2>
                <p>Ingreso, seguimiento y asignación de expedientes.</p>
            </a>

            <a href="sinadeci/frontend/sisvis/escritorio.php" class="card">
                <h2>Defensa Civil</h2>
                <p>Supervisión de seguridad y control de riesgos.</p>
            </a>
        </div>
    </div>

    <script src="js/menu.js"></script>
</body>

</html>