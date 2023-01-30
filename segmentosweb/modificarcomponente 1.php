<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Funciones en sesiones.php
	validaTiempo();

	//Carga inicial
	if(!isset($_POST["buscador"])){
		//Se carga la página
		$tpl = new TemplatePower("modificarcomponente.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
		$tpl->assign("DISPLAY","none;");
		
		if(isset($_SESSION["MODIFICADO_CODIGO"]) and strcmp($_SESSION["MODIFICADO_CODIGO"], "SI")==0){
			$tpl->assign("DISPLAY","compact;");
			$tpl->assign("MENSAJE", "LOS C&Oacute;DIGOS FUERON MODIFICADOS CORRECTAMENTE");
			unset($_SESSION["MODIFICADO_CODIGO"]);
		}
		
		//Buscamos los compomentes
		//FAJA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'FAJA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_FAJA");
			$tpl->assign("valor_codigo_faja",$fila["codigoComponente"]);
		}		
		//SANEAMIENTO
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'SANEAMIENTO'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_SANEAMIENTO");
			$tpl->assign("valor_codigo_saneamiento",$fila["codigoComponente"]);
		}
		//CALZADA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'CALZADA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_CALZADA");
			$tpl->assign("valor_codigo_calzada",$fila["codigoComponente"]);
		}
		//BERMA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'BERMA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_BERMA");
			$tpl->assign("valor_codigo_berma",$fila["codigoComponente"]);
		}
		//SENALIZACION
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'SENALIZACION'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_SENALIZACION");
			$tpl->assign("valor_codigo_senalizacion",$fila["codigoComponente"]);
		}
		//DEMARCACION
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'DEMARCACION'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_DEMARCACION");
			$tpl->assign("valor_codigo_demarcacion",$fila["codigoComponente"]);
		}
		
		if(isset($resultado)){ $resultado->close(); }		
		$conexion_db->close();
		
		$tpl->printToScreen();
	}
	else{
		$codigoFaja = $_POST["codigoFaja"];
		$codigoSaneamiento = $_POST["codigoSaneamiento"];
		$codigoCalzada = $_POST["codigoCalzada"];
		$codigoBerma = $_POST["codigoBerma"];
		$codigoSenalizacion = $_POST["codigoSenalizacion"];
		$codigoDemarcacion = $_POST["codigoDemarcacion"];
		
		//Guardamos los código de los componentes		
		$consulta = "update codigocomponente set codigoComponente = '".$codigoFaja."' where nombreComponente = 'FAJA'";
		$resultado = $conexion_db->query($consulta);
				
		$consulta2 = "update codigocomponente set codigoComponente = '".$codigoSaneamiento."' where nombreComponente = 'SANEAMIENTO'";
		$resultado2 = $conexion_db->query($consulta2);
		
		$consulta3 = "update codigocomponente set codigoComponente = '".$codigoCalzada."' where nombreComponente = 'CALZADA'";
		$resultado3 = $conexion_db->query($consulta3);

		$consulta4 = "update codigocomponente set codigoComponente = '".$codigoBerma."' where nombreComponente = 'BERMA'";
		$resultado4 = $conexion_db->query($consulta5);
		
		$consulta5 = "update codigocomponente set codigoComponente = '".$codigoSenalizacion."' where nombreComponente = 'SENALIZACION'";
		$resultado5 = $conexion_db->query($consulta6);
		
		$consulta5 = "update codigocomponente set codigoComponente = '".$codigoDemarcacion."' where nombreComponente = 'DEMARCACION'";
		$resultado5 = $conexion_db->query($consulta7);

		$_SESSION["MODIFICADO_CODIGO"] = "SI";
		header("Location: modificarcomponente.php");		
	}
?>