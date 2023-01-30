<?php
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("fpdf17/fpdf.php");						  //  |	
	require_once("sesiones.php");							  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO DE LA 
	date_default_timezone_set('America/Santiago');			  //  |-- PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															 //   |
	//Funciones en sesiones.php	
	validaTiempo();

	class PDF extends FPDF{
		var $widths;
		var $aligns;	
		public $hojaNumero=0;
		public $direccion_vialidad;
		public $fono_vialidad;
		public $web_vialidad;
		public $mail_vialidad;
		public $ciudad_vialidad;
		
		function Header(){
			$this->Image('imagenes/Logo Bogado/vialidad.jpg',9.6,1,3);								
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);			
			$this->Line(3,31.9,19,31.9);			
			$this->Cell(0,10,strtoupper(utf8_decode(html_entity_decode($this->direccion_vialidad).', '.html_entity_decode($this->ciudad_vialidad).
			' , Chile - FONO: '.html_entity_decode($this->fono_vialidad).' - EMAIL: '.html_entity_decode($this->mail_vialidad).
			' / '.html_entity_decode($this->web_vialidad))),0,0,'C',false);		
		}
	}

	//Buscamos bimestre
	if(!isset($_POST["cargador"]) and !isset($_POST["buscador"])){
		//$consulta = "select distinct bimestreIncumplimiento from incumplimiento";
		$consulta = "select distinct nroPagoBimestre, NroBimestre from bimestre t1 inner join incumplimiento t2 where t1.NroBimestre = t2.bimestreIncumplimiento";
		$resultado = $conexion_db->query($consulta);			
				
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
			$tpl = new TemplatePower("elegirBimestre.html");	
		/*}
		else{
			$tpl = new TemplatePower("elegirBimestre_usr.html");	
		}*/

		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");

		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
		$tpl->assign("REDIRECCIONAR","avisoIncumplimiento.php");
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			if($fila["nroPagoBimestre"] == 1000){
				$tpl->newBlock("NUMERO_BIMESTRE");
				$tpl->assign("numBimestre",$fila["NroBimestre"]);
				$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");	
			}
			else{
				$tpl->newBlock("NUMERO_BIMESTRE");
				$tpl->assign("numBimestre",$fila["NroBimestre"]);
				$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila["nroPagoBimestre"]);	
			}
		}
		$conexion_db->close();
		$tpl->printToScreen();					
	}
	else{
		if(isset($_POST["cargador"]) and !isset($_POST["buscador"])){
			$bimestre = $_POST["numeroBimestre"];
			$_SESSION["NUMERO_BIMESTRE"] = $_POST["numeroBimestre"];

			//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){		
				$tpl = new TemplatePower("avisoIncumplimiento.html");
			/*}
			else{
				$tpl = new TemplatePower("avisoIncumplimiento_usr.html");
			}*/

			$tpl->assignInclude("header", "header.html");
			$tpl->assignInclude("menu", "menu.html");

			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
			$x=0;
			//Calculo Faja
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoFajaNC = array();
			$tramoFajaNC = array();
			$comentarioFajaNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'FAJA'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoFajaNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoFajaNC[$j] = $i;
						$comentarioFajaNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoFajaNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoFajaNC[$i].
				" and tramoSubSegmentos = ".$tramoFajaNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Faja Vial");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioFajaNC[$i]);
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			//Calculo Saneamiento
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoSaneamientoNC = array();
			$tramoSaneamientoNC = array();
			$comentarioSaneamientoNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'SANEAMIENTO'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoSaneamientoNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoSaneamientoNC[$j] = $i;
						$comentarioSaneamientoNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoSaneamientoNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoSaneamientoNC[$i]." and tramoSubSegmentos = ".$tramoSaneamientoNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Saneamiento");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioSaneamientoNC[$i]);	
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			//Calculo Calzada
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoCalzadaNC = array();
			$tramoCalzadaNC = array();
			$comentarioCalzadaNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'CALZADA'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoCalzadaNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoCalzadaNC[$j] = $i;
						$comentarioCalzadaNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoCalzadaNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoCalzadaNC[$i]." and tramoSubSegmentos = ".$tramoCalzadaNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Calzada");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioCalzadaNC[$i]);
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			//Calculo Berma
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoBermaNC = array();
			$tramoBermaNC = array();
			$comentarioBermaNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'BERMA'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoBermaNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoBermaNC[$j] = $i;
						$comentarioBermaNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoBermaNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoBermaNC[$i]." and tramoSubSegmentos = ".$tramoBermaNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Berma");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioBermaNC[$i]);
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			//Calculo Señalizacion
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoSenalizacionNC = array();
			$tramoSenalizacionNC = array();
			$comentarioSenalizacionNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'SENALIZACION'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoSenalizacionNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoSenalizacionNC[$j] = $i;
						$comentarioSenalizacionNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoSenalizacionNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoSenalizacionNC[$i]." and tramoSubSegmentos = ".$tramoSenalizacionNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Se&ntilde;alizaci&oacute;n");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioSenalizacionNC[$i]);
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			//Calculo Demarcación
			//Variables
			$i=1;
			$j=0;
			$j=0;
			$segmentoDemarcacionNC = array();
			$tramoDemarcacionNC = array();
			$comentarioDemarcacionNC = array();
			//Consulta	
			$consulta = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and componenteIncumplimiento = 'DEMARCACION'";
			$resultado = $conexion_db->query($consulta);
			while($fila = $resultado->fetch_array(MYSQL_ASSOC)){		
				for($i=1;$i<16;$i++){
					if(strcmp($fila["t".$i."Incumplimiento"],"NC") == 0){
						$segmentoDemarcacionNC[$j] = $fila["segmentoIncumplimiento"];
						$tramoDemarcacionNC[$j] = $i;
						$comentarioDemarcacionNC[$j] = $fila["t".$i."Comentario"];
						$j++;
					}
				}
			}
			for($i=0;$i<count($segmentoDemarcacionNC);$i++){
				$consulta2 = "select rolSubSegmentos, kmInicioSubSegmento, kmFinalSubSegmentos from subsegmentos where segmentoSubSegmentos = ".$segmentoDemarcacionNC[$i]." and tramoSubSegmentos = ".$tramoDemarcacionNC[$i];
				$resultado2 = $conexion_db->query($consulta2);
				$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
				$tpl->newBlock("INCUMPLIMIENTOS");
				$tpl->assign("COMPONENTE","Demarcaci&oacute;n");
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("ROLCAMINO",$fila2["rolSubSegmentos"]);
				$tpl->assign("KMDESDE",$fila2["kmInicioSubSegmento"]);
				$tpl->assign("KMHASTA",$fila2["kmFinalSubSegmentos"]);
				$tpl->assign("DEFECTO",$comentarioDemarcacionNC[$i]);	
				$tpl->assign("indice",$x);
				for($k=1;$k<366;$k++){
					$tpl->newBlock("NUMERO_DIAS");
					$tpl->assign("numDias",$k);		
				}
				for($k=1;$k<1441;$k++){
					$tpl->newBlock("NUMERO_HORAS");
					$tpl->assign("numHoras",$k);		
				}
				$x++;
			}

			$tpl->gotoBlock("_ROOT");
			//Resto datos
			$consulta = "select fechaTerminoBimestre from bimestre where NroBimestre = ".$bimestre;
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			$fechaFinal = explode("-", $fila["fechaTerminoBimestre"]);
			$tpl->assign("DIAFINALRECEPCION",$fechaFinal[2]." de ".diaPalabra($fechaFinal[1])." de ".$fechaFinal[0]);
			$tpl->assign("MESFINALRECEPCION",diaPalabra($fechaFinal[1])." de ".$fechaFinal[0]);

			$conexion_db->close();
			$tpl->printToScreen();

		}
		else if(!isset($_POST["cargador"]) and isset($_POST["buscador"])){	
			$nroAviso = $_POST["nroAviso"];
			$componente = $_POST["componente"];	//Array
			$rolCamino = $_POST["rolCamino"];	//Array
			$kmDesde = $_POST["kmDesde"];	//Array
			$kmHasta = $_POST["kmHasta"];	//Array
			$faja = $_POST["faja"];	//Array
			$defecto = $_POST["defecto"];	//Array
			$dias = $_POST["dias"];	//Array
			$horas = $_POST["horas"];	//Array
			$hasta = $_POST["hasta"];	//Array
			$periodo = $_POST["periodo"];	//Array
			$fechaComunicacion = $_POST["fechaComunicacion"];
			$valorUTM = $_POST["valorUTM"];
			$multaXAviso = $_POST["multaXAviso"];
			$totalMulta = $_POST["totalMulta"];
			
			//**************************************** GENERAMOS EL PDF ***********************************
						
			//Información de la obra
			$consulta = "select * from obra";
			$resultado = $conexion_db->query($consulta);
			$fila = $resultado->fetch_array(MYSQL_ASSOC);
			
			//Información del contrato
			$consulta2 = "select * from contrato where bimestreContrato = ".$_SESSION["NUMERO_BIMESTRE"];
			$resultado2 = $conexion_db->query($consulta2);
			$fila2 = $resultado2->fetch_array(MYSQL_ASSOC);
			
			//Información del bimestre
			$consulta3 = "select * from bimestre where NroBimestre = ".$_SESSION["NUMERO_BIMESTRE"];
			$resultado3 = $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			
			//Informe de comisión
			$consulta4 = "select * from comision where bimestreComision = ".$_SESSION["NUMERO_BIMESTRE"];
			$resultado4 = $conexion_db->query($consulta4);
			$fila4 = $resultado4->fetch_array(MYSQL_ASSOC);
						
			//Datos cabecera
			//Creación de la hoja
			$pdf = new PDF('P','cm',array(22,33));
						
			$pdf->direccion_vialidad = $fila["direccionMandanteObra"];
			$pdf->fono_vialidad = $fila["fonoMandanteObra"];
			$pdf->web_vialidad = $fila["webMandanteObra"];
			$pdf->mail_vialidad = $fila["mailMandanteObra"];
			$pdf->ciudad_vialidad = $fila["ciudadOficinaObra"];

			$pdf->AddPage('P',array(22,33));										
			$pdf->SetFont('Arial','B',9);		
			
			//Indice
			$y = 4;
						
			//Titulo
			$pdf->SetXY(0,$y);
			$pdf->MultiCell(22,.5,strtoupper(utf8_decode('AVISO DE INCUMPLIMIENTO')),0,'C',false);	
			$y = $y + .5;	//4.5
			$pdf->SetXY(0,$y);
			$pdf->MultiCell(22,.5,strtoupper(utf8_decode('N° '.$nroAviso)),0,'C',false);	
			
			//Contrato
			$y = $y + 1.5;	//6
			$pdf->SetXY(.5,$y);
			$pdf->MultiCell(4,.5,strtoupper(utf8_decode('CONTRATO')),0,'J',false);	
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(4.5,$y);
			$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode(': "'.html_entity_decode($fila["nombreCompletoObra"]).'"')),0,'J',false);	
			
			//Tipo de inspección
			$y = $y + 1.5; //7.5
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(.5,$y);
			$pdf->MultiCell(4,.5,strtoupper(utf8_decode('TIPO DE INSPECCIÓN')),0,'J',false);
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(4.5,$y);
			$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode(': INSPECCIÓN DE PAGO N° '.$fila3["NroPagoBimestre"])),0,'J',false);	
			
			//Fecha de inspección
			$y = $y + 1;	//8.5
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(.5,$y);
			$pdf->MultiCell(4,.5,strtoupper(utf8_decode('FECHA DE INSPECCIÓN')),0,'J',false);	
						
			$fechaInicio = explode("-", $fila4["fechaInicioRecepcionComision"]);
			$fechaTermino = explode("-", $fila4["fechaFinalRecepcionComision"]);
			
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(4.5,$y);
			if($fechaInicio[0] == $fechaTermino[0] && $fechaInicio[1] == $fechaTermino[1] && $fechaInicio[2] == $fechaTermino[2]){
				$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode(': '.$fechaInicio[2].' de '.mesTexto($fechaInicio[1]).' de '.$fechaInicio[0])),0,'J',false);			
			}
			else{
				$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode(': '.$fechaInicio[2].' de '.mesTexto($fechaInicio[1]).' y '.$fechaTermino[2].' de '.mesTexto($fechaTermino[1]).' de '.$fechaTermino[0])),0,'J',false);	
			}
			//Calculamos cantidad componentes
			
			$aux_faja = 0;
			$aux_saneamiento = 0;
			$aux_calzada = 0;
			$aux_berma = 0;
			$aux_senalizacion = 0;
			$aux_demarcacion = 0;
			
			for($i=0;$i<count($componente);$i++){
				//Faja
				if(strcmp($componente[$i],"Faja Vial")==0){ $aux_faja++; }	
				//Saneamiento
				else if(strcmp($componente[$i],"Saneamiento")==0){ $aux_saneamiento++; }	
				//Calzada
				else if(strcmp($componente[$i],"Calzada")==0){ $aux_calzada++; }	
				//Berma
				else if(strcmp($componente[$i],"Berma")==0){ $aux_berma++; }	
				//Senalizacion
				else if(strcmp($componente[$i],"Señalización")==0){ $aux_senalizacion++; }	
				//Demarcacion	
				else if(strcmp($componente[$i],"Demarcación")==0){ $aux_demarcacion++; }	
			}
								
			//Tabla
			$y = $y + 1.5;	//10
			$pdf->SetFont('Arial','B',6);			 
			$pdf->SetXY(0.5,$y);			
			$pdf->MultiCell(1.8,1.2,strtoupper(utf8_decode('COMPONENTE')),'LTB','C',false);
			$pdf->SetXY(2.3,$y);
			$pdf->MultiCell(1.3,1.2,strtoupper(utf8_decode('CAMINO')),'LTB','C',false);
			$pdf->SetXY(3.6,$y);
			$pdf->MultiCell(4.8,.7,strtoupper(utf8_decode('UBICACIÓN')),'LTB','C',false);
			$pdf->SetXY(8.4,$y);
			$pdf->MultiCell(4.5,1.2,strtoupper(utf8_decode('DEFECTOS Y CARACTERISTICAS')),'LTB','C',false);
			$pdf->SetXY(12.9,$y);
			$pdf->MultiCell(4,.4,strtoupper(utf8_decode('TIEMPO DE RESPUESTA MÁXIMO (TRM) PARA CUMPLIR CON NS')),'LTB','C',false);
			$pdf->SetXY(16.9,$y);
			$pdf->MultiCell(1.5,.6,strtoupper(utf8_decode('FECHA CUMPLE NS')),'LTB','C',false);
			$pdf->SetXY(18.4,$y);
			$pdf->MultiCell(3.1,.7,strtoupper(utf8_decode('MULTA')),'LTBR','C',false);
			$y = $y + .7; //10.7
			$pdf->SetXY(3.6,$y);
			$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('DESDE KM')),'LB','C',false);
			$pdf->SetXY(5.2,$y);
			$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('HASTA KM')),'LB','C',false);
			$pdf->SetXY(6.8,$y);			
			$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('FAJA')),'LB','C',false);						
			$pdf->SetXY(18.4,$y);
			$pdf->MultiCell(1.55,.5,strtoupper(utf8_decode('PERIODO')),'LB','C',false);
			$pdf->SetXY(19.94,$y);
			$pdf->MultiCell(1.55,.5,strtoupper(utf8_decode('VALOR')),'LBR','C',false);
			$y = $y + .1;	//10.8
			$pdf->SetXY(12.9,$y);
			$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('DIA')),'LB','C',false);
			$pdf->SetXY(14.2,$y);
			$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('HORA')),'LB','C',false);
			$pdf->SetXY(15.53,$y);
			$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('HASTA')),'LB','C',false);			
						
			//Contenido de la tabla
			$pdf->SetFont('Arial','',6);
			
			$y = $y + .4;	//11.2
			for($i=0;$i<count($rolCamino);$i++){
				/*Verificar tamaño hoja*/
				if($y+5.5 > 27){				
					$pdf->AddPage('P',array(22,33));															
					//Tabla
					$y = 5;
					$pdf->SetFont('Arial','B',6);			 
					$pdf->SetXY(0.5,$y);			
					$pdf->MultiCell(1.8,1.2,strtoupper(utf8_decode('COMPONENTE')),'LTB','C',false);
					$pdf->SetXY(2.3,$y);
					$pdf->MultiCell(1.3,1.2,strtoupper(utf8_decode('CAMINO')),'LTB','C',false);
					$pdf->SetXY(3.6,$y);
					$pdf->MultiCell(4.8,.7,strtoupper(utf8_decode('UBICACIÓN')),'LTB','C',false);
					$pdf->SetXY(8.4,$y);
					$pdf->MultiCell(4.5,1.2,strtoupper(utf8_decode('DEFECTOS Y CARACTERISTICAS')),'LTB','C',false);
					$pdf->SetXY(12.9,$y);
					$pdf->MultiCell(4,.4,strtoupper(utf8_decode('TIEMPO DE RESPUESTA MÁXIMO (TRM) PARA CUMPLIR CON NS')),'LTB','C',false);
					$pdf->SetXY(16.9,$y);
					$pdf->MultiCell(1.5,.6,strtoupper(utf8_decode('FECHA CUMPLE NS')),'LTB','C',false);
					$pdf->SetXY(18.4,$y);
					$pdf->MultiCell(3.1,.7,strtoupper(utf8_decode('MULTA')),'LTBR','C',false);
					$y = $y + .7; //10.7
					$pdf->SetXY(3.6,$y);
					$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('DESDE KM')),'LB','C',false);
					$pdf->SetXY(5.2,$y);
					$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('HASTA KM')),'LB','C',false);
					$pdf->SetXY(6.8,$y);			
					$pdf->MultiCell(1.6,.5,strtoupper(utf8_decode('FAJA')),'LB','C',false);						
					$pdf->SetXY(18.4,$y);
					$pdf->MultiCell(1.55,.5,strtoupper(utf8_decode('PERIODO')),'LB','C',false);
					$pdf->SetXY(19.94,$y);
					$pdf->MultiCell(1.55,.5,strtoupper(utf8_decode('VALOR')),'LBR','C',false);
					$y = $y + .1;	//10.8
					$pdf->SetXY(12.9,$y);
					$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('DIA')),'LB','C',false);
					$pdf->SetXY(14.2,$y);
					$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('HORA')),'LB','C',false);
					$pdf->SetXY(15.53,$y);
					$pdf->MultiCell(1.33,.4,strtoupper(utf8_decode('HASTA')),'LB','C',false);			

					//Contenido de la tabla
					$pdf->SetFont('Arial','',6);
					$y = $y + .4;	
				}
				/*Fin Verificar tamaño hoja*/				
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(1.8,.6,strtoupper(utf8_decode($componente[$i])),'LB','C',false);	
				$pdf->SetXY(2.3,$y);
				$pdf->MultiCell(1.3,.6,strtoupper(utf8_decode($rolCamino[$i])),'LB','C',false);	
				$pdf->SetXY(3.6,$y);
				$pdf->MultiCell(1.6,.6,strtoupper(utf8_decode($kmDesde[$i])),'LB','C',false);
				$pdf->SetXY(5.2,$y);
				$pdf->MultiCell(1.6,.6,strtoupper(utf8_decode($kmHasta[$i])),'LB','C',false);
				$pdf->SetXY(6.8,$y);
				$pdf->MultiCell(1.6,.6,strtoupper(utf8_decode($faja[$i])),'LB','C',false);
				$pdf->SetXY(8.4,$y);
				$pdf->MultiCell(4.5,.6,strtoupper(utf8_decode($defecto[$i])),'LB','L',false);
				$pdf->SetXY(12.9,$y);
				$pdf->MultiCell(1.33,.6,strtoupper(utf8_decode($dias[$i])),'LB','C',false);
				$pdf->SetXY(14.2,$y);
				$pdf->MultiCell(1.33,.6,strtoupper(utf8_decode($horas[$i])),'LB','C',false);
				$pdf->SetXY(15.53,$y);
				$pdf->MultiCell(1.33,.6,strtoupper(utf8_decode($hasta[$i])),'LB','C',false);
				$pdf->SetXY(16.9,$y);
				$pdf->MultiCell(1.5,.6,strtoupper(utf8_decode(' ')),'LB','C',false);
				$pdf->SetXY(18.4,$y);
				$pdf->MultiCell(1.55,.6,strtoupper(utf8_decode($periodo[$i])),'LB','C',false);
				$pdf->SetXY(19.94,$y);
				$pdf->MultiCell(1.55,.6,strtoupper(utf8_decode('UTM')),'LBR','C',false);				
				$y = $y+.6;
			}
			
			//Total al
			$y = $y+.3;
			$pdf->SetXY(12.5,$y);
			$pdf->MultiCell(4,.5,strtoupper(utf8_decode('TOTAL AL '.$fechaTermino[2].' DE '.mesTexto($fechaTermino[1]).' DE '.$fechaTermino[0])),0,'L',false);
			$pdf->SetXY(19,$y);
			$pdf->MultiCell(1,.5,strtoupper(utf8_decode(':')),0,'C',false);
			$pdf->SetXY(20.1,$y);
			$pdf->MultiCell(1.2,.5,strtoupper(utf8_decode('$  0 UTM')),0,'C',false);
			
			//Valor UTM
			$y = $y+.5;
			$pdf->SetXY(12.5,$y);
			$pdf->MultiCell(4,.5,strtoupper(utf8_decode('VALOR UTM '.mesTexto($fechaTermino[1]).' DE '.$fechaTermino[0])),0,'L',false);
			$pdf->SetXY(19,$y);
			$pdf->MultiCell(1,.5,strtoupper(utf8_decode(':')),0,'C',false);
			$pdf->SetXY(20.1,$y);
			$pdf->MultiCell(1.2,.5,strtoupper(utf8_decode('$  '.number_format($valorUTM,0,'','.'))),0,'C',false);
			
			//Multa x aviso
			$y = $y+.5;
			$pdf->SetXY(12.5,$y);
			$pdf->MultiCell(7,.5,strtoupper(utf8_decode('MULTA POR AVISO DE INCUMPLIMIENTO (3 UTM POR AVISO)')),0,'L',false);
			$pdf->SetXY(19,$y);
			$pdf->MultiCell(1,.5,strtoupper(utf8_decode(':')),0,'C',false);
			$pdf->SetXY(20.1,$y);
			$pdf->MultiCell(1.2,.5,strtoupper(utf8_decode('$  '.number_format($totalMulta,0,'','.'))),0,'C',false);
			//Multa x aviso
			$y = $y+.5;
			$pdf->SetXY(12.5,$y);
			$pdf->MultiCell(7,.5,strtoupper(utf8_decode('TOTAL MULTA')),0,'L',false);
			$pdf->SetXY(19,$y);
			$pdf->MultiCell(1,.5,strtoupper(utf8_decode(':')),0,'C',false);
			$pdf->SetXY(20.1,$y);
			$pdf->MultiCell(1.2,.5,strtoupper(utf8_decode('$  '.number_format($totalMulta,0,'','.'))),0,'C',false);
			
			$y = $y+1;
			$pdf->SetXY(.5,$y);
			$pdf->MultiCell(21.5,.5,strtoupper(utf8_decode('COMUNICADO AL CONTRATISTA :')),0,'L',false);
			$y = $y+.7;
			$pdf->SetXY(3.05,$y);
			
			$fechaComunicacion_arr = explode('T',$fechaComunicacion);
			$fechaComunicacion2_arr = explode('-',$fechaComunicacion_arr[0]);		
			
			$pdf->MultiCell(18.95,.5,strtoupper(utf8_decode('FECHA   : '.$fechaComunicacion2_arr[2]." de ".mesTexto($fechaComunicacion2_arr[1])." de ".$fechaComunicacion2_arr[0])),0,'L',false);
			$y = $y+.7;
			$pdf->SetXY(3.05,$y);
			$pdf->MultiCell(18.95,.5,strtoupper(utf8_decode('HORA     :')),0,'L',false);
			$y = $y + .4;
			$pdf->Line(4.1,$y,8,$y);
			$y = $y + .3;
			$pdf->SetXY(3.05,$y);
			$pdf->MultiCell(18.95,.5,strtoupper(utf8_decode('MEDIO    :')),0,'L',false);
			$y = $y + .4;
			$pdf->Line(4.1,$y,8,$y);
			
			$pdf->SetFont('Arial','',9);
			
			$pdf->Line(6.5,28.8,15.5,28.8);	//Inspector	
			$pdf->SetXY(0,29);
			$pdf->MultiCell(22,0.4,strtoupper(utf8_decode(html_entity_decode($fila2["inspectorFiscalContrato"]))),0,'C');
			$pdf->SetXY(0,29.5);
			$pdf->MultiCell(22,0.4,utf8_decode('INSPECTOR FISCAL'),0,'C');
			$pdf->SetXY(0,30);
			$pdf->MultiCell(22,0.4,strtoupper(utf8_decode(html_entity_decode($fila4["regionDosVialidadComision"]))),0,'C');			
			
			$pdf->Output('respaldoInformes/Aviso_Incumplimiento_N'.$nroAviso.'.pdf','F');
			$pdf->Output('Aviso_Incumplimiento_N'.$nroAviso.'.pdf','D');
			//$pdf->Output();			
		}
	}
	function mesTexto($mesNumero){
		switch($mesNumero){
			case 1: 
				return "ENERO";
				break;
			case 2: 
				return "FEBRERO";
				break;
			case 3: 
				return "MARZO";
				break;
			case 4: 
				return "ABRIL";
				break;
			case 5: 
				return "MAYO";
				break;
			case 6: 
				return "JUNIO";
				break;
			case 7: 
				return "JULIO";
				break;
			case 8: 
				return "AGUSTO";
				break;
			case 9: 
				return "SEPTIEMBRE";
				break;
			case 10: 
				return "OCTUBRE";
				break;
			case 11: 
				return "NOVIEMBRE";
				break;
			case 12: 
				return "DICIEMBRE";
				break;			
		}
	}

	
	function diaPalabra($diaNumero){
		switch($diaNumero){
			case 1:
				return "enero";
				break;
			case 2:
				return "febrero";
				break;
			case 3:
				return "marzo";
				break;
			case 4:
				return "abril";
				break;
			case 5:
				return "Mayo";
				break;
			case 6:
				return "junio";
				break;
			case 7:
				return "julio";
				break;
			case 8:
				return "agosto";
				break;
			case 9:
				return "septiembre";
				break;
			case 10:
				return "octubre";
				break;
			case 11:
				return "noviembre";
				break;
			case 12:
				return "diciembre";
				break;
		}	
	}	
?>