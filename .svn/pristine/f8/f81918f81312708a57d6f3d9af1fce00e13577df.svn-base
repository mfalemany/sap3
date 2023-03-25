<?php
class ci_facultades extends sap_ci
{
	protected $s__filtro;

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
	//---- cuadro_dependencia -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_dependencia(sap_ei_cuadro $cuadro)
	{
		$filtro = (isset($this->s__filtro)) ? $this->s__filtro : array(); 
		$cuadro->set_datos(toba::consulta_php('co_tablas_basicas')->get_dependencias($filtro));
	}

	function evt__cuadro_dependencia__seleccion($seleccion)
	{
		$this->datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- form_dependencia -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_dependencia(sap_ei_formulario $form)
	{
		$datos = $this->datos('dependencia')->get();
		if ($datos) {
			$form->set_datos($datos);
		}
	}

	function evt__form_dependencia__modificacion($datos)
	{
		$this->datos('dependencia')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_autoridades ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_autoridades(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->datos('dependencia_autoridad')->get_filas();
		if ($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_autoridades__modificacion($datos)
	{
		$this->datos('dependencia_autoridad')->procesar_filas($datos);
	}

	
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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

	function evt__borrar()
	{
		try {
			$this->datos()->eliminar_todo();
			$this->datos()->resetear();
			toba::notificacion()->info('Dependencia eliminada con éxito!');
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		}
		
	}

	function evt__nuevo()
	{
		$this->datos()->resetear();
		$this->set_pantalla('pant_edicion');
	}

	function datos($tabla = null)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}


}
?>