<?php
class form_ml_necesidades extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Procesamiento de EFs --------------------------------
		
		{$this->objeto_js}.evt__id_rubro__procesar = function(es_inicial, fila)
		{
			if( ! es_inicial){

				var filas = this.filas()
				for (id_fila in filas) {
					var actual = this.ef('id_rubro').ir_a_fila(filas[id_fila]).get_estado();
					var seleccionado = this.ef('id_rubro').ir_a_fila(fila).get_estado();
					if(filas[id_fila] == fila){
						continue;
					}
					if(actual == seleccionado){
						
						notificacion.agregar('Ya se ha declarado ese rubro. No puede declarar un mismo rubro dos veces','error');
						notificacion.mostrar(this.controlador);
						notificacion.limpiar();
						this.ef('id_rubro').ir_a_fila(fila).set_estado('nopar');
						return false;
					}
					
				}
			}
		}
		";
	}


}
?>