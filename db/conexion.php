<?php
// SINADECI - Conexión Simple que Funciona

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'SINADECI';

// Crear conexión MySQLi
$conexion = new mysqli($host, $user, $pass, $db);

// Verificar si hay error
if ($conexion->connect_error) {
    die("Conexión falló: " . $conexion->connect_error);
}

// Configurar UTF-8
$conexion->set_charset("utf8mb4");

// Funciones básicas
function ejecutarConsulta($sql, $parametros = null)
{
    global $conexion;

    if ($parametros) {
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(...$parametros);
        $stmt->execute();
        return $stmt;
    } else {
        return $conexion->query($sql);
    }
}

function obtenerFilas($resultado)
{
    if (is_object($resultado) && method_exists($resultado, 'get_result')) {
        $result = $resultado->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}

function obtenerFila($resultado)
{
    if (is_object($resultado) && method_exists($resultado, 'get_result')) {
        $result = $resultado->get_result();
        return $result->fetch_assoc();
    } else {
        return $resultado->fetch_assoc();
    }
}

// Bloquear acceso directo
if (basename($_SERVER['PHP_SELF']) === 'conexion.php') {
    exit('Acceso denegado');
}
