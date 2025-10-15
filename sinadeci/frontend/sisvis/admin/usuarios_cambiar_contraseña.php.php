<?php
session_start();
if(!isset($_SESSION['sinadeci_id'])){
    echo json_encode(['success'=>false,'message'=>'No tienes sesión activa.']);
    exit;
}
require_once '../../../../db/conexion.php';

$idUsuario = isset($_POST['idUsuario']) ? intval($_POST['idUsuario']) : 0;
$nuevaContrasena = $_POST['nuevaContrasena'] ?? '';

if($idUsuario<=0 || !$nuevaContrasena){
    echo json_encode(['success'=>false,'message'=>'Datos incompletos.']); exit;
}

$passwordHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
$sql = "UPDATE usuarios SET password='$passwordHash' WHERE idUsuario=$idUsuario";

if($conn->query($sql)){
    echo json_encode(['success'=>true,'message'=>'Contraseña actualizada correctamente.']);
} else {
    echo json_encode(['success'=>false,'message'=>'Error al actualizar contraseña.']);
}
?>
