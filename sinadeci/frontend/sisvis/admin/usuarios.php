<?php
session_start();
if(!isset($_SESSION['sinadeci_id'])){
    header('Location: ../login.php?timeout=1');
    exit;
}
require_once '../../../../db/conexion.php';

$usuario_id = $_SESSION['sinadeci_id'];
$nombre_usuario = $_SESSION['sinadeci_nombre'];
$tipo_usuario = $_SESSION['sinadeci_tipo'];

// ---------------- FUNCIONES ----------------
if(!function_exists('ejecutarConsulta')){
    function ejecutarConsulta($sql){
        global $conn;
        return $conn->query($sql);
    }
}
if(!function_exists('obtenerFila')){
    function obtenerFila($result){
        return $result->fetch_assoc();
    }
}
if(!function_exists('ejecutarConsulta_retornarID')){
    function ejecutarConsulta_retornarID($sql){
        global $conn;
        $conn->query($sql);
        return $conn->insert_id;
    }
}
// -------------------------------------------

// Obtener usuarios
$sql = "SELECT u.idUsuario,u.usuario,p.numDoc,p.apePat,p.apeMat,p.nombres,
            t.nombre AS tipoUsuario,u.estado,u.idTipoUsuario
        FROM usuarios u
        INNER JOIN tipo_usuarios t ON u.idTipoUsuario=t.idTipo
        LEFT JOIN personas p ON u.idPersona=p.idPersona
        ORDER BY u.idUsuario DESC";
$result = ejecutarConsulta($sql);

// Estadísticas
$totalUsuarios = $result ? $result->num_rows : 0;
$admins = 0; $empleados = 0; $activos = 0; $inactivos = 0;
if($result && $result->num_rows>0){
    while($row=obtenerFila($result)){
        if($row['idTipoUsuario']==1) $admins++;
        if($row['idTipoUsuario']==2) $empleados++;
        if($row['estado']==1) $activos++; else $inactivos++;
    }
}
$result = ejecutarConsulta($sql);
$usuariosMes = "-";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>SINADECI - Gestión de Usuarios</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="../../../backend/css/sisvis/escritorio.css" rel="stylesheet">
<link rel="icon" type="image/png" href="../../../backend/img/ICONO-SINADECI.ico"/>
<style>
/* ================== BOTONES ================== */
.btn-azul, .btn-success, .btn-warning, .btn-danger, .btn-primary {border-radius:8px;transition:all .2s;}
.btn-azul:hover {background:#0b5ed7;color:#fff;transform:translateY(-2px);}
.btn-success:hover {background:#198754;transform:translateY(-2px);}
.btn-warning:hover {background:#ffc107;transform:translateY(-2px);}
.btn-danger:hover {background:#dc3545;transform:translateY(-2px);}
.btn-primary:hover {background:#0d6efd;transform:translateY(-2px);}

/* ================== CARD ================== */
.card {border-radius:15px;box-shadow:0 4px 12px rgba(0,0,0,.1);position:relative;}
.card::before {content:"";position:absolute;top:0;left:0;width:100%;height:6px;border-top-left-radius:15px;border-top-right-radius:15px;background:linear-gradient(90deg,#0d6efd,#0a58ca);}

/* ================== TABLA ================== */
.table thead th {border-bottom:none;font-weight:600;color:#fff;background:linear-gradient(90deg,#0d6efd,#0a58ca);}
.table tbody tr:nth-child(odd) {background-color:#f9f9f9;}
.table tbody tr:nth-child(even){background-color:#fff;}
.table tbody tr:hover{background-color:#cfe2ff;cursor:pointer;transition:all .2s;}
.table th, .table td {vertical-align:middle;text-align:center;}

/* ================== BADGES ================== */
.badge {font-size:.85rem;font-weight:600;padding:.5em .75em;border-radius:12px;box-shadow:0 2px 5px rgba(0,0,0,.15);}
.badge.bg-info{background-color:#0dcaf0 !important;}
.badge.bg-success{background-color:#28a745 !important;}
.badge.bg-danger{background-color:#dc3545 !important;}

/* ================== TITULO ================== */
h1.h3{font-size:2rem;font-weight:700;display:flex;align-items:center;gap:10px;color:#0d6efd !important;}
h1.h3 i{color:#0d6efd;}

/* ================== MODALES ================== */
.modal-content{border-radius:15px;box-shadow:0 5px 20px rgba(0,0,0,.2);}
.modal-header{border-bottom:none;}
.modal-footer{border-top:none;}

/* ================== INPUTS ================== */
.form-control,.form-select{border-radius:8px;border:1px solid #ced4da;box-shadow:inset 0 1px 3px rgba(0,0,0,.1);transition:all .2s;}
.form-control:focus,.form-select:focus{box-shadow:0 0 5px rgba(13,110,253,.2);border-color:#0d6efd;}
</style>
</head>
<body class="dashboard-body">

<main class="container-fluid py-4">

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
<?php
$cards = [
    ['label'=>'Total Usuarios','value'=>$totalUsuarios,'icon'=>'fas fa-users','detail'=>"$usuariosMes este mes"],
    ['label'=>'Administradores','value'=>$admins,'icon'=>'fas fa-user-shield','detail'=>''],
    ['label'=>'Empleados','value'=>$empleados,'icon'=>'fas fa-user-tie','detail'=>''],
    ['label'=>'Activos','value'=>$activos,'icon'=>'fas fa-toggle-on','detail'=>"$inactivos Inactivos"]
];
foreach($cards as $c):
?>
<div class="col-md-3">
<div class="card shadow-sm">
<div class="card-body d-flex justify-content-between align-items-center">
<div>
<h3 class="stat-number"><?= $c['value'] ?></h3>
<p class="stat-label"><?= $c['label'] ?></p>
<small class="stat-detail"><?= $c['detail'] ?></small>
</div>
<div><i class="<?= $c['icon'] ?>"></i></div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- Tabla usuarios -->
<div class="card shadow-sm mb-4">
<div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Usuarios</h3>
<button class="btn btn-success" onclick="abrirModal();"><i class="fas fa-user-plus me-1"></i> Nuevo Usuario</button>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th><th>Documento</th><th>Nombre Completo</th><th>Usuario</th><th>Tipo</th><th>Estado</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php
$filas_mostradas = 0;
if($result && $result->num_rows>0):
    while($row=obtenerFila($result)):
        $filas_mostradas++;
        $nombreCompleto = trim(($row['apePat'].' '.$row['apeMat']).($row['nombres']?' , '.$row['nombres']:''));
        $documento = $row['numDoc'] ?? '-';
?>
<tr id="usuario_<?= $row['idUsuario'] ?>">
<td><?= $row['idUsuario'] ?></td>
<td><?= htmlspecialchars($documento) ?></td>
<td><?= htmlspecialchars($nombreCompleto ?: '-') ?></td>
<td><?= htmlspecialchars($row['usuario']) ?></td>
<td><span class="badge bg-info"><?= htmlspecialchars($row['tipoUsuario']==1?'ADMINISTRADOR':'EMPLEADO') ?></span></td>
<td><?= $row['estado']==1?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-danger">Inactivo</span>' ?></td>
<td>
<button class="btn btn-sm btn-warning" onclick="abrirModal(<?= $row['idUsuario'] ?>,'<?= htmlspecialchars($row['numDoc']) ?>','<?= htmlspecialchars($row['nombres']) ?>','<?= htmlspecialchars($row['apePat']) ?>','<?= htmlspecialchars($row['apeMat']) ?>','<?= htmlspecialchars($row['usuario']) ?>',<?= $row['estado'] ?>,<?= $row['idTipoUsuario'] ?>)"><i class="fas fa-edit"></i></button>
<a href="#" class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= $row['idUsuario'] ?>)"><i class="fas fa-trash-alt"></i></a>
<button class="btn btn-sm btn-azul" onclick="cambiarContrasena(<?= $row['idUsuario'] ?>)"><i class="fas fa-key"></i></button>
</td>
</tr>
<?php
    endwhile;
endif;
for($i=$filas_mostradas;$i<10;$i++):
?>
<tr><td colspan="7">&nbsp;</td></tr>
<?php endfor; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- Modal Crear/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formUsuario">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModal"><i class="fas fa-user-plus"></i> Nuevo Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idUsuario" id="idUsuario">
          <div class="row mb-3">
            <div class="col-md-3"><input type="text" class="form-control" id="numDoc" name="numDoc" placeholder="Número de Documento" required></div>
            <div class="col-md-3"><input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres" required></div>
            <div class="col-md-3"><input type="text" class="form-control" id="apePat" name="apePat" placeholder="Apellido Paterno" required></div>
            <div class="col-md-3 mt-2"><input type="text" class="form-control" id="apeMat" name="apeMat" placeholder="Apellido Materno" required></div>
          </div>

          <div class="row mb-3 mt-2">
            <div class="col-md-3"><input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required></div>
            <div class="col-md-3"><input type="password" class="form-control" id="password" name="password" placeholder="Contraseña"></div>
            <div class="col-md-3">
              <select class="form-select" id="estado" name="estado" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
            <div class="col-md-3">
              <select class="form-select" id="tipoUsuario" name="tipoUsuario" required>
                <option value="1">ADMINISTRADOR</option>
                <option value="2">EMPLEADO</option>
              </select>
            </div>
          </div>

          <div id="mensajeModal"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="modalNuevaContrasena" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form id="formCambiarContrasena">
<div class="modal-header">
<h5 class="modal-title"><i class="fas fa-key"></i> Cambiar Contraseña</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" id="usuarioSelect" name="idUsuario">
<div class="mb-3"><input type="password" class="form-control" name="nuevaContrasena" placeholder="Nueva Contraseña" required></div>
<div id="mensajeContrasena"></div>
</div>
<div class="modal-footer">
<button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Cambiar</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>
</form>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Abrir modal usuario
function abrirModal(idUsuario = "", numDoc = "", nombres = "", apePat = "", apeMat = "", usuario = "", estado = 1, tipoUsuario = 1){
    $("#idUsuario").val(idUsuario);
    $("#numDoc").val(numDoc);
    $("#nombres").val(nombres);
    $("#apePat").val(apePat);
    $("#apeMat").val(apeMat);
    $("#usuario").val(usuario);
    $("#estado").val(estado);
    $("#tipoUsuario").val(tipoUsuario);

    if(idUsuario){
        $("#tituloModal").html('<i class="fas fa-edit"></i> Editar Usuario');
        $("#password").attr('placeholder','Dejar en blanco para no cambiar');
    } else {
        $("#tituloModal").html('<i class="fas fa-user-plus"></i> Nuevo Usuario');
        $("#formUsuario")[0].reset();
        $("#password").attr('placeholder','Contraseña');
    }

    $("#mensajeModal").html('');
    $("#modalUsuario").modal("show");
}

// Abrir modal cambiar contraseña
function cambiarContrasena(idUsuario){
    $("#usuarioSelect").val(idUsuario);
    $("#mensajeContrasena").html('');
    $("#formCambiarContrasena")[0].reset();
    $("#modalNuevaContrasena").modal("show");
}

// AJAX Crear/Editar Usuario
$('#formUsuario').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);

    fetch('usuarios_guardar_ajax.php', {
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(respuesta => {
        if(respuesta.success){
            let u = respuesta.usuario;
            let filaHtml = `<tr id="usuario_${u.idUsuario}">
<td>${u.idUsuario}</td>
<td>${u.numDoc||'-'}</td>
<td>${u.apePat} ${u.apeMat}${u.nombres? ', '+u.nombres:''}</td>
<td>${u.usuario}</td>
<td><span class="badge bg-info">${u.tipoUsuario==1?'ADMINISTRADOR':'EMPLEADO'}</span></td>
<td>${u.estado==1?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-danger">Inactivo</span>'}</td>
<td>
<button class="btn btn-sm btn-warning" onclick="abrirModal(${u.idUsuario},'${u.numDoc}','${u.nombres}','${u.apePat}','${u.apeMat}','${u.usuario}',${u.estado},${u.tipoUsuario})"><i class="fas fa-edit"></i></button>
<a href="#" class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.idUsuario})"><i class="fas fa-trash-alt"></i></a>
<button class="btn btn-sm btn-azul" onclick="cambiarContrasena(${u.idUsuario})"><i class="fas fa-key"></i></button>
</td></tr>`;
            if($("#idUsuario").val() == u.idUsuario){
                $("#usuario_"+u.idUsuario).replaceWith(filaHtml);
            } else {
                $("table tbody").prepend(filaHtml);
            }
            $("#mensajeModal").html('<div class="alert alert-success">'+respuesta.message+'</div>');
            $("#formUsuario")[0].reset();
            $("#modalUsuario").modal("hide");
        } else {
            $("#mensajeModal").html('<div class="alert alert-danger">'+respuesta.message+'</div>');
        }
    })
    .catch(err => $("#mensajeModal").html('<div class="alert alert-danger">Error al enviar datos.</div>'));
});

// AJAX Cambiar Contraseña
$('#formCambiarContrasena').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);

    fetch('usuarios_cambiar_contraseña.php', {
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(respuesta => {
        $('#mensajeContrasena').html('<div class="alert '+(respuesta.success?'alert-success':'alert-danger')+'">'+respuesta.message+'</div>');
        if(respuesta.success){
            this.reset();
            setTimeout(()=>$('#modalNuevaContrasena').modal('hide'),1000);
        }
    })
    .catch(err => $('#mensajeContrasena').html('<div class="alert alert-danger">Error al enviar datos.</div>'));
});

// AJAX Eliminar Usuario
function eliminarUsuario(idUsuario){
    if(!confirm('¿Eliminar este usuario?')) return;
    fetch('usuarios_eliminar.php?id='+idUsuario)
    .then(res => res.json())
    .then(respuesta => {
        if(respuesta.success){
            $('#usuario_'+idUsuario).remove();
        } else {
            alert(respuesta.message || 'Error al eliminar.');
        }
    })
    .catch(err => alert('Error al eliminar.'));
}
</script>
</body>
</html>
