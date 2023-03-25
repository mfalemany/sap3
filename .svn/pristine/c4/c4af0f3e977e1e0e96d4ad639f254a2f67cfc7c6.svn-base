<?php
class ci_informes_plazos extends sap_ci
{


	//-----------------------------------------------------------------------------------
	//---- cu_plazos --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_plazos(sap_ei_cuadro $cuadro)
	{
		$cuadro->set_datos(toba::consulta_php('co_becas_informes')->get_plazos());
	}

	function evt__cu_plazos__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
	}

	function evt__cu_plazos__borrar($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->get_datos()->eliminar();
		$this->get_datos()->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- form_plazos ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_plazos(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('be_informe_plazos')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	
	function evt__form_plazos__guardar($datos)
	{
		try {
			$this->get_datos('be_informe_plazos')->set($datos);
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();	
		} catch (toba_error_db $e) {
			toba::notificacion()->agregar('Ocurrió un error al intentar guardar los cambios.');
			if($e->get_codigo_motor() == '7'){
				toba::notificacion()->agregar('Verifique que la "Fecha Hasta" sea igual o mayor a la "Fecha Desde"');
			}
		}
	}

	function evt__form_plazos__cancelar()
	{
		$this->get_datos()->resetear();
	}

	protected function get_datos($tabla = NULL)
	{
		return $tabla ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}


}
?>