<?PHP
//Iniciar la sesión
session_start();
//Pagina no cacheada 
header("Expires: Tue, 01 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0, false");
header("Pragma: no-cache");

date_default_timezone_set('America/Santiago');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");

//Cierra la sessión
function salir(){
	session_unset();				//Borra variables de sesión
	session_destroy();				//Elimina la información asociada con la sesión en el servidor
	session_start();				//Crea la sesión
	session_regenerate_id(true);	//Nuevo identificador de sesión
	header("Location: index.php");	//Regresa a la página de inicio
}
//Revisa si paso el tiempo de conexión
function validaTiempo(){
	if(strcmp($_SESSION["CONECTADO"],"SI") == 0){	
		$fechaGuardada = $_SESSION["ULTIMO_ACCESO"];
		$fechaActual = date("Y-n-j H:i:s");
		$tiempoTranscurrido = (strtotime($fechaActual)-strtotime($fechaGuardada));
		//Se compara el tiempo (60 minutos)
		if($tiempoTranscurrido >= 3600){
			salir();
		}
		else{
			$_SESSION["ULTIMO_ACCESO"] = date("Y-n-j H:i:s");
			return;
		}
	}
	else{
		salir();
	}
}

function validarAdministrador(){
	if(strcmp($_SESSION["CONECTADO"],"SI") == 0 and strcmp($_SESSION["CARGO"],"Administrador") == 0){
		return;
	}
	else{
		salir();
	}
}
?>