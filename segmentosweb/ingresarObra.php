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
		//Verificamos que la información inicial ya esta ingresada		
		$consulta = "select count(*) as cantidadObra from obra";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		
		//Información de las regiones
		$consulta2 = "select * from regiones";
		$resultado2 = $conexion_db->query($consulta2);
				
		//Se carga la página
		$tpl = new TemplatePower("ingresarObra.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		
		if($fila["cantidadObra"] == 1 and !isset($_SESSION["MENSAJE_CUMPLE"])){
			$tpl->assign("MENSAJE","LA INFORMACIÓN DE LA OBRA YA ESTA INGRESADA - SOLO PUEDE MODIFICARLA");
			$tpl->assign("CARGAINICIO","carga();");
		}		
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);			
			$tpl->assign("CARGAINICIO","carga();");
		}				
		
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
		$consulta = "insert into obra (idObra, nombreCompletoObra, nombreCortoObra, DireccionMandanteObra, fonoMandanteObra, webMandanteObra, mailMandanteObra, ciudadOficinaObra, ".
		"regionOficinaObra) values ('', '".addslashes($nombreCompleto)."', '".addslashes($nombreCorto)."', '".addslashes($direccionMandante)."', '".addslashes($telefonoMandante)."', '".addslashes($webMandante)."', '".addslashes($mailMandate).
		"', '".addslashes($ciudadMandante)."', '".$regionMandante."')";
		$resultado = $conexion_db->query($consulta);
		
		//Modificar nombre faena	
		$consulta2 = "update nombrefaena set nombreFaena = '".addslashes($nombreCorto)."'";
		$resultado2 = $conexion_db->query($consulta2);
				
		$_SESSION["MENSAJE_CUMPLE"] = "SI";	
		header("Location: ingresarObra.php");		
	}
?>