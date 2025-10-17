<?php
// Simulación de datos (puedes reemplazar esto con consulta a DB)
$expedientesHoy = 12;
$expedientesTotal = 350;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mesa de Partes</title>
    <link rel="stylesheet" href="../../backend/css/escritorio/escritorio.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Mesa de Partes - Dashboard</h1>
        </header>

        <section class="cards">
            <div class="card">
                <h2>📄 Expedientes Hoy</h2>
                <p id="expedientes-hoy"><?= $expedientesHoy ?></p>
            </div>
            <div class="card">
                <h2>🗂️ Total de Expedientes</h2>
                <p id="expedientes-total"><?= $expedientesTotal ?></p>
            </div>
        </section>

        <footer>
            <p>© <?= date("Y") ?> Mesa de Partes</p>
        </footer>
    </div>

    <script src="../../backend/js/escritorio/escritorio.js"></script>
</body>

</html>