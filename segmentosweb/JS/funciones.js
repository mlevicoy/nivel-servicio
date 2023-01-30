// JavaScript Document

//Carga ingresar camino bloqueando los controles
function cargar(){	
	"use strict";
	var i = 1;
	for(i=1;i<21;i++){
		document.getElementsByName('numeroCamino[]').item(i).disabled = true;
		document.getElementsByName('rol[]').item(i).disabled = true;
		document.getElementsByName('codigo[]').item(i).disabled = true;
		document.getElementsByName('nombre[]').item(i).disabled = true;
		document.getElementsByName('kmInicio[]').item(i).disabled = true;
		document.getElementsByName('kmFinal[]').item(i).disabled = true;
		document.getElementsByName('longitud[]').item(i).disabled = true;		
	}
}

//suma los valores de km inicio y km final para generar la longitud del camino
function sumakm(valor){					
	"use strict";
	var numero1 = parseFloat(document.getElementsByName('kmInicio[]').item(valor).value);
	var numero2 = parseFloat(document.getElementsByName('kmFinal[]').item(valor).value);									
	var resta = numero2 - numero1;
	document.getElementsByName('longitud[]').item(valor).value = resta.toFixed(3);				
}

//Restringue campo numero camino
function soloNumero(evt, control){
	"use strict";
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	//Solo número
	if(control === 0){
		if(charCode > 31 && (charCode < 48 || charCode > 57)){
			return false;
		}
		return true;	
	}
	//Solo número y punto
	if(control === 1){		
		if(charCode > 31 && (charCode < 46 || charCode > 57 || charCode === 47)){
			return false;
		}  
		return true;	
	}	
	//Solo número, punto, signo peso y guión
	if(control === 2){
		if(charCode > 47 && charCode < 58){
			return true;
		}
		else if(charCode === 45 || charCode === 46 || charCode === 36){
			return true;
		}
		else{
			return false;	
		}
	}
	//Solo número, punto, y guión
	if(control === 3){
		if(charCode > 47 && charCode < 58){
			return true;
		}
		else if(charCode === 45 || charCode === 46 || charCode === 75 || charCode === 107){
			return true;
		}
		else{
			return false;	
		}		
	}	
}

//Crea los controles para ingresar los caminos
function crearControlesCamino(valor, cantidadHijos){	
	"use strict";			
	var cantidadElementos = parseInt(valor);			
	var padre = document.getElementById('controles');	
	if(cantidadHijos === 0){		
		cantidadHijos = padre.getElementsByTagName("li").length;				
	}	
	if(cantidadHijos > 1){		
		var hijo;
		var j=0;
		var hijo_btn = document.getElementById('li_boton');
		padre.removeChild(hijo_btn);				
		while(j < cantidadHijos-2){
			hijo = document.getElementById('li'+j);
			padre.removeChild(hijo);				
			j++;
		}
	}
	
	for(var i=0;i<cantidadElementos;i++){
		padre.innerHTML += "<li class='valor_campo' id='li"+i+"'>"+
			"<input type='text' name='numeroCamino[]' value='' placeholder='N&deg; Camino' required/>"+
			"<input type='text' name='rol[]' value='' placeholder='ROL' required/>"+
			"<input type='text' name='codigo[]' value='' placeholder='C&oacute;digo' required/>"+
			"<input type='text' name='nombre[]' value='' placeholder='Nombre Camino' style='width:30%;' required/>"+
			"<input type='text' name='kmInicio[]' value='' placeholder='KM. Inicio' onKeyPress='return soloNumero(event,1)' onBlur='sumakm("+i+")' required/>"+
			"<input type='text' name='kmFinal[]' value='' placeholder='KM. Final' onKeyPress='return soloNumero(event,1)' onBlur='sumakm("+i+")' required/>"+
			"<input type='text' name='longitud[]' value='' placeholder='Longitud' readonly required/>"+
			"<button style='position:relative;left:285px;cursor:pointer;color:#fff;font-weight:bolder;background-color:#01325D;padding:0.45rem 0rem 0.45rem 0rem;border-radius:7px;"+
			"transition-duration:1s;width:7%;display:inline-block;' onClick='return eliminarControlesCamino("+i+")'>Eliminar</button><br/></li>";
	}	
	padre.innerHTML += "<li id='li_boton'><br/><input type='submit' name='enviar' value='INGRESAR RED' id='boton_envio'/><br/><br/></li>";	
}

function eliminarControlesCamino(valor){	
	"use strict";	
	var padre = document.getElementById('controles');
	var hijo = document.getElementById('li'+valor);
	padre.removeChild(hijo);	
	return false;
}

//Redireccionar modificar usuario
function redireccionar(id){     				
	"use strict";
	if(id === 0){
		document.getElementsByName('nombre').item(0).value = "";
		document.getElementsByName('apellido').item(0).value = "";
		document.getElementsByName('email').item(0).value = "";
		document.getElementsByName('contrasena').item(0).value = "";
		document.getElementsByName('cargo').item(0).selectedIndex = "3";
		document.getElementsByName('usuarioBuscar').item(0).focus();
		return false;
	}
	else{					
		location.href = 'modificarUsuario.php?id='+id;
	}
}

function redireccionar2(id){     				
	"use strict";
	if(id === 0){
		document.getElementsByName('nombre').item(0).value = "";
		document.getElementsByName('apellido').item(0).value = "";
		document.getElementsByName('email').item(0).value = "";
		document.getElementsByName('contrasena').item(0).value = "";
		document.getElementsByName('cargo').item(0).selectedIndex = "3";
		document.getElementsByName('usuarioBuscar').item(0).focus();
		return false;
	}
	else{					
		location.href = 'modificarUsuario2.php?id='+id;
	}
}

//Redireccionar formulario exclusiones
function redireccionarExclusiones(id){
	"use strict";
	location.href = 'ingresarExclusionesFormulario.php?id='+id;
}

//Redireccionar Carga
function redireccionarActa(id){   
	"use strict";	
	if(id === 0){
		return false;
	}
	else{
		location.href = 'acta.php?id='+id;
	}
}
//Redireccionar incumplimiento
function redireccionarIncumplimiento(id){  
	"use strict";
	if(id === ''){
		return false;
	}
	else{
		location.href = 'informeTablaIncumplimiento.php?id='+id;
	}
}
function redireccionarIncumplimiento2(id){        
	location.href = 'informeIncumplimiento.php';
} 

//Redireccionar Informe Final
function redireccionarIF(id){        
	if(id == ''){
		return false;
	}
	else{
		location.href = 'informeFinal.php?id='+id;
	}
}

//Bloquea controles cuando ya esta ingresada la informacion
function carga(){
	"use strict";
	document.getElementsByName('enviar').item(0).disabled = true;
}
                	
//Valida el RUT
function rut(){
	"use strict";	
	var suma = 0;	
    var rutpunto = document.getElementsByName('rutEmpresaConstructora').item(0).value;	//Toma el valor del input                 
    var arrayrutpunto = rutpunto.split(".");	//Crea array separado por el punto
	
	if(arrayrutpunto.length === 1){
		alert("RUT INVALIDO");
		return false;                  
	}						
    var rutsinpunto = arrayrutpunto.join("");	//Crea string eje. 11111111-1                 
    var arrRut = rutsinpunto.split("-");	//array por guion 1111111,1
	if(arrRut.length === 1){
		alert("RUT INVALIDO");
		return false;                  
	}				
	var rutSolo = arrRut[0];
	var verif = arrRut[1];	
	var continuar = true;
	for(var i=2;continuar;i++){
		suma += (rutSolo%10)*i;
		rutSolo = parseInt((rutSolo /10));
		i=(i===7)?1:i;
		continuar = (rutSolo === 0)?false:true;
	}
	var resto = suma%11;
	var dv = parseInt(11-resto);
	
	if(dv===10){
		if(verif.toUpperCase() === 'K'){
			return true;
		}			
	}
	else if(dv === 11 && parseInt(verif) === 0){
		return true;
	}                  
	else if(dv === parseInt(verif)){
		return true;
	}
	else{
		alert("RUT INVALIDO");
		return false;                  
	}
}

function aprobar(control){	
	"use strict";
	if(control===1){
		if(confirm("¡Si continua, se guardara la red de caminos y se comenzará a generar los segmentos y tramos!. \n\n¿Realmente desea continuar?", "Red de Caminos")){
			if(confirm("Espere a que el sistema finalice de generar los segmentos y tramos.\n\n¿Comenzar?", "Red de Caminos")){
				return true;
			}
			return false;
		}
		return false;
	}
	if(control===2){
		if(confirm("¡Si continua, se modificara la red de caminos y se comenzará a generar los segmentos y tramos!. \n\n¿Realmente desea continuar?", "Red de Caminos")){
			if(confirm("Espere a que el sistema finalice de generar los segmentos y tramos.\n\n¿Comenzar?", "Red de Caminos")){
				return true;
			}
			return false;
		}
		return false;
	}
	if(control===3){
		if(confirm("¡Si continua se generaran las exclusión, deberá verificar cada recepción si las exclusiones ingresadas todavia cumplen o si falta alguna!. \n\n¿Realmente desea continuar?", "Exclusiones")){
			if(confirm("Espere a que el sistema finalice de ingresar las exclusiones.\n\n¿Comenzar?", "Exclusiones")){
				return true;
			}
			return false;
		}
		return false;
	}
}

function armarDesarmar(valor){
	"use strict";
	if(document.getElementsByName('excEliminar[]').item(valor).checked){
		document.getElementsByName('rolCamino[]').item(valor).disabled = true;
		document.getElementsByName('kmInicio[]').item(valor).disabled = true;
		document.getElementsByName('kmFinal[]').item(valor).disabled = true;
		document.getElementsByName('longitud[]').item(valor).disabled = true;
		document.getElementsByName('faja[]').item(valor).disabled = true;
		document.getElementsByName('saneamiento[]').item(valor).disabled = true;
		document.getElementsByName('calzada[]').item(valor).disabled = true;
		document.getElementsByName('bermas[]').item(valor).disabled = true;
		document.getElementsByName('senalizacion[]').item(valor).disabled = true;
		document.getElementsByName('demarcacion[]').item(valor).disabled = true;
		document.getElementsByName('excInicial[]').item(valor).disabled = true;
		document.getElementsByName('resolucion[]').item(valor).disabled = true;	
	}
	else{
		document.getElementsByName('rolCamino[]').item(valor).disabled = false;
		document.getElementsByName('kmInicio[]').item(valor).disabled = false;
		document.getElementsByName('kmFinal[]').item(valor).disabled = false;
		document.getElementsByName('longitud[]').item(valor).disabled = false;
		document.getElementsByName('faja[]').item(valor).disabled = false;
		document.getElementsByName('saneamiento[]').item(valor).disabled = false;
		document.getElementsByName('calzada[]').item(valor).disabled = false;
		document.getElementsByName('bermas[]').item(valor).disabled = false;
		document.getElementsByName('senalizacion[]').item(valor).disabled = false;
		document.getElementsByName('demarcacion[]').item(valor).disabled = false;
		document.getElementsByName('excInicial[]').item(valor).disabled = false;
		document.getElementsByName('resolucion[]').item(valor).disabled = false;
	}	
}

function calculoInicial(){
	"use strict";
	/*PORCENTAJE INCUMPLIMIENTO = SUMA(NC)/(SUMA(C)+SUMA(NC))*/				
	var cumple = String("C");
	var noCumple = String("NC");
	var guion = String("-");
	//Verificamos si estan todos los select en ""	
	var componente = "";
	var cantidad = "";
	var porcentaje = "";
	for(var j=0;j<6;j++){
		//Asignamos los valores
		if(j===0){
			componente = "fajaTramo"; 
			cantidad = "nroIncumplimientoFaja"; 
			porcentaje = "porcIncumplimientoFaja";
		}
		else if(j===1){
			componente = "saneamientoTramo"; 
			cantidad = "nroIncumplimientoSaneamiento"; 
			porcentaje = "porcIncumplimientoSaneamiento";
		}
		else if(j===2){
			componente = "calzadaTramo"; 
			cantidad = "nroIncumplimientoCalzada"; 
			porcentaje = "porcIncumplimientoCalzada";
		}
		else if(j===3){
			componente = "bermaTramo"; 
			cantidad = "nroIncumplimientoBerma"; 
			porcentaje = "porcIncumplimientoBerma";
		}
		else if(j===4){
			componente = "senalizacionTramo"; 
			cantidad = "nroIncumplimientoSenalizacion"; 
			porcentaje = "porcIncumplimientoSenalizacion";
		}
		else if(j===5){
			componente = "demarcacionTramo"; 
			cantidad = "nroIncumplimientoDemarcacion"; 
			porcentaje = "porcIncumplimientoDemarcacion";
		}
		var sumaCumple = 0;
		var sumaNoCumple = 0;
		var porcentajeIncumplimiento = 0;
		var vacio = 0;
		//Verificamos si son "" y - todos
		for(var k=1;k<=15;k++){
			if(document.getElementsByName(componente+k).item(0).value === "" || document.getElementsByName(componente+k).item(0).value === "-"){
				vacio = vacio + 1;
			}
		}
		if(vacio === 15){
			document.getElementsByName(cantidad).item(0).value = "-";				
			document.getElementsByName(porcentaje).item(0).value = "-";	
		}
		else{					
			for(var i=1;i<=15;i++){
				if(cumple === document.getElementsByName(componente+i).item(0).value){ 
					sumaCumple = sumaCumple + 1; 
				}
				else if(noCumple === document.getElementsByName(componente+i).item(0).value){ 
					sumaNoCumple = sumaNoCumple + 1; 
				}							
			}
			if((sumaCumple+sumaNoCumple) !== '' && (sumaCumple+sumaNoCumple) > 0){
				porcentajeIncumplimiento = sumaNoCumple/(sumaCumple+sumaNoCumple);														
				porcentajeIncumplimiento = porcentajeIncumplimiento*100;					
				porcentajeIncumplimiento = porcentajeIncumplimiento.toFixed(0);						
				document.getElementsByName(cantidad).item(0).value = sumaNoCumple;				
				document.getElementsByName(porcentaje).item(0).value = porcentajeIncumplimiento;
			}
			else if(sumaCumple === 0 && sumaNoCumple === 0){
				document.getElementsByName(cantidad).item(0).value = "SNS";				
				document.getElementsByName(porcentaje).item(0).value = "SNS";	
			}
		}
	}				
}

//Funcion todo cumple para tabla inclumplimiento
function todocumple(){
	"use strict";
	var guion = "-";
	var sinnivel = "SNS";			
	var i=0;
	var faja = "";
	var saneamiento = "";
	var calzada = "";
	var berma = "";
	var senalizacion = "";
	var demarcacion = "";
	if(document.getElementById('seleccionar_todo').checked){
		for(i=1;i<=15;i++){
			faja = document.getElementsByName('fajaTramo'+i).item(0).value;		
			saneamiento = document.getElementsByName('saneamientoTramo'+i).item(0).value;		
			calzada = document.getElementsByName('calzadaTramo'+i).item(0).value;		
			berma = document.getElementsByName('bermaTramo'+i).item(0).value;		
			senalizacion = document.getElementsByName('senalizacionTramo'+i).item(0).value;		
			demarcacion = document.getElementsByName('demarcacionTramo'+i).item(0).value;		
			if(faja !== guion && faja !== sinnivel){
				document.getElementsByName('fajaTramo'+i).item(0).selectedIndex = 1;  
			}
			if(saneamiento !== guion && saneamiento !== sinnivel){
				document.getElementsByName('saneamientoTramo'+i).item(0).selectedIndex = 1;  
			}
			if(calzada !== guion && calzada !== sinnivel){
				document.getElementsByName('calzadaTramo'+i).item(0).selectedIndex = 1;  
			}
			if(berma !== guion && berma !== sinnivel){
				document.getElementsByName('bermaTramo'+i).item(0).selectedIndex = 1;  
			}
			if(senalizacion !== guion && senalizacion !== sinnivel){
				document.getElementsByName('senalizacionTramo'+i).item(0).selectedIndex = 1;  
			}
			if(demarcacion !== guion && demarcacion !== sinnivel){
				document.getElementsByName('demarcacionTramo'+i).item(0).selectedIndex = 1;  
			}
		}                            
		calculo('fajaTramo','nroIncumplimientoFaja','porcIncumplimientoFaja');
		calculo('saneamientoTramo','nroIncumplimientoSaneamiento','porcIncumplimientoSaneamiento');
		calculo('calzadaTramo','nroIncumplimientoCalzada','porcIncumplimientoCalzada');  
		calculo('bermaTramo','nroIncumplimientoBerma','porcIncumplimientoBerma');                   
		calculo('senalizacionTramo','nroIncumplimientoSenalizacion','porcIncumplimientoSenalizacion');
		calculo('demarcacionTramo','nroIncumplimientoDemarcacion','porcIncumplimientoDemarcacion');
	}
	else{
		for(i=1;i<=15;i++){
			faja = document.getElementsByName('fajaTramo'+i).item(0).value;		
			saneamiento = document.getElementsByName('saneamientoTramo'+i).item(0).value;		
			calzada = document.getElementsByName('calzadaTramo'+i).item(0).value;		
			berma = document.getElementsByName('bermaTramo'+i).item(0).value;		
			senalizacion = document.getElementsByName('senalizacionTramo'+i).item(0).value;		
			demarcacion = document.getElementsByName('demarcacionTramo'+i).item(0).value;		
			if(faja !== guion && faja !== sinnivel){
				document.getElementsByName('fajaTramo'+i).item(0).selectedIndex = 0;  
			}
			if(saneamiento !== guion && saneamiento !== sinnivel){
				document.getElementsByName('saneamientoTramo'+i).item(0).selectedIndex = 0;  
			}
			if(calzada !== guion && calzada !== sinnivel){
				document.getElementsByName('calzadaTramo'+i).item(0).selectedIndex = 0;  
			}
			if(berma !== guion && berma !== sinnivel){
				document.getElementsByName('bermaTramo'+i).item(0).selectedIndex = 0;  
			}
			if(senalizacion !== guion && senalizacion !== sinnivel){
				document.getElementsByName('senalizacionTramo'+i).item(0).selectedIndex = 0;  
			}
			if(demarcacion !== guion && demarcacion !== sinnivel){
				document.getElementsByName('demarcacionTramo'+i).item(0).selectedIndex = 0;  
			}
		}
		calculoInicial();
	}     	       
}
function calculo(componente,cantidad,porcentaje){
	"use strict";
	/*PORCENTAJE INCUMPLIMIENTO = SUMA(NC)/(SUMA(C)+SUMA(NC))*/				
	var cumple = String("C");
	var noCumple = String("NC");
	var guion = String("-");
							
	var sumaCumple = 0;
	var sumaNoCumple = 0;
	var porcentajeIncumplimiento = 0;
				
	for(var i=1;i<=15;i++){
		if(cumple === document.getElementsByName(componente+i).item(0).value){
			sumaCumple = sumaCumple + 1;
		}
		else if(noCumple === document.getElementsByName(componente+i).item(0).value){
			sumaNoCumple = sumaNoCumple + 1;
		}
	}
	if((sumaCumple+sumaNoCumple) !== '' && (sumaCumple+sumaNoCumple) > 0){
		porcentajeIncumplimiento = sumaNoCumple/(sumaCumple+sumaNoCumple);															
		porcentajeIncumplimiento = porcentajeIncumplimiento*100;					
		porcentajeIncumplimiento = porcentajeIncumplimiento.toFixed(0);
						
		document.getElementsByName(cantidad).item(0).value = sumaNoCumple;				
		document.getElementsByName(porcentaje).item(0).value = porcentajeIncumplimiento;
	}
	else if(sumaCumple === 0 && sumaNoCumple === 0){
		document.getElementsByName(cantidad).item(0).value = "SNS";				
		document.getElementsByName(porcentaje).item(0).value = "SNS";	
	}			
}