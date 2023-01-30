<?php
require_once("conexion.php");

if(isset($_POST["componente"]))
	{
		$opciones = '<option value="0"> SELECCIONE ITEM</option>';

		//$conexion= new mysqli("localhost","root","","spg",3306);
		$strConsulta = "select codigocomponente, nombrecomponente FROM ctdadcodigocomponente WHERE nombreComponente LIKE '".$_POST["componente"]."%'";
		$result = $conexion_db->query($strConsulta);
		

		while( $fila = $result->fetch_array(MYSQL_ASSOC)) 
		{
			$opciones.='<option value="'.$fila["codigocomponente"].'">'.$fila["codigocomponente"].'</option>';
		}

		echo $opciones;
	}
?>
