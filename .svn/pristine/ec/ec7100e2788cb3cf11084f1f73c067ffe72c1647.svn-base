<?php
class ci_reporte_informes extends sap_ci
{
	protected $s__filtro;
	
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
	}

	function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__volver()
	{
		$this->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_informes ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_informes(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$filtro = isset($this->s__filtro) ? $this->s__filtro : array();
		$cuadro->set_datos(toba::consulta_php('co_proyectos_informes')->get_informes_cuadro($filtro));
	}

	function evt__cu_informes__seleccion($seleccion)
	{
		$tipos = array('0'=>'pi', 'D'=>'pdts');
		$datos = toba::consulta_php('co_proyectos_informes')->get_reporte_informe($tipos[$seleccion['tipo']],$seleccion['id_informe']);
		$template = $this->armar_template(__DIR__.'/template_reporte_informe.php',$datos);

		
		$this->set_pantalla('pant_edicion');
		$this->pantalla('pant_edicion')->set_template($template);
		
		
	}

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
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function servicio__ver_pdf()
	{
		$params = toba::memoria()->get_parametros();
		
		if( ! isset($params['id_informe'])) die ('No se recibió un ID de informe para generar su reporte');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/proyectos/informes/reporte_informe_proyecto.php' , 'sap' );
		
		$informe = toba::consulta_php('co_proyectos_informes')->get_reporte_informe($params['tipo_desc'], $params['id_informe']);
		$reporte = new Reporte_informe_proyecto($informe);
		
		$reporte->mostrar();
	}

}
?>