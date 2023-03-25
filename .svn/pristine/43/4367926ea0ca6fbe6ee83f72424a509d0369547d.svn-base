<?php
class ci_mis_evaluaciones extends sap_ci
{
	protected $s__comunicacion;
	//-----------------------------------------------------------------------------------
	//---- cu_comunicaciones ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_comunicaciones(sap_ei_cuadro $cuadro)
	{
		$filtro = ( ! $this->soy_admin()) ? array('usuario_id' => toba::usuario()->get_id()) : array();
		$cuadro->set_datos(toba::consulta_php('co_comunicaciones')->get_comunicaciones($filtro));
	}

	function evt__cu_comunicaciones__seleccion($seleccion)
	{
		$this->s__comunicacion = $seleccion;
	}

	//-----------------------------------------------------------------------------------
	//---- cu_evaluaciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_evaluaciones(sap_ei_cuadro $cuadro)
	{
		if(isset($this->s__comunicacion)){
			if (!toba::consulta_php('co_comunicaciones')->becarios_pueden_ver_evaluaciones($this->s__comunicacion['sap_convocatoria_id'])) {
				if (!$this->soy_admin()) {
					$cuadro->agregar_notificacion('Las evaluaciones todavìa no están disponibles');
					$this->dep('cu_evaluaciones')->colapsar();
					return;
				} else {
					$cuadro->agregar_notificacion('Estas evaluaciones no son visibles por el becario');
				}
			}

			$evaluaciones = toba::consulta_php('co_comunicaciones')->get_evaluaciones_comunicacion($this->s__comunicacion['id']);
			
			//Si no es admin, solo ve la cantidad de evaluadores que participaron (no quienes)
			if (!$this->soy_admin()) {
				//El becario solo ve la última evaluacion
				if (count($evaluaciones)) {
					$evaluaciones                   = [$evaluaciones[0]];
					$cant_evaluadores               = count(explode('/', $evaluaciones[0]['evaluadores']));
					$evaluaciones[0]['evaluadores'] = ($cant_evaluadores > 1) 
					? sprintf('%s evaluadores participaron en esta evaluación', $cant_evaluadores)
					: 'Esta evaluación fue realizada solo por un evaluador';
				}
			}
			
			$cuadro->set_datos($evaluaciones);
		}
	}

}

?>