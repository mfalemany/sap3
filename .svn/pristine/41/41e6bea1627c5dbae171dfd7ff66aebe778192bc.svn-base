<?php
class ci_comunicaciones_estados_evaluacion extends sap_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- cu_comunicacion_evaluacion ---------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__cu_comunicacion_evaluacion(sap_ei_cuadro $cuadro)
	{
		$filtro =  (isset($this->s__filtro)) ? $this->s__filtro : array();
		$datos = toba::consulta_php('co_comunicaciones')->get_comunicaciones_estado_eval($filtro);
		$cuadro->set_datos($datos);
	}
	//-----------------------------------------------------------------------------------
	//---- frm_filtro_evaluacion --------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__frm_filtro_evaluacion(sap_ei_formulario $form)
	{
		if (isset($this->s__filtro)) {
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__frm_filtro_evaluacion__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__frm_filtro_evaluacion__cancelar()
	{
		unset($this->s__filtro);
	}
}
?>