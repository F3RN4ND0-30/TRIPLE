<?php
session_start();
require_once 'db/conexion.php';

define('SES_PREFIX', 'triple_');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
@session_regenerate_id(true);

function responderJSON(array $datos)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function obtenerIP(): string
{
    $server = $_SERVER ?? [];
    $claves = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    foreach ($claves as $k) {
        $ip = $server[$k] ?? '';
        if ($ip === '') continue;
        if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $ip;
    }
    foreach ($claves as $k) {
        $ip = $server[$k] ?? '';
        if ($ip === '') continue;
        if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return $server['REMOTE_ADDR'] ?? '127.0.0.1';
}

function ipBloqueada(string $ip)
{
    $sql = "SELECT COUNT(*) AS total
            FROM dbo.intentos_login
            WHERE ip_address = ? AND tipo = 'fallido'
              AND fecha_registro > DATEADD(MINUTE, -5, GETDATE())";
    $st  = ejecutarConsulta($sql, [$ip]);
    $row = obtenerFila($st);
    if ($row && (int)$row['total'] >= 5) {
        return ['bloqueada' => true, 'intentos' => (int)$row['total']];
    }
    return false;
}

function registrarIntentoEnBD(string $ip, string $usuario, string $tipo): void
{
    $sql = "INSERT INTO dbo.intentos_login (ip_address, usuario, tipo, fecha_registro)
            VALUES (?, ?, ?, GETDATE())";
    ejecutarConsulta($sql, [$ip, $usuario, $tipo]);
}

function obtenerRutaEscritorioPorRol($idTipoUsuario): ?string
{
    switch ((int)$idTipoUsuario) {
        case 1:
            return 'menu.php';
        case 2:
            return '../sinadeci/frontend/sisvis/escritorio.php';
        case 3:
            return '../silif/frontend/sisvis/escritorio.php';
        case 4:
            return '../mpartes/frontend/sisvis/escritorio.php';
        default:
            return null;
    }
}

$SES = SES_PREFIX;

if (isset($_SESSION[$SES . 'id'])) {
    $rolRedirect = $_SESSION[$SES . 'last_redirect'] ?? '../sinadeci/frontend/sisvis/escritorio.php';
    if (isset($_POST['ajax'])) responderJSON(['success' => false, 'redirect' => $rolRedirect]);
    header("Location: " . $rolRedirect);
    exit;
}

$error = '';
$intentos_fallidos = $_SESSION[$SES . 'intentos'] ?? 0;
$bloqueado_usuario = false;
$bloqueado_ip = false;

if (isset($_SESSION[$SES . 'tiempo_bloqueo']) && time() < $_SESSION[$SES . 'tiempo_bloqueo']) {
    $bloqueado_usuario = true;
    $restante = $_SESSION[$SES . 'tiempo_bloqueo'] - time();
    $error = "Usuario bloqueado. Espere " . ceil($restante / 60) . " minutos.";
}

$ip_cliente = obtenerIP();
$bloqueo_ip = ipBloqueada($ip_cliente);
if ($bloqueo_ip) {
    $bloqueado_ip = true;
    $error = "IP bloqueada por 5 minutos.";
}
$bloqueado = $bloqueado_usuario || $bloqueado_ip;

if (isset($_GET['timeout'])) $error = "Sesión expirada por inactividad.";
if (isset($_GET['logout']))  $exito = "Sesión cerrada correctamente.";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $usuario = trim($_POST['username'] ?? '');
    $clave   = $_POST['password'] ?? '';

    if ($bloqueado) {
        responderJSON(['success' => false, 'bloqueado' => true, 'error' => $error, 'tipo' => $bloqueado_ip ? 'ip' : 'usuario']);
    }
    if ($usuario === '' || $clave === '') {
        responderJSON(['success' => false, 'error' => 'Complete todos los campos.']);
    }

    $sql = "SELECT TOP 1 u.*, p.nombres, p.apePat, p.apeMat, tu.nombre AS tipo_usuario
            FROM dbo.USUARIOS u
            INNER JOIN dbo.PERSONAS p       ON p.idPersona = u.idPersona
            INNER JOIN dbo.TIPO_USUARIOS tu ON tu.idTipo   = u.idTipoUsuario
            WHERE u.usuario = ? AND u.estado = '1'";
    $st = ejecutarConsulta($sql, [$usuario]);
    $usuarioDB = obtenerFila($st);

    $login_valido = $usuarioDB ? password_verify($clave, $usuarioDB['contraseña']) : false;
    if (!$usuarioDB) password_verify($clave, '$2y$10$dummy.hash.timing.protection.............');

    if ($login_valido) {
        unset($_SESSION[$SES . 'intentos'], $_SESSION[$SES . 'tiempo_bloqueo']);
        registrarIntentoEnBD($ip_cliente, $usuario, 'exitoso');
        @session_regenerate_id(true);

        $nombre = trim(($usuarioDB['nombres'] ?? '') . ' ' . ($usuarioDB['apePat'] ?? '') . ' ' . ($usuarioDB['apeMat'] ?? ''));

        $_SESSION[$SES . 'usuario']       = $usuarioDB['usuario'];
        $_SESSION[$SES . 'nombre']        = $nombre;
        $_SESSION[$SES . 'tipo']          = $usuarioDB['tipo_usuario'];
        $_SESSION[$SES . 'id']            = (int)$usuarioDB['idUsuario'];
        $_SESSION[$SES . 'login_time']    = time();
        $_SESSION[$SES . 'last_activity'] = time();

        $redirect = obtenerRutaEscritorioPorRol($usuarioDB['idTipoUsuario']);
        if (!$redirect) responderJSON(['success' => false, 'error' => 'Rol de usuario no reconocido.']);

        $_SESSION[$SES . 'last_redirect'] = $redirect;

        responderJSON(['success' => true, 'redirect' => $redirect, 'usuario' => $nombre]);
    }

    $intentos_fallidos++;
    $_SESSION[$SES . 'intentos'] = $intentos_fallidos;
    registrarIntentoEnBD($ip_cliente, $usuario, 'fallido');

    if ($intentos_fallidos >= 5) {
        $_SESSION[$SES . 'tiempo_bloqueo'] = time() + 300;
        responderJSON(['success' => false, 'bloqueado' => true, 'error' => 'Usuario bloqueado por 5 minutos.', 'tipo' => 'usuario', 'intentos' => $intentos_fallidos]);
    }

    if (ipBloqueada($ip_cliente)) {
        responderJSON(['success' => false, 'bloqueado' => true, 'error' => 'IP bloqueada por exceso de intentos.', 'tipo' => 'ip']);
    }

    responderJSON(['success' => false, 'error' => 'Credenciales incorrectas.', 'bloqueado' => false, 'intentos' => $intentos_fallidos, 'restantes' => max(0, 5 - $intentos_fallidos)]);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistema TRIPLE - Acceso</title>
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="Portal unificado de acceso: SINADECI, SILIF y MPARTES" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="css/login.css" />
    <link rel="stylesheet" href="css/login-responsive.css" />
    <link rel="icon" type="image/png" href="../sinadeci/backend/img/ICONO-SINADECI.ico" />
</head>

<body>
    <main class="contenedor-login">
        <!-- Panel Izquierdo -->
        <section class="panel-izquierdo">
            <div class="contenido-izquierdo">
                <div class="logo-defensa-civil">
                    <div class="escudo-sinadeci" aria-hidden="true" title="TRIPLE">
                        <!-- Ícono FA que simboliza 3 módulos apilados -->
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h1 class="nombre-sistema">TRIPLE</h1>
                    <p class="descripcion-sistema">Acceso unificado (SINADECI / SILIF / MPARTES)</p>
                </div>
            </div>
        </section>

        <!-- Panel Derecho -->
        <section class="panel-derecho">
            <div class="contenedor-formulario">
                <header class="encabezado-login">
                    <div class="icono-acceso" aria-hidden="true">
                        <!-- Mismo ícono para coherencia visual -->
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <p class="descripcion-acceso">Ingrese sus credenciales para acceder</p>
                </header>

                <div id="zona-alertas" class="zona-alertas"></div>

                <form id="formularioAuth" class="formulario-login" novalidate>
                    <div class="grupo-input">
                        <div class="envolvedor-input">
                            <div class="icono-input"><i class="fas fa-user"></i></div>
                            <input type="text" id="username" name="username" class="input-formulario" placeholder="Usuario" autocomplete="username" required />
                            <label for="username" class="etiqueta-input">Usuario del Sistema</label>
                        </div>
                        <div class="retroalimentacion-input"></div>
                    </div>

                    <div class="grupo-input">
                        <div class="envolvedor-input">
                            <div class="icono-input"><i class="fas fa-lock"></i></div>
                            <input type="password" id="password" name="password" class="input-formulario" placeholder="Contraseña" autocomplete="current-password" required />
                            <button type="button" class="revelar-password" id="revelarPassword" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <label for="password" class="etiqueta-input">Contraseña de Acceso</label>
                        </div>
                        <div class="retroalimentacion-input"></div>
                    </div>

                    <div class="opciones-formulario">
                        <label class="checkbox-personalizado">
                            <input type="checkbox" name="remember" id="remember" />
                            <span class="marca-checkbox"><i class="fas fa-check"></i></span>
                            <span class="texto-checkbox">Mantener sesión iniciada</span>
                        </label>
                    </div>

                    <button type="submit" class="boton-enviar" id="botonEnviar">
                        <span class="contenido-boton">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="texto-boton">Ingresar al Sistema</span>
                        </span>
                        <div class="cargador-boton">
                            <div class="spinner-cargador"></div>
                        </div>
                        <div class="exito-boton"><i class="fas fa-check"></i></div>
                    </button>
                </form>

                <footer class="info-sistema">
                    <div class="item-info"><i class="fas fa-clock"></i> <span id="tiempoActual"><?= date('H:i:s') ?></span></div>
                    <div class="divisor-info">•</div>
                    <div class="item-info"><i class="fas fa-calendar-day"></i> <span><?= date('d/m/Y') ?></span></div>
                </footer>
            </div>
        </section>
    </main>

    <script src="js/login.js"></script>
</body>

</html>