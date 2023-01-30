<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	//Se obtiene la red caminera ordena por ROL y por kmInicio
	$consulta = "select * from redcaminera order by nroCaminoRedCaminera";
	$resultado = $conexion_db->query($consulta);
	//Se carga la página
	//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
	$tpl = new TemplatePower("verRedCaminos.html");

	$tpl->assignInclude("header", "header.html");
    $tpl->assignInclude("menu", "menu.html");
	/*}
	else{
		$tpl = new TemplatePower("verRedCaminos_usr.html");

		    $tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
	}*/
	$tpl->prepare();
	$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
	$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
	$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
	
	while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
		$tpl->newBlock("MOSTRAR_REDCAMINERA");
		$tpl->assign("NRO_CAMINO",$fila["nroCaminoRedCaminera"]);
		$tpl->assign("CODIGO_SEGMENTO",$fila["codigoRedCaminera"]);
		$tpl->assign("ROL_SEGMENTO",$fila["rolRedCaminera"]);
		$tpl->assign("NOMBRE_SEGMENTO",$fila["nombreRedCaminera"]);
		$tpl->assign("KMINICIO_SEGMENTO",$fila["kmInicioRedCaminera"]);
		$tpl->assign("KMTERMINO_SEGMENTO",$fila["kmFinalRedCaminera"]);
		$tpl->assign("LONGITUD_SEGMENTO",$fila["longitudRedCaminera"]);
		$tpl->assign("VER_SEGMENTO",$fila["idRedCaminera"]);
	}
	$tpl->gotoBlock("_ROOT");	
	if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] == "SI"){
		$tpl->assign("MENSAJE","HA OCURRIDO UN ERROR, VERIFICAR LOS CAMBIOS");
		unset($_SESSION["MENSAJE_NO_CUMPLE"]);
	}
	if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
		$tpl->assign("MENSAJE","RED INGRESADA O ACTUALIZADA CORRECTAMENTE");
		unset($_SESSION["MENSAJE_CUMPLE"]);
	}
	/*if(isset($_SESSION["MENSAJE_FALTO_CAMINOS"]) and $_SESSION["MENSAJE_FALTO_CAMINOS"] == "SI"){
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
				$tpl->assign("MENSAJE","NO SE PUDO INGRESAR TODA LA INFORMACIÓN, SE REPITE ALGÚN ROL O CÓDIGO");
				unset($_SESSION["MENSAJE_CUMPLE"]);			
			}
			unset($_SESSION["MENSAJE_FALTO_CAMINOS"]);			
		}
		else{
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
				$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);			
			}
		}
					
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","NO SE PUDO INGRESADA LA INFORMACIÓN - TRATAR NUEVAMENTE");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);			
		}	*/
	//Se cierra la conexión
	$conexion_db->close();
	$tpl->printToScreen();				
?>