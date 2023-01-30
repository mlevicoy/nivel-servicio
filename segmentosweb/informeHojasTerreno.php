	<?php
	require_once("TemplatePower/class.TemplatePower.inc.php");		
	require_once("fpdf17/fpdf.php");
	require_once("conexion.php");
	require_once("sesiones.php");	
	header('Content-Type: text/html; charset=UTF-8');
	date_default_timezone_set('America/Santiago');
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");	

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
			$this->SetFont('Arial','B',9);						
			$this->SetXY(1.7,0.5);
			$this->Cell(3,0.6,utf8_decode(html_entity_decode('PLANILLA DE TERRENO '.$this->hojaNumero.' DE 10')),0,0,'C',false);							
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);			
			$this->Line(8,21,25.3,21);			
			$this->Cell(0,10,strtoupper(utf8_decode(html_entity_decode($this->direccion_vialidad).', '.html_entity_decode($this->ciudad_vialidad).
			' , Chile - FONO: '.html_entity_decode($this->fono_vialidad).' - EMAIL: '.html_entity_decode($this->mail_vialidad).
			' / '.html_entity_decode($this->web_vialidad))),0,0,'C',false);		
		}
		
		function SetWidths($w){
			//Set the array of column widths
    		$this->widths=$w;
		}
		function SetAligns($a){
    		//Set the array of column alignments
    		$this->aligns=$a;
		}
		function Row($data){
    		//Calculate the height of the row
    		$nb=0;
    		for($i=0;$i<count($data);$i++)
        		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    		$h=0.5*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];				
        		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        		//Save the current position
        		$x=$this->GetX();
        		$y=$this->GetY();
        		//Draw the border
        		$this->Rect($x,$y,$w,$h);
        		//Print the text
				$this->MultiCell($w,0.5,$data[$i],0,$a);
        		//Put the position to the right of the cell
        		$this->SetXY($x+$w,$y);
   		 	}
    		//Go to the next line
    		$this->Ln($h);
		}
		function Row1($data){
    		//Calculate the height of the row
    		$nb=0;
    		for($i=0;$i<count($data);$i++)
        		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    		$h=0.5*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];				
        		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
        		//Save the current position
        		$x=$this->GetX();
        		$y=$this->GetY();
        		//Draw the border
        		$this->Rect($x,$y,$w,$h);
        		//Print the text
				$this->MultiCell($w,0.5,$data[$i],0,$a);
        		//Put the position to the right of the cell
        		$this->SetXY($x+$w,$y);
   		 	}
    		//Go to the next line
    		$this->Ln($h);
		}
		function Row2($data){
    		//Calculate the height of the row
    		$nb=0;
    		for($i=0;$i<count($data);$i++)
        		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    		$h=0.5*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];				
        		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'R';
        		//Save the current position
        		$x=$this->GetX();
        		$y=$this->GetY();
        		//Draw the border
        		$this->Rect($x,$y,$w,$h);
        		//Print the text
				$this->MultiCell($w,0.5,$data[$i],0,$a);
        		//Put the position to the right of the cell
        		$this->SetXY($x+$w,$y);
   		 	}
    		//Go to the next line
    		$this->Ln($h);
		}
		function CheckPageBreak($h){
    		//If the height h would cause an overflow, add a new page immediately
    		if($this->GetY()+$h>$this->PageBreakTrigger)
        		$this->AddPage($this->CurOrientation);
		}
		function NbLines($w,$txt){
    		//Computes the number of lines a MultiCell of width w will take
    		$cw=&$this->CurrentFont['cw'];
    		if($w==0)
        		$w=$this->w-$this->rMargin-$this->x;
    		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    		$s=str_replace("\r",'',$txt);
    		$nb=strlen($s);
    		if($nb>0 and $s[$nb-1]=="\n")
        		$nb--;
    		$sep=-1;
    		$i=0;
    		$j=0;
    		$l=0;
    		$nl=1;
   			while($i<$nb){
        		$c=$s[$i];
        		if($c=="\n"){
            		$i++;
            		$sep=-1;
            		$j=$i;
            		$l=0;
            		$nl++;
            		continue;
        		}
        		if($c==' ')
            		$sep=$i;
       			$l+=$cw[$c];
        		if($l>$wmax){
            		if($sep==-1){
                		if($i==$j)
                    		$i++;
            		}
            		else
                		$i=$sep+1;
            		$sep=-1;
            		$j=$i;
            		$l=0;
            		$nl++;
        		}
        		else
            		$i++;
    		}	
    		return $nl;
		}
	}
	//Entrega la fecha
	function actual_date(){  
 	   $week_days = array ("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");  
 	   $months = array ("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");  
 	   $year_now = date ("Y");  
 	   $month_now = date ("n");  
 	   $day_now = date ("j");  
 	   $week_day_now = date ("w");  
 	   $date = $week_days[$week_day_now] . ", " . $day_now . " de " . $months[$month_now] . " de " . $year_now;   
 	   return $date;    
	}

	if(!isset($_POST["cargador"])){
		$consulta = "select count(*) as segSorteados from segmentosSorteados";
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		if($row["segSorteados"] == 0){
			if(strcmp($_SESSION["CARGO"],"Administrador") == 0){			
				$tpl = new TemplatePower("administrador.html");	
				$tpl->assignInclude("header", "header.html");
			    $tpl->assignInclude("menu", "menu.html");													
			}
			else{
				$tpl = new TemplatePower("usuario.html");														
			}
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));
			$tpl->assign("DISPLAY","compact;");	
			$tpl->assign("MENSAJE","NO SE PUEDE GENERAR EL INFORME, NO SE HA REALIZADO NING&Uacute;N SORTEO.");	
			$conexion_db->close();
			$tpl->printToScreen();										
		}
		else{	
			$consulta2 = "select * from bimestre where estadoBimestre = 0";
			$resultado2 = $conexion_db->query($consulta2);			
			
			if(strcmp($_SESSION["CARGO"],"Administrador") == 0){
				$tpl = new TemplatePower("elegirBimestre.html");
				$tpl->assignInclude("header", "header.html");
			    $tpl->assignInclude("menu", "menu.html");	
			}
			else{
				$tpl = new TemplatePower("elegirBimestre_usr.html");	
			}
			$tpl->prepare();
			$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
			$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
			$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
			$tpl->assign("REDIRECCIONAR","informeHojasTerreno.php");
			
			while($fila2 = $resultado2->fetch_array(MYSQL_ASSOC)){
				if($fila2["NroPagoBimestre"] == 1000){
					$tpl->newBlock("NUMERO_BIMESTRE");
					$tpl->assign("numBimestre",$fila2["NroBimestre"]);
					$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO FINAL");
				}
				else{
					$tpl->newBlock("NUMERO_BIMESTRE");
					$tpl->assign("numBimestre",$fila2["NroBimestre"]);
					$tpl->assign("nomBimestre","INSPECCI&Oacute;N DE PAGO N&deg; ".$fila2["NroPagoBimestre"]);	
				}				
			}	
			$tpl->printToScreen();			
		}
	}
	else{
		$bimestre = $_POST["numeroBimestre"];
		//Informacion de la tabla bimestre 
		$consulta = "select * from bimestre where NroBimestre=".$bimestre;
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		
		//Informacion de la tabla obra
		$consulta2 = "select * from obra";
		$resultado2 = $conexion_db->query($consulta2);
		$row2 = $resultado2->fetch_array(MYSQL_ASSOC);
		
		//Segmentos sorteados		
		$consulta3 = "select * from segmentosSorteados where bimestreSorteado= ".$bimestre." order by numeroSegmentoSorteado asc";
		$resultado3 = $conexion_db->query($consulta3);
		
		//Informacion de la comision
		$consulta5 = "select * from comision where bimestreComision=".$bimestre;
		$resultado5 = $conexion_db->query($consulta5);
		$row5 = $resultado5->fetch_array(MYSQL_ASSOC);
			
		//Informacion del contrato
		$consulta6 = "select * from contrato where bimestreContrato=".$bimestre;
		$resultado6 = $conexion_db->query($consulta6);
		$row6 = $resultado6->fetch_array(MYSQL_ASSOC);
		
		//Obtenemos los componentes seleccionados
		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);		
		$i=0;
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$Ncomponente[$i]= $fila["nombreComponente"];
			$Ccomponente[$i]= $fila["codigoComponente"];
			$i++;
		}
				
		//Objeto de la clase heredada
		$pdf = new PDF('L','cm',array(22,33));
		
		//Datos cabecera
		$pdf->direccion_vialidad = $row2["direccionMandanteObra"];
		$pdf->fono_vialidad = $row2["fonoMandanteObra"];
		$pdf->web_vialidad = $row2["webMandanteObra"];
		$pdf->mail_vialidad = $row2["mailMandanteObra"];
		$pdf->ciudad_vialidad = $row2["ciudadOficinaObra"];
		//Final datos cabecera	
		
		$l = 1;
		while($row3 = $resultado3->fetch_array(MYSQL_ASSOC)){			
			//Tabla desafeccion
			$consulta4 = "select * from designacion where nroSegmentoDesignacion=".$row3["numeroSegmentoSorteado"];
			$resultado4 = $conexion_db->query($consulta4);
			
			//Tramos del segmento
			$consulta7 = "select kmInicioSubSegmento, kmFinalSubSegmentos from subSegmentos where segmentoSubSegmentos = ".
			$row3["numeroSegmentoSorteado"];	
			$resultado7 = $conexion_db->query($consulta7);		
			
			//Fin tabla desafeccion
			$pdf->hojaNumero = $l;
			$l++;
		
			//Agrega una pagina y su fuente
			$pdf->AddPage('L',array(22,33));										//Creacion de una pagina
			$pdf->SetFont('Arial','B',9);				
			//Fin pagina y fuente													//Arial, negrita y tamaño 16 (I: italica, U: Subrayado, '':Normal)		
	
			//Titulo
			$pdf->ln(1);			
			$pdf->Cell(0,0,utf8_decode('INSPECCIÓN DE PAGO Nº ').$row["NroPagoBimestre"],0,0,'C',false);		
			
			//Indicador de posicion Y
			$y = 0;
			
			//Fecha
			$pdf->SetFont('Arial','B',9);
			$y = $y+2.5;
			$pdf->SetXY(1.7,$y);
			$pdf->MultiCell(2,0.5,utf8_decode('1. FECHA:'),0,'L',false);					
			
			//Fecha recepcion (comision)
			$fechaInicioRecepcion = strtotime($row5["fechaInicioRecepcionComision"]);
			$fechaTerminoRecepcion = strtotime($row5["fechaFinalRecepcionComision"]);											
						
			//Vemos si son iguales las fechas
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(4,$y);
			if($fechaInicioRecepcion == $fechaTerminoRecepcion){				
				$fecha = utf8_encode(strftime('%A, %d de %B del %Y',strtotime($row5["fechaInicioRecepcionComision"])));
				$pdf->MultiCell(10,0.5,strtoupper(utf8_decode($fecha)),0,'L',false);
				
			}
			else{
				$fechaInicioRecepcionTexto = utf8_encode(strftime('%A %d',strtotime($row5["fechaInicioRecepcionComision"])));
				$fechaTerminoRecepcionTexto = utf8_encode(strftime('%A %d de %B del %Y',strtotime($row5["fechaFinalRecepcionComision"])));
				$pdf->MultiCell(10,0.5,strtoupper(utf8_decode($fechaInicioRecepcionTexto." y ".$fechaTerminoRecepcionTexto)),0,'L',false);	
			}

			//Segmento
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(14,$y);			
			$pdf->MultiCell(3,0.5,utf8_decode('2. SEGMENTO N°:'),0,'L',false);		
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(17.3,$y);			
			$pdf->MultiCell(7,0.5,strtoupper(utf8_decode(html_entity_decode($row3["numeroSegmentoSorteado"].", DESDE KM ".$row3["kmInicioSorteado"]." HASTA ".
			$row3["kmFinalSorteado"]))),0,'L',false);			
			
			//Nro Camino			
			$consulta9 = "select nroCaminoRedCaminera from redCaminera where rolRedCaminera = '".$row3["rolCaminoSorteado"]."'";
			$resultado9 = $conexion_db->query($consulta9);
			$fila9 = $resultado9->fetch_array(MYSQL_ASSOC);				
			
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(24.3,$y);			
			$pdf->MultiCell(2.5,0.5,utf8_decode('3. N° CAMINO:'),0,'L',false);		
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(27.1,$y);			
			$pdf->MultiCell(4,0.5,strtoupper(utf8_decode(html_entity_decode($fila9["nroCaminoRedCaminera"]))),0,'L',false);			
			
			//Camino				
			$pdf->SetFont('Arial','B',9);
			$y = $y + 0.5;
			$pdf->SetXY(1.7,$y);			
			$pdf->MultiCell(0,0.5,utf8_decode('4. CAMINO:'),0,'L',false);		
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(4,$y);			
			$pdf->MultiCell(0,0.5,strtoupper(utf8_decode(html_entity_decode($row3["nombreCaminoSorteado"].", rol ".$row3["rolCaminoSorteado"]))),0,'L',false);	
			
			//Cabecera de la tabla
				$pdf->SetFont('Arial','B',8);
				//Faja vial
			$y = $y + 1;
			//$k = $y;
			$pdf->SetXY(5.9,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[0]),'LBRT','C',false);			
				//Saneamiento
			$pdf->SetXY(10.1,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[1]),'BRT','C',false);					
				//Calzada
			$pdf->SetXY(14.3,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[2]),'BRT','C',false);					
				//Bermas
			$pdf->SetXY(18.5,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[3]),'BRT','C',false);					
				//Senalizacion
				$pdf->SetFont('Arial','B',7.5);
			$pdf->SetXY(22.7,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[4]),'BRT','C',false);					
				//Demarcacion
				$pdf->SetFont('Arial','B',8);
			$pdf->SetXY(26.9,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode($Ncomponente[5]),'BRT','C',false);								
				//Segunda fila de la tabla
				//Bermas			
			$y = $y + 0.8;
			$pdf->SetXY(1.7,$y);
			$pdf->MultiCell(4.2,0.8,utf8_decode('TRAMOS (KM)'),'TLBR','C',false);		
			$pdf->SetFont('Arial','B',7);	
				//Evaluacion y observacion faja vial
			$pdf->SetXY(5.9,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(7.6,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
				//Evaluacion y observacion saneamiento
			$pdf->SetXY(10.1,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(11.8,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
				//Evaluacion y observacion calzada
			$pdf->SetXY(14.3,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(16,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
				//Evaluacion y observacion bermas
			$pdf->SetXY(18.5,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(20.2,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
				//Evaluacion y observacion señalizacion
			$pdf->SetXY(22.7,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(24.4,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
				//Evaluacion y observacion demarcacion
			$pdf->SetXY(26.9,$y);
			$pdf->MultiCell(1.7,0.4,utf8_decode('Evaluación en Terreno'),'BR','C',false);						
			$pdf->SetXY(28.6,$y);
			$pdf->MultiCell(2.5,0.8,utf8_decode('Observación'),'BR','C',false);						
			
			$pdf->SetFont('Arial','',7);	
			
				//Relleno tramos
			//$y = $k;
			$y = $y + 0.8;
			$k=$y;
			while($row7 = $resultado7->fetch_array(MYSQL_ASSOC)){
				$pdf->SetXY(1.7,$y);
				$pdf->MultiCell(4.2,0.7,$row7["kmInicioSubSegmento"]." - ".$row7["kmFinalSubSegmentos"],'BRL','C',false);				
				$y = $y + 0.7;
			}
			$pdf->SetFont('Arial','',8);	
				//Relleno 1				
			$y = $k;
			//$y = $y + 0.9;
			$relleno = 0;
			while($row4 = $resultado4->fetch_array(MYSQL_ASSOC)){			
				//Faja Vial
				$pdf->SetXY(5.9,$y);
				$pdf->MultiCell(1.7,0.7,$row4["fajaDesignacion"],'BR','C',false);						
				$pdf->SetXY(7.6,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);						
				//Saneamiento
				$pdf->SetXY(10.1,$y);
				$pdf->MultiCell(1.7,0.7,$row4["saneamientoDesignacion"],'BR','C',false);						
				$pdf->SetXY(11.8,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);						
				//Calzada
				$pdf->SetXY(14.3,$y);
				$pdf->MultiCell(1.7,0.7,$row4["calzadaDesignacion"],'BR','C',false);						
				$pdf->SetXY(16,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);						
				//Berma
				$pdf->SetXY(18.5,$y);
				$pdf->MultiCell(1.7,0.7,$row4["bermasDesignacion"],'BR','C',false);						
				$pdf->SetXY(20.2,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);						
				//Senalización
				$pdf->SetXY(22.7,$y);
				$pdf->MultiCell(1.7,0.7,$row4["senalizacionDesignacion"],'BR','C',false);						
				$pdf->SetXY(24.4,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);						
				//Demarcación
				$pdf->SetXY(26.9,$y);
				$pdf->MultiCell(1.7,0.7,$row4["demarcacionDesignacion"],'BR','C',false);						
				$pdf->SetXY(28.6,$y);
				$pdf->MultiCell(2.5,0.7,'','BR','C',false);										
				//Indice y relleno
				$y = $y + 0.7;
				$relleno++;
			}
				//Relleno 2
			while($relleno < 15){
				//Tramos
				$pdf->SetFont('Arial','',6);
				$pdf->SetXY(1.7,$y);
				$pdf->MultiCell(4.2,0.6,"NO EXISTE TRAMO",'BRL','C',false);				
				$pdf->SetFont('Arial','',8);
				//Faja Vial
				$pdf->SetXY(5.9,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(7.6,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);										
				//Saneamiento
				$pdf->SetXY(10.1,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(11.8,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);										
				//Calzada
				$pdf->SetXY(14.3,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(16,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);					
				//Berma
				$pdf->SetXY(18.5,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(20.2,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);										
				//Senalización
				$pdf->SetXY(22.7,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(24.4,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);						
				//Demarcación
				$pdf->SetXY(26.9,$y);
				$pdf->MultiCell(1.7,0.6,"-",'BR','C',false);						
				$pdf->SetXY(28.6,$y);
				$pdf->MultiCell(2.5,0.6,"-",'BR','C',false);										
				//Indice y relleno
				$y = $y + 0.6;
				$relleno++;			
			}		
			//Instrucción
			$pdf->SetFont('Arial','B',9);
			$y = $y + 0.1;
			$pdf->SetXY(20,$y);
			$pdf->MultiCell(0,0.5,utf8_decode('(C) Cumple ; (NC) No Cumple ; (SNS) Sin Nivel de Servicio ; (-) Sin Tramo'),0,'L',false);					

			//Firmas
			$y = $y + 1.6;
			$k = $y;
			$pdf->SetXY(1,$y);	
			$pdf->MultiCell(15,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]))),0,'C');	
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);
			$pdf->MultiCell(15,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoUnoVialidadComision"]))),0,'C');	
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);
			$pdf->MultiCell(15,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
			
			$y = $k;
			$pdf->SetXY(16,$y);
			$pdf->MultiCell(16,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]))),0,'C');	
			$y = $y + 0.4;
			$pdf->SetXY(16,$y);
			$pdf->MultiCell(16,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoDosVialidadComision"]))),0,'C');
			$y = $y + 0.4;
			$pdf->SetXY(16,$y);
			$pdf->MultiCell(16,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
			
			//Linea de firma
			$y = $k-0.1;			
			$pdf->Line(3,$y,14,$y);		//Vialidad 1
			$pdf->Line(18,$y,30,$y);	//Vialidad 2			
		}
		$conexion_db->close();
		
		$pdf->Output('respaldoInformes/Hojas_Terreno_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','F');		
		$pdf->Output('Hojas_Terreno_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','D');
		//$pdf->Output();
	}
?>