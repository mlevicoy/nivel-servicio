<?php
	require_once("TemplatePower/class.TemplatePower.inc.php");		
	require_once("fpdf17/fpdf.php");
	require_once("conexion.php");
	require_once("sesiones.php");	
	date_default_timezone_set('America/Santiago');
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
	header('Content-Type: text/html; charset=UTF-8');

	validarAdministrador();
	validaTiempo();
	
	class PDF extends FPDF{
		var $widths;
		var $aligns;	
		public $hojaNumero=0;
		public $cabecera=0;	
		public $direccion_vialidad;
		public $fono_vialidad;
		public $web_vialidad;
		public $mail_vialidad;
		public $ciudad_vialidad;
		
		function header2(){
			//Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])
			$this->Image('imagenes/Logo Bogado/vialidad.jpg',1,1,2.8);						
		}
		
		function header3(){
			$this->SetFont('Arial','B',9);						
			$this->SetXY(1.7,0.5);
			$this->Cell(3,0.6,utf8_decode(html_entity_decode('PLANILLA DE TERRENO '.$this->hojaNumero.' DE 10')),0,0,'C',false);								
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);		
			//$this->Line(8,21,25.3,21);					
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
	
	if(!isset($_SESSION["BIMESTRE_INFORME"])){
		salir();
	}
	else{		
		//Generamos la tabla porcentaje
		$consulta="select count(*) as ctdadPorcetaje from porcentaje where bimestrePorcentaje=".$_SESSION["BIMESTRE_INFORME"];
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		if($row["ctdadPorcetaje"] == 0){
			$consulta = "insert into porcentaje (idPorcentaje,bimestrePorcentaje,incumplimientoFajaPorcentaje,incumplimientoSaneamientoPorcentaje,".
		  "incumplimientoCalzadaPorcentaje,incumplimientoBermaPorcentaje,incumplimientoSenalizacionPorcentaje,incumplimientoDemarcacionPorcentaje,".
		  "cumplimientoFajaPorcentaje,cumplimientoSaneamientoPorcentaje,cumplimientoCalzadaPorcentaje,cumplimientoBermaPorcentaje,".
		  "cumplimientoSenalizacionPorcentaje,cumplimientoDemarcacionPorcentaje) values ('',".$_SESSION["BIMESTRE_INFORME"].
		  ",0,0,0,0,0,0,0,0,0,0,0,0)";
		  $resultado = $conexion_db->query($consulta);
		}
		
		//Obtenermos los componentes seleccionados
		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);
		
		$i=0;
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$Ncomponente[$i]= $fila["nombreComponente"];
			$Ccomponente[$i]= $fila["codigoComponente"];
			$i++;
		}
			
		//Vemos que todos los segmentos esten en la tabla de incumplimiento
		$consulta = "select numeroSegmentos from inspeccionar";
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		$numeroSegmentos = $row["numeroSegmentos"];
		
		$consulta = "select count(*) as SegmentosUno from segmentosSorteados where bimestreSorteado=".$_SESSION["BIMESTRE_INFORME"].
		" and estadoIncumplimiento=1";		
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		if($row["SegmentosUno"] != $numeroSegmentos){
			header("Location: informeTablaIncumplimiento.php");
		}
		else{		
			//Informacion de la tabla bimestre 
			$bimestre = $_SESSION["BIMESTRE_INFORME"];			
			$consulta = "select * from bimestre where NroBimestre=".$bimestre;
			$resultado = $conexion_db->query($consulta);
			$row = $resultado->fetch_array(MYSQL_ASSOC);
		
			//Informacion de la tabla obra
			$consulta2 = "select * from obra";
			$resultado2 = $conexion_db->query($consulta2);
			$row2 = $resultado2->fetch_array(MYSQL_ASSOC);
				
			//Segmentos sorteados		
			$consulta3 = "select * from segmentosSorteados where bimestreSorteado=".$bimestre;
			$resultado3 = $conexion_db->query($consulta3);
			
			//Informacion de la comision
			$consulta5 = "select * from comision where bimestreComision=".$bimestre;
			$resultado5 = $conexion_db->query($consulta5);
			$row5 = $resultado5->fetch_array(MYSQL_ASSOC);
			
			$consulta10 = "select * from contrato where bimestreContrato=".$_SESSION["BIMESTRE_INFORME"];
			$resultado10 = $conexion_db->query($consulta10);
			$row10 = $resultado10->fetch_array(MYSQL_ASSOC);
				
			//Informacion del contrato
			$consulta6 = "select * from contrato where bimestreContrato=".$bimestre;
			$resultado6 = $conexion_db->query($consulta6);
			$row6 = $resultado6->fetch_array(MYSQL_ASSOC);
					
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
				
				//Tabla incumplimiento
				$consulta9 = "select * from incumplimiento where bimestreIncumplimiento = ".$bimestre." and segmentoIncumplimiento = ".
				$row3["numeroSegmentoSorteado"];
				$resultado9 = $conexion_db->query($consulta9);
				
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
				$pdf->header3();				
		
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
				//$y = $y + 0.5;
				$pdf->SetXY(14,$y);			
				$pdf->MultiCell(3,0.5,utf8_decode('2. SEGMENTO N°:'),0,'L',false);		
				$pdf->SetFont('Arial','',9);
				$pdf->SetXY(17.3,$y);			
				$pdf->MultiCell(7,0.5,strtoupper(utf8_decode(html_entity_decode($row3["numeroSegmentoSorteado"].", DESDE KM ".$row3["kmInicioSorteado"]." HASTA ".
				$row3["kmFinalSorteado"]))),0,'L',false);	
				
				//Nro Camino			
				$consulta12 = "select nroCaminoRedCaminera from redCaminera where rolRedCaminera = '".$row3["rolCaminoSorteado"]."'";
				$resultado12 = $conexion_db->query($consulta12);
				$fila12 = $resultado12->fetch_array(MYSQL_ASSOC);				
				
				$pdf->SetFont('Arial','B',9);
				$pdf->SetXY(24.3,$y);			
				$pdf->MultiCell(2.5,0.5,utf8_decode('3. N° CAMINO:'),0,'L',false);		
				$pdf->SetFont('Arial','',9);
				$pdf->SetXY(27.1,$y);			
				$pdf->MultiCell(4,0.5,strtoupper(utf8_decode(html_entity_decode($fila12["nroCaminoRedCaminera"]))),0,'L',false);
				
				//Camino			
			/*	$consulta8 = "select rolOriginal from asociacion where rolCreado = '".$row3["rolCaminoSorteado"]."'";	//Busca el rol real
				$resultado8 = $conexion_db->query($consulta8);
				$fila8 = $resultado8->fetch_array(MYSQL_ASSOC);
			*/				
				$pdf->SetFont('Arial','B',9);
				$y = $y + 0.5;
				$pdf->SetXY(1.7,$y);			
				$pdf->MultiCell(0,0.5,utf8_decode('4. CAMINO:'),0,'L',false);		
				$pdf->SetFont('Arial','',9);
				$pdf->SetXY(4,$y);			
				$pdf->MultiCell(0,0.5,strtoupper(utf8_decode(html_entity_decode($row3["nombreCaminoSorteado"].", rol ".
				$row3["rolCaminoSorteado"]))),0,'L',false);	
				
				//Cabecera de la tabla
				$pdf->SetFont('Arial','B',8);
					//Faja vial
				$y = $y + 1;
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
				$relleno = 0;
				while($row4 = $resultado4->fetch_array(MYSQL_ASSOC)){													
					//Indice y relleno
					$y = $y + 0.7;
					$relleno++;
				}
				$llenarValor = $relleno;	
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
				$llenarValor2 = $y;
				//Cantidad inclumplimiento
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(1.7,$y);
				$pdf->MultiCell(4.2,0.6,strtoupper(utf8_decode(html_entity_decode("N&deg; INCUMPLIMIENTO"))),'BRL','C',false);								
				//Cantidad inclumplimiento
				$y = $y + 0.6;
				$pdf->SetXY(1.7,$y);
				$pdf->MultiCell(4.2,0.6,strtoupper(utf8_decode(html_entity_decode("% INCUMPLIMIENTO"))),'BRL','C',false);				
				
				//Instrucción
				$pdf->SetFont('Arial','B',9);
				$y = $y + 0.6;
				$pdf->SetXY(20,$y);
				$pdf->MultiCell(0,0.5,utf8_decode('(C) Cumple ; (NC) No Cumple ; (SNS) Sin Nivel de Servicio ; (-) Sin Tramo'),0,'L',false);					

				//Firmas
				$y = $y + 1.4;
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
				$pdf->Line(8,20.9,25.3,20.9);	//Pie de firma				
				
				//LLenamos la informacion de cada celda				
				$x = 5.9;
				while($row9 = $resultado9->fetch_array(MYSQL_ASSOC)){											
					$y = 5.6;
					$z = $llenarValor2;
					for($i=1;$i<=$llenarValor;$i++){						
						$pdf->SetXY($x,$y);
						$pdf->SetFont('Arial','',9);
						$pdf->MultiCell(1.7,0.7,$row9["t".$i."Incumplimiento"],'BR','C',false);	
						$pdf->SetXY($x+1.7,$y);
						$pdf->SetFont('Arial','',5);
						$pdf->MultiCell(2.5,0.7,strtoupper(utf8_decode(html_entity_decode($row9["t".$i."Comentario"]))),'BR','C',false);													
						$y = $y + 0.7;	
					}
					
					$pdf->SetXY($x,$z);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(4.2,0.6,$row9["nroIncumplimiento"],'BR','C',false);	
					$z = $z + 0.6;
					$pdf->SetXY($x,$z);					
					$pdf->MultiCell(4.2,0.6,$row9["porcentajeIncumplimiento"],'BR','C',false);	
					$x = $x + 4.2;										
				}			
			}
			
			//Informe tabla incumplimiento
			
			//Inicio calculo de km excluidos
			//Variables
			$suma_faja = 0;
			$suma_saneamiento = 0;
			$suma_calzada = 0;
			$suma_bermas = 0;
			$suma_senalizacion = 0;
			$suma_demarcacion = 0;
						
			//Total KM				
			$consulta29 = "select sum(longitudRedCaminera) as sumakmred from redcaminera";
			$resultado29 = $conexion_db->query($consulta29);			
			$fila29 = $resultado29->fetch_array(MYSQL_ASSOC);
			$kmIngresados = $fila29["sumakmred"];
				
			//Desafeccion por componente
			$consulta30 = "select * from desafeccionreal";
			$resultado30 = $conexion_db->query($consulta30);							
			while($fila30 = $resultado30->fetch_array(MYSQL_ASSOC)){
				if(strcmp($fila30["fajaVialDesafeccionReal"],"SNS") == 0){ $suma_faja = $suma_faja + $fila30["longitudDesafeccionReal"]; }
				if(strcmp($fila30["saneamientoDesafeccionReal"],"SNS") == 0){ $suma_saneamiento = $suma_saneamiento + $fila30["longitudDesafeccionReal"]; }
				if(strcmp($fila30["calzadaDesafeccionReal"],"SNS") == 0){ $suma_calzada = $suma_calzada + $fila30["longitudDesafeccionReal"]; }
				if(strcmp($fila30["bermasDesafeccionReal"],"SNS") == 0){ $suma_bermas = $suma_bermas + $fila30["longitudDesafeccionReal"]; }
				if(strcmp($fila30["senalizacionDesafeccionReal"],"SNS") == 0){ $suma_senalizacion = $suma_senalizacion + $fila30["longitudDesafeccionReal"]; }
				if(strcmp($fila30["demarcacionDesafeccionReal"],"SNS") == 0){ $suma_demarcacion = $suma_demarcacion + $fila30["longitudDesafeccionReal"]; }
			}
				
			$fajaDescontado = $kmIngresados - number_format($suma_faja, 3, '.', '');
			$saneamientoDescontado = $kmIngresados - number_format($suma_saneamiento, 3, '.', '');
			$calzadaDescontado = $kmIngresados - number_format($suma_calzada, 3, '.', '');
			$bermaDescontado = $kmIngresados - number_format($suma_bermas, 3, '.', '');
			$senalizacionDescontado = $kmIngresados - number_format($suma_senalizacion, 3, '.', '');
			$demarcacionDescontado = $kmIngresados - number_format($suma_demarcacion, 3, '.', '');
				
			$fajaDescontado = number_format($fajaDescontado, 3, '.', '');
			$saneamientoDescontado = number_format($saneamientoDescontado, 3, '.', '');
			$calzadaDescontado = number_format($calzadaDescontado, 3, '.', '');
			$bermaDescontado = number_format($bermaDescontado, 3, '.', '');
			$senalizacionDescontado = number_format($senalizacionDescontado, 3, '.', '');
			$demarcacionDescontado = number_format($demarcacionDescontado, 3, '.', '');							
			//Fin calculo km excluidos				
			
			$consulta6 = "select * from segmentosSorteados where bimestreSorteado=".$_SESSION["BIMESTRE_INFORME"];
			$resultado6 = $conexion_db->query($consulta6);			
								
			$pdf->AddPage('P',array(22,33));										//Creacion de una pagina
			$pdf->SetFont('Arial','B',9);	
			$pdf->header2();
					
			$y = 4;
					
			//Titulo			
			$pdf->SetY($y);
			$pdf->Cell(0,.5,utf8_decode('INSPECCIÓN DE PAGO Nº ').$row["NroPagoBimestre"],0,0,'C',false);						
			$y = $y + 0.5;
			$pdf->SetY($y);
			$pdf->Cell(0,.5,utf8_decode('TABLA DE INCUMPLIMIENTO'),0,0,'C',false);			
					
			//Nombre obra
			$pdf->SetFont('Arial','',9);	
			$y = $y + 1;
			$pdf->SetXY(3,$y);							
			$pdf->MultiCell(15,.5,strtoupper(utf8_decode('"'.html_entity_decode($row2["nombreCompletoObra"]).'"')),0,'C',false);
			
			// Obtenemos los codigos de los componentes
				// Faja
			// $consulta23 = "select codigoComponente from codigocomponente where nombreComponente = 'FAJA'";
			// $resultado23 = $conexion_db->query($consulta23);
			// $fila23 = $resultado23->fetch_array(MYSQL_ASSOC);
				// Saneamiento
			// $consulta24 = "select codigoComponente from codigocomponente where nombreComponente = 'SANEAMIENTO'";
			// $resultado24 = $conexion_db->query($consulta24);
			// $fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				// Calzada
			// $consulta25 = "select codigoComponente from codigocomponente where nombreComponente = 'CALZADA'";
			// $resultado25 = $conexion_db->query($consulta25);
			// $fila25 = $resultado25->fetch_array(MYSQL_ASSOC);
				// Berma
			// $consulta26 = "select codigoComponente from codigocomponente where nombreComponente = 'BERMA'";
			// $resultado26 = $conexion_db->query($consulta26);
			// $fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				// Senalizacion
			// $consulta27 = "select codigoComponente from codigocomponente where nombreComponente = 'SENALIZACION'";
			// $resultado27 = $conexion_db->query($consulta27);
			// $fila27 = $resultado27->fetch_array(MYSQL_ASSOC);
				// Demarcacion
			// $consulta28 = "select codigoComponente from codigocomponente where nombreComponente = 'DEMARCACION'";
			// $resultado28 = $conexion_db->query($consulta28);
			// $fila28 = $resultado28->fetch_array(MYSQL_ASSOC);
			
			//TABLA					
				//Cabecera
			$pdf->SetFont('Arial','B',8);
			$y = $y+2;
			$pdf->SetXY(5.6,$y);
			$pdf->MultiCell(15,1,utf8_decode('PORCENTAJE DE INCUMPLIMIENTO (%)'),1,'C',false);					
			$y = $y+1;
			$k = $y;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,'HOJA DE TERRENO',1,'C',false);			
			$pdf->SetXY(3.6,$y);
			$pdf->MultiCell(2,1,'SEGMENTO','TRB','C',false);			
			$pdf->SetXY(5.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[0]),'RB','C',false);			
			$pdf->SetXY(8.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[1]),'RB','C',false);			
			$pdf->SetXY(10.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[2]),'RB','C',false);			
			$pdf->SetXY(13.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[3]),'RB','C',false);			
			$pdf->SetXY(15.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[4]),'RB','C',false);			
			$pdf->SetXY(18.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ccomponente[5]),'RB','C',false);			
			$y = $y + .5;
			$pdf->SetXY(5.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[0]),'RB','C',false);			
			$pdf->SetXY(8.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[1]),'RB','C',false);			
			$pdf->SetXY(10.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[2]),'RB','C',false);			
			$pdf->SetXY(13.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[3]),'RB','C',false);			
			$pdf->SetXY(15.6,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[4]),'RB','C',false);			
			$pdf->SetXY(18.1,$y);
			$pdf->MultiCell(2.5,.5,utf8_decode($Ncomponente[5]),'RB','C',false);			
					
			//Contenido
				//Columna 1				
			$pdf->SetFont('Arial','',8);
			$y = $k;
			$y = $y + 1;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('1'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('2'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('3'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('4'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('5'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('6'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('7'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('8'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('9'),'LRB','C',false);			
			$y = $y + 0.5;
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(1.6,.5,utf8_decode('10'),'LRB','C',false);			
					
					
			$y = $k;
			$y = $y + 1;
			$y2 = $k;
			$y2 = $y2 + 1;
			while($row6 = $resultado6->fetch_array(MYSQL_ASSOC)){
				//Columna 2
				$pdf->SetXY(3.6,$y);
				$pdf->MultiCell(2,.5,utf8_decode(html_entity_decode($row6["numeroSegmentoSorteado"])),'RB','C',false);			
				$y = $y + 0.5;
				
				//Consulta para el resto de la columnas
				$consulta7 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
				$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento=".$row6["numeroSegmentoSorteado"];
				$resultado7 = $conexion_db->query($consulta7);
				
				//Resto de las columnas
				$coordenadaX = 5.6;				
				while($row7 = $resultado7->fetch_array(MYSQL_ASSOC)){
					$pdf->SetXY($coordenadaX,$y2);
					$pdf->MultiCell(2.5,.5,utf8_decode(html_entity_decode($row7["porcentajeIncumplimiento"])),'RB','C',false);											
					$coordenadaX = $coordenadaX + 2.5;
				}
				$y2 = $y2+0.5;			
			}

			//RESULTADO			
			$pdf->SetFont('Arial','B',8);
			$pdf->SetXY(2,$y);
			$pdf->MultiCell(3.6,.5,'RESULTADO','LRB','C',false);						
				
				//Factor de pago
			$y2 = $y;			
			$y2 = $y2 + 1;
			$k2 = $y2;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(10,1,utf8_decode('FACTOR DE PAGO (%)'),'1','C',false);			
			$y2 = $y2 + 1;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode('COMPONENTE INSPECCIONADO'),'LRB','C',false);			
			$pdf->SetXY(7,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode('FACTOR DE PAGO'),'RB','C',false);
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[0]),'LRB','C',false);			
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[1]),'LRB','C',false);
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[2]),'LRB','C',false);			
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[3]),'LRB','C',false);			
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[4]),'LRB','C',false);			
			$y2 = $y2 + 0.5;
			$pdf->SetXY(2,$y2);
			$pdf->MultiCell(5,0.5,utf8_decode($Ncomponente[5]),'LRB','C',false);						
				
				//Resultado	FAJA
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='FAJA'";
			$resultado8 = $conexion_db->query($consulta8);
				
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0){// and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					$cantidadElementos++;
					$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
				}
			}
			//% de incumplimiento			
			$pdf->SetXY(5.6,$y);			
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($fajaDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false);		
				}			
			 }		
			//Factor de pago
			$y2 = $k2;
			$y2 = $y2 + 1.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);
			//$pdf->MultiCell(5,.5,utf8_decode(round((100-($sumaCantidadElementos/$cantidadElementos))/100,2)),'LRB','C',false);
			if($cantidadElementos > 0){ $pdf->MultiCell(5,.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{
				if($fajaDescontado > 0){
					$pdf->MultiCell(5,.5,utf8_decode('100'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(5,.5,utf8_decode('SNS'),'RB','C',false); 
				}			
			}
						
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoFajaPorcentaje='".round($sumaCantidadElementos/$cantidadElementos).
				"', cumplimientoFajaPorcentaje='".(100-round($sumaCantidadElementos/$cantidadElementos))."' where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoFajaPorcentaje='SNS', cumplimientoFajaPorcentaje='SNS' where bimestrePorcentaje=".$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}			
				
				//Resultado	SANEAMIENTO
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='SANEAMIENTO'";
			$resultado8 = $conexion_db->query($consulta8);
						
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0){// and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					$cantidadElementos++;
					$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
				} 
			}			
			//% de incumplimiento
			$pdf->SetXY(8.1,$y);
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($saneamientoDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false);		
				}						
			}
			//Factor de pago
			$y2 = $y2 + 0.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);			
			if($cantidadElementos > 0){ $pdf->MultiCell(5,0.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{ 
				if($saneamientoDescontado > 0){
					$pdf->MultiCell(5,0.5,utf8_decode('100'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(5,0.5,utf8_decode('SNS'),'RB','C',false); 
				}			
			}					
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoSaneamientoPorcentaje=".round($sumaCantidadElementos/$cantidadElementos).
				", cumplimientoSaneamientoPorcentaje=".(100-round($sumaCantidadElementos/$cantidadElementos))." where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoSaneamientoPorcentaje='SNS', cumplimientoSaneamientoPorcentaje='SNS' where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
				
				//Resultado	CALZADA
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='CALZADA'";
			$resultado8 = $conexion_db->query($consulta8);
						
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){				
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0 ){//and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					$cantidadElementos++;
					$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
				}
			}	
			//% de incumplimiento
			$pdf->SetXY(10.6,$y);
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($calzadaDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false); 
				}		
			}
			//Facto de pago
			$y2 = $y2 + 0.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);
			if($cantidadElementos > 0){ $pdf->MultiCell(5,0.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{ 
				if($calzadaDescontado > 0){
					$pdf->MultiCell(5,0.5,utf8_decode('100'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(5,0.5,utf8_decode('SNS'),'RB','C',false); 
				}			
			}									
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoCalzadaPorcentaje=".round($sumaCantidadElementos/$cantidadElementos).
				", cumplimientoCalzadaPorcentaje=".(100-round($sumaCantidadElementos/$cantidadElementos))." where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoCalzadaPorcentaje='SNS', cumplimientoCalzadaPorcentaje='SNS' where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}			
				
				//Resultado	BERMAS
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='BERMA'";
			$resultado8 = $conexion_db->query($consulta8);
						
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0){// and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					$cantidadElementos++;
					$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
				}
			}			
			//% de incumplimiento
			$pdf->SetXY(13.1,$y);
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($bermaDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false); 
				}
			}
			//Facto de pago
			$y2 = $y2 + 0.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);
			if($cantidadElementos > 0){ $pdf->MultiCell(5,0.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{ 
				if($bermaDescontado > 0){
					$pdf->MultiCell(5,0.5,utf8_decode('100'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(5,0.5,utf8_decode('SNS'),'RB','C',false); 
				}				
			}			
			
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoBermaPorcentaje=".round($sumaCantidadElementos/$cantidadElementos).
				", cumplimientoBermaPorcentaje=".(100-round($sumaCantidadElementos/$cantidadElementos))." where bimestrePorcentaje="
				.$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoBermaPorcentaje='SNS', cumplimientoBermaPorcentaje='SNS' where bimestrePorcentaje=".$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
						
				//Resultado	SENALIZACION
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='SENALIZACION'";
			$resultado8 = $conexion_db->query($consulta8);
					
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0 ){//and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					//if(strcmp($row8["porcentajeIncumplimiento"],'0')!=0){
						$cantidadElementos++;
						$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
					//}
				}
			}			
			//% de incumplimiento
			$pdf->SetXY(15.6,$y);
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($senalizacionDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false); 
				}
			}
			//Factor de pago
			$y2 = $y2 + 0.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);
			if($cantidadElementos > 0){ $pdf->MultiCell(5,0.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{ 
				if($senalizacionDescontado > 0){
					$pdf->MultiCell(5,0.5,utf8_decode('100'),'RB','C',false); 
				}
				else{
					$pdf->MultiCell(5,0.5,utf8_decode('SNS'),'RB','C',false); 
				}	
			}
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoSenalizacionPorcentaje=".round($sumaCantidadElementos/$cantidadElementos).
				", cumplimientoSenalizacionPorcentaje=".(100-round($sumaCantidadElementos/$cantidadElementos))." where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoSenalizacionPorcentaje='SNS', cumplimientoSenalizacionPorcentaje='SNS' where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			
				//Resultado	DEMARCACION
			$cantidadElementos = 0;
			$sumaCantidadElementos = 0;
			$consulta8 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
			$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='DEMARCACION'";
			$resultado8 = $conexion_db->query($consulta8);
						
			while($row8=$resultado8->fetch_array(MYSQL_ASSOC)){
				if(strcmp($row8["porcentajeIncumplimiento"],'-')!=0 ){//and strcmp($row8["porcentajeIncumplimiento"],'SNS')!=0){
					$cantidadElementos++;
					$sumaCantidadElementos = $sumaCantidadElementos+$row8["porcentajeIncumplimiento"];
				}
			}
			//% de incumplimiento
			$pdf->SetXY(18.1,$y);
			if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.5,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
			else{ 
				if($demarcacionDescontado > 0){
					$pdf->MultiCell(2.5,.5,utf8_decode('0'),'RB','C',false);		
				}
				else{
					$pdf->MultiCell(2.5,.5,utf8_decode('SNS'),'RB','C',false); 
				}			
			}
			//Facto de pago
			$y2 = $y2 + 0.5;
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY(7,$y2);
			if($cantidadElementos > 0){ $pdf->MultiCell(5,0.5,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
			else{ 
				if($demarcacionDescontado > 0){
					$pdf->MultiCell(5,0.5,utf8_decode('100'),'RB','C',false); 						
				}
				else{
					$pdf->MultiCell(5,0.5,utf8_decode('SNS'),'RB','C',false); 
				}					
			}			
			$pdf->SetFont('Arial','B',8);
			//Actualizacion tabla porcentaje
			if($cantidadElementos > 0){
				$consulta11 = "update porcentaje set incumplimientoDemarcacionPorcentaje=".round($sumaCantidadElementos/$cantidadElementos).
				", cumplimientoDemarcacionPorcentaje=".(100-round($sumaCantidadElementos/$cantidadElementos))." where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}
			else{
				$consulta11 = "update porcentaje set incumplimientoDemarcacionPorcentaje='SNS', cumplimientoDemarcacionPorcentaje='SNS' where bimestrePorcentaje=".
				$_SESSION["BIMESTRE_INFORME"];
				$resultado11 = $conexion_db->query($consulta11);
			}			
				
			//Firmas
			$y = $y2 + 2.5;
			$pdf->SetFont('Arial','',9);
			
			$pdf->Line(3,$y,8.8,$y);		//Vialidad 1
			$pdf->Line(12.8,$y,18.6,$y);	//Vialidad 2	
			
			$pdf->SetXY(1,$y);	
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]))),0,'C');	
			$pdf->SetXY(10.8,$y);
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]))),0,'C');	
			
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);	
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoUnoVialidadComision"]))),0,'C');	
			$pdf->SetXY(10.8,$y);
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoDosVialidadComision"]))),0,'C');
				
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);	
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
			$pdf->SetXY(10.8,$y);
			$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
		
		
			$y = $y + 2.5;
			$pdf->Line(8,$y,13.8,$y);	//Inspector
				
			$pdf->SetXY(0,$y);
			$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row10["inspectorFiscalContrato"]))),0,'C');
				
			$y = $y + 0.4;
			$pdf->SetXY(0,$y);
			$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode('INSPECTOR FISCAL'))),0,'C');
			
			$y = $y + 0.4;
			$pdf->SetXY(0,$y);
			$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionContructoraComision"]))),0,'C');				
				
			$y = $y + 1;	
			$pdf->SetXY(1.7,$y);
			$pdf->MultiCell(0,0.4,utf8_decode(html_entity_decode('SE DEJA CONSTANCIA QUE EN REPRESENTACIÓN DE LA EMPRESA ASISTIÓ:')),0,'L');
			
			$y = $y + 2.5;	
			$pdf->Line(8,$y,13.8,$y);	//Constructora
			$pdf->SetXY(1,$y);
			$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteContructoraComision"]))),0,'C');
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);
			$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["cargoContructoraComision"]))),0,'C');
			$y = $y + 0.4;
			$pdf->SetXY(1,$y);
			$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionContructoraComision"]))),0,'C');
			$pdf->Line(1,31.9,20.5,31.9);					
			
			$conexion_db->close();
			$pdf->Output('respaldoInformes/Informe_Inclumplimiento_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','F');	
			$pdf->Output('Informe_Inclumplimiento_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','D');
			//$pdf->Output();	 		
		}			
	}
?>