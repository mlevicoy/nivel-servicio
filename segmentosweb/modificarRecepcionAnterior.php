<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Funciones en sesiones.php
	validarAdministrador();
	validaTiempo();
	

	if(!isset($_POST["buscador"])){
		//Se carga la página
		$tpl = new TemplatePower("modificarRecepcionAnterior.html");
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
		
		//Verificamos si hay recepcion anterior
		$consulta = "select count(*) as cantidad from recepcionanteriordescontada where fajaRecepcionAnterior > 0 or saneamientoRecepcionAnterior > 0 or ".
			"calzadaRecepcionAnterior > 0 or bermasRecepcionAnterior > 0 or senalizacionRecepcionAnterior > 0 or demarcacionRecepcionAnterior > 0";	
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		
		if($fila["cantidad"] == 0){
			$tpl->assign("MENSAJE","NO EXISTE RECEPCIONES ANTERIORES, DEBE GENERAR COMO MINIMO UN INFORME FINAL");
		}
		else{
			$tpl->assign("MENSAJE","");
			//Cargamos los datos de la base de datos
			$consulta2 = "select * from bimestre where estadoBimestre = 0";
			$resultado2 = $conexion_db->query($consulta2);
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){			
				//Obtenemos las recepciones anteriores
				$consulta3 = "select * from recepcionanteriordescontada where bimestreRecepcionAnterior = ".$fila2["NroBimestre"];
				$resultado3 = $conexion_db->query($consulta3);
				$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);			
				$tpl->newBlock("RECEPCION_ANTERIOR");
				$tpl->assign("NRO_BIMESTRE", $fila2["NroBimestre"]);
				$tpl->assign("INSPECCION",$fila2["NroPagoBimestre"]);
				$tpl->assign("FAJA_VIAL",$fila3["fajaRecepcionAnterior"]);
				$tpl->assign("SANEAMIENTO",$fila3["saneamientoRecepcionAnterior"]);
				$tpl->assign("CALZADA",$fila3["calzadaRecepcionAnterior"]);
				$tpl->assign("BERMAS",$fila3["bermasRecepcionAnterior"]);
				$tpl->assign("SENALIZACION",$fila3["senalizacionRecepcionAnterior"]);
				$tpl->assign("DEMARCACION",$fila3["demarcacionRecepcionAnterior"]);			
			}
			$tpl->gotoBlock("_ROOT");
			if(isset($_SESSION["SUCESS"]) and strcmp($_SESSION["SUCESS"],"OK") == 0){
				$tpl->assign("MENSAJE", "LA ACTUALIZACIÓN SE REALIZÓ CORRECTAMENTE");
				unset($_SESSION["SUCESS"]);
			}
		}
		$tpl->printToScreen();	
	}
	else{
		$bimestre = $_POST["bimestre"];
		$inspeccion = $_POST["inspeccion"];
		$fajaVial = $_POST["fajaVial"];
		$saneamiento = $_POST["saneamiento"];
		$calzada = $_POST["calzada"];
		$bermas = $_POST["bermas"];
		$senalizacion = $_POST["senalizacion"];
		$demarcacion = $_POST["demarcacion"];
		
		for($i=0;$i<count($bimestre);$i++){
			$consulta = "update recepcionanteriordescontada set fajaRecepcionAnterior = ".$fajaVial[$i].", saneamientoRecepcionAnterior = ".
			$saneamiento[$i].", calzadaRecepcionAnterior = ".$calzada[$i].", bermasRecepcionAnterior = ".$bermas[$i].", senalizacionRecepcionAnterior = ".
			$senalizacion[$i].", demarcacionRecepcionAnterior = ".$demarcacion[$i]." where bimestreRecepcionAnterior = ".$bimestre[$i];	
			$resultado = $conexion_db->query($consulta);			
		}	
		$_SESSION["SUCESS"] = "OK";
		header("Location: modificarRecepcionAnterior.php");
	}	
?>