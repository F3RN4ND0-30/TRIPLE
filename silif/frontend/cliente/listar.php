<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
    <link rel="stylesheet" href="../../backend/css/cliente/listar.css">
</head>

<body>
    <div class="container">
        <div class="tarjeta">
            <h1>Listado de Clientes</h1>

            <!-- Buscador con select -->
            <div class="buscador-unificado">
                <select id="criterio-busqueda">
                    <option value="documento-dni">DNI</option>
                    <option value="documento-ruc">RUC</option>
                    <option value="cliente-nombre">Apellidos y Nombres</option>
                    <option value="cliente-razon">Razón Social</option>
                </select>
                <input type="text" id="input-busqueda" placeholder="Buscar..." maxlength="50">
            </div>

            <!-- Tabla de clientes -->
            <div class="tabla-container">
                <table id="tabla-clientes">
                    <thead>
                        <tr>
                            <th>Num Documento</th>
                            <th>Cliente</th>
                            <th>Celular</th>
                            <th>Dirección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>12345678</td>
                            <td>Juan Pérez García</td>
                            <td>987654321</td>
                            <td>Av. Los Álamos 123</td>
                        </tr>
                        <tr>
                            <td>10458796321</td>
                            <td>Constructora Lima S.A.C.</td>
                            <td>912345678</td>
                            <td>Jr. Arequipa 456</td>
                        </tr>
                        <tr>
                            <td>87654321</td>
                            <td>María López Ruiz</td>
                            <td>998877665</td>
                            <td>Calle Falsa 123</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../backend/js/cliente/listar.js"></script>
</body>

</html>