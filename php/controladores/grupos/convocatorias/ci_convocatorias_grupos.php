<?php
class ci_convocatorias_grupos extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__nueva()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__guardar()
	{
		try {
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			toba::notificacion()->info('Datos guardados');
			$this->set_pantalla('pant_seleccion');
		} catch (Exception $e) {
			toba::notificacion()->error('Ocurrió un error: ' . $e->getMessage());
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cu_convocatorias -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_convocatorias(sap_ei_cuadro $cuadro)
	{
		$datos = toba::consulta_php('co_grupos')->get_convocatorias();
		$cuadro->set_datos(toba::consulta_php('co_grupos')->get_convocatorias());
	}

	function evt__cu_convocatorias__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- form_convocatoria ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_convocatoria(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('sap_convocatoria')->get();
		if ($datos) {
			$datos = $this->extract_custom_params($datos);
			$form->set_datos($datos);
		}
	}

	function evt__form_convocatoria__modificacion($datos)
	{
		$datos['custom_params'] = json_encode([
			'presentacion_informes_abierta' => $datos['presentacion_informes_abierta'],
			'evaluacion_informes_abierta'   => $datos['evaluacion_informes_abierta'],
		]);

		unset($datos['presentacion_informes_abierta']);
		unset($datos['evaluacion_informes_abierta']);
		
		$datos['aplicable'] = 'EQUIPOS';
		$this->get_datos('sap_convocatoria')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	public function get_datos($tabla = null)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}



}

?>