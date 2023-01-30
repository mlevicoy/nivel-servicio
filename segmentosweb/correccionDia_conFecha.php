<?PHP														
	require_once("TemplatePower/class.TemplatePower.inc.php");//  |
	require_once("conexion.php");							  //  |
	require_once("sesiones.php");							  //  |
	require_once("fpdf17/fpdf.php");						  //  |-- CABECERA E INCLUSIÓN DE ARCHIVOS PARA EL FUNCIONAMIENTO 	
	date_default_timezone_set('America/Santiago');			  //  |-- DE LA PÁGINA
	setlocale(LC_ALL,"es_ES@euro","es_ES","esp");			  //  |
	header('Content-Type: text/html; charset=UTF-8');		  //  |
															
	//Funciones en sesiones.php
	validaTiempo();

	/*Clases para PDF*/
	class PDF extends FPDF{
		public $direccion_vialidad;
		public $fono_vialidad;
		public $web_vialidad;
		public $mail_vialidad;
		public $ciudad_vialidad;

		function Header(){
			//Image(string file [, float x [, float y [, float w [, float h [, string type [, mixed link]]]]]])			
			$this->Image('imagenes/Logo Bogado/vialidad.jpg',1,1,2.8);						
			//Salto de linea
			$this->Ln(3.3);
		}
		function Footer(){			
			$this->SetXY(1,-5.8);
			$this->SetFont('Arial','B',6);			
			$this->SetTextColor(1,11,126);			
			$this->Line(1,31.9,20.5,31.9);			
			$this->Cell(0,10,strtoupper(utf8_decode(html_entity_decode($this->direccion_vialidad).', '.html_entity_decode($this->ciudad_vialidad).' , Chile - FONO: '.html_entity_decode($this->fono_vialidad).' - EMAIL: '.html_entity_decode($this->mail_vialidad).' / '.html_entity_decode($this->web_vialidad))),0,0,'C',false);		
		}
	}

	//Primera carga
	if(!isset($_POST["cargador"]))
	{
		//Obtenemos la variable de session
		$bimestre = $_SESSION["BIMESTRE_INFORME"];
		//Cargamos la página
		//if(strcmp($_SESSION["CARGO"],"Administrador") == 0){ 
			$tpl = new TemplatePower("correccionDia_conFecha.html"); 
		//else{ $tpl = new TemplatePower("correccionDia_conFecha_usr.html"); }
		
		//Obtenermos los componentes seleccionados
		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);		
		$i=0;
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$Ncomponente[$i]= $fila["nombreComponente"];
			$Ccomponente[$i]= $fila["codigoComponente"];
			$i++;
		}
		
		$tpl->assignInclude("header", "header.html");
		$tpl->assignInclude("menu", "menu.html");
		
		$tpl->prepare();
		$tpl->assign("NOMBRE",ucwords(strtolower($_SESSION["NOMBRE"])));
		$tpl->assign("CARGO",ucwords(strtolower($_SESSION["CARGO"])));
		$tpl->assign("OBRA",ucwords(strtolower($_SESSION["NOMBRE_OBRA"])));		
		//Se toman los datos del bimestre
		$consulta = "select * from bimestre where NroBimestre=".$bimestre;		
		$resultado = $conexion_db->query($consulta);
		$row = $resultado->fetch_array(MYSQL_ASSOC);			
		//Calculamos los días
		$fechaInicio = $row["fechaInicioBimestre"];	//Fecha inicio	//***	
		$fechaInicio_aux = $row["fechaInicioBimestre"];	//Fecha inicio Auxiliar de la base de datos
		$fechaInicio = date("Y-m-d", strtotime('-1 day', strtotime($fechaInicio)));
		$fechaTermino = $row["fechaTerminoBimestre"];	//Fecha Termino	//***		
		
		//FajaVial
		$tpl->newBlock("TITULO_FAJA");
		$tpl->assign("CORRECCION_FAJA",$Ncomponente[0]);
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and fajaVialDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_FAJA");
				$tpl->assign("CODIGO_RUTA_FAJA",$rol);
				$tpl->assign("KM_INICIO_FAJA",$kmInicio);
				$tpl->assign("KM_TERMINO_FAJA",$kmFinal);
				$tpl->assign("VALOR_RUTA_FAJA",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_FAJA",number_format($longitudRedCaminera, 3, '.', ''));				
				$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino);
				$tpl->assign("DIAS_FAJA",compararFechas($fechaTermino, $fechaInicio));
				
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else
			{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and fajaVialDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0)
				{
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and fajaVialDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_FAJA");
					$tpl->assign("CODIGO_RUTA_FAJA",$rol);
					$tpl->assign("KM_INICIO_FAJA",$kmInicio);
					$tpl->assign("KM_TERMINO_FAJA",$kmFinal);
					$tpl->assign("VALOR_RUTA_FAJA",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_FAJA",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino);
					$tpl->assign("DIAS_FAJA",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1)
					{
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and fajaVialDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.',''); 
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.',''); 
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2, 3, '.', ''); 							
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1, 3, '.', ''); 
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud1);
							$tpl->assign("LICITADO_FAJA",$kmLicitado);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino1);
							$tpl->assign("DIAS_FAJA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio1);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal1);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud1);
							$tpl->assign("LICITADO_FAJA",$kmLicitado);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino1);
							$tpl->assign("DIAS_FAJA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio2);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal2);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud2);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino2);
							$tpl->assign("DIAS_FAJA",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio1);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal1);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud1);
							$tpl->assign("LICITADO_FAJA",$kmLicitado);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino1);
							$tpl->assign("DIAS_FAJA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio2);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal2);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud2);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino2);
							$tpl->assign("DIAS_FAJA",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_FAJA");
							$tpl->assign("CODIGO_RUTA_FAJA",$rol);
							$tpl->assign("KM_INICIO_FAJA",$kmInicio3);
							$tpl->assign("KM_TERMINO_FAJA",$kmFinal3);
							$tpl->assign("VALOR_RUTA_FAJA",$longitud3);
							$tpl->assign("FECHA_DESDE_FAJA",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_FAJA",$fechaTermino3);
							$tpl->assign("DIAS_FAJA",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}				
		}	
		//Saneamiento
		$tpl->newBlock("TITULO_SANEAMIENTO");
		$tpl->assign("CORRECCION_SANEAMIENTO",$Ncomponente[1]);
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and saneamientoDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
				$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
				$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio);
				$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal);
				$tpl->assign("VALOR_RUTA_SANEAMIENTO",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_SANEAMIENTO",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino);
				$tpl->assign("DIAS_SANEAMIENTO",compararFechas($fechaTermino, $fechaInicio));
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and saneamientoDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0){
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and saneamientoDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
					$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
					$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio);
					$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal);
					$tpl->assign("VALOR_RUTA_SANEAMIENTO",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_SANEAMIENTO",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino);
					$tpl->assign("DIAS_SANEAMIENTO",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1){
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and saneamientoDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.','');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2,3,'.','');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud1);
							$tpl->assign("LICITADO_SANEAMIENTO",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino1);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio1);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal1);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud1);
							$tpl->assign("LICITADO_SANEAMIENTO",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino1);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio2);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal2);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud2);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino2);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio1);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal1);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud1);
							$tpl->assign("LICITADO_SANEAMIENTO",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino1);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio2);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal2);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud2);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino2);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SANEAMIENTO");
							$tpl->assign("CODIGO_RUTA_SANEAMIENTO",$rol);
							$tpl->assign("KM_INICIO_SANEAMIENTO",$kmInicio3);
							$tpl->assign("KM_TERMINO_SANEAMIENTO",$kmFinal3);
							$tpl->assign("VALOR_RUTA_SANEAMIENTO",$longitud3);
							$tpl->assign("FECHA_DESDE_SANEAMIENTO",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_SANEAMIENTO",$fechaTermino3);
							$tpl->assign("DIAS_SANEAMIENTO",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}				
		}
		//Calzada
		$tpl->newBlock("TITULO_CALZADA");
		$tpl->assign("CORRECCION_CALZADA",$Ncomponente[2]);
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and calzadaDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_CALZADA");
				$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
				$tpl->assign("KM_INICIO_CALZADA",$kmInicio);
				$tpl->assign("KM_TERMINO_CALZADA",$kmFinal);
				$tpl->assign("VALOR_RUTA_CALZADA",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_CALZADA",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino);
				$tpl->assign("DIAS_CALZADA",compararFechas($fechaTermino, $fechaInicio));
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and calzadaDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0){
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and calzadaDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_CALZADA");
					$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
					$tpl->assign("KM_INICIO_CALZADA",$kmInicio);
					$tpl->assign("KM_TERMINO_CALZADA",$kmFinal);
					$tpl->assign("VALOR_RUTA_CALZADA",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_CALZADA",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino);
					$tpl->assign("DIAS_CALZADA",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1){
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and calzadaDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.','');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2,3,'.','');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud1);
							$tpl->assign("LICITADO_CALZADA",$kmLicitado);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino1);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio1);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal1);
							$tpl->assign("LICITADO_CALZADA",$kmLicitado);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud1);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino1);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio2);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal2);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud2);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino2);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio1);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal1);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud1);
							$tpl->assign("LICITADO_CALZADA",$kmLicitado);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino1);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio2);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal2);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud2);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino2);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_CALZADA");
							$tpl->assign("CODIGO_RUTA_CALZADA",$rol);
							$tpl->assign("KM_INICIO_CALZADA",$kmInicio3);
							$tpl->assign("KM_TERMINO_CALZADA",$kmFinal3);
							$tpl->assign("VALOR_RUTA_CALZADA",$longitud3);
							$tpl->assign("FECHA_DESDE_CALZADA",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_CALZADA",$fechaTermino3);
							$tpl->assign("DIAS_CALZADA",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}
		}
		//Bermas
		$tpl->newBlock("TITULO_BERMAS");
		$tpl->assign("CORRECCION_BERMAS",$Ncomponente[3]);
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and bermasDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_BERMAS");
				$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
				$tpl->assign("KM_INICIO_BERMAS",$kmInicio);
				$tpl->assign("KM_TERMINO_BERMAS",$kmFinal);
				$tpl->assign("VALOR_RUTA_BERMAS",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_BERMAS",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino);
				$tpl->assign("DIAS_BERMAS",compararFechas($fechaTermino, $fechaInicio));
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and bermasDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0){
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and bermasDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_BERMAS");
					$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
					$tpl->assign("KM_INICIO_BERMAS",$kmInicio);
					$tpl->assign("KM_TERMINO_BERMAS",$kmFinal);
					$tpl->assign("VALOR_RUTA_BERMAS",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_BERMAS",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino);
					$tpl->assign("DIAS_BERMAS",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1){
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and bermasDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.','');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2,3,'.','');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud1);
							$tpl->assign("LICITADO_BERMAS",$kmLicitado);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino1);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio1);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal1);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud1);
							$tpl->assign("LICITADO_BERMAS",$kmLicitado);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino1);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio2);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal2);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud2);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino2);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio1);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal1);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud1);
							$tpl->assign("LICITADO_BERMAS",$kmLicitado);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino1);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio2);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal2);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud2);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino2);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_BERMAS");
							$tpl->assign("CODIGO_RUTA_BERMAS",$rol);
							$tpl->assign("KM_INICIO_BERMAS",$kmInicio3);
							$tpl->assign("KM_TERMINO_BERMAS",$kmFinal3);
							$tpl->assign("VALOR_RUTA_BERMAS",$longitud3);
							$tpl->assign("FECHA_DESDE_BERMAS",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_BERMAS",$fechaTermino3);
							$tpl->assign("DIAS_BERMAS",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}	
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}
		}
		//Señalizacion
		$tpl->newBlock("TITULO_SENALIZACION");
		$tpl->assign("CORRECCION_SENALIZACION",utf8_decode($Ncomponente[4]));
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and senalizacionDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
				$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
				$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio);
				$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal);
				$tpl->assign("VALOR_RUTA_SENALIZACION",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_SENALIZACION",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino);
				$tpl->assign("DIAS_SENALIZACION",compararFechas($fechaTermino, $fechaInicio));
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and senalizacionDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0){
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and senalizacionDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
					$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
					$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio);
					$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal);
					$tpl->assign("VALOR_RUTA_SENALIZACION",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_SENALIZACION",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino);
					$tpl->assign("DIAS_SENALIZACION",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1){
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and senalizacionDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.','');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2,3,'.','');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud1);
							$tpl->assign("LICITADO_SENALIZACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino1);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio1);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal1);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud1);
							$tpl->assign("LICITADO_SENALIZACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino1);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio2);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal2);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud2);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino2);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio1);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal1);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud1);
							$tpl->assign("LICITADO_SENALIZACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino1);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio2);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal2);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud2);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino2);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_SENALIZACION");
							$tpl->assign("CODIGO_RUTA_SENALIZACION",$rol);
							$tpl->assign("KM_INICIO_SENALIZACION",$kmInicio3);
							$tpl->assign("KM_TERMINO_SENALIZACION",$kmFinal3);
							$tpl->assign("VALOR_RUTA_SENALIZACION",$longitud3);
							$tpl->assign("FECHA_DESDE_SENALIZACION",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_SENALIZACION",$fechaTermino3);
							$tpl->assign("DIAS_SENALIZACION",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}	
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}
		}
		//Demarcacion
		$tpl->newBlock("TITULO_DEMARCACION");
		$tpl->assign("CORRECCION_DEMARCACION",$Ncomponente[5]);
		//Se toma los caminos
		$consulta2 = "select * from redcaminera order by nroCaminoRedCaminera";
		$resultado2 = $conexion_db->query($consulta2);				
		$aux_color = 1;
		while($row2 = $resultado2->fetch_array(MYSQL_ASSOC)){
			//Obtenemos el rol
			$rol = $row2["rolRedCaminera"];	//***
			$kmInicio = $row2["kmInicioRedCaminera"]; //***
			$kmFinal = $row2["kmFinalRedCaminera"];	//***
			$longitudRedCaminera = $row2["longitudRedCaminera"]; //***
			
			//Vemos si hay exclusiones
			$consulta3 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and demarcacionDesafeccionReal = 'SNS'";
			$resultado3 =  $conexion_db->query($consulta3);
			$fila3 = $resultado3->fetch_array(MYSQL_ASSOC);
			if($fila3["cantidad"] == 0){
				//Caso 1: No hay exclusiones
				$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
				$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
				$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio);
				$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal);
				$tpl->assign("VALOR_RUTA_DEMARCACION",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("LICITADO_DEMARCACION",number_format($longitudRedCaminera, 3, '.', ''));
				$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio_aux);
				$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino);
				$tpl->assign("DIAS_DEMARCACION",compararFechas($fechaTermino, $fechaInicio));
				//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
				if($aux_color == 1){
					$color = "background-color: white;";
					$aux_color = 0;
				}
				else{
					$color = "background-color: lightblue;";
					$aux_color = 1;
				}
				$tpl->assign("COLOR",$color);
			}
			else{
				//Vemos si hay exclusiones no iniciales
				$consulta5 = "select count(*) as cantidad from desafeccionreal where rolDesafeccionReal = '".$rol.
				"' and demarcacionDesafeccionReal = 'SNS' and exclusionInicial = 0";
				$resultado5 = $conexion_db->query($consulta5);
				$fila5 = $resultado5->fetch_array(MYSQL_ASSOC);
				if($fila5["cantidad"] == 0){
					//Caso 2: Hay puras exclusiones iniciales
					$consulta6 = "SELECT sum(`longitudDesafeccionReal`) as sumaLongitud FROM `desafeccionreal` ".
						"WHERE rolDesafeccionReal = '".$rol."' and demarcacionDesafeccionReal = 'SNS'";
					$resultado6 = $conexion_db->query($consulta6);
					$fila6 = $resultado6->fetch_array(MYSQL_ASSOC); 
					$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
					$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
					$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio);
					$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal);
					$tpl->assign("VALOR_RUTA_DEMARCACION",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("LICITADO_DEMARCACION",number_format($longitudRedCaminera - $fila6["sumaLongitud"], 3, '.', ''));
					$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio_aux);
					$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino);
					$tpl->assign("DIAS_DEMARCACION",compararFechas($fechaTermino, $fechaInicio));
					//$tpl->assign("RESOLUCION_CALZADA", "SIN EXCLUSIÓN");
					if($aux_color == 1){
						$color = "background-color: white;";
						$aux_color = 0;
					}
					else{
						$color = "background-color: lightblue;";
						$aux_color = 1;
					}
					$tpl->assign("COLOR",$color);
				}
				else{
					//Caso 3: Si hay exclusiones iniciales y no iniciales			
					$consulta4 = "select * from desafeccionreal where rolDesafeccionReal = '".$rol.
						"' and demarcacionDesafeccionReal = 'SNS' and desdeDesafeccionReal >= ".$kmInicio.
						" and hastaDesafeccionReal <= ".$kmFinal." and exclusionInicial = 0";
					$resultado4 = $conexion_db->query($consulta4);		
				
					while($fila4 = $resultado4->fetch_array(MYSQL_ASSOC)){
						//Caso 1: Exclusión cubre todo el bimestre
						if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"],3,'.','');
							$kmLicitado = number_format($longitud1,3,'.','');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fechaTermino;
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);	
						}
						//Caso 2: Cubre parte dentro del bimestre
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafeccionReal"];//
							$kmFinal2 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format(($kmFinal2 - $kmInicio2) - $fila4["longitudDesafeccionReal"], 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fila4["fecha_termino"];
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);

							$kmInicio3 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmInicio3 = $kmFinal;
							$longitud3 = number_format($longitudRedCaminera, 3, '.', '');
							//$longitud3 = number_format($kmFinal3 - $kmInicio3, 3, '.', '');
							$fechaInicio3 = $fila4["fecha_termino"];
							$fechaTermino3 = $fechaTermino;
							$diferenciaDias3 = compararFechas($fechaTermino3, $fechaInicio3);					
						}
						//Caso 3: Exclusión antes del bimestre actual y antes que termine el bimestre actual 
						else if(compararFechas($fechaInicio_aux, $fila4["fecha_inicio"]) >= 0 && compararFechas($fechaTermino, $fila4["fecha_termino"]) > 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["desdeDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_termino"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["desdeDesafecionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud2,3,'.','');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_termino"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						//Caso 4: Exclusión despues del bimestre actual y despues que termine el bimestre actual 
						else if(compararFechas($fila4["fecha_inicio"], $fechaInicio_aux) > 0 && compararFechas($fila4["fecha_termino"], $fechaTermino) >= 0){
							$kmInicio1 = $kmInicio;
							$kmFinal1 = $kmFinal;//$fila4["hastaDesafeccionReal"];//
							$longitud1 = number_format($longitudRedCaminera, 3, '.', '');
							$kmLicitado = number_format($longitud1,3,'.','');
							//$longitud1 = number_format($kmFinal1 - $kmInicio1, 3, '.', '');
							$fechaInicio1 = $fechaInicio_aux;
							$fechaTermino1 = $fila4["fecha_inicio"];
							$diferenciaDias1 = compararFechas($fechaTermino1, $fechaInicio);

							$kmInicio2 = $kmInicio;//$fila4["hastaDesafeccionReal"];//
							$kmFinal2 = $kmFinal;
							$longitud2 = number_format($longitudRedCaminera - $fila4["longitudDesafeccionReal"], 3, '.', '');
							//$longitud2 = number_format($kmFinal2 - $kmInicio2, 3, '.', '');
							$fechaInicio2 = $fila4["fecha_inicio"];
							$fechaTermino2 = $fechaTermino;
							$diferenciaDias2 = compararFechas($fechaTermino2, $fechaInicio2);
						}
						if(isset($longitud1) && !isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud1);
							$tpl->assign("LICITADO_DEMARCACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino1);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);
						}
						else if(isset($longitud1) && isset($longitud2) && !isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio1);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal1);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud1);
							$tpl->assign("LICITADO_DEMARCACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino1);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio2);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal2);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud2);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino2);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						else if(isset($longitud1) && isset($longitud2) && isset($longitud3)){
							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio1);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal1);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud1);
							$tpl->assign("LICITADO_DEMARCACION",$kmLicitado);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio1);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino1);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias1);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							if($aux_color == 1){
								$color = "background-color: white;";
								$aux_color = 0;
							}
							else{
								$color = "background-color: lightblue;";
								$aux_color = 1;
							}
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio2);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal2);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud2);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio2);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino2);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias2);
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						

							$tpl->newBlock("CORRECCIONDIA_DEMARCACION");
							$tpl->assign("CODIGO_RUTA_DEMARCACION",$rol);
							$tpl->assign("KM_INICIO_DEMARCACION",$kmInicio3);
							$tpl->assign("KM_TERMINO_DEMARCACION",$kmFinal3);
							$tpl->assign("VALOR_RUTA_DEMARCACION",$longitud3);
							$tpl->assign("FECHA_DESDE_DEMARCACION",$fechaInicio3);
							$tpl->assign("FECHA_HASTA_DEMARCACION",$fechaTermino3);
							$tpl->assign("DIAS_DEMARCACION",$diferenciaDias3);	
							//$tpl->assign("RESOLUCION_CALZADA", $fila4["observacionDesafeccionReal"]);
							$tpl->assign("COLOR",$color);						
						}
						if(isset($longitud1)){
							unset($longitud1);
						}
						if(isset($longitud2)){
							unset($longitud2);
						}
						if(isset($longitud3)){
							unset($longitud3);
						}									
						if(isset($kmLicitado)){
							unset($kmLicitado);
						}
					}
				}	
			}
		}		
		//Se muestra la página
		$tpl->printToScreen();
	}
	else
	{
		
	//****************************************** DATOS FORMULARIO CORRECCION DIA *************************************************
		//Información del formulario
	$valor_filtro = '';
	//$nroCamino = array_merge(array_filter(, function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
		//Faja
	$codigo_ruta_faja = array_merge(array_filter($_POST["codigo_ruta_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioFaja = array_merge(array_filter($_POST["kmInicioFaja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoFaja = array_merge(array_filter($_POST["kmTerminoFaja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoFaja = array_merge(array_filter($_POST["longitudMenosUnoFaja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_faja = array_merge(array_filter($_POST["fecha_desde_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_faja = array_merge(array_filter($_POST["fecha_hasta_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_faja = array_merge(array_filter($_POST["dia_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_faja = array_merge(array_filter($_POST["factor_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_faja = array_merge(array_filter($_POST["cantidad_faja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$licitadoFaja = array_merge(array_filter($_POST["licitadoFaja"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_faja = $_POST["resolucion_faja"];
		//Saneamiento
	$codigo_ruta_saneamiento = array_merge(array_filter($_POST["codigo_ruta_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioSaneamiento = array_merge(array_filter($_POST["kmInicioSaneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoSaneamiento = array_merge(array_filter($_POST["kmTerminoSaneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoSaneamiento = array_merge(array_filter($_POST["longitudMenosUnoSaneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_saneamiento = array_merge(array_filter($_POST["fecha_desde_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_saneamiento = array_merge(array_filter($_POST["fecha_hasta_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_saneamiento = array_merge(array_filter($_POST["dia_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_saneamiento = array_merge(array_filter($_POST["factor_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_saneamiento = array_merge(array_filter($_POST["cantidad_saneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));	
	$licitadoSaneamiento = array_merge(array_filter($_POST["licitadoSaneamiento"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_saneamiento = $_POST["resolucion_saneamiento"];
		//Calzada
	$codigo_ruta_calzada = array_merge(array_filter($_POST["codigo_ruta_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioCalzada = array_merge(array_filter($_POST["kmInicioCalzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoCalzada = array_merge(array_filter($_POST["kmTerminoCalzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoCalzada = array_merge(array_filter($_POST["longitudMenosUnoCalzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_calzada = array_merge(array_filter($_POST["fecha_desde_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_calzada = array_merge(array_filter($_POST["fecha_hasta_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_calzada = array_merge(array_filter($_POST["dia_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_calzada = array_merge(array_filter($_POST["factor_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_calzada = array_merge(array_filter($_POST["cantidad_calzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$licitadoCalzada = array_merge(array_filter($_POST["licitadoCalzada"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_calzada = $_POST["resolucion_calzada"];
		//Bermas
	$codigo_ruta_bermas = array_merge(array_filter($_POST["codigo_ruta_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioBermas = array_merge(array_filter($_POST["kmInicioBermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoBermas = array_merge(array_filter($_POST["kmTerminoBermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoBermas = array_merge(array_filter($_POST["longitudMenosUnoBermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_bermas = array_merge(array_filter($_POST["fecha_desde_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_bermas = array_merge(array_filter($_POST["fecha_hasta_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_bermas = array_merge(array_filter($_POST["dia_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_bermas = array_merge(array_filter($_POST["factor_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_bermas = array_merge(array_filter($_POST["cantidad_bermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$licitadoBermas = array_merge(array_filter($_POST["licitadoBermas"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_bermas = $_POST["resolucion_bermas"];
		//Senalizacion
	$codigo_ruta_senalizacion = array_merge(array_filter($_POST["codigo_ruta_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioSenalizacion = array_merge(array_filter($_POST["kmInicioSenalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoSenalizacion = array_merge(array_filter($_POST["kmTerminoSenalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoSenalizacion = array_merge(array_filter($_POST["longitudMenosUnoSenalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_senalizacion = array_merge(array_filter($_POST["fecha_desde_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_senalizacion = array_merge(array_filter($_POST["fecha_hasta_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_senalizacion = array_merge(array_filter($_POST["dia_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_senalizacion = array_merge(array_filter($_POST["factor_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_senalizacion = array_merge(array_filter($_POST["cantidad_senalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$licitadoSenalizacion = array_merge(array_filter($_POST["licitadoSenalizacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_senalizacion = $_POST["resolucion_senalizacion"];
		//Demarcacion
	$codigo_ruta_demarcacion = array_merge(array_filter($_POST["codigo_ruta_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmInicioDemarcacion = array_merge(array_filter($_POST["kmInicioDemarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$kmTerminoDemarcacion = array_merge(array_filter($_POST["kmTerminoDemarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$longitudMenosUnoDemarcacion = array_merge(array_filter($_POST["longitudMenosUnoDemarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_desde_demarcacion = array_merge(array_filter($_POST["fecha_desde_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$fecha_hasta_demarcacion = array_merge(array_filter($_POST["fecha_hasta_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$dia_demarcacion = array_merge(array_filter($_POST["dia_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$factor_demarcacion = array_merge(array_filter($_POST["factor_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	$cantidad_demarcacion = array_merge(array_filter($_POST["cantidad_demarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));	
	$licitadoDemarcacion = array_merge(array_filter($_POST["licitadoDemarcacion"], function ($element) use ($valor_filtro) { return ($element != $valor_filtro); }));
	//$resolucion_demarcacion = $_POST["resolucion_demarcacion"];
		
	//Sumamos los kmLicitados y kmVigentes
	$kmLicitadoFaja = 0;
	$kmVigenteFaja = 0;
	for($i=0;$i<count($licitadoFaja);$i++){
		$kmLicitadoFaja = $kmLicitadoFaja + $licitadoFaja[$i];
	}
	for($i=0;$i<count($cantidad_faja);$i++){
		$kmVigenteFaja = $kmVigenteFaja + $cantidad_faja[$i];
	}	
	$kmLicitadoFaja = number_format($kmLicitadoFaja,3,'.','');
	$kmVigenteFaja = number_format($kmVigenteFaja,3,'.','');
		
		
	$kmLicitadoSaneamiento = 0;
	$kmVigenteSaneamiento = 0;
	for($i=0;$i<count($licitadoSaneamiento);$i++){
		$kmLicitadoSaneamiento = $kmLicitadoSaneamiento + $licitadoSaneamiento[$i];
	}
	for($i=0;$i<count($cantidad_saneamiento);$i++){
		$kmVigenteSaneamiento = $kmVigenteSaneamiento + $cantidad_saneamiento[$i];
	}
	$kmLicitadoSaneamiento = number_format($kmLicitadoSaneamiento,3,'.','');	
	$kmVigenteSaneamiento = number_format($kmVigenteSaneamiento,3,'.','');
	
	$kmLicitadoCalzada = 0;
	$kmVigenteCalzada = 0;
	for($i=0;$i<count($licitadoCalzada);$i++){
		$kmLicitadoCalzada = $kmLicitadoCalzada + $licitadoCalzada[$i];
	}
	for($i=0;$i<count($cantidad_calzada);$i++){
		$kmVigenteCalzada = $kmVigenteCalzada + $cantidad_calzada[$i];
	}
	$kmLicitadoCalzada = number_format($kmLicitadoCalzada,3,'.','');		
	$kmVigenteCalzada = number_format($kmVigenteCalzada,3,'.','');
		
	$kmLicitadoBermas = 0;
	$kmVigenteBermas = 0;
	for($i=0;$i<count($licitadoBermas);$i++){
		$kmLicitadoBermas = $kmLicitadoBermas + $licitadoBermas[$i];
	}
	for($i=0;$i<count($cantidad_bermas);$i++){
		$kmVigenteBermas = $kmVigenteBermas + $cantidad_bermas[$i];
	}
	$kmLicitadoBermas = number_format($kmLicitadoBermas,3,'.','');
	$kmVigenteBermas = number_format($kmVigenteBermas,3,'.','');
		
	$kmLicitadoSenalizacion = 0;		
	$kmVigenteSenalizacion = 0;		
	for($i=0;$i<count($licitadoSenalizacion);$i++){
		$kmLicitadoSenalizacion = $kmLicitadoSenalizacion + $licitadoSenalizacion[$i];
	}		
	for($i=0;$i<count($cantidad_senalizacion);$i++){
		$kmVigenteSenalizacion = $kmVigenteSenalizacion + $cantidad_senalizacion[$i];
	}
	$kmLicitadoSenalizacion = number_format($kmLicitadoSenalizacion,3,'.','');
	$kmVigenteSenalizacion = number_format($kmVigenteSenalizacion,3,'.','');
			
	$kmLicitadoDemarcacion = 0;
	$kmVigenteDemarcacion = 0;
	for($i=0;$i<count($licitadoDemarcacion);$i++){
		$kmLicitadoDemarcacion = $kmLicitadoDemarcacion + $licitadoDemarcacion[$i];
	}	

	for($i=0;$i<count($cantidad_demarcacion);$i++){
		$kmVigenteDemarcacion = $kmVigenteDemarcacion + $cantidad_demarcacion[$i];
	}
	$kmLicitadoDemarcacion = number_format($kmLicitadoDemarcacion,3,'.','');
	$kmVigenteDemarcacion = number_format($kmVigenteDemarcacion,3,'.','');
		
	
	//******************************************** GENERAMOS EL INFORME FINAL *************************************************
		
	//Informacion de la tabla obra
	$consulta = "select * from obra";
	$resultado = $conexion_db->query($consulta);
	$row = $resultado->fetch_array(MYSQL_ASSOC);
	//Buscamos el nombre de la región
	$consulta_1 = "select nombreRegion from regiones where numeroRegion = '".$row["regionOficinaObra"]."'";
	$resultad_1 = $conexion_db->query($consulta_1);
	$fila_1 = $resultad_1->fetch_array(MYSQL_ASSOC);
	//Informacion de la tabla bimestre		
	$consulta2 = "select * from bimestre where NroBimestre = ".$_SESSION["BIMESTRE_INFORME"];
	$resultado2 = $conexion_db->query($consulta2);
	$row2 = $resultado2->fetch_array(MYSQL_ASSOC);		
	//Informacion de la tabla contrato
	$consulta3 = "select * from contrato where bimestreContrato=".$_SESSION["BIMESTRE_INFORME"];
	$resultado3 = $conexion_db->query($consulta3);
	$row3 = $resultado3->fetch_array(MYSQL_ASSOC);
	//Informacion de la tabla porcentaje
	$consulta4 = "select * from porcentaje where bimestrePorcentaje=".$_SESSION["BIMESTRE_INFORME"];
	$resultado4 = $conexion_db->query($consulta4);
	$row4 = $resultado4->fetch_array(MYSQL_ASSOC);
	//Informacion de la tabla comision
	$consulta5 = "select * from comision where bimestreComision=".$_SESSION["BIMESTRE_INFORME"];
	$resultado5 = $conexion_db->query($consulta5);
	$row5 = $resultado5->fetch_array(MYSQL_ASSOC);					
	//Informacion de la tabla recepcionAnteriorDescontada
	$consulta18 = "select * from recepcionAnteriorDescontada where bimestreRecepcionAnterior < ".$_SESSION["BIMESTRE_INFORME"];
	$resultado18 = $conexion_db->query($consulta18);	
	//Objeto de la clase heredada
	$pdf = new PDF('P','cm',array(21.6,33));	
	//Datos cabecera
	$pdf->direccion_vialidad = $row["direccionMandanteObra"];
	$pdf->fono_vialidad = $row["fonoMandanteObra"];
	$pdf->web_vialidad = $row["webMandanteObra"];
	$pdf->mail_vialidad = $row["mailMandanteObra"];
	$pdf->ciudad_vialidad = $row["ciudadOficinaObra"];
	//Final datos cabecera			
	//Agrega una pagina y su fuente
	$pdf->AddPage('P|',array(21.6,33));					//Creacion de una pagina							
	//Salto de linea
	//Nombre de la obra
	$pdf->SetFont('Arial','',9);
	$pdf->SetXY(11.4,1);
	$pdf->MultiCell(1.1,.4,utf8_decode('REF:'),0,'J',false);	
	$pdf->SetXY(12.58,1);
	$pdf->MultiCell(8,.4,strtoupper(utf8_decode('"'.html_entity_decode($row["nombreCompletoObra"]).'"')),0,'J',false);
	//Titulo
	$pdf->Ln(1);
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(0,.4,utf8_decode('ACTA INSPECCIÓN DE PAGO N° '.html_entity_decode($row2["NroPagoBimestre"]).''),0,'C',false);
	$pdf->SetY(4);
	$pdf->MultiCell(0,.4,utf8_decode('(Conservación por nivel de servicio)'),0,'C',false);
	//Contenido
	$pdf->Ln(.8);
	$pdf->SetFont('Arial','',9);
	
	$fechaInicioRecepcion = date("d",strtotime($row5["fechaInicioRecepcionComision"]));
	$diaTerminoRecepcion = date("d",strtotime($row5["fechaFinalRecepcionComision"]));
	$fechaTerminoRecepcion = utf8_encode(strftime('%d de %B del %Y',strtotime($row5["fechaFinalRecepcionComision"])));

	$pdf->SetX(.8);
	if($fechaInicioRecepcion == $diaTerminoRecepcion){
		$pdf->MultiCell(0,.5,utf8_decode('En el lugar de las obras, con fecha '.$fechaTerminoRecepcion.', se reunió la comisión de Recepción Única de '.
		'Inspección Pago, para el contrato indicado en REF. La Comisión ha sido designada mediante Res. D.R.V. (EX) N° '.
		strtoupper(html_entity_decode($row3["resolucionRecepcionContrato"])).' y se constituye por los siguientes profesionales:'),0,'J',false);
	}
	else{		
		$pdf->MultiCell(0,.5,utf8_decode('En el lugar de las obras, con fecha '.$fechaInicioRecepcion.' y '.$fechaTerminoRecepcion.', se reunió la comisión '.
		'de Recepción Única de Inspección Pago, para el contrato indicado en REF. La Comisión ha sido designada mediante Res. D.R.V. (EX) N° '.
		strtoupper(html_entity_decode($row3["resolucionRecepcionContrato"])).' y se constituye por los siguientes profesionales:'),0,'J',false);
	}
		
	//Se agregar los profesionales de la comision
	$pdf->Ln(.5);
	$pdf->SetFont('Arial','B',9);
	$pdf->SetX(.8);
	$pdf->MultiCell(0,.5,strtoupper(utf8_decode(html_entity_decode($row5["profesionUnoIntegrante"]).
												' ').
									utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]).
												', DIRECCIÓN DE VIALIDAD, ').
									utf8_decode(html_entity_decode($fila_1["nombreRegion"]))),0,'J',false);
	$pdf->SetX(.8);
	$pdf->MultiCell(0,.5,strtoupper(utf8_decode(html_entity_decode($row5["profesionDosIntegrante"]).
												' ').
									utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]).
												', DIRECCIÓN DE VIALIDAD, ').
									utf8_decode(html_entity_decode($fila_1["nombreRegion"]))),0,'J',false);
	$pdf->SetFont('Arial','',9);
	
	/*Revisamos el nombre del contrato*/
	$nombreArray = array();
	$nombreArray = explode(" ",$row3["nombreEmpresaConstructoraContrato"]);
	$largoArray = count($nombreArray);	
	
	if(strcmp($nombreArray[$largoArray-1], "s.a.") == 0)
	{
		$nombreArray[$largoArray-1] = "S.A.";
	}
	/*Fin revisión del nombre del contrato*/
	
	$pdf->Ln(.5);
	$pdf->SetX(.8);
	$pdf->MultiCell(0,.5,utf8_decode('Las obras fueron adjudicadas mediante Propuesta Pública a Series de Precios Unitarios y Suma Alzada a la empresa ').
	ucwords(utf8_decode(html_entity_decode(implode(" ",$nombreArray)))).utf8_decode(', en virtud de la resolución D.R.V. N° ').
	strtolower(html_entity_decode($row3["resolucionContrato"])).utf8_decode('.'),0,'J',false);	
	$pdf->Ln(.5);
	$pdf->SetX(.8);
	$pdf->MultiCell(0,.5,utf8_decode('De acuerdo a lo establecido en el articulo 14° de las bases Administrativas Generales, la Comision ').
	utf8_decode('se remitió a inspeccionar un porcentaje de la red conservada por el nivel de servicio, seleccionada de acuerdo a lo indicado ').
	utf8_decode('en las Especificaciones Técnicas Generales para Contratos de Conservación Global Mixto por Nivel de Servicio y por Precios Unitarios.'),0,'J',false);
	
	$pdf->Ln(.5);
	
	//Titulo
	$pdf->SetFont('Arial','B',7);	
	$pdf->SetXY(.8,12.2);
	$pdf->MultiCell(3.8,1.4,utf8_decode('ELEMENTO O COMPONENTE'),1,'C',false);
	$pdf->SetXY(4.6,12.2);	
	$pdf->MultiCell(2,1.4,utf8_decode('KM. LICITADO'),'TRB','C',false);	
	$pdf->SetXY(6.6,12.2);	
	$pdf->MultiCell(2,1.4,utf8_decode('KM. VIGENTE'),'TRB','C',false);	
	$pdf->SetXY(8.6,12.2);
	$pdf->MultiCell(1.3,1.4,utf8_decode('DIAS'),'TRB','C',false);
	$pdf->SetXY(9.9,12.2);
	$pdf->MultiCell(2.3,0.465,utf8_decode('CANT. EN CONSERVACIÓN * FP (DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.2,12.2);
	$pdf->MultiCell(2.2,0.465,utf8_decode('FACTOR DE CUMPLIMIENTO (%)'),'TRB','C',false);
	$pdf->SetXY(14.4,12.2);
	$pdf->MultiCell(6.2,.6,utf8_decode('CANTIDAD RECEPCIONADA'),'TRB','C',false);
	$pdf->SetXY(14.4,12.8);
	$pdf->MultiCell(2.2,.4,utf8_decode('Hasta recepción anterior'),'RB','C',false);
	$pdf->SetXY(16.6,12.8);
	$pdf->MultiCell(2,.4,utf8_decode('En la presente recepción'),'RB','C',false);
	$pdf->SetXY(18.6,12.8);
	$pdf->MultiCell(2,.4,utf8_decode('Recepcionada a la fecha'),'RB','C',false);
	//codigos y elemento
		//Obtenemos los codigos de los componentes
		//Faja
	$consulta23 = "select codigoComponente from codigocomponente where nombreComponente = 'FAJA'";
	$resultado23 = $conexion_db->query($consulta23);
	$fila23 = $resultado23->fetch_array(MYSQL_ASSOC);
		//Saneamiento
	$consulta24 = "select codigoComponente from codigocomponente where nombreComponente = 'SANEAMIENTO'";
	$resultado24 = $conexion_db->query($consulta24);
	$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
		//Calzada
	$consulta25 = "select codigoComponente from codigocomponente where nombreComponente = 'CALZADA'";
	$resultado25 = $conexion_db->query($consulta25);
	$fila25 = $resultado25->fetch_array(MYSQL_ASSOC);
		//Berma
	$consulta26 = "select codigoComponente from ctdadcodigocomponente where nombreComponente = 'CALZADA DTS'";
	$resultado26 = $conexion_db->query($consulta26);
	$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
		//Senalizacion
	$consulta27 = "select codigoComponente from codigocomponente where nombreComponente = 'SENALIZACION'";
	$resultado27 = $conexion_db->query($consulta27);
	$fila27 = $resultado27->fetch_array(MYSQL_ASSOC);
		//Demarcacion
	$consulta28 = "select codigoComponente from codigocomponente where nombreComponente = 'DEMARCACION'";
	$resultado28 = $conexion_db->query($consulta28);
	$fila28 = $resultado28->fetch_array(MYSQL_ASSOC);
	
	//Obtenermos los componentes seleccionados
		$consulta = "select ctdadcodigocomponente.* from ctdadcodigocomponente inner join codigocomponente on ctdadcodigocomponente.codigocomponente=codigocomponente.codigocomponente order by idCodigo";
		$resultado = $conexion_db->query($consulta);		
		$i=0;
		while($fila = $resultado->fetch_array(MYSQL_ASSOC)){
			$Ncomponente[$i]= $fila["nombreComponente"];
			$Ccomponente[$i]= $fila["codigoComponente"];
			$i++;
		}
	
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(.8,13.6);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[0]),'LRB','C',false);
	$pdf->SetXY(2.2,13.6);
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[0]),'RB','C',false);
	$pdf->SetXY(.8,14.1);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[1]),'LRB','C',false);
	$pdf->SetXY(2.2,14.1);
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[1]),'RB','C',false);
	$pdf->SetXY(.8,14.6);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[2]),'LRB','C',false);
	$pdf->SetXY(2.2,14.6);
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[2]),'RB','C',false);
	$pdf->SetXY(.8,15.1);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[3]),'LRB','C',false);
	$pdf->SetXY(2.2,15.1);
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[3]),'RB','C',false);
	$pdf->SetXY(.8,15.6);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[4]),'LRB','C',false);	
	$pdf->SetXY(2.2,15.6);	
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[4]),'RB','C',false);
	$pdf->SetXY(.8,16.1);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[5]),'LRB','C',false);
	$pdf->SetXY(2.2,16.1);
	$pdf->MultiCell(2.4,.5,utf8_decode($Ncomponente[5]),'RB','C',false);
	
	//KM Licitados
		//Faja
	$consulta29 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[0]."'";
	$resultado29 = $conexion_db->query($consulta29);
	$fila29	= $resultado29->fetch_array(MYSQL_ASSOC);
		//Saneamiento
	$consulta30 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[1]."'";
	$resultado30 = $conexion_db->query($consulta30);
	$fila30	= $resultado30->fetch_array(MYSQL_ASSOC);
		//Calzada
	$consulta31 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[2]."'";
	$resultado31 = $conexion_db->query($consulta31);
	$fila31	= $resultado31->fetch_array(MYSQL_ASSOC);
		//Berma
	$consulta32 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[3]."'";
	$resultado32 = $conexion_db->query($consulta32);
	$fila32	= $resultado32->fetch_array(MYSQL_ASSOC);
		//Senalizacion
	$consulta33 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[4]."'";
	$resultado33 = $conexion_db->query($consulta33);
	$fila33	= $resultado33->fetch_array(MYSQL_ASSOC);
		//Demarcacion
	$consulta34 = "select cantidad from kmContratados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[5]."'";
	$resultado34 = $conexion_db->query($consulta34);
	$fila34	= $resultado34->fetch_array(MYSQL_ASSOC);

	$pdf->SetFont('Arial','',7);
	/*$pdf->SetXY(4.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoFaja),'RB','C',false);//Faja
	$pdf->SetXY(4.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoSaneamiento),'RB','C',false);//Saneamiento
	$pdf->SetXY(4.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoCalzada),'RB','C',false);//Calzada
	$pdf->SetXY(4.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoBermas),'RB','C',false);//Berma
	$pdf->SetXY(4.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoSenalizacion),'RB','C',false);//Senalizacion
	$pdf->SetXY(4.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode($kmLicitadoDemarcacion),'RB','C',false);//Demarcacion*/
	
	/*
	//***************** SE QUITO PORQUE SECANO LO PIDIO **********************
	$pdf->SetXY(4.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode($fila29["cantidad"]),'RB','C',false);//Faja
	$pdf->SetXY(4.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode($fila30["cantidad"]),'RB','C',false);//Saneamiento
	$pdf->SetXY(4.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode($fila31["cantidad"]),'RB','C',false);//Calzada
	$pdf->SetXY(4.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode($fila32["cantidad"]),'RB','C',false);//Berma
	$pdf->SetXY(4.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode($fila33["cantidad"]),'RB','C',false);//Senalizacion
	$pdf->SetXY(4.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode($fila34["cantidad"]),'RB','C',false);//Demarcacion
	*/
		
	//KM Vigentes
		//Faja
	$consulta29 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[0]."'";
	$resultado29 = $conexion_db->query($consulta29);
	$fila29	= $resultado29->fetch_array(MYSQL_ASSOC);
	$fajaDescontado = $fila29["cantidad"];
		//Saneamiento
	$consulta30 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[1]."'";
	$resultado30 = $conexion_db->query($consulta30);
	$fila30	= $resultado30->fetch_array(MYSQL_ASSOC);
	$saneamientoDescontado = $fila30["cantidad"];
		//Calzada
	$consulta31 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[2]."'";
	$resultado31 = $conexion_db->query($consulta31);
	$fila31	= $resultado31->fetch_array(MYSQL_ASSOC);
	$calzadaDescontado = $fila31["cantidad"];
		//Berma
	$consulta32 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[3]."'";
	$resultado32 = $conexion_db->query($consulta32);
	$fila32	= $resultado32->fetch_array(MYSQL_ASSOC);
	$bermaDescontado = $fila32["cantidad"];
		//Senalizacion
	$consulta33 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[4]."'";
	$resultado33 = $conexion_db->query($consulta33);
	$fila33	= $resultado33->fetch_array(MYSQL_ASSOC);
	$senalizacionDescontado = $fila33["cantidad"];
		//Demarcacion
	$consulta34 = "select cantidad from kmDescontados where kmBimestre = ".$_SESSION["BIMESTRE_INFORME"]." and codigo = '".$Ccomponente[5]."'";
	$resultado34 = $conexion_db->query($consulta34);
	$fila34	= $resultado34->fetch_array(MYSQL_ASSOC);
	$demarcacionDescontado = $fila34["cantidad"];
	
	/*$pdf->SetXY(6.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteFaja, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteSaneamiento, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteCalzada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteBermas, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteSenalizacion, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($kmVigenteDemarcacion, 3, '.', '')),'RB','C',false);*/

	// SE AGREGO PORQUE SECANO LO PIDIO
	$pdf->SetXY(4.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($fajaDescontado, 3, '.', '')),'RB','C',false);//Faja
	$pdf->SetXY(4.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($saneamientoDescontado, 3, '.', '')),'RB','C',false);//Saneamiento
	$pdf->SetXY(4.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($calzadaDescontado, 3, '.', '')),'RB','C',false);//Calzada
	$pdf->SetXY(4.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($bermaDescontado, 3, '.', '')),'RB','C',false);//Berma
	$pdf->SetXY(4.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($senalizacionDescontado, 3, '.', '')),'RB','C',false);//Senalizacion
	$pdf->SetXY(4.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($demarcacionDescontado, 3, '.', '')),'RB','C',false);//Demarcacion
	// HASTA AQUI - SE AGREGO PORQUE SECANO LO PIDIO

	$pdf->SetXY(6.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($fajaDescontado, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($saneamientoDescontado, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($calzadaDescontado, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($bermaDescontado, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($senalizacionDescontado, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(6.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($demarcacionDescontado, 3, '.', '')),'RB','C',false);
	
	/*Dias*/		
	$fechaInicio = $row2["fechaInicioBimestre"];	//Fecha inicio	
	$fechaInicio_aux = $row2["fechaInicioBimestre"];	//Fecha inicio Auxiliar
	$fechaInicio = date("Y-m-d", strtotime('-1 day', strtotime($fechaInicio)));
	$fechaTermino = $row2["fechaTerminoBimestre"];	//Fecha Termino				
	$fechaInicioFormat = date_create($fechaInicio);	//en formato fecha para la resta
	$fechaTerminoFormat = date_create($fechaTermino); //en formato fecha para la resta					
	$dias = date_diff($fechaInicioFormat,$fechaTerminoFormat);	//Diferencia para el calculo de dias	
	$diferenciaDias = $dias->format("%a");	
	$m = 13.6;
	for($l=0;$l<6;$l++){
		$pdf->SetXY(8.6,$m);		
		$pdf->MultiCell(1.3,.5,utf8_decode($diferenciaDias),'RB','C',false);
		$m = $m + 0.5;
	}
	
	/*Cantidad en conservación  * FP (DIAS/60)*/	
	/*$pdf->SetXY(9.9,13.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteFaja * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,14.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteSaneamiento * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,14.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteCalzada * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,15.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteBermas * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,15.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteSenalizacion * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,16.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($kmVigenteDemarcacion * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);*/
		
	$pdf->SetXY(9.9,13.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($fajaDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,14.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($saneamientoDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,14.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($calzadaDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,15.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($bermaDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,15.6);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($senalizacionDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	$pdf->SetXY(9.9,16.1);	
	$pdf->MultiCell(2.3,.5,utf8_decode(number_format($demarcacionDescontado * ($diferenciaDias/60), 3, '.', '')),'RB','C',false);
	
	/* Factor de cumplimiento */			
	$pdf->SetXY(12.2,13.6);
	if($kmVigenteFaja == 0 and strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoFaja = "SNS";
	}
	else if($kmVigenteFaja > 0 and strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false); 
		$factorCumplimientoFaja = 100;
	}
	else{
		$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoFajaPorcentaje"]),'RB','C',false);
		$factorCumplimientoFaja = $row4["cumplimientoFajaPorcentaje"];
	}
	
	$pdf->SetXY(12.2,14.1);
	if($kmVigenteSaneamiento == 0 and strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoSaneamiento = "SNS";
	}
	else if($kmVigenteSaneamiento > 0 and strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
		$factorCumplimientoSaneamiento = 100;
	}
	else{
		$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoSaneamientoPorcentaje"]),'RB','C',false);
		$factorCumplimientoSaneamiento = $row4["cumplimientoSaneamientoPorcentaje"];
	}
		
	$pdf->SetXY(12.2,14.6);
	if($kmVigenteCalzada == 0 and strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoCalzada = "SNS";
	}
	else if($kmVigenteCalzada > 0 and strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
		$factorCumplimientoCalzada = 100;
	}
	else{
		if (strcmp($row4["cumplimientoCalzadaPorcentaje"],"0") == 0){
			$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
			$factorCumplimientoCalzada = 100;
		}
		else{
			$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoCalzadaPorcentaje"]),'RB','C',false);
			$factorCumplimientoCalzada = $row4["cumplimientoCalzadaPorcentaje"];
		}
	}
		
	$pdf->SetXY(12.2,15.1);
	if($kmVigenteBermas == 0 and strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoBerma = "SNS";
	}
	else if($kmVigenteBermas > 0 and strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
		$factorCumplimientoBerma = 100;
	}
	else{
		$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoBermaPorcentaje"]),'RB','C',false);
		$factorCumplimientoBerma = $row4["cumplimientoBermaPorcentaje"];
	}
	
	$pdf->SetXY(12.2,15.6);
	if($kmVigenteSenalizacion == 0 and strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoSenalizacion = "SNS";
	}
	else if($kmVigenteSenalizacion > 0 and strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
		$factorCumplimientoSenalizacion = 100;
	}
	else{
		$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoSenalizacionPorcentaje"]),'RB','C',false);
		$factorCumplimientoSenalizacion = $row4["cumplimientoSenalizacionPorcentaje"];
	}
	
	$pdf->SetXY(12.2,16.1);
	if($kmVigenteDemarcacion == 0 and strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("SNS"),'RB','C',false);
		$factorCumplimientoDemarcacion = "SNS";
	}
	else if($kmVigenteDemarcacion > 0 and strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0){
		$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
		$factorCumplimientoDemarcacion = 100;
	}
	else{
		if(strcmp($row4["cumplimientoDemarcacionPorcentaje"],"0") == 0){
			$pdf->MultiCell(2.2,.5,utf8_decode("100"),'RB','C',false);
			$factorCumplimientoDemarcacion = 100;
		}
		else{
			$pdf->MultiCell(2.2,.5,utf8_decode($row4["cumplimientoDemarcacionPorcentaje"]),'RB','C',false);
			$factorCumplimientoDemarcacion = $row4["cumplimientoDemarcacionPorcentaje"];
		}
	}	
		
	/* Hasta la recepción anterior*/
	if($_SESSION["BIMESTRE_INFORME"] == 1){
		$anteriorFajaDescontada = number_format(0, 3, ".", "");
		$anteriorSaneamientoDescontada = number_format(0, 3, ".", "");
		$anteriorCalzadaDescontada = number_format(0, 3, ".", "");
		$anteriorBermasDescontada = number_format(0, 3, ".", "");
		$anteriorSenalizacionDescontada = number_format(0, 3, ".", "");
		$anteriorDemarcacionDescontada = number_format(0, 3, ".", "");
	}
	else{
		$anteriorFajaDescontada = number_format(0, 3, ".", "");
		$anteriorSaneamientoDescontada = number_format(0, 3, ".", "");
		$anteriorCalzadaDescontada = number_format(0, 3, ".", "");
		$anteriorBermasDescontada = number_format(0, 3, ".", "");
		$anteriorSenalizacionDescontada = number_format(0, 3, ".", "");
		$anteriorDemarcacionDescontada = number_format(0, 3, ".", "");
		while($row18 = $resultado18->fetch_array(MYSQL_ASSOC)){
			$anteriorFajaDescontada = $anteriorFajaDescontada + $row18["fajaRecepcionAnterior"];
			$anteriorSaneamientoDescontada = $anteriorSaneamientoDescontada + $row18["saneamientoRecepcionAnterior"];
			$anteriorCalzadaDescontada = $anteriorCalzadaDescontada + $row18["calzadaRecepcionAnterior"];
			$anteriorBermasDescontada = $anteriorBermasDescontada + $row18["bermasRecepcionAnterior"];
			$anteriorSenalizacionDescontada = $anteriorSenalizacionDescontada + $row18["senalizacionRecepcionAnterior"];
			$anteriorDemarcacionDescontada = $anteriorDemarcacionDescontada + $row18["demarcacionRecepcionAnterior"];
		}		
	}	
	$pdf->SetXY(14.4,13.6);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorFajaDescontada, 3, ".", "")),'RB','C',false);
	$pdf->SetXY(14.4,14.1);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorSaneamientoDescontada, 3, ".", "")),'RB','C',false);
	$pdf->SetXY(14.4,14.6);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorCalzadaDescontada, 3, ".", "")),'RB','C',false);
	$pdf->SetXY(14.4,15.1);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorBermasDescontada, 3, ".", "")),'RB','C',false);
	$pdf->SetXY(14.4,15.6);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorSenalizacionDescontada, 3, ".", "")),'RB','C',false);
	$pdf->SetXY(14.4,16.1);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($anteriorDemarcacionDescontada, 3, ".", "")),'RB','C',false);
			
	/* En la presente recepción */		
	/*$presenteRecepcion_faja = $kmVigenteFaja * ($diferenciaDias/60);
	$presenteRecepcion_saneamiento = $kmVigenteSaneamiento * ($diferenciaDias/60);
	$presenteRecepcion_calzada = $kmVigenteCalzada * ($diferenciaDias/60);
	$presenteRecepcion_berma = $kmVigenteBermas * ($diferenciaDias/60);
	$presenteRecepcion_senalizacion = $kmVigenteSenalizacion * ($diferenciaDias/60);
	$presenteRecepcion_demarcacion = $kmVigenteDemarcacion * ($diferenciaDias/60);*/
	$presenteRecepcion_faja = $fajaDescontado * ($diferenciaDias/60);
	$presenteRecepcion_saneamiento = $saneamientoDescontado * ($diferenciaDias/60);
	$presenteRecepcion_calzada = $calzadaDescontado * ($diferenciaDias/60);
	$presenteRecepcion_berma = $bermaDescontado * ($diferenciaDias/60);
	$presenteRecepcion_senalizacion = $senalizacionDescontado * ($diferenciaDias/60);
	$presenteRecepcion_demarcacion = $demarcacionDescontado * ($diferenciaDias/60);
		
		
	if(strcmp($factorCumplimientoFaja, "SNS") == 0){
		$presenteRecepcion_faja = 0;
	}
	else{
		$presenteRecepcion_faja = $presenteRecepcion_faja * ($factorCumplimientoFaja/100);		
	}		
	if(strcmp($factorCumplimientoSaneamiento, "SNS") == 0){
		$presenteRecepcion_saneamiento = 0;
	}
	else{
		$presenteRecepcion_saneamiento = $presenteRecepcion_saneamiento * ($factorCumplimientoSaneamiento/100);		
	}
	if(strcmp($factorCumplimientoCalzada, "SNS") == 0){
		$presenteRecepcion_calzada = 0;
	}
	else{
		$presenteRecepcion_calzada = $presenteRecepcion_calzada * ($factorCumplimientoCalzada/100);		
	}
	if(strcmp($factorCumplimientoBerma, "SNS") == 0){
		$presenteRecepcion_berma = 0;
	}
	else{
		$presenteRecepcion_berma = $presenteRecepcion_berma * ($factorCumplimientoBerma/100);		
	}
	if(strcmp($factorCumplimientoSenalizacion, "SNS") == 0){
		$presenteRecepcion_senalizacion = 0;
	}
	else{
		$presenteRecepcion_senalizacion = $presenteRecepcion_senalizacion * ($factorCumplimientoSenalizacion/100);		
	}
	if(strcmp($factorCumplimientoDemarcacion, "SNS") == 0){
		$presenteRecepcion_demarcacion = 0;
	}
	else{
		$presenteRecepcion_demarcacion = $presenteRecepcion_demarcacion * ($factorCumplimientoDemarcacion/100);		
	}
		
	$pdf->SetXY(16.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_faja, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(16.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_saneamiento, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(16.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_calzada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(16.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_berma, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(16.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_senalizacion, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(16.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_demarcacion, 3, '.', '')),'RB','C',false);
	
	//Recepcion a la fecha
	$pdf->SetXY(18.6,13.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_faja+$anteriorFajaDescontada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(18.6,14.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_saneamiento+$anteriorSaneamientoDescontada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(18.6,14.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_calzada+$anteriorCalzadaDescontada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(18.6,15.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_berma+$anteriorBermasDescontada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(18.6,15.6);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_senalizacion+$anteriorSenalizacionDescontada, 3, '.', '')),'RB','C',false);
	$pdf->SetXY(18.6,16.1);	
	$pdf->MultiCell(2,.5,utf8_decode(number_format($presenteRecepcion_demarcacion+$anteriorDemarcacionDescontada, 3, '.', '')),'RB','C',false);
			
	//actualizamos la tabla recepcionAnteriorDescontada
	$consulta7 = "update recepcionAnteriorDescontada set fajaRecepcionAnterior=".number_format($presenteRecepcion_faja, 3, ".", "").
	" where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 
	
	$consulta7 = "update recepcionAnteriorDescontada set saneamientoRecepcionAnterior=".
	number_format($presenteRecepcion_saneamiento, 3, ".", "")." where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 
	
	$consulta7 = "update recepcionAnteriorDescontada set calzadaRecepcionAnterior=".
	number_format($presenteRecepcion_calzada, 3, ".", "")." where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 
	
	$consulta7 = "update recepcionAnteriorDescontada set bermasRecepcionAnterior=".number_format($presenteRecepcion_berma, 3, ".", "").
	" where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 
	
	$consulta7 = "update recepcionAnteriorDescontada set senalizacionRecepcionAnterior=".
	number_format($presenteRecepcion_senalizacion, 3, ".", "")." where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 
	
	$consulta7 = "update recepcionAnteriorDescontada set demarcacionRecepcionAnterior=".
	number_format($presenteRecepcion_demarcacion, 3, ".", "")." where bimestreRecepcionAnterior=".$_SESSION["BIMESTRE_INFORME"];
	$resultado7 = $conexion_db->query($consulta7); 	
	$pdf->SetFont('Arial','',9);
	
	/* Resoluciones */
	$pdf->SetXY(.8, 17.2);
	$m = 18;
	$consulta36 = "select observacionDesafeccionReal from desafeccionreal where exclusionInicial = 0";
	$resultado36 = $conexion_db->query($consulta36);
	if($resultado36->num_rows == 0){		
		$pdf->MultiCell(0,.5,utf8_decode('- Exclusiones de acuerdo a DRV N° ').utf8_decode(strtolower(html_entity_decode($row3["resolucionContrato"]))).utf8_decode('.'),0,'J',false);	
		
	}
	else{
		$resolucion1 = "";
		$resolucion2 = "";
		$resolucion3 = "";
		$i=0;		
		
		$pdf->MultiCell(0,.5,utf8_decode('- Exclusiones de acuerdo a DRV N° '). utf8_decode(strtolower(html_entity_decode($row3["resolucionContrato"]))).utf8_decode('.'),0,'J',false);	
		$pdf->SetXY(.8, 17.8);
		while($fila36 = $resultado36->fetch_array(MYSQL_ASSOC)){
			if($i < 5){
				$resolucion1 = $resolucion1.", N° ".$fila36["observacionDesafeccionReal"];
			}
			else if($i>=5 and $i < 10){
				$resolucion2 = $resolucion2.", N° ".$fila36["observacionDesafeccionReal"];
			}
			else{
				$resolucion3 = $resolucion3.", N° ".$fila36["observacionDesafeccionReal"];
			}	
			$i++;	
		}
		
		if(strcmp($resolucion1,'') != 0){
			$pdf->MultiCell(0,.5,utf8_decode('- Resolución de Exclusión'.$resolucion1.'.'),0,'J',false);	
		}
		if(strcmp($resolucion2,'') != 0){
			$m = $m + .5;
			$pdf->SetXY(.8, $m);
			$pdf->MultiCell(0,.5,utf8_decode('- Resolución de Exclusión'.$resolucion2.'.'),0,'J',false);			
		}
		if(strcmp($resolucion3,'') != 0){
			$m = $m + .5;
			$pdf->SetXY(.8, $m);
			$pdf->MultiCell(0,.5,utf8_decode('- Resolución de Exclusión'.$resolucion3.'.'),0,'J',false);			
		}
	}
		
	/* Fecha bimestre */
	$m = $m + .5;
	$pdf->SetXY(.8, $m);
	$pdf->MultiCell(0,.5,utf8_decode('- Fecha de inicio bimestre: ').html_entity_decode(fecha($fechaInicio_aux)).
	utf8_decode('. Fecha de fin bimestre: ').html_entity_decode(fecha($fechaTermino)).utf8_decode('.'),0,'J',false);
	
	$pdf->SetFont('Arial','',9);
	$pdf->Ln(.5);
	$pdf->SetX(.8);
	$pdf->MultiCell(0,.5,utf8_decode('Se deja constancia de que la presente acta, se encuentra respaldada por el listado de "Segmentos a Inspeccionar", ').
	utf8_decode('los registros de inspección de pago (terreno) y cuadro de incumplimiento de los segmentos solicitados. Estos documentos quedan en poder ').	
	utf8_decode('de la Inspección Fiscal. Para constancia se firma la presente Acta de Recepción Única de pago N° ').
	html_entity_decode($row2["NroPagoBimestre"]).utf8_decode(' en cuatro ejemplares.'),0,'J',false);
	
	//FIRMAS
	$pdf->SetFont('Arial','',8);
	$pdf->Line(3,23.2,8.8,23.2);	//Vialidad 1
	$pdf->SetXY(1,23.3);	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]))),0,'C');	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoUnoVialidadComision"]))),0,'C');	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
	
	$pdf->Line(12.8,23.2,18.6,23.2);	//Vialidad 2		
	$pdf->SetXY(10.8,23.3);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]))),0,'C');	
	$pdf->SetXY(10.8,23.7);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoDosVialidadComision"]))),0,'C');
	$pdf->SetXY(10.8,24.1);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
		
	$pdf->Line(6.9,25.8,14.7,25.8);	//Inspector	
	$pdf->SetXY(0,25.9);
	$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row3["inspectorFiscalContrato"]))),0,'C');
	$pdf->SetXY(0,26.3);
	$pdf->MultiCell(21.6,0.4,utf8_decode('INSPECTOR FISCAL'),0,'C');
	$pdf->SetXY(0,26.7);
	$pdf->MultiCell(21.6,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');			
	
	$pdf->SetFont('Arial','',9);
	$pdf->SetXY(1.7,27.5);				
	$pdf->MultiCell(9.8,0.4,utf8_decode('Se deja constancia que en representación de la empresa asistió:'),0,'C');
	
	$pdf->SetFont('Arial','',8);
	$pdf->Line(6.9,29.6,14.7,29.6);	//Constructora				
	$pdf->SetXY(1,29.7);
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteContructoraComision"]))),0,'C');
	$pdf->SetXY(1,30.1);
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["cargoContructoraComision"]))),0,'C');
	$pdf->SetXY(1,30.5);
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row3["nombreEmpresaConstructoraContrato"]))),0,'C');
	$pdf->SetFont('Arial','',9);
	
	
	//****************************************** GENERAMOS EL INFORME TABLA DE INCUMPLIMIENTO *********************************	
		
	$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina							
	$pdf->SetFont('Arial','B',9);
	$consulta12 = "select * from segmentosSorteados where bimestreSorteado=".$_SESSION["BIMESTRE_INFORME"];
	$resultado12 = $conexion_db->query($consulta12);			
	//Titulo
	$pdf->SetXY(0,4);
	$pdf->Cell(0,0,utf8_decode('INPECCIÓN DE PAGO Nº ').$row2["NroPagoBimestre"],0,0,'C',false);			
	$pdf->SetXY(0,4.5);
	$pdf->Cell(0,0,utf8_decode('TABLA DE INCUMPLIMIENTO'),0,0,'C',false);			
	//Nombre obra
	$pdf->SetXY(3,5.5);
	$pdf->SetFont('Arial','',9);	
	$pdf->MultiCell(15,.5,strtoupper(utf8_decode('"'.html_entity_decode($row["nombreCompletoObra"]).'"')),0,'C',false);	
	//TABLA		
	//Cabecera
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(4.9,8.2);
	$pdf->MultiCell(15,1,utf8_decode('PORCENTAJE DE INCUMPLIMIENTO (%)'),1,'C',false);					
	$pdf->SetXY(1.3,9.2);
	$pdf->MultiCell(1.6,.4,'HOJA DE TERRENO',1,'C',false);			
	$pdf->SetXY(2.9,9.2);
	$pdf->MultiCell(2,.8,'SEGMENTO','TRB','C',false);			
	$pdf->SetXY(4.9,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[0]),'RB','C',false);			
	$pdf->SetXY(7.4,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[1]),'RB','C',false);			
	$pdf->SetXY(9.9,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[2]),'RB','C',false);			
	$pdf->SetXY(12.4,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[3]),'RB','C',false);			
	$pdf->SetXY(14.9,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[4]),'RB','C',false);			
	$pdf->SetXY(17.4,9.2);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ccomponente[5]),'RB','C',false);			
	$pdf->SetXY(4.9,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[0]),'RB','C',false);			
	$pdf->SetXY(7.4,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[1]),'RB','C',false);			
	$pdf->SetXY(9.9,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[2]),'RB','C',false);			
	$pdf->SetXY(12.4,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[3]),'RB','C',false);			
	$pdf->SetXY(14.9,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[4]),'RB','C',false);			
	$pdf->SetXY(17.4,9.6);
	$pdf->MultiCell(2.5,.4,utf8_decode($Ncomponente[5]),'RB','C',false);			
	//Contenido
		//Columna 1
	$pdf->SetFont('Arial','',7);
	$pdf->SetXY(1.3,10);
	$pdf->MultiCell(1.6,.4,utf8_decode('1'),'LRB','C',false);			
	$pdf->SetXY(1.3,10.4);
	$pdf->MultiCell(1.6,.4,utf8_decode('2'),'LRB','C',false);			
	$pdf->SetXY(1.3,10.8);
	$pdf->MultiCell(1.6,.4,utf8_decode('3'),'LRB','C',false);			
	$pdf->SetXY(1.3,11.2);
	$pdf->MultiCell(1.6,.4,utf8_decode('4'),'LRB','C',false);			
	$pdf->SetXY(1.3,11.6);
	$pdf->MultiCell(1.6,.4,utf8_decode('5'),'LRB','C',false);			
	$pdf->SetXY(1.3,12.0);
	$pdf->MultiCell(1.6,.4,utf8_decode('6'),'LRB','C',false);			
	$pdf->SetXY(1.3,12.4);
	$pdf->MultiCell(1.6,.4,utf8_decode('7'),'LRB','C',false);			
	$pdf->SetXY(1.3,12.8);
	$pdf->MultiCell(1.6,.4,utf8_decode('8'),'LRB','C',false);			
	$pdf->SetXY(1.3,13.2);
	$pdf->MultiCell(1.6,.4,utf8_decode('9'),'LRB','C',false);			
	$pdf->SetXY(1.3,13.6);
	$pdf->MultiCell(1.6,.4,utf8_decode('10'),'LRB','C',false);			
				
	$coordenadaY=10;
	$coordenadaY2=10;
		
	while($row12 = $resultado12->fetch_array(MYSQL_ASSOC)){
		//Columna 2
		$pdf->SetXY(2.9,$coordenadaY);
		$pdf->MultiCell(2,.4,utf8_decode(html_entity_decode($row12["numeroSegmentoSorteado"])),'RB','C',false);			
		$coordenadaY = $coordenadaY+0.4;
				
		//Consulta para el resto de la columnas
		$consulta13 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
		$_SESSION["BIMESTRE_INFORME"]." and segmentoIncumplimiento=".$row12["numeroSegmentoSorteado"];
		$resultado13 = $conexion_db->query($consulta13);
				
		//Resto de las columnas
		$coordenadaX = 4.9;				
		while($row13 = $resultado13->fetch_array(MYSQL_ASSOC)){
			$pdf->SetXY($coordenadaX,$coordenadaY2);
			$pdf->MultiCell(2.5,.4,utf8_decode(html_entity_decode($row13["porcentajeIncumplimiento"])),'RB','C',false);											
			$coordenadaX = $coordenadaX + 2.5;
		}
		$coordenadaY2 = $coordenadaY2+0.4;			
	}
			
	//RESULTADO			
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1.3,14);
	$pdf->MultiCell(3.6,.4,'RESULTADO','LRB','C',false);						
			
	//Factor de pago
	$pdf->SetXY(1.3,15.5);	
	$pdf->MultiCell(10,1,utf8_decode('FACTOR DE PAGO (%)'),'1','C',false);			
	$pdf->SetXY(1.3,16.5);
	$pdf->MultiCell(5,0.4,utf8_decode('COMPONENTE INSPECCIONADO'),'LRB','C',false);			
	$pdf->SetXY(6.3,16.5);
	$pdf->MultiCell(5,0.4,utf8_decode('FACTOR DE PAGO'),'RB','C',false);
	$pdf->SetXY(1.3,16.9);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[0]),'LRB','C',false);			
	$pdf->SetXY(1.3,17.3);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[1]),'LRB','C',false);
	$pdf->SetXY(1.3,17.7);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[2]),'LRB','C',false);			
	$pdf->SetXY(1.3,18.1);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[3]),'LRB','C',false);			
	$pdf->SetXY(1.3,18.5);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[4]),'LRB','C',false);			
	$pdf->SetXY(1.3,18.9);
	$pdf->MultiCell(5,0.4,utf8_decode($Ncomponente[5]),'LRB','C',false);			
		
	//Resultado	FAJA
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='FAJA'";
	$resultado14 = $conexion_db->query($consulta14);					
	while($row14=$resultado14->fetch_array(MYSQL_ASSOC))
	{
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0)
		{// and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0){		
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetXY(4.9,14);
	if($cantidadElementos > 0)
	{	 
		$pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); 
	}			
	else{ /*
		if($fajaDescontado > 0){
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}	*/		
	}	
	//Factor de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,16.9);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{ /*
		if($fajaDescontado > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}	*/			
	}				
	
	//Resultado	SANEAMIENTO	
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='SANEAMIENTO'";
	$resultado14 = $conexion_db->query($consulta14);					
	while($row14=$resultado14->fetch_array(MYSQL_ASSOC)){
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0)
		{// and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0){
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}			
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(7.4,14);
	if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
	else{ /*
		if($saneamientoDescontada > 0){
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}*/
	}
	//Factor de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,17.3);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,0.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{ /*
		if($saneamientoDescontada > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}*/
	}			
				
	//Resultado	CALZADA
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='CALZADA'";
	$resultado14 = $conexion_db->query($consulta14);					
	while($row14=$resultado14->fetch_array(MYSQL_ASSOC)){
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0)
		{// and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0){
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}			
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(9.9,14);
	if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
	else{ 
		/*if($calzadaDescontada > 0){
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}*/
	}	
	//Facto de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,17.7);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,0.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{ /*
		if($calzadaDescontada > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}*/
	}
	
	//Resultado	BERMAS
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='BERMA'";
	$resultado14 = $conexion_db->query($consulta14);

	while($row14=$resultado14->fetch_array(MYSQL_ASSOC))
	{
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0)// and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0)
		{
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}			
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(12.4,14);
	if($cantidadElementos > 0)
	{ 
		$pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); 
	}			
	else
	{ 
		/*if($bermaDescontada > 0)
		{
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}*/
	}

	//Facto de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,18.1);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,0.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{
		/*if($bermaDescontada > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}	*/
	}
			
	//Resultado	SENALIZACION
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='SENALIZACION'";
	$resultado14 = $conexion_db->query($consulta14);					
	while($row14=$resultado14->fetch_array(MYSQL_ASSOC)){
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0 )
		{//and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0){
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}			
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(14.9,14);
	if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
	else{ /*
		if($senalizacionDescontada > 0){
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}*/
	}				
	//Factor de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,18.5);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,0.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{/*
		if($senalizacionDescontada > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}		*/
	}
						
	//Resultado	DEMARCACION
	$cantidadElementos = 0;
	$sumaCantidadElementos = 0;
	$consulta14 = "select porcentajeIncumplimiento from incumplimiento where bimestreIncumplimiento=".
	$_SESSION["BIMESTRE_INFORME"]." and componenteIncumplimiento='DEMARCACION'";
	$resultado14 = $conexion_db->query($consulta14);					
	while($row14=$resultado14->fetch_array(MYSQL_ASSOC)){
		if(strcmp($row14["porcentajeIncumplimiento"],'-')!=0)
		{// and strcmp($row14["porcentajeIncumplimiento"],'SNS')!=0){
			$cantidadElementos++;
			$sumaCantidadElementos = $sumaCantidadElementos+$row14["porcentajeIncumplimiento"];
		}
	}
	//% de incumplimiento
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(17.4,14);
	if($cantidadElementos > 0){ $pdf->MultiCell(2.5,.4,utf8_decode(round($sumaCantidadElementos/$cantidadElementos)),'RB','C',false); }			
	else{/*
		if($demarcacionDescontada > 0){
			$pdf->MultiCell(2.5,.4,utf8_decode('0'),'RB','C',false);
		}
		else{*/
			$pdf->MultiCell(2.5,.4,utf8_decode('SNS'),'RB','C',false);/*
		}*/
	}
	//Facto de pago
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(6.3,18.9);
	if($cantidadElementos > 0){ $pdf->MultiCell(5,0.4,utf8_decode(100 - round($sumaCantidadElementos/$cantidadElementos)),'LRB','C',false); }			
	else{/*
		if($demarcacionDescontada > 0){
			$pdf->MultiCell(5,.4,utf8_decode('100'),'LRB','C',false); 
		}
		else{*/
			$pdf->MultiCell(5,.4,utf8_decode('SNS'),'LRB','C',false); /*
		}	*/	
	}
				
	//RESUMEN CANTIDAD RECEPCIONADA
	//Titulo
	$pdf->Ln(1);
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetX(1.3);
	$pdf->MultiCell(5,1.4,utf8_decode('ELEMENTO O COMPONENTE'),1,'C',false);
	$pdf->SetXY(6.3,20.3);	
	$pdf->MultiCell(3,.7,utf8_decode('CANTIDAD EN CONSERVACIÓN'),'TRB','C',false);
	$pdf->SetXY(9.3,20.3);
	$pdf->MultiCell(3.1,1.4,utf8_decode('FACTOR DE CUMPL.'),'TRB','C',false);
	$pdf->SetXY(12.4,20.3);
	$pdf->MultiCell(3,0.467,utf8_decode('CANTIDAD EN CONSERVACION * FP (DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(15.4,20.3);
	$pdf->MultiCell(4.5,1.4,utf8_decode('CANTIDAD RECEPCIONADA'),'TRB','C',false);
	//codigos y elemento
	$pdf->SetXY(1.3,21.7);
	$pdf->SetFont('Arial','B',7);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[0]),'LRB','C',false);
	$pdf->SetXY(2.7,21.7);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[0]),'RB','C',false);
	$pdf->SetXY(1.3,22.2);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[1]),'LRB','C',false);
	$pdf->SetXY(2.7,22.2);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[1]),'RB','C',false);
	$pdf->SetXY(1.3,22.7);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[2]),'LRB','C',false);
	$pdf->SetXY(2.7,22.7);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[2]),'RB','C',false);
	$pdf->SetXY(1.3,23.2);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[3]),'LRB','C',false);
	$pdf->SetXY(2.7,23.2);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[3]),'RB','C',false);
	$pdf->SetXY(1.3,23.7);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[4]),'LRB','C',false);	
	$pdf->SetXY(2.7,23.7);
	$pdf->SetFont('Arial','B',6);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[4]),'RB','C',false);	
	$pdf->SetXY(1.3,24.2);
	$pdf->SetFont('Arial','B',7);
	$pdf->MultiCell(1.5,.5,utf8_decode($Ccomponente[5]),'LRB','C',false);
	$pdf->SetXY(2.7,24.2);
	$pdf->MultiCell(3.6,.5,utf8_decode($Ncomponente[5]),'RB','C',false);
	
	//Cantidad en conservacion
	$pdf->SetFont('Arial','',8);			
	$m = 21.7;
	$pdf->SetXY(6.3,$m);	
	$pdf->MultiCell(3,.5,utf8_decode($fajaDescontado),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($saneamientoDescontado),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($calzadaDescontado),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($bermaDescontado),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($senalizacionDescontado),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($demarcacionDescontado),'RB','C',false);
	/*$m = 21.7;
	$pdf->SetXY(6.3,$m);	
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteFaja),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteSaneamiento),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteCalzada),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteBermas),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteSenalizacion),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(6.3,$m);
	$pdf->MultiCell(3,.5,utf8_decode($kmVigenteDemarcacion),'RB','C',false);*/
		
		//Factor de cumplimiento	
	$pdf->SetXY(9.3,21.7);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoFaja),'RB','C',false);
	$pdf->SetXY(9.3,22.2);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoSaneamiento),'RB','C',false);
	$pdf->SetXY(9.3,22.7);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoCalzada),'RB','C',false);
	$pdf->SetXY(9.3,23.2);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoBerma),'RB','C',false);
	$pdf->SetXY(9.3,23.7);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoSenalizacion),'RB','C',false);
	$pdf->SetXY(9.3,24.2);
	$pdf->MultiCell(3.1,.5,utf8_decode($factorCumplimientoDemarcacion),'RB','C',false);
		
	//CANTIDAD EN CONSERVACION * FP (DIAS/60)
	/*$m = 21.7;
	$pdf->SetXY(12.4,$m);	
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteFaja * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteSaneamiento * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteCalzada * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteBermas * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteSenalizacion * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($kmVigenteDemarcacion * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);*/	
		
	$m = 21.7;
	$pdf->SetXY(12.4,$m);	
	$pdf->MultiCell(3,.5,utf8_decode(number_format($fajaDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($saneamientoDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($calzadaDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($bermaDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($senalizacionDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);
	$m = $m + 0.5;
	$pdf->SetXY(12.4,$m);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($demarcacionDescontado * ($diferenciaDias/60), 3, ".", "")),'RB','C',false);	
			
	/* CANTIDAD RECEPCIONADA */
	$consulta19 = "select * from recepcionAnteriorDescontada where bimestreRecepcionAnterior = ".$_SESSION["BIMESTRE_INFORME"];
	$resultado19 = $conexion_db->query($consulta19);
	$fila19 = $resultado19->fetch_array(MYSQL_ASSOC);
	
	$pdf->SetXY(15.4,21.7);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["fajaRecepcionAnterior"]),'RB','C',false);
	$pdf->SetXY(15.4,22.2);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["saneamientoRecepcionAnterior"]),'RB','C',false);
	$pdf->SetXY(15.4,22.7);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["calzadaRecepcionAnterior"]),'RB','C',false);
	$pdf->SetXY(15.4,23.2);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["bermasRecepcionAnterior"]),'RB','C',false);
	$pdf->SetXY(15.4,23.7);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["senalizacionRecepcionAnterior"]),'RB','C',false);
	$pdf->SetXY(15.4,24.2);
	$pdf->MultiCell(4.5,.5,utf8_decode($fila19["demarcacionRecepcionAnterior"]),'RB','C',false);
	/*FIN CANTIDAD RECEPCIONADA ACTUAL LIMARI NORTE Y SUR*/
	
	//Firmas
	$pdf->SetFont('Arial','',9);
	$pdf->SetXY(1,28.2);	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteUnoVialidadComision"]))),0,'C');	
	$pdf->SetXY(1,28.6);	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoUnoVialidadComision"]))),0,'C');	
	$pdf->SetXY(1,29);	
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
			
	$pdf->SetXY(10.8,28.2);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["integranteDosVialidadComision"]))),0,'C');	
	$pdf->SetXY(10.8,28.6);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["dptoDosVialidadComision"]))),0,'C');
	$pdf->SetXY(10.8,29);
	$pdf->MultiCell(9.8,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionDosVialidadComision"]))),0,'C');
	
	//Linea de firma
	$pdf->Line(3,28.1,8.8,28.1);		//Vialidad 1
	$pdf->Line(12.8,28.1,18.6,28.1);	//Vialidad 2	

//----------------------------------------------------------AQUUIII

//******************************************* CREAMOS RESUMEN CANTIDAD CONTRATADAS *******************************************
		
	$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina									
	//PERIODO REVISION
	$j = 4;	
	$pdf->SetFont('Arial','B',9);	
	$pdf->SetXY(0,$j);
	$pdf->MultiCell(0,.5,utf8_decode('INSPECCIÓN DE PAGO N° '.html_entity_decode($row2["NroPagoBimestre"]).''),0,'C',false);
	$pdf->SetXY(0,$j+.5);
	$pdf->MultiCell(0,.5,utf8_decode('PERIODO DE REVISIÓN'),0,'C',false);	
	
	//NOMBRE DE LA OBRA
	$j = $j + 1.5;
	$pdf->SetFont('Arial','',9);	
	$pdf->SetXY(3,$j);
	$pdf->MultiCell(15,.5,strtoupper(utf8_decode('"'.html_entity_decode($row["nombreCompletoObra"]).'"')),0,'C',false);		
	
	//FECHA PERIODO REVISION
	$j = $j + 1.5;
	$pdf->SetFont('Arial','',8);		
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.4,utf8_decode('PERIODO ACTUAL DESDE EL '.html_entity_decode(date("d-m-Y",strtotime($row2["fechaInicioBimestre"])))).' HASTA EL '.
	utf8_decode(html_entity_decode(date("d-m-Y",strtotime($row2["fechaTerminoBimestre"])))),0,'L',false);			
	$j = $j + 1
	;
	/*VALIDAMOS TAMAÑO DE HOJA PARA FAJA*/
	$cantidadElementosFaja = count($codigo_ruta_faja);
	$J_TOTAL_FAJA = $j + 1 + 0.35 + 0.35 + ($cantidadElementosFaja*0.35);
	
	if($J_TOTAL_FAJA >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA FAJA*/
	
	//FAJA VIAL	
		//Titulo faja
	$pdf->SetFont('Arial','B',8);		
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[0].'. '.$Ncomponente[0]),0,'L',false);	
		//Titulo cuadro faja
	$j = $j + 1;	
	$pdf->SetFont('Arial','B',8);			
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);	
	
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);	
	
		//Rol faja
	$j = $j + 0.35;	
	$k = $j;	//Auxiliar inicio de las columnas
	$pdf->SetFont('Arial','',7);
	for($i=0;$i<count($codigo_ruta_faja);$i++){				
		if(strlen($codigo_ruta_faja[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_faja[$i]),'LRB','C',false);	
		$j = $j+0.35;
	}
	
		//Longitud faja
	$j=$k;
	for($i=0;$i<count($longitudMenosUnoFaja);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoFaja[$i],3)),'RB','C',false);		
		$j = $j+0.35;
	}
		//Desde faja
	$j=$k;
	for($i=0;$i<count($fecha_desde_faja);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_faja[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta faja
	$j=$k;
	for($i=0;$i<count($fecha_hasta_faja);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_faja[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias faja
	$j=$k;
	for($i=0;$i<count($dia_faja);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_faja[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo faja
	$j=$k;
	for($i=0;$i<count($factor_faja);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_faja[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad conservación faja
	$j=$k;
	$suma_cantidad_faja = 0;
	for($i=0;$i<count($cantidad_faja);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_faja[$i],3)),'RB','C',false);		
		$j = $j+0.35;
		$suma_cantidad_faja = $suma_cantidad_faja + $cantidad_faja[$i];
	}
		//Factor de cumplimiento faja
	$j=$k;	
	for($i=0;$i<count($cantidad_faja);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0 and $cantidad_faja[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0 and $cantidad_faja[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoFajaPorcentaje"]),'RB','C',false);		
		}		
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_faja2 = 0;
	for($i=0;$i<count($cantidad_faja);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0 and $cantidad_faja[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_faja2 = $suma_cantidad_faja2 + 0;			
		}
		else if(strcmp($row4["cumplimientoFajaPorcentaje"],"SNS") == 0 and $cantidad_faja[$i] > 0){	//0
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_faja[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_faja2 = $suma_cantidad_faja2 + ($cantidad_faja[$i] * (100 / 100));			
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_faja[$i] * ($row4["cumplimientoFajaPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_faja2 = $suma_cantidad_faja2 + ($cantidad_faja[$i] * ($row4["cumplimientoFajaPorcentaje"] / 100));			
		}
		$j = $j+0.35;		
	}		
		//Total conservacion faja
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LB','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_faja2,3)),'LRB','C',false);		
	$j=$j+1;
	
	/*VALIDAMOS TAMAÑO DE HOJA PARA SANEAMIENTO*/
	$cantidadElementosSaneamiento = count($codigo_ruta_saneamiento);
	$J_TOTAL_SANEAMIENTO = $j + 1 + 0.35 + 0.35 + ($cantidadElementosFaja*0.35);
	
	if($J_TOTAL_SANEAMIENTO >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA SANEAMIENTO*/
	
	//SANEAMIENTO	
		//Titulo saneamiento	
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[1].'. '.$Ncomponente[1]),0,'L',false);
	
		//Titulo cuadro saneamiento
	$j = $j+1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);		
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);	
	
		//Rol saneamiento
	$j = $j+0.35;
	$k = $j;
	$pdf->SetFont('Arial','',7);	
	for($i=0;$i<count($codigo_ruta_saneamiento);$i++){
		if(strlen($codigo_ruta_saneamiento[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_saneamiento[$i]),'LRB','C',false);	
		$j = $j + 0.35;
	}
		//Longitud Saneamiento	
	$j = $k;
	for($i=0;$i<count($longitudMenosUnoSaneamiento);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoSaneamiento[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Desde saneamiento
	$j =$k;
	for($i=0;$i<count($fecha_desde_saneamiento);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_saneamiento[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta saneamiento
	$j = $k;
	for($i=0;$i<count($fecha_hasta_saneamiento);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_saneamiento[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias saneamiento
	$j = $k;
	for($i=0;$i<count($dia_saneamiento);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_saneamiento[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo saneamiento
	$j = $k;
	for($i=0;$i<count($factor_saneamiento);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_saneamiento[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad conservacion saneamiento
	$j = $k;
	$suma_cantidad_saneamiento = 0;
	for($i=0;$i<count($cantidad_saneamiento);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_saneamiento[$i],3)),'RB','C',false);
		$suma_cantidad_saneamiento = $suma_cantidad_saneamiento + $cantidad_saneamiento[$i];
		$j = $j+0.35;
	}
		//Factor de cumplimiento saneamiento
	$j=$k;	
	for($i=0;$i<count($cantidad_saneamiento);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0 and $cantidad_saneamiento[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0 and $cantidad_saneamiento[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoSaneamientoPorcentaje"]),'RB','C',false);		
		}		
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_saneamiento2 = 0;
	for($i=0;$i<count($cantidad_saneamiento);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0 and $cantidad_saneamiento[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_saneamiento2 = $suma_cantidad_saneamiento2 + 0;		
		}
		else if(strcmp($row4["cumplimientoSaneamientoPorcentaje"],"SNS") == 0 and $cantidad_saneamiento[$i] > 0){	//0
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_saneamiento[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_saneamiento2 = $suma_cantidad_saneamiento2 + ($cantidad_saneamiento[$i] * (100 / 100));
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_saneamiento[$i] * ($row4["cumplimientoSaneamientoPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_saneamiento2 = $suma_cantidad_saneamiento2 + ($cantidad_saneamiento[$i] * ($row4["cumplimientoSaneamientoPorcentaje"] / 100));
		}
		$j = $j+0.35;		
	}	
		//Total conservación saneamiento
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LB','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_saneamiento2,3)),'LRB','C',false);		
	$j = $j + 1;	
	
	/*VALIDAMOS TAMAÑO DE HOJA PARA CALZADA*/
	$cantidadElementosCalzada = count($codigo_ruta_calzada);
	$J_TOTAL_CALZADA = $j + 1 + 0.35 + 0.35 + ($cantidadElementosCalzada*0.35);
	
	if($J_TOTAL_CALZADA >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA CALZADA*/
	//CALZADA	
		//Titulo calzada
	$pdf->SetFont('Arial','B',8);		
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[2].'. '.$Ncomponente[2]),0,'L',false);
	
		//Titulo cuadro calzada
	$j = $j+1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);		
	
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);	
		
		//Rol calzada
	$pdf->SetFont('Arial','',7);	
	$j = $j+0.35;
	$k = $j;
	for($i=0;$i<count($codigo_ruta_calzada);$i++){
		if(strlen($codigo_ruta_calzada[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_calzada[$i]),'LRB','C',false);	
		$j = $j + 0.35;
	}
		//Longitud calzada
	$j = $k;
	for($i=0;$i<count($longitudMenosUnoCalzada);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoCalzada[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Desde calzada
	$j=$k;
	for($i=0;$i<count($fecha_desde_calzada);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_calzada[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta calzada
	$j=$k;
	for($i=0;$i<count($fecha_hasta_calzada);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_calzada[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias calzada
	$j=$k;
	for($i=0;$i<count($dia_calzada);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_calzada[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo calzada
	$j=$k;
	for($i=0;$i<count($factor_calzada);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_calzada[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad conservacion calzada
	$j=$k;
	$suma_cantidad_calzada = 0;
	for($i=0;$i<count($cantidad_calzada);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_calzada[$i],3)),'RB','C',false);
		$suma_cantidad_calzada = $suma_cantidad_calzada + $cantidad_calzada[$i];
		$j = $j+0.35;
	}
		//Factor de cumplimiento calzada
	$j=$k;	
	for($i=0;$i<count($cantidad_calzada);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0 and $cantidad_calzada[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0 and $cantidad_calzada[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoCalzadaPorcentaje"]),'RB','C',false);		
		}		
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_calzada2 = 0;
	for($i=0;$i<count($cantidad_calzada);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0 and $cantidad_calzada[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_calzada2 = $suma_cantidad_calzada2 + 0;			
		}
		else if(strcmp($row4["cumplimientoCalzadaPorcentaje"],"SNS") == 0 and $cantidad_calzada[$i] > 0){	//100
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_calzada[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_calzada2 = $suma_cantidad_calzada2 + ($cantidad_calzada[$i] * (100 / 100));			
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_calzada[$i] * ($row4["cumplimientoCalzadaPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_calzada2 = $suma_cantidad_calzada2 + ($cantidad_calzada[$i] * ($row4["cumplimientoCalzadaPorcentaje"] / 100));	
		}
		$j = $j+0.35;		
	}	
	
		//Total conservacion calzada
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LB','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_calzada2,3)),'LRB','C',false);	
	$j = $j + 1;
	
	/*VALIDAMOS TAMAÑO DE HOJA PARA BERMAS*/
	$cantidadElementosBermas = count($codigo_ruta_bermas);
	$J_TOTAL_BERMAS = $j + 1 + 0.35 + 0.35 + ($cantidadElementosBermas*0.35);
	
	if($J_TOTAL_BERMAS >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA BERMAS*/
	//BERMAS	
		//Titulo berma
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[3].'. '.$Ncomponente[3]),0,'L',false);	
		//Titulo cuadro berma		
	$j = $j+1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);		
	
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);
		
		//Rol berma
	$pdf->SetFont('Arial','',8);	
	$j = $j + 0.35;
	$k = $j;
	for($i=0;$i<count($codigo_ruta_bermas);$i++){
		if(strlen($codigo_ruta_bermas[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_bermas[$i]),'LRB','C',false);	
		$j = $j + 0.35;
	}
		//Longitud berma
	$j=$k;
	for($i=0;$i<count($longitudMenosUnoBermas);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoBermas[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Desde berma
	$j=$k;
	for($i=0;$i<count($fecha_desde_bermas);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_bermas[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta berma
	$j=$k;
	for($i=0;$i<count($fecha_hasta_bermas);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_bermas[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias berma
	$j=$k;
	for($i=0;$i<count($dia_bermas);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_bermas[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo berma
	$j=$k;
	for($i=0;$i<count($factor_bermas);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_bermas[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad de conservacion berma
	$j=$k;
	$suma_cantidad_berma = 0;
	for($i=0;$i<count($cantidad_bermas);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_bermas[$i],3)),'RB','C',false);
		$suma_cantidad_berma = $suma_cantidad_berma + $cantidad_bermas[$i];
		$j = $j+0.35;	
	}	
		//Factor de cumplimiento berma
	$j=$k;	
	for($i=0;$i<count($cantidad_bermas);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0 and $cantidad_bermas[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0 and $cantidad_bermas[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoBermaPorcentaje"]),'RB','C',false);		
		}		
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_berma2 = 0;
	for($i=0;$i<count($cantidad_bermas);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0 and $cantidad_bermas[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_berma2 = $suma_cantidad_berma2 + 0;
		}
		else if(strcmp($row4["cumplimientoBermaPorcentaje"],"SNS") == 0 and $cantidad_bermas[$i] > 0){	//100
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_bermas[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_berma2 = $suma_cantidad_berma2 + ($cantidad_bermas[$i] * (100 / 100));
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_bermas[$i] * ($row4["cumplimientoBermaPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_berma2 = $suma_cantidad_berma2 + ($cantidad_bermas[$i] * ($row4["cumplimientoBermaPorcentaje"] / 100));
		}		
		$j = $j+0.35;		
	}		
		//Total conservacion berma
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LB','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_berma2,3)),'LRB','C',false);	
	$j = $j + 1;
	
	/*VALIDAMOS TAMAÑO DE HOJA PARA SEÑALIZACION*/
	$cantidadElementosSenalizacion = count($codigo_ruta_senalizacion);
	$J_TOTAL_SENALIZACION = $j + 1 + 0.35 + 0.35 + ($cantidadElementosSenalizacion*0.35);
	
	if($J_TOTAL_SENALIZACION >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA SEÑALIZACION*/
	//SEÑALIZACION	
		//Titulo senalizacion
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[4].'. '.$Ncomponente[4]),0,'L',false);		
		//Titulo cuadro senalizacion	
	$j = $j+1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);		
	
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);
	
		//Rol señalizacion	
	$pdf->SetFont('Arial','',7);
	$j = $j+0.35;
	$k = $j;	
	for($i=0;$i<count($codigo_ruta_senalizacion);$i++){
		if(strlen($codigo_ruta_senalizacion[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_senalizacion[$i]),'LRB','C',false);	
		$j = $j + 0.35;
	}
		//Longitud señalizacion
	$j=$k;
	for($i=0;$i<count($longitudMenosUnoSenalizacion);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoSenalizacion[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Desde señalizacion
	$j=$k;
	for($i=0;$i<count($fecha_desde_senalizacion);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_senalizacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta señalizacion
	$j=$k;
	for($i=0;$i<count($fecha_hasta_senalizacion);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_senalizacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias señalizacion
	$j=$k;
	for($i=0;$i<count($dia_senalizacion);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_senalizacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo señalizacion
	$j=$k;
	for($i=0;$i<count($factor_senalizacion);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_senalizacion[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad en conservacion señalizacion
	$j=$k;
	$suma_cantidad_senalizacion = 0;
	for($i=0;$i<count($cantidad_senalizacion);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_senalizacion[$i],3)),'RB','C',false);
		$suma_cantidad_senalizacion = $suma_cantidad_senalizacion + $cantidad_senalizacion[$i];
		$j = $j+0.35;
	}
		//Factor de cumplimiento señalizacion
	$j=$k;	
	for($i=0;$i<count($cantidad_senalizacion);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0 and $cantidad_senalizacion[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0 and $cantidad_senalizacion[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoSenalizacionPorcentaje"]),'RB','C',false);		
		}		
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_senalizacion2 = 0;
	for($i=0;$i<count($cantidad_senalizacion);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0 and $cantidad_senalizacion[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_senalizacion2 = $suma_cantidad_senalizacion2 + 0;
		}
		else if(strcmp($row4["cumplimientoSenalizacionPorcentaje"],"SNS") == 0 and $cantidad_senalizacion[$i] > 0){	//100
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_senalizacion[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_senalizacion2 = $suma_cantidad_senalizacion2 + ($cantidad_senalizacion[$i] * (100 / 100));
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_senalizacion[$i] * ($row4["cumplimientoSenalizacionPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_senalizacion2 = $suma_cantidad_senalizacion2 + ($cantidad_senalizacion[$i] * ($row4["cumplimientoSenalizacionPorcentaje"] / 100));
		}
		$j = $j+0.35;		
	}
		//Total conservacion señalizacion
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LB','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_senalizacion2,3)),'LRB','C',false);	
	$j = $j + 1;
	
	/*VALIDAMOS TAMAÑO DE HOJA PARA DEMARCACION*/
	$cantidadElementosDemarcacion = count($codigo_ruta_demarcacion);
	$J_TOTAL_DEMARCACION = $j + 1 + 0.35 + 0.35 + ($cantidadElementosDemarcacion*0.35);
	
	if($J_TOTAL_DEMARCACION >= 30){
		$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina								
		$j=4.5;
	}
	/*FIN VALIDAMOS TAMAÑO DE HOJA PARA DEMARCACION*/
	//DEMARCACION	
		//Titulo demarcacion
	$pdf->SetFont('Arial','B',8);	
	$pdf->SetXY(1,$j);
	$pdf->MultiCell(0,.3,utf8_decode($Ccomponente[5].'. '.$Ncomponente[5]),0,'L',false);	
		//Titulo cuadro demarcacion
	$j = $j+1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(1.5,.7,utf8_decode('ROL'),1,'C',false);
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('LONGITUD EN N.S.'),'TRB','C',false);
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(5,.35,utf8_decode('PERIODO BAJO N.S.'),'TRB','C',false);
	$pdf->SetXY(10.5,$j);
	$pdf->MultiCell(2.2,.35,utf8_decode('FACT. PLAZO (FP = DIAS/60)'),'TRB','C',false);
	$pdf->SetXY(12.7,$j);
	$pdf->MultiCell(2.6,.35,utf8_decode('CANTIDAD CONSERVACIÓN'),'TRB','C',false);	
	$pdf->SetXY(15.3,$j);
	$pdf->MultiCell(2,.35,utf8_decode('FACTOR DE CUMP. (%)'),'TRB','C',false);	
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode('TOTAL A PAGAR (KM)'),'TRB','C',false);		
	
	$j= $j + 0.35;
	$pdf->SetXY(2.5,$j);
	$pdf->MultiCell(3,.35,utf8_decode('KM'),'RB','C',false);		
	$pdf->SetXY(5.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('DESDE'),'RB','C',false);
	$pdf->SetXY(7.5,$j);
	$pdf->MultiCell(2,.35,utf8_decode('HASTA'),'RB','C',false);
	$pdf->SetXY(9.5,$j);
	$pdf->MultiCell(1,.35,utf8_decode('DIAS'),'RB','C',false);		
		//Rol demarcacion		
	$pdf->SetFont('Arial','',7);
	$j = $j+0.35;
	$k = $j;		
	for($i=0;$i<count($codigo_ruta_demarcacion);$i++){
		if(strlen($codigo_ruta_demarcacion[$i]) > 8){ $pdf->SetFont('Arial','',5); }
		else{ $pdf->SetFont('Arial','',7); }
		$pdf->SetXY(1,$j);
		$pdf->MultiCell(1.5,.35,utf8_decode($codigo_ruta_demarcacion[$i]),'LRB','C',false);	
		$j = $j + 0.35;
	}
		//Longitud demarcacion
	$j=$k;
	for($i=0;$i<count($longitudMenosUnoDemarcacion);$i++){
		$pdf->SetXY(2.5,$j);
		$pdf->MultiCell(3,.35,utf8_decode(number_format($longitudMenosUnoDemarcacion[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Desde demarcacion
	$j=$k;
	for($i=0;$i<count($fecha_desde_demarcacion);$i++){
		$pdf->SetXY(5.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_desde_demarcacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Hasta demarcacion
	$j=$k;
	for($i=0;$i<count($fecha_hasta_demarcacion);$i++){
		$pdf->SetXY(7.5,$j);
		$pdf->MultiCell(2,.35,utf8_decode($fecha_hasta_demarcacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Dias demarcacion
	$j=$k;
	for($i=0;$i<count($dia_demarcacion);$i++){
		$pdf->SetXY(9.5,$j);
		$pdf->MultiCell(1,.35,utf8_decode($dia_demarcacion[$i]),'RB','C',false);
		$j = $j+0.35;
	}
		//Factor de plazo demarcacion
		$j=$k;
	for($i=0;$i<count($factor_demarcacion);$i++){
		$pdf->SetXY(10.5,$j);
		$pdf->MultiCell(2.2,.35,utf8_decode(number_format($factor_demarcacion[$i],3)),'RB','C',false);
		$j = $j+0.35;
	}
		//Cantidad conservacion demarcacion
	$j=$k;
	$suma_cantidad_demarcacion = 0;
	for($i=0;$i<count($cantidad_demarcacion);$i++){
		$pdf->SetXY(12.7,$j);
		$pdf->MultiCell(2.6,.35,utf8_decode(number_format($cantidad_demarcacion[$i],3)),'RB','C',false);
		$suma_cantidad_demarcacion = $suma_cantidad_demarcacion + $cantidad_demarcacion[$i];
		$j = $j+0.35;		
	}
		//Factor de cumplimiento demarcacion
	$j=$k;	
	for($i=0;$i<count($cantidad_demarcacion);$i++){
		$pdf->SetXY(15.3,$j);
		if(strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0 and $cantidad_demarcacion[$i] == 0){
			$pdf->MultiCell(2,.35,utf8_decode("SNS"),'RB','C',false);		
		}
		else if(strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0 and $cantidad_demarcacion[$i] > 0){
			$pdf->MultiCell(2,.35,utf8_decode("100"),'RB','C',false);		
		}
		else{
			$pdf->MultiCell(2,.35,utf8_decode($row4["cumplimientoDemarcacionPorcentaje"]),'RB','C',false);		
		}			
		$j = $j+0.35;		
	}
		//Total a pagar (KM) - Cantidad conservacion * factor de cumplimiento
	$j=$k;	
	$suma_cantidad_demarcacion2 = 0;
	for($i=0;$i<count($cantidad_demarcacion);$i++){
		$pdf->SetXY(17.3,$j);		
		if(strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0 and $cantidad_demarcacion[$i] == 0){	//SNS
			$pdf->MultiCell(3,.35,utf8_decode(number_format(0,3)),'RB','C',false);		
			$suma_cantidad_demarcacion2 = $suma_cantidad_demarcacion2 + 0;		
		}
		else if(strcmp($row4["cumplimientoDemarcacionPorcentaje"],"SNS") == 0 and $cantidad_demarcacion[$i] > 0){	//100
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_demarcacion[$i] * (100 / 100),3)),'RB','C',false);		
			$suma_cantidad_demarcacion2 = $suma_cantidad_demarcacion2 + ($cantidad_demarcacion[$i] * (100 / 100));		
		}
		else{	//row
			$pdf->MultiCell(3,.35,utf8_decode(number_format($cantidad_demarcacion[$i] * ($row4["cumplimientoDemarcacionPorcentaje"] / 100),3)),'RB','C',false);		
			$suma_cantidad_demarcacion2 = $suma_cantidad_demarcacion2 + ($cantidad_demarcacion[$i] * ($row4["cumplimientoDemarcacionPorcentaje"] / 100));		
		}
		$j = $j+0.35;		
	}		
		//Total conservacion demarcacion
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(1,$j);	
	$pdf->MultiCell(16.3,.35,utf8_decode("TOTAL"),'LBR','R',false);
	$pdf->SetXY(17.3,$j);
	$pdf->MultiCell(3,.35,utf8_decode(number_format($suma_cantidad_demarcacion2,3)),'LRB','C',false);		

	//********************************** GENERAMOS LA MINUTA INCLUIDO LOS CAMINOS CON CERO ******************************************
	
	//Agrega una pagina y su fuente
	$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina							
	$pdf->SetFont('Arial','B',10);
	$y = 4;
	
	//Titulo
	$pdf->SetXY(0,$y);
	$pdf->MultiCell(0,.5,utf8_decode('MINUTA'),0,'C',false);	
	$pdf->SetFont('Arial','B',8);
	
	//Nombre de la obra	
	$y = $y + 1;
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(3,.5,utf8_decode('CONTRATO:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(4,$y);
	$pdf->MultiCell(0,.5,strtoupper(utf8_decode('"'.html_entity_decode($row["nombreCompletoObra"]).'"')),0,'J',false);
	
	//Contratista
	$y = $y + 1.5;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(3,.5,utf8_decode('CONTRATISTA:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(4,$y);
	$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode('EMPRESA '.html_entity_decode(implode(" ",$nombreArray)))),0,'J',false);
	
	//Resolucion
	$y = $y + 1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(7,.5,utf8_decode('OBRA CONTRATADA SEGÚN RESOLUCIÓN:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(8,$y);
	$pdf->MultiCell(12.5,.5,utf8_decode('RES D.R.V. N° '.strtoupper(html_entity_decode($row3["resolucionContrato"]))),0,'J',false);
	
	//Inspeccion de pago
	$y = $y + 1.5;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(0,.5,utf8_decode('INSPECCIÓN DE PAGO N° '.html_entity_decode($row2["NroPagoBimestre"]).''),0,'C',false);
	$pdf->SetFont('Arial','',8);
	$y = $y + 0.5;
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(0,.5,utf8_decode('(Conservación por nivel de servicio)'),0,'C',false);
		
	//Cabecera tabla
	$pdf->SetFont('Arial','B',8);
	$y = $y + 1;	
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,1,utf8_decode('COMPONENTE'),'LTB','C',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,1,utf8_decode('UNIDAD'),'LTB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,1,utf8_decode('CANTIDAD'),'LTB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode('% CUMPLIMIENTO'),'LTB','C',false);
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode('ACUMULADO A RECEPCION ANTERIOR'),'LTB','C',false);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode('LONGITUD PAGO I.P. N° '.html_entity_decode($row2["NroPagoBimestre"]).''),'LTB','C',false);	
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode('RECEPCIONADO A LA FECHA'),'LRTB','C',false);	
	$y = $y + .5;	
	$suma_faja = 0;
	$suma_saneamiento = 0;
	$suma_calzada = 0;
	$suma_bermas = 0;
	$suma_senalizacion = 0;
	$suma_demarcacion = 0;
	$acumulado_anterior_faja = 0;
	$acumulado_anterior_saneamiento = 0;
	$acumulado_anterior_calzada = 0;
	$acumulado_anterior_berma = 0;
	$acumulado_anterior_senalizacion = 0;
	$acumulado_anterior_demarcacion = 0;
	$longitud_faja = 0;	
	$longitud_saneamiento = 0;	
	$longitud_calzada = 0;
	$longitud_berma = 0;	
	$longitud_senalizacion = 0;	
	$longitud_demarcacion = 0;	
	$acumulado_fecha_faja = 0;
	$acumulado_fecha_saneamiento = 0;
	$acumulado_fecha_calzada = 0;
	$acumulado_fecha_berma = 0;
	$acumulado_fecha_senalizacion = 0;
	$acumulado_fecha_demarcacion = 0;
	//Códigos y ROLES del contrato
	$consulta22 = "select * from redcaminera";
	$resultado22 = $conexion_db->query($consulta22);	
	while($fila22 = $resultado22->fetch_array(MYSQL_ASSOC)){	
		$y = $y + .5;
		//Validamos tamaño de hoja
		if($y+6 > 27){
			$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina							
			$pdf->SetFont('Arial','B',8);
			$y = 3.5;
			//Cabecera tabla
			$y = $y + 1;	
			$pdf->SetXY(0.5,$y);
			$pdf->MultiCell(6.1,1,utf8_decode('COMPONENTE'),'LTB','C',false);
			$pdf->SetXY(6.6,$y);
			$pdf->MultiCell(1.3,1,utf8_decode('UNIDAD'),'LTB','C',false);
			$pdf->SetXY(7.9,$y);
			$pdf->MultiCell(1.9,1,utf8_decode('CANTIDAD'),'LTB','C',false);
			$pdf->SetXY(9.8,$y);
			$pdf->MultiCell(2.4,.5,utf8_decode('% CUMPLIMIENTO'),'LTB','C',false);
			$pdf->SetXY(12.2,$y);
			$pdf->MultiCell(3.6,.5,utf8_decode('ACUMULADO A RECEPCION ANTERIOR'),'LTB','C',false);
			$pdf->SetXY(15.8,$y);
			$pdf->MultiCell(2.2,.5,utf8_decode('LONGITUD PAGO I.P. N° '.html_entity_decode($row2["NroPagoBimestre"]).''),'LTB','C',false);	
			$pdf->SetXY(18,$y);
			$pdf->MultiCell(3,.5,utf8_decode('RECEPCIONADO A LA FECHA'),'LRTB','C',false);	
			$pdf->SetFont('Arial','',8);
			$y = $y + 1;
		}
		
		//Nombre y rol camino		
		$pdf->SetXY(0.5,$y);
		if(strlen(utf8_decode(strtoupper(html_entity_decode($fila22["rolRedCaminera"].': '.$fila22["nombreRedCaminera"].': KM '.$fila22["kmInicioRedCaminera"]." AL KM ".$fila22["kmFinalRedCaminera"])))) > 115){
			$pdf->SetFont('Arial','B',5);
		}
		else{
			$pdf->SetFont('Arial','B',8);	
		}		
		$pdf->MultiCell(20.5,.5,utf8_decode(strtoupper(html_entity_decode($fila22["rolRedCaminera"].': '.$fila22["nombreRedCaminera"].': KM '.$fila22["kmInicioRedCaminera"]." AL KM ".$fila22["kmFinalRedCaminera"]))),'LRB','J',false);
		$pdf->SetFont('Arial','',8);
		
		//Faja		
		for($indice=0;$indice<count($codigo_ruta_faja);$indice++){
			if($codigo_ruta_faja[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[0]),'LB','L',false);	
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);		
					//Cantidad		
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoFaja[$indice],3)),'LB','C',false);				
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoFaja),'LB','C',false);		
				//Acumulado	
				$consulta26 = "select sum(faja) as sumFaja from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);			
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumFaja"],3)),'LRB','C',false);	
				$acumulado_anterior_faja = $acumulado_anterior_faja + $fila26["sumFaja"];	
					//Longitud
				$longitud = ($longitudMenosUnoFaja[$indice] * ($dia_faja[$indice] / 60))*($row4["cumplimientoFajaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_faja = $longitud_faja + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', ".number_format($longitud,3).", 0, 0, 0, 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set faja = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha		
				$consulta26 = "select sum(faja) as sumFaja from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumFaja"],3)),'LRB','C',false);
				$acumulado_fecha_faja = $acumulado_fecha_faja + $fila26["sumFaja"];	
				$pdf->SetFont('Arial','',8);				
			}
		}
		//Saneamiento
		for($indice=0;$indice<count($codigo_ruta_saneamiento);$indice++){
			if($codigo_ruta_saneamiento[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[1]),'LB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
					//Cantidad
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoSaneamiento[$indice],3)),'LB','C',false);		
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSaneamiento),'LB','C',false);		
					//Acumulado
				$consulta26 = "select sum(saneamiento) as sumSaneamiento from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumSaneamiento"],3)),'LRB','C',false);
				$acumulado_anterior_saneamiento = $acumulado_anterior_saneamiento + $fila26["sumSaneamiento"];	
					//$longitud
				$longitud = ($longitudMenosUnoSaneamiento[$indice] * ($dia_saneamiento[$indice] / 60))*($row4["cumplimientoSaneamientoPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_saneamiento = $longitud_saneamiento + $longitud;	
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, ".number_format($longitud,3).", 0, 0, 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set saneamiento = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(saneamiento) as sumSaneamiento from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumSaneamiento"],3)),'LRB','C',false);
				$acumulado_fecha_saneamiento = $acumulado_fecha_saneamiento + $fila26["sumSaneamiento"];	
				$pdf->SetFont('Arial','',8);				
			}
		}			
		//Calzada
		for($indice=0;$indice<count($codigo_ruta_calzada);$indice++){
			if($codigo_ruta_calzada[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[2]),'LB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
					//Cantidad
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoCalzada[$indice],3)),'LB','C',false);				
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoCalzada),'LB','C',false);		
					//Acumulado	
				$consulta26 = "select sum(calzada) as sumCalzada from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumCalzada"],3)),'LRB','C',false);
				$acumulado_anterior_calzada = $acumulado_anterior_calzada + $fila26["sumCalzada"];	
					//$longitud
				$longitud = ($longitudMenosUnoCalzada[$indice] * ($dia_calzada[$indice] / 60))*($row4["cumplimientoCalzadaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_calzada = $longitud_calzada + $longitud;	
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, ".number_format($longitud,3).", 0, 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set calzada = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(calzada) as sumCalzada from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumCalzada"],3)),'LRB','C',false);
				$acumulado_fecha_calzada = $acumulado_fecha_calzada + $fila26["sumCalzada"];	
				$pdf->SetFont('Arial','',8);	
			}
		}
		//Bermas
		for($indice=0;$indice<count($codigo_ruta_bermas);$indice++){
			if($codigo_ruta_bermas[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[3]),'LTB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
					//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoBermas[$indice],3)),'LB','C',false);		
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoBerma),'LB','C',false);		
					//Acumulado		
				$consulta26 = "select sum(berma) as sumBerma from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumBerma"],3)),'LRB','C',false);
				$acumulado_anterior_berma = $acumulado_anterior_berma + $fila26["sumBerma"];	
					//$longitud
				$longitud = ($longitudMenosUnoBermas[$indice] * ($dia_bermas[$indice] / 60))*($row4["cumplimientoBermaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_berma = $longitud_berma + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, ".number_format($longitud,3).", 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set berma = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(berma) as sumBerma from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumBerma"],3)),'LRB','C',false);
				$acumulado_fecha_berma = $acumulado_fecha_berma + $fila26["sumBerma"];	
				$pdf->SetFont('Arial','',8);
			}
		}		
		//Señalizacion
		for($indice=0;$indice<count($codigo_ruta_senalizacion);$indice++){
			if($codigo_ruta_senalizacion[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[4]),'LTB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
					//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoSenalizacion[$indice],3)),'LB','C',false);
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSenalizacion),'LB','C',false);		
					//Acumulado		
				$consulta26 = "select sum(senalizacion) as sumSenalizacion from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumSenalizacion"],3)),'LRB','C',false);
				$acumulado_anterior_senalizacion = $acumulado_anterior_senalizacion + $fila26["sumSenalizacion"];	
					//$longitud
				$longitud = ($longitudMenosUnoSenalizacion[$indice] * ($dia_senalizacion[$indice] / 60))*($row4["cumplimientoSenalizacionPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_senalizacion = $longitud_senalizacion + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, 0, ".number_format($longitud,3).", 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set senalizacion = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(senalizacion) as sumSenalizacion from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumSenalizacion"],3)),'LRB','C',false);
				$acumulado_fecha_senalizacion = $acumulado_fecha_senalizacion + $fila26["sumSenalizacion"];	
				$pdf->SetFont('Arial','',8);			
			}
		}
		//Demarcacion
		for($indice=0;$indice<count($codigo_ruta_demarcacion);$indice++){
			if($codigo_ruta_demarcacion[$indice] == $fila22["rolRedCaminera"]){
				$y = $y + 0.5;	
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[5]),'LTB','L',false);
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
				//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoDemarcacion[$indice],3)),'LB','C',false);
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoDemarcacion),'LB','C',false);		
					//Acumulado
				$consulta26 = "select sum(demarcacion) as sumDemarcacion from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumDemarcacion"],3)),'LRB','C',false);	
				$acumulado_anterior_demarcacion = $acumulado_anterior_demarcacion + $fila26["sumDemarcacion"];
					//$longitud
				$longitud = ($longitudMenosUnoDemarcacion[$indice] * ($dia_demarcacion[$indice] / 60))*($row4["cumplimientoDemarcacionPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_demarcacion = $longitud_demarcacion + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, 0, 0, ".number_format($longitud,3).", ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set demarcacion = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(demarcacion) as sumDemarcacion from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumDemarcacion"],3)),'LRB','C',false);
				$acumulado_fecha_demarcacion = $acumulado_fecha_demarcacion + $fila26["sumDemarcacion"];
				$pdf->SetFont('Arial','',8);
			}	
		}
	}
		
	//Mostramos el resumen	
	$y = $y + .5;
	$pdf->SetXY(0.5,$y);
	$pdf->SetFont('Arial','B',8);
	$pdf->MultiCell(20.5,.5,utf8_decode(strtoupper(html_entity_decode('RESUMEN'))),'LRB','J',false);
	$y = $y + 0.5;	
	$pdf->SetFont('Arial','',8);
	//Faja
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[0]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteFaja,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoFaja),'LB','C',false);							
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorFajaDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_faja,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorFajaDescontada+$presenteRecepcion_faja,3)),'LRB','C',false);
	$y = $y + 0.5;	
	$pdf->SetFont('Arial','',8);
	//Saneamiento
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[1]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteSaneamiento,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSaneamiento),'LB','C',false);		
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorSaneamientoDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_saneamiento,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorSaneamientoDescontada+$presenteRecepcion_saneamiento,3)),'LRB','C',false);
	$y = $y + 0.5;
	$pdf->SetFont('Arial','',8);
	//Calzada
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[2]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteCalzada,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoCalzada),'LB','C',false);							
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorCalzadaDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_calzada,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorCalzadaDescontada+$presenteRecepcion_calzada,3)),'LRB','C',false);
	$y = $y + 0.5;
	$pdf->SetFont('Arial','',8);
	//Bermas
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[3]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteBermas,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoBerma),'LB','C',false);		
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorBermasDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_berma,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorBermasDescontada+$presenteRecepcion_berma,3)),'LRB','C',false);
	$y = $y + 0.5;
	$pdf->SetFont('Arial','',8);
	//Senalizacion
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[4]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteSenalizacion,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSenalizacion),'LB','C',false);		
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorSenalizacionDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_senalizacion,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorSenalizacionDescontada+$presenteRecepcion_senalizacion,3)),'LRB','C',false);
	$y = $y + 0.5;
	$pdf->SetFont('Arial','',8);
	//Demarcacion
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[5]),'LB','L',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteDemarcacion,3)),'LB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoDemarcacion),'LB','C',false);		
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorDemarcacionDescontada,3)),'LRB','C',false);	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_demarcacion,3)),'LRB','C',false);
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorDemarcacionDescontada+$presenteRecepcion_demarcacion,3)),'LRB','C',false);
	$y = $y + 0.5;
	$pdf->SetFont('Arial','',8);		
	//Firmas		
	$y = 29;		
	$pdf->Line(6,$y,15.5,$y);		//Vialidad 1
	$y = $y + 0.1;
	$pdf->SetXY(1,$y);	
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode("INSPECTOR FISCAL DON ".$row3["inspectorFiscalContrato"]))),0,'C');	
	$y = $y + 0.4;
	$pdf->SetXY(1,$y);	
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode("DEPARTAMENTO DE VIALIDAD"))),0,'C');	
	$y = $y + 0.4;$y;
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');
	
	
	//************************* GENERAMOS LA MINUTA SIN INCLUIR LOS CAMINOS CON CERO ******************************
	
		//Agrega una pagina y su fuente
	$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina							
	$pdf->SetFont('Arial','B',10);
	$y = 4;
	
	//Titulo
	$pdf->SetXY(0,$y);
	$pdf->MultiCell(0,.5,utf8_decode('MINUTA'),0,'C',false);	
	$pdf->SetFont('Arial','B',8);
	
	//Nombre de la obra	
	$y = $y + 1;
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(3,.5,utf8_decode('CONTRATO:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(4,$y);
	$pdf->MultiCell(0,.5,strtoupper(utf8_decode('"'.html_entity_decode($row["nombreCompletoObra"]).'"')),0,'J',false);
	
	//Contratista
	$y = $y + 1.5;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(3,.5,utf8_decode('CONTRATISTA:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(4,$y);
	$pdf->MultiCell(16.5,.5,strtoupper(utf8_decode('EMPRESA '.html_entity_decode(implode(" ",$nombreArray)))),0,'J',false);
	
	//Resolucion
	$y = $y + 1;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(7,.5,utf8_decode('OBRA CONTRATADA SEGÚN RESOLUCIÓN:'),0,'J',false);	
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(8,$y);
	$pdf->MultiCell(12.5,.5,utf8_decode('RES D.R.V. N° '.strtoupper(html_entity_decode($row3["resolucionContrato"]))),0,'J',false);
	
	//Inspeccion de pago
	$y = $y + 1.5;
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(0,.5,utf8_decode('INSPECCIÓN DE PAGO N° '.html_entity_decode($row2["NroPagoBimestre"]).''),0,'C',false);
	$pdf->SetFont('Arial','',8);
	$y = $y + 0.5;
	$pdf->SetXY(1,$y);
	$pdf->MultiCell(0,.5,utf8_decode('(Conservación por nivel de servicio)'),0,'C',false);
		
	//Cabecera tabla
	$pdf->SetFont('Arial','B',8);
	$y = $y + 1;	
	$pdf->SetXY(0.5,$y);
	$pdf->MultiCell(6.1,1,utf8_decode('COMPONENTE'),'LTB','C',false);
	$pdf->SetXY(6.6,$y);
	$pdf->MultiCell(1.3,1,utf8_decode('UNIDAD'),'LTB','C',false);
	$pdf->SetXY(7.9,$y);
	$pdf->MultiCell(1.9,1,utf8_decode('CANTIDAD'),'LTB','C',false);
	$pdf->SetXY(9.8,$y);
	$pdf->MultiCell(2.4,.5,utf8_decode('% CUMPLIMIENTO'),'LTB','C',false);
	$pdf->SetXY(12.2,$y);
	$pdf->MultiCell(3.6,.5,utf8_decode('ACUMULADO A RECEPCION ANTERIOR'),'LTB','C',false);
	$pdf->SetXY(15.8,$y);
	$pdf->MultiCell(2.2,.5,utf8_decode('LONGITUD PAGO I.P. N° '.html_entity_decode($row2["NroPagoBimestre"]).''),'LTB','C',false);	
	$pdf->SetXY(18,$y);
	$pdf->MultiCell(3,.5,utf8_decode('RECEPCIONADO A LA FECHA'),'LRTB','C',false);	
	$y = $y + .5;	
	$suma_faja = 0;
	$suma_saneamiento = 0;
	$suma_calzada = 0;
	$suma_bermas = 0;
	$suma_senalizacion = 0;
	$suma_demarcacion = 0;
	$acumulado_anterior_faja = 0;
	$acumulado_anterior_saneamiento = 0;
	$acumulado_anterior_calzada = 0;
	$acumulado_anterior_berma = 0;
	$acumulado_anterior_senalizacion = 0;
	$acumulado_anterior_demarcacion = 0;
	$longitud_faja = 0;	
	$longitud_saneamiento = 0;	
	$longitud_calzada = 0;
	$longitud_berma = 0;	
	$longitud_senalizacion = 0;	
	$longitud_demarcacion = 0;	
	$acumulado_fecha_faja = 0;
	$acumulado_fecha_saneamiento = 0;
	$acumulado_fecha_calzada = 0;
	$acumulado_fecha_berma = 0;
	$acumulado_fecha_senalizacion = 0;
	$acumulado_fecha_demarcacion = 0;
	$hayComponente=0;
		$pdf->SetFont('Arial','',8);	
	//Códigos y ROLES del contrato
	$consulta22 = "select * from redcaminera";
	$resultado22 = $conexion_db->query($consulta22);	
	while($fila22 = $resultado22->fetch_array(MYSQL_ASSOC))
	{	
		$y = $y + .5;
		$aux=$y;
		//Validamos tamaño de hoja
		if($y+6 > 27){
			$pdf->AddPage('P',array(21.6,33));					//Creacion de una pagina							
			$pdf->SetFont('Arial','B',8);
			$y = 3.5;
			//Cabecera tabla
			$y = $y + 1;	
			$pdf->SetXY(0.5,$y);
			$pdf->MultiCell(6.1,1,utf8_decode('COMPONENTE'),'LTB','C',false);
			$pdf->SetXY(6.6,$y);
			$pdf->MultiCell(1.3,1,utf8_decode('UNIDAD'),'LTB','C',false);
			$pdf->SetXY(7.9,$y);
			$pdf->MultiCell(1.9,1,utf8_decode('CANTIDAD'),'LTB','C',false);
			$pdf->SetXY(9.8,$y);
			$pdf->MultiCell(2.4,.5,utf8_decode('% CUMPLIMIENTO'),'LTB','C',false);
			$pdf->SetXY(12.2,$y);
			$pdf->MultiCell(3.6,.5,utf8_decode('ACUMULADO A RECEPCION ANTERIOR'),'LTB','C',false);
			$pdf->SetXY(15.8,$y);
			$pdf->MultiCell(2.2,.5,utf8_decode('LONGITUD PAGO I.P. N° '.html_entity_decode($row2["NroPagoBimestre"]).''),'LTB','C',false);	
			$pdf->SetXY(18,$y);
			$pdf->MultiCell(3,.5,utf8_decode('RECEPCIONADO A LA FECHA'),'LRTB','C',false);	
			$pdf->SetFont('Arial','',8);
			$y = $y + 1;
		}
		$hayComponente = 0;
		//Faja		
		for($indice=0;$indice<count($codigo_ruta_faja);$indice++)
		{
			if(($codigo_ruta_faja[$indice] == $fila22["rolRedCaminera"]) && ($longitudMenosUnoFaja[$indice] != 0))
			{

				$hayComponente++;
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[0]),'LB','L',false);	
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);		
					//Cantidad		
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoFaja[$indice],3)),'LB','C',false);				
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoFaja),'LB','C',false);

				//Acumulado	
				$consulta26 = "select sum(faja) as sumFaja from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);			
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumFaja"],3)),'LRB','C',false);	
				$acumulado_anterior_faja = $acumulado_anterior_faja + $fila26["sumFaja"];

				//Longitud
				$longitud = ($longitudMenosUnoFaja[$indice] * ($dia_faja[$indice] / 60))*($row4["cumplimientoFajaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_faja = $longitud_faja + $longitud;		
				$pdf->SetFont('Arial','',8);

				//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);

				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, "."demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', ".number_format($longitud,3).", 0, 0, 0, 0, 0, ".$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else
				{
					$consulta25 = "update recxcompdescontada set faja = ".number_format($longitud,3)." where rolCamino = '".$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}

				//Recepcionado a la fecha		
				$consulta26 = "select sum(faja) as sumFaja from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumFaja"],3)),'LRB','C',false);
				$acumulado_fecha_faja = $acumulado_fecha_faja + $fila26["sumFaja"];	
				$pdf->SetFont('Arial','',8);
			}				
														
		}

		//Saneamiento
		for($indice=0;$indice<count($codigo_ruta_saneamiento);$indice++){
			if($codigo_ruta_saneamiento[$indice] == $fila22["rolRedCaminera"] && $longitudMenosUnoSaneamiento[$indice] != 0)
			{
				$hayComponente++;
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[1]),'LB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
					//Cantidad
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoSaneamiento[$indice],3)),'LB','C',false);		
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSaneamiento),'LB','C',false);		
					//Acumulado
				$consulta26 = "select sum(saneamiento) as sumSaneamiento from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumSaneamiento"],3)),'LRB','C',false);
				$acumulado_anterior_saneamiento = $acumulado_anterior_saneamiento + $fila26["sumSaneamiento"];	
					//$longitud
				$longitud = ($longitudMenosUnoSaneamiento[$indice] * ($dia_saneamiento[$indice] / 60))*($row4["cumplimientoSaneamientoPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_saneamiento = $longitud_saneamiento + $longitud;	
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, ".number_format($longitud,3).", 0, 0, 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set saneamiento = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(saneamiento) as sumSaneamiento from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumSaneamiento"],3)),'LRB','C',false);
				$acumulado_fecha_saneamiento = $acumulado_fecha_saneamiento + $fila26["sumSaneamiento"];	
				$pdf->SetFont('Arial','',8);				
			}
		}			
		//Calzada
		for($indice=0;$indice<count($codigo_ruta_calzada);$indice++){
			if($codigo_ruta_calzada[$indice] == $fila22["rolRedCaminera"] && $longitudMenosUnoCalzada[$indice] != 0){
				$hayComponente++;
				$y = $y + 0.5;
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[2]),'LB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
					//Cantidad
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoCalzada[$indice],3)),'LB','C',false);				
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoCalzada),'LB','C',false);		
					//Acumulado	
				$consulta26 = "select sum(calzada) as sumCalzada from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumCalzada"],3)),'LRB','C',false);
				$acumulado_anterior_calzada = $acumulado_anterior_calzada + $fila26["sumCalzada"];	
					//$longitud
				$longitud = ($longitudMenosUnoCalzada[$indice] * ($dia_calzada[$indice] / 60))*($row4["cumplimientoCalzadaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_calzada = $longitud_calzada + $longitud;	
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, ".number_format($longitud,3).", 0, 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set calzada = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(calzada) as sumCalzada from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumCalzada"],3)),'LRB','C',false);
				$acumulado_fecha_calzada = $acumulado_fecha_calzada + $fila26["sumCalzada"];	
				$pdf->SetFont('Arial','',8);	
			}
		}
		//Bermas
		for($indice=0;$indice<count($codigo_ruta_bermas);$indice++){
			if($codigo_ruta_bermas[$indice] == $fila22["rolRedCaminera"] && $longitudMenosUnoBermas[$indice] != 0){
				$hayComponente++;
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[3]),'LTB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
					//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoBermas[$indice],3)),'LB','C',false);		
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoBerma),'LB','C',false);		
					//Acumulado		
				$consulta26 = "select sum(berma) as sumBerma from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumBerma"],3)),'LRB','C',false);
				$acumulado_anterior_berma = $acumulado_anterior_berma + $fila26["sumBerma"];	
					//$longitud
				$longitud = ($longitudMenosUnoBermas[$indice] * ($dia_bermas[$indice] / 60))*($row4["cumplimientoBermaPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_berma = $longitud_berma + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, ".number_format($longitud,3).", 0, 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set berma = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(berma) as sumBerma from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumBerma"],3)),'LRB','C',false);
				$acumulado_fecha_berma = $acumulado_fecha_berma + $fila26["sumBerma"];	
				$pdf->SetFont('Arial','',8);
			}
		}		
		//Señalizacion
		for($indice=0;$indice<count($codigo_ruta_senalizacion);$indice++){
			if($codigo_ruta_senalizacion[$indice] == $fila22["rolRedCaminera"] && $longitudMenosUnoSenalizacion[$indice] != 0){
				$hayComponente++;
				$y = $y + 0.5;	
					//Componente
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[4]),'LTB','L',false);
					//Unidad
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
					//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoSenalizacion[$indice],3)),'LB','C',false);
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSenalizacion),'LB','C',false);		
					//Acumulado		
				$consulta26 = "select sum(senalizacion) as sumSenalizacion from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumSenalizacion"],3)),'LRB','C',false);
				$acumulado_anterior_senalizacion = $acumulado_anterior_senalizacion + $fila26["sumSenalizacion"];	
					//$longitud
				$longitud = ($longitudMenosUnoSenalizacion[$indice] * ($dia_senalizacion[$indice] / 60))*($row4["cumplimientoSenalizacionPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_senalizacion = $longitud_senalizacion + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, 0, ".number_format($longitud,3).", 0, ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set senalizacion = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(senalizacion) as sumSenalizacion from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumSenalizacion"],3)),'LRB','C',false);
				$acumulado_fecha_senalizacion = $acumulado_fecha_senalizacion + $fila26["sumSenalizacion"];	
				$pdf->SetFont('Arial','',8);			
			}
		}
		//Demarcacion
		for($indice=0;$indice<count($codigo_ruta_demarcacion);$indice++){
			if($codigo_ruta_demarcacion[$indice] == $fila22["rolRedCaminera"] && $longitudMenosUnoDemarcacion[$indice] != 0){
				$hayComponente++;
				$y = $y + 0.5;	
				$pdf->SetXY(0.5,$y);
				$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[5]),'LTB','L',false);
				$pdf->SetXY(6.6,$y);
				$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LTB','C',false);
				//Cantidad				
				$pdf->SetXY(7.9,$y);
				$pdf->MultiCell(1.9,.5,utf8_decode(number_format($longitudMenosUnoDemarcacion[$indice],3)),'LB','C',false);
					//Porcentaje de incumplimiento
				$pdf->SetXY(9.8,$y);
				$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoDemarcacion),'LB','C',false);		
					//Acumulado
				$consulta26 = "select sum(demarcacion) as sumDemarcacion from recxcompdescontada where rolCamino = '".
					$fila22["rolRedCaminera"]."' and bimestre < ".$_SESSION["BIMESTRE_INFORME"];
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetXY(12.2,$y);
				$pdf->MultiCell(3.6,.5,utf8_decode(number_format($fila26["sumDemarcacion"],3)),'LRB','C',false);	
				$acumulado_anterior_demarcacion = $acumulado_anterior_demarcacion + $fila26["sumDemarcacion"];
					//$longitud
				$longitud = ($longitudMenosUnoDemarcacion[$indice] * ($dia_demarcacion[$indice] / 60))*($row4["cumplimientoDemarcacionPorcentaje"] / 100);
				$pdf->SetFont('Arial','B',8);	
				$pdf->SetXY(15.8,$y);
				$pdf->MultiCell(2.2,.5,utf8_decode(number_format($longitud,3)),'LRB','C',false);
				$longitud_demarcacion = $longitud_demarcacion + $longitud;		
				$pdf->SetFont('Arial','',8);
					//Guardamos la longitud en tabla recxcompdescontada
				$consulta24 = "select count(*) as contador from recxcompdescontada where rolCamino = '".$fila22["rolRedCaminera"].
					"' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
				$resultado24 = $conexion_db->query($consulta24);
				$fila24 = $resultado24->fetch_array(MYSQL_ASSOC);
				if($fila24["contador"] == 0){
					$consulta25 = "insert into recxcompdescontada (idCamino, rolCamino, faja, saneamiento, calzada, berma, senalizacion, ".
						"demarcacion, bimestre) values ('', '".$fila22["rolRedCaminera"]."', 0, 0, 0, 0, 0, ".number_format($longitud,3).", ".
						$_SESSION["BIMESTRE_INFORME"].")";
					$resultad25 = $conexion_db->query($consulta25);
				}
				else{
					$consulta25 = "update recxcompdescontada set demarcacion = ".number_format($longitud,3)." where rolCamino = '".
						$fila22["rolRedCaminera"]."' and bimestre = ".$_SESSION["BIMESTRE_INFORME"];
					$resultado25 = $conexion_db->query($consulta25);
				}
					//Recepcionado a la fecha
				$consulta26 = "select sum(demarcacion) as sumDemarcacion from recxcompdescontada where rolCamino ='".
					$fila22["rolRedCaminera"]."'";
				$resultado26 = $conexion_db->query($consulta26);
				$fila26 = $resultado26->fetch_array(MYSQL_ASSOC);
				$pdf->SetFont('Arial','B',8);
				$pdf->SetXY(18,$y);
				$pdf->MultiCell(3,.5,utf8_decode(number_format($fila26["sumDemarcacion"],3)),'LRB','C',false);
				$acumulado_fecha_demarcacion = $acumulado_fecha_demarcacion + $fila26["sumDemarcacion"];
				$pdf->SetFont('Arial','',8);
			}	
		}

		if($hayComponente > 0){
			//Nombre y rol camino		
			$pdf->SetXY(0.5,$aux);
			if(strlen(utf8_decode(strtoupper(html_entity_decode($fila22["rolRedCaminera"].': '.$fila22["nombreRedCaminera"].': KM '.$fila22["kmInicioRedCaminera"]." AL KM ".$fila22["kmFinalRedCaminera"])))) > 115){
				$pdf->SetFont('Arial','B',5);
			}
			else{
				$pdf->SetFont('Arial','B',8);	
			}		
			$pdf->MultiCell(20.5,.5,utf8_decode(strtoupper(html_entity_decode($fila22["rolRedCaminera"].': '.$fila22["nombreRedCaminera"].': KM '.$fila22["kmInicioRedCaminera"]." AL KM ".$fila22["kmFinalRedCaminera"]))),'LRB','J',false);
			$pdf->SetFont('Arial','',8);	
		}	
	}
		
	//Mostramos el resumen	
	//$y = $y + .5;
	$aux = $y;	
	$y = $y + 0.5;	
	$suma = 0;	
	//Faja
	if($kmVigenteFaja > 0){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[0]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteFaja,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoFaja),'LB','C',false);							
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorFajaDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_faja,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorFajaDescontada+$presenteRecepcion_faja,3)),'LRB','C',false);
		$y = $y + 0.5;	
		$pdf->SetFont('Arial','',8);
	}	
	//Saneamiento
	if($kmVigenteSaneamiento > 0){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[1]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteSaneamiento,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSaneamiento),'LB','C',false);		
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorSaneamientoDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_saneamiento,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorSaneamientoDescontada+$presenteRecepcion_saneamiento,3)),'LRB','C',false);
		$y = $y + 0.5;
		$pdf->SetFont('Arial','',8);
	}		
	//Calzada
	if($kmVigenteCalzada > 0){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[2]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteCalzada,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoCalzada),'LB','C',false);							
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorCalzadaDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_calzada,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorCalzadaDescontada+$presenteRecepcion_calzada,3)),'LRB','C',false);
		$y = $y + 0.5;
		$pdf->SetFont('Arial','',8);
	}	
	//Bermas
	if($kmVigenteBermas){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[3]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteBermas,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoBerma),'LB','C',false);		
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorBermasDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_berma,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorBermasDescontada+$presenteRecepcion_berma,3)),'LRB','C',false);
		$y = $y + 0.5;
		$pdf->SetFont('Arial','',8);
	}	
	//Senalizacion
	if($kmVigenteSenalizacion > 0){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[4]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteSenalizacion,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoSenalizacion),'LB','C',false);		
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorSenalizacionDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_senalizacion,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorSenalizacionDescontada+$presenteRecepcion_senalizacion,3)),'LRB','C',false);
		$y = $y + 0.5;
		$pdf->SetFont('Arial','',8);
	}	
	//Demarcacion
	if($kmVigenteDemarcacion > 0){
		$suma++;
		$pdf->SetXY(0.5,$y);
		$pdf->MultiCell(6.1,.5,utf8_decode($Ncomponente[5]),'LB','L',false);
		$pdf->SetXY(6.6,$y);
		$pdf->MultiCell(1.3,.5,utf8_decode('KM'),'LB','C',false);
		$pdf->SetXY(7.9,$y);
		$pdf->MultiCell(1.9,.5,utf8_decode(number_format($kmVigenteDemarcacion,3)),'LB','C',false);
		$pdf->SetXY(9.8,$y);
		$pdf->MultiCell(2.4,.5,utf8_decode($factorCumplimientoDemarcacion),'LB','C',false);		
		$pdf->SetXY(12.2,$y);
		$pdf->MultiCell(3.6,.5,utf8_decode(number_format($anteriorDemarcacionDescontada,3)),'LRB','C',false);	
		$pdf->SetFont('Arial','B',8);
		$pdf->SetXY(15.8,$y);
		$pdf->MultiCell(2.2,.5,utf8_decode(number_format($presenteRecepcion_demarcacion,3)),'LRB','C',false);
		$pdf->SetXY(18,$y);
		$pdf->MultiCell(3,.5,utf8_decode(number_format($anteriorDemarcacionDescontada+$presenteRecepcion_demarcacion,3)),'LRB','C',false);
		$y = $y + 0.5;
		$pdf->SetFont('Arial','',8);		
	}
	if($suma > 0){		
		$pdf->SetXY(0.5,$aux);
		$pdf->SetFont('Arial','B',8);
		$pdf->MultiCell(20.5,.5,utf8_decode(strtoupper(html_entity_decode('RESUMEN'))),'LRB','J',false);	
		$pdf->SetFont('Arial','',8);
	}
	
	//Firmas		
	$y = 29;		
	$pdf->Line(6,$y,15.5,$y);		//Vialidad 1
	$y = $y + 0.1;
	$pdf->SetXY(1,$y);	
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode("INSPECTOR FISCAL DON ".$row3["inspectorFiscalContrato"]))),0,'C');	
	$y = $y + 0.4;
	$pdf->SetXY(1,$y);	
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode("DEPARTAMENTO DE VIALIDAD"))),0,'C');	
	$y = $y + 0.4;$y;
	$pdf->MultiCell(0,0.4,strtoupper(utf8_decode(html_entity_decode($row5["regionUnoVialidadComision"]))),0,'C');


	//-------------------------------------------------------------------------------------------------------------------------------	
	//Respaldo base de datos
	$nombre_respaldo = NOMBRE_DB."_BIMESTRE".$_SESSION["BIMESTRE_INFORME"]."_".date('d-m-Y').".sql";
	$direccion_sistema=getcwd();
	$directorio_respaldo = $direccion_sistema."\\respaldoDB";
	$directorio_nombre = $directorio_respaldo."\\".$nombre_respaldo;
	$directorio_dump = $direccion_sistema."\\bin_mysql\\mysqldump.exe";

	$comando = "$directorio_dump --opt --user=".USUARIO_DB." --password=".CONTRASENA_DB." ".NOMBRE_DB." > $directorio_nombre";	
	system($comando,$error);
	//Fin respaldo base de datos
	
	$pdf->Output('respaldoInformes/Informe_Final_Inspeccion_N'.$row2["NroPagoBimestre"].'.pdf','F');
	$pdf->Output('Informe_Final_Inspeccion_N'.$row2["NroPagoBimestre"].'.pdf','D');
	//$pdf->Output();		
}

//Funcion FECHA
function fecha($date){
	$array_date = explode("-",$date);
	switch($array_date[1]){
		case 1:
			return($array_date[2]." de enero de ".$array_date[0]);;
			break;
		case 2:
			return($array_date[2]." de febrero de ".$array_date[0]);;
			break;
		case 3:
			return($array_date[2]." de marzo de ".$array_date[0]);;
			break;
		case 4:
			return($array_date[2]." de abril de ".$array_date[0]);;
			break;
		case 5:
			return($array_date[2]." de mayo de ".$array_date[0]);;
			break;
		case 6:
			return($array_date[2]." de junio de ".$array_date[0]);;
			break;
		case 7:
			return($array_date[2]." de julio de ".$array_date[0]);
			break;
		case 8:
			return($array_date[2]." de agosto de ".$array_date[0]);
			break;
		case 9:
			return($array_date[2]." de septiembre de ".$array_date[0]);;
			break;
		case 10:
			return($array_date[2]." de octubre de ".$array_date[0]);
			break;
		case 11:
			return($array_date[2]." de noviembre de ".$array_date[0]);
			break;
		case 12:
			return($array_date[2]." de diciembre de ".$array_date[0]);
			break;
		default:
			echo "El valor ingresado no corresponde a un mes.";
		
	}	
}

function compararFechas($primera, $segunda){
	$valoresPrimera = explode ("-", $primera);   
	$valoresSegunda = explode ("-", $segunda); 
	$anyoPrimera = $valoresPrimera[0];  
	$mesPrimera = $valoresPrimera[1];  
	$diaPrimera = $valoresPrimera[2]; 
	$anyoSegunda = $valoresSegunda[0];  
	$mesSegunda = $valoresSegunda[1];  
	$diaSegunda = $valoresSegunda[2];
	$diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
	$diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     
	if(!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)){
		// "La fecha ".$primera." no es válida";
		return 0;
	}
	else if(!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)){
		// "La fecha ".$segunda." no es válida";
		return 0;
	}
	else{
		return  $diasPrimeraJuliano - $diasSegundaJuliano;
	} 
}

?>