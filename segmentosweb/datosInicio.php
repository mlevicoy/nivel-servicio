<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															//-----	
	//Iniciar la sesión
	session_start();

	//Ingresa a este if, si es la primera carga, es decir, no se ha enviado el formulario de ingreso
	if(!isset($_POST["cargador1"]) and !isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){		
		//Se carga la página
		$tpl = new TemplatePower("datosInicio.html");
		$tpl->prepare();
		//Verifica si hay error en el ingreso de datos o no		
		if(isset($_SESSION["MENSAJE_NO_CUMPLE"]) and strcmp($_SESSION["MENSAJE_NO_CUMPLE"],"SI") == 0){
			$tpl->assign("MENSAJE","ERROR AL CREAR EL USUARIO - INTERTAR NUEVAMENTE");
			//Eliminar la variable de sesión para que no aparezca cada vez que se carga el index.php
			unset($_SESSION["MENSAJE_NO_CUMPLE"]);
		}
		/*	
		//Buscamos los compomentes
		//FAJA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'FAJA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_FAJA");
			$tpl->assign("valor_codigo_faja",$fila["codigoComponente"]);
		}		
		//SANEAMIENTO
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'SANEAMIENTO'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_SANEAMIENTO");
			$tpl->assign("valor_codigo_saneamiento",$fila["codigoComponente"]);
		}
		//CALZADA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'CALZADA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_CALZADA");
			$tpl->assign("valor_codigo_calzada",$fila["codigoComponente"]);
		}
		//BERMA
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'BERMA'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_BERMA");
			$tpl->assign("valor_codigo_berma",$fila["codigoComponente"]);
		}
		//SENALIZACION
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'SENALIZACION'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_SENALIZACION");
			$tpl->assign("valor_codigo_senalizacion",$fila["codigoComponente"]);
		}
		//DEMARCACION
		$consulta = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'DEMARCACION'";
		$resultado = $conexion_db->query($consulta);
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$tpl->newBlock("CODIGO_DEMARCACION");
			$tpl->assign("valor_codigo_demarcacion",$fila["codigoComponente"]);
		}*/			
		
		//Se muestra la página
		$tpl->printToScreen();	
	}
	else if(isset($_POST["cargador1"]) and !isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){
		//Se obtiene la información del formulario inicio
		$nombre_obra = htmlentities(ucwords(mb_strtolower(trim($_POST["nombreObra"]),'UTF-8')));
		$bimestrePagoUno = $_POST["bimestrePagoUno"];
		$bimestreSorteoActual = $_POST["bimestrePrimerSorteo"];
		/*
		$codigoFaja = $_POST["codigoFaja"];
		$codigoSaneamiento = $_POST["codigoSaneamiento"];
		$codigoCalzada = $_POST["codigoCalzada"];
		$codigoBerma = $_POST["codigoBerma"];
		$codigoSenalizacion = $_POST["codigoSenalizacion"];
		$codigoDemarcacion = $_POST["codigoDemarcacion"];
*/
		$nombrecomp1 = $_POST["componentes1"];
		$nombrecomp2 = $_POST["componentes2"];
		$nombrecomp3 = $_POST["componentes3"];
		$nombrecomp4 = $_POST["componentes4"];
		$nombrecomp5 = $_POST["componentes5"];
		$nombrecomp6 = $_POST["componentes6"];

		$nombreitems1 = $_POST["items1"];
		$nombreitems2 = $_POST["items2"];
		$nombreitems3 = $_POST["items3"];
		$nombreitems4 = $_POST["items4"];
		$nombreitems5 = $_POST["items5"];
		$nombreitems6 = $_POST["items6"];
/*
		//Guardamos los código de los componentes		
		$consulta4 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'FAJA', '".$codigoFaja."')";
		$resultado4 = $conexion_db->query($consulta4);
		
		$consulta5 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'SANEAMIENTO', '".$codigoSaneamiento."')";
		$resultado5 = $conexion_db->query($consulta5);
		
		$consulta6 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'CALZADA', '".$codigoCalzada."')";
		$resultado6 = $conexion_db->query($consulta6);
		
		$consulta7 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'BERMA', '".$codigoBerma."')";
		$resultado7 = $conexion_db->query($consulta7);
		
		$consulta8 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'SENALIZACION', '".$codigoSenalizacion."')";
		$resultado8 = $conexion_db->query($consulta8);
		
		$consulta9 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('', 'DEMARCACION', '".$codigoDemarcacion."')";
		$resultado9 = $conexion_db->query($consulta9);
*/
		if(strcmp($nombrecomp1, "")!=0)
		{
			$consulta4 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('1', '".$nombrecomp1."', '".$nombreitems1."')";
			$resultado4 = $conexion_db->query($consulta4);	
		}

		if(strcmp($nombrecomp2, "")!=0)
		{
			$consulta5 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('2', '".$nombrecomp2."', '".$nombreitems2."')";
			$resultado5 = $conexion_db->query($consulta5);	
		}

		if(strcmp($nombrecomp3, "")!=0)
		{
			$consulta6 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('3', '".$nombrecomp3."', '".$nombreitems3."')";
			$resultado6 = $conexion_db->query($consulta6);	
		}

		if(strcmp($nombrecomp4, "")!=0)
		{
			$consulta7 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('4', '".$nombrecomp4."', '".$nombreitems4."')";
			$resultado7 = $conexion_db->query($consulta7);	
		}

		if(strcmp($nombrecomp5, "")!=0)
		{
			$consulta8 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('5', '".$nombrecomp5."', '".$nombreitems5."')";
			$resultado8 = $conexion_db->query($consulta8);	
		}

		if(strcmp($nombrecomp6, "")!=0)
		{
			$consulta9 = "insert into codigocomponente (idCodigo, nombreComponente, codigoComponente) values ('6', '".$nombrecomp6."', '".$nombreitems6."')";
			$resultado9 = $conexion_db->query($consulta9);	
		}
				
		//Actualizamos el Primero bimestre a pago.		
		for($i=1;$i<$bimestrePagoUno;$i++){
			$consulta = "update bimestre set NroPagoBimestre = 0 where NroBimestre = ".$i;
			$resultado = $conexion_db->query($consulta);
		}
		$j=1;
		for($i=$bimestrePagoUno;$i<=24;$i++){
			$consulta2 = "update bimestre set NroPagoBimestre = ".$j." where NroBimestre = ".$i;
			$resultado2 = $conexion_db->query($consulta2);
			$j++;
		}
		//Guardamos el nombre de faena
		$consulta3 = "insert into nombreFaena (codFaena,nombreFaena) values ('','".$nombre_obra."')";
		$resultado3 = $conexion_db->query($consulta3);
		
		if($bimestreSorteoActual != 1){
			//Se carga la página
			$tpl = new TemplatePower("datosInicio2.html");
			$tpl->prepare();
			$tpl->assign("NRO_SORTEO_ACTUAL",$bimestreSorteoActual);
			$tpl->assign("SORTEO_ACTUAL",$bimestreSorteoActual);
			for($i=1;$i<$bimestreSorteoActual;$i++){
				$tpl->newBlock("FECHA_BIMESTRE");
				$tpl->assign("NRO_BIMESTRE",$i);
			}
			//Se muestra la página
			$conexion_db->close();
			$tpl->printToScreen();	
		}
		else{
			//Se carga la página
			$tpl = new TemplatePower("datosInicio3.html");
			$tpl->prepare();
			//Se muestra la página
			$conexion_db->close();
			$tpl->printToScreen();			
		}
	}
	else if(!isset($_POST["cargador1"]) and isset($_POST["cargador2"]) and !isset($_POST["cargador3"])){
		$fechaInicio = $_POST["fechaInicio"];
		$fechaTermino = $_POST["fechaTermino"];
		$bimestreSorteoActual = $_POST["cargador2"];
		
		for($i=1;$i<$bimestreSorteoActual;$i++){
			$consulta = "update bimestre set fechaInicioBimestre = '".$fechaInicio[$i-1]."' where NroBimestre = ".$i;
			$resultado = $conexion_db->query($consulta);
			$consulta2 = "update bimestre set fechaTerminoBimestre = '".$fechaTermino[$i-1]."' where NroBimestre = ".$i;
			$resultado2 = $conexion_db->query($consulta2);
		}
		
		//Se carga la página
		$tpl = new TemplatePower("datosInicio3.html");
		$tpl->prepare();
		//Se muestra la página
		$conexion_db->close();
		$tpl->printToScreen();	
	}
	else if(!isset($_POST["cargador1"]) and !isset($_POST["cargador2"]) and isset($_POST["cargador3"])){
		//Informacion del formulario usuario
		$nombre = htmlentities(ucwords(mb_strtolower(trim($_POST["nombre"]),'UTF-8')));
		$apellido = htmlentities(ucwords(mb_strtolower(trim($_POST["apellido"]),'UTF-8')));
		$correo = htmlentities(mb_strtolower(trim($_POST["correo"]),'UTF-8'));
		$contrasena = htmlentities(trim($_POST["contrasena"]));
		$tipoUsuario = $_POST["tipoUsuario"];
		//Nombre de usuario
		$separa_correo = explode('@',$correo);
		$usuario = $separa_correo[0];
		//Ingreso de usuario		
		if(strcmp($nombre,"Usuario") != 0 || strcmp($apellido,"Usuario") != 0 || strcmp($usuario,"usuario") != 0){		
			//Como es el primero solo se guarda y se elimina el usuario usuario
			$consulta = "insert into datosUsuario (codigo_usuario, codigo_tipo_usuario, nombre_usuario, apellido_usuario, correo_usuario, userName_usuario, ".
			"contrasena_usuario) values ('',1,'".$nombre."','".$apellido."','".$correo."','".$usuario."','".$contrasena."')";
			$resultado = $conexion_db->query($consulta);
			$consulta2 = "delete from datosUsuario where userName_usuario = 'usuario'";
			$resultado2 = $conexion_db->query($consulta2);
			
			$_SESSION["USUARIO_INICIAL"] = $usuario;			
			$_SESSION["MENSAJE_CUMPLE"] = "SI";		
			header("Location: index.php");
		}
		else{
			$_SESSION["MENSAJE_NO_CUMPLE"] = "SI";
			header("Location: datosInicio.php");
		}		
	}
?>