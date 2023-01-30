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
		if(!isset($_GET["id"])){
			//Se obtiene los tipo de usuario
			$consulta = "select * from tipoUsuario";
			$resultado = $conexion_db->query($consulta);				
			//Obtener los usuario
			$consulta2 = "select * from datosUsuario";
			$resultado2 = $conexion_db->query($consulta2);
			
			//Se carga la página
			$tpl = new TemplatePower("modificarUsuario.html");

			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("SELECT_INICIO","");
			$tpl->assign("VALOR_SELECT_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("SELECT_TERMINO","");
			$tpl->assign("VALOR_SELECT_TERMINO","");			
			$tpl->assign("CARGO_INICIO","");
			$tpl->assign("CARGO_INICIO_NOMBRE","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("CARGO_INICIO_FIN","");
			$tpl->assign("CARGO_FIN_NOMBRE","");
			//Ingresa cuando el usuario ya existe
			if(isset($_SESSION["MENSAJE_NO_CUMPLE2"]) and $_SESSION["MENSAJE_NO_CUMPLE2"] == "SI"){
				$tpl->assign("MENSAJE","USUARIO NO SE PUDO ACTUALIZAR - INTENTAR NUEVAMENTE");
				unset($_SESSION["MENSAJE_NO_CUMPLE2"]);			
			}
			if(isset($_SESSION["MENSAJE_OCUPADO"]) and $_SESSION["MENSAJE_OCUPADO"] == "SI"){
				$tpl->assign("MENSAJE","USUARIO NO SE PUDO ELIMINAR - CAMBIE DE CUENTA PARA ELIMINAR AL USUARIO");
				unset($_SESSION["MENSAJE_OCUPADO"]);			
			}
			if(isset($_SESSION["MENSAJE_OCUPADO2"]) and $_SESSION["MENSAJE_OCUPADO2"] == "SI"){
				$tpl->assign("MENSAJE","USUARIO NO SE PUDO MODIFICAR - CREAR OTRO ADMINISTRADOR PARA PODER ELIMINAR ESTA CUENTA");
				unset($_SESSION["MENSAJE_OCUPADO2"]);			
			}
			if(isset($_SESSION["MENSAJE_CUMPLE2"]) and $_SESSION["MENSAJE_CUMPLE2"] == "SI"){
				$tpl->assign("MENSAJE","USUARIO ACTUALIZADO CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE2"]);			
			}
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
				$tpl->assign("MENSAJE","USUARIO ELIMINADO CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);			
			}				
			//Se llena el select
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("CODIGO_CARGO");
				$tpl->assign("COD_CARGO",$fila["codigo_tipo_usuario"]);
				$tpl->assign("NOM_CARGO",$fila["nombre_tipo_usuario"]);			
			}
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("USUARIO_BUSCAR");
				$tpl->assign("CODIGO_USUARIO",$fila2["codigo_usuario"]);
				$tpl->assign("NOMBRE_USUARIO",$fila2["nombre_usuario"]." ".$fila2["apellido_usuario"]." - ".$fila2["userName_usuario"]);
			}
			
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}
		else{		
			//Se obtiene todos los tipo de usuario
			$consulta = "select * from tipoUsuario";
			$resultado = $conexion_db->query($consulta);			
			//Obtener los usuario
			$consulta2 = "select * from datosUsuario";
			$resultado2 = $conexion_db->query($consulta2);
			//Obtener usuario seleccionado
			$consulta4 = "select * from datosUsuario where codigo_usuario = ".$_GET["id"];
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);			
			//Obtenemos el tipo del usuario
			$consulta5 = "select * from tipoUsuario where codigo_tipo_usuario = ".$fila4["codigo_tipo_usuario"];
			$resultado5 = $conexion_db->query($consulta5);
			$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);			
			//Se carga la página
			$tpl = new TemplatePower("modificarUsuario.html");
			
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
			
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("NOMBRE_UPDATE", $fila4["nombre_usuario"]);
			$tpl->assign("APELLIDO_UPDATE", $fila4["apellido_usuario"]);
			$tpl->assign("EMAIL_UPDATE", $fila4["correo_usuario"]);
			$tpl->assign("CONTRASENA_UPDATE", $fila4["contrasena_usuario"]);
			//Usuario
			$tpl->assign("SELECT_TERMINO","");
			$tpl->assign("VALOR_SELECT_TERMINO","--- SELECCIONAR OPCI&Oacute;N ---");			
			$tpl->assign("SELECT_INICIO",$fila4["codigo_usuario"]);
			$tpl->assign("VALOR_SELECT_INICIO",$fila4["nombre_usuario"]." ".$fila4["apellido_usuario"]." - ".$fila4["userName_usuario"]);				
			//Tipo usuario
			$tpl->assign("CARGO_INICIO", $fila5["codigo_tipo_usuario"]);
			$tpl->assign("CARGO_INICIO_NOMBRE", $fila5["nombre_tipo_usuario"]);
			$tpl->assign("CARGO_INICIO_FIN","");
			$tpl->assign("CARGO_FIN_NOMBRE","--- SELECCIONAR OPCI&Oacute;N ---");			
			//Se llena el select de usuario
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				if($fila2["codigo_usuario"] != $_GET["id"]){					
					$tpl->newBlock("USUARIO_BUSCAR");
					$tpl->assign("CODIGO_USUARIO",$fila2["codigo_usuario"]);
					$tpl->assign("NOMBRE_USUARIO",$fila2["nombre_usuario"]." ".$fila2["apellido_usuario"]." - ".$fila2["userName_usuario"]);
				}
			}			
			//Se llena el select de cargos
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){				
				if($fila["codigo_tipo_usuario"] != $fila4["codigo_tipo_usuario"]){					
					$tpl->newBlock("CODIGO_CARGO");
					$tpl->assign("COD_CARGO",$fila["codigo_tipo_usuario"]);
					$tpl->assign("NOM_CARGO",$fila["nombre_tipo_usuario"]);			
				}
			}
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}
	}
	else{
		//Información del formulario
		$codigoUsuarioSelect = $_POST["usuarioBuscar"];
		$nombre = htmlentities(ucwords(mb_strtolower(trim($_POST["nombre"]),'UTF-8')));		
		$apellido = htmlentities(ucwords(mb_strtolower(trim($_POST["apellido"]),'UTF-8')));				
		$email = htmlentities(mb_strtolower(trim($_POST["email"]),'UTF-8'));						
		$contrasena_usuario = htmlentities(trim($_POST["contrasena"]));
		$codigo_cargo = $_POST["cargo"];
		
		//Verificamos el administrador
		$consulta = "SELECT count(*) as ctdadAdmin FROM datosusuario WHERE codigo_tipo_usuario = 1";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		
		if($fila["ctdadAdmin"] == 1 and $codigo_cargo != 1){
			$_SESSION["MENSAJE_OCUPADO2"] = "SI";
			header("Location: modificarUsuario.php");
		}
		
		if(strcmp($_POST["enviar"],"ELIMINAR") == 0){
			if(strcmp($email,$_SESSION["EMAIL"]) == 0){
				$_SESSION["MENSAJE_OCUPADO"] = "SI";
			}
			else{
				$consulta = "delete from datosUsuario where codigo_usuario = ".$codigoUsuarioSelect;
				$resultado = $conexion_db->query($consulta);
				$_SESSION["MENSAJE_CUMPLE"] = "SI";
			}
			header("Location: modificarUsuario.php");
		}
		else if(strcmp($_POST["enviar"],"ACTUALIZAR") == 0){
			//Obtenemos la informacion del usuario select
			$consulta2 = "select * from datosUsuario where codigo_usuario = ".$codigoUsuarioSelect;
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
			
			//Generamos el nombre de usuario
			$separamos_email = explode('@',$email);
			$nombre_usuario = $separamos_email[0];		
			//Consultamos si usuario existe
			$consulta = "select count(*) as cantidad_usuario from datosUsuario where userName_usuario = '".$nombre_usuario."'";
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			//Si el usuario existe arroja el mensaje, sino guarda el usuario
			if(($fila["cantidad_usuario"] == 1 and strcmp($nombre_usuario,$fila2["userName_usuario"]) != 0) || strcmp($nombre,"Usuario") == 0 
			|| strcmp($apellido,"Usuario") == 0 || strcmp($nombre_usuario,"usuario") == 0){	
				$_SESSION["MENSAJE_NO_CUMPLE2"] = "SI";
				$conexion_db->close();
				header("Location: modificarUsuario.php");			
			}
			else{
				
				if(strcmp($_SESSION["EMAIL"],$fila2["correo_usuario"]) == 0){
					$consulta = "update datosUsuario set codigo_tipo_usuario = ".$codigo_cargo.", nombre_usuario = '".$nombre.
					"', apellido_usuario = '".$apellido."', correo_usuario = '".$email."', userName_usuario = '".$nombre_usuario.
					"', contrasena_usuario = '".$contrasena_usuario."' where codigo_usuario = ".$codigoUsuarioSelect;
					$resultado = $conexion_db->query($consulta);					
					$conexion_db->close();
					header("Location: salir.php");					
				}
				else{
					$consulta = "update datosUsuario set codigo_tipo_usuario = ".$codigo_cargo.", nombre_usuario = '".$nombre.
					"', apellido_usuario = '".$apellido."', correo_usuario = '".$email."', userName_usuario = '".$nombre_usuario.
					"', contrasena_usuario = '".$contrasena_usuario."' where codigo_usuario = ".$codigoUsuarioSelect;
					$resultado = $conexion_db->query($consulta);
					$_SESSION["MENSAJE_CUMPLE2"] = "SI";
					$conexion_db->close();
					header("Location: modificarUsuario.php");
				}				
			}
		}
	}
?>