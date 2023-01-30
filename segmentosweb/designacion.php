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
	
	//Obtenemos toda la información de la tabla subsegmentos
	$consulta = "select * from subSegmentos where designado = 0";
	$resultado = $conexion_db->query($consulta);
	//Recorremos el contenido de la consulta
	while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
		$consulta2 = "insert into designacion (idDesignacion, rolCaminoDesignacion, codigoCaminoDesignacion, estadoDesignacion, nroSegmentoDesignacion, nroTramoDesignacion, ".
		"nombreCaminoDesignacion, fajaDesignacion, saneamientoDesignacion, calzadaDesignacion, bermasDesignacion, senalizacionDesignacion, demarcacionDesignacion) values ('', '".
		$fila["rolSubSegmentos"]."', '".$fila["codigoSubSegmentos"]."', 1, ".$fila["segmentoSubSegmentos"].", ".$fila["tramoSubSegmentos"].", '".$fila["caminoSubSegmentos"]."', ".
		"'', '', '', '', '', '')";
		$resultado2 = $conexion_db->query($consulta2);					
		
		$consulta3 = "update subSegmentos set designado = 1 where idSubSegmentos = ".$fila["idSubSegmentos"];
		$resultado3 = $conexion_db->query($consulta3);
	}
	$conexion_db->close();
	$_SESSION["MENSAJE_CUMPLE"] = "SI";	
	header("Location: verRedCaminos.php");
?>
