<?php
class form_ml_integrantes extends sap_ei_formulario_ml
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Se valida que no hayan dos integrantes repetidos ----------
		{$this->objeto_js}.evt__validar_datos = function()
		{
			var integrantes = [];
			filas = this.filas();
			for (id_fila in filas) {
				fila = this.ef('persona').ir_a_fila(filas[id_fila]).get_estado();
				posicion = $.inArray(fila,integrantes);
				console.log(fila);
				//console.log(integrantes);
				//console.log(posicion);
			
				if(posicion >= 0){
					pos1 = parseInt(id_fila) + 1;
					pos2 = parseInt(posicion) + 1;
					notificacion.agregar('Existen personas repetidas en la lista de integrantes (fila '+pos1+' y fila '+pos2+'). Por favor, quite alguno de los dos.');
					return false;
				}else{
					integrantes.push(fila);	
				}
			}
			return true;
		}
		";
	}

}
?>