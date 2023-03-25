<?php
class ci_criterios_evaluacion extends sap_ci
{
	protected $s__convocatoria_seleccionada;
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__volver_a_convocatorias()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_convocatorias');
		unset($this->s__convocatoria_seleccionada);
	}

	function evt__volver_a_criterios()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_criterios');
	}


	function evt__guardar()
	{
		$this->get_datos()->sincronizar();
		$this->get_datos()->resetear();
		$this->evt__volver_a_criterios();
	}

	function evt__nuevo()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_subcriterios');
	}

	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_convocatorias -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_convocatorias(sap_ei_cuadro $cuadro)
	{
		$convocatorias_todas = toba::consulta_php('co_convocatoria_beca')->get_convocatorias_todas();
		$cuadro->set_datos($convocatorias_todas);
	}

	function evt__cu_convocatorias__seleccion($seleccion)
	{
		$this->s__convocatoria_seleccionada = $seleccion;
		$this->set_pantalla('pant_criterios');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_criterios -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_criterios(sap_ei_cuadro $cuadro)
	{
		if (!isset($this->s__convocatoria_seleccionada)) {
			toba::notificacion()->agregar('No se ha seleccionado una convocatoria');
		}
		$datos = toba::consulta_php('co_becas')->get_criterios_evaluacion($this->s__convocatoria_seleccionada);
		
		if ($datos) {
			$cuadro->set_datos($datos);
		}
	}

	function evt__cu_criterios__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_subcriterios');
	}

	//-----------------------------------------------------------------------------------
	//---- form_criterios ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_criterio(sap_ei_formulario $form)
	{
		$datos           = $this->get_datos('be_tipo_beca_criterio_eval')->get();
		$id_convocatoria = !empty($datos['id_convocatoria']) ? $datos['id_convocatoria'] : $this->s__convocatoria_seleccionada['id_convocatoria'];
		
		$tipos_beca = toba::consulta_php('co_becas')->get_tipos_beca_por_convocatoria($id_convocatoria, true);
		$tipos_beca = array_column($tipos_beca, 'tipo_beca', 'id_tipo_beca');
		$form->ef('id_tipo_beca')->set_opciones($tipos_beca);
		
		if ($datos) {
			$form->set_datos($datos);
		}
	}

	function evt__form_criterio__modificacion($datos)
	{
		if (empty($datos['id_convocatoria'])) {
			$datos['id_convocatoria'] = $this->s__convocatoria_seleccionada['id_convocatoria'];
		}
		$this->get_datos('be_tipo_beca_criterio_eval')->set($datos);
	}

	

	

	//-----------------------------------------------------------------------------------
	//---- ml_subcriterios --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_subcriterios(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('be_subcriterio_evaluacion')->get_filas();

		if ($datos) {
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_subcriterios__modificacion($datos)
	{
		$this->get_datos('be_subcriterio_evaluacion')->procesar_filas($datos);
	}
}
?>