<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |	
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
	
	//Carga Inicial
	if(!isset($_POST["cargador"]) and !isset($_GET["BIMESTRE"]) and !isset($_GET["LIMPIAR_BD"]) and !isset($_GET["LIMPIAR_EX"])){
		//Se carga la página
		$tpl = new TemplatePower("limpiarBD.html");
		$tpl->prepare();
		$tpl->assign("CARGAINICIO","carga();");
		$tpl->printToScreen();
	}
	//Validamos al usuario
	else if(isset($_POST["cargador"]) and !isset($_GET["BIMESTRE"]) and !isset($_GET["LIMPIAR_BD"]) and !isset($_GET["LIMPIAR_EX"])){
		//formulario
		$nombre_usuario = htmlentities(mb_strtolower(trim($_POST["nombreUsuario"]),'UTF-8'));		
		$contrasena_usuario = htmlentities(trim($_POST["contrasenaUsuario"]));
		//Validamos el usuario
		$consulta = "select count(*) as cantidad from datosUsuario where userName_usuario = '".$nombre_usuario."' and contrasena_usuario = '".$contrasena_usuario.
		"' and codigo_tipo_usuario = 1";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		if($fila["cantidad"] == 0){
			//Se carga la página
			$tpl = new TemplatePower("limpiarBD.html");
			$tpl->prepare();
			$tpl->assign("CARGAINICIO","carga();");
			$tpl->assign("MENSAJE","USUARIO Y/O CONTRASE&Ntilde;A INCORRECTA");
			$tpl->printToScreen();
		}
		else{
			//Buscamos bimestres
			$consulta2 = "select * from bimestre where estadoBimestre = 0";
			$resultado2 = $conexion_db->query($consulta2);
			
			//Se carga la página
			$tpl = new TemplatePower("limpiarBD.html");
			$tpl->prepare();
			$tpl->assign("CARGAINICIO","carga2();");
			$tpl->assign("USER_NAME",$nombre_usuario);
			$tpl->assign("USER_PASSWORD",$contrasena_usuario);
			//Se carga el select
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("BIMESTRE_SELECT");
				$tpl->assign("NUMERO_BIMESTRE",$fila2["NroBimestre"]);
				$tpl->assign("NOMBRE_BIMESTRE","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila2["NroPagoBimestre"]);
			}
			
			$tpl->printToScreen();			
		}
	}
	//Liberar bimestre
	else if(!isset($_POST["cargador"]) and isset($_GET["BIMESTRE"]) and !isset($_GET["LIMPIAR_BD"]) and !isset($_GET["LIMPIAR_EX"])){
		//Formulario
		$bimestre = $_GET["BIMESTRE"];
		//actualizar bimestre
		$consulta = "update bimestre set estadoBimestre = 1, fechaInicioBimestre = '0000-00-00', fechaTerminoBimestre = '0000-00-00' where NroBimestre = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos comision
		$consulta = "delete from comision where bimestreComision = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos contrato
		$consulta = "delete from contrato where bimestreContrato = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos incumplimiento
		$consulta = "delete from incumplimiento where bimestreIncumplimiento = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizar inspeccionar
		$consulta = "delete from inspeccionar where bimestreInspeccionar = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos km contratados
		$consulta = "delete from kmcontratados where kmBimestre = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos km descontados
		$consulta = "delete from kmDescontados where kmBimestre = ".$bimestre;
		$resultado = $conexion_db->query($consulta);		
		//actualizamos modificaciones
		$consulta = "delete from modificaciones where bimestreCambio = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos porcentaje
		$consulta = "delete from porcentaje where bimestrePorcentaje = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		//actualizamos recepcion anterior
		$consulta = "update recepcionAnterior set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', calzadaRecepcionAnterior = '0.00', ".
		"bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', demarcacionRecepcionAnterior = '0.00' where bimestreRecepcionAnterior = ".
		$bimestre; 
		$resultado = $conexion_db->query($consulta);	
		//actualizamos recepcion anterior descontada
		$consulta = "update recepcionAnteriorDescontada set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', ".
		"calzadaRecepcionAnterior = '0.00', bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', ".
		"demarcacionRecepcionAnterior = '0.00' where bimestreRecepcionAnterior = ".$bimestre; 
		$resultado = $conexion_db->query($consulta);		
		//actualizamos segmentos sorteados
		$consulta = "delete from segmentosSorteados where bimestreSorteado = ".$bimestre;
		$resultado = $conexion_db->query($consulta);		
		//actualizamos recxcompdescontada
		$consulta = "delete from recxcompdescontada where bimestre = ".$bimestre;
		$resultado = $conexion_db->query($consulta);		
		//Se carga la página
		$tpl = new TemplatePower("limpiarBD.html");
		$tpl->prepare();
		$tpl->assign("CARGAINICIO","carga();");
		$tpl->assign("MENSAJE","BIMESTRE N&deg; ".$bimestre." LIBERADO CORRECTAMENTE");
		$tpl->printToScreen();
	}
	//Limpiamos base de datos
	else if(!isset($_POST["cargador"]) and !isset($_GET["BIMESTRE"]) and isset($_GET["LIMPIAR_BD"]) and !isset($_GET["LIMPIAR_EX"])){
		//Limpiamos bimestre
		$consulta = "update bimestre set estadoBimestre = 1, fechaInicioBimestre = '0000-00-00', fechaTerminoBimestre = '0000-00-00'";
		$resultado = $conexion_db->query($consulta);
		$i=1;
		$consulta = "select * from bimestre";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$consulta2 = "update bimestre set NroPagoBimestre = ".$i." where NroBimestre = ".$i;
			$resultado2 = $conexion_db->query($consulta2);		
			$i++;
		}	
		//Limpiamos codigoComponente
		$consulta = "truncate table codigocomponente";
		$resultado = $conexion_db->query($consulta);				
		//Limpiamos comision
		$consulta = "truncate table comision";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos contrato
		$consulta = "truncate table contrato";
		$resultado = $conexion_db->query($consulta);
		$consulta = "truncate table correccion";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos datosUsuario
		$consulta = "truncate table datosUsuario";
		$resultado = $conexion_db->query($consulta);
		$consulta = "insert into datosUsuario (codigo_usuario, codigo_tipo_usuario, nombre_usuario, apellido_usuario, correo_usuario, userName_usuario, contrasena_usuario) values ".
		"('', 1, 'usuario', 'usuario', 'usuario@usuario.us', 'usuario', 'usuario')";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos desafeccion real
		$consulta = "truncate table desafeccionReal";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos designacion
		$consulta = "truncate table designacion";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos incumplimiento
		$consulta = "truncate table incumplimiento";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos inspeccionar
		$consulta = "truncate table inspeccionar";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos kmContratados
		$consulta = "truncate table kmContratados";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos kmDescontados
		$consulta = "truncate table kmDescontados";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos modificaciones
		$consulta = "truncate table modificaciones";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos nombre Faena
		$consulta = "truncate table nombreFaena";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos obra
		$consulta = "truncate table obra";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos porcentaje
		$consulta = "truncate table porcentaje";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos recepcion anterior
		$consulta = "update recepcionAnterior set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', calzadaRecepcionAnterior = '0.00', ".
		"bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', demarcacionRecepcionAnterior = '0.00'";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos recepcion anterior descontada
		$consulta = "update recepcionAnteriorDescontada set fajaRecepcionAnterior = '0.00', saneamientoRecepcionAnterior = '0.00', ".
		"calzadaRecepcionAnterior = '0.00', bermasRecepcionAnterior = '0.00', senalizacionRecepcionAnterior = '0.00', ".
		"demarcacionRecepcionAnterior = '0.00'";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos recxcompdescontada
		$consulta = "truncate table recxcompdescontada";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos red caminera
		$consulta = "truncate table redCaminera";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos segmentos
		$consulta = "truncate table segmentos";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos segmentos sorteados
		$consulta = "truncate table segmentosSorteados";
		$resultado = $conexion_db->query($consulta);
		//Limpiamos subSegmentos
		$consulta = "truncate table subSegmentos";
		$resultado = $conexion_db->query($consulta);				
		//Se carga la página
		$tpl = new TemplatePower("limpiarBD.html");
		$tpl->prepare();
		$tpl->assign("CARGAINICIO","carga();");
		$tpl->assign("MENSAJE","SE HA LIMPIADO CORRECTAMENTE LA BASE DE DATOS");
		$tpl->printToScreen();
	}	
	//Limpiar Exclusiones
	else if(!isset($_POST["cargador"]) and !isset($_GET["BIMESTRE"]) and !isset($_GET["LIMPIAR_BD"]) and isset($_GET["LIMPIAR_EX"])){
		//Actualizamos la tabla designacion		
		$consulta = "update designacion set fajaDesignacion = '', saneamientoDesignacion = '', calzadaDesignacion = '', bermasDesignacion = '', ".
		"senalizacionDesignacion = '', demarcacionDesignacion = ''";
		$resultado = $conexion_db->query($consulta);		
		//Actualizamos la tabla subSegmentos		
		$consulta = "update subSegmentos set estadoSubSegmentos = 1";
		$resultado = $conexion_db->query($consulta);		
		//Actualizamos segmentos
		$consulta = "update segmentos set estadoSegmento = 1";
		$resultado = $conexion_db->query($consulta);		
		//Eliminamos información de la desafeccion real
		$consulta = "truncate table desafeccionReal";
		$resultado = $conexion_db->query($consulta);		
		//Se carga la página
		$tpl = new TemplatePower("limpiarBD.html");
		$tpl->prepare();
		$tpl->assign("CARGAINICIO","carga();");
		$tpl->assign("MENSAJE","SE HA ELIMINADO CORRECTAMENTE LAS EXCLUSIONES");
		$tpl->printToScreen();
	}
?>