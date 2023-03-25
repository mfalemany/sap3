<?php
class form_ml_becas_obtenidas extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Validacion de EFs -----------------------------------
		
		{$this->objeto_js}.evt__fecha_desde__validar = function(fila)
		{
			desde = this.ef('fecha_desde').ir_a_fila(fila).get_estado().split('/');
			desde = new Date(desde[2],(desde[1]-1),desde[0]);
			if (desde <= new Date(".date('Y').",".(date('m')-1).",".date('d').")){
				return true;
			}else{
				this.ef('fecha_desde').set_error('La fecha debe ser anterior o igual a la fecha actual (".date('d/m/Y').")');
				return false;	
			}
		}
		
		{$this->objeto_js}.evt__fecha_hasta__validar = function(fila)
		{
			desde = this.ef('fecha_desde').ir_a_fila(fila).get_estado().split('/');
			desde = new Date(desde[2],(desde[1]-1),desde[0]);

			hasta = this.ef('fecha_hasta').ir_a_fila(fila).get_estado().split('/');
			hasta = new Date(hasta[2],(hasta[1]-1),hasta[0]);
			if (hasta >= desde){
				return true;
			}else{
				this.ef('fecha_hasta').set_error('La fecha es anterior a \'Fecha de inicio\'');
				return false;	
			}
		}
		";
	}
}
?>