<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |	
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Iniciar la sesión
	session_unset();				//Borra variables de sesión	
	session_start();	
	
	//Ingresa a este if, si es la primera carga, es decir, no se ha enviado el formulario de ingreso
	if(!isset($_POST["cargador"])){
		//Se carga la página
		$tpl = new TemplatePower("inicio.html");
		$tpl->prepare();
		//Verifica si hay error en el ingreso o no
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","Usuario ".$_SESSION["USUARIO_INICIAL"]." creado correctamente, informar al usuario");
			//Eliminar la variable de sesión para que no aparezca cada vez que se carga el index.php
			unset($_SESSION["MENSAJE_CUMPLE"]);
			unset($_SESSION["USUARIO_INICIAL"]);			
		}
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"])){
			$tpl->assign("MENSAJE","ERROR DE INGRESO - VERIFICAR INFORMACI&Oacute;N");
			//Eliminar la variable de sesión para que no aparezca cada vez que se carga el index.php
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);
		}				
		//Se cierra la conexión
		$conexion_db->close();
		//Se muestra la página
		$tpl->printToScreen();	
	}
	//Cuando se envia la información del formulario de ingreso
	else{
		//Se obtiene la información del formulario
		$nombre_usuario = htmlentities(mb_strtolower(trim($_POST["nombreUsuario"]),'UTF-8'));		
		$contrasena_usuario = htmlentities(trim($_POST["contrasena"]));
		
		//Se realiza la consulta para ver si hay un usuario que cumpla con lo ingresado en el formulario
		$consulta = "select count(*) as cantidad_usuario from datosUsuario where userName_usuario = '".$nombre_usuario."' and BINARY contrasena_usuario = '".$contrasena_usuario."'";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);		
		
		//Verifica si hay algún usuario que cumpla
		if($fila["cantidad_usuario"] == 1){
			
			//Verificamos que es usuario
			if(strcmp($nombre_usuario,"usuario") == 0){
				$conexion_db->close();
				header("Location: datosInicio.php");
			}
			else{			
				//Obtenemos los datos del usuario
				$consulta = "select * from datosUsuario where userName_usuario = '".$nombre_usuario."'";
				$resultado = $conexion_db->query($consulta);
				$fila = $resultado->fetch_array(MYSQL_ASSOC);
				
				$consulta2 = "select * from nombreFaena";
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
								
				//Variables de sesion creadas
				$_SESSION["NOMBRE"] = $fila["nombre_usuario"]." ".$fila["apellido_usuario"];
				$_SESSION["NOMBRE_USUARIO"] = $nombre_usuario;
				$_SESSION["EMAIL"] = $fila["correo_usuario"];
				$_SESSION["NOMBRE_OBRA"] = $fila2["nombreFaena"];
				$_SESSION["CONECTADO"] = "SI";
				$_SESSION["ULTIMO_ACCESO"] = date("Y-n-j H:i:s");
				
				//Cierre de la conexión
				$conexion_db->close();
				if($fila["codigo_tipo_usuario"] == 1){
					$_SESSION["CARGO"] = "Administrador";
					header("Location: administrador.php");
				}
				else if($fila["codigo_tipo_usuario"] == 2){
					$_SESSION["CARGO"] = "Usuario";
					header("Location: usuario.php");
				}
				else if($fila["codigo_tipo_usuario"] == 3){
					$_SESSION["CARGO"] = "Inspector Fiscal";
					header("Location: usuario.php");
				}
			}
		}
		//vuelve al formulario de ingreso
		else{
			$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
			header("Location: index.php");						
		}
	}
?>