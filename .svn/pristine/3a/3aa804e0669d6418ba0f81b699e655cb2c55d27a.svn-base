<?php
class ci_requisitos_convocatoria extends becas_ci
{
	protected $s__estado_inicial = array();
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__procesar()
	{
	}

	function evt__cancelar()
	{
	}

	function evt__volver()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_convocatorias ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_convocatorias(becas_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(toba::consulta_php('co_convocatoria_beca')->get_convocatorias(array(),FALSE));
	}

	function evt__cuadro_convocatorias__seleccion($seleccion)
	{
		$this->datos('datos')->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- ml_requisitos_conv -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_requisitos_conv(becas_ei_formulario_ml $form_ml)
	{
		if($this->datos('datos')->esta_cargada()){
			$form_ml->set_datos($this->datos('datos','requisitos_convocatoria')->get_filas());
			$this->s__estado_inicial = $form_ml->get_datos();
		}
	}
	
	function evt__ml_requisitos_conv__modificacion($datos)
	{
	}



	//-----------------------------------------------------------------------------------
	//---- Datos ------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	


	function datos($relacion = NULL, $tabla = NULL)
	{
		if($relacion){
			if($tabla){
				return $this->dep($relacion)->tabla($tabla);
			}else{
				return $this->dep($relacion);
			}
		}else{
			if($tabla){
				return $this->dep($tabla);
			}else{
				return false;
			}
		}
	}

}

?>