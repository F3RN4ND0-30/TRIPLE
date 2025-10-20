<?php
// conexion.php para SQL Server
$serverName = "DESKTOP-B340BGP\SQLEXPRESS"; 
$connectionOptions = [
    "Database" => "DB_RECEPCION",  
    "Uid" => "sa",               
    "PWD" => "admin1234",   
    "CharacterSet" => "UTF-8"
];

// Crear conexión
$conexion = sqlsrv_connect($serverName, $connectionOptions);

if ($conexion === false) {
    die(print_r(sqlsrv_errors(), true));
}

function ejecutarConsulta($sql, $params = [])
{
    global $conexion;
    $stmt = sqlsrv_prepare($conexion, $sql, $params);
    if (!$stmt) {
        die(print_r(sqlsrv_errors(), true));
    }
    if (!sqlsrv_execute($stmt)) {
        die(print_r(sqlsrv_errors(), true));
    }
    return $stmt;
}

function obtenerFila($stmt)
{
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

function obtenerFilas($stmt)
{
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}
