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
		//Informacion del nombre faena
		$consulta = "select * from nombreFaena";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		//Se carga la página
		$tpl = new TemplatePower("modificarNombreFaena.html");
        $tpl->assignInclude("header", "header.html");
	    $tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		$tpl->assign("NOMBREFAENA_UPDATE",mb_strtoupper(html_entity_decode($fila["nombreFaena"]),'UTF-8'));
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] = "SI"){
			$tpl->assign("MENSAJE","NOMBRE FAENA ACTUALIZADA CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);
		}
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();		
	}
	else{
		//Información del formulario
		$nombreFaena = htmlentities(mb_strtolower(trim($_POST["nombreFaena"]),'UTF-8'));
        //Almacenamos la informacion		
		$consulta = "update nombreFaena set nombreFaena = '".$nombreFaena."'";
		$resultado = $conexion_db->query($consulta);
		
		$consulta = "update obra set nombreCortoObra = '".addslashes($nombreFaena)."'";
		$resultado = $conexion_db->query($consulta);
		
		$_SESSION["MENSAJE_CUMPLE"] = "SI";	
		header("Location: modificarNombreFaena.php");		
	}
?>