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
		$consulta = "select count(*) as bimestreUno from contrato where bimestreContrato = 1";
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		
		//Se carga la página
		$tpl = new TemplatePower("ingresarContrato.html");
		
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
		
		if($fila["bimestreUno"] == 1 and !isset($_SESSION["MENSAJE_CUMPLE"])){
			$tpl->assign("MENSAJE","LA INFORMACIÓN INICIAL DEL CONTRATO YA ESTA INGRESADA.");
			$tpl->assign("CARGAINICIO","carga();");
		}		
		if(isset($_SESSION["MENSAJE_CUMPLE"]) and $_SESSION["MENSAJE_CUMPLE"] == "SI"){
			$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA CORRECTAMENTE");
			unset($_SESSION["MENSAJE_CUMPLE"]);			
			$tpl->assign("CARGAINICIO","carga();");
		}				
		//Se cierra la conexión
		$conexion_db->close();
		$tpl->printToScreen();
	}
	else{
		//Información del formulario
		$resolucion = htmlentities(mb_strtolower(trim($_POST["resolucion"]),'UTF-8'));		
		$inicioLegal = $_POST["inicioLegal"];		
		$montoCV = htmlentities(mb_strtolower(trim($_POST["montoCV"]),'UTF-8'));		
		$montoNS = htmlentities(mb_strtolower(trim($_POST["montoNS"]),'UTF-8'));		
		$nombreConstructora = htmlentities(mb_strtolower(trim($_POST["nombreEmpresaConstructora"]),'UTF-8'));		
		$RutContructora = htmlentities(mb_strtolower(trim($_POST["rutEmpresaConstructora"]),'UTF-8'));		
		$NombreIF = htmlentities(ucwords(mb_strtolower(trim($_POST["nombreInspectorFiscal"]),'UTF-8')));		
		$bimestre = $_POST["bimestre"];									
		//Almacenamos la informacion		
		$consulta = "insert into contrato (idContrato, resolucionContrato, montoVigenteContrato, montoNivelServicioContrato, resolucionModificadaContrato, ".
		"nombreEmpresaConstructoraContrato, rutEmpresaConstructoraContrato, inspectorFiscalContrato, inicioLegalContrato, resolucionRecepcionContrato, memorandumNumeroContrato, ".
		"fechaMemorandumContrato, otrosPuntosContrato, bimestreContrato) values ('', '".$resolucion."', '".$montoCV."', '".$montoNS."', '', '".$nombreConstructora.
		"', '".$RutContructora."', '".$NombreIF."','".$inicioLegal."', '', '', '', '', ".$bimestre.")";
		$resultado = $conexion_db->query($consulta);
		
		$consulta2 = "update bimestre set fechaInicioBimestre = '".$inicioLegal."' where NroBimestre = 1 and fechaInicioBimestre = '0000-00-00'";
		$resultado2 = $conexion_db->query($consulta2);
		
		$conexion_db->close();
		$_SESSION["MENSAJE_CUMPLE"] = "SI";			
		header("Location: ingresarContrato.php");		
	}
?>