<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		//Obtenemos la informaci de los bimestres
		$consulta = "select * from bimestre where NroPagoBimestre > 0";
		$resultado = $conexion_db->query($consulta);
		
		//Se carga la página		
		$tpl = new TemplatePower("sorteo.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");		

		$tpl->prepare();

		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		//Mensajes
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","NO CORRESPONDE EL BIMESTRE PARA EL SORTEO");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);	
		}
		if(isset($_SESSION["MENSAJE_NO_CUMPLE2"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE2"],"SI") == 0){
			$tpl->assign("MENSAJE","LA FECHA INGRESADA ES MENOR O IGUAL AL TERMINO DEL BIMESTRE ANTERIOR");
			unset($_SESSION["MENSAJE_NO_CUMPLE2"]);	
		}
		//Fin mensaje
		//llenamos el select bimestre
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			if($fila["NroPagoBimestre"] == 1000){
				$tpl->newBlock("NUMERO_BIMESTRE");
				$tpl->assign("numBimestre",$fila["NroBimestre"]);
				$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");					
			}
			else{		
				$tpl->newBlock("NUMERO_BIMESTRE");
				$tpl->assign("numBimestre",$fila["NroBimestre"]);
				$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila["NroPagoBimestre"]);				
			}
			if($fila["estadoBimestre"] == 0){
				$tpl->assign("CHECK"," - FINALIZADA");
			}
			else{$tpl->assign("CHECK","");}
			
		}
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//Formulario sorteo		
		$nroBimestre = $_POST["numeroBimestre"];
		$fechaTerminoBimestreActual = $_POST["fechaTerminoBimestre"];
		
		//Variable session de la fecha termino
		$_SESSION["FECHA_TERMINO"] = $fechaTerminoBimestreActual;
		
		//Variable session del bimestre actual
		$_SESSION["BIMESTRE_SORTEO"] = $nroBimestre;
		
		//Verificamos si se repite el sorteo
		$consulta3 = "select count(*) as cantidad_repetida from segmentosSorteados where bimestreSorteado = ".$nroBimestre;
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
		
		//Repite el sorteo
		if($fila3["cantidad_repetida"] != 0){
			$conexion_db->close();
			header("Location: repetirSorteo.php");					
		}
		//Envia a sorteo
		else{
			//Sumamos la cantidad total de km
			$consulta4 = "select sum(longitudRedCaminera) as longitud from redCaminera";
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
			$kmIngresados = $fila4["longitud"];
			
			//Realizamos el calculo de $PI y segmentos
				//Vemos si es el bimestre final
			if($nroBimestre == 1000){
				//Obtenemos la cantidad de segmentos
				$consulta9 = "select count(*) as ctdadSegmentos from segmentos where estadoSegmento = 1"; 
				$resultado9 = $conexion_db->query($consulta9);
				$fila9 = $resultado9->fetch_array(MYSQL_ASSOC);
				
				$PI = 100;
				$PI_AUX = 100;
				
				//insertamos o actualizamos
				$consulta6 = "select count(*) as cantidad_inspeccionar from inspeccionar where bimestreInspeccionar = ".$nroBimestre;
				$resultado6 = $conexion_db->query($consulta6);
				$fila6 = $resultado6->fetch_array(MYSQL_ASSOC);
		
				if($fila6["cantidad_inspeccionar"] == 0){
					//Almacenamos la información en la tabla inspeccionar
					$consulta7 = "insert into inspeccionar (idInspeccionar, bimestreInspeccionar, totalInspeccionar, porcentajeInspeccionar, numeroSegmentos) ".
					"values ('', ".$nroBimestre.", ".number_format($kmIngresados, 3, '.', '').", ".$PI.", ".$fila9["ctdadSegmentos"].")";
					$resultado7 = $conexion_db->query($consulta7);	
				}
				else{
						//Actualizamos la información en la tabla inspeccionar
					$consulta8 = "update inspeccionar set totalInspeccionar = ".number_format($kmIngresados, 3, '.', '').", porcentajeInspeccionar = ".$PI.
					", numeroSegmentos = ".$fila9["ctdadSegmentos"]." where bimestreInspeccionar = ".$nroBimestre;
					$resultado8 = $conexion_db->query($consulta8);	
				}
						
				if(isset($resultado4)){ $resultado4-> close(); }				
				if(isset($resultado6)){ $resultado6-> close(); }
				if(isset($resultado9)){ $resultado9-> close(); }
			}
			else{
				if($kmIngresados > 10){ $PI = round(1000/$kmIngresados); $PI_AUX = 1000/$kmIngresados; }
				else{ $PI = 100; $PI_AUX = 100; }	

					//insertamos o actualizamos
				$consulta6 = "select count(*) as cantidad_inspeccionar from inspeccionar where bimestreInspeccionar = ".$nroBimestre;
				$resultado6 = $conexion_db->query($consulta6);
				$fila6 = $resultado6->fetch_array(MYSQL_ASSOC);
		
				if($fila6["cantidad_inspeccionar"] == 0){
						//Almacenamos la información en la tabla inspeccionar
					$consulta7 = "insert into inspeccionar (idInspeccionar, bimestreInspeccionar, totalInspeccionar, porcentajeInspeccionar, numeroSegmentos) ".
					"values ('', ".$nroBimestre.", ".number_format($kmIngresados, 3, '.', '').", ".$PI.", ".round(($PI_AUX/100) * $kmIngresados).")";
					$resultado7 = $conexion_db->query($consulta7);	
				}
				else{
						//Actualizamos la información en la tabla inspeccionar
					$consulta8 = "update inspeccionar set totalInspeccionar = ".number_format($kmIngresados, 3, '.', '').", porcentajeInspeccionar = ".$PI.
					", numeroSegmentos = ".round(($PI_AUX/100) * $kmIngresados)." where bimestreInspeccionar = ".$nroBimestre;
					$resultado8 = $conexion_db->query($consulta8);	
				}
						
				if(isset($resultado4)){ $resultado4-> close(); }				
				if(isset($resultado6)){ $resultado6-> close(); }				
			}
			
			if($nroBimestre == 1){
				//Actualizamos la fecha del sorteo en tabla bimestre
				$consulta2 = "update bimestre set fechaTerminoBimestre = '".$fechaTerminoBimestreActual."' where NroBimestre = ".$nroBimestre;
				$resultado2 = $conexion_db->query($consulta2);
			}
			else{
				if($nroBimestre == 1000){
					//Buscamos la información de bimestre
					$consulta = "select * from bimestre where estadoBimestre = 0 order by idBimestre desc limit 1";
					$resultado = $conexion_db->query($consulta);
					$fila = $resultado->fetch_array(MYSQL_ASSOC);					
					//Comparamos que la fecha ingresada no sea menor o igual que la de termino del bimestre anterior
					if(strtotime($fechaTerminoBimestreActual) <= strtotime($fila["fechaTerminoBimestre"])){
						//Redireccionamos
						$conexion_db->close();
						$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";
						header("Location: sorteo.php");
					}
					//La información ingresada es correcta
					else{
						//Calculamos la fecha inicio del bimestre
						$fechaInicioBimestreActual = date('Y-m-d',strtotime('+1day',strtotime($fila["fechaTerminoBimestre"])));
						//Actualizamos la fecha del sorteo en tabla bimestre
						$consulta2 = "update bimestre set fechaInicioBimestre = '".$fechaInicioBimestreActual."', fechaTerminoBimestre = '".
						$fechaTerminoBimestreActual."' where NroBimestre = ".$nroBimestre;
						$resultado2 = $conexion_db->query($consulta2);
					}				
				}
				else{					
					//Buscamos la información de bimestre
					$consulta = "select * from bimestre where NroBimestre = ".($nroBimestre-1);
					$resultado = $conexion_db->query($consulta);
					$fila = $resultado->fetch_array(MYSQL_ASSOC);
					//Vemos que la fecha termino de bimestre anterio no sea cero, es decir, no cumple el bimestre
					if(strcmp($fila["fechaTerminoBimestre"],"0000-00-00") == 0){
						//Redireccionamos
						$conexion_db->close();
						$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
						header("Location: sorteo.php");
					}
					else{
						//Comparamos que la fecha ingresada no sea menor o igual que la de termino del bimestre anterior
						if(strtotime($fechaTerminoBimestreActual) <= strtotime($fila["fechaTerminoBimestre"])){
							//Redireccionamos
							$conexion_db->close();
							$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";
							header("Location: sorteo.php");
						}
						//La información ingresada es correcta
						else{
							//Calculamos la fecha inicio del bimestre
							$fechaInicioBimestreActual = date('Y-m-d',strtotime('+1day',strtotime($fila["fechaTerminoBimestre"])));
							//Actualizamos la fecha del sorteo en tabla bimestre
							$consulta2 = "update bimestre set fechaInicioBimestre = '".$fechaInicioBimestreActual."', fechaTerminoBimestre = '".
							$fechaTerminoBimestreActual."' where NroBimestre = ".$nroBimestre;
							$resultado2 = $conexion_db->query($consulta2);
						}
					}					
				}				
			}			
			//Se carga la página
			
			$tpl = new TemplatePower("barra.html");
			
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");	

			$tpl->prepare();

			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->printToScreen();							
		}		
	}
?>