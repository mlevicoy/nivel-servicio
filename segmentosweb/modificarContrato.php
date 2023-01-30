<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	if(!isset($_POST["cargador"]))
	{	
		//if(!isset($_GET["id"])){

			//Obtenemos los bimestres
			//$consulta = "select * from bimestre where NroPagoBimestre > 0";

			//$resultado = $conexion_db->query($consulta);


			//Obtenemos datos del contrato
			$consulta2 = "select * from contrato order by bimestreContrato desc limit 1";
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);	
					
			//Se carga la página
			//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("modificarContrato.html");
			/*}
			else{
				$tpl = new TemplatePower("modificarContrato_usr.html");
			}*/
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
			
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			//Datos del contrato
			
			//$tpl->assign("SELECT_FINAL","");
			//$tpl->assign("VALOR_SELECT_FINAL","");			
			$tpl->assign("RESOLUCION",$fila2["resolucionContrato"]);
			$tpl->assign("MONTOVIGENTE",$fila2["montoVigenteContrato"]);
			$tpl->assign("MONTONIVELSERVICIO",$fila2["montoNivelServicioContrato"]);
			$tpl->assign("RESOLUCIONMODIFICADA",$fila2["resolucionModificadaContrato"]);
			$tpl->assign("EMPRESACONSTRUCTORA",$fila2["nombreEmpresaConstructoraContrato"]);
			$tpl->assign("RUTCONSTRUCTORA",$fila2["rutEmpresaConstructoraContrato"]);
			$tpl->assign("INSPECTORFISCAL",$fila2["inspectorFiscalContrato"]);
			$tpl->assign("INICIOLEGAL",$fila2["inicioLegalContrato"]);
			$tpl->assign("RESOLRECEPCION",$fila2["resolucionRecepcionContrato"]);
			$tpl->assign("bimestreContrato",$fila2["bimestreContrato"]);			
			
			//Mensajes
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
				$tpl->assign("MENSAJE","INFORMACIÓN ACTUALIZADA CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);	
			}
			//Fin mensaje
			
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		/*}//
		else{			
			//Obtenemos los bimestres
			$consulta = "select * from bimestre where NroPagoBimestre > 0";
			$resultado = $conexion_db->query($consulta);
			//Obtenemos datos del contrato
			$consulta1 = "select * from contrato order by bimestreContrato desc limit 1";
			$resultado1 = $conexion_db->query($consulta1);
			$fila1 = $resultado1->fetch_array(MYSQL_ASSOC);	
			
			//Verificamos si hay o no información
			$consulta2 = "select count(*) as cantidad_contrato from contrato where bimestreContrato = ".$_GET["id"];
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
			//Se carga la página
			//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
				$tpl = new TemplatePower("modificarContrato.html");
			/*}
			else{
				$tpl = new TemplatePower("modificarContrato_usr.html");
			}*	
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			//Mensajes
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
				$tpl->assign("MENSAJE","INFORMACIÓN ACTUALIZADO2 CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);	
			}			
			//Datos usuario
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			//Datos del contrato
			$tpl->assign("SELECT_FINAL","");
			$tpl->assign("VALOR_SELECT_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");			
			$tpl->assign("RESOLUCION",$fila2["resolucionContrato"]);
			$tpl->assign("MONTOVIGENTE",$fila2["montoVigenteContrato"]);
			$tpl->assign("MONTONIVELSERVICIO",$fila2["montoNivelServicioContrato"]);
			$tpl->assign("RESOLUCIONMODIFICADA",$fila2["resolucionModificadaContrato"]);
			$tpl->assign("EMPRESACONSTRUCTORA",$fila2["nombreEmpresaConstructoraContrato"]);
			$tpl->assign("RUTCONSTRUCTORA",$fila2["rutEmpresaConstructoraContrato"]);
			$tpl->assign("INSPECTORFISCAL",$fila2["inspectorFiscalContrato"]);
			$tpl->assign("INICIOLEGAL",$fila2["inicioLegalContrato"]);
			$tpl->assign("RESOLRECEPCION",$fila2["resolucionRecepcionContrato"]);
			$tpl->assign("datos",$consulta2);
			
			
			
			
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}*/		
	}
	else{

     	//Formulario contrato
		$resolucion = htmlentities(mb_strtolower(trim($_POST["resolucion"]),'UTF-8'));
		$montoVigente = htmlentities(mb_strtolower(trim($_POST["montoCttoVigente"]),'UTF-8'));
		$montoNivelServicio = htmlentities(mb_strtolower(trim($_POST["montoCttoNivelServicio"]),'UTF-8'));
		$resolucionModificada = htmlentities(mb_strtolower(trim($_POST["resolucionModificada"]),'UTF-8'));
		$empresaConstructora = htmlentities(mb_strtolower(trim($_POST["contructora"]),'UTF-8'));
		$rutConstructora = htmlentities(mb_strtolower(trim($_POST["rutEmpresaConstructora"]),'UTF-8'));
		$inspectorFiscal = htmlentities(mb_strtolower(trim($_POST["inspectorFiscal"]),'UTF-8'));
		$inicioLegal = $_POST["inicioLegal"];
		$resolucionRecepcion = htmlentities(mb_strtolower(trim($_POST["resolucionRecepcion"]),'UTF-8'));
		$nroBimestre= htmlentities(mb_strtolower(trim($_POST["bimestre"]),'UTF-8'));
	
		
		//Insertamos
		/*if($fila2["cantidad_contrato"] == 0){
			$consulta2 = "insert into contrato (IdContrato, resolucionContrato, montoVigenteContrato, montoNivelServicioContrato, resolucionModificadaContrato, ".
			"nombreEmpresaConstructoraContrato, rutEmpresaConstructoraContrato, inspectorFiscalContrato, inicioLegalContrato, resolucionRecepcionContrato, bimestreContrato) values ('', '".$resolucion."', '".$montoVigente.
			"', '".$montoNivelServicio."', '".$resolucionModificada.
			"', '".$empresaConstructora."', '".$rutConstructora."', '".$inspectorFiscal."', '".$inicioLegal."', '".$resolucionRecepcion."', ".$nroBimestre.")";
			$resultado2 = $conexion_db->query($consulta2);
		}
		//Actualizamos
		else{*/
			$consulta2 = "update contrato set resolucionContrato = '".$resolucion."', montoVigenteContrato = '".$montoVigente."', montoNivelServicioContrato = '".$montoNivelServicio.
			"', resolucionModificadaContrato = '".$resolucionModificada."', nombreEmpresaConstructoraContrato = '".$empresaConstructora."', rutEmpresaConstructoraContrato = '".
			$rutConstructora."', inspectorFiscalContrato = '".$inspectorFiscal."', inicioLegalContrato = '".$inicioLegal."', resolucionRecepcionContrato = '".$resolucionRecepcion.
			"' where bimestreContrato = ".$nroBimestre;			

			$resultado2 = $conexion_db->query($consulta2);

			//Cerramos la conexión
			$conexion_db->close();	

			$_SESSION["MENSAJE_CUMPLE"] = "SI";
			header("Location: modificarContrato.php");
		}
		
			
		
		//Redireccionamos
		
	//}
?>	