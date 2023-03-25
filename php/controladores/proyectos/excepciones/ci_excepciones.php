<?php
class ci_excepciones extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- cu_excepciones ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_excepciones(sap_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(toba::consulta_php('co_proyectos')->get_excepciones_dir(array('P','F')));
	}

	function evt__cu_excepciones__borrar($seleccion)
	{
		$this->dep('sap_excepcion_dir')->cargar($seleccion);
		$this->dep('sap_excepcion_dir')->eliminar_filas();
		$this->dep('sap_excepcion_dir')->sincronizar();
		$this->dep('sap_excepcion_dir')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- form_excepcion ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_excepcion(sap_ei_formulario $form)
	{
		$datos = $this->dep('sap_excepcion_dir')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_excepcion__modificacion($datos)
	{
		$this->dep('sap_excepcion_dir')->set($datos);
		$this->dep('sap_excepcion_dir')->sincronizar();
		$this->dep('sap_excepcion_dir')->resetear();
	}

}

?>