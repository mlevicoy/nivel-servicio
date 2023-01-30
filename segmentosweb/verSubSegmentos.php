<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	if(!isset($_GET["id"])){
		//Sacamos todos los tramos
		$consulta = "select * from subSegmentos order by segmentoSubSegmentos";
		$resultado = $conexion_db->query($consulta);
		
		$consulta2 = "select count(*) as tramosExcluidos from subSegmentos where estadoSubSegmentos = 0";
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
		
		$consulta3 = "select count(*) as tramosIncluidos from subSegmentos where estadoSubSegmentos = 1";
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
		
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
		$tpl = new TemplatePower("verSubSegmentos.html");
		
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		/*}
		else{
			$tpl = new TemplatePower("verSubSegmentos_usr.html");
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
		}*/

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
		$tpl->assign("INGRESA_TRAMO_SORTEO",$fila3["tramosIncluidos"]);		
		$tpl->assign("EXCLUIDO_TRAMO_SORTEO",$fila2["tramosExcluidos"]);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			//Sacamos el Nro del camino
			$consulta4 = "select nroCaminoRedCaminera from redcaminera where rolRedCaminera = '".$fila["rolSubSegmentos"]."'";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);	
			
			//Llenamos la tabla
			$tpl->newBlock("MOSTRAR_SUBSEGMENTO");
			$tpl->assign("NRO_SEGMENTO",$fila["segmentoSubSegmentos"]);
			$tpl->assign("NRO_TRAMO",$fila["tramoSubSegmentos"]);
			$tpl->assign("NRO_CAMINO",$fila4["nroCaminoRedCaminera"]);			
			$tpl->assign("CODIGO_TRAMO",$fila["codigoSubSegmentos"]);
			$tpl->assign("ROL_TRAMO",$fila["rolSubSegmentos"]);
			$tpl->assign("NOMBRE_TRAMO",$fila["caminoSubSegmentos"]);
			$tpl->assign("KMINICIO_TRAMO",$fila["kmInicioSubSegmento"]);
			$tpl->assign("KMTERMINO_TRAMO",$fila["kmFinalSubSegmentos"]);
			if($fila["estadoSubSegmentos"] == 0){
				$tpl->assign("ESTADO_TRAMO","imagenes/iconos/equis.png");
			}
			else{
				$tpl->assign("ESTADO_TRAMO","imagenes/iconos/checkestado.png");				
			}
		}
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();						
	}
	else{
		//Informacion de id del segmento
		$idSegmento = $_GET["id"];
		//Se filtra la tabla subSegmentos usando la id del segmento
		$consulta = "select * from subSegmentos where idSegmento = ".$idSegmento." order by tramoSubSegmentos";
		$resultado = $conexion_db->query($consulta);		
		
		$consulta2 = "select count(*) as tramosExcluidos from subSegmentos where idSegmento = ".$idSegmento." and estadoSubSegmentos = 0";
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
		
		$consulta3 = "select count(*) as tramosIncluidos from subSegmentos where idSegmento = ".$idSegmento." and estadoSubSegmentos = 1";
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
		
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
		$tpl = new TemplatePower("verSubSegmentos.html");
		/*}
		else{
			$tpl = new TemplatePower("verSubSegmentos_usr.html");
		}*/

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		$tpl->assign("INGRESA_TRAMO_SORTEO",$fila3["tramosIncluidos"]);		
		$tpl->assign("EXCLUIDO_TRAMO_SORTEO",$fila2["tramosExcluidos"]);		
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			//Sacamos el Nro del camino
			$consulta4 = "select nroCaminoRedCaminera from redcaminera where rolRedCaminera = '".$fila["rolSubSegmentos"]."'";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
			
			//Llenamos la tabla
			$tpl->newBlock("MOSTRAR_SUBSEGMENTO");
			$tpl->assign("NRO_SEGMENTO",$fila["segmentoSubSegmentos"]);
			$tpl->assign("NRO_TRAMO",$fila["tramoSubSegmentos"]);
			$tpl->assign("NRO_CAMINO",$fila4["nroCaminoRedCaminera"]);
			$tpl->assign("CODIGO_TRAMO",$fila["codigoSubSegmentos"]);
			$tpl->assign("ROL_TRAMO",$fila["rolSubSegmentos"]);
			$tpl->assign("NOMBRE_TRAMO",$fila["caminoSubSegmentos"]);
			$tpl->assign("KMINICIO_TRAMO",$fila["kmInicioSubSegmento"]);
			$tpl->assign("KMTERMINO_TRAMO",$fila["kmFinalSubSegmentos"]);
			if($fila["estadoSubSegmentos"] == 0){
				$tpl->assign("ESTADO_TRAMO","imagenes/equis.jpg");
			}
			else{
				$tpl->assign("ESTADO_TRAMO","imagenes/check.png");				
			}
		}		
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();	
	}
?>