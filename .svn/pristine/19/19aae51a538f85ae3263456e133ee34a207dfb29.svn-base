<?php
class form_ml_requisitos extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
				
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__cumplido__procesar = function(es_inicial, fila)
		{
			if( ! es_inicial){
				if(this.ef('cumplido').get_estado() == 1){
					hoy = new Date();
					anio = hoy.getFullYear();
					mes = hoy.getMonth()+1;
					dia = hoy.getDate();
					
					this.ef('fecha').ir_a_fila(fila).set_estado(dia+'/'+mes+'/'+anio);
				}else{
					this.ef('fecha').ir_a_fila(fila).set_estado('');
				}
			}

				
		}
		";
	}

}
?>