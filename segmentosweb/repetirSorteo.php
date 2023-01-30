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
		//Se carga la página
		if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("repetirSorteo.html");
		}
		else{
			$tpl = new TemplatePower("repetirSorteo_usr.html");
		}
			
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		//Mensajes
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","NOMBRE DE USUARIO Y/O CONTRASE&Ntilde;A INCORRECTA - INTENTAR DE NUEVO");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);	
		}
		if(isset($_SESSION["MENSAJE_NO_CUMPLE2"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE2"],"SI") == 0){
			$tpl->assign("MENSAJE","INTENTAR DE NUEVO POR FAVOR");
			unset($_SESSION["MENSAJE_NO_CUMPLE2"]);	
		}
		//Fin mensaje
		$tpl->printToScreen();
	}
	else{
		//Formulario repetir sorteo		
		$nombre_usuario = htmlentities(mb_strtolower(trim($_POST["nombreUsuario"]),'UTF-8'));		
		$contrasena_usuario = htmlentities(trim($_POST["contrasenaUsuario"]));
		$comentario_usuario = htmlentities(mb_strtolower(trim($_POST["comentario"]),'UTF-8'));
		
		//Validamos al usuario
		$consulta = "select *, count(*) as cantidad_usuario from datosusuario where userName_usuario = '".$nombre_usuario."' and contrasena_usuario = '".$contrasena_usuario."'";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);		
		//Vemos si hay algún usuario que cumpla con usuario y contraseña
		//No cumple
		if($fila["cantidad_usuario"] == 0){
			$conexion_db->close();
			$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
			header("Location: repetirSorteo.php");		
		}		
		//Cumple
		else{
			//Es Administrador
			if($fila["codigo_tipo_usuario"] == 1){			
				//Buscamos la información de bimestre
				$consulta2 = "select * from bimestre where NroBimestre = ".($_SESSION["BIMESTRE_SORTEO"]-1);
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
	
				//Comparamos que la fecha ingresada no sea menor o igual que la de termino del bimestre anterior
				if(strtotime($_SESSION["FECHA_TERMINO"]) <= strtotime($fila2["fechaTerminoBimestre"])){
					//Redireccionamos
					$conexion_db->close();
					$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";
					header("Location: sorteo.php");
				}
				//La información ingresada es correcta
				else{
					//Calculamos la fecha inicio del bimestre
					$fechaInicioBimestreActual = date('Y-m-d',strtotime('+1day',strtotime($fila2["fechaTerminoBimestre"])));
					//Actualizamos la fecha del sorteo en tabla bimestre
					$consulta3 = "update bimestre set fechaInicioBimestre = '".$fechaInicioBimestreActual."', fechaTerminoBimestre = '".$_SESSION["FECHA_TERMINO"].
					"' where NroBimestre = ".$_SESSION["BIMESTRE_SORTEO"];
					$resultado3 = $conexion_db->query($consulta3);
				}
				
				//Eliminamos la información de incumplimiento para el bimestre
				$consulta4 = "delete from incumplimiento where bimestreIncumplimiento = ".$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);
				//Eliminamos KM Descontados
				$consulta4 = "delete from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);
				//Eliminamos la información del sorteo en porcentaje
				$consulta4 = "delete from porcentaje where bimestrePorcentaje = ".$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);
				//Actualizamos recepcionAnterior
				$consulta4 = "update recepcionAnterior set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', calzadaRecepcionAnterior = '0.00', ".
				"bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', demarcacionRecepcionAnterior = '0.00' where bimestreRecepcionAnterior = ".
				$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);				
				//Actualizamos recepcionAnteriorDescontada
				$consulta4 = "update recepcionAnteriorDescontada set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', calzadaRecepcionAnterior = '0.00', ".
				"bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', demarcacionRecepcionAnterior = '0.00' where bimestreRecepcionAnterior = ".
				$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);								
				//Hay que eliminar el sorteo ya realizado
				$consulta4 = "delete from segmentosSorteados where bimestreSorteado = ".$_SESSION["BIMESTRE_SORTEO"];
				$resultado4 = $conexion_db->query($consulta4);
				
				//Ingresamos los cambio a tabla modificaciones
				if(!empty($comentario_usuario)){
					$consulta5 = "insert into modificaciones (codigoCambio, bimestreCambio, usuarioCambio, comentarioCambio) values ('', ".$_SESSION["BIMESTRE_SORTEO"].
					", '".$nombre_usuario."', '".$comentario_usuario."')";
					$resultado5 = $conexion_db->query($consulta5);
				}
												
				//Se carga la página
				//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
				$tpl = new TemplatePower("barra2.html");
				/*}
				else{
					$tpl = new TemplatePower("barra_usr.html");
				}*/

				$tpl->assignInclude("header", "header.html");
				$tpl->assignInclude("menu", "menu.html");

				$tpl->prepare();

				$tpl->assign("PAGINA","realizarSorteo.php");
				$tpl->assign("MENSAJE","--- REALIZANDO SORTEO ---");
				$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
				$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
				$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
				$conexion_db->close();
				$tpl->printToScreen();
			}
			//Es usuario pero no inspector fiscal
			else{
				$conexion_db->close();
				$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";
				header("Location: repetirSorteo.php");		
			}
		}
	}
?>