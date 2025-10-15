<?php
session_start();
require_once 'db/conexion.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
session_regenerate_id(true);

function responderJSON($datos)
{
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

function obtenerIP()
{
    $claves_ip = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($claves_ip as $clave) {
        if (!empty($_SERVER[$clave])) {
            $ip = $_SERVER[$clave];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    foreach ($claves_ip as $clave) {
        if (!empty($_SERVER[$clave])) {
            $ip = $_SERVER[$clave];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function ipBloqueada($ip)
{
    $sql = "SELECT COUNT(*) as total FROM intentos_login 
            WHERE ip_address = ? 
            AND tipo = 'fallido' 
            AND timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";

    $resultado = ejecutarConsulta($sql, ["s", $ip]);
    $fila = obtenerFila($resultado);

    if ($fila && $fila['total'] >= 5) {
        return [
            'bloqueada' => true,
            'intentos' => $fila['total'],
            'mensaje' => "IP bloqueada por {$fila['total']} intentos fallidos en 5 minutos"
        ];
    }
    return false;
}

function registrarIntentoEnBD($ip, $usuario, $tipo)
{
    $sql = "INSERT INTO intentos_login (ip_address, usuario, tipo) VALUES (?, ?, ?)";
    ejecutarConsulta($sql, ["sss", $ip, $usuario, $tipo]);
}

$ip_cliente = obtenerIP();

if (isset($_SESSION["sinadeci_id"])) {
    if (isset($_POST['ajax'])) {
        responderJSON(['success' => false, 'redirect' => 'sinadeci/frontend/sisvis/escritorio.php']);
    }
    header("Location: sinadeci/frontend/sisvis/escritorio.php");
    exit;
}

$error = '';
$exito = '';
$intentos_fallidos = $_SESSION['sinadeci_intentos'] ?? 0;
$bloqueado_usuario = false;
$bloqueado_ip = false;

if (isset($_SESSION['tiempo_bloqueo']) && time() < $_SESSION['tiempo_bloqueo']) {
    $bloqueado_usuario = true;
    $tiempo_restante = $_SESSION['tiempo_bloqueo'] - time();
    $error = "Usuario bloqueado. Espere " . ceil($tiempo_restante / 60) . " minutos.";
}

$bloqueo_ip = ipBloqueada($ip_cliente);
if ($bloqueo_ip) {
    $bloqueado_ip = true;
    $error = "IP bloqueada por exceso de intentos. Espere 5 minutos.";
}

$bloqueado = $bloqueado_usuario || $bloqueado_ip;

if (isset($_GET['timeout'])) {
    $error = "Sesión expirada por inactividad.";
}
if (isset($_GET['logout'])) {
    $exito = "Sesión cerrada correctamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $usuario = trim($_POST['username'] ?? '');
    $clave = $_POST['password'] ?? '';

    if ($bloqueado) {
        responderJSON([
            'success' => false,
            'bloqueado' => true,
            'error' => $error,
            'tipo' => $bloqueado_ip ? 'ip' : 'usuario'
        ]);
    }

    if (empty($usuario) || empty($clave)) {
        responderJSON([
            'success' => false,
            'error' => 'Complete todos los campos obligatorios.',
            'bloqueado' => false
        ]);
    }

    if (strlen($usuario) < 3 || strlen($clave) < 4) {
        responderJSON([
            'success' => false,
            'error' => 'Usuario o contraseña muy cortos.',
            'bloqueado' => false
        ]);
    }

    // Consulta con MySQLi
    $sql = "SELECT u.*, p.nombres, p.apePat, p.apeMat, tu.nombre as tipo_usuario 
            FROM USUARIOS u 
            INNER JOIN PERSONAS p ON u.idPersona = p.idPersona 
            INNER JOIN TIPO_USUARIOS tu ON u.idTipoUsuario = tu.idTipo
            WHERE u.usuario = ? AND u.estado = '1' 
            LIMIT 1";

    $resultado = ejecutarConsulta($sql, ["s", $usuario]);
    $usuarioDB = obtenerFila($resultado);

    $login_valido = false;
    if ($usuarioDB) {
        $login_valido = password_verify($clave, $usuarioDB['contraseña']);
    } else {
        password_verify($clave, '$2y$10$dummy.hash.timing.protection');
    }

    if ($login_valido) {
        unset($_SESSION['sinadeci_intentos'], $_SESSION['tiempo_bloqueo']);
        registrarIntentoEnBD($ip_cliente, $usuario, 'exitoso');
        session_regenerate_id(true);

        $nombre_completo = trim($usuarioDB['nombres'] . ' ' . $usuarioDB['apePat'] . ' ' . $usuarioDB['apeMat']);

        $_SESSION['sinadeci_usuario'] = $usuarioDB['usuario'];
        $_SESSION['sinadeci_nombre'] = $nombre_completo;
        $_SESSION['sinadeci_tipo'] = $usuarioDB['tipo_usuario'];
        $_SESSION['sinadeci_id'] = (int)$usuarioDB['idUsuario'];
        $_SESSION['sinadeci_login_time'] = time();
        $_SESSION['sinadeci_last_activity'] = time();

        error_log("Login SINADECI exitoso: {$usuario} desde IP {$ip_cliente}");

        responderJSON([
            'success' => true,
            'redirect' => 'sinadeci/frontend/sisvis/escritorio.php',
            'usuario' => $nombre_completo
        ]);
    } else {
        $intentos_fallidos++;
        $_SESSION['sinadeci_intentos'] = $intentos_fallidos;

        registrarIntentoEnBD($ip_cliente, $usuario, 'fallido');

        if ($intentos_fallidos >= 5) {
            $_SESSION['tiempo_bloqueo'] = time() + 300;
            responderJSON([
                'success' => false,
                'bloqueado' => true,
                'error' => 'Usuario bloqueado por 5 minutos por seguridad.',
                'tipo' => 'usuario',
                'intentos' => $intentos_fallidos
            ]);
        }

        $nuevo_bloqueo_ip = ipBloqueada($ip_cliente);
        if ($nuevo_bloqueo_ip) {
            responderJSON([
                'success' => false,
                'bloqueado' => true,
                'error' => 'IP bloqueada por exceso de intentos.',
                'tipo' => 'ip',
                'intentos' => $nuevo_bloqueo_ip['intentos']
            ]);
        }

        error_log("Login SINADECI fallido: {$usuario} desde IP {$ip_cliente}");

        responderJSON([
            'success' => false,
            'error' => 'Credenciales incorrectas.',
            'bloqueado' => false,
            'intentos' => $intentos_fallidos,
            'restantes' => 5 - $intentos_fallidos
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SINADECI - Acceso al Sistema</title>
    <meta name="description" content="Sistema Nacional de Defensa Civil - Portal de Acceso">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="SINADECI">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/login-responsive.css">

    <link rel="icon" type="image/png" href="../sinadeci/backend/img/ICONO-SINADECI.ico" />
</head>

<body>
    <main class="contenedor-login">
        <!-- Panel Izquierdo -->
        <section class="panel-izquierdo">
            <div class="contenido-izquierdo">
                <div class="logo-defensa-civil">
                    <!-- Aquí va el icono de SINADECI -->
                    <div class="escudo-sinadeci">
                        <img src="../sinadeci/backend/img/ICONO-SINADECI.ico" alt="Logo SINADECI" class="icono-sinadeci">
                    </div>
                    <h1 class="nombre-sistema">SINADECI</h1>
                    <p class="descripcion-sistema">Sistema Nacional de Defensa Civil</p>
                </div>
            </div>
        </section>

        <!-- Panel Derecho -->
        <section class="panel-derecho">
            <div class="contenedor-formulario">
                <header class="encabezado-login">
                    <!-- Aquí va el escudo de la Municipalidad -->
                    <div class="icono-acceso">
                        <img src="../sinadeci/backend/img/logoPisco2.png" alt="Municipalidad de Pisco" class="icono-pisco">
                    </div>
                    <p class="descripcion-acceso">Ingrese sus credenciales para acceder al sistema</p>
                </header>

                <div id="zona-alertas" class="zona-alertas"></div>

                <form id="formularioAuth" class="formulario-login" novalidate>
                    <div class="grupo-input">
                        <div class="envolvedor-input">
                            <div class="icono-input"><i class="fas fa-user"></i></div>
                            <input type="text" id="username" name="username" class="input-formulario"
                                placeholder="Usuario" autocomplete="username" required>
                            <label for="username" class="etiqueta-input">Usuario del Sistema</label>
                        </div>
                        <div class="retroalimentacion-input"></div>
                    </div>

                    <div class="grupo-input">
                        <div class="envolvedor-input">
                            <div class="icono-input"><i class="fas fa-lock"></i></div>
                            <input type="password" id="password" name="password" class="input-formulario"
                                placeholder="Contraseña" autocomplete="current-password" required>
                            <button type="button" class="revelar-password" id="revelarPassword" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <label for="password" class="etiqueta-input">Contraseña de Acceso</label>
                        </div>
                        <div class="retroalimentacion-input"></div>
                    </div>

                    <div class="opciones-formulario">
                        <label class="checkbox-personalizado">
                            <input type="checkbox" name="remember" id="remember">
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

    <script src="loginfunc/login.js"></script>
</body>

</html>