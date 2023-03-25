<?php
class ci_carreras extends sap_ci
{
	protected $s__filtro;
	//-----------------------------------------------------------------------------------
	//---- cu_carreras ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_carreras(sap_ei_cuadro $cuadro)
	{
		$filtro = isset($this->s__filtro) ? $this->s__filtro : array();
		$cuadro->desactivar_modo_clave_segura();
		$cuadro->set_datos(toba::consulta_php('co_tablas_basicas')->get_carreras($filtro));
	}

	function evt__cu_carreras__seleccion($seleccion)
	{
		$this->datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- form_carrera -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_carrera(sap_ei_formulario $form)
	{
		$datos = $this->datos('carreras')->get();
		if ($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_carrera__modificacion($datos)
	{
		$this->datos('carreras')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_dependencias --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_dependencias(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->datos('carreras_dependencia')->get_filas();
		if ($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_dependencias__modificacion($datos)
	{
		$this->datos('carreras_dependencia')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__nueva()
	{
		$this->datos()->resetear();
		$this->set_pantalla('pant_edicion');
	}

	function evt__eliminar()
	{
		try {
			$this->datos()->eliminar_todo();
			$this->datos()->resetear();
			toba::notificacion()->info('Carrera eliminada con éxito!');
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		}
	}

	function evt__cancelar()
	{
		$this->datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__guardar()
	{
		try {
			$this->datos()->sincronizar();
			$this->datos()->resetear();
			toba::notificacion()->info('Guardado con éxito!');
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		}
	}

	function datos($tabla = null)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

}

?>