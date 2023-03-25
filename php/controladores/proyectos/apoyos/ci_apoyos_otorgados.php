<?php
class ci_apoyos_otorgados extends sap_ci
{
	protected $s__filtro;


	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__guardar()
	{
		try {
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			$this->set_pantalla('pant_seleccion');	
		} catch (toba_error $e) {
			toba::notificacion()->agregar('Ocurrió el siguiente error: '.$e->get_mensaje());
		}
		
	}

	function evt__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');	
	}


	//-----------------------------------------------------------------------------------
	//---- cu_apoyos_otorgados ----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_apoyos_otorgados(sap_ei_cuadro $cuadro)
	{
		$filtro = (isset($this->s__filtro)) ? $this->s__filtro : array();
		$cuadro->set_datos(toba::consulta_php('co_apoyos')->get_apoyos_otorgados($filtro));
	}

	function evt__cu_apoyos_otorgados__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_apoyos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_apoyos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_apoyos__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_apoyos__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- form_apoyo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_apoyo(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('sap_apoyo_otorgado')->get();
		if($datos){
			$form->set_datos($datos);
		}

	}

	function evt__form_apoyo__modificacion($datos)
	{
		$this->get_datos('sap_apoyo_otorgado')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	
}
?>