<?php
class ci_asignar_evaluadores extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- cu_evaluadores ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_evaluadores(sap_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(toba::consulta_php('co_grupos')->get_evaluadores_y_grupos_asignados());
	}

	function evt__cu_evaluadores__borrar($datos)
	{
		try {
			$this->get_datos('sap_grupo_evaluador')->cargar($datos);
			$this->get_datos('sap_grupo_evaluador')->eliminar_todo();
			$this->get_datos('sap_grupo_evaluador')->resetear();
			toba::notificacion()->info('Borrado con éxito!');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		} catch (Exception $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->getMessage());
		}
		
	}

	//-----------------------------------------------------------------------------------
	//---- form_evaluador ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__form_evaluadores__modificacion($datos)
	{
		try {
			$this->get_datos('sap_grupo_evaluador')->nueva_fila($datos);
			$this->get_datos('sap_grupo_evaluador')->sincronizar();
			$this->get_datos('sap_grupo_evaluador')->resetear();
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		} catch (Exception $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->getMessage());
		}
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