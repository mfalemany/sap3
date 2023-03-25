<?php
class ci_asignar_evaluadores extends sap_ci
{
	protected $s__filtro;


	function servicio__ver_comprobante()
	{
		
		$parametros= toba::memoria()->get_parametros();
		$dni = $parametros['nro_documento'];
		$evaluador = array('nro_documento'=>$dni,'ayn'=>toba::consulta_php('co_personas')->get_solo_ayn($dni));
		$evaluaciones = toba::consulta_php('co_proyectos')->get_evaluaciones_realizadas(array('nro_documento_evaluador'=>$dni));
		$pdf = new Informe_evaluaciones_realizadas(
			array('evaluador'=>$evaluador,'evaluaciones'=>$evaluaciones)
		);
		$pdf->mostrar();
	}

	function servicio__ver_certificado()
	{
		$parametros = toba::memoria()->get_parametros();
		$evaluador = toba::consulta_php('co_proyectos')->get_detalles_evaluador($parametros['nro_documento']);
		$datos = array('evaluador'=>$evaluador);
		if(count($datos)){
			$pdf = new Certificado_evaluacion($datos);
			$pdf->mostrar();
		}
	}
/*	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_evaluadores(toba_ei_pantalla $pantalla)
	{
	}*/

	//-----------------------------------------------------------------------------------
	//---- cu_evaluadores ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_evaluadores(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
   		if (isset($this->s__filtro)) {
   			$filtro = $this->s__filtro;
   			//$filtro['nro_documento_evaluador'] = $this->s__filtro['nro_documento'];
   		} else{
   			$filtro = array();
   		}
   		
        $datos = toba::consulta_php('co_proyectos')->get_evaluaciones_asignadas($filtro);
        /*foreach ($datos as &$value) {
        	//$value['evaluaciones'] = toba::consulta_php('co_proyectos')->detalle_evaluaciones_realizadas($value['nro_documento']);
        	$value['finalizado'] = toba::consulta_php('co_proyectos')->ha_finalizado_evaluaciones($value['nro_documento']);
        }
		unset($value);
        */
        $cuadro->set_datos($datos);

	}

	function evt__cu_evaluadores__seleccion($seleccion)
	{
		$this->dep('sap_proyecto_evaluador')->cargar($seleccion);
	}

	function conf_evt__cu_evaluadores__comprobante(toba_evento_usuario $evento, $fila)
	{
		$parametros = explode('||', $evento->get_parametros());
		$dni = $parametros[0];
		if (toba::consulta_php('co_proyectos')->ha_finalizado_evaluaciones($dni)) {
			$evento->mostrar();
		} else {
			$evento->ocultar();
		}
	}

	function conf_evt__cu_evaluadores__certificado(toba_evento_usuario $evento, $fila)
	{
		$parametros = explode('||', $evento->get_parametros());
		$dni = $parametros[0];
		if(toba::consulta_php('co_proyectos')->puede_descargar_certificado($dni)){
			$evento->mostrar();
		} else {
			$evento->ocultar();
		}
	}
	
	//-----------------------------------------------------------------------------------
	//---- form_evaluadores_edicion -----------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_evaluadores_edicion(sap_ei_formulario $form)
	{
		if ($this->dep('sap_proyecto_evaluador')->esta_cargada()) {
			$form->set_datos($this->dep('sap_proyecto_evaluador')->get());
		}
	}

	function evt__form_evaluadores_edicion__baja()
	{
		$this->dep('sap_proyecto_evaluador')->set(null);
		$this->dep('sap_proyecto_evaluador')->sincronizar();
		$this->resetear();
	}

	function evt__form_evaluadores_edicion__alta($datos)
	{
		$this->dep('sap_proyecto_evaluador')->set($datos);
		$this->dep('sap_proyecto_evaluador')->sincronizar();
		$this->resetear();
	}

	function evt__form_evaluadores_edicion__modificacion($datos)
	{
		$this->dep('sap_proyecto_evaluador')->set($datos);
		$this->dep('sap_proyecto_evaluador')->sincronizar();
		$this->resetear();

	}

	function evt__form_evaluadores_edicion__cancelar()
	{
		unset($this->s__filtro);
		$this->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- form_evaluadores_proy --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_evaluadores_proy(sap_ei_formulario $form)
	{
		if (isset($this->s__filtro)) {
               $form->set_datos($this->s__filtro);
        }
	}

	function evt__form_evaluadores_proy__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__form_evaluadores_proy__cancelar()
	{
		unset($this->s__filtro);
		$this->resetear();
	}
	
	function resetear()
	{
		$this->dep('sap_proyecto_evaluador')->resetear();
	}

	function get_ayn($nro_documento)
	{
		$sql = "SELECT apellido||', '||nombres AS ayn FROM sap_personas WHERE nro_documento = ".quote($nro_documento);
		$persona = toba::db()->consultar_fila($sql);
		return (count($persona)) ? $persona['ayn'] : '';
	}
}
?>