<?PHP														//-----
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	date_default_timezone_set('America/Santiago');			  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |

	//Funciones en sesiones.php															//-----	
	validaTiempo();
	
	if(!isset($_POST["cargador"])){	
		if(!isset($_GET["id"])){
			//Obtenemos los bimestres
			$consulta = "select * from bimestre where NroPagoBimestre > 0";
			$resultado = $conexion_db->query($consulta);
			//Obtenemos datos del contrato
			$consulta2 = "select * from contrato order by bimestreContrato desc limit 1";
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);	
			//Obtenemos las regiones
			$consulta3 = "select * from regiones";
			$resultado3 = $conexion_db->query($consulta3);			
			
			//Se carga la página
			//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("acta.html");
			/*}
			else{
				$tpl = new TemplatePower("acta_usr.html");
			}*/
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
			
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			//Datos del contrato
			$tpl->assign("SELECT_INICIO","");
			$tpl->assign("VALOR_SELECT_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("REGION1_INICIO","");
			$tpl->assign("VALOR_REGION1_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("REGION2_INICIO","");
			$tpl->assign("VALOR_REGION2_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("REGION3_INICIO","");
			$tpl->assign("VALOR_REGION3_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("REGION4_INICIO","");
			$tpl->assign("VALOR_REGION4_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
			$tpl->assign("SELECT_FINAL","");
			$tpl->assign("VALOR_SELECT_FINAL","");
			$tpl->assign("RESOLUCION",$fila2["resolucionContrato"]);
			$tpl->assign("MONTOVIGENTE",$fila2["montoVigenteContrato"]);
			$tpl->assign("MONTONIVELSERVICIO",$fila2["montoNivelServicioContrato"]);
			$tpl->assign("RESOLUCIONMODIFICADA",$fila2["resolucionModificadaContrato"]);
			$tpl->assign("EMPRESACONSTRUCTORA",$fila2["nombreEmpresaConstructoraContrato"]);
			$tpl->assign("RUTCONSTRUCTORA",$fila2["rutEmpresaConstructoraContrato"]);
			$tpl->assign("INSPECTORFISCAL",$fila2["inspectorFiscalContrato"]);
			$tpl->assign("INICIOLEGAL",$fila2["inicioLegalContrato"]);
			$tpl->assign("RESOLRECEPCION",$fila2["resolucionRecepcionContrato"]);
			$tpl->assign("MEMORANDUMN",$fila2["memorandumNumeroContrato"]);
			$tpl->assign("FECHAMEMORANDUM",$fila2["fechaMemorandumContrato"]);
			$tpl->assign("OTROSPUNTOS",$fila2["otrosPuntosContrato"]);	
			//Mensajes
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
				$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA CORRECTAMENTE");
				unset($_SESSION["MENSAJE_CUMPLE"]);	
			}
			//Fin mensaje
			//cargamos el select bimestres
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
				if($fila["NroPagoBimestre"] == 1000){
					$tpl->newBlock("BIMESTRE");
					$tpl->assign("NRO_BIMESTRE",$fila["NroBimestre"]);
					$tpl->assign("NOMBRE_BIMESTRE","INSPECCI&Oacute;N DE PAGO FINAL");
				} 
				else{
					$tpl->newBlock("BIMESTRE");
					$tpl->assign("NRO_BIMESTRE",$fila["NroBimestre"]);
					$tpl->assign("NOMBRE_BIMESTRE","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila["NroPagoBimestre"]);	
				}				
			}
			//Se carga el select de las regiones
			while($fila3 = $resultado3->fetch_array(MYSQL_ASSOC)){
				$tpl->newBlock("REGION1");
				$tpl->assign("NOMBRE_REGION1",$fila3["nombreRegion"]);
				$tpl->assign("VALOR_NOMBRE_REGION1",$fila3["nombreRegion"]);
				$tpl->newBlock("REGION2");
				$tpl->assign("NOMBRE_REGION2",$fila3["nombreRegion"]);
				$tpl->assign("VALOR_NOMBRE_REGION2",$fila3["nombreRegion"]);
				$tpl->newBlock("REGION3");
				$tpl->assign("NOMBRE_REGION3",$fila3["nombreRegion"]);
				$tpl->assign("VALOR_NOMBRE_REGION3",$fila3["nombreRegion"]);
				$tpl->newBlock("REGION4");
				$tpl->assign("NOMBRE_REGION4",$fila3["nombreRegion"]);
				$tpl->assign("VALOR_NOMBRE_REGION4",$fila3["nombreRegion"]);
			}
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}

		///////// 
		else{			
			//Obtenemos los bimestres
			$consulta = "select * from bimestre where NroPagoBimestre > 0";
			$resultado = $conexion_db->query($consulta);
			//Obtenemos datos del contrato
			$consulta2 = "select * from contrato order by bimestreContrato desc limit 1";
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);	
			//Obtenemos la informacion de la comision para el bimestre
			$consulta3 = "select * from comision where bimestreComision = ".$_GET["id"];
			$resultado3 = $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			//Obtenemos las regiones			
			$consulta4 = "select * from regiones";
			$resultado4 = $conexion_db->query($consulta4);
			//Verificamos si hay o no información
			$consulta5 = "select count(*) as ctdad_comision from comision where bimestreComision = ".$_GET["id"];
			$resultado5 = $conexion_db->query($consulta5);
			$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
			//Se carga la página
			//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("acta.html");
			//}
			//else{
				//$tpl = new TemplatePower("acta_usr.html");
			//}	
			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");
					
			$tpl->prepare();

			//Mensajes
			if(isset($_SESSION["MENSAJE_CUMPLE"]) and strcmp($_SESSION["MENSAJE_CUMPLE"],"SI") == 0){
				$tpl->assign("MENSAJE","INFORMACIÓN INGRESADA CORRECTAMENTE");
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
			$tpl->assign("MEMORANDUMN",$fila2["memorandumNumeroContrato"]);
			$tpl->assign("FECHAMEMORANDUM",$fila2["fechaMemorandumContrato"]);
			$tpl->assign("OTROSPUNTOS",$fila2["otrosPuntosContrato"]);
			//Datos de la comision
			$tpl->assign("DESDE",$fila3["fechaInicioRecepcionComision"]);
			$tpl->assign("HASTA",$fila3["fechaFinalRecepcionComision"]);
			$tpl->assign("INTEGRANTEVIALIDADUNO",$fila3["integranteUnoVialidadComision"]);
			$tpl->assign("INTEGRANTEVIALIDADDOS",$fila3["integranteDosVialidadComision"]);
			$tpl->assign("DEPTOVIALIDADUNO",$fila3["dptoUnoVialidadComision"]);
			$tpl->assign("DEPTOVIALIDADDOS",$fila3["dptoDosVialidadComision"]);
			$tpl->assign("PROFESIONINTEGRANTEVIALIDADUNO",$fila3["profesionUnoIntegrante"]);
			$tpl->assign("PROFESIONINTEGRANTEVIALIDADDOS",$fila3["profesionDosIntegrante"]);
			$tpl->assign("INTEGRANTEASESORIA",$fila3["integranteAsesoriaComision"]);
			$tpl->assign("INTEGRANTECONSTRUCTORA",$fila3["integranteContructoraComision"]);
			$tpl->assign("PROFESIONINTEGRANTEASESORIA",$fila3["profesionTresIntegrante"]);
			$tpl->assign("PROFESIONINTEGRANTECONSTRUCTORA",$fila3["profesionCuatroIntegrante"]);
			$tpl->assign("DEPARTAMENTOASESORIA",$fila3["dptoAsesoriaComision"]);
			$tpl->assign("CARGOEMPRESACONSTRUCTORA",$fila3["cargoContructoraComision"]);
			if($fila5["ctdad_comision"] == 0){
				$tpl->assign("REGION1_INICIO","");
				$tpl->assign("VALOR_REGION1_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION2_INICIO","");
				$tpl->assign("VALOR_REGION2_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION3_INICIO","");
				$tpl->assign("VALOR_REGION3_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION4_INICIO","");
				$tpl->assign("VALOR_REGION4_INICIO","--- SELECCIONAR OPCI&Oacute;N ---");	
				//Se carga el select de las regiones
				while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
					$tpl->newBlock("REGION1");
					$tpl->assign("NOMBRE_REGION1",$fila4["nombreRegion"]);
					$tpl->assign("VALOR_NOMBRE_REGION1",$fila4["nombreRegion"]);
					$tpl->newBlock("REGION2");
					$tpl->assign("NOMBRE_REGION2",$fila4["nombreRegion"]);
					$tpl->assign("VALOR_NOMBRE_REGION2",$fila4["nombreRegion"]);
					$tpl->newBlock("REGION3");
					$tpl->assign("NOMBRE_REGION3",$fila4["nombreRegion"]);
					$tpl->assign("VALOR_NOMBRE_REGION3",$fila4["nombreRegion"]);
					$tpl->newBlock("REGION4");
					$tpl->assign("NOMBRE_REGION4",$fila4["nombreRegion"]);
					$tpl->assign("VALOR_NOMBRE_REGION4",$fila4["nombreRegion"]);
				}				
			}
			else{
				$tpl->assign("REGION1_FINAL","");
				$tpl->assign("VALOR_REGION1_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION2_FINAL","");
				$tpl->assign("VALOR_REGION2_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION3_FINAL","");
				$tpl->assign("VALOR_REGION3_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");
				$tpl->assign("REGION4_FINAL","");
				$tpl->assign("VALOR_REGION4_FINAL","--- SELECCIONAR OPCI&Oacute;N ---");	
				//Se carga el select de las regiones 
				while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){					
					if(strcmp(htmlentities(mb_strtolower($fila4["nombreRegion"])),htmlentities(mb_strtolower($fila3["regionUnoVialidadComision"]))) == 0){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("REGION1_INICIO",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_REGION1_INICIO",$fila4["nombreRegion"]);
					}
					else{
						$tpl->newBlock("REGION1");
						$tpl->assign("NOMBRE_REGION1",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_NOMBRE_REGION1",$fila4["nombreRegion"]);
					}	
					if(strcmp(htmlentities(mb_strtolower($fila4["nombreRegion"])),htmlentities(mb_strtolower($fila3["regionDosVialidadComision"]))) == 0){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("REGION2_INICIO",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_REGION2_INICIO",$fila4["nombreRegion"]);
					}
					else{
						$tpl->newBlock("REGION2");
						$tpl->assign("NOMBRE_REGION2",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_NOMBRE_REGION2",$fila4["nombreRegion"]);
					}						
					if(strcmp(htmlentities(mb_strtolower($fila4["nombreRegion"])),htmlentities(mb_strtolower($fila3["regionAsesoriaComision"]))) == 0){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("REGION3_INICIO",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_REGION3_INICIO",$fila4["nombreRegion"]);
					}
					else{
						$tpl->newBlock("REGION3");
						$tpl->assign("NOMBRE_REGION3",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_NOMBRE_REGION3",$fila4["nombreRegion"]);
					}
					if(strcmp(htmlentities(mb_strtolower($fila4["nombreRegion"])),htmlentities(mb_strtolower($fila3["regionContructoraComision"]))) == 0){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("REGION4_INICIO",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_REGION4_INICIO",$fila4["nombreRegion"]);
					}
					else{
						$tpl->newBlock("REGION4");
						$tpl->assign("NOMBRE_REGION4",$fila4["nombreRegion"]);
						$tpl->assign("VALOR_NOMBRE_REGION4",$fila4["nombreRegion"]);
					}			
				}					
			}
			
			//cargamos el select bimestres
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
				if($fila["NroBimestre"] == $_GET["id"]){
					if($fila["NroBimestre"] == 1000){
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("SELECT_INICIO",$fila["NroBimestre"]);
						$tpl->assign("VALOR_SELECT_INICIO","INSPECCI&Oacute;N DE PAGO FINAL");
					}
					else{
						$tpl->gotoBlock("_ROOT");
						$tpl->assign("SELECT_INICIO",$fila["NroBimestre"]);
						$tpl->assign("VALOR_SELECT_INICIO","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila["NroPagoBimestre"]);	
					}					
				}
				else{
					if($fila["NroBimestre"] == 1000){
						$tpl->newBlock("BIMESTRE");
						$tpl->assign("NRO_BIMESTRE",$fila["NroBimestre"]);
						$tpl->assign("NOMBRE_BIMESTRE","INSPECCI&Oacute;N DE PAGO FINAL");
					}
					else{
						$tpl->newBlock("BIMESTRE");
						$tpl->assign("NRO_BIMESTRE",$fila["NroBimestre"]);
						$tpl->assign("NOMBRE_BIMESTRE","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila["NroPagoBimestre"]);
					}
				}
			}
			//Cargamos las regiones			
			//Se cierra la conexión
			$conexion_db->close();
			$tpl->printToScreen();
		}		
	}
	else{
		//Formulario comision
			//Bimestre
		$nroBimestre = $_POST["id_bimestre"];
			//Fecha recepcion
		$fechaDesde = $_POST["desde"];
		$fechaHasta = $_POST["hasta"];
			//Integrante Vialidad 1
		$integranteVialidadUno = htmlentities(mb_strtolower(trim($_POST["vialidad1"]),'UTF-8'));
		$profesionVialidadUno = htmlentities(mb_strtolower(trim($_POST["profesionvialidad1"]),'UTF-8'));
		$deptoVialidadUno = htmlentities(mb_strtolower(trim($_POST["deptoVialidad1"]),'UTF-8'));
		$regionVialidadUno = htmlentities(mb_strtolower(trim($_POST["region1"]),'UTF-8'));
			//Integrante Vialidad 2
		$integranteVialidadDos = htmlentities(mb_strtolower(trim($_POST["vialidad2"]),'UTF-8'));		
		$profesionVialidadDos = htmlentities(mb_strtolower(trim($_POST["profesionvialidad2"]),'UTF-8'));		
		$deptoVialidadDos = htmlentities(mb_strtolower(trim($_POST["deptoVialidad2"]),'UTF-8'));		
		$regionVialidadDos = htmlentities(mb_strtolower(trim($_POST["region2"]),'UTF-8'));
			//Integrante Asesoria
		$integranteAsesoria = htmlentities(mb_strtolower(trim($_POST["asesoria1"]),'UTF-8'));
		$profesionAsesoria = htmlentities(mb_strtolower(trim($_POST["profesionasesoria1"]),'UTF-8'));
		$departamentoAsesoria = htmlentities(mb_strtolower(trim($_POST["deptoAsesoria1"]),'UTF-8'));
		$regionAsesoria = htmlentities(mb_strtolower(trim($_POST["region3"]),'UTF-8'));
			//Integrante Constructora
		$integranteConstructora = htmlentities(mb_strtolower(trim($_POST["constructora1"]),'UTF-8'));
		$profesionConstructora = htmlentities(mb_strtolower(trim($_POST["profesionconstructora1"]),'UTF-8'));		
		$cargoConstructora = htmlentities(mb_strtolower(trim($_POST["cargoConstructora1"]),'UTF-8'));		
		$regionConstructora = htmlentities(mb_strtolower(trim($_POST["region4"]),'UTF-8'));		
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
		$memorandumNumero = htmlentities(mb_strtolower(trim($_POST["memorandum"]),'UTF-8'));
		$fechaMemorandum = $_POST["fechaMemorandum"];
		$otrosPuntos = htmlentities(mb_strtolower(trim($_POST["otrosPuntos"]),'UTF-8'));
		//Agregamos o actualizamos el comite
		$consulta = "select count(*) as cantidad_bimestre from comision where bimestreComision = ".$nroBimestre;
		$resultado = $conexion_db->query($consulta);
		$fila = $resultado->fetch_array(MYSQL_ASSOC);
		//Insertamos
		if($fila["cantidad_bimestre"] == 0){
			$consulta2 = "insert into comision (idComision, integranteUnoVialidadComision, integranteDosVialidadComision, integranteAsesoriaComision, ".
			"integranteContructoraComision, profesionUnoIntegrante, profesionDosIntegrante, profesionTresIntegrante, profesionCuatroIntegrante, ".			
			"dptoUnoVialidadComision, dptoDosVialidadComision, dptoAsesoriaComision, cargoContructoraComision, regionUnoVialidadComision, ".
			"regionDosVialidadComision, regionAsesoriaComision, regionContructoraComision, bimestreComision, fechaInicioRecepcionComision, ".
			"fechaFinalRecepcionComision) values ('', '".$integranteVialidadUno."', '".$integranteVialidadDos."', '".$integranteAsesoria."', '".
			$integranteConstructora."', '".$profesionVialidadUno."', '".$profesionVialidadDos."', '".$profesionAsesoria."', '".$profesionConstructora."', '".
			$deptoVialidadUno."', '".$deptoVialidadDos."', '".$departamentoAsesoria."', '".$cargoConstructora."', '".$regionVialidadUno."', '".
			$regionVialidadDos."', '".$regionAsesoria."', '".$regionConstructora."', ".$nroBimestre.", '".$fechaDesde."', '".$fechaHasta."')";
			$resultado2 = $conexion_db->query($consulta2);
		}
		//Actualizamos
		else{
			$consulta2 = "update comision set integranteUnoVialidadComision = '".$integranteVialidadUno."', integranteDosVialidadComision = '".
			$integranteVialidadDos."', integranteAsesoriaComision = '".$integranteAsesoria."', integranteContructoraComision = '".$integranteConstructora.
			"', profesionUnoIntegrante = '".$profesionVialidadUno."', profesionDosIntegrante = '".$profesionVialidadDos."', profesionTresIntegrante = '".
			$profesionAsesoria."', profesionCuatroIntegrante = '".$profesionConstructora."', dptoUnoVialidadComision = '".$deptoVialidadUno.
			"', dptoDosVialidadComision = '".$deptoVialidadDos."', dptoAsesoriaComision = '".$departamentoAsesoria."', cargoContructoraComision = '".
			$cargoConstructora."', regionUnoVialidadComision = '".$regionVialidadUno."', regionDosVialidadComision = '".$regionVialidadDos.
			"', regionAsesoriaComision = '".$regionAsesoria."', regionContructoraComision = '".$regionConstructora."', fechaInicioRecepcionComision = '".
			$fechaDesde."', fechaFinalRecepcionComision = '".$fechaHasta."' where bimestreComision = ".$nroBimestre;
			$resultado2 = $conexion_db->query($consulta2);
		}
		
		//Agregamos o actualizamos el contrato
		$consulta3 = "select count(*) as cantidad_contrato from contrato where bimestreContrato = ".$nroBimestre;
		$resultado3 = $conexion_db->query($consulta3);
		$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
		//Insertamos
		if($fila3["cantidad_contrato"] == 0){
			$consulta4 = "insert into contrato (IdContrato, resolucionContrato, montoVigenteContrato, montoNivelServicioContrato, resolucionModificadaContrato, ".
			"nombreEmpresaConstructoraContrato, rutEmpresaConstructoraContrato, inspectorFiscalContrato, inicioLegalContrato, resolucionRecepcionContrato, memorandumNumeroContrato, ".
			"fechaMemorandumContrato, otrosPuntosContrato, bimestreContrato) values ('', '".$resolucion."', '".$montoVigente."', '".$montoNivelServicio."', '".$resolucionModificada.
			"', '".$empresaConstructora."', '".$rutConstructora."', '".$inspectorFiscal."', '".$inicioLegal."', '".$resolucionRecepcion."', '".$memorandumNumero.
			"', '".$fechaMemorandum."', '".$otrosPuntos."', ".$nroBimestre.")";
			$resultado4 = $conexion_db->query($consulta4);
		}
		//Actualizamos
		else{
			$consulta4 = "update contrato set resolucionContrato = '".$resolucion."', montoVigenteContrato = '".$montoVigente."', montoNivelServicioContrato = '".$montoNivelServicio.
			"', resolucionModificadaContrato = '".$resolucionModificada."', nombreEmpresaConstructoraContrato = '".$empresaConstructora."', rutEmpresaConstructoraContrato = '".
			$rutConstructora."', inspectorFiscalContrato = '".$inspectorFiscal."', inicioLegalContrato = '".$inicioLegal."', resolucionRecepcionContrato = '".$resolucionRecepcion.
			"', memorandumNumeroContrato = '".$memorandumNumero."', fechaMemorandumContrato = '".$fechaMemorandum."', otrosPuntosContrato = '".$otrosPuntos.
			"' where bimestreContrato = ".$nroBimestre;
			$resultado4 = $conexion_db->query($consulta4);
		}
		
		//Cerramos la conexión
		$conexion_db->close();		
		
		//Redireccionamos
		$_SESSION["MENSAJE_CUMPLE"] = "SI";
		header("Location: acta.php");
	}
?>	