<?PHP														
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO
	date_default_timezone_set('America/Santiago');			  //  |-- DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php					
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		//Verificamos que existan exclusiones
		$consulta = "select count(*) as cantidad from desafeccionReal";
		$resultado = $conexion_db->query($consulta);		
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		
		if($fila["cantidad"] == 0){
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
			
			if(isset($_SESSION["MENSAJE_ELIMINADO"]) and strcmp($_SESSION["MENSAJE_ELIMINADO"],"SI") == 0){
				$tpl->assign("MENSAJE","SE HAN ELIMINADO TODAS LAS EXCLUSIONES");
				unset($_SESSION["MENSAJE_ELIMINADO"]);
			}
			else{
				$tpl->assign("MENSAJE","NO EXISTEN EXCLUSIONES EN EL SISTEMA");
			}		
			
			
			
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}
		else{
			$consulta2 = "select * from desafeccionReal order by rolDesafeccionReal, desdeDesafeccionReal desc";
			$resultado2 = $conexion_db->query($consulta2);
			
			//Se carga la página
			$tpl = new TemplatePower("modificarExclusion.html");

			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("DISPLAY","compact;");

			$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("NOMBRES_COMPONENTES");
				$tpl->assign("valor_componente",$fila["nombreComponente"]);
			}
		
			//Verificamos el considerar fecha
			//Caso 1: no hay exclusiones
			$consulta4 = "select count('rolDesafeccionReal') as cantidad_exclusiones from desafeccionreal";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
			if($fila4["cantidad_exclusiones"] == 0){
				$tpl->assign("CONTROLAR", "<input type='checkbox' name='no_considerar_fecha'/>"); 
			}
			else{
				$consulta5 = "select sum(`noConsiderarFecha`) as cantidad_considera from desafeccionreal";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);		
				//Caso 2: Considera
				if($fila5["cantidad_considera"] == 0){
					$tpl->assign("CONTROLAR", "<input type='checkbox' name='no_considerar_fecha'/>"); 
				}
				//Caso 3: No considera
				else{
					$tpl->assign("CONTROLAR", "<input type='checkbox' name='no_considerar_fecha' checked/>"); 
				}			
			}
			$i=0;
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("EXCLUSIONES");
				$tpl->assign("DESDE_EXCLUSION",$fila2["desdeDesafeccionReal"]);
				$tpl->assign("HASTA_EXCLUSION",$fila2["hastaDesafeccionReal"]);
				$tpl->assign("LONGITUD_EXCLUSIONES",$fila2["longitudDesafeccionReal"]);
				$tpl->assign("ROL_EXCLUSION",$fila2["rolDesafeccionReal"]);			
				$tpl->assign("RESOLUCION_EXCLUSION",$fila2["observacionDesafeccionReal"]);	
				$tpl->assign("FECHA_INICIO_EXCLUSIONES", $fila2["fecha_inicio"]);
				$tpl->assign("FECHA_TERMINO_EXCLUSIONES", $fila2["fecha_termino"]);
				$tpl->assign("RESOLUCION_ELIMINAR",$i);			
				
				if($fila2["exclusionInicial"] == 0){
					$tpl->assign("CHECKONO", "");
				}
				else{
					$tpl->assign("CHECKONO", "checked");
				}
				
				//Select faja
				if(strcmp($fila2["fajaVialDesafeccionReal"],"")==0){
					$tpl->assign("FAJA_EXCLUSION","SI");			
					$tpl->assign("RESTO_FAJA_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("FAJA_EXCLUSION","NO");			
					$tpl->assign("RESTO_FAJA_EXCLUSION","SI");		
				}			
				//select saneamiento	
				if(strcmp($fila2["saneamientoDesafeccionReal"],"")==0){
					$tpl->assign("SANEAMIENTO_EXCLUSION","SI");			
					$tpl->assign("RESTO_SANEAMIENTO_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("SANEAMIENTO_EXCLUSION","NO");			
					$tpl->assign("RESTO_SANEAMIENTO_EXCLUSION","SI");		
				}
				//Select calzada
				if(strcmp($fila2["calzadaDesafeccionReal"],"")==0){
					$tpl->assign("CALZADA_EXCLUSION","SI");			
					$tpl->assign("RESTO_CALZADA_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("CALZADA_EXCLUSION","NO");			
					$tpl->assign("RESTO_CALZADA_EXCLUSION","SI");		
				}
				//Select bermas
				if(strcmp($fila2["bermasDesafeccionReal"],"")==0){
					$tpl->assign("BERMAS_EXCLUSION","SI");			
					$tpl->assign("RESTO_BERMAS_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("BERMAS_EXCLUSION","NO");			
					$tpl->assign("RESTO_BERMAS_EXCLUSION","SI");		
				}
				//Select senalizacion
				if(strcmp($fila2["senalizacionDesafeccionReal"],"")==0){
					$tpl->assign("SENALIZACION_EXCLUSION","SI");			
					$tpl->assign("RESTO_SENALIZACION_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("SENALIZACION_EXCLUSION","NO");			
					$tpl->assign("RESTO_SENALIZACION_EXCLUSION","SI");		
				}
				//Select demarcacion
				if(strcmp($fila2["demarcacionDesafeccionReal"],"")==0){
					$tpl->assign("DEMARCACION_EXCLUSION","SI");			
					$tpl->assign("RESTO_DEMARCACION_EXCLUSION","NO");			
				}
				else{
					$tpl->assign("DEMARCACION_EXCLUSION","NO");			
					$tpl->assign("RESTO_DEMARCACION_EXCLUSION","SI");		
				}
				
				$tpl->assign("INDICE_ARRAY",$i);
				$i++;
				
				$consulta3 = "select distinct rolRedCaminera from redcaminera";
				$resultado3 = $conexion_db->query($consulta3);
				//Recorremos la consulta
				while($fila3 = $resultado3->fetch_array(MYSQL_ASSOC)){
					if(strcmp($fila3["rolRedCaminera"],$fila2["rolDesafeccionReal"]) != 0){
						$tpl->newBlock("ROL_CAMINO");	
						$tpl->assign("ROL",$fila3["rolRedCaminera"]);
					}
				}
			}
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}
	}
	else{
		//truncamos y normalizamos segmentos, desafeccionReal, designacion
		$consulta = "update designacion set fajaDesignacion = '', saneamientoDesignacion = '', calzadaDesignacion = '', bermasDesignacion = '', senalizacionDesignacion = '', demarcacionDesignacion = ''";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "update subSegmentos set estadoSubSegmentos = 1";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "update segmentos set estadoSegmento = 1";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "truncate table desafeccionReal";
		$resultado = $conexion_db->query($consulta);
		
		//Información del formulario
		$valor_filtro = '';		
		$rol = array_merge(array_filter($_POST["rolCamino"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));					
		$desde = array_merge(array_filter($_POST["kmInicio"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
		$hasta = array_merge(array_filter($_POST["kmFinal"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));			
		$longitud = array_merge(array_filter($_POST["longitud"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$fechaInicio = array_merge(array_filter($_POST["fechaInicio"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$fechaTermino = array_merge(array_filter($_POST["fechaTermino"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$faja = array_merge(array_filter($_POST["faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$saneamiento = array_merge(array_filter($_POST["saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$calzada = array_merge(array_filter($_POST["calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$bermas = array_merge(array_filter($_POST["bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$senalizacion = array_merge(array_filter($_POST["senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		$demarcacion = array_merge(array_filter($_POST["demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
		$resolucion = array_merge(array_filter($_POST["resolucion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		if(isset($_POST["no_considerar_fecha"])){
			$noConsiderar = 1;	//No se considera
		}
		else{
			$noConsiderar = 0;	//Si se considera
		}
		$exclusionInicial = array();			
		if(isset($_POST["excInicial"])){
			for($i=0;$i<count($_POST["excInicial"]);$i++){
				$exclusionInicial[$_POST["excInicial"][$i]] = 1;
			}
			for($i=0;$i<count($rol);$i++){
				if(!isset($exclusionInicial[$i])){
					$exclusionInicial[$i] = 0;	
				}				
			}
		}	
		else{
			for($i=0;$i<count($rol);$i++){
				$exclusionInicial[$i] = 0;									
			}
		}
		
		if(isset($_POST["eliminar"])){			
			$eliminar = array_merge(array_filter($_POST["eliminar"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
			if(count($eliminar) == count($rol)){
				//Redireccionamos
				$_SESSION["MENSAJE_ELIMINADO"] = "SI";
				header("Location: modificarExclusion.php");
			}	
			
			for($i=0;$i<count($eliminar);$i++){				
				unset($rol[$eliminar[$i]]);
				unset($desde[$eliminar[$i]]);
				unset($hasta[$eliminar[$i]]);
				unset($longitud[$eliminar[$i]]);
				unset($fechaInicio[$eliminar[$i]]);
				unset($fechaTermino[$eliminar[$i]]);
				unset($faja[$eliminar[$i]]);
				unset($saneamiento[$eliminar[$i]]);
				unset($calzada[$eliminar[$i]]);
				unset($bermas[$eliminar[$i]]);
				unset($senalizacion[$eliminar[$i]]);
				unset($demarcacion[$eliminar[$i]]);
				unset($exclusionInicial[$eliminar[$i]]);				
				unset($resolucion[$eliminar[$i]]);
			}
			$rol = array_merge(array_filter($rol, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));					
			$desde = array_merge(array_filter($desde, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));		
			$hasta = array_merge(array_filter($hasta, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));			
			$longitud = array_merge(array_filter($longitud, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$fechaInicio = array_merge(array_filter($fechaInicio, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$fechaTermino = array_merge(array_filter($fechaTermino, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$faja = array_merge(array_filter($faja, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$saneamiento = array_merge(array_filter($saneamiento, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));	
			$calzada = array_merge(array_filter($calzada, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$bermas = array_merge(array_filter($bermas, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$senalizacion = array_merge(array_filter($senalizacion, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }))	;
			$demarcacion = array_merge(array_filter($demarcacion, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));			
			$exclusionInicial = array_merge(array_filter($exclusionInicial, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
			$resolucion = array_merge(array_filter($resolucion, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));	
		}	
		
		$aux_error = 0;		
		$aux_fecha = 0;		
		//Recorremos el formulario
		for($i=0;$i<count($rol);$i++){		
			//Caso1 - desde debe ser menor que hasta
			if($desde[$i] >= $hasta[$i]){
				$aux_error++;				
			}			
			//Validacion Inicial
				//Caso 2 - Verificamos que el valor ingresado este entre el camino			
			$consulta = "select count(*) as ctdadCamino from redcaminera where rolRedCaminera = '".$rol[$i]."' and kmInicioRedCaminera <= ".$desde[$i]." and kmFinalRedCaminera >= ".$hasta[$i];
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			if($fila["ctdadCamino"] == 0){
				$aux_error++;	
			}
				//Caso 3 - No puede cruzarse Validamos el km
			$consulta1 = "select count(*) as cantidadCruce from desafeccionreal where rolDesafeccionReal = '".$rol[$i]."' and ".$hasta[$i]." > desdeDesafeccionReal and ".$hasta[$i]." < hastaDesafeccionReal";
			$resultado1 = $conexion_db->query($consulta1);
			$fila1 = $resultado1->fetch_array(MYSQL_ASSOC);
			$consulta2 = "select count(*) as cantidadCruce from desafeccionreal where rolDesafeccionReal = '".$rol[$i]."' and ".$desde[$i]." > desdeDesafeccionReal and ".$hasta[$i]." < hastaDesafeccionReal";
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
			$consulta3 = "select count(*) as cantidadCruce from desafeccionreal where rolDesafeccionReal = '".$rol[$i]."' and ".$desde[$i]." > desdeDesafeccionReal and ".$desde[$i]." < hastaDesafeccionReal";
			$resultado3 = $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			$consulta4 = "select count(*) as cantidadCruce from desafeccionreal where rolDesafeccionReal = '".$rol[$i]."' and ".$desde[$i]." = desdeDesafeccionReal and ".$hasta[$i]." = hastaDesafeccionReal";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);			
			if($fila1["cantidadCruce"] > 0 || $fila2["cantidadCruce"] > 0 || $fila3["cantidadCruce"] > 0 || $fila4["cantidadCruce"] > 0){
				$aux_error++;
			}
			if($noConsiderar == 0){
				//Caso 4 - Las fechas no pueden ser iguales y Inicio no puede ser mayor que Termino 				
				if(compararFechas($fechaTermino[$i], $fechaInicio[$i]) <= 0){
					$aux_error++;
				}	
			}			
				//Caso 5 - Se debe excluir algo
			if(strcmp($faja[$i], "SI") == 0 && strcmp($saneamiento[$i], "SI") == 0 && strcmp($calzada[$i], "SI") == 0 && strcmp($bermas[$i], "SI") == 0 && strcmp($demarcacion[$i], "SI") == 0 && strcmp($senalizacion[$i], "SI") == 0){
				$aux_error++;
			}
			if($aux_error != 0){
				break;
			}
			else{	
				if($noConsiderar == 0){
					//Realizamos la exclusión si se encuentra dentro de la fecha
					if(compararFechas(date("Y-m-d"), $fechaInicio[$i]) >= 0 && compararFechas($fechaTermino[$i], date("Y-m-d")) >= 0){
						//Generamos el valor desde a buscar
						$aux = str_split(number_format($desde[$i], 3, '.', ''));				
						$aux2 = array();
						$k=0;
						for($j=0;$j < count($aux);$j++){
							if(strcmp($aux[$j],".") == 0){
								$k=$j+1;
							}
						}
						for($j=0;$j<=$k;$j++){
							$aux2[$j] = $aux[$j];
						}		
						$desde_final = number_format(implode('',$aux2), 3, '.', '');

						//Generamos el valor hasta a buscar
						$aux3 = str_split(number_format($hasta[$i],3,'.',''));	//Creamos un array del valor hasta
						$aux4 = array();
						$k=0;
						for($j=0;$j<count($aux3);$j++){
							if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
								$k = $j+1;
							}
						}
						for($j=0;$j<=$k;$j++){
							$aux4[$j] = $aux3[$j];	//Creamos el array hasta
						}
						//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
						$hasta_final = number_format(implode('',$aux4),3,'.','');
						if($aux3[$k+1] > 0){
							$hasta_final = number_format($hasta_final + 0.1,3,'.','');
						}
						//Personalizamos los elementos
						//Faja
						if(strcmp($faja[$i],"SI") == 0){
							$faja[$i] = "";
						}
						else if(strcmp($faja[$i],"NO") == 0){
							$faja[$i] = "SNS";
						}
						//Saneamiento				
						if(strcmp($saneamiento[$i],"SI") == 0){
							$saneamiento[$i] = "";
						}
						else if(strcmp($saneamiento[$i],"NO") == 0){
							$saneamiento[$i] = "SNS";
						}
						//Calzada
						if(strcmp($calzada[$i],"SI") == 0){
							$calzada[$i] = "";
						}
						else if(strcmp($calzada[$i],"NO") == 0){
							$calzada[$i] = "SNS";
						}
						//Berma
						if(strcmp($bermas[$i],"SI") == 0){
							$bermas[$i] = "";
						}
						else if(strcmp($bermas[$i],"NO") == 0){
							$bermas[$i] = "SNS";
						}
						//Señalizacion
						if(strcmp($senalizacion[$i],"SI") == 0){
							$senalizacion[$i] = "";
						}
						else if(strcmp($senalizacion[$i],"NO") == 0){
							$senalizacion[$i] = "SNS";
						}
						//Demarcacion
						if(strcmp($demarcacion[$i],"SI") == 0){
							$demarcacion[$i] = "";
						}
						else if(strcmp($demarcacion[$i],"NO") == 0){
							$demarcacion[$i] = "SNS";
						}
						//Verificamos si el Rol ya tiene exclusiones
						$consulta7 = "select count(*) as cantidad from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";
						$resultado7 = $conexion_db->query($consulta7);
						$fila7 = $resultado7->fetch_array(MYSQL_ASSOC);
						//Si no tiene exclusiones
						if($fila7["cantidad"] == 0){	
							$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;				
						}
						else{
							$auxiliar = 0;
							$consulta9 = "select * from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";	//Obtenemos todas las exclusiones de ese rol
							$resultado9 = $conexion_db->query($consulta9);
							while($fila9 = $resultado9->fetch_array(MYSQL_ASSOC)){
								//Generamos el valor hasta_final_DB
								$aux3 = str_split(number_format($fila9["hastaDesafeccionReal"],3,'.',''));	//Creamos un array del valor hasta
								$aux4 = array();
								$k=0;
								for($j=0;$j<count($aux3);$j++){
									if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
										$k = $j+1;
									}
								}
								for($j=0;$j<=$k;$j++){
									$aux4[$j] = $aux3[$j];	//Creamos el array hasta
								}
								//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
								$hasta_final_DB = number_format(implode('',$aux4),3,'.','');
								if($aux3[$k+1] > 0){
									$hasta_final_DB = number_format($hasta_final_DB + 0.1,3,'.','');
								}						

								//Fin generador de hasta_final_db
								//Verifica si son iguales los valores entonces no se le sumo ninguno
								if($desde_final == $hasta_final_DB){
									$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;
									$auxiliar = 1;
									break;	
								}
								else if(number_format($desde_final + 0.1,3,'.','') == $hasta_final_DB){
									$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".number_format($desde_final + 0.1,3,'.','')." and kmFinalSubSegmentos <= ".$hasta_final;
									$auxiliar=1;
									break;
								}
							}
							if($auxiliar == 0){
								$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;
							}
						}
						$resultado8 = $conexion_db->query($consulta8);
						while($fila8 = $resultado8->fetch_array(MYSQL_ASSOC)){ //Se actualiza la tabla designacion				
							$consulta10 = "update designacion set fajaDesignacion = '".$faja[$i]."', saneamientoDesignacion = '".$saneamiento[$i].
							"', calzadaDesignacion = '".$calzada[$i]."', bermasDesignacion = '".$bermas[$i]."', senalizacionDesignacion = '".$senalizacion[$i].
							"', demarcacionDesignacion = '".$demarcacion[$i]."' where nroSegmentoDesignacion = ".$fila8["segmentoSubSegmentos"].
							" and nroTramoDesignacion = ".$fila8["tramoSubSegmentos"];				
							$resultado10 = $conexion_db->query($consulta10);		

							$consulta11 = "update subsegmentos set estadoSubSegmentos = 0 where idSubSegmentos = ".$fila8["idSubSegmentos"];
							$resultado11 = $conexion_db->query($consulta11);
						}				
						//Actualizamos desafeccion real
						$consulta3 = "insert into desafeccionReal (idDesafeccionReal, rolDesafeccionReal, desdeDesafeccionReal, hastaDesafeccionReal, ".
						"longitudDesafeccionReal, fecha_inicio, fecha_termino, noConsiderarFecha, fajaVialDesafeccionReal, saneamientoDesafeccionReal, ".
						"calzadaDesafeccionReal, bermasDesafeccionReal, senalizacionDesafeccionReal, demarcacionDesafeccionReal, exclusionInicial, ".
						"observacionDesafeccionReal) values ('', '".$rol[$i]."', ".number_format($desde[$i], 3, '.', '').", ".
						number_format($hasta[$i], 3, '.', '').", ".number_format($longitud[$i], 3, '.', '').", '".$fechaInicio[$i]."', '".$fechaTermino[$i].
						"', ".$noConsiderar.", '".$faja[$i]."', '".$saneamiento[$i]."', '".$calzada[$i]."', '".$bermas[$i]."', '".$senalizacion[$i]."', '".$demarcacion[$i]."', ".$exclusionInicial[$i].", '".htmlentities(mb_strtoupper(trim($resolucion[$i]),'UTF-8'))."')";
						$resultado3 = $conexion_db->query($consulta3);	

						//Desafectamos el segmento completo para que no salga en el sorteo
						$consulta4 = "select segmentoSubSegmentos, count(segmentoSubSegmentos) as repite_segmento from subSegmentos group by segmentoSubSegmentos";
						$resultado4 = $conexion_db->query($consulta4);	//Obtenemos la cantidad de subsegmentos
						while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
							$consulta5 = "select count(nroSegmentoDesignacion) as cantidad_tramos from designacion where nroSegmentoDesignacion= ".
							$fila4["segmentoSubSegmentos"]." and fajaDesignacion='SNS' and saneamientoDesignacion='SNS' and calzadaDesignacion='SNS' and ".
							"bermasDesignacion='SNS' and senalizacionDesignacion='SNS' and demarcacionDesignacion='SNS'";
							$resultado5 = $conexion_db->query($consulta5);
							$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
							if($fila5["cantidad_tramos"] == $fila4["repite_segmento"]){
								$consulta6 = "update segmentos set estadoSegmento = 0 where numeroSegmento = ".$fila4["segmentoSubSegmentos"];
								$resultado6 = $conexion_db->query($consulta6);				
							}		
						}
						//Cerramos la conexión
						//$conexion_db->close();
					}
					else{
						$aux_fecha++;
					}	
				}
				else{
					//Generamos el valor desde a buscar
					$aux = str_split(number_format($desde[$i], 3, '.', ''));				
					$aux2 = array();
					$k=0;
					for($j=0;$j < count($aux);$j++){
						if(strcmp($aux[$j],".") == 0){
							$k=$j+1;
						}
					}
					for($j=0;$j<=$k;$j++){
						$aux2[$j] = $aux[$j];
					}		
					$desde_final = number_format(implode('',$aux2), 3, '.', '');

					//Generamos el valor hasta a buscar
					$aux3 = str_split(number_format($hasta[$i],3,'.',''));	//Creamos un array del valor hasta
					$aux4 = array();
					$k=0;
					for($j=0;$j<count($aux3);$j++){
						if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
							$k = $j+1;
						}
					}
					for($j=0;$j<=$k;$j++){
						$aux4[$j] = $aux3[$j];	//Creamos el array hasta
					}
					//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
					$hasta_final = number_format(implode('',$aux4),3,'.','');
					if($aux3[$k+1] > 0){
						$hasta_final = number_format($hasta_final + 0.1,3,'.','');
					}
					//Personalizamos los elementos
					//Faja
					if(strcmp($faja[$i],"SI") == 0){
						$faja[$i] = "";
					}
					else if(strcmp($faja[$i],"NO") == 0){
						$faja[$i] = "SNS";
					}
					//Saneamiento				
					if(strcmp($saneamiento[$i],"SI") == 0){
						$saneamiento[$i] = "";
					}
					else if(strcmp($saneamiento[$i],"NO") == 0){
						$saneamiento[$i] = "SNS";
					}
					//Calzada
					if(strcmp($calzada[$i],"SI") == 0){
						$calzada[$i] = "";
					}
					else if(strcmp($calzada[$i],"NO") == 0){
						$calzada[$i] = "SNS";
					}
					//Berma
					if(strcmp($bermas[$i],"SI") == 0){
						$bermas[$i] = "";
					}
					else if(strcmp($bermas[$i],"NO") == 0){
						$bermas[$i] = "SNS";
					}
					//Señalizacion
					if(strcmp($senalizacion[$i],"SI") == 0){
						$senalizacion[$i] = "";
					}
					else if(strcmp($senalizacion[$i],"NO") == 0){
						$senalizacion[$i] = "SNS";
					}
					//Demarcacion
					if(strcmp($demarcacion[$i],"SI") == 0){
						$demarcacion[$i] = "";
					}
					else if(strcmp($demarcacion[$i],"NO") == 0){
						$demarcacion[$i] = "SNS";
					}
					//Verificamos si el Rol ya tiene exclusiones
					$consulta7 = "select count(*) as cantidad from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";
					$resultado7 = $conexion_db->query($consulta7);
					$fila7 = $resultado7->fetch_array(MYSQL_ASSOC);
					//Si no tiene exclusiones
					if($fila7["cantidad"] == 0){	
						$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;				
					}
					else{
						$auxiliar = 0;
						$consulta9 = "select * from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";	//Obtenemos todas las exclusiones de ese rol
						$resultado9 = $conexion_db->query($consulta9);
						while($fila9 = $resultado9->fetch_array(MYSQL_ASSOC)){
							//Generamos el valor hasta_final_DB
							$aux3 = str_split(number_format($fila9["hastaDesafeccionReal"],3,'.',''));	//Creamos un array del valor hasta
							$aux4 = array();
							$k=0;
							for($j=0;$j<count($aux3);$j++){
								if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
									$k = $j+1;
								}
							}
							for($j=0;$j<=$k;$j++){
								$aux4[$j] = $aux3[$j];	//Creamos el array hasta
							}
							//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
							$hasta_final_DB = number_format(implode('',$aux4),3,'.','');
							if($aux3[$k+1] > 0){
								$hasta_final_DB = number_format($hasta_final_DB + 0.1,3,'.','');
							}						
							//Fin generador de hasta_final_db
							//Verifica si son iguales los valores entonces no se le sumo ninguno
							if($desde_final == $hasta_final_DB){
								$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;
								$auxiliar = 1;
								break;	
							}
							else if(number_format($desde_final + 0.1,3,'.','') == $hasta_final_DB){
								$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".number_format($desde_final + 0.1,3,'.','')." and kmFinalSubSegmentos <= ".$hasta_final;
								$auxiliar=1;
								break;
							}
						}
						if($auxiliar == 0){
							$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final." and kmFinalSubSegmentos <= ".$hasta_final;
						}
					}
					$resultado8 = $conexion_db->query($consulta8);
					while($fila8 = $resultado8->fetch_array(MYSQL_ASSOC)){ //Se actualiza la tabla designacion				
						$consulta10 = "update designacion set fajaDesignacion = '".$faja[$i]."', saneamientoDesignacion = '".$saneamiento[$i].
						"', calzadaDesignacion = '".$calzada[$i]."', bermasDesignacion = '".$bermas[$i]."', senalizacionDesignacion = '".$senalizacion[$i].
						"', demarcacionDesignacion = '".$demarcacion[$i]."' where nroSegmentoDesignacion = ".$fila8["segmentoSubSegmentos"].
						" and nroTramoDesignacion = ".$fila8["tramoSubSegmentos"];				
						$resultado10 = $conexion_db->query($consulta10);		

						$consulta11 = "update subsegmentos set estadoSubSegmentos = 0 where idSubSegmentos = ".$fila8["idSubSegmentos"];
						$resultado11 = $conexion_db->query($consulta11);
					}				
					//Actualizamos desafeccion real
						$consulta3 = "insert into desafeccionReal (idDesafeccionReal, rolDesafeccionReal, desdeDesafeccionReal, hastaDesafeccionReal, ".
						"longitudDesafeccionReal, fecha_inicio, fecha_termino, noConsiderarFecha, fajaVialDesafeccionReal, saneamientoDesafeccionReal, ".
						"calzadaDesafeccionReal, bermasDesafeccionReal, senalizacionDesafeccionReal, demarcacionDesafeccionReal, exclusionInicial, ".
						"observacionDesafeccionReal) values ('', '".$rol[$i]."', ".number_format($desde[$i], 3, '.', '').", ".
						number_format($hasta[$i], 3, '.', '').", ".number_format($longitud[$i], 3, '.', '').", '".$fechaInicio[$i]."', '".$fechaTermino[$i].
						"', ".$noConsiderar.", '".$faja[$i]."', '".$saneamiento[$i]."', '".$calzada[$i]."', '".$bermas[$i]."', '".$senalizacion[$i]."', '".$demarcacion[$i]."', ".$exclusionInicial[$i].", '".htmlentities(mb_strtoupper(trim($resolucion[$i]),'UTF-8'))."')";
						$resultado3 = $conexion_db->query($consulta3);	

					//Desafectamos el segmento completo para que no salga en el sorteo
					$consulta4 = "select segmentoSubSegmentos, count(segmentoSubSegmentos) as repite_segmento from subSegmentos group by segmentoSubSegmentos";
					$resultado4 = $conexion_db->query($consulta4);	//Obtenemos la cantidad de subsegmentos
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						$consulta5 = "select count(nroSegmentoDesignacion) as cantidad_tramos from designacion where nroSegmentoDesignacion= ".
						$fila4["segmentoSubSegmentos"]." and fajaDesignacion='SNS' and saneamientoDesignacion='SNS' and calzadaDesignacion='SNS' and ".
						"bermasDesignacion='SNS' and senalizacionDesignacion='SNS' and demarcacionDesignacion='SNS'";
						$resultado5 = $conexion_db->query($consulta5);
						$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
						if($fila5["cantidad_tramos"] == $fila4["repite_segmento"]){
							$consulta6 = "update segmentos set estadoSegmento = 0 where numeroSegmento = ".$fila4["segmentoSubSegmentos"];
							$resultado6 = $conexion_db->query($consulta6);				
						}		
					}				
				}
			}
		}
		if($aux_error != 0){
			//Se carga la página
			$tpl = new TemplatePower("ingresarExclusionesFormulario.html");

			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
		
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("MENSAJE", "HA OCURRIDO UNO O VARIOS ERRORES, VERIFICAR EL MANUAL");

			for($i=0;$i<count($rol);$i++){			
				$tpl->newBlock("EXCLUSIONES");
				$tpl->assign("INDICE_ARRAY",$i);
				$tpl->assign("VALOR_EXCLUSION_INICIAL",$i);					
				
				$tpl->assign("KM_INICIO", $desde[$i]);
				$tpl->assign("KM_FINAL", $hasta[$i]);
				$tpl->assign("LONGITUD", $longitud[$i]);
				$tpl->assign("FECHA_INICIO", $fechaInicio[$i]);
				$tpl->assign("FECHA_TERMINO", $fechaTermino[$i]);				
				if($exclusionInicial[$i] == 1){
					$tpl->assign("CHECK", "checked");
				}
				else{
					$tpl->assign("CHECK", "");
				}				
				$tpl->assign("RESOLUCION", $resolucion[$i]);
				if(strcmp($faja[$i], "SI") == 0){
					$tpl->assign("VALORFAJASELECT", "SI");
					$tpl->assign("VALORFAJA1", "NO");
					$tpl->assign("VALORFAJA2", "");					
				}
				else{
					$tpl->assign("VALORFAJASELECT", "NO");
					$tpl->assign("VALORFAJA1", "SI");
					$tpl->assign("VALORFAJA2", "");					
				}
				if(strcmp($saneamiento[$i], "SI") == 0){
					$tpl->assign("VALORSANEAMIENTOSELECT", "SI");
					$tpl->assign("VALORSANEAMIENTO1", "NO");
					$tpl->assign("VALORSANEAMIENTO2", "");					
				}
				else{
					$tpl->assign("VALORSANEAMIENTOSELECT", "NO");
					$tpl->assign("VALORSANEAMIENTO1", "SI");
					$tpl->assign("VALORSANEAMIENTO2", "");					
				}
				if(strcmp($calzada[$i], "SI") == 0){
					$tpl->assign("VALORCALZADASELECT", "SI");
					$tpl->assign("VALORCALZADA1", "NO");
					$tpl->assign("VALORCALZADA2", "");					
				}
				else{
					$tpl->assign("VALORCALZADASELECT", "NO");
					$tpl->assign("VALORCALZADA1", "SI");
					$tpl->assign("VALORCALZADA2", "");					
				}
				if(strcmp($bermas[$i], "SI") == 0){
					$tpl->assign("VALORBERMASELECT", "SI");
					$tpl->assign("VALORBERMA1", "NO");
					$tpl->assign("VALORBERMA2", "");					
				}
				else{
					$tpl->assign("VALORBERMASELECT", "NO");
					$tpl->assign("VALORBERMA1", "SI");
					$tpl->assign("VALORBERMA2", "");					
				}
				if(strcmp($demarcacion[$i], "SI") == 0){
					$tpl->assign("VALORDEMARCACIONSELECT", "SI");
					$tpl->assign("VALORDEMARCACION1", "NO");
					$tpl->assign("VALORDEMARCACION2", "");					
				}
				else{
					$tpl->assign("VALORDEMARCACIONSELECT", "NO");
					$tpl->assign("VALORDEMARCACION1", "SI");
					$tpl->assign("VALORDEMARCACION2", "");					
				}
				if(strcmp($senalizacion[$i], "SI") == 0){
					$tpl->assign("VALORSENALIZACIONSELECT", "SI");
					$tpl->assign("VALORSENALIZACION1", "NO");
					$tpl->assign("VALORSENALIZACION2", "");					
				}
				else{
					$tpl->assign("VALORSENALIZACIONSELECT", "NO");
					$tpl->assign("VALORSENALIZACION1", "SI");
					$tpl->assign("VALORSENALIZACION2", "");					
				}
				$tpl->assign("VALORROL", $rol[$i]);
				//Información de la red caminera para obtener los rol
				$consulta = "select distinct rolRedCaminera from redcaminera";
				$resultado = $conexion_db->query($consulta);
				//Recorremos la consulta
				while($fila = $resultado->fetch_array(MYSQL_ASSOC)){										
					if(strcmp($fila["rolRedCaminera"], $rol[$i]) != 0){						
						$tpl->newBlock("ROL_CAMINO");	
						$tpl->assign("ROL",$fila["rolRedCaminera"]);							
					}					
				}			
			}		
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}	
		if($aux_fecha != 0){
			$_SESSION["MENSAJE_CUMPLE"] = "SI_FECHA";
		}
		else{
			$_SESSION["MENSAJE_CUMPLE"] = "SI";	
		}
		//Cerramos la conexión
		$conexion_db->close();
		//Redireccionamos		
		header("Location: verExclusion.php");
	}

	function compararFechas($primera, $segunda){
		$valoresPrimera = explode ("-", $primera);   
		$valoresSegunda = explode ("-", $segunda); 
		$anyoPrimera = $valoresPrimera[0];  
		$mesPrimera = $valoresPrimera[1];  
		$diaPrimera = $valoresPrimera[2]; 
		$anyoSegunda = $valoresSegunda[0];  
		$mesSegunda = $valoresSegunda[1];  
		$diaSegunda = $valoresSegunda[2];
		$diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
		$diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     
		if(!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)){
			// "La fecha ".$primera." no es válida";
			return 0;
		}
		else if(!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)){
			// "La fecha ".$segunda." no es válida";
			return 0;
		}
		else{
			return  $diasPrimeraJuliano - $diasSegundaJuliano;
		} 
	}
			
			//Generamos el valor desde a buscar
			/*$aux = str_split(number_format($desde[$i], 2, '.', ''));
			$aux2 = array();
			$k=0;
			for($j=0;$j < count($aux);$j++){
				if(strcmp($aux[$j],".") == 0){
					$k=$j+1;
				}
			}
			for($j=0;$j<=$k;$j++){
				$aux2[$j] = $aux[$j];
			}		
			$desde_final = number_format(implode('',$aux2), 2, '.', '');
		
			//Generamos el valor hasta a buscar
			$aux3 = str_split(number_format($hasta[$i],2,'.',''));	//Creamos un array del valor hasta
			$aux4 = array();
			$k=0;
			for($j=0;$j<count($aux3);$j++){
				if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
					$k = $j+1;
				}
			}
			for($j=0;$j<=$k;$j++){
				$aux4[$j] = $aux3[$j];	//Creamos el array hasta
			}
			//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
			$hasta_final = number_format(implode('',$aux4),2,'.','');
			if($aux3[$k+1] > 0){
				$hasta_final = number_format($hasta_final + 0.1,2,'.','');
			}
				
			//Personalizamos los elementos
			//Faja
			if(strcmp($faja[$i],"SI") == 0){
				$faja[$i] = "";
			}
			else if(strcmp($faja[$i],"NO") == 0){
				$faja[$i] = "SNS";
			}
			//Saneamiento				
			if(strcmp($saneamiento[$i],"SI") == 0){
				$saneamiento[$i] = "";
			}
			else if(strcmp($saneamiento[$i],"NO") == 0){
				$saneamiento[$i] = "SNS";
			}
			//Calzada
			if(strcmp($calzada[$i],"SI") == 0){
				$calzada[$i] = "";
			}
			else if(strcmp($calzada[$i],"NO") == 0){
				$calzada[$i] = "SNS";
			}
			//Berma
			if(strcmp($bermas[$i],"SI") == 0){
				$bermas[$i] = "";
			}
			else if(strcmp($bermas[$i],"NO") == 0){
				$bermas[$i] = "SNS";
			}
			//Señalizacion
			if(strcmp($senalizacion[$i],"SI") == 0){
				$senalizacion[$i] = "";
			}
			else if(strcmp($senalizacion[$i],"NO") == 0){
				$senalizacion[$i] = "SNS";
			}
			//Demarcacion
			if(strcmp($demarcacion[$i],"SI") == 0){
				$demarcacion[$i] = "";
			}
			else if(strcmp($demarcacion[$i],"NO") == 0){
				$demarcacion[$i] = "SNS";
			}
			
			//Verificamos si el Rol ya tiene exclusiones
			$consulta7 = "select count(*) as cantidad from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";
			$resultado7 = $conexion_db->query($consulta7);
			$fila7 = $resultado7->fetch_array(MYSQL_ASSOC);
			//Si no tiene exclusiones
			if($fila7["cantidad"] == 0){	
				$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final.	
				" and kmFinalSubSegmentos <= ".$hasta_final;				
			}
			else{
				$auxiliar = 0;
				$consulta9 = "select * from desafeccionReal where rolDesafeccionReal = '".$rol[$i]."'";	//Obtenemos todas las exclusiones de ese rol
				$resultado9 = $conexion_db->query($consulta9);
				while($fila9 = $resultado9->fetch_array(MYSQL_ASSOC)){
					//Generamos el valor hasta_final_DB
					$aux3 = str_split(number_format($fila9["hastaDesafeccionReal"],2,'.',''));	//Creamos un array del valor hasta
					$aux4 = array();
					$k=0;
					for($j=0;$j<count($aux3);$j++){
						if(strcmp($aux3[$j],".") == 0){	//Buscamos el indice de donde esta el . + 1
							$k = $j+1;
						}
					}
					for($j=0;$j<=$k;$j++){
						$aux4[$j] = $aux3[$j];	//Creamos el array hasta
					}
					//Verificamos si el valor es distinto de cero para redondear hacia arriba, si o si
					$hasta_final_DB = number_format(implode('',$aux4),2,'.','');
					if($aux3[$k+1] > 0){
						$hasta_final_DB = number_format($hasta_final_DB + 0.1,2,'.','');
					}						
					//Fin generador de hasta_final_db
					
					//Verifica si son iguales los valores entonces no se le sumo ninguno
					if($desde_final == $hasta_final_DB){
						$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final.
						" and kmFinalSubSegmentos <= ".$hasta_final;
						$auxiliar = 1;
						break;	
					}
					else if(number_format($desde_final + 0.1,2,'.','') == $hasta_final_DB){
						$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".number_format($desde_final + 0.1,2,'.','').
						" and kmFinalSubSegmentos <= ".$hasta_final;
						$auxiliar=1;
						break;
					}
				}
				if($auxiliar == 0){
					$consulta8 = "SELECT * FROM subsegmentos WHERE rolSubSegmentos = '".$rol[$i]."' and kmInicioSubSegmento >= ".$desde_final.
					" and kmFinalSubSegmentos <= ".$hasta_final;
				}
			}
			$resultado8 = $conexion_db->query($consulta8);
			while($fila8 = $resultado8->fetch_array(MYSQL_ASSOC)){ //Se actualiza la tabla designacion				
				$consulta10 = "update designacion set fajaDesignacion = '".$faja[$i]."', saneamientoDesignacion = '".$saneamiento[$i].
				"', calzadaDesignacion = '".$calzada[$i]."', bermasDesignacion = '".$bermas[$i]."', senalizacionDesignacion = '".$senalizacion[$i].
				"', demarcacionDesignacion = '".$demarcacion[$i]."' where nroSegmentoDesignacion = ".$fila8["segmentoSubSegmentos"].
				" and nroTramoDesignacion = ".$fila8["tramoSubSegmentos"];				
				$resultado10 = $conexion_db->query($consulta10);
				
				$consulta11 = "update subsegmentos set estadoSubSegmentos = 0 where idSubSegmentos = ".$fila8["idSubSegmentos"];
				$resultado11 = $conexion_db->query($consulta11);		
			}
			//Actualizamos desafeccion real
			$consulta3 = "insert into desafeccionReal (idDesafeccionReal, rolDesafeccionReal, desdeDesafeccionReal, ".
				"hastaDesafeccionReal, longitudDesafeccionReal, fajaVialDesafeccionReal, saneamientoDesafeccionReal, ".
				"calzadaDesafeccionReal, bermasDesafeccionReal, senalizacionDesafeccionReal, demarcacionDesafeccionReal, ".
				"exclusionInicial, observacionDesafeccionReal) values ('', '".$rol[$i]."', ".number_format($desde[$i], 2, '.', '').
				", ".number_format($hasta[$i], 2, '.', '').", ".number_format($longitud[$i], 2, '.', '').", '".$faja[$i].
				"', '".$saneamiento[$i]."', '".$calzada[$i]."', '".$bermas[$i]."', '".$senalizacion[$i]."', '".$demarcacion[$i].
				"', ".$exclusionInicial[$i].", '".htmlentities(mb_strtoupper(trim($resolucion[$i]),'UTF-8'))."')";
			$resultado3 = $conexion_db->query($consulta3);
			//}						
		}		
		//Desafectamos el segmento completo para que no salga en el sorteo
		$consulta4 = "select segmentoSubSegmentos, count(segmentoSubSegmentos) as repite_segmento from subSegmentos group by segmentoSubSegmentos";
		$resultado4 = $conexion_db->query($consulta4);	//Obtenemos la cantidad de subsegmentos
		while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
			$consulta5 = "select count(nroSegmentoDesignacion) as cantidad_tramos from designacion where nroSegmentoDesignacion= ".
			$fila4["segmentoSubSegmentos"]." and fajaDesignacion='SNS' and saneamientoDesignacion='SNS' and calzadaDesignacion='SNS' and ".
			"bermasDesignacion='SNS' and senalizacionDesignacion='SNS' and demarcacionDesignacion='SNS'";
			$resultado5 = $conexion_db->query($consulta5);
			$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
			if($fila5["cantidad_tramos"] == $fila4["repite_segmento"]){
				$consulta6 = "update segmentos set estadoSegmento = 0 where numeroSegmento = ".$fila4["segmentoSubSegmentos"];
				$resultado6 = $conexion_db->query($consulta6);				
			}		
		}
		//Cerramos la conexión
		$conexion_db->close();
		
		//Redireccionamos
		$_SESSION["MENSAJE_CUMPLE"] = "SI";
		header("Location: verExclusion.php");
	}*/
?>