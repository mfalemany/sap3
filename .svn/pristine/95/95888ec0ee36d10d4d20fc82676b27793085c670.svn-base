<?php
class ml_evaluacion_criterios extends sap_ei_formulario_ml
{
	
	function ini()
	{
		$url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$tpl_reglamento = "<a href='{$url_base}/becas/estaticos/reglamento.pdf' TARGET='_BLANK'>Ver reglamento</a>";
		$tpl_pautas = "<a href='{$url_base}/becas/estaticos/pautas.pdf' TARGET='_BLANK'>Ver pautas de evaluación</a>";
		$this->agregar_notificacion($tpl_reglamento."&nbsp;&nbsp;-&nbsp;&nbsp;".$tpl_pautas,'info');
	}
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function extender_objeto_js()
	{
		echo "
		var filas = {$this->objeto_js}.filas()
		
		function actualizar_total()
		{
			//Hace una sumatoria de todos los campos de texto de puntaje (obtiene todos los textbox, con map obtiene la propiedad value de cada uno, y con reduce hace la suma);
			asignados = Array.from(
				document.querySelectorAll('input[id*=criteriospuntaje]'))
				.map( elem => parseFloat(elem.value.replace(',','.')) || 0 )
				.reduce( (acum,actual) => acum+actual);
			
			if(!isNaN(parseFloat($('#puntaje_inicial_valor').html()))){
				final = asignados + parseFloat($('#puntaje_inicial_valor').html());
				$('#puntaje_final_valor').html(parseFloat(final).toFixed(3));	
			}else{
				$('#puntaje_final_valor').html(parseFloat(asignados).toFixed(2));	
			}
			
			
		}

		//---- Validacion de EFs -----------------------------------
		for (id_fila in filas) {
			{$this->objeto_js}.ef('puntaje').ir_a_fila(filas[id_fila]).cuando_cambia_valor('actualizar_total()');
		}	

		{$this->objeto_js}.evt__puntaje__validar = function(fila)
		{
			asignado = this.ef('puntaje').ir_a_fila(fila).get_estado();
			maximo = this.ef('puntaje_maximo').ir_a_fila(fila).get_estado()
			//this.ef('puntaje').ir_a_fila(fila)._rango = [[0,true],[maximo,true]];
			
			if( (asignado >= 0) && (asignado <= maximo) && (asignado.toString().length > 0) ){
				return true;
			}else{
				this.ef('puntaje').ir_a_fila(fila).set_error('El puntaje asignado está fuera de los límites permitidos');
				return false;	
			}
		}

		";
	}

}
?>