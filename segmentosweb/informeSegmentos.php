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
		public $direccion_vialidad;
		public $fono_vialidad;
		public $web_vialidad;
		public $mail_vialidad;
		public $ciudad_vialidad;
		
		function Header(){
			//Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])			
			$this->Image('imagenes/vialidad.jpg',1,1,2.8);			
			//Salto de linea
			$this->Ln(3.3);
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);			
			$this->Line(1,31.9,20.5,31.9);			
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
    		$h=0.4*$nb;
    		//Issue a page break first if needed
    		$this->CheckPageBreak($h);
    		//Draw the cells of the row
    		for($i=0;$i<count($data);$i++){
        		$w=$this->widths[$i];				
				if($i<3)
        			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'C';
				else if($i==3)
					$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
				else
					$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'R';
        		//Save the current position
        		$x=$this->GetX();
        		$y=$this->GetY();
        		//Draw the border
        		$this->Rect($x,$y,$w,$h);
        		//Print the text
				$this->MultiCell($w,0.4,$data[$i],0,$a);
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
			$tpl->assign("REDIRECCIONAR","informeSegmentos.php");
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
		//Información del formulario
		$bimestre = $_POST["numeroBimestre"];
		
		//Informacion de la tabla bimestre 
		$consulta = "select * from bimestre where NroBimestre = ".$bimestre;
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);
		
		//Informacion de la tabla obra
		$consulta2 = "select * from obra";
		$resultado2 = $conexion_db->query($consulta2);
		$row2 = $resultado2->fetch_array(MYSQL_ASSOC);		
			
		//Informacion del contrato
		$consulta4 = "select * from contrato where bimestreContrato=".$bimestre;
		$resultado4 = $conexion_db->query($consulta4);
		$row4 = $resultado4->fetch_array(MYSQL_ASSOC);		
			
		//Informacion de la comision
		$consulta5 = "select * from comision where bimestreComision=".$bimestre;
		$resultado5 = $conexion_db->query($consulta5);
		$row5 = $resultado5->fetch_array(MYSQL_ASSOC);
		
		//Objeto de la clase heredada
		$pdf = new PDF('P','cm',array(21.6,33));
		
		//Datos cabecera
		$pdf->direccion_vialidad = $row2["direccionMandanteObra"];
		$pdf->fono_vialidad = $row2["fonoMandanteObra"];
		$pdf->web_vialidad = $row2["webMandanteObra"];
		$pdf->mail_vialidad = $row2["mailMandanteObra"];
		$pdf->ciudad_vialidad = $row2["ciudadOficinaObra"];
		//Final datos cabecera	
	
		//Agrega una pagina y su fuente
		$pdf->AddPage('P',array(21.6,33));										//Creacion de una pagina
		$pdf->SetFont('Arial','B',9);	
		$pdf->SetLeftMargin(1.7);
		//Fin pagina y fuente													//Arial, negrita y tamaño 16 (I: italica, U: Subrayado, '':Normal)		
	
		$pdf->Ln(0);
		$pdf->Cell(0,0,utf8_decode('INSPECCIÓN DE PAGO Nº ').$row["NroPagoBimestre"],0,0,'C',false);
		$pdf->Ln(.5);
		$pdf->Cell(0,0,utf8_decode('ANEXO Nº 1'),0,0,'C',false);
		$pdf->Ln(1);
		$pdf->SetFont('Arial','B',9);											//Arial, negrita y tamaño 16 (I: italica, U: Subrayado, '':Normal)	
		$pdf->Cell(0,0,utf8_decode('CONTRATO CONSERVACIÓN GLOBAL MIXTO'),0,0,'C',false);
		$pdf->Ln(.5);
		$pdf->Cell(0,0,utf8_decode('POR NIVEL DE SERVICIO Y POR PRECIOS UNITARIOS'),0,0,'C',false);	
		$pdf->Ln(1.5);
		$pdf->SetFont('Arial','B',9);
		$pdf->MultiCell(0,0,utf8_decode('CONTRATO:'),0,'J',false);		
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY(7,7.5);
		$pdf->MultiCell(12,.4,strtoupper(utf8_decode('"'.html_entity_decode($row2["nombreCompletoObra"]).'"')),0,'J',false);
		$pdf->Ln(1);
		$pdf->SetFont('Arial','B',9);
		$pdf->MultiCell(0,0,utf8_decode('RESOLUCIÓN ADJUDICACIÓN:'),0,'J',false);	
		$pdf->SetX(7);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(0,0,'D.R.V. '.utf8_decode('Nº ').strtoupper(utf8_decode(html_entity_decode($row4["resolucionContrato"]))),0,'J',false);	
		$pdf->Ln(1);
		$pdf->SetFont('Arial','B',9);
		$pdf->MultiCell(0,0,utf8_decode('FECHA:'),0,'J',false);	
		$pdf->SetX(7);
		
		//Fecha recepcion (comision)
		$fechaInicioRecepcion = strtotime($row5["fechaInicioRecepcionComision"]);
		$fechaTerminoRecepcion = strtotime($row5["fechaFinalRecepcionComision"]);				
				
		$pdf->SetFont('Arial','',9);			
		
		//Vemos si son iguales las fechas
		if($fechaInicioRecepcion == $fechaTerminoRecepcion){				
			$fecha = utf8_encode(strftime('%A, %d de %B del %Y',strtotime($row5["fechaInicioRecepcionComision"])));
			$pdf->MultiCell(0,0,strtoupper(utf8_decode($fecha)),0,'J',false);	
		}
		else{
			$fechaInicioRecepcionTexto = utf8_encode(strftime('%A %d',strtotime($row5["fechaInicioRecepcionComision"])));
			$fechaTerminoRecepcionTexto = utf8_encode(strftime('%A %d de %B del %Y',strtotime($row5["fechaFinalRecepcionComision"])));
			$pdf->MultiCell(0,0,strtoupper(utf8_decode($fechaInicioRecepcionTexto." y ".$fechaTerminoRecepcionTexto)),0,'J',false);	
		}
		$pdf->Ln(1);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(0,0,utf8_decode('SEGMENTOS ELEGIDOS AL AZAR'),0,0,'C',false);
		$pdf->Ln(.8);
	
		/*Tabla con datos*/
		/*$consulta3 = "select nroCaminoRedCaminera, idSorteado, codigoCaminoSorteado, rolCaminoSorteado, nombreCaminoSorteado, ".
			"numeroSegmentoSorteado, kmInicioSorteado, kmFinalSorteado, numeroSegmentoSorteado, bimestreSorteado, ".
			"estadoIncumplimiento from segmentossorteados t1 inner join redcaminera t2 on t1.rolCaminoSorteado = t2.rolRedCaminera ".
			"where t1.bimestreSorteado = ".$bimestre." order by t2.nroCaminoRedCaminera asc, t1.numeroSegmentoSorteado asc";
		$resultado3 = $conexion_db->query($consulta3);*/
		$consulta3 = "select * from segmentosSorteados where bimestreSorteado=".$bimestre." order by numeroSegmentoSorteado";
		$resultado3 = $conexion_db->query($consulta3);
				
		$pdf->SetWidths(array(.6,2,2,8.6,1.7,2.1,1.8));	
		$pdf->SetFont('Arial','B',9);	
		$pdf->Row1(array(utf8_decode('Nº'),utf8_decode('Código'),'ROL','Nombre Camino','KM Inicio','KM Termino','Segmento'));
		$pdf->SetFont('Arial','',7);
		$i=1;
		while($row3 = $resultado3->fetch_array(MYSQL_ASSOC)){
			$pdf->Row2(array($i,utf8_decode($row3["codigoCaminoSorteado"]),utf8_decode($row3["rolCaminoSorteado"]),
			utf8_decode(html_entity_decode($row3["nombreCaminoSorteado"])),$row3["kmInicioSorteado"],$row3["kmFinalSorteado"],
			$row3["numeroSegmentoSorteado"]));
			$i++;			
		}
		//FIRMAS	
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY(1,22);	
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]))),0,'C');	
		$pdf->SetXY(1,22.4);	
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoUnoVialidadComision"]))),0,'C');	
		$pdf->SetXY(1,22.8);	
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
		
		$pdf->SetXY(10.8,22);
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]))),0,'C');	
		$pdf->SetXY(10.8,22.4);
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoDosVialidadComision"]))),0,'C');
		$pdf->SetXY(10.8,22.8);
		$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
			
		$pdf->SetXY(0,25);
		$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row4["inspectorFiscalContrato"]))),0,'C');
		$pdf->SetXY(0,25.4);
		$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode('INSPECTOR FISCAL')),0,'C');
		$pdf->SetXY(0,25.8);
		$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
			
		//Linea de firma
		$pdf->Line(3,21.9,8.8,21.9);	//Vialidad 1
		$pdf->Line(12.8,21.9,18.6,21.9);	//Vialidad 2	
		/*$pdf->Line(3,24.9,8.8,24.9);*/	//Asesorias
		$pdf->Line(7.9,24.9,13.7,24.9);	//Inspector	
		$pdf->Line(7.9,29.6,13.7,29.6);	//Constructora				
		
		$pdf->SetY(27.5);			
		$pdf->MultiCell(0,0.4,utf8_decode('SE DEJA CONSTANCIA QUE EN REPRESENTACIÓN DE LA EMPRESA ASISTIÓ:'),0,'L');
			
		$pdf->SetXY(1,29.6);
		$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteContructoraComision"]))),0,'C');
		$pdf->SetXY(1,30);
		$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["cargoContructoraComision"]))),0,'C');
		$pdf->SetXY(1,30.4);
		$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row4["nombreEmpresaConstructoraContrato"]))),0,'C');
	
		
		$pdf->Output('respaldoInformes/Segmentos_Sorteado_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','F');		
		$pdf->Output('Segmentos_Sorteado_Inspeccion_N'.$row["NroPagoBimestre"].'.pdf','D');					
	}
?>