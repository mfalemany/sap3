<?php
class ci_tipos_de_convocatoria extends sap_ci
{
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(sap_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(toba::consulta_php('co_convocatoria_beca')->get_tipos_convocatoria());
	}

	function evt__cuadro__eliminar($datos)
	{
		$this->dep('datos')->resetear();
		$this->dep('datos')->cargar($datos);
		$this->dep('datos')->eliminar_todo();
		$this->dep('datos')->resetear();
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(sap_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('tipos_convocatoria')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('tipos_convocatoria')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('tipos_convocatoria')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>