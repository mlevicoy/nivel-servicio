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
		//Sacamos todos los segmentos
		$consulta = "select * from segmentos order by numeroSegmento";
		$resultado = $conexion_db->query($consulta);
		
		$consulta2 = "select count(*) as ingresaSorteo from segmentos where estadoSegmento=1";
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);		
		
		$consulta3 = "select count(*) as excluidoSorteo from segmentos where estadoSegmento=0";
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);	
		
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
		$tpl = new TemplatePower("verSegmentos.html");
/*
			
		}
		else{
			$tpl = new TemplatePower("verSegmentos_usr.html");
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
		}*/
		
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
		$tpl->assign("INGRESA_SORTEO",$fila2["ingresaSorteo"]);
		$tpl->assign("EXCLUIDO_DEL_SORTEO",$fila3["excluidoSorteo"]);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			//Sacamos el Nro del camino
			$consulta4 = "select nroCaminoRedCaminera from redcaminera where rolRedCaminera = '".$fila["rolCaminoSegmento"]."'";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);	
			
			//Agregamos la informacion a la tabla
			$tpl->newBlock("MOSTRAR_SEGMENTO");
			$tpl->assign("NUMERO_SEGMENTO",$fila["numeroSegmento"]);
			$tpl->assign("NUMERO_CAMINO",$fila4["nroCaminoRedCaminera"]);			
			$tpl->assign("CODIGO_SEGMENTO",$fila["codigoCaminoSegmento"]);
			$tpl->assign("ROL_SEGMENTO",$fila["rolCaminoSegmento"]);
			$tpl->assign("NOMBRE_SEGMENTO",$fila["nombreSegmento"]);
			$tpl->assign("KMINICIO_SEGMENTO",$fila["kmInicioSegmento"]);
			$tpl->assign("KMTERMINO_SEGMENTO",$fila["kmFinalSegmento"]);
			$tpl->assign("VER_SUBSEGMENTO",$fila["idSegmentos"]);
			if($fila["estadoSegmento"] == 0){
				$tpl->assign("ESTADO","imagenes/iconos/equis.jpg");
			}
			else{
				$tpl->assign("ESTADO","imagenes/iconos/checkestado.png");
				
			}
		}
		
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();						
	}
	else{
		//Informacion de id red caminera
		$idRedCaminera = $_GET["id"];
		//Se filtra la tabla segmentos usando la id red caminera
		$consulta = "select * from segmentos where idRedCaminera = ".$idRedCaminera." order by numeroSegmento";
		$resultado = $conexion_db->query($consulta);	
		
		$consulta2 = "select count(*) as ingresaSorteo from segmentos where estadoSegmento=1 and idRedCaminera = ".$idRedCaminera;
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);		
		
		$consulta3 = "select count(*) as excluidoSorteo from segmentos where estadoSegmento=0 and idRedCaminera = ".$idRedCaminera;
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);	
			
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
		$tpl = new TemplatePower("verSegmentos.html");
		/*}
		else{
			$tpl = new TemplatePower("verSegmentos_usr.html");
		}*/

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		$tpl->assign("INGRESA_SORTEO",$fila2["ingresaSorteo"]);
		$tpl->assign("EXCLUIDO_DEL_SORTEO",$fila3["excluidoSorteo"]);		
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			//Sacamos el Nro del camino
			$consulta4 = "select nroCaminoRedCaminera from redcaminera where rolRedCaminera = '".$fila["rolCaminoSegmento"]."'";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
			
			//Agregamos la informacion a la tabla
			$tpl->newBlock("MOSTRAR_SEGMENTO");
			$tpl->assign("NUMERO_SEGMENTO",$fila["numeroSegmento"]);
			$tpl->assign("NUMERO_CAMINO",$fila4["nroCaminoRedCaminera"]);			
			$tpl->assign("CODIGO_SEGMENTO",$fila["codigoCaminoSegmento"]);
			$tpl->assign("ROL_SEGMENTO",$fila["rolCaminoSegmento"]);
			$tpl->assign("NOMBRE_SEGMENTO",$fila["nombreSegmento"]);
			$tpl->assign("KMINICIO_SEGMENTO",$fila["kmInicioSegmento"]);
			$tpl->assign("KMTERMINO_SEGMENTO",$fila["kmFinalSegmento"]);
			$tpl->assign("VER_SUBSEGMENTO",$fila["idSegmentos"]);
			if($fila["estadoSegmento"] == 0){
				$tpl->assign("ESTADO","imagenes/equis.jpg");
			}
			else{
				$tpl->assign("ESTADO","imagenes/check.png");
				
			}
		}		
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();	
	}
?>