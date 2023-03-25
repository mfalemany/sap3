<?php
class ci_reporte_proyectos extends sap_ci
{
	protected $s__filtro;
	//-----------------------------------------------------------------------------------
	//---- cu_proyectos -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_proyectos(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$filtro = array('solo_propios'=>TRUE,'estado'=>'C');
		$filtro = isset($this->s__filtro) ? array_merge($this->s__filtro,$filtro) : $filtro;
		$cuadro->set_datos(toba::consulta_php('co_proyectos')->get_proyectos($filtro));
	}

	function evt__cu_proyectos__seleccion($seleccion)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_proyectos -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_proyectos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_proyectos__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_proyectos__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- Servicios --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function servicio__ver_formulario()
	{
		$params = toba::memoria()->get_parametros();
		$proyecto_id = $params['id'];
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/proyectos/formulario_proyecto.php' , 'sap' );
		
		$proyecto = toba::consulta_php('co_proyectos')->get_reporte_proyecto($proyecto_id);
		$proyecto['ver_completo'] = FALSE;
		$reporte = new Reporte_proyecto($proyecto);
		
		$reporte->mostrar();	
	}

	
}
?>