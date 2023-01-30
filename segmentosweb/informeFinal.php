<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Funciones en sesiones.php
	//validarAdministrador();
	validaTiempo();

	//Verificamos que se haya realizado algun sorteo
	$consultaSorteo = "select count(*) as cantidadSorteo from segmentosSorteados";	
	$resultadoSorteo = $conexion_db->query($consultaSorteo);
	$filaSorteo = $resultadoSorteo->fetch_array(MYSQL_ASSOC);
	if($filaSorteo["cantidadSorteo"] == 0){
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){		
			$tpl = new TemplatePower("administrador.html");
		/*}
		else{
			$tpl = new TemplatePower("usuario.html");
		}*/

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
		$tpl->assign("DISPLAY","compact;");
		$tpl->assign("MENSAJE","NO SE PUEDE GENERAR EL INFORME, NO SE HA REALIZADO NINGÚN SORTEO.");		
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{	//Hay sorte realizado
		//Obtenemos el código de FAJA
		$consulta20 = "select codigoComponente from codigocomponente where nombreComponente = 'FAJA'";
		$resultado20 = $conexion_db->query($consulta20);
		$fila20 = $resultado20->fetch_array(MYSQL_ASSOC);
		//Obtenemos el código de SANEAMIENTO
		$consulta21 = "select codigoComponente from codigocomponente where nombreComponente = 'SANEAMIENTO'";
		$resultado21 = $conexion_db->query($consulta21);
		$fila21 = $resultado21->fetch_array(MYSQL_ASSOC);
		//Obtenemos el código de CALZADA
		$consulta22 = "select codigoComponente from codigocomponente where nombreComponente = 'CALZADA'";
		$resultado22 = $conexion_db->query($consulta22);
		$fila22 = $resultado22->fetch_array(MYSQL_ASSOC);
		//Obtenemos el código de BERMA
		$consulta23 = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'CALZADA DTS'";
		$resultado23 = $conexion_db->query($consulta23);
		$fila23 = $resultado23->fetch_array(MYSQL_ASSOC);
		//Obtenemos el código de SENALIZACION
		$consulta24 = "select codigoComponente from codigocomponente where nombreComponente = 'SENALIZACION'";
		$resultado24 = $conexion_db->query($consulta24);
		$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
		//Obtenemos el código de DEMARCACION
		$consulta25 = "select codigoComponente from codigocomponente where nombreComponente = 'DEMARCACION'";
		$resultado25 = $conexion_db->query($consulta25);
		$fila25 = $resultado25->fetch_array(MYSQL_ASSOC);
		
		//Obtenermos los componentes seleccionados
		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);
		
		$i=0;
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$Ncomponente[$i]= $fila["nombreComponente"];
			$Ccomponente[$i]= $fila["codigoComponente"];
			$i++;
		}
		
		//Cargamos la información del select
		if(isset($_GET["id"])){			
			//Obtenemos el nro de pago de ese bimestre
			$consulta16 = "select NroPagoBimestre from bimestre where NroBimestre = ".$_GET["id"];
			$resultado16 = $conexion_db->query($consulta16);
			$fila16 = $resultado16->fetch_array(MYSQL_ASSOC);
			//Vemos que hay información de ese bimestre en la tabla kmDescontados
			$consulta8 = "select count(*) as cantidadKmDescontados from kmDescontados where kmBimestre = ".$_GET["id"];
			$resultado8 = $conexion_db->query($consulta8);
			$fila8 = $resultado8->fetch_array(MYSQL_ASSOC);
			//Si hay información entramos
			if($fila8["cantidadKmDescontados"] != 0){
				//Obtenemos los valores por componentes
				//Faja
				$consulta9 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[0]."' and kmBimestre = ".$_GET["id"];
				$resultado9 = $conexion_db->query($consulta9);
				$fila9 = $resultado9->fetch_array(MYSQL_ASSOC);
				//Saneamiento
				$consulta11 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[1]."' and kmBimestre = ".$_GET["id"];
				$resultado11 = $conexion_db->query($consulta11);
				$fila11 = $resultado11->fetch_array(MYSQL_ASSOC);
				//Calzada
				$consulta12 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[2]."' and kmBimestre = ".$_GET["id"];
				$resultado12 = $conexion_db->query($consulta12);
				$fila12 = $resultado12->fetch_array(MYSQL_ASSOC);				
				//Bermas
				$consulta13 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[3]."' and kmBimestre = ".$_GET["id"];
				$resultado13 = $conexion_db->query($consulta13);
				$fila13 = $resultado13->fetch_array(MYSQL_ASSOC);
				//Senalizacion
				$consulta14 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[4]."' and kmBimestre = ".$_GET["id"];
				$resultado14 = $conexion_db->query($consulta14);
				$fila14 = $resultado14->fetch_array(MYSQL_ASSOC);
				//Demarcacion
				$consulta15 = "select cantidad from kmDescontados where codigo = '".$Ccomponente[5]."' and kmBimestre = ".$_GET["id"];
				$resultado15 = $conexion_db->query($consulta15);
				$fila15 = $resultado15->fetch_array(MYSQL_ASSOC);
				
				//Tomamos los bimestres ya sorteados
				$consulta10 = "select distinct bimestreSorteado from segmentosSorteados";
				$resultado10 = $conexion_db->query($consulta10);			
				
				//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){		
					$tpl = new TemplatePower("bimestreKmDescontados.html");
				/*}
				else{
					$tpl = new TemplatePower("bimestreKmDescontado_usr.html");
				}*/

				$tpl->assignInclude("header", "header.html");
				$tpl->assignInclude("menu", "menu.html");

				$tpl->prepare();
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
				$tpl->assign("REDIRECCIONAR","informeFinal.php");
				$tpl->assign("CODIGO_FAJA",$Ccomponente[0]);
				$tpl->assign("CODIGO_SANEAMIENTO",$Ccomponente[1]);
				$tpl->assign("CODIGO_CALZADA",$Ccomponente[2]);
				$tpl->assign("CODIGO_BERMA",$Ccomponente[3]);
				$tpl->assign("CODIGO_SENALIZACION",$Ccomponente[4]);
				$tpl->assign("CODIGO_DEMARCACION",$Ccomponente[5]);	

				$tpl->assign("NOMBRE_COMPONENTE1",$Ncomponente[0]);
				$tpl->assign("NOMBRE_COMPONENTE2",$Ncomponente[1]);
				$tpl->assign("NOMBRE_COMPONENTE3",$Ncomponente[2]);
				$tpl->assign("NOMBRE_COMPONENTE4",$Ncomponente[3]);
				$tpl->assign("NOMBRE_COMPONENTE5",$Ncomponente[4]);
				$tpl->assign("NOMBRE_COMPONENTE6",$Ncomponente[5]);	

				$tpl->assign("KM_FAJA", $fila9["cantidad"]);						
				$tpl->assign("KM_SANEAMIENTO", $fila11["cantidad"]);						
				$tpl->assign("KM_CALZADA", $fila12["cantidad"]);						
				$tpl->assign("KM_BERMAS", $fila13["cantidad"]);						
				$tpl->assign("KM_SENALIZACION", $fila14["cantidad"]);						
				$tpl->assign("KM_DEMARCACION", $fila15["cantidad"]);						
				$tpl->assign("BIMESTRE_V1", $_GET["id"]);
				$tpl->assign("VALOR_BIMESTRE_V1", "INSPECCI&Oacute;N DE PAGO N&deg; ".$fila16["NroPagoBimestre"]);
				$tpl->assign("BIMESTRE_V2", "");
				$tpl->assign("VALOR_BIMESTRE_V2", "--- SELECCIONAR OPCI&Oacute;N ---");
				//Llenamos el select
				while($fila10 = $resultado10->fetch_array(MYSQL_ASSOC)){
					$consulta17 = "select NroPagoBimestre from bimestre where NroBimestre = ".$fila10["bimestreSorteado"];
					$resultado17 = $conexion_db->query($consulta17);
					$fila17 = $resultado17->fetch_array(MYSQL_ASSOC);
					
					if($fila10["bimestreSorteado"] != $_GET["id"]){					
						if($fila17["NroPagoBimestre"] == 1000){
							$tpl->newBlock("NUMERO_BIMESTRE");
							$tpl->assign("numBimestre",$fila10["bimestreSorteado"]);
							$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");
						}
						else{
							$tpl->newBlock("NUMERO_BIMESTRE");
							$tpl->assign("numBimestre",$fila10["bimestreSorteado"]);
							$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila17["NroPagoBimestre"]);
						}						
					}
				}		
				$conexion_db->close();
				$tpl->printToScreen();
			}
			else{	//No hay información en segmentos descontados
				//Tomamos los bimestres ya sorteados
				$consulta = "select distinct bimestreSorteado from segmentosSorteados";
				$resultado = $conexion_db->query($consulta);			
				//Se carga la página
				//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){		
					$tpl = new TemplatePower("bimestreKmDescontados.html");
				/*}
				else{
					$tpl = new TemplatePower("bimestreKmDescontado_usr.html");
				}*/

				$tpl->assignInclude("header", "header.html");
				$tpl->assignInclude("menu", "menu.html");

				$tpl->prepare();
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
				$tpl->assign("REDIRECCIONAR","informeFinal.php");						
				$tpl->assign("CODIGO_FAJA",$Ccomponente[0]);
				$tpl->assign("CODIGO_SANEAMIENTO",$Ccomponente[1]);
				$tpl->assign("CODIGO_CALZADA",$Ccomponente[2]);
				$tpl->assign("CODIGO_BERMA",$Ccomponente[3]);
				$tpl->assign("CODIGO_SENALIZACION",$Ccomponente[4]);
				$tpl->assign("CODIGO_DEMARCACION",$Ccomponente[5]);	

				$tpl->assign("NOMBRE_COMPONENTE1",$Ncomponente[0]);
				$tpl->assign("NOMBRE_COMPONENTE2",$Ncomponente[1]);
				$tpl->assign("NOMBRE_COMPONENTE3",$Ncomponente[2]);
				$tpl->assign("NOMBRE_COMPONENTE4",$Ncomponente[3]);
				$tpl->assign("NOMBRE_COMPONENTE5",$Ncomponente[4]);
				$tpl->assign("NOMBRE_COMPONENTE6",$Ncomponente[5]);	

				$tpl->assign("BIMESTRE_V1", $_GET["id"]);
				$tpl->assign("VALOR_BIMESTRE_V1", "INSPECCI&Oacute;N DE PAGO N&deg; ".$fila16["NroPagoBimestre"]);
				$tpl->assign("BIMESTRE_V2", "");
				$tpl->assign("VALOR_BIMESTRE_V2", "--- SELECCIONAR OPCI&Oacute;N ---");
				//Llenamos el select
				while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
					$consulta18 = "select NroPagoBimestre from bimestre where NroBimestre = ".$fila["bimestreSorteado"];
					$resultado18 = $conexion_db->query($consulta18);
					$fila18 = $resultado18->fetch_array(MYSQL_ASSOC);
					
					if($fila["bimestreSorteado"] != $_GET["id"]){
						if($fila18["NroPagoBimestre"] == 1000){
							$tpl->newBlock("NUMERO_BIMESTRE");
							$tpl->assign("numBimestre",$fila["bimestreSorteado"]);
							$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");
						}
						else{
							$tpl->newBlock("NUMERO_BIMESTRE");
							$tpl->assign("numBimestre",$fila["bimestreSorteado"]);
							$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila18["NroPagoBimestre"]);	
						}					
					}
				}
				$tpl->gotoBlock("_ROOT");
				
				//Calculamos los KM Descontados
					//Variables
				$suma_faja_descontada = 0;
				$suma_saneamiento_descontada = 0;
				$suma_calzada_descontada = 0;
				$suma_bermas_descontada = 0;
				$suma_senalizacion_descontada = 0;
				$suma_demarcacion_descontada = 0;
						
					//Total KM				
				$consulta = "select sum(longitudRedCaminera) as sumakmred from redcaminera";
				$resultado = $conexion_db->query($consulta);			
				$fila = $resultado->fetch_array(MYSQL_ASSOC);
				$kmIngresados = $fila["sumakmred"];
								
					//Desafeccion por componente				
				$consulta2 = "select * from desafeccionreal";
				$resultado2 = $conexion_db->query($consulta2);							
				while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
					if(strcmp($fila2["fajaVialDesafeccionReal"],"SNS") == 0){ $suma_faja_descontada = $suma_faja_descontada + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["saneamientoDesafeccionReal"],"SNS") == 0){ $suma_saneamiento_descontada = $suma_saneamiento_descontada + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["calzadaDesafeccionReal"],"SNS") == 0){ $suma_calzada_descontada = $suma_calzada_descontada + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["bermasDesafeccionReal"],"SNS") == 0){ $suma_bermas_descontada = $suma_bermas_descontada + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["senalizacionDesafeccionReal"],"SNS") == 0){ $suma_senalizacion_descontada = $suma_senalizacion_descontada + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["demarcacionDesafeccionReal"],"SNS") == 0){ $suma_demarcacion_descontada = $suma_demarcacion_descontada + $fila2["longitudDesafeccionReal"]; }
				}
				
				$fajaDescontado = $kmIngresados - number_format($suma_faja_descontada, 3, '.', '');
				$saneamientoDescontado = $kmIngresados - number_format($suma_saneamiento_descontada, 3, '.', '');
				$calzadaDescontado = $kmIngresados - number_format($suma_calzada_descontada, 3, '.', '');
				$bermaDescontado = $kmIngresados - number_format($suma_bermas_descontada, 3, '.', '');
				$senalizacionDescontado = $kmIngresados - number_format($suma_senalizacion_descontada, 3, '.', '');
				$demarcacionDescontado = $kmIngresados - number_format($suma_demarcacion_descontada, 3, '.', '');
				
				//Guardamos o actualizamos la informacion
				$consulta26 = "select count(*) as ctdadkmdescontados from kmdescontados where kmBimestre = ".$_GET["id"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				
				if($fila26["ctdadkmdescontados"] == 0){					
					//Insertamos FAJA
					$consulta27 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) ". "values ('', '".$Ccomponente[0]."', 'Faja vial', 'km', ".number_format($fajaDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado27 = $conexion_db->query($consulta27);
					//Insertamos SANEAMIENTO
					$consulta28 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) ". "values ('', '".$Ccomponente[1]."', 'Saneamiento', 'km',".number_format($saneamientoDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado28 = $conexion_db->query($consulta28);
					//Insertamos CALZADA
					$consulta29 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[2]."', 'Calzada', 'km', ".number_format($calzadaDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado29 = $conexion_db->query($consulta29);					
					//Insertamos BERMAS
					$consulta30 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[3]."', 'Bermas', 'km', ".number_format($bermaDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado30 = $conexion_db->query($consulta30);										
					//Insertamos SENALIZACION
					$consulta31 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[4]."', 'Se&ntilde;alizaci&oacute;n vertical y defensas met&aacute;licas', 'km', ".
					number_format($senalizacionDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado31 = $conexion_db->query($consulta31);											
					//Insertamos DEMARCACION
					$consulta32 = "insert into kmdescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[5]."', 'Demarcaci&oacute;n', 'km', ".number_format($demarcacionDescontado, 3, '.', '').", ".$_GET["id"].")";
					$resultado32 = $conexion_db->query($consulta32);											
				}
				else{
					//Actualizamos FAJA
					$consulta33 = "update kmdescontados set cantidad = ".number_format($fajaDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[0]."' and kmBimestre = ".$_GET["id"];
					$resultado33 = $conexion_db->query($consulta33);
					//Actualizamos SANEAMIENTO
					$consulta34 = "update kmdescontados set cantidad = ".number_format($saneamientoDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[1]."' and kmBimestre = ".$_GET["id"];
					$resultado34 = $conexion_db->query($consulta34);
					//Actualizamos CALZADA
					$consulta35 = "update kmdescontados set cantidad = ".number_format($calzadaDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[2]."' and kmBimestre = ".$_GET["id"];
					$resultado35 = $conexion_db->query($consulta35);
					//Actualizamos BERMAS
					$consulta36 = "update kmdescontados set cantidad = ".number_format($bermaDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[3]."' and kmBimestre = ".$_GET["id"];
					$resultado36 = $conexion_db->query($consulta36);
					//Actualizamos SENALIZACION
					$consulta37 = "update kmdescontados set cantidad = ".number_format($senalizacionDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[4]."' and kmBimestre = ".$_GET["id"];
					$resultado37 = $conexion_db->query($consulta37);
					//Actualizar DEMARCACION
					$consulta38 = "update kmdescontados set cantidad = ".number_format($demarcacionDescontado, 3, '.', '').
					" where codigo = '".$Ccomponente[5]."' and kmBimestre = ".$_GET["id"];
					$resultado38 = $conexion_db->query($consulta38);			
				}
				
				//Calculamos los KM Contratados
				//Variables
				$suma_faja_original = 0;
				$suma_saneamiento_original = 0;
				$suma_calzada_original = 0;
				$suma_bermas_original = 0;
				$suma_senalizacion_original = 0;
				$suma_demarcacion_original = 0;
						
				//Total KM				
				$consulta = "select sum(longitudRedCaminera) as sumakmred from redcaminera";
				$resultado = $conexion_db->query($consulta);			
				$fila = $resultado->fetch_array(MYSQL_ASSOC);
				$kmIngresados = $fila["sumakmred"];
				
				//Desafeccion por componente
				$consulta2 = "select * from desafeccionreal where exclusionInicial = 1";				
				$resultado2 = $conexion_db->query($consulta2);							
				while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
					if(strcmp($fila2["fajaVialDesafeccionReal"],"SNS") == 0){ $suma_faja_original = $suma_faja_original + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["saneamientoDesafeccionReal"],"SNS") == 0){ $suma_saneamiento_original = $suma_saneamiento_original + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["calzadaDesafeccionReal"],"SNS") == 0){ $suma_calzada_original = $suma_calzada_original + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["bermasDesafeccionReal"],"SNS") == 0){ $suma_bermas_original = $suma_bermas_original + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["senalizacionDesafeccionReal"],"SNS") == 0){ $suma_senalizacion_original = $suma_senalizacion_original + $fila2["longitudDesafeccionReal"]; }
					if(strcmp($fila2["demarcacionDesafeccionReal"],"SNS") == 0){ $suma_demarcacion_original = $suma_demarcacion_original + $fila2["longitudDesafeccionReal"]; }
				}
				
				$fajaDescontado_original = $kmIngresados - number_format($suma_faja_original, 3, '.', '');
				$saneamientoDescontado_original = $kmIngresados - number_format($suma_saneamiento_original, 3, '.', '');
				$calzadaDescontado_original = $kmIngresados - number_format($suma_calzada_original, 3, '.', '');
				$bermaDescontado_original = $kmIngresados - number_format($suma_bermas_original, 3, '.', '');
				$senalizacionDescontado_original = $kmIngresados - number_format($suma_senalizacion_original, 3, '.', '');
				$demarcacionDescontado_original = $kmIngresados - number_format($suma_demarcacion_original, 3, '.', '');
				
				//Guardamos o actualizamos la informacion
				$consulta26 = "select count(*) as ctdadkmoriginales from kmcontratados where kmBimestre = ".$_GET["id"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				
				if($fila26["ctdadkmoriginales"] == 0){					
					//Insertamos FAJA
					$consulta27 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) ". "values ('', '".$Ccomponente[0]."', 'Faja vial', 'km', ".number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado27 = $conexion_db->query($consulta27);
					//Insertamos SANEAMIENTO
					$consulta28 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) ". "values ('', '".$Ccomponente[1]."', 'Saneamiento', 'km',".number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado28 = $conexion_db->query($consulta28);
					//Insertamos CALZADA
					$consulta29 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[2]."', 'Calzada', 'km', ".number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado29 = $conexion_db->query($consulta29);					
					//Insertamos BERMAS
					$consulta30 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[3]."', 'Bermas', 'km', ".number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado30 = $conexion_db->query($consulta30);										
					//Insertamos SENALIZACION
					$consulta31 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[4]."', 'Se&ntilde;alizaci&oacute;n vertical y defensas met&aacute;licas', 'km', ".
					number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado31 = $conexion_db->query($consulta31);											
					//Insertamos DEMARCACION
					$consulta32 = "insert into kmcontratados (idKmContratados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".$Ccomponente[5]."', 'Demarcaci&oacute;n', 'km', ".number_format($kmIngresados, 3, '.', '').", ".$_GET["id"].")";
					$resultado32 = $conexion_db->query($consulta32);											
				}
				else{
					//Actualizamos FAJA
					$consulta33 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[0]."' and kmBimestre = ".$_GET["id"];
					$resultado33 = $conexion_db->query($consulta33);
					//Actualizamos SANEAMIENTO
					$consulta34 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[1]."' and kmBimestre = ".$_GET["id"];
					$resultado34 = $conexion_db->query($consulta34);
					//Actualizamos CALZADA
					$consulta35 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[2]."' and kmBimestre = ".$_GET["id"];
					$resultado35 = $conexion_db->query($consulta35);
					//Actualizamos BERMAS
					$consulta36 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[3]."' and kmBimestre = ".$_GET["id"];
					$resultado36 = $conexion_db->query($consulta36);
					//Actualizamos SENALIZACION
					$consulta37 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[4]."' and kmBimestre = ".$_GET["id"];
					$resultado37 = $conexion_db->query($consulta37);
					//Actualizar DEMARCACION
					$consulta38 = "update kmcontratados set cantidad = ".number_format($kmIngresados, 3, '.', '').
					" where codigo = '".$Ccomponente[5]."' and kmBimestre = ".$_GET["id"];
					$resultado38 = $conexion_db->query($consulta38);			
				}
				
				/***** Llenamos los km con el calculo automático ******/				
				$tpl->assign("KM_FAJA", number_format($fajaDescontado, 3, '.', ''));
				$tpl->assign("KM_SANEAMIENTO", number_format($saneamientoDescontado, 3, '.', ''));
				$tpl->assign("KM_CALZADA", number_format($calzadaDescontado, 3, '.', ''));						
				$tpl->assign("KM_BERMAS", number_format($bermaDescontado, 3, '.', ''));						
				$tpl->assign("KM_SENALIZACION", number_format($senalizacionDescontado, 3, '.', ''));						
				$tpl->assign("KM_DEMARCACION", number_format($demarcacionDescontado, 3, '.', ''));			
				
				
				if(isset($resultado)){ $resultado-> close(); }
				if(isset($resultado2)){ $resultado2-> close(); }
				$conexion_db->close();
				$tpl->printToScreen();				
			}
		}
		//Cuando se pulsa el botón 
		else{		
			if(!isset($_POST["buscador"])){	/*Carga Inicial*/
				//Tomamos los bimestres ya sorteados
				$consulta = "select distinct bimestreSorteado from segmentosSorteados";
				$resultado = $conexion_db->query($consulta);			
				//Se carga la página
				//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){		
					$tpl = new TemplatePower("bimestreKmDescontados.html");
				/*}
				else{
					$tpl = new TemplatePower("bimestreKmDescontado_usr.html");
				}*/

				$tpl->assignInclude("header", "header.html");
				$tpl->assignInclude("menu", "menu.html");

				$tpl->prepare();
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
				$tpl->assign("REDIRECCIONAR","informeFinal.php");						
				$tpl->assign("CODIGO_FAJA",$Ccomponente[0]);
				$tpl->assign("CODIGO_SANEAMIENTO",$Ccomponente[1]);
				$tpl->assign("CODIGO_CALZADA",$Ccomponente[2]);
				$tpl->assign("CODIGO_BERMA",$Ccomponente[3]);
				$tpl->assign("CODIGO_SENALIZACION",$Ccomponente[4]);
				$tpl->assign("CODIGO_DEMARCACION",$Ccomponente[5]);		

				$tpl->assign("NOMBRE_COMPONENTE1",$Ncomponente[0]);
				$tpl->assign("NOMBRE_COMPONENTE2",$Ncomponente[1]);
				$tpl->assign("NOMBRE_COMPONENTE3",$Ncomponente[2]);
				$tpl->assign("NOMBRE_COMPONENTE4",$Ncomponente[3]);
				$tpl->assign("NOMBRE_COMPONENTE5",$Ncomponente[4]);
				$tpl->assign("NOMBRE_COMPONENTE6",$Ncomponente[5]);	
						
				$tpl->assign("BIMESTRE_V1", "");
				$tpl->assign("VALOR_BIMESTRE_V1", "--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("BIMESTRE_V2", "");
				$tpl->assign("VALOR_BIMESTRE_V2", "");
				//Llenamos el select
				while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
					$consulta19 = "select NroPagoBimestre from bimestre where NroBimestre = ".$fila["bimestreSorteado"];
					$resultado19 = $conexion_db->query($consulta19);
					$fila19 = $resultado19->fetch_array(MYSQL_ASSOC);
					
					if($fila19["NroPagoBimestre"] == 1000){
						$tpl->newBlock("NUMERO_BIMESTRE");
						$tpl->assign("numBimestre",$fila["bimestreSorteado"]);
						$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");
					}
					else{
						$tpl->newBlock("NUMERO_BIMESTRE");
						$tpl->assign("numBimestre",$fila["bimestreSorteado"]);
						$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila19["NroPagoBimestre"]);	
					}					
				}		
				$conexion_db->close();
				$tpl->printToScreen();
			}
			else if(isset($_POST["buscador"])){
				//Información del Formulario	//No son km contratados son km descontados
				$bimestre = $_POST["numeroBimestre"];					
				$kmContratadoFaja = htmlentities(mb_strtolower(trim($_POST["kmContratadoFaja"]),'UTF-8'));
				$kmContratadoSaneamiento = htmlentities(mb_strtolower(trim($_POST["kmContratadoSaneamiento"]),'UTF-8'));
				$kmContratadoCalzada = htmlentities(mb_strtolower(trim($_POST["kmContratadoCalzada"]),'UTF-8'));
				$kmContratadoBermas = htmlentities(mb_strtolower(trim($_POST["kmContratadoBermas"]),'UTF-8')); 
				$kmContratadoSenalizacion = htmlentities(mb_strtolower(trim($_POST["kmContratadoSenalizacion"]),'UTF-8'));
				$kmContratadoDemarcacion = htmlentities(mb_strtolower(trim($_POST["kmContratadoDemarcacion"]),'UTF-8'));
				
				//Variable session
				$_SESSION["BIMESTRE_INFORME"] = $bimestre;
			
				//Verificamos que existan porcentajes para el bimestre seleccionado
				$consultaPorcentaje = "select count(*) as cantidadPorcentaje from porcentaje";
				$resultadoPorcentaje = $conexion_db->query($consultaPorcentaje);
				$filaPorcentaje = $resultadoPorcentaje->fetch_array(MYSQL_ASSOC);
				if($filaPorcentaje["cantidadPorcentaje"] == 0){
					//Se carga la página	
					//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){	
						$tpl = new TemplatePower("administrador.html");
					/*}
					else{
						$tpl = new TemplatePower("usuario.html");
					}*/

					$tpl->assignInclude("header", "header.html");
					$tpl->assignInclude("menu", "menu.html");
					
					$tpl->prepare();
					$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
					$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
					$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));	
					$tpl->assign("DISPLAY","compact;");
					$tpl->assign("MENSAJE","GENERAR PRIMERO EL INFORME DE INCUMPLIMIENTO");		
					$conexion_db->close();
					unset($_SESSION["BIMESTRE_INFORME"]);
					$tpl->printToScreen();
				}
				else{	
					//Formateo de las variables	//No son km contratados, son descontados
					$kmContratadoFaja = number_format($kmContratadoFaja, 3, '.', '');
					$kmContratadoSaneamiento = number_format($kmContratadoSaneamiento, 3, '.', '');
					$kmContratadoCalzada = number_format($kmContratadoCalzada, 3, '.', '');
					$kmContratadoBermas = number_format($kmContratadoBermas, 3, '.', '');
					$kmContratadoSenalizacion = number_format($kmContratadoSenalizacion, 3, '.', '');
					$kmContratadoDemarcacion = number_format($kmContratadoDemarcacion, 3, '.', ''); 
					
					//Verificamos si ya existen los 6 elementos en la tabla correspondiente al bimestre
					$consulta = "select count(*) as cantidadElementos from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado = $conexion_db->query($consulta);
					$fila = $resultado->fetch_array(MYSQL_ASSOC);
					if($fila["cantidadElementos"] == 6){
						//Actualizamos FAJA
						$consulta2 = "update kmDescontados set cantidad = ".$kmContratadoFaja." where codigo = '".$Ccomponente[0]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado2 = $conexion_db->query($consulta2);
						//Actualizamos SANEAMIENTO
						$consulta3 = "update kmDescontados set cantidad = ".$kmContratadoSaneamiento." where codigo = '".$Ccomponente[1]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado3 = $conexion_db->query($consulta3);
						//Actualizamos CALZADA
						$consulta4 = "update kmDescontados set cantidad = ".$kmContratadoCalzada." where codigo = '".$Ccomponente[2]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado4 = $conexion_db->query($consulta4);
						//Actualizamos BERMAS
						$consulta5 = "update kmDescontados set cantidad = ".$kmContratadoBermas." where codigo = '".$Ccomponente[3]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado5 = $conexion_db->query($consulta5);
						//Actualizamos SENALIZACION
						$consulta6 = "update kmDescontados set cantidad = ".$kmContratadoSenalizacion." where codigo = '".$Ccomponente[4]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado6 = $conexion_db->query($consulta6);
						//Actualizar DEMARCACION
						$consulta7 = "update kmDescontados set cantidad = ".$kmContratadoDemarcacion." where codigo = '".$Ccomponente[5]."' and kmBimestre = ".$_SESSION["BIMESTRE_INFORME"];
						$resultado7 = $conexion_db->query($consulta7);			
					}
					else{
						//Insertamos FAJA
						$consulta2 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[0]."', 'Faja vial', 'km', ".$kmContratadoFaja.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado2 = $conexion_db->query($consulta2);
						//Insertamos SANEAMIENTO
						$consulta3 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[1]."', 'Saneamiento', 'km', ".$kmContratadoSaneamiento.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado3 = $conexion_db->query($consulta3);
						//Insertamos CALZADA
						$consulta4 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[2]."', 'Calzada', 'km', ".$kmContratadoCalzada.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado4 = $conexion_db->query($consulta4);
						//Insertamos BERMAS
						$consulta5 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[3]."', 'Bermas', 'km', ".$kmContratadoBermas.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado5 = $conexion_db->query($consulta5);
						//Insertamos SENALIZACION
						$consulta6 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[4]."', "."'Se&ntilde;alizaci&oacute;n vertical y defensas met&aacute;licas', 'km', ".
						$kmContratadoSenalizacion.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado6 = $conexion_db->query($consulta6);
						//Insertamos DEMARCACION
						$consulta7 = "insert into kmDescontados (idKmDescontados, codigo, operaciones, unidad, cantidad, kmBimestre) values ('', '".
						$Ccomponente[5]."', 'Demarcaci&oacute;n', 'km', ".$kmContratadoDemarcacion.", ".$_SESSION["BIMESTRE_INFORME"].")";
						$resultado7 = $conexion_db->query($consulta7);
					}
					
					$consulta = "select sum(`noConsiderarFecha`) as suma_noConsiderarFecha from desafeccionreal";
					$resultado = $conexion_db->query($consulta);
					$fila = $resultado->fetch_array(MYSQL_ASSOC);
					if($fila["suma_noConsiderarFecha"] == 0){
						$conexion_db->close();
						header("Location: correccionDia_conFecha.php");				
					}
					else{
						$conexion_db->close();
						header("Location: correccionDia_sinFecha.php");										
					}
				}
			}
		}
	}
?>