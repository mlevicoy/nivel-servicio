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
	$consulta = "select * from segmentos where subSegmentadoSegmentos = 0";
	$resultado = $conexion_db->query($consulta);	
	
	//Se recorre el resultado de la consulta 
	while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
		//Sacamos el kmInicio y kmFinal
		$kmInicio = $fila["kmInicioSegmento"];
		$kmFinal = $fila["kmFinalSegmento"];
		
		//Nro del tramo
		$l=1;

		//indices
		$i=0;
		$k=0;
		$j=0;
		
		//Declaramos el array
		$subSegmentos = array();
		$kmInicioSiguiente_tmp = array();
		$kmFinalAnterior_tmp = array();
		
		$partesKmInicio = str_split(number_format($kmInicio, 3, '.', ''));	//Creamos un array del km inicio
		$partesKmFinal = str_split(number_format($kmFinal, 3, '.', ''));	//Creamos un arraty del km final
		
		//Obtenemos el indice-1 del km inicio
		for($j=0;$j<count($partesKmInicio);$j++){
			if(strcmp($partesKmInicio[$j],".") == 0){
					$k=$j;
			}
		}
		//Generamos el arreglo del km inicio		
		for($j=0;$j<=$k+1;$j++){
			$kmInicioSiguiente_tmp[$j] = $partesKmInicio[$j];				
		}
		
		//Obtenemos el indice del km final
		for($j=0;$j<count($partesKmFinal);$j++){
			if(strcmp($partesKmFinal[$j],".") == 0){
					$k=$j;
			}
		}	
		//Generamos el arreglo del km final		
		for($j=0;$j<=$k+1;$j++){
			$kmFinalAnterior_tmp[$j] = $partesKmFinal[$j];				
		}
			
		
		$kmInicioSiguiente = number_format(implode('',$kmInicioSiguiente_tmp), 3, '.', '');	//Valor siguiente del km inicio
		$kmFinalAnterior = number_format(implode('',$kmFinalAnterior_tmp), 3, '.', '');	//Valor anterior del km final

		//$final = floor($kmFinal);	//aproximamos hacia abajo el km final					
		$partesKmFinal = str_split($kmFinal);	//Creamos un array del km final
		//Obtenemos el primer decimal del km final usando el array anterior
		for($j=0;$j<count($partesKmFinal);$j++){
			if(strcmp($partesKmFinal[$j],".") == 0){
				$decimalKmFinal = (int)$partesKmFinal[$j+2];	
			}
		}
		
		//Comenzamos a llegar el arreglo de sub segmentos	
		$subSegmentos[$i] = $kmInicio;
		$i++;
		while($kmInicioSiguiente < ($kmFinalAnterior-0.1)){	
			$kmInicioSiguiente = $kmInicioSiguiente + 0.1;			
			$subSegmentos[$i] = number_format($kmInicioSiguiente, 3, ".", "");
			$i++;
		}		

		if($subSegmentos[$i-1] != $kmFinal){

			//$valor= number_format($kmFinal, 3, ".", "");
			//$resta_comprueba =  $valor - $subSegmentos[$i-1];
			//$valor2= number_format($resta_comprueba, 3, ".", "");
			//$valorfijo= 0.150;
			
			if($decimalKmFinal < 5){
				$subSegmentos[$i] = $kmFinal;
			}
			else {
				$kmInicioSiguiente = $kmInicioSiguiente + 0.1;			
				$subSegmentos[$i] = number_format($kmInicioSiguiente, 3, ".", "");
				$i++;
				$subSegmentos[$i] = $kmFinal;
			}
		}
		//Se termina de llenar el arreglo de sub segmentos
		
		//Se guarda la información en la DB
		for($k=0;$k<count($subSegmentos)-1;$k++){
			$consulta2 = "insert into subSegmentos (idSubSegmentos, idSegmento, rolSubSegmentos, codigoSubSegmentos, caminoSubSegmentos, ".
			"segmentoSubSegmentos, tramoSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos, estadoSubSegmentos, designado) ".
			"values ('', ".$fila["idSegmentos"].", '".$fila["rolCaminoSegmento"]."', '".$fila["codigoCaminoSegmento"]."', '".$fila["nombreSegmento"].
			"', ".$fila["numeroSegmento"].", ".$l.", ".number_format($subSegmentos[$k], 3, '.', '').", ".number_format($subSegmentos[$k+1], 3, '.', '').
			", 1, 0)";
			$resultado2 = $conexion_db->query($consulta2);
			$l++;
		}
		//Se le indica que el segmento ha sido tramado
		$consulta3 = "update segmentos set subSegmentadoSegmentos = 1 where idSegmentos = ".$fila["idSegmentos"];
		$resultado3 = $conexion_db->query($consulta3);	
	}	
	$conexion_db->close();		
	header("Location: designacion.php");
?>