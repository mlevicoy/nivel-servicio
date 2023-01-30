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
		//Se obtiene los tipo de usuario
		$consulta = "select * from tipoUsuario";
		$resultado = $conexion_db->query($consulta);				
		//Se carga la página
		$tpl = new TemplatePower("ingresarUsuario.html");

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();

		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		
		//Ingresa cuando el usuario ya existe
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and $_SESSION["MENSAJE_NO_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","NO SE PUDO CREAR EL USUARIO, YA EXISTE O USO LA PALABRA USUARIO");
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);			
		}
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","USUARIO CREADO CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);			
		}
		/*				
		//Se llena el select
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_CARGO");
			$tpl->assign("COD_CARGO",$fila["codigo_tipo_usuario"]);
			$tpl->assign("NOM_CARGO",$fila["nombre_tipo_usuario"]);			
		}*/

		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//Información del formulario
		$nombre = htmlentities(ucwords(mb_strtolower(trim($_POST["nombre"]),'UTF-8')));		
		$apellido = htmlentities(ucwords(mb_strtolower(trim($_POST["apellido"]),'UTF-8')));				
		$email = htmlentities(mb_strtolower(trim($_POST["email"]),'UTF-8'));						
		$contrasena_usuario = htmlentities(trim($_POST["contrasena"]));
		
		//$codigo_cargo = $_POST["cargo"];
		$codigo_cargo=1;

		//Generamos el nombre de usuario
		$separamos_email = explode('@',$email);
		$nombre_usuario = $separamos_email[0];
		//Consultamos si usuario existe
		$consulta = "select count(*) as cantidad_usuario from datosUsuario where userName_usuario = '".$nombre_usuario."'";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		//Si el usuario existe arroja el mensaje, sino guarda el usuario
		if($fila["cantidad_usuario"] == 1 || strcmp($nombre,"Usuario") == 0 || strcmp($apellido,"Usuario") == 0 || strcmp($nombre_usuario,"usuario") == 0){
			$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
			$conexion_db->close();
			header("Location: ingresarUsuario.php");			
		}
		else{
			$consulta = "insert into datosUsuario (codigo_usuario, codigo_tipo_usuario, nombre_usuario, apellido_usuario, correo_usuario, ".
			"userName_usuario, contrasena_usuario) values ('', ".$codigo_cargo.", '".$nombre."', '".$apellido."', '".$email."', '".$nombre_usuario.
			"', '".$contrasena_usuario."')";
			$resultado = $conexion_db->query($consulta);
			$_SESSION["MENSAJE_CUMPLE"] = "SI";
			$conexion_db->close();
			header("Location: ingresarUsuario.php");
		}
	}
?>