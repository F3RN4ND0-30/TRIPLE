<?php
require_once '../../../backend/db/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'listar': {
                // filtros
                $campo  = $_GET['campo']  ?? 'usuario';
                $q      = trim($_GET['q'] ?? '');
                $rol    = trim($_GET['rol'] ?? '');
                $estado = trim($_GET['estado'] ?? '');
                $page   = max(1, (int)($_GET['p'] ?? 1));
                $per    = max(10, (int)($_GET['per'] ?? 50));
                $off    = ($page - 1) * $per;

                // where
                $w = [];
                $t = "";
                $v = [];
                if ($estado !== '') {
                    $w[] = "u.estado=?";
                    $t .= "s";
                    $v[] = (strtoupper($estado) === 'ACTIVO') ? '1' : '0';
                }
                if ($rol !== '') {
                    $w[] = "tu.nombre=?";
                    $t .= "s";
                    $v[] = $rol;
                }
                if ($q !== '') {
                    if ($campo === 'nombres') {
                        $w[] = "CONCAT(p.nombres,' ',p.apePat,' ',p.apeMat) LIKE ?";
                        $t .= "s";
                        $v[] = "%$q%";
                    } elseif ($campo === 'rol') {
                        $w[] = "tu.nombre LIKE ?";
                        $t .= "s";
                        $v[] = "%$q%";
                    } else {
                        $w[] = "u.usuario LIKE ?";
                        $t .= "s";
                        $v[] = "%$q%";
                    }
                }
                $ws = $w ? ('WHERE ' . implode(' AND ', $w)) : "";

                // total
                $sqlC = "SELECT COUNT(*) total FROM USUARIOS u
             INNER JOIN PERSONAS p ON p.idPersona=u.idPersona
             INNER JOIN TIPO_USUARIOS tu ON tu.idTipo=u.idTipoUsuario $ws";
                $stC = $conexion->prepare($sqlC);
                if ($t) $stC->bind_param($t, ...$v);
                $stC->execute();
                $rt = $stC->get_result();
                $total = (int)($rt->fetch_assoc()['total'] ?? 0);
                $stC->close();

                // datos
                $sql = "SELECT u.idUsuario id, u.usuario, p.numDoc dni,
                   CONCAT(p.nombres,' ',p.apePat,' ',p.apeMat) nombres,
                   tu.nombre rol, u.estado estado_raw, u.estadoPass estadoPass_raw
            FROM USUARIOS u
            INNER JOIN PERSONAS p ON p.idPersona=u.idPersona
            INNER JOIN TIPO_USUARIOS tu ON tu.idTipo=u.idTipoUsuario
            $ws ORDER BY u.idUsuario DESC LIMIT ? OFFSET ?";
                $st = $conexion->prepare($sql);
                if ($t) {
                    $t .= "ii";
                    $st->bind_param($t, ...array_merge($v, [$per, $off]));
                } else {
                    $st->bind_param("ii", $per, $off);
                }
                $st->execute();
                $rs = $st->get_result();
                $rows = [];
                while ($f = $rs->fetch_assoc()) {
                    $rows[] = [
                        'id' => (int)$f['id'],
                        'usuario' => $f['usuario'],
                        'dni' => $f['dni'],
                        'nombres' => $f['nombres'],
                        'rol' => $f['rol'],
                        'estado' => ($f['estado_raw'] == '1' ? 'Activo' : 'Suspendido'),
                        'estadoPass' => ($f['estadoPass_raw'] == '1' ? 'Válida' : 'Requiere cambio'),
                    ];
                }
                $st->close();

                echo json_encode(['ok' => true, 'total' => $total, 'data' => $rows]);
                break;
            }

        case 'buscar_personas': {
                $campo = $_GET['campo'] ?? 'dni'; // dni | apellidos | ruc
                $q     = trim($_GET['q'] ?? '');
                if ($q === '') {
                    echo json_encode(['ok' => true, 'data' => []]);
                    break;
                }

                $sql = "SELECT idPersona, numDoc AS dni,
                     CONCAT(apePat,' ',apeMat,' ',nombres) AS nombre,
                     RUC AS ruc
              FROM PERSONAS WHERE ";
                if ($campo === 'apellidos') {
                    $sql .= "CONCAT(apePat,' ',apeMat,' ',nombres) LIKE ?";
                    $t = "s";
                    $v = ["%$q%"];
                } elseif ($campo === 'ruc') {
                    $sql .= "RUC LIKE ?";
                    $t = "s";
                    $v = ["%$q%"];
                } else {
                    $sql .= "numDoc LIKE ?";
                    $t = "s";
                    $v = ["%$q%"];
                }
                $sql .= " ORDER BY nombre LIMIT 50";
                $st = $conexion->prepare($sql);
                $st->bind_param($t, ...$v);
                $st->execute();
                $rs = $st->get_result();
                $out = [];
                while ($f = $rs->fetch_assoc()) $out[] = $f;
                $st->close();
                echo json_encode(['ok' => true, 'data' => $out]);
                break;
            }

        case 'crear': {
                // Espera: idPersona, usuario, rolNombre, estado
                $idPersona = (int)($_POST['idPersona'] ?? 0);
                $usuario   = trim($_POST['usuario'] ?? '');
                $rolNom    = trim($_POST['rol'] ?? '');
                $estadoTxt = trim($_POST['estado'] ?? 'Activo');

                if ($idPersona <= 0 || $usuario === '' || $rolNom === '') {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
                    break;
                }

                // rol -> idTipo
                $stR = $conexion->prepare("SELECT idTipo FROM TIPO_USUARIOS WHERE nombre=? LIMIT 1");
                $stR->bind_param("s", $rolNom);
                $stR->execute();
                $r = $stR->get_result()->fetch_assoc();
                $stR->close();
                if (!$r) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'msg' => 'Rol no válido']);
                    break;
                }
                $idTipo = (int)$r['idTipo'];

                // usuario repetido?
                $stU = $conexion->prepare("SELECT 1 FROM USUARIOS WHERE usuario=? LIMIT 1");
                $stU->bind_param("s", $usuario);
                $stU->execute();
                $ex = $stU->get_result()->fetch_assoc();
                $stU->close();
                if ($ex) {
                    http_response_code(409);
                    echo json_encode(['ok' => false, 'msg' => 'Usuario ya existe']);
                    break;
                }

                $estado = (strtoupper($estadoTxt) === 'ACTIVO') ? '1' : '0';
                // guarda con estadoPass=1 (válida)
                $stI = $conexion->prepare("INSERT INTO USUARIOS(idPersona,usuario,estado,estadoPass,idTipoUsuario) VALUES(?,?,?,?,?)");
                $stI->bind_param("isssi", $idPersona, $usuario, $estado, $estadoPass = '1', $idTipo);
                $ok = $stI->execute();
                $id = $conexion->insert_id;
                $stI->close();

                echo json_encode(['ok' => $ok, 'id' => $id]);
                break;
            }

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
