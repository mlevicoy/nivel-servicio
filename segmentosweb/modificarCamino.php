<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validarAdministrador();
	validaTiempo();
	
	if(!isset($_POST["cargador"])){
		//Verificamos si hay camino
		$consulta = "select count(*) as cantidad from redCaminera";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		if($fila["cantidad"] == 0){
			//Se carga la página
			$tpl = new TemplatePower("modificarCamino.html");

			$tpl->assignInclude("header", "header.html");
		    $tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("MENSAJE","ERROR - DEBE AGREGAR LA RED DE CAMINOS POR NIVEL DE SERVICIO");
			$tpl->assign("DISPLAY","none;");
			/*if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] = "SI"){
				$tpl->assign("MENSAJE","ERROR AL ACTUALIZAR LA RED, VERIFICAR LOS CAMBIOS");
				unset($_SESSION["MENSAJE_NO_CUMPLE"]);
			}*/	
			//Se cierra la conexión
			$conexion_db->close();			
			$tpl->printToScreen();
		}
		else{
			//contador
			$i=0;
			//Obtenemos la red de camino
			$consulta2 = "select * from redCaminera order by nroCaminoRedCaminera";
			$resultado2 = $conexion_db->query($consulta2);
			//Se carga la página
			$tpl = new TemplatePower("modificarCamino.html");
			
			$tpl->assignInclude("header", "header.html");
		    $tpl->assignInclude("menu", "menu.html");
		    
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));			
			/*if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] = "SI"){
				$tpl->assign("MENSAJE","ERROR AL ACTUALIZAR LA RED, VERIFICAR LOS CAMBIOS");
				unset($_SESSION["MENSAJE_NO_CUMPLE"]);
			}
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] = "SI"){
				$tpl->assign("MENSAJE","RED ACTUALIZADA CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);
			}*/
			$tpl->assign("DISPLAY","compact;");
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("CAMPOS_CAMINO");
				$tpl->assign("NRO_CAMINO",$fila2["nroCaminoRedCaminera"]);
				$tpl->assign("ROL_CAMINO",$fila2["rolRedCaminera"]);
				$tpl->assign("CODIGO_CAMINO",$fila2["codigoRedCaminera"]);
				$tpl->assign("NOMBRE_CAMINO",$fila2["nombreRedCaminera"]);
				$tpl->assign("KMINICIO_CAMINO",$fila2["kmInicioRedCaminera"]);
				$tpl->assign("KMFINAL_CAMINO",$fila2["kmFinalRedCaminera"]);
				$tpl->assign("LONGITUD_CAMINO",$fila2["longitudRedCaminera"]);
				$tpl->assign("I",$i);
				$i++;
			}
			//Se cierra la conexión
			$conexion_db->close();			
			$tpl->printToScreen();		
		}
	}
	else{
		//Truncamos las tablas redCaminera, segmentos, subSegmentos, designacion, desafeccionReal
		$consulta = "truncate table redCaminera";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "truncate table segmentos";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "truncate table subSegmentos";
		$resultado = $conexion_db->query($consulta);

		$consulta = "truncate table designacion";
		$resultado = $conexion_db->query($consulta);

		$consulta = "truncate table desafeccionreal";
		$resultado = $conexion_db->query($consulta);
		
		if(isset($_POST["rol"])){
			//Información del formulario
			$valor_filtro = '';
			$nroCamino = array_merge(array_filter($_POST["numeroCamino"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$rol = array_merge(array_filter($_POST["rol"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$codigo = array_merge(array_filter($_POST["codigo"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$nombre = array_merge(array_filter($_POST["nombre"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
			$kmInicio = array_merge(array_filter($_POST["kmInicio"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$kmFinal = array_merge(array_filter($_POST["kmFinal"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$longitud = array_merge(array_filter($_POST["longitud"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
		
			for($i=0;$i<count($rol);$i++){
				//Formateao del las variables
				$nroCamino_array = htmlentities(mb_strtoupper(trim($nroCamino[$i]),'UTF-8'));
				$rol_array = htmlentities(mb_strtoupper(trim($rol[$i]),'UTF-8'));
				$codigo_array = htmlentities(mb_strtoupper(trim($codigo[$i]),'UTF-8'));	
				$nombre_array = htmlentities(mb_strtoupper(trim($nombre[$i]),'UTF-8'));	
				$kmInicio_array = number_format($kmInicio[$i], 3, '.', '');
				$kmFinal_array =  number_format($kmFinal[$i], 3, '.', '');
				$longitud_array = $longitud[$i];

				$consulta2 = "insert into redCaminera (idRedCaminera, nroCaminoRedCaminera, rolRedCaminera, codigoRedCaminera, nombreRedCaminera, kmInicioRedCaminera, kmFinalRedCaminera, ".
				"longitudRedCaminera, estadoRedCaminera, segmentadoRedCaminera) values ('', '".$nroCamino_array."', '".$rol_array."', '".$codigo_array."', '".$nombre_array."', ".$kmInicio_array.", ".
				$kmFinal_array.", ".$longitud_array.", 1, 0)";
				$resultado2 = $conexion_db->query($consulta2);							
			}
			//$_SESSION["MENSAJE_CUMPLE"] = "SI";
			//header("Location: segmentacion.php");

			$tpl = new TemplatePower("barra2.html");
			
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");	

			$tpl->prepare();

			$tpl->assign("PAGINA","segmentacion.php");
			$tpl->assign("MENSAJE","GENERANDO SEGMENTOS Y SUB-SEGMENTOS");
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->printToScreen();		
		}	
	}
?>
