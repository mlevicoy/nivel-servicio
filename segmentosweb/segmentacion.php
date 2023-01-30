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
	
	//Obtenemos los datos de la red caminera	
	$consulta = "select * from redCaminera where segmentadoRedCaminera = 0";
	$resultado = $conexion_db->query($consulta);	
	
	//Buscamos el nro segmento actual	
	$consulta4 = "select count(*) as nro_segmento from segmentos";
	$resultado4 = $conexion_db->query($consulta4);
	$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
	if($fila4["nro_segmento"] == 0){
		$k=1;
	}
	else{
		$k=$fila4["nro_segmento"]+1;
	}
	
	//Se recorre el resultado de la consulta 
	while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
		//Sacamos el kmInicio y kmFinal
		$kmInicio = $fila["kmInicioRedCaminera"];
		$kmFinal = $fila["kmFinalRedCaminera"];
		
		//indice del array
		$i=0;

		//Declaramos el array
		$segmentos = array();
	
		//Vemos si la red es menor o igual que 1 km
		if(($kmFinal - $kmInicio) <= 1){
			$segmentos[$i] = $kmInicio;	//Primer elemento
			$segmentos[$i+1] = $kmFinal;	//Segundo elemento			
		}
		//No es menos que 1 km
		else{
			//Calculamos el elemento siguiente + 1
			$siguiente = round($kmInicio)+1;
			$final = floor($kmFinal);	//aproximamos hacia abajo el km final					
			$partesKmFinal = str_split($kmFinal);	//Creamos un array del km final
			//Obtenemos el primer decimal del km final usando el array anterior
			for($j=0;$j<count($partesKmFinal);$j++){
				if(strcmp($partesKmFinal[$j],".") == 0){
					$decimalKmFinal = (int)$partesKmFinal[$j+1];	
				}
			}
					
			//Primer elemento		
			$segmentos[$i] = $kmInicio;
			$i++;		
		
			//Comenzamos a recorreo hasta llegar al anterior del km final
			while($siguiente < $final){
				//Siguiente elemento
				$segmentos[$i] = number_format($siguiente, 3, '.', '');		
				$siguiente = $siguiente + 1;
				$i++;			
			}
			
			//Preguntamos si el decimal anterior es menor a 5 
			if($decimalKmFinal < 5){
				$segmentos[$i] = $kmFinal;	
			}
			//El decimal es mayor o igual a 5 
			else{
				$segmentos[$i] = number_format($final, 3, '.', '');		
				$segmentos[$i+1] = $kmFinal;
			}			
		}	
		
		//Almacenamos la informacion en la DB
		for($i=0;$i<count($segmentos)-1;$i++){
			$consulta2 = "insert into segmentos (idSegmentos, idRedCaminera, rolCaminoSegmento, codigoCaminoSegmento, estadoSegmento, ".
			"kmInicioSegmento, kmFinalSegmento, numeroSegmento, nombreSegmento, subSegmentadoSegmentos) values ('', ".$fila["idRedCaminera"].
			", '".$fila["rolRedCaminera"]."', '".$fila["codigoRedCaminera"]."', 1, ".$segmentos[$i].", ".$segmentos[$i+1].", ".$k.", '".
			$fila["nombreRedCaminera"]."', 0)";
			$resultado2 = $conexion_db->query($consulta2);
			$k++;			
		}		
		//Actualizamos el camino indicandole que fue segmentado 
		$consulta3 = "update redCaminera set segmentadoRedCaminera = 1 where idRedCaminera = ".$fila["idRedCaminera"];
		$resultado3 = $conexion_db->query($consulta3);				
	}
	$conexion_db->close();		
	//header("Location: subSegmentacion.php");

	$tpl = new TemplatePower("barra2.html");
			
	$tpl->assignInclude("header", "header.html");
	$tpl->assignInclude("menu", "menu.html");	

	$tpl->prepare();

	$tpl->assign("PAGINA","subSegmentacion.php");
	$tpl->assign("MENSAJE","GENERANDO SEGMENTOS Y SUB-SEGMENTOS");
	$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
	$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
	$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
	$tpl->printToScreen();		
?>