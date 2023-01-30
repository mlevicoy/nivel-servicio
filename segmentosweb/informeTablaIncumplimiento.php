<?php
	require_once("TemplatePower/class.TemplatePower.inc.php");		
	require_once("fpdf17/fpdf.php");
	require_once("conexion.php");
	require_once("sesiones.php");	
	header('Content-Type: text/html; charset=UTF-8');
	date_default_timezone_set('America/Santiago');
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");	

	//validarAdministrador();
	validaTiempo();
	
	//Primero ingreso a la página
	if(!isset($_POST["cargador"]) and !isset($_POST["buscador"])){
		//Se verifica si hay algun sorteo
		$consulta = "select count(*) as segSorteados from segmentosSorteados";
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		//No hay sorteo
		if($row["segSorteados"] == 0){		
			if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
				$tpl = new TemplatePower("administrador.html");	
				$tpl->assignInclude("header", "header.html");
			    $tpl->assignInclude("menu", "menu.html");													
			}
			else{
				$tpl = new TemplatePower("usuario.html");
			}
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("DISPLAY","compact;");	
			$tpl->assign("MENSAJE","NO SE PUEDE GENERAR EL INFORME, NO SE HA REALIZADO NING&Uacute;N SORTEO.");				
			$conexion_db->close();
			$tpl->printToScreen();										
		}
		//Si hay sorteo
		else{	
			//No se eligio un segmento
			if(!isset($_GET["id"])){
				$consulta2 = "select * from bimestre where estadoBimestre = 0";
				$resultado2 = $conexion_db->query($consulta2);			
				
				if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
					$tpl = new TemplatePower("elegirBimestre.html");
					$tpl->assignInclude("header", "header.html");
			        $tpl->assignInclude("menu", "menu.html");	
				}
				else{
					$tpl = new TemplatePower("elegirBimestre_usr.html");	
				}
				$tpl->prepare();
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
				$tpl->assign("REDIRECCIONAR","informeTablaIncumplimiento.php");	

				/*********************************************************
				$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
				$resultado = $conexion_db->query($consulta);
				while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("NOMBRES_COMPONENTES");
					$tpl->assign("valor_componente",$fila["nombreComponente"]);
				}
				//************************************************************/			

				while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
					if($fila2["NroPagoBimestre"] == 1000){
						$tpl->newBlock("NUMERO_BIMESTRE");
						$tpl->assign("numBimestre",$fila2["NroBimestre"]);
						$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");	
					}
					else{
						$tpl->newBlock("NUMERO_BIMESTRE");
						$tpl->assign("numBimestre",$fila2["NroBimestre"]);
						$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila2["NroPagoBimestre"]);	
					}
				}
				
				//************************************************************************************************************
				$conexion_db->close();
				$tpl->printToScreen();			
			}
			//Se eligio un segmento
			else{
				//Valor del segmento
				$segmento = $_GET["id"];
				//Toma la informacion del bimestre
				$consulta2 = "select * from bimestre where NroBimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				//Se seleccionar la informacion de los segmentos correspondiente al segmento
				$consulta4 = "select * from segmentosSorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado4 = $conexion_db->query($consulta4);				
				//Se selecciona la información del segmento en especifico
				$consulta5 = "select * from segmentosSorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"].
				" and numeroSegmentoSorteado = ".$segmento;
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				//Verificamos si habilitamos o no el botón generar informe
				$consulta11 = "select count(*) as cantidadSegmentos from segmentossorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"]." and estadoIncumplimiento = 0";
				$resultado11 = $conexion_db->query($consulta11);
				$fila11 = $resultado11->fetch_array(MYSQL_ASSOC);			
				//Tomamos los tramos del segmento
				$consulta12 = "select kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmento;
				$resultado12 = $conexion_db->query($consulta12);
				//Obtenemos el rolOriginal
			/*	$consulta13 = "select rolOriginal from asociacion where rolCreado = '".$fila5["rolCaminoSorteado"]."'";				
				$resultado13 = $conexion_db->query($consulta13);
				$fila13 = $resultado13->fetch_array(MYSQL_ASSOC);
			*/
				//Obtenemos el nro del camino
				$consulta16 = "select nroCaminoRedCaminera from redCaminera where rolRedCaminera = '".$fila5["rolCaminoSorteado"]."'";
				$resultado16 = $conexion_db->query($consulta16);
				$fila16 = $resultado16->fetch_array(MYSQL_ASSOC);
				
				//Se carga la página
				$tpl = new TemplatePower("informeTablaIncumplimiento.html");														
				$tpl->prepare();
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));						
				$tpl->assign("PAGONUMERO",$fila2["NroPagoBimestre"]);
				$tpl->assign("FECHA_SORTEO",utf8_encode(strftime('%A, %d de %B del %Y',strtotime($fila2["fechaTerminoBimestre"]))));		
				$tpl->assign("ROLSEGMENTO",$fila5["rolCaminoSorteado"]);
				$tpl->assign("NOMBRESEGMENTO",$fila5["nombreCaminoSorteado"]);
				$tpl->assign("NROCAMINO",$fila16["nroCaminoRedCaminera"]);

				/*********************************************************
				$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
				$resultado = $conexion_db->query($consulta);
				while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("NOMBRES_COMPONENTES");
					$tpl->assign("valor_componente",$fila["nombreComponente"]);
				}
				//*************************************************************/

				if($fila11["cantidadSegmentos"] == 0){
					$tpl->assign("HABILITAR_BOTON","");		
				}
				else{
					$tpl->assign("HABILITAR_BOTON","disabled");		
				}
				$tpl->assign("NUMEROSEGMENTO",$segmento);
				$tpl->assign("KMINICIOSEGMENTO",$fila5["kmInicioSorteado"]);
				$tpl->assign("KMFINALSEGMENTO",$fila5["kmFinalSorteado"]);											
				$tpl->assign("SELECT_FINAL", "");
				$tpl->assign("VALOR_SELECT_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");							
				//Se llena los segmentos
				while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
					if($segmento == $fila4["numeroSegmentoSorteado"]){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("SELECT_INICIO",$fila4["numeroSegmentoSorteado"]);
						$tpl->assign("VALOR_SELECT_INICIO","SEGMENTO N&deg; ".$fila4["numeroSegmentoSorteado"]);
					}
					else{					
						$tpl->newBlock("SEGMENTO");
						$tpl->assign("NRO_SEGMENTO",$fila4["numeroSegmentoSorteado"]);
						$tpl->assign("NOMBRE_SEGMENTO","SEGMENTO N&deg; ".$fila4["numeroSegmentoSorteado"]);			
					}
				}

				//********************************************************** otra ves
				$consultaSQL = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
				$resultadoSQL = $conexion_db->query($consultaSQL);
				while($filacomponentes = $resultadoSQL->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("NOMBRES_COMPONENTES");
					$tpl->assign("valor_componente",$filacomponentes["nombreComponente"]);
				}
				//*************************************************************

				//Buscamos los datos de faja	
					//Buscamos los faja que esten SNS en el segmento en designacion
				$i=0;
				$fajadesignacion_array = array();
				$consulta14 = "select fajaDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado14 = $conexion_db->query($consulta14);
				while($fila14 = $resultado14->fetch_array(MYSQL_ASSOC)){
					$fajadesignacion_array[$i] = $fila14["fajaDesignacion"];
					$i++;
				}	
				while($i < 15){
					$fajadesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de faja en inclumplimientos
				$i=0;
				$faja_array = array();
				$comentariofaja_array = array();
				$consulta3 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'FAJA'";				
				$resultado3 = $conexion_db->query($consulta3);
				$cantidad_faja = $resultado3->num_rows;		
				if($cantidad_faja > 0){					
					$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$faja_array[$i] = $fila3["t".($i+1)."Incumplimiento"];
							$comentariofaja_array[$i] = $fila3["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$faja_array[$i] = $fila3["nroIncumplimiento"];								
							}
							else if($i == 16){
								$faja_array[$i] = $fila3["porcentajeIncumplimiento"];
							}	
						}
					}				
				}
				else{
					for($i=0;$i<15;$i++){
						$faja_array[$i] = "";
						$comentariofaja_array[$i] = "";						
					}
					$faja_array[15] = "";
					$faja_array[16] = "";	
				}
					//Unimos fajadesignacion_array y faja_array
				for($i=0;$i<15;$i++){
					if(strcmp($fajadesignacion_array[$i], "SNS") == 0){
						$faja_array[$i] = "SNS";
					}
				}
				
				//Buscamos los datos de saneamiento
					//Buscamos los saneamiento que esten SNS en el segmento en designacion
				$i=0;
				$saneamientodesignacion_array = array();
				$consulta15 = "select saneamientoDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado15 = $conexion_db->query($consulta15);
				while($fila15 = $resultado15->fetch_array(MYSQL_ASSOC)){
					$saneamientodesignacion_array[$i] = $fila15["saneamientoDesignacion"];
					$i++;
				}	
				while($i < 15){
					$saneamientodesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de saneamiento en inclumplimientos
				$i=0;
				$saneamiento_array = array();
				$comentariosaneamiento_array = array();				
				$consulta6 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'SANEAMIENTO'";			
				$resultado6 = $conexion_db->query($consulta6);	
				$cantidad_saneamiento = $resultado6->num_rows;		
				if($cantidad_saneamiento > 0){					
					$fila6= $resultado6->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$saneamiento_array[$i] = $fila6["t".($i+1)."Incumplimiento"];
							$comentariosaneamiento_array[$i] = $fila6["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$saneamiento_array[$i] = $fila6["nroIncumplimiento"];								
							}
							else if($i == 16){
								$saneamiento_array[$i] = $fila6["porcentajeIncumplimiento"];
							}	
						}
					}				
				}	
				else{
					for($i=0;$i<15;$i++){
						$saneamiento_array[$i] = "";
						$comentariosaneamiento_array[$i] = "";						
					}
					$saneamiento_array[15] = "";
					$saneamiento_array[16] = "";	
				}
					//Unimos saneamientodesignacion_array y saneamiento_array
				for($i=0;$i<15;$i++){
					if(strcmp($saneamientodesignacion_array[$i], "SNS") == 0){
						$saneamiento_array[$i] = "SNS";
					}
				}
				//Buscamos los datos de calzada
					//Buscamos la calzada que esten SNS en el segmento en designacion
				$i=0;
				$calzadadesignacion_array = array();
				$consulta15 = "select calzadaDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado15 = $conexion_db->query($consulta15);
				while($fila15 = $resultado15->fetch_array(MYSQL_ASSOC)){
					$calzadadesignacion_array[$i] = $fila15["calzadaDesignacion"];
					$i++;
				}	
				while($i < 15){
					$calzadadesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de calzada en inclumplimientos
				$i=0;
				$calzada_array = array();				
				$comentariocalzada_array = array();				
				$consulta7 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'CALZADA'";
				$resultado7 = $conexion_db->query($consulta7);	
				$cantidad_calzada = $resultado7->num_rows;		
				if($cantidad_calzada > 0){					
					$fila7= $resultado7->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$calzada_array[$i] = $fila7["t".($i+1)."Incumplimiento"];
							$comentariocalzada_array[$i] = $fila7["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$calzada_array[$i] = $fila7["nroIncumplimiento"];								
							}
							else if($i == 16){
								$calzada_array[$i] = $fila7["porcentajeIncumplimiento"];
							}	
						}
					}				
				}
				else{
					for($i=0;$i<15;$i++){
						$calzada_array[$i] = "";
						$comentariocalzada_array[$i] = "";						
					}
					$calzada_array[15] = "";
					$calzada_array[16] = "";	
				}
					//Unimos calzadadesignacion_array y calzada_array
				for($i=0;$i<15;$i++){
					if(strcmp($calzadadesignacion_array[$i], "SNS") == 0){
						$calzada_array[$i] = "SNS";
					}
				}
				//Buscamos los datos de berma
					//Buscamos la berma que esten SNS en el segmento en designacion
				$i=0;
				$bermadesignacion_array = array();
				$consulta15 = "select bermasDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado15 = $conexion_db->query($consulta15);
				while($fila15 = $resultado15->fetch_array(MYSQL_ASSOC)){
					$bermadesignacion_array[$i] = $fila15["bermasDesignacion"];
					$i++;
				}	
				while($i < 15){
					$bermadesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de berma en inclumplimientos
				$i=0;
				$berma_array = array();							
				$comentarioberma_array = array();								
				$consulta8 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'BERMA'";
				$resultado8 = $conexion_db->query($consulta8);	
				$cantidad_berma = $resultado8->num_rows;		
				if($cantidad_berma > 0){
					$fila8 = $resultado8->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$berma_array[$i] = $fila8["t".($i+1)."Incumplimiento"];
							$comentarioberma_array[$i] = $fila8["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$berma_array[$i] = $fila8["nroIncumplimiento"];								
							}
							else if($i == 16){
								$berma_array[$i] = $fila8["porcentajeIncumplimiento"];
							}	
						}
					}
				}
				else{
					for($i=0;$i<15;$i++){
						$berma_array[$i] = "";
						$comentarioberma_array[$i] = "";						
					}
					$berma_array[15] = "";
					$berma_array[16] = "";	
				}
					//Unimos bermadesignacion_array y berma_array
				for($i=0;$i<15;$i++){
					if(strcmp($bermadesignacion_array[$i], "SNS") == 0){
						$berma_array[$i] = "SNS";
					}
				}
				//Buscamos los datos de senalizacion
					//Buscamos la senalizacion que esten SNS en el segmento en designacion
				$i=0;
				$senalizaciondesignacion_array = array();
				$consulta15 = "select senalizacionDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado15 = $conexion_db->query($consulta15);
				while($fila15 = $resultado15->fetch_array(MYSQL_ASSOC)){
					$senalizaciondesignacion_array[$i] = $fila15["senalizacionDesignacion"];
					$i++;
				}	
				while($i < 15){
					$senalizaciondesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de senalizacion en inclumplimientos
				$i=0;
				$senalizacion_array = array();	
				$comentariosenalizacion_array = array();					
				$consulta9 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'SENALIZACION'";
				$resultado9 = $conexion_db->query($consulta9);	
				$cantidad_senalizacion = $resultado9->num_rows;		
				if($cantidad_senalizacion > 0){
					$fila9 = $resultado9->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$senalizacion_array[$i] = $fila9["t".($i+1)."Incumplimiento"];
							$comentariosenalizacion_array[$i] = $fila9["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$senalizacion_array[$i] = $fila9["nroIncumplimiento"];								
							}
							else if($i == 16){
								$senalizacion_array[$i] = $fila9["porcentajeIncumplimiento"];
							}	
						}
					}
				}
				else{
					for($i=0;$i<15;$i++){
						$senalizacion_array[$i] = "";
						$comentariosenalizacion_array[$i] = "";						
					}
					$senalizacion_array[15] = "";
					$senalizacion_array[16] = "";	
				}
					//Unimos senalizaciondesignacion_array y senalizacion_array
				for($i=0;$i<15;$i++){
					if(strcmp($senalizaciondesignacion_array[$i], "SNS") == 0){
						$senalizacion_array[$i] = "SNS";
					}
				}
				//Buscamos los datos de demarcacion
					//Buscamos la demarcacion que esten SNS en el segmento en designacion
				$i=0;
				$demarcaciondesignacion_array = array();
				$consulta15 = "select demarcacionDesignacion from designacion where nroSegmentoDesignacion = ".$segmento;
				$resultado15 = $conexion_db->query($consulta15);
				while($fila15 = $resultado15->fetch_array(MYSQL_ASSOC)){
					$demarcaciondesignacion_array[$i] = $fila15["demarcacionDesignacion"];
					$i++;
				}	
				while($i < 15){
					$demarcaciondesignacion_array[$i] = "";
					$i++;
				}
					//Buscamos los resultados de demarcacion en inclumplimientos
				$i=0;
				$demarcacion_array = array();	
				$comentariodemarcacion_array = array();				
				$consulta10 = "select t1Incumplimiento, t1Comentario, t2Incumplimiento, t2Comentario, t3Incumplimiento, t3Comentario, t4Incumplimiento, ".
				"t4Comentario, t5Incumplimiento, t5Comentario, t6Incumplimiento, t6Comentario, t7Incumplimiento, t7Comentario, t8Incumplimiento, ".
				"t8Comentario, t9Incumplimiento, t9Comentario, t10Incumplimiento, t10Comentario, t11Incumplimiento, t11Comentario, t12Incumplimiento, ".
				"t12Comentario, t13Incumplimiento, t13Comentario, t14Incumplimiento, t14Comentario, t15Incumplimiento, t15Comentario, nroIncumplimiento, ".
				"porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento = ".
				$segmento." and componenteIncumplimiento = 'DEMARCACION'";
				$resultado10 = $conexion_db->query($consulta10);	
				$cantidad_demarcacion = $resultado10->num_rows;		
				if($cantidad_demarcacion > 0){
					$fila10 = $resultado10->fetch_array(MYSQL_ASSOC);
					for($i=0;$i<17;$i++){
						if($i <= 14){							
							$demarcacion_array[$i] = $fila10["t".($i+1)."Incumplimiento"];
							$comentariodemarcacion_array[$i] = $fila10["t".($i+1)."Comentario"];							
						}
						else{
							if($i == 15){
								$demarcacion_array[$i] = $fila10["nroIncumplimiento"];								
							}
							else if($i == 16){
								$demarcacion_array[$i] = $fila10["porcentajeIncumplimiento"];
							}	
						}
					}
				}
				else{
					for($i=0;$i<15;$i++){
						$demarcacion_array[$i] = "";
						$comentariodemarcacion_array[$i] = "";						
					}
					$demarcacion_array[15] = "";
					$demarcacion_array[16] = "";	
				}
					//Unimos demarcaciondesignacion_array y demarcacion_array
				for($i=0;$i<15;$i++){
					if(strcmp($demarcaciondesignacion_array[$i], "SNS") == 0){
						$demarcacion_array[$i] = "SNS";
					}
				}
				
				//Se llena los <td>
				$j=0;
				$i=0;	
				$k=1;				
				while($fila12 = $resultado12->fetch_array(MYSQL_ASSOC)){									
					//Se llenan los tramos
					$tpl->newBlock("TRAMOS_TITULO");
					$tpl->assign("TRAMO_DESDE",$fila12["kmInicioSubSegmento"]);
					$tpl->assign("TRAMO_HASTA",$fila12["kmFinalSubSegmentos"]);		
										
					//Se llena la faja
					$tpl->assign("NOMBRE_FAJA", "fajaTramo".($i+1));
					$tpl->assign("NOMBRE_COMENTARIO_FAJA", "comentarioFaja".($i+1));
					$tpl->assign("SELECT_FAJA_TRAMO",$faja_array[$i]);
					$tpl->assign("VALOR_SELECT_FAJA_TRAMO",$faja_array[$i]);
					$tpl->assign("VALOR_COMENTARIO_FAJA", $comentariofaja_array[$i]);					
					//Se llena el saneamiento
					$tpl->assign("NOMBRE_SANEAMIENTO", "saneamientoTramo".($i+1));
					$tpl->assign("NOMBRE_COMENTARIO_SANEAMIENTO", "comentarioSaneamiento".($i+1));
					$tpl->assign("SELECT_SANEAMIENTO_TRAMO",$saneamiento_array[$i]);
					$tpl->assign("VALOR_SELECT_SANEAMIENTO_TRAMO",$saneamiento_array[$i]);
					$tpl->assign("VALOR_COMENTARIO_SANEAMIENTO", $comentariosaneamiento_array[$i]);
					//Se llena la calzada					
					$tpl->assign("NOMBRE_CALZADA", "calzadaTramo".($i+1));
					$tpl->assign("NOMBRE_COMENTARIO_CALZADA", "comentarioCalzada".($i+1));
					$tpl->assign("SELECT_CALZADA_TRAMO", $calzada_array[$i]);
					$tpl->assign("VALOR_SELECT_CALZADA_TRAMO", $calzada_array[$i]);
					$tpl->assign("VALOR_COMENTARIO_CALZADA", $comentariocalzada_array[$i]);	
					//Se llena la berma
					$tpl->assign("NOMBRE_BERMA", "bermaTramo".($i+1));	
					$tpl->assign("NOMBRE_COMENTARIO_BERMA", "comentarioBerma".($i+1));					
					$tpl->assign("SELECT_BERMAS_TRAMO",$berma_array[$i]);
					$tpl->assign("VALOR_SELECT_BERMAS_TRAMO",$berma_array[$i]);					
					$tpl->assign("VALOR_COMENTARIO_BERMA", $comentarioberma_array[$i]);
					//Se llena la senalizacion										
					$tpl->assign("NOMBRE_SENALIZACION", "senalizacionTramo".($i+1));	
					$tpl->assign("NOMBRE_COMENTARIO_SENALIZACION", "comentarioSenalizacion".($i+1));										
					$tpl->assign("SELECT_SENALIZACION_TRAMO", $senalizacion_array[$i]);
					$tpl->assign("VALOR_SELECT_SENALIZACION_TRAMO", $senalizacion_array[$i]);
					$tpl->assign("VALOR_COMENTARIO_SENALIZACION", $comentariosenalizacion_array[$i]);
					//Se llena la demarcacion
					$tpl->assign("NOMBRE_DEMARCACION", "demarcacionTramo".($i+1));	
					$tpl->assign("NOMBRE_COMENTARIO_DEMARCACION", "comentarioDemarcacion".($i+1));										
					$tpl->assign("SELECT_DEMARCACION_TRAMO", $demarcacion_array[$i]);
					$tpl->assign("VALOR_SELECT_DEMARCACION_TRAMO", $demarcacion_array[$i]);
					$tpl->assign("VALOR_COMENTARIO_DEMARCACION", $comentariodemarcacion_array[$i]);					
										
					//Se llena los select de faja
					if(strcmp($faja_array[$i],"C") == 0){
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","NC");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","-");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","");                           		
					}
					else if(strcmp($faja_array[$i],"NC") == 0){
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","-");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","");                           		
					}
					else if(strcmp($faja_array[$i],"SNS") == 0){
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","NC");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","-");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","");                           		
					}	
					else if(strcmp($faja_array[$i],"-") == 0){
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","NC");
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","");                           		
					}
					else{
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","NC");
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
						$tpl->newBlock("FAJA_TRAMO");
						$tpl->assign("VALOR_FAJA_TRAMO","-");                           		
					}
					//Se llena los select de saneamiento
					if(strcmp($saneamiento_array[$i],"C") == 0){													
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           	
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","");                           			
					}
					else if(strcmp($saneamiento_array[$i],"NC") == 0){
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           	
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","");                           		
					}
					else if(strcmp($saneamiento_array[$i],"SNS") == 0){
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");                           	
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","");                           		
					}
					else if(strcmp($saneamiento_array[$i],"-") == 0){
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");                           	
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           		
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","");                           		
					}
					else{
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");                           	
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           		
						$tpl->newBlock("SANEAMIENTO_TRAMO");
						$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
					}					
					//Se llena los select de calzada					
					if(strcmp($calzada_array[$i],"C") == 0){
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","NC");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","SNS");                           	
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","-");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","");
					}
					else if(strcmp($calzada_array[$i],"NC") == 0){
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","C");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","SNS");                           	
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","-");					                          		
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","");					                          		
					}
					else if(strcmp($calzada_array[$i],"SNS") == 0){
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","C");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","NC");                           	
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","-");					                          		
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","");					                          		
					}
					else if(strcmp($calzada_array[$i],"-") == 0){
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","C");                           	
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","NC");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","SNS");					                          		
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","");					                          		
					}
					else{
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","C");                           	
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","NC");
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","SNS");					                          		
						$tpl->newBlock("CALZADA_TRAMO");
						$tpl->assign("VALOR_CALZADA_TRAMO","-");					                          		
					}	
					//Se llena los select de berma
					if(strcmp($berma_array[$i],"C") == 0){
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","NC");
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           	
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","-");                           		
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","");                           		
					}
					else if(strcmp($berma_array[$i],"NC") == 0){
					$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","C");
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           	
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","-");                           		
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","");                           		
					}
					else if(strcmp($berma_array[$i],"SNS") == 0){
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","C");
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","NC");                           	
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","-");                           		
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","");                           		
					}
					else if(strcmp($berma_array[$i],"-") == 0){
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","C");                           	
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","NC");
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           		
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","");                           		
					}	
					else{
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","C");                           	
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","NC");
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           		
						$tpl->newBlock("BERMAS_TRAMO");
						$tpl->assign("VALOR_BERMAS_TRAMO","-");                           							
					}		
					//Se llena los select de senalizacion
					if(strcmp($senalizacion_array[$i],"C") == 0){
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           	
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","");                           		
					}
					else if(strcmp($senalizacion_array[$i],"NC") == 0){
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","C");
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           	
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","");                           		
					}
					else if(strcmp($senalizacion_array[$i],"SNS") == 0){
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","C");
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");                           	
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","");                           		
					}
					else if(strcmp($senalizacion_array[$i],"-") == 0){
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","C");                           	
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           		
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","");                           		
					}
					else{
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","C");                           	
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           		
						$tpl->newBlock("SENALIZACION_TRAMO");
						$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
					}	
					//Se llena los select de demarcacion
					if(strcmp($demarcacion_array[$i],"C") == 0){											
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");                           		
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","-");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","");                           	
					}
					else if(strcmp($demarcacion_array[$i],"NC") == 0){
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","C");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");                           		
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","-");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","");                           	
					}
					else if(strcmp($demarcacion_array[$i],"SNS") == 0){
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","C");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");                           		
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","-");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","");                           	
					}
					else if(strcmp($demarcacion_array[$i],"-") == 0){
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","C");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");                           		
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","");                           	
					}
					else{
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","C");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");                           		
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");
						$tpl->newBlock("DEMARCACION_TRAMO");
						$tpl->assign("VALOR_DEMARCACION_TRAMO","-");                           	
					}
					$i++;				
					$j++;
				}
				//LLenamos lo que no tienen tramos
				while($j<15){
					//Tramos
					$tpl->newBlock("TRAMOS_TITULO");
					$tpl->assign("TRAMO_DESDE","-");
					$tpl->assign("TRAMO_HASTA","-");
					//Faja				
					$tpl->assign("NOMBRE_FAJA", "fajaTramo".($i+1));	
					$tpl->assign("SELECT_FAJA_TRAMO","-");
					$tpl->assign("VALOR_SELECT_FAJA_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_FAJA", "comentarioFaja".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_FAJA", "-");
					//Saneamiento
					$tpl->assign("NOMBRE_SANEAMIENTO", "saneamientoTramo".($i+1));	
					$tpl->assign("SELECT_SANEAMIENTO_TRAMO","-");
					$tpl->assign("VALOR_SELECT_SANEAMIENTO_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_SANEAMIENTO", "comentarioSaneamiento".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_SANEAMIENTO", "-");
					//Calzada
					$tpl->assign("NOMBRE_CALZADA", "calzadaTramo".($i+1));	
					$tpl->assign("SELECT_CALZADA_TRAMO","-");
					$tpl->assign("VALOR_SELECT_CALZADA_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_CALZADA", "comentarioCalzada".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_CALZADA", "-");
					//Berma
					$tpl->assign("NOMBRE_BERMA", "bermaTramo".($i+1));	
					$tpl->assign("SELECT_BERMAS_TRAMO","-");
					$tpl->assign("VALOR_SELECT_BERMAS_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_BERMA", "comentarioBerma".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_BERMA", "-");
					//Senalizacion
					$tpl->assign("NOMBRE_SENALIZACION", "senalizacionTramo".($i+1));	
					$tpl->assign("SELECT_SENALIZACION_TRAMO","-");
					$tpl->assign("VALOR_SELECT_SENALIZACION_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_SENALIZACION", "comentarioSenalizacion".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_SENALIZACION", "-");
					//Demarcacion
					$tpl->assign("NOMBRE_DEMARCACION", "demarcacionTramo".($i+1));	
					$tpl->assign("SELECT_DEMARCACION_TRAMO","-");
					$tpl->assign("VALOR_SELECT_DEMARCACION_TRAMO","-");
					$tpl->assign("NOMBRE_COMENTARIO_DEMARCACION", "comentarioDemarcacion".($i+1));										
					$tpl->assign("VALOR_COMENTARIO_DEMARCACION", "-");
					
					//Faja
					$tpl->newBlock("FAJA_TRAMO");
					$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
					$tpl->newBlock("FAJA_TRAMO");
					$tpl->assign("VALOR_FAJA_TRAMO","NC");
					$tpl->newBlock("FAJA_TRAMO");
					$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
					$tpl->newBlock("FAJA_TRAMO");
					$tpl->assign("VALOR_FAJA_TRAMO","");                           							
					//saneamiento									
					$tpl->newBlock("SANEAMIENTO_TRAMO");
					$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");                           	
					$tpl->newBlock("SANEAMIENTO_TRAMO");
					$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
					$tpl->newBlock("SANEAMIENTO_TRAMO");
					$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           		
					$tpl->newBlock("SANEAMIENTO_TRAMO");
					$tpl->assign("VALOR_SANEAMIENTO_TRAMO","");                         
					//Calzada					
					$tpl->newBlock("CALZADA_TRAMO");
					$tpl->assign("VALOR_CALZADA_TRAMO","C");                           	
					$tpl->newBlock("CALZADA_TRAMO");
					$tpl->assign("VALOR_CALZADA_TRAMO","NC");
					$tpl->newBlock("CALZADA_TRAMO");
					$tpl->assign("VALOR_CALZADA_TRAMO","SNS");					                          		
					$tpl->newBlock("CALZADA_TRAMO");
					$tpl->assign("VALOR_CALZADA_TRAMO","");					            
					//berma
					$tpl->newBlock("BERMAS_TRAMO");
					$tpl->assign("VALOR_BERMAS_TRAMO","C");                           	
					$tpl->newBlock("BERMAS_TRAMO");
					$tpl->assign("VALOR_BERMAS_TRAMO","NC");
					$tpl->newBlock("BERMAS_TRAMO");
					$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           		
					$tpl->newBlock("BERMAS_TRAMO");
					$tpl->assign("VALOR_BERMAS_TRAMO","");                           	
					//Senalizacion
					$tpl->newBlock("SENALIZACION_TRAMO");
					$tpl->assign("VALOR_SENALIZACION_TRAMO","C");                           	
					$tpl->newBlock("SENALIZACION_TRAMO");
					$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
					$tpl->newBlock("SENALIZACION_TRAMO");
					$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           		
					$tpl->newBlock("SENALIZACION_TRAMO");
					$tpl->assign("VALOR_SENALIZACION_TRAMO","");                           		
					//Demarcacion					
					$tpl->newBlock("DEMARCACION_TRAMO");
					$tpl->assign("VALOR_DEMARCACION_TRAMO","C");                           	
					$tpl->newBlock("DEMARCACION_TRAMO");
					$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");
					$tpl->newBlock("DEMARCACION_TRAMO");
					$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");                           		
					$tpl->newBlock("DEMARCACION_TRAMO");
					$tpl->assign("VALOR_DEMARCACION_TRAMO","");										
					$i++;
					$j++;
				}
				
				//Faltan los números y porcentajes de incumplimientos
				$tpl->gotoBlock("_ROOT");
				$tpl->assign("NRO_INCUMPLIMIENTO_FAJA", $faja_array[15]);
				$tpl->assign("NRO_INCUMPLIMIENTO_SANEAMIENTO", $saneamiento_array[15]);
				$tpl->assign("NRO_INCUMPLIMIENTO_CALZADA", $calzada_array[15]);
				$tpl->assign("NRO_INCUMPLIMIENTO_BERMA", $berma_array[15]);
				$tpl->assign("NRO_INCUMPLIMIENTO_SENALIZACION", $senalizacion_array[15]);
				$tpl->assign("NRO_INCUMPLIMIENTO_DEMARCACION", $demarcacion_array[15]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_FAJA", $faja_array[16]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SANEAMIENTO", $saneamiento_array[16]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_CALZADA", $calzada_array[16]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_BERMA", $berma_array[16]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SENALIZACION", $senalizacion_array[16]);
				$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_DEMARCACION", $demarcacion_array[16]);			
				
				//Cierre de conexion			
				$conexion_db->close();
				$tpl->printToScreen();									
			}
		}
	}
	//Carga Inicial
	else if(isset($_POST["cargador"]) and !isset($_POST["buscador"])){
		//Bimestre a trabajar		
		$bimestre = $_POST["numeroBimestre"];
		//Bimestre informe
		$_SESSION["BIMESTRE_INFORME"] = $bimestre;
		//Se seleccionar la informacion de los segmentos correspondiente al segmento
		$consulta = "select * from segmentossorteados where bimestreSorteado = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//Toma la informacion del bimestre
		$consulta2 = "select * from bimestre where NroBimestre = ".$bimestre;
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
		//Verificamos si habilitamos o no el botón generar informe
		$consulta11 = "select count(*) as cantidadSegmentos from segmentossorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"].
		" and estadoIncumplimiento = 0";
		$resultado11 = $conexion_db->query($consulta11);
		$fila11 = $resultado11->fetch_array(MYSQL_ASSOC);
		//Se carga la página
		$tpl = new TemplatePower("informeTablaIncumplimiento.html");														
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));

		//*********************************************************
				$consultaSQL = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
				$resultadoSQL = $conexion_db->query($consultaSQL);
				while($filaSQL = $resultadoSQL->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("NOMBRES_COMPONENTES");
					$tpl->assign("valor_componente",$filaSQL["nombreComponente"]);
				}
				//*************************************************************

		if($fila11["cantidadSegmentos"] == 0){
			$tpl->assign("HABILITAR_BOTON","");		
		}
		else{
			$tpl->assign("HABILITAR_BOTON","disabled");		
		}				
		$tpl->assign("PAGONUMERO",$fila2["NroPagoBimestre"]);
		$tpl->assign("FECHA_SORTEO",utf8_encode(strftime('%A, %d de %B del %Y',strtotime($fila2["fechaTerminoBimestre"]))));		
		//Select segmentos
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("SELECT_INICIO", "");
		$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR OPCI&Oacute;N ---");
		$tpl->assign("SELECT_FINAL", "");
		$tpl->assign("VALOR_SELECT_FINAL", "");							
		$i=0;
		while($i < 16){
			//Se llenan los tramos
			$tpl->newBlock("TRAMOS_TITULO");
			$tpl->assign("TRAMO_DESDE","-");
			$tpl->assign("TRAMO_HASTA","-");
										
			//Se llena los nombres
			$tpl->assign("NOMBRE_FAJA", "fajaTramo".($i+1));
			$tpl->assign("NOMBRE_SANEAMIENTO", "saneamientoTramo".($i+1));
			$tpl->assign("NOMBRE_CALZADA", "calzadaTramo".($i+1));
			$tpl->assign("NOMBRE_BERMA", "bermaTramo".($i+1));
			$tpl->assign("NOMBRE_SENALIZACION", "senalizacionTramo".($i+1));
			$tpl->assign("NOMBRE_DEMARCACION", "demarcacionTramo".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_FAJA","comentarioFaja".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_SANEAMIENTO","comentarioSaneamiento".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_CALZADA","comentarioCalzada".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_BERMA","comentarioBerma".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_SENALIZACION","comentarioSenalizacion".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_DEMARCACION","comentarioDemarcacion".($i+1));
					
			//Se llena la faja
			$tpl->assign("SELECT_FAJA_TRAMO","");
			$tpl->assign("VALOR_SELECT_FAJA_TRAMO","");
			//Se llena el saneamiento
			$tpl->assign("SELECT_SANEAMIENTO_TRAMO","");
			$tpl->assign("VALOR_SELECT_SANEAMIENTO_TRAMO","");
			//Se llena la calzada
			$tpl->assign("SELECT_CALZADA_TRAMO","");
			$tpl->assign("VALOR_SELECT_CALZADA_TRAMO","");
			//Se llena la berma
			$tpl->assign("SELECT_BERMAS_TRAMO","");
			$tpl->assign("VALOR_SELECT_BERMAS_TRAMO","");
			//Se llena la demarcacion
			$tpl->assign("SELECT_DEMARCACION_TRAMO","");
			$tpl->assign("VALOR_SELECT_DEMARCACION_TRAMO","");
			//Se llena la senalizacion
			$tpl->assign("SELECT_SENALIZACION_TRAMO","");
			$tpl->assign("VALOR_SELECT_SENALIZACION_TRAMO","");
			
			//Select faja
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","NC");
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","-");                           	
			$tpl->assign("VALOR_COMENTARIO_FAJA","");		
			//Select saneamiento			
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");                           	
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           		
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
			$tpl->assign("VALOR_COMENTARIO_SANEAMIENTO","");
			//Select calzada
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","C");                           	
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","NC");
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","SNS");                           		
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","-");  
			$tpl->assign("VALOR_COMENTARIO_CALZADA","");			
			//Select bermas			
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","C");                           	
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","NC");
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           		
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","-");
			$tpl->assign("VALOR_COMENTARIO_BERMA","");
			//Senalizacion
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","C");                           	
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           		
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
			$tpl->assign("VALOR_COMENTARIO_SENALIZACION","");
			//Demarcacion
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","C");                           	
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");                           		
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","-");
			$tpl->assign("VALOR_COMENTARIO_DEMARCACION","");			
			$i++;
		}
	
		//Nro y % incumplimiento
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("NRO_INCUMPLIMIENTO_FAJA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_SANEAMIENTO", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_CALZADA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_BERMA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_SENALIZACION", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_DEMARCACION", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_FAJA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SANEAMIENTO", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_CALZADA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_BERMA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SENALIZACION", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_DEMARCACION", "");
			
		//Se llena los segmetos
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("SEGMENTO");
			$tpl->assign("NRO_SEGMENTO",$fila["numeroSegmentoSorteado"]);
			$tpl->assign("NOMBRE_SEGMENTO","SEGMENTO N&deg; ".$fila["numeroSegmentoSorteado"]);			
		}
		$conexion_db->close();
		$tpl->printToScreen();					
	}
	else if(!isset($_POST["cargador"]) and isset($_POST["buscador"])){
		//Información del formulario
		$bimestre = $_SESSION["BIMESTRE_INFORME"];
		$segmento = $_POST["id_bimestre"];
			//Faja Vial
		$t1IncumplimientoFaja = $_POST["fajaTramo1"];
		$t2IncumplimientoFaja = $_POST["fajaTramo2"]; 
		$t3IncumplimientoFaja = $_POST["fajaTramo3"]; 
		$t4IncumplimientoFaja = $_POST["fajaTramo4"]; 
		$t5IncumplimientoFaja = $_POST["fajaTramo5"]; 
		$t6IncumplimientoFaja = $_POST["fajaTramo6"]; 
		$t7IncumplimientoFaja = $_POST["fajaTramo7"]; 
		$t8IncumplimientoFaja = $_POST["fajaTramo8"]; 
		$t9IncumplimientoFaja = $_POST["fajaTramo9"]; 
		$t10IncumplimientoFaja = $_POST["fajaTramo10"]; 
		$t11IncumplimientoFaja = $_POST["fajaTramo11"]; 
		$t12IncumplimientoFaja = $_POST["fajaTramo12"]; 
		$t13IncumplimientoFaja = $_POST["fajaTramo13"]; 
		$t14IncumplimientoFaja = $_POST["fajaTramo14"]; 
		$t15IncumplimientoFaja = $_POST["fajaTramo15"]; 
		$t1ComentarioFaja = $_POST["comentarioFaja1"];
		$t2ComentarioFaja = $_POST["comentarioFaja2"];
		$t3ComentarioFaja = $_POST["comentarioFaja3"];
		$t4ComentarioFaja = $_POST["comentarioFaja4"];
		$t5ComentarioFaja = $_POST["comentarioFaja5"];
		$t6ComentarioFaja = $_POST["comentarioFaja6"];
		$t7ComentarioFaja = $_POST["comentarioFaja7"];
		$t8ComentarioFaja = $_POST["comentarioFaja8"];
		$t9ComentarioFaja = $_POST["comentarioFaja9"];
		$t10ComentarioFaja = $_POST["comentarioFaja10"];
		$t11ComentarioFaja = $_POST["comentarioFaja11"];
		$t12ComentarioFaja = $_POST["comentarioFaja12"];
		$t13ComentarioFaja = $_POST["comentarioFaja13"];
		$t14ComentarioFaja = $_POST["comentarioFaja14"];
		$t15ComentarioFaja = $_POST["comentarioFaja15"];
		$nroIncumplimientoFaja = $_POST["nroIncumplimientoFaja"]; 
		$porcIncumplimientoFaja = $_POST["porcIncumplimientoFaja"]; 
			//Saneamiento
		$t1IncumplimientoSaneamiento = $_POST["saneamientoTramo1"];
		$t2IncumplimientoSaneamiento = $_POST["saneamientoTramo2"];
		$t3IncumplimientoSaneamiento = $_POST["saneamientoTramo3"];
		$t4IncumplimientoSaneamiento = $_POST["saneamientoTramo4"];
		$t5IncumplimientoSaneamiento = $_POST["saneamientoTramo5"];
		$t6IncumplimientoSaneamiento = $_POST["saneamientoTramo6"];
		$t7IncumplimientoSaneamiento = $_POST["saneamientoTramo7"];
		$t8IncumplimientoSaneamiento = $_POST["saneamientoTramo8"];
		$t9IncumplimientoSaneamiento = $_POST["saneamientoTramo9"];
		$t10IncumplimientoSaneamiento = $_POST["saneamientoTramo10"];
		$t11IncumplimientoSaneamiento = $_POST["saneamientoTramo11"];
		$t12IncumplimientoSaneamiento = $_POST["saneamientoTramo12"];
		$t13IncumplimientoSaneamiento = $_POST["saneamientoTramo13"];
		$t14IncumplimientoSaneamiento = $_POST["saneamientoTramo14"];
		$t15IncumplimientoSaneamiento = $_POST["saneamientoTramo15"];
		$t1ComentarioSaneamiento = $_POST["comentarioSaneamiento1"];
		$t2ComentarioSaneamiento = $_POST["comentarioSaneamiento2"];
		$t3ComentarioSaneamiento = $_POST["comentarioSaneamiento3"];
		$t4ComentarioSaneamiento = $_POST["comentarioSaneamiento4"];
		$t5ComentarioSaneamiento = $_POST["comentarioSaneamiento5"];
		$t6ComentarioSaneamiento = $_POST["comentarioSaneamiento6"];
		$t7ComentarioSaneamiento = $_POST["comentarioSaneamiento7"];
		$t8ComentarioSaneamiento = $_POST["comentarioSaneamiento8"];
		$t9ComentarioSaneamiento = $_POST["comentarioSaneamiento9"];
		$t10ComentarioSaneamiento = $_POST["comentarioSaneamiento10"];
		$t11ComentarioSaneamiento = $_POST["comentarioSaneamiento11"];
		$t12ComentarioSaneamiento = $_POST["comentarioSaneamiento12"];
		$t13ComentarioSaneamiento = $_POST["comentarioSaneamiento13"];
		$t14ComentarioSaneamiento = $_POST["comentarioSaneamiento14"];
		$t15ComentarioSaneamiento = $_POST["comentarioSaneamiento15"];
		$nroIncumplimientoSaneamiento = $_POST["nroIncumplimientoSaneamiento"]; 
		$porcIncumplimientoSaneamiento = $_POST["porcIncumplimientoSaneamiento"]; 
			//Calzada
		$t1IncumplimientoCalzada = $_POST["calzadaTramo1"];
		$t2IncumplimientoCalzada = $_POST["calzadaTramo2"];
		$t3IncumplimientoCalzada = $_POST["calzadaTramo3"];
		$t4IncumplimientoCalzada = $_POST["calzadaTramo4"];
		$t5IncumplimientoCalzada = $_POST["calzadaTramo5"];
		$t6IncumplimientoCalzada = $_POST["calzadaTramo6"];
		$t7IncumplimientoCalzada = $_POST["calzadaTramo7"];
		$t8IncumplimientoCalzada = $_POST["calzadaTramo8"];
		$t9IncumplimientoCalzada = $_POST["calzadaTramo9"];
		$t10IncumplimientoCalzada = $_POST["calzadaTramo10"];
		$t11IncumplimientoCalzada = $_POST["calzadaTramo11"];
		$t12IncumplimientoCalzada = $_POST["calzadaTramo12"];
		$t13IncumplimientoCalzada = $_POST["calzadaTramo13"];
		$t14IncumplimientoCalzada = $_POST["calzadaTramo14"];
		$t15IncumplimientoCalzada = $_POST["calzadaTramo15"];
		$t1ComentarioCalzada = $_POST["comentarioCalzada1"];
		$t2ComentarioCalzada = $_POST["comentarioCalzada2"];
		$t3ComentarioCalzada = $_POST["comentarioCalzada3"];
		$t4ComentarioCalzada = $_POST["comentarioCalzada4"];
		$t5ComentarioCalzada = $_POST["comentarioCalzada5"];
		$t6ComentarioCalzada = $_POST["comentarioCalzada6"];
		$t7ComentarioCalzada = $_POST["comentarioCalzada7"];
		$t8ComentarioCalzada = $_POST["comentarioCalzada8"];
		$t9ComentarioCalzada = $_POST["comentarioCalzada9"];
		$t10ComentarioCalzada = $_POST["comentarioCalzada10"];
		$t11ComentarioCalzada = $_POST["comentarioCalzada11"];
		$t12ComentarioCalzada = $_POST["comentarioCalzada12"];
		$t13ComentarioCalzada = $_POST["comentarioCalzada13"];
		$t14ComentarioCalzada = $_POST["comentarioCalzada14"];
		$t15ComentarioCalzada = $_POST["comentarioCalzada15"];		
		$nroIncumplimientoCalzada = $_POST["nroIncumplimientoCalzada"]; 
		$porcIncumplimientoCalzada = $_POST["porcIncumplimientoCalzada"]; 
			//Berma
		$t1IncumplimientoBerma = $_POST["bermaTramo1"];
		$t2IncumplimientoBerma = $_POST["bermaTramo2"];
		$t3IncumplimientoBerma = $_POST["bermaTramo3"];
		$t4IncumplimientoBerma = $_POST["bermaTramo4"];
		$t5IncumplimientoBerma = $_POST["bermaTramo5"];
		$t6IncumplimientoBerma = $_POST["bermaTramo6"];
		$t7IncumplimientoBerma = $_POST["bermaTramo7"];
		$t8IncumplimientoBerma = $_POST["bermaTramo8"];
		$t9IncumplimientoBerma = $_POST["bermaTramo9"];
		$t10IncumplimientoBerma = $_POST["bermaTramo10"];
		$t11IncumplimientoBerma = $_POST["bermaTramo11"];
		$t12IncumplimientoBerma = $_POST["bermaTramo12"];
		$t13IncumplimientoBerma = $_POST["bermaTramo13"];
		$t14IncumplimientoBerma = $_POST["bermaTramo14"];
		$t15IncumplimientoBerma = $_POST["bermaTramo15"];
		$t1ComentarioBerma = $_POST["comentarioBerma1"];
		$t2ComentarioBerma = $_POST["comentarioBerma2"];
		$t3ComentarioBerma = $_POST["comentarioBerma3"];
		$t4ComentarioBerma = $_POST["comentarioBerma4"];
		$t5ComentarioBerma = $_POST["comentarioBerma5"];
		$t6ComentarioBerma = $_POST["comentarioBerma6"];
		$t7ComentarioBerma = $_POST["comentarioBerma7"];
		$t8ComentarioBerma = $_POST["comentarioBerma8"];
		$t9ComentarioBerma = $_POST["comentarioBerma9"];
		$t10ComentarioBerma = $_POST["comentarioBerma10"];
		$t11ComentarioBerma = $_POST["comentarioBerma11"];
		$t12ComentarioBerma = $_POST["comentarioBerma12"];
		$t13ComentarioBerma = $_POST["comentarioBerma13"];
		$t14ComentarioBerma = $_POST["comentarioBerma14"];
		$t15ComentarioBerma = $_POST["comentarioBerma15"];
		$nroIncumplimientoBerma = $_POST["nroIncumplimientoBerma"]; 
		$porcIncumplimientoBerma = $_POST["porcIncumplimientoBerma"]; 
			//Señalizacion
		$t1IncumplimientoSenalizacion = $_POST["senalizacionTramo1"];
		$t2IncumplimientoSenalizacion = $_POST["senalizacionTramo2"];
		$t3IncumplimientoSenalizacion = $_POST["senalizacionTramo3"];
		$t4IncumplimientoSenalizacion = $_POST["senalizacionTramo4"];
		$t5IncumplimientoSenalizacion = $_POST["senalizacionTramo5"];
		$t6IncumplimientoSenalizacion = $_POST["senalizacionTramo6"];
		$t7IncumplimientoSenalizacion = $_POST["senalizacionTramo7"];
		$t8IncumplimientoSenalizacion = $_POST["senalizacionTramo8"];
		$t9IncumplimientoSenalizacion = $_POST["senalizacionTramo9"];
		$t10IncumplimientoSenalizacion = $_POST["senalizacionTramo10"];
		$t11IncumplimientoSenalizacion = $_POST["senalizacionTramo11"];
		$t12IncumplimientoSenalizacion = $_POST["senalizacionTramo12"];
		$t13IncumplimientoSenalizacion = $_POST["senalizacionTramo13"];
		$t14IncumplimientoSenalizacion = $_POST["senalizacionTramo14"];
		$t15IncumplimientoSenalizacion = $_POST["senalizacionTramo15"];
		$t1ComentarioSenalizacion = $_POST["comentarioSenalizacion1"];
		$t2ComentarioSenalizacion = $_POST["comentarioSenalizacion2"];
		$t3ComentarioSenalizacion = $_POST["comentarioSenalizacion3"];
		$t4ComentarioSenalizacion = $_POST["comentarioSenalizacion4"];
		$t5ComentarioSenalizacion = $_POST["comentarioSenalizacion5"];
		$t6ComentarioSenalizacion = $_POST["comentarioSenalizacion6"];
		$t7ComentarioSenalizacion = $_POST["comentarioSenalizacion7"];
		$t8ComentarioSenalizacion = $_POST["comentarioSenalizacion8"];
		$t9ComentarioSenalizacion = $_POST["comentarioSenalizacion9"];
		$t10ComentarioSenalizacion = $_POST["comentarioSenalizacion10"];
		$t11ComentarioSenalizacion = $_POST["comentarioSenalizacion11"];
		$t12ComentarioSenalizacion = $_POST["comentarioSenalizacion12"];
		$t13ComentarioSenalizacion = $_POST["comentarioSenalizacion13"];
		$t14ComentarioSenalizacion = $_POST["comentarioSenalizacion14"];
		$t15ComentarioSenalizacion = $_POST["comentarioSenalizacion15"];
		$nroIncumplimientoSenalizacion = $_POST["nroIncumplimientoSenalizacion"]; 
		$porcIncumplimientoSenalizacion = $_POST["porcIncumplimientoSenalizacion"]; 
			//Demarcacion
		$t1IncumplimientoDemarcacion = $_POST["demarcacionTramo1"];
		$t2IncumplimientoDemarcacion = $_POST["demarcacionTramo2"];
		$t3IncumplimientoDemarcacion = $_POST["demarcacionTramo3"];
		$t4IncumplimientoDemarcacion = $_POST["demarcacionTramo4"];
		$t5IncumplimientoDemarcacion = $_POST["demarcacionTramo5"];
		$t6IncumplimientoDemarcacion = $_POST["demarcacionTramo6"];
		$t7IncumplimientoDemarcacion = $_POST["demarcacionTramo7"];
		$t8IncumplimientoDemarcacion = $_POST["demarcacionTramo8"];
		$t9IncumplimientoDemarcacion = $_POST["demarcacionTramo9"];
		$t10IncumplimientoDemarcacion = $_POST["demarcacionTramo10"];
		$t11IncumplimientoDemarcacion = $_POST["demarcacionTramo11"];
		$t12IncumplimientoDemarcacion = $_POST["demarcacionTramo12"];
		$t13IncumplimientoDemarcacion = $_POST["demarcacionTramo13"];
		$t14IncumplimientoDemarcacion = $_POST["demarcacionTramo14"];
		$t15IncumplimientoDemarcacion = $_POST["demarcacionTramo15"];
		$t1ComentarioDemarcacion = $_POST["comentarioDemarcacion1"];
		$t2ComentarioDemarcacion = $_POST["comentarioDemarcacion2"];
		$t3ComentarioDemarcacion = $_POST["comentarioDemarcacion3"];
		$t4ComentarioDemarcacion = $_POST["comentarioDemarcacion4"];
		$t5ComentarioDemarcacion = $_POST["comentarioDemarcacion5"];
		$t6ComentarioDemarcacion = $_POST["comentarioDemarcacion6"];
		$t7ComentarioDemarcacion = $_POST["comentarioDemarcacion7"];
		$t8ComentarioDemarcacion = $_POST["comentarioDemarcacion8"];
		$t9ComentarioDemarcacion = $_POST["comentarioDemarcacion9"];
		$t10ComentarioDemarcacion = $_POST["comentarioDemarcacion10"];
		$t11ComentarioDemarcacion = $_POST["comentarioDemarcacion11"];
		$t12ComentarioDemarcacion = $_POST["comentarioDemarcacion12"];
		$t13ComentarioDemarcacion = $_POST["comentarioDemarcacion13"];
		$t14ComentarioDemarcacion = $_POST["comentarioDemarcacion14"];
		$t15ComentarioDemarcacion = $_POST["comentarioDemarcacion15"];
		$nroIncumplimientoDemarcacion = $_POST["nroIncumplimientoDemarcacion"]; 
		$porcIncumplimientoDemarcacion = $_POST["porcIncumplimientoDemarcacion"]; 		
		
		/*echo "Bimestre: ".$bimestre." - Segmento: ".$segmento."<br>";		
			//Faja Vial
		echo "Inclumplimiento Faja 1: ".$t1IncumplimientoFaja." - Comentario Faja 1: ".$t1ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 2: ".$t2IncumplimientoFaja." - Comentario Faja 2: ".$t2ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 3: ".$t3IncumplimientoFaja." - Comentario Faja 3: ".$t3ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 4: ".$t4IncumplimientoFaja." - Comentario Faja 4: ".$t4ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 5: ".$t5IncumplimientoFaja." - Comentario Faja 5: ".$t5ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 6: ".$t6IncumplimientoFaja." - Comentario Faja 6: ".$t6ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 7: ".$t7IncumplimientoFaja." - Comentario Faja 7: ".$t7ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 8: ".$t8IncumplimientoFaja." - Comentario Faja 8: ".$t8ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 9: ".$t9IncumplimientoFaja." - Comentario Faja 9: ".$t9ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 10: ".$t10IncumplimientoFaja." - Comentario Faja 10: ".$t10ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 11: ".$t11IncumplimientoFaja." - Comentario Faja 11: ".$t11ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 12: ".$t12IncumplimientoFaja." - Comentario Faja 12: ".$t12ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 13: ".$t13IncumplimientoFaja." - Comentario Faja 13: ".$t13ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 14: ".$t14IncumplimientoFaja." - Comentario Faja 14: ".$t14ComentarioFaja."<br>";
		echo "Inclumplimiento Faja 15: ".$t15IncumplimientoFaja." - Comentario Faja 15: ".$t15ComentarioFaja."<br>";	 
		echo "Numero Inclumplimiento Faja: ".$nroIncumplimientoFaja." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoFaja."<br>"; 		 
			//Saneamiento
		echo "Inclumplimiento Saneamiento 1: ".$t1IncumplimientoSaneamiento." - Comentario Saneamiento 1: ".$t1ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 2: ".$t2IncumplimientoSaneamiento." - Comentario Saneamiento 2: ".$t2ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 3: ".$t3IncumplimientoSaneamiento." - Comentario Saneamiento 3: ".$t3ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 4: ".$t4IncumplimientoSaneamiento." - Comentario Saneamiento 4: ".$t4ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 5: ".$t5IncumplimientoSaneamiento." - Comentario Saneamiento 5: ".$t5ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 6: ".$t6IncumplimientoSaneamiento." - Comentario Saneamiento 6: ".$t6ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 7: ".$t7IncumplimientoSaneamiento." - Comentario Saneamiento 7: ".$t7ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 8: ".$t8IncumplimientoSaneamiento." - Comentario Saneamiento 8: ".$t8ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 9: ".$t9IncumplimientoSaneamiento." - Comentario Saneamiento 9: ".$t9ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 10: ".$t10IncumplimientoSaneamiento." - Comentario Saneamiento 10: ".$t10ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 11: ".$t11IncumplimientoSaneamiento." - Comentario Saneamiento 11: ".$t11ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 12: ".$t12IncumplimientoSaneamiento." - Comentario Saneamiento 12: ".$t12ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 13: ".$t13IncumplimientoSaneamiento." - Comentario Saneamiento 13: ".$t13ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 14: ".$t14IncumplimientoSaneamiento." - Comentario Saneamiento 14: ".$t14ComentarioSaneamiento."<br>";
		echo "Inclumplimiento Saneamiento 15: ".$t15IncumplimientoSaneamiento." - Comentario Saneamiento 15: ".$t15ComentarioSaneamiento."<br>";	 
		echo "Numero Inclumplimiento Saneamiento: ".$nroIncumplimientoSaneamiento." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoSaneamiento."<br>"; 		
			//Calzada
		echo "Inclumplimiento Calzada 1: ".$t1IncumplimientoCalzada." - Comentario Calzada 1: ".$t1ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 2: ".$t2IncumplimientoCalzada." - Comentario Calzada 2: ".$t2ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 3: ".$t3IncumplimientoCalzada." - Comentario Calzada 3: ".$t3ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 4: ".$t4IncumplimientoCalzada." - Comentario Calzada 4: ".$t4ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 5: ".$t5IncumplimientoCalzada." - Comentario Calzada 5: ".$t5ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 6: ".$t6IncumplimientoCalzada." - Comentario Calzada 6: ".$t6ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 7: ".$t7IncumplimientoCalzada." - Comentario Calzada 7: ".$t7ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 8: ".$t8IncumplimientoCalzada." - Comentario Calzada 8: ".$t8ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 9: ".$t9IncumplimientoCalzada." - Comentario Calzada 9: ".$t9ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 10: ".$t10IncumplimientoCalzada." - Comentario Calzada 10: ".$t10ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 11: ".$t11IncumplimientoCalzada." - Comentario Calzada 11: ".$t11ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 12: ".$t12IncumplimientoCalzada." - Comentario Calzada 12: ".$t12ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 13: ".$t13IncumplimientoCalzada." - Comentario Calzada 13: ".$t13ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 14: ".$t14IncumplimientoCalzada." - Comentario Calzada 14: ".$t14ComentarioCalzada."<br>";
		echo "Inclumplimiento Calzada 15: ".$t15IncumplimientoCalzada." - Comentario Calzada 15: ".$t15ComentarioCalzada."<br>";	 
		echo "Numero Inclumplimiento Calzada: ".$nroIncumplimientoCalzada." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoCalzada."<br>"; 		
			//Berma
		echo "Inclumplimiento Berma 1: ".$t1IncumplimientoBerma." - Comentario Berma 1: ".$t1ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 2: ".$t2IncumplimientoBerma." - Comentario Berma 2: ".$t2ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 3: ".$t3IncumplimientoBerma." - Comentario Berma 3: ".$t3ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 4: ".$t4IncumplimientoBerma." - Comentario Berma 4: ".$t4ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 5: ".$t5IncumplimientoBerma." - Comentario Berma 5: ".$t5ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 6: ".$t6IncumplimientoBerma." - Comentario Berma 6: ".$t6ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 7: ".$t7IncumplimientoBerma." - Comentario Berma 7: ".$t7ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 8: ".$t8IncumplimientoBerma." - Comentario Berma 8: ".$t8ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 9: ".$t9IncumplimientoBerma." - Comentario Berma 9: ".$t9ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 10: ".$t10IncumplimientoBerma." - Comentario Berma 10: ".$t10ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 11: ".$t11IncumplimientoBerma." - Comentario Berma 11: ".$t11ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 12: ".$t12IncumplimientoBerma." - Comentario Berma 12: ".$t12ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 13: ".$t13IncumplimientoBerma." - Comentario Berma 13: ".$t13ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 14: ".$t14IncumplimientoBerma." - Comentario Berma 14: ".$t14ComentarioBerma."<br>";
		echo "Inclumplimiento Berma 15: ".$t15IncumplimientoBerma." - Comentario Berma 15: ".$t15ComentarioBerma."<br>";	 
		echo "Numero Inclumplimiento Berma: ".$nroIncumplimientoBerma." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoBerma."<br>"; 				
			//Señalizacion
		echo "Inclumplimiento Senalizacion 1: ".$t1IncumplimientoSenalizacion." - Comentario Senalizacion 1: ".$t1ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 2: ".$t2IncumplimientoSenalizacion." - Comentario Senalizacion 2: ".$t2ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 3: ".$t3IncumplimientoSenalizacion." - Comentario Senalizacion 3: ".$t3ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 4: ".$t4IncumplimientoSenalizacion." - Comentario Senalizacion 4: ".$t4ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 5: ".$t5IncumplimientoSenalizacion." - Comentario Senalizacion 5: ".$t5ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 6: ".$t6IncumplimientoSenalizacion." - Comentario Senalizacion 6: ".$t6ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 7: ".$t7IncumplimientoSenalizacion." - Comentario Senalizacion 7: ".$t7ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 8: ".$t8IncumplimientoSenalizacion." - Comentario Senalizacion 8: ".$t8ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 9: ".$t9IncumplimientoSenalizacion." - Comentario Senalizacion 9: ".$t9ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 10: ".$t10IncumplimientoSenalizacion." - Comentario Senalizacion 10: ".$t10ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 11: ".$t11IncumplimientoSenalizacion." - Comentario Senalizacion 11: ".$t11ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 12: ".$t12IncumplimientoSenalizacion." - Comentario Senalizacion 12: ".$t12ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 13: ".$t13IncumplimientoSenalizacion." - Comentario Senalizacion 13: ".$t13ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 14: ".$t14IncumplimientoSenalizacion." - Comentario Senalizacion 14: ".$t14ComentarioSenalizacion."<br>";
		echo "Inclumplimiento Senalizacion 15: ".$t15IncumplimientoSenalizacion." - Comentario Senalizacion 15: ".$t15ComentarioSenalizacion."<br>";	 
		echo "Numero Inclumplimiento Senalizacion: ".$nroIncumplimientoSenalizacion." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoSenalizacion."<br>";				
			//Demarcacion
		echo "Inclumplimiento Demarcacion 1: ".$t1IncumplimientoDemarcacion." - Comentario Demarcacion 1: ".$t1ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 2: ".$t2IncumplimientoDemarcacion." - Comentario Demarcacion 2: ".$t2ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 3: ".$t3IncumplimientoDemarcacion." - Comentario Demarcacion 3: ".$t3ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 4: ".$t4IncumplimientoDemarcacion." - Comentario Demarcacion 4: ".$t4ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 5: ".$t5IncumplimientoDemarcacion." - Comentario Demarcacion 5: ".$t5ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 6: ".$t6IncumplimientoDemarcacion." - Comentario Demarcacion 6: ".$t6ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 7: ".$t7IncumplimientoDemarcacion." - Comentario Demarcacion 7: ".$t7ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 8: ".$t8IncumplimientoDemarcacion." - Comentario Demarcacion 8: ".$t8ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 9: ".$t9IncumplimientoDemarcacion." - Comentario Demarcacion 9: ".$t9ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 10: ".$t10IncumplimientoDemarcacion." - Comentario Demarcacion 10: ".$t10ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 11: ".$t11IncumplimientoDemarcacion." - Comentario Demarcacion 11: ".$t11ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 12: ".$t12IncumplimientoDemarcacion." - Comentario Demarcacion 12: ".$t12ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 13: ".$t13IncumplimientoDemarcacion." - Comentario Demarcacion 13: ".$t13ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 14: ".$t14IncumplimientoDemarcacion." - Comentario Demarcacion 14: ".$t14ComentarioDemarcacion."<br>";
		echo "Inclumplimiento Demarcacion 15: ".$t15IncumplimientoDemarcacion." - Comentario Demarcacion 15: ".$t15ComentarioDemarcacion."<br>";	 
		echo "Numero Inclumplimiento Demarcacion: ".$nroIncumplimientoDemarcacion." - Porcentaje Inclumplimiento Faja: ".$porcIncumplimientoDemarcacion."<br>";
		
		exit;*/
		
		//Verificamos si esta o no
		$consulta = "select count(*) as cantidadSegmento from incumplimiento where segmentoIncumplimiento = ".$segmento." and bimestreIncumplimiento = ".
		$bimestre;
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		//Actualizar
		if($fila["cantidadSegmento"] != 0){
			//Actualizamos FAJA
			$consulta2 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoFaja."', t2Incumplimiento = '".$t2IncumplimientoFaja."', t3Incumplimiento = '".
			$t3IncumplimientoFaja."', t4Incumplimiento = '".$t4IncumplimientoFaja."', t5Incumplimiento = '".$t5IncumplimientoFaja."', t6Incumplimiento = '".$t6IncumplimientoFaja.
			"', t7Incumplimiento = '".$t7IncumplimientoFaja."', t8Incumplimiento = '".$t8IncumplimientoFaja."', t9Incumplimiento = '".$t9IncumplimientoFaja."', t10Incumplimiento = '".
			$t10IncumplimientoFaja."', t11Incumplimiento = '".$t11IncumplimientoFaja."', t12Incumplimiento = '".$t12IncumplimientoFaja."', t13Incumplimiento = '".
			$t13IncumplimientoFaja."', t14Incumplimiento = '".$t14IncumplimientoFaja."', t15Incumplimiento = '".$t15IncumplimientoFaja."', t1Comentario = '".$t1ComentarioFaja.
			"', t2Comentario = '".$t2ComentarioFaja."', t3Comentario = '".$t3ComentarioFaja."', t4Comentario = '".$t4ComentarioFaja."', t5Comentario = '".$t5ComentarioFaja.
			"', t6Comentario = '".$t6ComentarioFaja."', t7Comentario = '".$t7ComentarioFaja."', t8Comentario = '".$t8ComentarioFaja."', T9Comentario = '".$t9ComentarioFaja.
			"', t10Comentario = '".$t10ComentarioFaja."', t11Comentario = '".$t11ComentarioFaja."', t12Comentario = '".$t12ComentarioFaja."', t13Comentario = '".
			$t13ComentarioFaja."', t14Comentario = '".$t14ComentarioFaja."', t15Comentario = '".$t15ComentarioFaja."', nroIncumplimiento = '".$nroIncumplimientoFaja.
			"', porcentajeIncumplimiento = '".$porcIncumplimientoFaja."' where componenteIncumplimiento = 'FAJA' and segmentoIncumplimiento = ".$segmento.
			" and bimestreIncumplimiento = ".$bimestre;
			$resultado2 = $conexion_db->query($consulta2);
						
			//Actualizamos SANEAMIENTO
			$consulta3 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoSaneamiento."', t2Incumplimiento = '".$t2IncumplimientoSaneamiento."', t3Incumplimiento = '".
			$t3IncumplimientoSaneamiento."', t4Incumplimiento = '".$t4IncumplimientoSaneamiento."', t5Incumplimiento = '".$t5IncumplimientoSaneamiento."', t6Incumplimiento = '".
			$t6IncumplimientoSaneamiento."', t7Incumplimiento = '".$t7IncumplimientoSaneamiento."', t8Incumplimiento = '".$t8IncumplimientoSaneamiento."', t9Incumplimiento = '".
			$t9IncumplimientoSaneamiento."', t10Incumplimiento = '".$t10IncumplimientoSaneamiento."', t11Incumplimiento = '".$t11IncumplimientoSaneamiento."', t12Incumplimiento = '".
			$t12IncumplimientoSaneamiento."', t13Incumplimiento = '".$t13IncumplimientoSaneamiento."', t14Incumplimiento = '".$t14IncumplimientoSaneamiento."', t15Incumplimiento = '".
			$t15IncumplimientoSaneamiento."', t1Comentario = '".$t1ComentarioSaneamiento."', t2Comentario = '".$t2ComentarioSaneamiento."', t3Comentario = '".$t3ComentarioSaneamiento.
			"', t4Comentario = '".$t4ComentarioSaneamiento."', t5Comentario = '".$t5ComentarioSaneamiento."', t6Comentario = '".$t6ComentarioSaneamiento."', t7Comentario = '".
			$t7ComentarioSaneamiento."', t8Comentario = '".$t8ComentarioSaneamiento."', T9Comentario = '".$t9ComentarioSaneamiento."', t10Comentario = '".$t10ComentarioSaneamiento.
			"', t11Comentario = '".$t11ComentarioSaneamiento."', t12Comentario = '".$t12ComentarioSaneamiento."', t13Comentario = '".$t13ComentarioSaneamiento."', t14Comentario = '".
			$t14ComentarioSaneamiento."', t15Comentario = '".$t15ComentarioSaneamiento."', nroIncumplimiento = '".$nroIncumplimientoSaneamiento."', porcentajeIncumplimiento = '".
			$porcIncumplimientoSaneamiento."' where componenteIncumplimiento = 'SANEAMIENTO' and segmentoIncumplimiento = ".$segmento." and bimestreIncumplimiento = ".$bimestre;
			$resultado3 = $conexion_db->query($consulta3);
			
			//Actualizamos CALZADA
			$consulta4 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoCalzada."', t2Incumplimiento = '".$t2IncumplimientoCalzada."', t3Incumplimiento = '".
			$t3IncumplimientoCalzada."', t4Incumplimiento = '".$t4IncumplimientoCalzada."', t5Incumplimiento = '".$t5IncumplimientoCalzada."', t6Incumplimiento = '".
			$t6IncumplimientoCalzada."', t7Incumplimiento = '".$t7IncumplimientoCalzada."', t8Incumplimiento = '".$t8IncumplimientoCalzada."', t9Incumplimiento = '".
			$t9IncumplimientoCalzada."', t10Incumplimiento = '".$t10IncumplimientoCalzada."', t11Incumplimiento = '".$t11IncumplimientoCalzada."', t12Incumplimiento = '".
			$t12IncumplimientoCalzada."', t13Incumplimiento = '".$t13IncumplimientoCalzada."', t14Incumplimiento = '".$t14IncumplimientoCalzada."', t15Incumplimiento = '".
			$t15IncumplimientoCalzada."', t1Comentario = '".$t1ComentarioCalzada."', t2Comentario = '".$t2ComentarioCalzada."', t3Comentario = '".$t3ComentarioCalzada.
			"', t4Comentario = '".$t4ComentarioCalzada."', t5Comentario = '".$t5ComentarioCalzada."', t6Comentario = '".$t6ComentarioCalzada."', t7Comentario = '".
			$t7ComentarioCalzada."', t8Comentario = '".$t8ComentarioCalzada."', T9Comentario = '".$t9ComentarioCalzada."', t10Comentario = '".$t10ComentarioCalzada.
			"', t11Comentario = '".$t11ComentarioCalzada."', t12Comentario = '".$t12ComentarioCalzada."', t13Comentario = '".$t13ComentarioCalzada."', t14Comentario = '".
			$t14ComentarioCalzada."', t15Comentario = '".$t15ComentarioCalzada."', nroIncumplimiento = '".$nroIncumplimientoCalzada."', porcentajeIncumplimiento = '".
			$porcIncumplimientoCalzada."' where componenteIncumplimiento = 'CALZADA' and segmentoIncumplimiento = ".$segmento." and bimestreIncumplimiento = ".$bimestre;		
			$resultado4 = $conexion_db->query($consulta4);
			
			//Actualizamos BERMA
			$consulta5 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoBerma."', t2Incumplimiento = '".$t2IncumplimientoBerma."', t3Incumplimiento = '".
			$t3IncumplimientoBerma."', t4Incumplimiento = '".$t4IncumplimientoBerma."', t5Incumplimiento = '".$t5IncumplimientoBerma."', t6Incumplimiento = '".
			$t6IncumplimientoBerma."', t7Incumplimiento = '".$t7IncumplimientoBerma."', t8Incumplimiento = '".$t8IncumplimientoBerma."', t9Incumplimiento = '".
			$t9IncumplimientoBerma."', t10Incumplimiento = '".$t10IncumplimientoBerma."', t11Incumplimiento = '".$t11IncumplimientoBerma."', t12Incumplimiento = '".
			$t12IncumplimientoBerma."', t13Incumplimiento = '".$t13IncumplimientoBerma."', t14Incumplimiento = '".$t14IncumplimientoBerma."', t15Incumplimiento = '".
			$t15IncumplimientoBerma."', t1Comentario = '".$t1ComentarioBerma."', t2Comentario = '".$t2ComentarioBerma."', t3Comentario = '".$t3ComentarioBerma.
			"', t4Comentario = '".$t4ComentarioBerma."', t5Comentario = '".$t5ComentarioBerma."', t6Comentario = '".$t6ComentarioBerma."', t7Comentario = '".
			$t7ComentarioBerma."', t8Comentario = '".$t8ComentarioBerma."', T9Comentario = '".$t9ComentarioBerma."', t10Comentario = '".$t10ComentarioBerma.
			"', t11Comentario = '".$t11ComentarioBerma."', t12Comentario = '".$t12ComentarioBerma."', t13Comentario = '".$t13ComentarioBerma."', t14Comentario = '".
			$t14ComentarioBerma."', t15Comentario = '".$t15ComentarioBerma."', nroIncumplimiento = '".$nroIncumplimientoBerma."', porcentajeIncumplimiento = '".
			$porcIncumplimientoBerma."' where componenteIncumplimiento = 'BERMA' and segmentoIncumplimiento = ".$segmento." and bimestreIncumplimiento = ".$bimestre;			
			$resultado5 = $conexion_db->query($consulta5);
			
			//Actualizamos SENALIZACION
			$consulta6 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoSenalizacion."', t2Incumplimiento = '".$t2IncumplimientoSenalizacion.
			"', t3Incumplimiento = '".$t3IncumplimientoSenalizacion."', t4Incumplimiento = '".$t4IncumplimientoSenalizacion."', t5Incumplimiento = '".
			$t5IncumplimientoSenalizacion."', t6Incumplimiento = '".$t6IncumplimientoSenalizacion."', t7Incumplimiento = '".$t7IncumplimientoSenalizacion.
			"', t8Incumplimiento = '".$t8IncumplimientoSenalizacion."', t9Incumplimiento = '".$t9IncumplimientoSenalizacion."', t10Incumplimiento = '".
			$t10IncumplimientoSenalizacion."', t11Incumplimiento = '".$t11IncumplimientoSenalizacion."', t12Incumplimiento = '".$t12IncumplimientoSenalizacion.
			"', t13Incumplimiento = '".$t13IncumplimientoSenalizacion."', t14Incumplimiento = '".$t14IncumplimientoSenalizacion."', t15Incumplimiento = '".
			$t15IncumplimientoSenalizacion."', t1Comentario = '".$t1ComentarioSenalizacion."', t2Comentario = '".$t2ComentarioSenalizacion."', t3Comentario = '".
			$t3ComentarioSenalizacion."', t4Comentario = '".$t4ComentarioSenalizacion."', t5Comentario = '".$t5ComentarioSenalizacion."', t6Comentario = '".
			$t6ComentarioSenalizacion."', t7Comentario = '".$t7ComentarioSenalizacion."', t8Comentario = '".$t8ComentarioSenalizacion."', T9Comentario = '".
			$t9ComentarioSenalizacion."', t10Comentario = '".$t10ComentarioSenalizacion."', t11Comentario = '".$t11ComentarioSenalizacion."', t12Comentario = '".
			$t12ComentarioSenalizacion."', t13Comentario = '".$t13ComentarioSenalizacion."', t14Comentario = '".$t14ComentarioSenalizacion."', t15Comentario = '".
			$t15ComentarioSenalizacion."', nroIncumplimiento = '".$nroIncumplimientoSenalizacion."', porcentajeIncumplimiento = '".
			$porcIncumplimientoSenalizacion."' where componenteIncumplimiento = 'SENALIZACION' and segmentoIncumplimiento = ".$segmento.
			" and bimestreIncumplimiento = ".$bimestre;			
			$resultado6 = $conexion_db->query($consulta6);
			
			//Actualizamos DEMARCACION
			$consulta7 = "update incumplimiento set t1Incumplimiento = '".$t1IncumplimientoDemarcacion."', t2Incumplimiento = '".$t2IncumplimientoDemarcacion.
			"', t3Incumplimiento = '".$t3IncumplimientoDemarcacion."', t4Incumplimiento = '".$t4IncumplimientoDemarcacion."', t5Incumplimiento = '".
			$t5IncumplimientoDemarcacion."', t6Incumplimiento = '".$t6IncumplimientoDemarcacion."', t7Incumplimiento = '".$t7IncumplimientoDemarcacion.
			"', t8Incumplimiento = '".$t8IncumplimientoDemarcacion."', t9Incumplimiento = '".$t9IncumplimientoDemarcacion."', t10Incumplimiento = '".
			$t10IncumplimientoDemarcacion."', t11Incumplimiento = '".$t11IncumplimientoDemarcacion."', t12Incumplimiento = '".$t12IncumplimientoDemarcacion.
			"', t13Incumplimiento = '".$t13IncumplimientoDemarcacion."', t14Incumplimiento = '".$t14IncumplimientoDemarcacion."', t15Incumplimiento = '".
			$t15IncumplimientoDemarcacion."', t1Comentario = '".$t1ComentarioDemarcacion."', t2Comentario = '".$t2ComentarioDemarcacion."', t3Comentario = '".
			$t3ComentarioDemarcacion."', t4Comentario = '".$t4ComentarioDemarcacion."', t5Comentario = '".$t5ComentarioDemarcacion."', t6Comentario = '".
			$t6ComentarioDemarcacion."', t7Comentario = '".$t7ComentarioDemarcacion."', t8Comentario = '".$t8ComentarioDemarcacion."', T9Comentario = '".
			$t9ComentarioDemarcacion."', t10Comentario = '".$t10ComentarioDemarcacion."', t11Comentario = '".$t11ComentarioDemarcacion."', t12Comentario = '".
			$t12ComentarioDemarcacion."', t13Comentario = '".$t13ComentarioDemarcacion."', t14Comentario = '".$t14ComentarioDemarcacion."', t15Comentario = '".
			$t15ComentarioDemarcacion."', nroIncumplimiento = '".$nroIncumplimientoDemarcacion."', porcentajeIncumplimiento = '".
			$porcIncumplimientoDemarcacion."' where componenteIncumplimiento = 'DEMARCACION' and segmentoIncumplimiento = ".$segmento.
			" and bimestreIncumplimiento = ".$bimestre;						
			$resultado7 = $conexion_db->query($consulta7);
			
			//Actualizamos tabla segmentosSorteados
			$consulta8 = "update segmentosSorteados set estadoIncumplimiento=1 where numeroSegmentoSorteado = ".$segmento." and bimestreSorteado = ".
			$bimestre;
			$resultado8 = $conexion_db->query($consulta8);
		}
		//Agregar
		else{
			//Insertamos FAJA
			$consulta2 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, ".
			"porcentajeIncumplimiento) values ('', ".$bimestre.", ".$segmento.", 'FAJA', '".$t1IncumplimientoFaja."', '".$t2IncumplimientoFaja."', '".
			$t3IncumplimientoFaja."', '".$t4IncumplimientoFaja."', '".$t5IncumplimientoFaja."', '".$t6IncumplimientoFaja."', '".$t7IncumplimientoFaja.
			"', '".$t8IncumplimientoFaja."', '".$t9IncumplimientoFaja."', '".$t10IncumplimientoFaja."', '".$t11IncumplimientoFaja."', '".
			$t12IncumplimientoFaja."', '".$t13IncumplimientoFaja."', '".$t14IncumplimientoFaja."', '".$t15IncumplimientoFaja."', '".$t1ComentarioFaja.
			"', '".$t2ComentarioFaja."', '".$t3ComentarioFaja."', '".$t4ComentarioFaja."', '".$t5ComentarioFaja."', '".$t6ComentarioFaja."', '".
			$t7ComentarioFaja."', '".$t8ComentarioFaja."', '".$t9ComentarioFaja."', '".$t10ComentarioFaja."', '".$t11ComentarioFaja."', '".
			$t12ComentarioFaja."', '".$t13ComentarioFaja."', '".$t14ComentarioFaja."', '".$t15ComentarioFaja."', '".$nroIncumplimientoFaja."', '".
			$porcIncumplimientoFaja."')";
			$resultado2 = $conexion_db->query($consulta2);
			//Insertamos SANEAMIENTO
			$consulta3 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, ".
			"porcentajeIncumplimiento) values ('', ".$bimestre.", ".$segmento.", 'SANEAMIENTO', '".$t1IncumplimientoSaneamiento."', '".
			$t2IncumplimientoSaneamiento."', '".$t3IncumplimientoSaneamiento."', '".$t4IncumplimientoSaneamiento."', '".$t5IncumplimientoSaneamiento.
			"', '".$t6IncumplimientoSaneamiento."', '".$t7IncumplimientoSaneamiento."', '".$t8IncumplimientoSaneamiento."', '".$t9IncumplimientoSaneamiento.
			"', '".$t10IncumplimientoSaneamiento."', '".$t11IncumplimientoSaneamiento."', '".$t12IncumplimientoSaneamiento."', '".
			$t13IncumplimientoSaneamiento."', '".$t14IncumplimientoSaneamiento."', '".$t15IncumplimientoSaneamiento."', '".$t1ComentarioSaneamiento."', '".
			$t2ComentarioSaneamiento."', '".$t3ComentarioSaneamiento."', '".$t4ComentarioSaneamiento."', '".$t5ComentarioSaneamiento."', '".
			$t6ComentarioSaneamiento."', '".$t7ComentarioSaneamiento."', '".$t8ComentarioSaneamiento."', '".$t9ComentarioSaneamiento."', '".
			$t10ComentarioSaneamiento."', '".$t11ComentarioSaneamiento."', '".$t12ComentarioSaneamiento."', '".$t13ComentarioSaneamiento."', '".
			$t14ComentarioSaneamiento."', '".$t15ComentarioSaneamiento."', '".$nroIncumplimientoSaneamiento."', '".$porcIncumplimientoSaneamiento."')";
			$resultado3 = $conexion_db->query($consulta3);
			//Insertamos CALZADA
			$consulta4 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, ".
			"porcentajeIncumplimiento) values ('', ".$bimestre.", ".$segmento.", 'CALZADA', '".$t1IncumplimientoCalzada."', '".$t2IncumplimientoCalzada.
			"', '".$t3IncumplimientoCalzada."', '".$t4IncumplimientoCalzada."', '".$t5IncumplimientoCalzada."', '".$t6IncumplimientoCalzada."', '".
			$t7IncumplimientoCalzada."', '".$t8IncumplimientoCalzada."', '".$t9IncumplimientoCalzada."', '".$t10IncumplimientoCalzada."', '".
			$t11IncumplimientoCalzada."', '".$t12IncumplimientoCalzada."', '".$t13IncumplimientoCalzada."', '".$t14IncumplimientoCalzada."', '".
			$t15IncumplimientoCalzada."', '".$t1ComentarioCalzada."', '".$t2ComentarioCalzada."', '".$t3ComentarioCalzada."', '".$t4ComentarioCalzada."', '".
			$t5ComentarioCalzada."', '".$t6ComentarioCalzada."', '".$t7ComentarioCalzada."', '".$t8ComentarioCalzada."', '".$t9ComentarioCalzada."', '".
			$t10ComentarioCalzada."', '".$t11ComentarioCalzada."', '".$t12ComentarioCalzada."', '".$t13ComentarioCalzada."', '".$t14ComentarioCalzada.
			"', '".$t15ComentarioCalzada."', '".$nroIncumplimientoCalzada."', '".$porcIncumplimientoCalzada."')";
			$resultado4 = $conexion_db->query($consulta4);
			//Insertamos BERMA
			$consulta5 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, ".
			"porcentajeIncumplimiento) values ('', ".$bimestre.", ".$segmento.", 'BERMA', '".$t1IncumplimientoBerma."', '".$t2IncumplimientoBerma."', '".
			$t3IncumplimientoBerma."', '".$t4IncumplimientoBerma."', '".$t5IncumplimientoBerma."', '".$t6IncumplimientoBerma."', '".
			$t7IncumplimientoBerma."', '".$t8IncumplimientoBerma."', '".$t9IncumplimientoBerma."', '".$t10IncumplimientoBerma."', '".
			$t11IncumplimientoBerma."', '".$t12IncumplimientoBerma."', '".$t13IncumplimientoBerma."', '".$t14IncumplimientoBerma."', '".
			$t15IncumplimientoBerma."', '".$t1ComentarioBerma."', '".$t2ComentarioBerma."', '".$t3ComentarioBerma."', '".$t4ComentarioBerma."', '".
			$t5ComentarioBerma."', '".$t6ComentarioBerma."', '".$t7ComentarioBerma."', '".$t8ComentarioBerma."', '".$t9ComentarioBerma."', '".
			$t10ComentarioBerma."', '".$t11ComentarioBerma."', '".$t12ComentarioBerma."', '".$t13ComentarioBerma."', '".$t14ComentarioBerma."', '".
			$t15ComentarioBerma."', '".$nroIncumplimientoBerma."', '".$porcIncumplimientoBerma."')";
			$resultado5 = $conexion_db->query($consulta5);
			//Insertamos SENALIZACION
			$consulta6 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, porcentajeIncumplimiento) values ('', ".
			$bimestre.", ".$segmento.", 'SENALIZACION', '".$t1IncumplimientoSenalizacion."', '".$t2IncumplimientoSenalizacion."', '".
			$t3IncumplimientoSenalizacion."', '".$t4IncumplimientoSenalizacion."', '".$t5IncumplimientoSenalizacion."', '".$t6IncumplimientoSenalizacion.
			"', '".$t7IncumplimientoSenalizacion."', '".$t8IncumplimientoSenalizacion."', '".$t9IncumplimientoSenalizacion."', '".
			$t10IncumplimientoSenalizacion."', '".$t11IncumplimientoSenalizacion."', '".$t12IncumplimientoSenalizacion."', '".$t13IncumplimientoSenalizacion.
			"', '".$t14IncumplimientoSenalizacion."', '".$t15IncumplimientoSenalizacion."', '".$t1ComentarioSenalizacion."', '".
			$t2ComentarioSenalizacion."', '".$t3ComentarioSenalizacion."', '".$t4ComentarioSenalizacion."', '".$t5ComentarioSenalizacion."', '".
			$t6ComentarioSenalizacion."', '".$t7ComentarioSenalizacion."', '".$t8ComentarioSenalizacion."', '".$t9ComentarioSenalizacion."', '".
			$t10ComentarioSenalizacion."', '".$t11ComentarioSenalizacion."', '".$t12ComentarioSenalizacion."', '".$t13ComentarioSenalizacion."', '".
			$t14ComentarioSenalizacion."', '".$t15ComentarioSenalizacion."', '".$nroIncumplimientoSenalizacion."', '".$porcIncumplimientoSenalizacion."')";
			$resultado6 = $conexion_db->query($consulta6);
			//Insertamos DEMARCACION
			$consulta7 = "insert into incumplimiento (idIncumplimiento, bimestreIncumplimiento, segmentoIncumplimiento, componenteIncumplimiento, ".
			"t1Incumplimiento, t2Incumplimiento, t3Incumplimiento, t4Incumplimiento, t5Incumplimiento, t6Incumplimiento, t7Incumplimiento, ".
			"t8Incumplimiento, t9Incumplimiento, t10Incumplimiento, t11Incumplimiento, t12Incumplimiento, t13Incumplimiento, t14Incumplimiento, ".
			"t15Incumplimiento, t1Comentario, t2Comentario, t3Comentario, t4Comentario, t5Comentario, t6Comentario, t7Comentario, t8Comentario, ".
			"t9Comentario, t10Comentario, t11Comentario, t12Comentario, t13Comentario, t14Comentario, t15Comentario, nroIncumplimiento, porcentajeIncumplimiento) values ('', ".
			$bimestre.", ".$segmento.", 'DEMARCACION', '".$t1IncumplimientoDemarcacion."', '".$t2IncumplimientoDemarcacion."', '".
			$t3IncumplimientoDemarcacion."', '".$t4IncumplimientoDemarcacion."', '".$t5IncumplimientoDemarcacion."', '".$t6IncumplimientoDemarcacion.
			"', '".$t7IncumplimientoDemarcacion."', '".$t8IncumplimientoDemarcacion."', '".$t9IncumplimientoDemarcacion."', '".
			$t10IncumplimientoDemarcacion."', '".$t11IncumplimientoDemarcacion."', '".$t12IncumplimientoDemarcacion."', '".$t13IncumplimientoDemarcacion.
			"', '".$t14IncumplimientoDemarcacion."', '".$t15IncumplimientoDemarcacion."', '".$t1ComentarioDemarcacion."', '".
			$t2ComentarioDemarcacion."', '".$t3ComentarioDemarcacion."', '".$t4ComentarioDemarcacion."', '".$t5ComentarioDemarcacion."', '".
			$t6ComentarioDemarcacion."', '".$t7ComentarioDemarcacion."', '".$t8ComentarioDemarcacion."', '".$t9ComentarioDemarcacion."', '".
			$t10ComentarioDemarcacion."', '".$t11ComentarioDemarcacion."', '".$t12ComentarioDemarcacion."', '".$t13ComentarioDemarcacion."', '".
			$t14ComentarioDemarcacion."', '".$t15ComentarioDemarcacion."', '".$nroIncumplimientoDemarcacion."', '".$porcIncumplimientoDemarcacion."')";
			$resultado7 = $conexion_db->query($consulta7);
			//Actualizamos tabla segmentosSorteados
			$consulta8 = "update segmentosSorteados set estadoIncumplimiento=1 where numeroSegmentoSorteado = ".$segmento." and bimestreSorteado = ".
			$bimestre;
			$resultado8 = $conexion_db->query($consulta8);
		}
		
		//Se actualiza la página nuevamente
		//Se seleccionar la informacion de los segmentos correspondiente al segmento
		$consulta = "select * from segmentossorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"];
		$resultado = $conexion_db->query($consulta);
		//Toma la informacion del bimestre
		$consulta2 = "select * from bimestre where NroBimestre = ".$_SESSION["BIMESTRE_INFORME"];
		$resultado2 = $conexion_db->query($consulta2);
		$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
		//Verificamos si habilitamos o no el botón generar informe
		$consulta11 = "select count(*) as cantidadSegmentos from segmentossorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_INFORME"].
		" and estadoIncumplimiento = 0";
		$resultado11 = $conexion_db->query($consulta11);
		$fila11 = $resultado11->fetch_array(MYSQL_ASSOC);
		//Se carga la página
		$tpl = new TemplatePower("informeTablaIncumplimiento.html");														
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));

		//*********************************************************
				$consultaSQL = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
				$resultadoSQL = $conexion_db->query($consultaSQL);
				while($filaSQL = $resultadoSQL->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("NOMBRES_COMPONENTES");
					$tpl->assign("valor_componente",$filaSQL["nombreComponente"]);
				}
				//*************************************************************

		if($fila11["cantidadSegmentos"] == 0){
			$tpl->assign("HABILITAR_BOTON","");		
		}
		else{
			$tpl->assign("HABILITAR_BOTON","disabled");		
		}				
		$tpl->assign("PAGONUMERO",$fila2["NroPagoBimestre"]);
		$tpl->assign("FECHA_SORTEO",utf8_encode(strftime('%A, %d de %B del %Y',strtotime($fila2["fechaTerminoBimestre"]))));		
		//Select segmentos
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("SELECT_INICIO", "");
		$tpl->assign("VALOR_SELECT_INICIO", "--- SELECCIONAR OPCI&Oacute;N ---");
		$tpl->assign("SELECT_FINAL", "");
		$tpl->assign("VALOR_SELECT_FINAL", "");							
		$i=0;
		while($i < 16){
			//Se llenan los tramos
			$tpl->newBlock("TRAMOS_TITULO");
			$tpl->assign("TRAMO_DESDE","-");
			$tpl->assign("TRAMO_HASTA","-");
										
			//Se llena los nombres
			$tpl->assign("NOMBRE_FAJA", "fajaTramo".($i+1));
			$tpl->assign("NOMBRE_SANEAMIENTO", "saneamientoTramo".($i+1));
			$tpl->assign("NOMBRE_CALZADA", "calzadaTramo".($i+1));
			$tpl->assign("NOMBRE_BERMA", "bermaTramo".($i+1));
			$tpl->assign("NOMBRE_SENALIZACION", "senalizacionTramo".($i+1));
			$tpl->assign("NOMBRE_DEMARCACION", "demarcacionTramo".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_FAJA","comentarioFaja".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_SANEAMIENTO","comentarioSaneamiento".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_CALZADA","comentarioCalzada".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_BERMA","comentarioBerma".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_SENALIZACION","comentarioSenalizacion".($i+1));
			$tpl->assign("NOMBRE_COMENTARIO_DEMARCACION","comentarioDemarcacion".($i+1));
					
			//Se llena la faja
			$tpl->assign("SELECT_FAJA_TRAMO","");
			$tpl->assign("VALOR_SELECT_FAJA_TRAMO","");
			//Se llena el saneamiento
			$tpl->assign("SELECT_SANEAMIENTO_TRAMO","");
			$tpl->assign("VALOR_SELECT_SANEAMIENTO_TRAMO","");
			//Se llena la calzada
			$tpl->assign("SELECT_CALZADA_TRAMO","");
			$tpl->assign("VALOR_SELECT_CALZADA_TRAMO","");
			//Se llena la berma
			$tpl->assign("SELECT_BERMAS_TRAMO","");
			$tpl->assign("VALOR_SELECT_BERMAS_TRAMO","");
			//Se llena la demarcacion
			$tpl->assign("SELECT_DEMARCACION_TRAMO","");
			$tpl->assign("VALOR_SELECT_DEMARCACION_TRAMO","");
			//Se llena la senalizacion
			$tpl->assign("SELECT_SENALIZACION_TRAMO","");
			$tpl->assign("VALOR_SELECT_SENALIZACION_TRAMO","");
			
			//Select faja
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","C");                           	
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","NC");
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","SNS");                           		
			$tpl->newBlock("FAJA_TRAMO");
			$tpl->assign("VALOR_FAJA_TRAMO","-");                           	
			$tpl->assign("VALOR_COMENTARIO_FAJA","");		
			//Select saneamiento			
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","C");                           	
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","NC");
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","SNS");                           		
			$tpl->newBlock("SANEAMIENTO_TRAMO");
			$tpl->assign("VALOR_SANEAMIENTO_TRAMO","-");                           		
			$tpl->assign("VALOR_COMENTARIO_SANEAMIENTO","");
			//Select calzada
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","C");                           	
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","NC");
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","SNS");                           		
			$tpl->newBlock("CALZADA_TRAMO");
			$tpl->assign("VALOR_CALZADA_TRAMO","-");  
			$tpl->assign("VALOR_COMENTARIO_CALZADA","");			
			//Select bermas			
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","C");                           	
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","NC");
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","SNS");                           		
			$tpl->newBlock("BERMAS_TRAMO");
			$tpl->assign("VALOR_BERMAS_TRAMO","-");
			$tpl->assign("VALOR_COMENTARIO_BERMA","");
			//Senalizacion
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","C");                           	
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","NC");
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","SNS");                           		
			$tpl->newBlock("SENALIZACION_TRAMO");
			$tpl->assign("VALOR_SENALIZACION_TRAMO","-");                           		
			$tpl->assign("VALOR_COMENTARIO_SENALIZACION","");
			//Demarcacion
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","C");                           	
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","NC");
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","SNS");                           		
			$tpl->newBlock("DEMARCACION_TRAMO");
			$tpl->assign("VALOR_DEMARCACION_TRAMO","-");
			$tpl->assign("VALOR_COMENTARIO_DEMARCACION","");			
			$i++;
		}
	
		//Nro y % incumplimiento
		$tpl->gotoBlock("_ROOT");
		$tpl->assign("NRO_INCUMPLIMIENTO_FAJA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_SANEAMIENTO", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_CALZADA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_BERMA", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_SENALIZACION", "");
		$tpl->assign("NRO_INCUMPLIMIENTO_DEMARCACION", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_FAJA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SANEAMIENTO", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_CALZADA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_BERMA", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_SENALIZACION", "");
		$tpl->assign("PORCENTAJE_INCUMPLIMIENTO_DEMARCACION", "");
			
		//Se llena los segmetos
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("SEGMENTO");
			$tpl->assign("NRO_SEGMENTO",$fila["numeroSegmentoSorteado"]);
			$tpl->assign("NOMBRE_SEGMENTO","SEGMENTO N&deg; ".$fila["numeroSegmentoSorteado"]);			
		}				
		$conexion_db->close();
		$tpl->printToScreen();			
	}