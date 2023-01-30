<?PHP														
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php								
	validarAdministrador();
	validaTiempo();
	
	if(!isset($_POST["cargador"])){
		//Se carga la página
		$tpl = new TemplatePower("ingresarCamino.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		
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
		}*/			
		
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//Información del formulario
		$valor_filtro = '';
		$nroCamino = array_merge(array_filter($_POST["numeroCamino"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$rol = array_merge(array_filter($_POST["rol"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$codigo = array_merge(array_filter($_POST["codigo"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$nombre = array_merge(array_filter($_POST["nombre"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
		$kmInicio = array_merge(array_filter($_POST["kmInicio"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$kmFinal = array_merge(array_filter($_POST["kmFinal"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$longitud = array_merge(array_filter($_POST["longitud"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));	
		//Recorro los arreglos		
		for($i=0;$i<count($rol);$i++){
			//Formateao del las variables
			$nroCamino_array = htmlentities(mb_strtoupper(trim($nroCamino[$i]),'UTF-8'));
			$rol_array = htmlentities(mb_strtoupper(trim($rol[$i]),'UTF-8'));
			$codigo_array = htmlentities(mb_strtoupper(trim($codigo[$i]),'UTF-8'));	
			$nombre_array = htmlentities(mb_strtoupper(trim($nombre[$i]),'UTF-8'));	
			$kmInicio_array = number_format($kmInicio[$i], 3, '.', '');
			$kmFinal_array =  number_format($kmFinal[$i], 3, '.', '');
			$longitud_array = $longitud[$i];						
			//Se almacena la información			
			$consulta2 = "insert into redCaminera (idRedCaminera, nroCaminoRedCaminera, rolRedCaminera, codigoRedCaminera, nombreRedCaminera, ".
			"kmInicioRedCaminera, kmFinalRedCaminera, longitudRedCaminera, estadoRedCaminera, segmentadoRedCaminera) values ('', '".$nroCamino_array."', '".$rol_array."', '".$codigo_array."', '".$nombre_array."', ".$kmInicio_array.", ".$kmFinal_array.", ".$longitud_array.", 1, 0)";
			$resultado2 = $conexion_db->query($consulta2);		
		}
		//$_SESSION["MENSAJE_CUMPLE"] = "SI";	
		//header("Location: segmentacion.php");	

		$tpl = new TemplatePower("barra2.html");
			
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");	

		$tpl->prepare();

		$tpl->assign("PAGINA","modificarCamino.php");
		$tpl->assign("MENSAJE","GENERANDO SEGMENTOS Y SUB-SEGMENTOS");
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		$tpl->printToScreen();				
	}
?>