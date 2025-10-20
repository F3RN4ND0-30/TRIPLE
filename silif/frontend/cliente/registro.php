<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Clientes</title>
    <link rel="stylesheet" href="../../backend/css/cliente/registro.css">
</head>

<body>
    <div class="container">
        <h1>Registro de Clientes</h1>

        <!-- Selector de tipo de cliente -->
        <div class="tipo-cliente-selector">
            <label class="tipo-btn">
                <input type="radio" name="tipo_cliente" value="persona" checked>
                Persona
            </label>
            <label class="tipo-btn">
                <input type="radio" name="tipo_cliente" value="empresa">
                Empresa
            </label>
        </div>

        <!-- Formulario para Persona -->
        <form id="form-persona" class="formulario activo">
            <h2>Datos de Persona</h2>
            <div class="form-grid">
                <input type="text" name="dni" placeholder="DNI" required>
                <input type="text" name="apellido_paterno" placeholder="Apellido Paterno" required>

                <input type="text" name="apellido_materno" placeholder="Apellido Materno" required>
                <input type="text" name="nombres" placeholder="Nombres" required>

                <input type="text" name="ruc" placeholder="RUC">
                <input type="text" name="celular" placeholder="Celular">

                <input type="text" name="direccion" placeholder="Dirección">
                <input type="email" name="email" placeholder="Correo Electrónico">

                <select name="departamento" id="dep-persona">
                    <option value="">Seleccione Departamento</option>
                </select>
                <select name="provincia" id="prov-persona" disabled>
                    <option value="">Seleccione Provincia</option>
                </select>

                <select name="distrito" id="dist-persona" disabled>
                    <option value="">Seleccione Distrito</option>
                </select>
            </div>
            <button type="submit">Registrar Persona</button>
        </form>

        <!-- Formulario para Empresa -->
        <form id="form-empresa" class="formulario">
            <h2>Datos de Empresa</h2>
            <div class="form-grid">
                <input type="text" name="razon_social" placeholder="Razón Social" required>
                <input type="text" name="ruc" placeholder="RUC" required>

                <input type="text" name="celular" placeholder="Celular">
                <input type="text" name="direccion" placeholder="Dirección">

                <input type="email" name="email" placeholder="Correo Electrónico">

                <select name="departamento" id="dep-empresa">
                    <option value="">Seleccione Departamento</option>
                </select>
                <select name="provincia" id="prov-empresa" disabled>
                    <option value="">Seleccione Provincia</option>
                </select>

                <select name="distrito" id="dist-empresa" disabled>
                    <option value="">Seleccione Distrito</option>
                </select>
            </div>
            <button type="submit">Registrar Empresa</button>
        </form>
    </div>

    <script src="../../backend/js/cliente/registro.js"></script>
</body>

</html>