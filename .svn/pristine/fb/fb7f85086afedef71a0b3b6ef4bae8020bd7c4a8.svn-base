<?php
class ci_reportes extends sap_ci
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
	//---- cu_becarios ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_becarios(sap_ei_cuadro $cuadro)
	{
		
		if(isset($this->s__filtro)){
			$filtro = array_merge($this->s__filtro,array('beca_otorgada'=>'S','admisible'=>'S'));
			$cuadro->set_datos(toba::consulta_php('co_inscripcion_conv_beca')->get_inscripciones($filtro));
		}
	}

}
?>