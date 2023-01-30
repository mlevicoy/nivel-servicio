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
		$consulta = "select * from bimestre where estadoBimestre = 0 order by idBimestre desc limit 1";
		$resultado = $conexion_db->query($consulta);
		//Se carga la página
		if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("modificarFecha.html");
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
		}
		else{
			$tpl = new TemplatePower("modificarFecha_usr.html");
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
		}
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		//Mensajes
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","NO SE PUDO MODIFICAR LA FECHA");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);	
		}
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","LA FECHA SE ACTUALIZ&Oacute; CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);	
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
		}
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//Formulario sorteo		
		$nroBimestre = $_POST["numeroBimestre"];
		$fechaTerminoBimestreActual = $_POST["fechaTerminoBimestre"];
		
		//Buscamos la información de bimestre
		$consulta = "select * from bimestre where NroBimestre = ".($nroBimestre-1);
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		//Vemos que la fecha termino de bimestre anterio no sea cero, es decir, no cumple el bimestre
		if(strcmp($fila["fechaTerminoBimestre"],"0000-00-00") == 0){
			//Redireccionamos
			$conexion_db->close();
			$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
			header("Location: modificarFecha.php");
		}
		else{
			//Comparamos que la fecha ingresada no sea menor o igual que la de termino del bimestre anterior
			if(strtotime($fechaTerminoBimestreActual) <= strtotime($fila["fechaTerminoBimestre"])){
				//Redireccionamos
				$conexion_db->close();
				$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
				header("Location: modificarFecha.php");
			}
			//La información ingresada es correcta
			else{
				//Actualizamos la fecha del sorteo en tabla bimestre
				$consulta2 = "update bimestre set fechaTerminoBimestre = '".$fechaTerminoBimestreActual."' where NroBimestre = ".$nroBimestre;
				$resultado2 = $conexion_db->query($consulta2);
				$conexion_db->close();
				$_SESSION["MENSAJE_CUMPLE"] = "SI";
				header("Location: modificarFecha.php");
			}
		}
	}
?>