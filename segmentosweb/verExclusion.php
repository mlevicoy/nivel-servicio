<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	//Vemos si hay exclusiones
	$consulta = "select count(*) as cantidad from desafeccionReal";
	$resultado = $conexion_db->query($consulta);
	$fila = $resultado->fetch_array(MYSQL_ASSOC);
	if($fila["cantidad"] == 0){
		//Se carga la página
		$tpl = new TemplatePower("verExclusion.html");

		$tpl->assignInclude("header", "header.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		$tpl->assign("DISPLAY","compact;");
		$tpl->assign("MENSAJE","NO EXISTEN EXCLUSIONES EN EL SISTEMA");	

		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente ";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("NOMBRES_COMPONENTES");
			$tpl->assign("valor_componente",$fila["nombreComponente"]);
		}

		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();	
	}
	else{
		//Informacion de la desafeccion
		$consulta2 = "select * from desafeccionReal order by rolDesafeccionReal,desdeDesafeccionReal";
		$resultado2 = $conexion_db->query($consulta2);
		//Se carga la página
		$tpl = new TemplatePower("verExclusion.html");

		$tpl->assignInclude("header", "header.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));

		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("NOMBRES_COMPONENTES");
			$tpl->assign("valor_componente",$fila["nombreComponente"]);
		}

		while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("EXCLUSIONES");
			$tpl->assign("ROL_EXCLUSION",$fila2["rolDesafeccionReal"]);
			$tpl->assign("DESDE_EXCLUSION",$fila2["desdeDesafeccionReal"]);
			$tpl->assign("HASTA_EXCLUSION",$fila2["hastaDesafeccionReal"]);
			$tpl->assign("LONGITUD_EXCLUSION",$fila2["longitudDesafeccionReal"]);
			$tpl->assign("FECHA_INICIO_EXCLUSION",$fila2["fecha_inicio"]);
			$tpl->assign("FECHA_TERMINO_EXCLUSION",$fila2["fecha_termino"]);
			$tpl->assign("FAJA_EXCLUSION",$fila2["fajaVialDesafeccionReal"]);
			$tpl->assign("SANEAMIENTO_EXCLUSION",$fila2["saneamientoDesafeccionReal"]);
			$tpl->assign("CALZADA_EXCLUSION",$fila2["calzadaDesafeccionReal"]);
			$tpl->assign("BERMAS_EXCLUSION",$fila2["bermasDesafeccionReal"]);
			$tpl->assign("SENALIZACION_EXCLUSION",$fila2["senalizacionDesafeccionReal"]);
			$tpl->assign("DEMARCACION_EXCLUSION",$fila2["demarcacionDesafeccionReal"]);
			$tpl->assign("RESOLUCION_EXCLUSION",$fila2["observacionDesafeccionReal"]);
		}
		$tpl->gotoBlock("_ROOT");
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA Y/O MODIFICADA CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);	
		}
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI_FECHA") == 0){
			$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA Y/O MODIFICADA CORRECTAMENTE PERO ALGUNAS EXCLUSIONES NO SE PUDIERON REALIZAR POR ERROR CON LAS FECHAS, VERIFICAR");
			unset($_SESSION["MENSAJE_CUMPLE"]);	
		}
		
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();	
	}
?>
