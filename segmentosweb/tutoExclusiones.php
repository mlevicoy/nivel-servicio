<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Funciones en sesiones.php
	//validarAdministrador();
	validaTiempo();
	
	//Se carga la página
	if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
		$tpl = new TemplatePower("tutoExclusiones.html");
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
	}
	else{
		$tpl = new TemplatePower("tutoExclusiones_usr.html");
		$tpl->assignInclude("header", "header.html");
	    $tpl->assignInclude("menu", "menu.html");
	}		
	$tpl->prepare();
	$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
	$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
	$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
	$tpl->assign("DISPLAY","none;");	
	$tpl->printToScreen();
?>