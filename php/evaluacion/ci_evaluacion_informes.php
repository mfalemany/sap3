<?php
class ci_evaluacion_informes extends becas_ci
{
	protected $area_conoc;
	protected $ruta_base_documentos;
	protected $url_base_documentos;
	private $hay_plazo_habilitado;
	private $seleccion;
	private $detalles_beca;
	protected $s__filtro;


	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		//Se obtiene la base de documentos por �nica vez.
		$this->ruta_base_documentos = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$this->url_base_documentos = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$this->hay_plazo_habilitado = (count(toba::consulta_php('co_informes')->get_plazos(TRUE, 'E', FALSE)) > 0);
		
		if($this->soy_admin()){
			return;
		}
		if( ! in_array('integrante_comision_asesora',toba::usuario()->get_perfiles_funcionales()) ){
			throw new toba_error('Usted no es integrante de alguna Comisi�n Asesora. No puede evaluar informes de becas.');
		}  

		$usuario = toba::usuario()->get_id();
		$this->area_conoc = toba::consulta_php('co_comision_asesora')->get_area_conocimiento_evaluador($usuario);
	}
	//-----------------------------------------------------------------------------------
	//---- Eventos del CI ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function evt__guardar()
	{
		try {
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			$this->set_pantalla('pant_becarios');
			toba::notificacion()->agregar('Evaluaci�n registrada con �xito!','info');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurri� un error al intentar guardar la evaluaci�n',$e->get_mensaje_motor());
		}
	}

	function evt__volver()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_becarios');
	}

	//-----------------------------------------------------------------------------------
	//---- form_filtro ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_filtro(becas_ei_formulario $form)
	{
		if(isset($this->s__filtro) && $this->s__filtro){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__form_filtro__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__form_filtro__cancelar()
	{
		unset($this->s__filtro);
	}


	//-----------------------------------------------------------------------------------
	//---- cu_becas ---------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_becas(becas_ei_cuadro $cuadro)
	{
		$filtro = ($this->area_conoc) ? array('id_area_conocimiento'=>$this->area_conoc) : array();
		if(isset($this->s__filtro)){
			$filtro = array_merge($filtro,$this->s__filtro);
		}
		$cuadro->set_datos(toba::consulta_php('co_informes')->get_informes_pendientes_evaluacion($filtro));
	}

	function evt__cu_becas__seleccion($seleccion)
	{
		$this->seleccion = $seleccion;
		$this->detalles_beca = toba::consulta_php('co_becas_otorgadas')->get_detalles_beca(
			$this->seleccion['id_convocatoria'],
			$this->seleccion['id_tipo_beca'],
			$this->seleccion['nro_documento']
		);
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_informes');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_informes ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_informes(becas_ei_cuadro $cuadro)
	{
		$det = $this->detalles_beca;
		$titulo = sprintf('Becario: %s.<br>Beca de %s (%s) dirigida por %s.<br>Periodo de la beca: %s al %s',
			$det['postulante'],
			strtolower($det['tipo_beca']),
			$det['convocatoria'],
			$det['director'],
			date('d-m-Y',strtotime($det['fecha_desde'])), 
			date('d-m-Y',strtotime($det['fecha_hasta']))
		);
		$cuadro->set_titulo($titulo);
		$cuadro->desactivar_modo_clave_segura();
		if(! $this->soy_admin() ){
			if(! $this->hay_plazo_habilitado){
				$cuadro->agregar_notificacion('No hay periodo de evaluaci�n abierto en este momento','warning');
				return;
			}
		}
		$informes = toba::consulta_php('co_informes')->get_informes_presentados($this->seleccion,TRUE);
		if($informes > 0){
			$cuadro->set_datos($informes);	
		}
	}

	function conf_evt__cu_informes__evaluar(toba_evento_usuario $evento,$fila)
	{
		if( ! $this->hay_plazo_habilitado){
			$evento->desactivar();
			return;
		}
	}

	function evt__cu_informes__evaluar($seleccion)
	{
		$this->get_datos('informe_evaluacion')->set($seleccion);
		$this->set_pantalla('pant_evaluacion');
	}
	
	function conf_evt__cu_informes__ver_informe(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		if(file_exists($this->get_ruta_informe($params))){
			$evento->activar();
		}else{
			$evento->desactivar();
		}
	}

	function servicio__ver_informe(){
		$params = array_values(toba::memoria()->get_parametros());
		if(file_exists($this->get_ruta_informe($params))){
			$this->no_cache();
			header("Location: ".utf8_encode($this->get_ruta_informe($params,TRUE)));
		}else{
			echo 'Lo sentimos... No hemos encontrado el informe seleccionado.';
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_evaluacion --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_evaluacion(becas_ei_formulario $form)
	{
		$datos = $this->get_datos('informe_evaluacion')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_evaluacion__modificacion($datos)
	{
		if( ! (isset($datos['nro_documento_evaluador']) && $datos['nro_documento_evaluador'])){
			$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		}
		$this->get_datos('informe_evaluacion')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/**
	 * Dependiendo del segundo par�metro, retorna:
	 * - FALSE: La ruta del sistema de archivos donde est�n los documentos (desde la ra�z)
	 * - TRUE: la url accesible a los documentos
	 */
	private function get_ruta_informe($informe,$formato_url=FALSE)
	{
		list($id_conv,$id_tipo_beca,$nro_documento,$id_informe,$nro_informe) = $informe;
		$ruta = sprintf("/becas/doc_por_convocatoria/%s/%s/%s/informes/%s.pdf",$id_conv,$id_tipo_beca,$nro_documento,$nro_informe);
		return ($formato_url) ? $this->url_base_documentos.$ruta : $this->ruta_base_documentos.$ruta;
	}


}
?>