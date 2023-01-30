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
	if(!isset($_POST["buscador"]))
	{
		//Se carga la página
		$tpl = new TemplatePower("modificarcomponente1.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();

		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
		$tpl->assign("DISPLAY","none;");
		
		if(isset($_SESSION["MODIFICADO_CODIGO"]) and strcmp($_SESSION["MODIFICADO_CODIGO"], "SI")==0)
		{
			$tpl->assign("DISPLAY","compact;");
			$tpl->assign("MENSAJE", "LOS C&Oacute;DIGOS FUERON MODIFICADOS CORRECTAMENTE");
			unset($_SESSION["MODIFICADO_CODIGO"]);
		}
		/*
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
		$consulta = "SELECT codigoComponente FROM ctdadcodigocomponente WHERE nombreComponente = 'BERMA'";
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
		*/
		$tpl->printToScreen();
	}
	else{
		/*
		$codigoFaja = $_POST["codigoFaja"];
		$codigoSaneamiento = $_POST["codigoSaneamiento"];
		$codigoCalzada = $_POST["codigoCalzada"];
		$codigoBerma = $_POST["codigoBerma"];
		$codigoSenalizacion = $_POST["codigoSenalizacion"];
		$codigoDemarcacion = $_POST["codigoDemarcacion"];*/

		$nombrecomp1 = $_POST["componentes1"];
		$nombrecomp2 = $_POST["componentes2"];
		$nombrecomp3 = $_POST["componentes3"];
		$nombrecomp4 = $_POST["componentes4"];
		$nombrecomp5 = $_POST["componentes5"];
		$nombrecomp6 = $_POST["componentes6"];

		$nombreitems1 = $_POST["items1"];
		$nombreitems2 = $_POST["items2"];
		$nombreitems3 = $_POST["items3"];
		$nombreitems4 = $_POST["items4"];
		$nombreitems5 = $_POST["items5"];
		$nombreitems6 = $_POST["items6"];

		
		//Guardamos los código de los componentes		
		$consulta = "delete from codigocomponente where 1";
		$resultado = $conexion_db->query($consulta);
		/*		
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
*/
		if(strcmp($nombrecomp1, "")!=0)
		{
			$consulta4 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp1."', '".$nombreitems1."')";
			$resultado4 = $conexion_db->query($consulta4);	
		}

		if(strcmp($nombrecomp2, "")!=0)
		{
			$consulta5 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp2."', '".$nombreitems2."')";
			$resultado5 = $conexion_db->query($consulta5);	
		}

		if(strcmp($nombrecomp3, "")!=0)
		{
			$consulta6 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp3."', '".$nombreitems3."')";
			$resultado6 = $conexion_db->query($consulta6);	
		}

		if(strcmp($nombrecomp4, "")!=0)
		{
			$consulta7 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp4."', '".$nombreitems4."')";
			$resultado7 = $conexion_db->query($consulta7);	
		}

		if(strcmp($nombrecomp5, "")!=0)
		{
			$consulta8 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp5."', '".$nombreitems5."')";
			$resultado8 = $conexion_db->query($consulta8);	
		}

		if(strcmp($nombrecomp6, "")!=0)
		{
			$consulta9 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', '".$nombrecomp6."', '".$nombreitems6."')";
			$resultado9 = $conexion_db->query($consulta9);	
		}
		

		$_SESSION["MODIFICADO_CODIGO"] = "SI";
		header("Location: modificarcomponente1.php");		
	}
?>