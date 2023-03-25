<?php
class ci_ver_evaluaciones extends sap_ci
{
	protected $s__seleccion;
	function conf()
	{
		$archivo = __DIR__."/template_ver_evaluaciones.php";
		$datos = toba::consulta_php('co_grupos')->get_evaluaciones($this->s__seleccion['id_grupo']);

		$tpl = $this->armar_template($archivo,$datos);
		$this->pantalla()->set_template($tpl);
	}

	function get_datos($tabla = NULL)
	{
		return $this->controlador()->get_datos($tabla);
	}

	function set_seleccion($seleccion)
	{
		$this->s__seleccion = $seleccion;
	}
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__volver()
	{
		unset($this->s__seleccion);
		$this->controlador()->set_pantalla('pant_seleccion');
	}

}
?>