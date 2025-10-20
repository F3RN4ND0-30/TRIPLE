<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Licencias</title>
    <link rel="stylesheet" href="../../backend/css/licencia/registro.css">
</head>

<body>
    <div class="container">
        <div class="tarjeta">
            <h1>Registro de Licencias</h1>

            <!-- Selector de tipo RUC -->
            <div class="tipo-cliente-selector">
                <label class="tipo-btn">
                    <input type="radio" name="tipo_ruc" value="natural" checked>
                    <span>RUC Natural</span>
                </label>
                <label class="tipo-btn">
                    <input type="radio" name="tipo_ruc" value="juridico">
                    <span>RUC Jurídico</span>
                </label>
            </div>

            <!-- Expediente -->
            <div class="form-grid">
                <div>
                    <label>Nº Expediente</label>
                    <input type="text" id="expediente" placeholder="000123">
                </div>
                <div>
                    <label>Año</label>
                    <input type="number" id="anio" placeholder="2025">
                </div>
            </div>
            <button style="margin-bottom: 20px;">Verificar Expediente</button>

            <!-- Otros campos -->
            <div class="form-grid">
                <div>
                    <label>Nombre</label>
                    <input type="text" placeholder="Nombre o razón social">
                </div>
                <div>
                    <label>Giro</label>
                    <div class="input-btn-group">
                        <input type="text" placeholder="Buscar giro">
                        <button class="btn-secundario">🔍</button>
                    </div>
                </div>

                <div>
                    <label>Dirección</label>
                    <div class="input-btn-group">
                        <input type="text" placeholder="Dirección">
                        <button class="btn-secundario" onclick="abrirModal()">➕</button>
                    </div>
                </div>

                <div>
                    <label>Riesgo</label>
                    <select>
                        <option value="">Seleccionar</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                        <option value="muy_alto">Muy Alto</option>
                    </select>
                </div>

                <div>
                    <label>Área (m²)</label>
                    <input type="number" placeholder="Área en m²">
                </div>
                <div>
                    <label>Aforo</label>
                    <input type="number" placeholder="Cantidad de personas">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <label>Observaciones</label>
                <textarea rows="4" style="width: 100%; border-radius: 10px; border: 1px solid #ccc; padding: 12px;"></textarea>
            </div>

            <button style="margin-top: 30px;">Registrar Licencia</button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="modalDireccion">
        <div class="modal-contenido">
            <h2>Nueva Dirección</h2>
            <div class="form-grid">
                <div>
                    <label>Razón Social</label>
                    <input type="text" placeholder="Nombre o empresa">
                </div>
                <div>
                    <label>Dirección</label>
                    <input type="text" placeholder="Nueva dirección">
                </div>
                <div>
                    <label>Área (m²)</label>
                    <input type="number" placeholder="Área en m²">
                </div>
                <div>
                    <label>Aforo</label>
                    <input type="number" placeholder="Aforo permitido">
                </div>
                <div>
                    <label>Riesgo</label>
                    <select>
                        <option value="">Seleccionar</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                        <option value="muy_alto">Muy Alto</option>
                    </select>
                </div>
            </div>
            <div class="modal-botones">
                <button onclick="cerrarModal()" class="btn-cancelar">Cancelar</button>
                <button class="btn-guardar">Guardar</button>
            </div>
        </div>
    </div>

    <script src="../../backend/js/licencia/registro.js"></script>
</body>

</html>