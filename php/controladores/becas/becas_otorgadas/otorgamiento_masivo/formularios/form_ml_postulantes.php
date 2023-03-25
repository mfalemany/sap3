<?php
class form_ml_postulantes extends sap_ei_formulario_ml
{
	function extender_objeto_js()
	{
		echo "
		/*
		
		//---- Validacion general ----------------------------------
		
		{$this->objeto_js}.evt__validar_datos = function()
		{
			form = {$this->objeto_js};
			filas = {$this->objeto_js}.filas();
			for (id_fila in filas) {

				//Hace obligatorios los campos si el checkbox est� tildado
				obligatorio = form.ef('seleccionado').ir_a_fila(filas[id_fila]).get_estado();
				obligatorio = (obligatorio !== null);

				console.log(obligatorio);
				console.log(form.ef('fecha_desde').ir_a_fila(filas[id_fila]));

				form.ef('fecha_desde').ir_a_fila(filas[id_fila]).set_obligatorio(obligatorio);
				form.ef('fecha_hasta').ir_a_fila(filas[id_fila]).set_obligatorio(obligatorio);
			}
			return true;
		}

		*/
		
		";
	}

}
?>