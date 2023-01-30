<?php
	require_once("variables_conexion.php");
	date_default_timezone_set('America/Santiago');
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
	header('Content-Type: text/html; charset=UTF-8');
	
	$conexion_db = new mysqli();
	@$conexion_db->connect(SERVIDOR_DB,USUARIO_DB,CONTRASENA_DB,NOMBRE_DB);
	if($conexion_db->connect_errno){
		echo "Error al conectarse a MySQL: (".$conexion_db->connect_errno.") ".$conexion_db->connect_error;		
		exit;
	}
?>