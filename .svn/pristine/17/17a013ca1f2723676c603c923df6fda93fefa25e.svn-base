<?php
class form_proyecto extends sap_ei_formulario
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		/* =================================================================================================
		 * VALIDACION: CUANDO CAMBIA EL TIPO DE PROYECTO, SI SE ELIGE 'PI', LA DURACIÓN NO PUEDE MODIFICARSE	
		 * ================================================================================================= */
		{$this->objeto_js}.ef('tipo').cuando_cambia_valor(function(){
			
			if({$this->objeto_js}.ef('tipo').get_estado() == '0'){
				{$this->objeto_js}.ef('duracion').cambiar_valor(4);
				{$this->objeto_js}.ef('duracion').set_solo_lectura(true);
			}else{
				{$this->objeto_js}.ef('duracion').set_solo_lectura(false);
			} 
		});

		/* =================================================================================================
		 * CONTEO DE PALABRAS DEL CAMPO 'RESUMEN'. NO PUEDE SUPERAR MAS DE 'max_resumen'
		 * ================================================================================================= */
		//Variable que controla cuantas palabras debe contener el resumen (como máximo)
		var max_resumen = 250;

		//obtengo el id del elemento JS del resumen
		id = '#'+{$this->objeto_js}.ef('resumen')._id_form;
		
		//cuando se escriba sobre el resumen, actualizo el contador de palabras
		$(id).on('keyup',function(){
			cant = $(id).prop('value').split(' ').filter(palabra => palabra.length > 0).length;
			$('#cant_palabras_resumen').text(cant);
		});
		
		//---- Validacion de EFs -----------------------------------
		{$this->objeto_js}.evt__resumen__validar = function()
		{
			if(this.ef('resumen').get_estado().split(' ').filter(pal => pal.length > 0).length > max_resumen){
				this.ef('resumen').set_error('Este campo debe tener, como máximo, '+max_resumen+' palabras');
				return false;
			}else{
				return true;
			}
		}

		/* =================================================================================================
		 * VALIDACION DE LA CANTIDAD DE PALABRAS CLAVE
		 * ================================================================================================= */
		{$this->objeto_js}.ef('palabras_clave').validar  = function(){
			pal = {$this->objeto_js}.ef('palabras_clave').get_estado().split(',').filter(pal => pal.length > 0).length;
			if(pal < 3){
				{$this->objeto_js}.ef('palabras_clave').set_error('Debe cargar, como mínimo, tres palabras clave');
				return false;
			}else{
				return true;
			}
		}

		
		";
	}


}
?>