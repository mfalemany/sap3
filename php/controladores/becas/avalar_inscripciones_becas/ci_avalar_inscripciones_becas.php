<?php
class ci_avalar_inscripciones_becas extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf(){
		//Solo los decanos y directores pueden avalar todo de una sola vez
		
		if( ! $this->es_decano()){
			if($this->dep('cu_aval_inscripciones')->existe_evento('avalar_todo')){
				$this->dep('cu_aval_inscripciones')->eliminar_evento('avalar_todo');
			}
		}
	}

	function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
		$inscripcion = $this->get_datos('inscripcion_conv_beca')->get();
		
		$ruta = __DIR__ . '/template_pant_aval.php';
		$datos = toba::consulta_php('co_becas')->get_resumen_inscripcion($inscripcion);
		$datos['ruta_documentos'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$datos['url_documentos'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');

		$template = $this->armar_template($ruta,$datos);
		$this->pantalla()->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__volver()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__guardar()
	{
		$this->get_datos()->sincronizar();
		$this->get_datos()->resetear();
		toba::notificacion()->agregar('Cambios guardados!','info');
		$this->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_aval_inscripciones --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_aval_inscripciones(sap_ei_cuadro $cuadro)
	{
		$filtro = array();
		$cargos = $this->get_cargos_autoridad(toba::usuario()->get_id());
		if( ! empty($cargos)){
			$filtro['cargos'] = $cargos;	
		}
		$filtro['dirigido_por'] = toba::usuario()->get_id();

		$datos = toba::consulta_php('co_becas')->get_inscripciones_pendientes_aval($filtro);
		if( ! $datos){
			if($cuadro->existe_evento('avalar_todo')){
				$cuadro->eliminar_evento('avalar_todo');
			}
		}else{
			$cuadro->set_datos($datos);
		}
		
	}

	function evt__cu_aval_inscripciones__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	function evt__cu_aval_inscripciones__avalar_todo(){
		$usuario = toba::usuario()->get_id();
		$cargos = $this->get_cargos_autoridad($usuario);
		if( ! empty($cargos)) {
			$postulaciones = toba::consulta_php('co_becas')->get_inscripciones_pendientes_aval(
				array('cargos'=>$cargos,'dirigido_por'=>$usuario)
			);
			$cant_postulacions_avaladas = 0;
			foreach($postulaciones as $postulacion){
				if(toba::consulta_php('co_becas')->avalar_decanato_postulacion($postulacion,1)){
					$cant_postulacions_avaladas++;
				}
			}
			toba::notificacion()->agregar("Se avalaron $cant_postulacions_avaladas postulaciones con �xito!",'info');
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cu_postulaciones_avaladas ----------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_postulaciones_avaladas(sap_ei_cuadro $cuadro)
	{
		$avales = toba::consulta_php('co_becas')->get_avales_realizados(toba::usuario()->get_id());
		$cuadro->set_datos($avales);
	}

	//-----------------------------------------------------------------------------------
	//---- form_aval_beca ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_aval_beca(sap_ei_formulario $form)
	{

	}

	function evt__form_aval_beca__modificacion($datos)
	{
		$usuario = toba::usuario()->get_id();
		$insc = $this->get_datos('inscripcion_conv_beca')->get();
		
		//Obtiene un array de tipo: array('DECANO'=>3,'SECRE'=>4) : Es decano de la dependencia 3 y secretario de la dependencia 4
		$cargos = $this->get_cargos_autoridad($usuario);
		if($cargos){
			$cargos = array_column($cargos,'id_dependencia','identificador');
			
		}
		//Obtiene la dependencia de la beca (facultad si es pregrado o lugar de trabajo para las otras)
		$dep_beca = ($insc['id_tipo_beca'] == 1) ? $insc['id_dependencia'] : $insc['lugar_trabajo_becario'];
		
		
		//Si es decano
		if($this->es_decano()){
			//Y es decano de la unidad academica que corresponde avalar a la postulacion...
			if($cargos['DECANO'] == $dep_beca){
				$datos['aval_decanato'] = boolval($datos['aval']);
				$datos['aval_decanato_fecha'] = date('Y-m-d H:i:s');
				$datos['decano_avalo'] = $usuario;
			}
		}
		//Si es director (lo mismo que para decano)
		if($this->es_director() ){
			//Y es director de la unidad academica que corresponde avalar a la postulacion...
			if($cargos['DIRECTOR'] == $dep_beca){
				$datos['aval_decanato'] = boolval($datos['aval']);
				$datos['aval_decanato_fecha'] = date('Y-m-d H:i:s');
				$datos['decano_avalo'] = $usuario;
			}
		}

		//Si es director del proyecto marco 
		if($this->es_director_proyecto($usuario, $insc['id_proyecto'])){
			$datos['aval_dir_proyecto'] = boolval($datos['aval']);
			$datos['aval_dir_proyecto_fecha'] = date('Y-m-d H:i:s');
			$datos['dir_proyecto_avalo'] = $usuario;
			
		}
		//Si es secretario
		if($this->es_secretario()){
			//Y el cargo de secretario es de la unidad academico que corresponde avalar a la postulacion...
			if($cargos['SECRE'] == $dep_beca){
				$datos['aval_secretaria'] = boolval($datos['aval']);
				$datos['aval_secretaria_fecha'] = date('Y-m-d H:i:s');
				$datos['secretario_avalo'] = $usuario;
			}
		}
		if($insc['nro_documento_dir'] == toba::usuario()->get_id()){
			$datos['aval_director'] = boolval($datos['aval']);
			$datos['aval_director_fecha'] = date('Y-m-d H:i:s');
			$datos['director_avalo'] = $usuario;
		}
		if($insc['nro_documento_codir'] == toba::usuario()->get_id()){
			$datos['aval_director'] = boolval($datos['aval']);
			$datos['aval_director_fecha'] = date('Y-m-d H:i:s');
			$datos['director_avalo'] = $usuario;
		}
		unset($datos['aval']);
		$this->get_datos('inscripcion_avales')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------


	function get_cargos_autoridad($nro_documento)
	{
		return toba::consulta_php('co_personas')->get_cargos_autoridad($nro_documento);
	}

	function get_datos($tabla = NULL){
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	function es_decano(){
		return $this->es_autoridad_tipo('DECANO');  
	}
	function es_director(){
		return $this->es_autoridad_tipo('DIRECTOR');  
	}
	function es_secretario(){
		return $this->es_autoridad_tipo('SECRE');
	}

	function es_director_proyecto($nro_documento,$id_proyecto)
	{
		$director = toba::consulta_php('co_proyectos')->get_director_proyecto($id_proyecto);
		return (isset($director['nro_documento']) && ($director['nro_documento'] == $nro_documento));
	}

	function es_autoridad_tipo($identificador_cargo){
		$cargos = $this->get_cargos_autoridad(toba::usuario()->get_id());
		if(empty($cargos)) return FALSE;
		return (in_array($identificador_cargo,array_column($cargos,'identificador')));   
	}
	

}
?>