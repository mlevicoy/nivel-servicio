<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	//validarAdministrador();
	validaTiempo();
	
	if(!isset($_POST["cargador"])){
		//Se carga la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("contrasena.html");
		/*}
		else{
			$tpl = new TemplatePower("contrasena_usr.html");
		}*/
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
			
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		//Mensaje		
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","ERROR AL CAMBIAR LA CONTRASE&Ntilde;A - LA CONTRASE&Ntilde;A ACTUAL NO CORRESPONDE");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);						
		}
		if(isset($_SESSION["MENSAJE_NO_CUMPLE2"]) and $_SESSION["MENSAJE_NO_CUMPLE2"] == "SI"){
			$tpl->assign("MENSAJE","ERROR AL CAMBIAR LA CONTRASE&Ntilde;A - NO COINCIDE LA CONTRASE&Ntilde;A NUEVA CON SU CONFIRMACI&Oacute;N");
			unset($_SESSION["MENSAJE_NO_CUMPLE2"]);						
		}
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","LA CONTRASE&Ntilde;A SE HA ACTUALIZADO CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);						
		}
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{		
		//Información del formulario
		$contrasena_actual = htmlentities(trim($_POST["contrasenaActual"]));
		$contrasena_nueva = htmlentities(trim($_POST["contrasenaNueva"]));
		$contrasena_nueva_dos = htmlentities(trim($_POST["validarContrasenaNueva"]));
		
		if(strcmp($contrasena_nueva,$contrasena_nueva_dos) == 0){
			$consulta = "select count(*) as cantidad from datosUsuario where userName_usuario = '".$_SESSION["NOMBRE_USUARIO"].
			"' and contrasena_usuario = '".$contrasena_actual."'";
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			if($fila["cantidad"] != 0){
				$consulta2 = "update datosUsuario set contrasena_usuario = '".$contrasena_nueva."' where userName_usuario = '".$_SESSION["NOMBRE_USUARIO"].
				"'";
				$resultado2 = $conexion_db->query($consulta2);
				$conexion_db->close();
				$_SESSION["MENSAJE_CUMPLE"] = "SI";	
				header("Location: contrasena.php");
			}
			else{
				$conexion_db->close();
				$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";	
				header("Location: contrasena.php");
			}
		}
		else{
			$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";	
			header("Location: contrasena.php");		
		}
	}
?>