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
	
	if(!isset($_POST["cargador"])){
		//vemos si hay alguna obra ingresada
		$consulta3 = "select count(*) as cantidad from obra";
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
		if($fila3["cantidad"] == 0){
			//Información de las regiones
			$consulta2 = "select * from regiones";
			$resultado2 = $conexion_db->query($consulta2);				
			
			//Se carga la página
			$tpl = new TemplatePower("modificarObra.html");
			
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("MENSAJE","ERROR - PRIMERO DEBE INGRESADA UNA OBRA");
			$tpl->assign("CARGAINICIO","carga();");
			//Llenamos el select
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("REGION_MANDANTE");
				$tpl->assign("COD_REGION",$fila2["numeroRegion"]);
				$tpl->assign("NOM_REGION",$fila2["nombreRegion"]);
			}		
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}		
		else{
			//Informacion de la obra
			$consulta = "select * from obra";
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			//Información de las regiones
			$consulta2 = "select * from regiones";
			$resultado2 = $conexion_db->query($consulta2);		
			//Region del mandante
			$consulta3 = "select * from regiones where numeroRegion = '".$fila["regionOficinaObra"]."'";		
			$resultado3 = $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			//Se carga la página
			$tpl = new TemplatePower("modificarObra.html");

            $tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");


			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("NOMBRE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["nombreCompletoObra"])),'UTF-8'));
			$tpl->assign("NOMBRECORTO_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["nombreCortoObra"])),'UTF-8'));
			$tpl->assign("DIRECCIONMANDANTE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["direccionMandanteObra"])),'UTF-8'));
			$tpl->assign("TELEFONOMANDANTE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["fonoMandanteObra"])),'UTF-8'));
			$tpl->assign("EMAILMANDANTE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["webMandanteObra"])),'UTF-8'));
			$tpl->assign("WEBMANDANTE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["mailMandanteObra"])),'UTF-8'));
			$tpl->assign("CIUDADMANDANTE_UPDATE",mb_strtoupper(html_entity_decode(stripcslashes($fila["ciudadOficinaObra"])),'UTF-8'));
			$tpl->assign("CODIGOREGION_UPDATE",mb_strtoupper(html_entity_decode($fila["regionOficinaObra"]),'UTF-8'));
			$tpl->assign("NOMBREREGION_UPDATE",mb_strtoupper(html_entity_decode($fila3["nombreRegion"]),'UTF-8'));
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] = "SI"){
				$tpl->assign("MENSAJE","OBRA ACTUALIZADA CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);
			}
			//Llenamos el select
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				if(strcmp($fila2["numeroRegion"],$fila["regionOficinaObra"]) != 0){
					$tpl->newBlock("REGION_MANDANTE");
					$tpl->assign("COD_REGION",$fila2["numeroRegion"]);
					$tpl->assign("NOM_REGION",$fila2["nombreRegion"]);
				}
			}		
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}
	}
	else{
		//Información del formulario
		$nombreCompleto = htmlentities(mb_strtolower(trim($_POST["nombreCompleto"]),'UTF-8'));
        $nombreCorto = htmlentities(mb_strtolower(trim($_POST["nombreCorto"]),'UTF-8'));
		$direccionMandante = htmlentities(mb_strtolower(trim($_POST["direccionMandante"]),'UTF-8'));
		$telefonoMandante = htmlentities(mb_strtolower(trim($_POST["telefonoMandante"]),'UTF-8'));
		$webMandante = htmlentities(mb_strtolower(trim($_POST["webMandante"]),'UTF-8'));
		$mailMandate = htmlentities(mb_strtolower(trim($_POST["emailMandante"]),'UTF-8'));
		$ciudadMandante = htmlentities(mb_strtolower(trim($_POST["ciudadMandante"]),'UTF-8'));
		$regionMandante = $_POST["regionMandante"];
		//Almacenamos la informacion		
		$consulta = "update obra set nombreCompletoObra = '".addslashes($nombreCompleto)."', nombreCortoObra = '".addslashes($nombreCorto).
		"', direccionMandanteObra = '".addslashes($direccionMandante)."', fonoMandanteObra = '".addslashes($telefonoMandante).
		"', webMandanteObra = '".addslashes($webMandante)."', mailMandanteObra = '".addslashes($mailMandate)."', ciudadOficinaObra = '".
		addslashes($ciudadMandante)."', regionOficinaObra = '".$regionMandante."'";
		$resultado = $conexion_db->query($consulta);
		
		//Modificar nombre faena	
		$consulta2 = "update nombrefaena set nombreFaena = '".addslashes($nombreCorto)."'";
		$resultado2 = $conexion_db->query($consulta2);
		
		$_SESSION["MENSAJE_CUMPLE"] = "SI";	
		header("Location: modificarObra.php");		
	}
?>