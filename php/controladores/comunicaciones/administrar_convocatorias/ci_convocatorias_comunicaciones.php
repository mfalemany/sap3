<?php
class ci_convocatorias_comunicaciones extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{

	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__nueva()
	{
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
			$this->set_pantalla('pant_seleccion');
		} catch (Exception $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->getMessage());
		}
				
	}

	//-----------------------------------------------------------------------------------
	//---- cu_convocatorias -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_convocatorias(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$datos = toba::consulta_php('co_comunicaciones')->get_convocatorias_todas();
		$cuadro->set_datos($datos);
	}

	function evt__cu_convocatorias__seleccion($seleccion)
	{
		$this->get_datos('sap_convocatoria')->cargar($seleccion);
		$this->set_pantalla('pant_edicion'); 
	}

	//-----------------------------------------------------------------------------------
	//---- form_convocatoria ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_convocatoria(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('sap_convocatoria')->get();
		if ($datos) {
			$datos = toba::consulta_php('co_comunicaciones')->extract_custom_params($datos);
			$form->set_datos($datos);
		}

	}

	function evt__form_convocatoria__modificacion($datos)
	{
		$datos['aplicable'] = 'BECARIOS';
		$datos['custom_params'] = [
			'evaluacion_abierta'      => $datos['evaluacion_abierta'],
			'becario_ve_evaluaciones' => $datos['becario_ve_evaluaciones'],
			'becario_ve_certificados' => $datos['becario_ve_certificados'],
		];
		//Elimino los indices que no forman parte del DT
		foreach ($datos['custom_params'] as $param => $valor) {
			unset($datos[$param]);
		}
		$datos['custom_params'] = json_encode($datos['custom_params']);
		$this->get_datos('sap_convocatoria')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	protected function get_datos($tabla = null)
	{
		return $tabla ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	

}
?>