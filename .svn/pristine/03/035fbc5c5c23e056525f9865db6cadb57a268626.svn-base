<?php
class ci_otorgamiento_masivo extends sap_ci
{
	protected $s__filtro;
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__otorgar()
	{
	}
	//-----------------------------------------------------------------------------------
	//---- filtro_otorgamiento ----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_otorgamiento(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_otorgamiento__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_otorgamiento__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_solicitudes ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_solicitudes(sap_ei_formulario_ml $form_ml)
	{
		//Se filtran solo las solicitudes cerradas y no otorgadas
		$filtro = array('otorgado' => 'N','estado' => 'C','order_by' => 'proyecto_codigo DESC');
		$filtro = (isset($this->s__filtro)) ? array_merge($this->s__filtro,$filtro) : $filtro;
		
		//Solicitudes que fueron cerradas, pero no se otorgaron
		$solicitudes_pendientes = toba::consulta_php('co_apoyos')->get_apoyos($filtro);
		//Parámetro que indica el monto máximo que se otorgará en el presente año
		$maximo_otorgar = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('apoyo_econ_max_otorgar');
		//Se asigna el monto a otorgar por solicitud.
		foreach($solicitudes_pendientes as &$solicitud){
			$solicitud['monto'] = ($solicitud['monto_solicitado'] <= $maximo_otorgar) ? $solicitud['monto_solicitado'] : $maximo_otorgar;
		}
		$form_ml->set_datos($solicitudes_pendientes);
		
	}

	function evt__ml_solicitudes__modificacion($datos)
	{
		foreach($datos as $solicitud){
			$monto = (isset($solicitud['monto'])) ? $solicitud['monto'] : NULL;
			if($solicitud['otorgar'] == 'S'){
				toba::consulta_php('co_apoyos')->otorgar_apoyo($solicitud['id_apoyo'],$monto);
			}
		}
	}

}

?>