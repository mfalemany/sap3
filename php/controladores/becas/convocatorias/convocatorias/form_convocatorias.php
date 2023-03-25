<?php
class form_convocatorias extends sap_ei_formulario
{
	function extender_objeto_js()
	{
		echo "{$this->objeto_js}.evt__fecha_hasta__procesar = function(es_inicial)
		{
			if( ! es_inicial){
				{$this->objeto_js}.ef('limite_movimientos').set_estado({$this->objeto_js}.ef('fecha_hasta').get_estado());
			}
		}
		";
	}

}
?>