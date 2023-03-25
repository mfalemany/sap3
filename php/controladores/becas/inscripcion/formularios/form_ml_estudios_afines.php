<?php
class form_ml_estudios_afines extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Validacion de EFs -----------------------------------
		{$this->objeto_js}.evt__anio_desde__validar = function(fila)
		{
			if(this.ef('anio_desde').ir_a_fila(fila).get_estado() <= ".date('Y')."){
				return true;
			}else{
				this.ef('anio_desde').set_error('El campo \'Fecha Desde\' debe ser menor o igual al año actual (".date('Y').")');
				return false;
			}

		}
		
		{$this->objeto_js}.evt__anio_hasta__validar = function(fila)
		{
			if( this.ef('anio_hasta').ir_a_fila(fila).get_estado() >= this.ef('anio_desde').ir_a_fila(fila).get_estado()){
				return true;
			}else{
				this.ef('anio_hasta').set_error('El campo \'Año hasta\' debe ser mayor o igual al campo \'Año desde\'');
				return false;
			};
		}
		";
	}

}
?>