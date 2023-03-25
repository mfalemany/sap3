<?php
class form_ml_requisitos extends becas_ei_formulario_ml
{

	function evt__pedido_registro_nuevo()
	{
		echo "NADA";
	}
	function set_registro_nuevo()
	{
		//Si o si tiene que haber una convocatoria cargada, pues se selecciona del cuadro
		$conv = $this->controlador()->datos('datos','convocatoria_beca')->get();
		if(!$conv){
			throw new toba_error('No se ha seleccionado una convocatoria a la cual asignar requisitos');
		}
		return array('Pepe',array("1"=>"Simulacro"),1);
	}

}

?>