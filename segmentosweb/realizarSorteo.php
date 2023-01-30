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
	
	///////////// ELEGIMOS LOS SEGMENTO ALEATORIOS /////////////
	
	if($_SESSION["BIMESTRE_SORTEO"] == 1000){
		$i=0;
		//Obtenemos los segmentos que no estan excluidos y los agregamos a un array
		$consulta = "select * from segmentos where estadoSegmento = 1";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$todoSegmentos[$i] = $fila["numeroSegmento"];
			$i++;		
		}
		$indiceSegmentosAleatorios = array();
		//Se guardan los segmentos en un arreglo
		for($j=0;$j<count($todoSegmentos);$j++){
			$segmentosAleatorios[$j] = $todoSegmentos[$j];
		}		
	}
	else{
		//Arreglo e indice
		$i=0;
		$j=0;
		$aux=0;
		$todoSegmentos = array();
		$indiceSegmentosAleatorios = array();
		$segmentosAleatorios = array();
			
		//Obtenemos los segmentos que no estan excluidos y los agregamos a un array
		$consulta = "select * from segmentos where estadoSegmento = 1";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$todoSegmentos[$i] = $fila["numeroSegmento"];
			$i++;		
		}
		
		//Obtenemos la cantidad de segmentos que se deben elegir
		$consulta2 = "select numeroSegmentos from inspeccionar where bimestreInspeccionar = ".$_SESSION["BIMESTRE_SORTEO"];
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);

		//Generamos el array aleatorio	
		while($j < $fila2["numeroSegmentos"]){
			$aux = mt_rand(0, (count($todoSegmentos)-1));	//Obtenemos aleatoriamente un indice de $segmentos
			if(in_array($aux, $indiceSegmentosAleatorios) === false){			
				array_push($indiceSegmentosAleatorios, $aux);
				$j++;
			}		
		}
		
		//Se guardan los segmentos en un arreglo
		for($j=0;$j<count($indiceSegmentosAleatorios);$j++){
			$segmentosAleatorios[$j] = $todoSegmentos[$indiceSegmentosAleatorios[$j]];
		}
	}
	
	//Ordenamos el array
	sort($segmentosAleatorios);	
	
	///////////// FIN ELEGIMOS LOS SEGMENTO ALEATORIOS /////////////
	
	///////////// CARGAMOS LA PAGINA /////////////
	
	//Obtenemos la información de los segmentos sorteados
	$consulta3 = "select * from segmentos where numeroSegmento in (".implode(",",$segmentosAleatorios).")";
	$resultado3 = $conexion_db->query($consulta3);	
	//Obtenemos la información del bimestre sorteado anterior
	$consulta4 = "select * from bimestre where estadoBimestre=0 order by idBimestre desc limit 0,1";
	$resultado4 = $conexion_db->query($consulta4);
	$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);	
	//Sumamos la red de caminos
	$consulta9 = "select sum(longitudRedCaminera) as longitud from redCaminera";
	$resultado9 = $conexion_db->query($consulta9);
	$fila9 = $resultado9->fetch_array(MYSQL_ASSOC);
	$kmIngresados = $fila9["longitud"];
	//Calculamos KMDescontados
	$suma_faja = 0;
	$suma_saneamiento = 0;
	$suma_calzada = 0;
	$suma_bermas = 0;
	$suma_senalizacion = 0;
	$suma_demarcacion = 0;			
			
	//Desafeccion por componente			
	$consulta7 = "select * from desafeccionreal";
	$resultado7 = $conexion_db->query($consulta7);			
	while($fila7 = $resultado7->fetch_array(MYSQL_ASSOC)){
		if(strcmp($fila7["fajaVialDesafeccionReal"],"SNS") == 0){ $suma_faja = $suma_faja + $fila7["longitudDesafeccionReal"]; }
		if(strcmp($fila7["saneamientoDesafeccionReal"],"SNS") == 0){ $suma_saneamiento = $suma_saneamiento + $fila7["longitudDesafeccionReal"]; }
		if(strcmp($fila7["calzadaDesafeccionReal"],"SNS") == 0){ $suma_calzada = $suma_calzada + $fila7["longitudDesafeccionReal"]; }
		if(strcmp($fila7["bermasDesafeccionReal"],"SNS") == 0){ $suma_bermas = $suma_bermas + $fila7["longitudDesafeccionReal"]; }
		if(strcmp($fila7["senalizacionDesafeccionReal"],"SNS") == 0){ $suma_senalizacion = $suma_senalizacion + $fila7["longitudDesafeccionReal"]; }
		if(strcmp($fila7["demarcacionDesafeccionReal"],"SNS") == 0){ $suma_demarcacion = $suma_demarcacion + $fila7["longitudDesafeccionReal"]; }
	}
			
	$suma_faja = $kmIngresados - number_format($suma_faja, 3, '.', '');
	$suma_saneamiento = $kmIngresados - number_format($suma_saneamiento, 3, '.', '');
	$suma_calzada = $kmIngresados - number_format($suma_calzada, 3, '.', '');
	$suma_bermas = $kmIngresados - number_format($suma_bermas, 3, '.', '');
	$suma_senalizacion = $kmIngresados - number_format($suma_senalizacion, 3, '.', '');
	$suma_demarcacion = $kmIngresados - number_format($suma_demarcacion, 3, '.', '');
	
	//PI y Nro FAJA
	if($suma_faja > 10){ $PI_FAJA = round(1000/$suma_faja); $PI_FAJA_AUX = 1000/$suma_faja; }
	else{ $PI_FAJA = 100; $PI_FAJA_AUX = 100; }					
	$nroSegmentosFaja = round(($PI_FAJA_AUX/100) * $suma_faja);
	
	//PI y Nro SANEAMIENTO
	if($suma_saneamiento > 10){ $PI_SANEAMIENTO = round(1000/$suma_saneamiento); $PI_SANEAMIENTO_AUX = 1000/$suma_saneamiento; }
	else{ $PI_SANEAMIENTO = 100; $PI_SANEAMIENTO_AUX = 100; }					
	$nroSegmentosSaneamiento = round(($PI_SANEAMIENTO_AUX/100) * $suma_saneamiento);
	
	//PI y Nro CALZADA
	if($suma_calzada > 10){ $PI_CALZADA = round(1000/$suma_calzada); $PI_CALZADA_AUX = 1000/$suma_calzada; }
	else{ $PI_CALZADA = 100; $PI_CALZADA_AUX = 100; }					
	$nroSegmentosCalzada = round(($PI_CALZADA_AUX/100) * $suma_calzada);
	
	//PI y Nro BERMAS
	if($suma_bermas > 10){ $PI_BERMAS = round(1000/$suma_bermas); $PI_BERMAS_AUX = 1000/$suma_bermas; }
	else{ $PI_BERMAS = 100; $PI_BERMAS_AUX = 100; }					
	$nroSegmentosBermas = round(($PI_BERMAS_AUX/100) * $suma_bermas);
	
	//PI y Nro SENALIZACION
	if($suma_senalizacion > 10){ $PI_SENALIZACION = round(1000/$suma_senalizacion); $PI_SENALIZACION_AUX = 1000/$suma_senalizacion; }
	else{ $PI_SENALIZACION = 100; $PI_SENALIZACION_AUX = 100; }					
	$nroSegmentosSenalizacion = round(($PI_SENALIZACION_AUX/100) * $suma_senalizacion);
	
	//PI y Nro DEMARCACION
	if($suma_demarcacion > 10){ $PI_DEMARCACION = round(1000/$suma_demarcacion); $PI_DEMARCACION_AUX = 1000/$suma_demarcacion; }
	else{ $PI_DEMARCACION = 100; $PI_DEMARCACION_AUX = 100; }					
	$nroSegmentosDemarcacion = round(($PI_DEMARCACION_AUX/100) * $suma_demarcacion);				
	
	//Cargamos la página
	$tpl = new TemplatePower("realizarSorteo.html");
	$tpl->prepare();
	$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
	$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
	$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
	$tpl->assign("BIMESTRE_PAGO",($fila4["NroPagoBimestre"]+1));
	$tpl->assign("LONGITUD_KM_FAJA",$suma_faja);
	$tpl->assign("LONGITUD_KM_SANEAMIENTO",$suma_saneamiento);
	$tpl->assign("LONGITUD_KM_BERMA",$suma_bermas);
	$tpl->assign("LONGITUD_KM_CALZADA",$suma_calzada);
	$tpl->assign("LONGITUD_KM_SENALIZACION",$suma_senalizacion);
	$tpl->assign("LONGITUD_KM_DEMARCACION",$suma_demarcacion);
	$tpl->assign("PORCENTAJE_PI_FAJA",$PI_FAJA);
	$tpl->assign("PORCENTAJE_PI_SANEAMIENTO",$PI_SANEAMIENTO);
	$tpl->assign("PORCENTAJE_PI_CALZADA",$PI_CALZADA);
	$tpl->assign("PORCENTAJE_PI_BERMA",$PI_BERMAS);
	$tpl->assign("PORCENTAJE_PI_SENALIZACION",$PI_SENALIZACION);
	$tpl->assign("PORCENTAJE_PI_DEMARCACION",$PI_DEMARCACION);
	$tpl->assign("SEGMENTO_SORTEO_FAJA",$nroSegmentosFaja);
	$tpl->assign("SEGMENTO_SORTEO_SANEAMIENTO",$nroSegmentosSaneamiento);
	$tpl->assign("SEGMENTO_SORTEO_CALZADA",$nroSegmentosCalzada);
	$tpl->assign("SEGMENTO_SORTEO_BERMA",$nroSegmentosBermas);
	$tpl->assign("SEGMENTO_SORTEO_DEMARCACION",$nroSegmentosDemarcacion);
	$tpl->assign("SEGMENTO_SORTEO_SENALIZACION",$nroSegmentosSenalizacion);
	//Llenamos el block
	$i=1;
	while($fila3 = $resultado3->fetch_array(MYSQL_ASSOC)){
		//Obtenemos el nro del camino
		$consulta8 = "select nroCaminoRedCaminera from redCaminera where rolRedCaminera = '".$fila3["rolCaminoSegmento"]."'";
		$resultado8 = $conexion_db->query($consulta8);
		$fila8 = $resultado8->fetch_array(MYSQL_ASSOC);
		//Llenamos la tabla
		$tpl->newBlock("SEGMENTOS_SORTEADOS");
		$tpl->assign("N",$i);
		$tpl->assign("N_CAMINO",$fila8["nroCaminoRedCaminera"]);		
		$tpl->assign("CODIGO",$fila3["codigoCaminoSegmento"]);
		$tpl->assign("ROL",$fila3["rolCaminoSegmento"]);
		$tpl->assign("NOMBRE_CAMINO",$fila3["nombreSegmento"]);
		$tpl->assign("KM_INICIO",$fila3["kmInicioSegmento"]);
		$tpl->assign("KM_TERMINO",$fila3["kmFinalSegmento"]);
		$tpl->assign("SEGMENTO",$fila3["numeroSegmento"]);
		$i++;
		//Guardamos la información en la tabla segmentos sorteados
		$consulta5 = "insert into segmentosSorteados (idSorteado, codigoCaminoSorteado, rolCaminoSorteado, nombreCaminoSorteado, kmInicioSorteado, kmFinalSorteado, ".
		"numeroSegmentoSorteado, bimestreSorteado, estadoIncumplimiento) values ('', '".$fila3["codigoCaminoSegmento"]."', '".$fila3["rolCaminoSegmento"].
		"', '".$fila3["nombreSegmento"]."', ".$fila3["kmInicioSegmento"].", ".$fila3["kmFinalSegmento"].", ".$fila3["numeroSegmento"].", ".$_SESSION["BIMESTRE_SORTEO"].
		", 0)";
		$resultado5 = $conexion_db->query($consulta5);
		//Le indicamos a la tabla bimestre cual bimeste fue sorteado
		$consulta6 = "update bimestre set estadoBimestre = 0 where NroBimestre = ".$_SESSION["BIMESTRE_SORTEO"];
		$resultado6 = $conexion_db->query($consulta6);
	}
	$conexion_db->close();
	$tpl->printToScreen();
?>