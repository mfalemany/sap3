<?php
class form_ml_activ_docentes extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Validacion de EFs -----------------------------------
		
		{$this->objeto_js}.evt__anio_egreso__validar = function(fila)
		{
			return (this.ef('anio_egreso').ir_a_fila(fila).get_estado() >= this.ef('anio_ingreso').ir_a_fila(fila).get_estado() );
		}
		";
	}

}
?>