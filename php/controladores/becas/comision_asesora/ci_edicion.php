<?php
class ci_edicion extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- formulario  ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__formulario(sap_ei_formulario $form)
	{
		if($this->get_datos('comision_asesora')->get()){
			$form->set_datos($this->get_datos('comision_asesora')->get());
			$form->set_solo_lectura();
		}
	}

	function evt__formulario__modificacion($datos)
	{
		$this->get_datos('comision_asesora')->set($datos);
	}


	//-----------------------------------------------------------------------------------
	//---- ml_integrantes ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	//
	function conf__ml_integrantes(sap_ei_formulario_ml $form)
	{
		if($this->get_datos('comision_asesora_integrante')->get_filas()){
			$integrantes = $this->get_datos('comision_asesora_integrante')->get_filas();
			foreach($integrantes as $i => $registro){
				$integrantes[$i]['persona'] = $registro['nro_documento']; 
			}
			
			$form->set_datos($integrantes);
		}
	}

	function evt__ml_integrantes__modificacion($datos)
	{
		//ei_arbol($datos);
		$integrantes = array();
		foreach($datos as $indice => $registro){
			$integrantes[$indice] = array('nro_documento' => $registro['persona'],
								  'x_dbr_clave'           => $registro['x_dbr_clave'],
								  'apex_ei_analisis_fila' => $registro['apex_ei_analisis_fila']);
		}
		$this->get_datos('comision_asesora_integrante')->procesar_filas($integrantes);
	}

	function get_datos($tabla = NULL)
	{
		if( ! $tabla){
			return $this->controlador()->dep('datos');
		}else{
			return $this->controlador()->dep('datos')->tabla($tabla);
		}
	}
}
?>