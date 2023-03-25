<?php
class ci_dependencias extends sap_ci{
	
	protected $s__filtro_universidades;

	/**
	 *  ================================ UNIVERSIDADES ==========================================
	 */

	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro_universidad(toba_ei_formulario $filtro)
	{
		if (isset($this->s__filtro_universidades)) {
			$filtro->set_datos($this->s__filtro_universidades);
		}
	}

	function evt__filtro_universidad__filtrar($datos)
	{
		$this->s__filtro_universidades = $datos;
	}

	function evt__filtro_universidad__cancelar()
	{
		unset($this->s__filtro_universidades);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cu_universidades(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__filtro_universidades)) {
			$cuadro->set_datos(toba::consulta_php('co_tablas_basicas')->get_universidades($this->s__filtro_universidades));
		} else {
			$cuadro->set_datos(toba::consulta_php('co_tablas_basicas')->get_universidades());
		}
	}

	function evt__cu_universidades__seleccion($datos)
	{
		$this->datos('universidad')->cargar($datos);
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__form_universidad(toba_ei_formulario $form)
	{
		if ($this->datos('universidad')->esta_cargada()) {
			$form->set_datos($this->datos('universidad')->get());
		} 
	}

	function evt__form_universidad__guardar($datos)
	{
		$this->datos('universidad')->set($datos);
		$this->datos('universidad')->sincronizar();
		$this->datos('universidad')->resetear();
	}

	function evt__form_universidad__modificar($datos)
	{
		$this->datos('universidad')->set($datos);
		$this->datos('universidad')->sincronizar();
		$this->datos('universidad')->resetear();
	}

	function evt__form_universidad__cancelar()
	{
		$this->datos('universidad')->resetear();
	}

	function evt__form_universidad__borrar()
	{
		$this->datos('universidad')->eliminar_todo();
		$this->datos('universidad')->resetear();
	}



	function datos($tabla = null)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos') ;
	}

}
?>