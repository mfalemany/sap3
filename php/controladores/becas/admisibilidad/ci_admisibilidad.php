<?php
class ci_admisibilidad extends sap_ci
{
	protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- FILTRO DE SOLICITUDES --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_solicitud(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);	
		}
	}

	function evt__filtro_solicitud__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_solicitud__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- CUADRO SELECCION DE SOLICITUDES  ---------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_solicitudes(sap_ei_cuadro $cuadro)
	{
		$filtro = (isset($this->s__filtro) ? $this->s__filtro : array());
		$filtro['estado'] = 'C';
		$cuadro->set_datos(toba::consulta_php('co_inscripcion_conv_beca')->get_inscripciones($filtro));
	}

	function evt__cu_solicitudes__seleccion($seleccion)
	{
		$this->get_datos('inscripcion')->cargar($seleccion);
		$this->set_pantalla('pant_edicion');

	}

	//-----------------------------------------------------------------------------------
	//---- form_admisibilidad -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_admisibilidad(sap_ei_formulario $form)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();

		
		if($insc){
			//se calcula el porcentaje de aprobacion y la cantidad de materias que adeuda para egresar.
			if(isset($insc['materias_aprobadas']) && isset($insc['materias_plan'])){
				$insc['porcentaje_aprobacion'] = $insc['materias_aprobadas'] / $insc['materias_plan'] * 100;
				$insc['mat_para_egresar'] = $insc['materias_plan'] - $insc['materias_aprobadas'];
			}
			$persona = toba::consulta_php('co_personas')->get_personas(array('nro_documento'=>$insc['nro_documento']));
			
			if(count($persona)){
				$insc['es_egresado'] = (isset($persona[0]['archivo_titulo_grado']) && $persona[0]['archivo_titulo_grado']) ? 'Si' : 'No';
			}else{
				$insc['es_egresado'] = 'No';
			}
			$form->set_datos($insc);
		}
	}

	function evt__form_admisibilidad__modificacion($datos)
	{
		//se eliminan los efs que solo muestran informaci�n relevante para la toma de decision de admisibilidad
		unset($datos['porcentaje_aprobacion']);
		unset($datos['mat_para_egresar']);

		//se asignan los datos as datos_tabla
		$this->get_datos('inscripcion','inscripcion_conv_beca')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_requisitos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_requisitos(sap_ei_formulario_ml $form_ml)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$req = toba::consulta_php('co_inscripcion_conv_beca')->get_requisitos_insc($insc['id_convocatoria'],$insc['id_tipo_beca'],$insc['nro_documento']);
		$form_ml->set_datos($req);
		
	}

	function evt__ml_requisitos__modificacion($datos)
	{
		$this->get_datos('inscripcion','requisitos_insc')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$datos['inscripcion'] = toba::consulta_php('co_inscripcion_conv_beca')->get_detalles_comprobante($insc);
		$postulante = $datos['inscripcion']['postulante'];
		$datos['postulante'] = sprintf('%s, %s (DNI: %s)',$postulante['apellido'],$postulante['nombres'],$postulante['nro_documento']);
		$datos['postulante_cargos'] = toba::consulta_php('co_personas')->get_cargos_persona($postulante['nro_documento'], true);
		
		/* =========================== AVALES ============================ */
		$datos['avales'] = toba::consulta_php('co_inscripcion_conv_beca')->get_estado_aval_solicitud($insc);
		


		/* esta variable va a contener todo lo necesario para determinar si la solicitud es admisible.
		   Se compone de los datos de cargos del director y codirector, edad del aspirante, porcentaje
		   de materias aprobadas del alumno, materias que adeuda para recibirse, si est� inscripto a un posgrado,
		   al m�ximo grado del director y codirector y la cantidad de becarios a cargo. */
		$datos_admisibilidad = array(
									'nivel_academico' => NULL,
									'mayor_dedicacion'=> FALSE,
									'cat_incentivos'  => NULL
									);

		/* ================================== DIRECTOR ================================ */
		$det = toba::consulta_php('co_personas')->get_resumen_director($insc['nro_documento_dir']);
		$detalles_cargos = toba::consulta_php('co_personas')->get_cargos_persona($insc['nro_documento_dir'],TRUE);
		$datos['direccion'][] = array_merge($det,array('cargos'=>$detalles_cargos),array('rol'=>'Director'));
		/* ============================================================================ */

		//comparo los datos del director con los datos de admisibilidad
		$this->set_datos_admisibilidad($datos_admisibilidad,$det,$detalles_cargos);

		unset($det);
		unset($detalles_cargos);

		/* ================================= CODIRECTOR =============================== */
		if($insc['nro_documento_codir']){
			$det = toba::consulta_php('co_personas')->get_resumen_director($insc['nro_documento_codir']);
			$detalles_cargos = toba::consulta_php('co_personas')->get_cargos_persona($insc['nro_documento_codir'],TRUE);
			$datos['direccion'][] = array_merge($det,array('cargos'=>$detalles_cargos),array('rol'=>'Co-Director'));

			//comparo los datos del co-director con los datos de admisibilidad
			$this->set_datos_admisibilidad($datos_admisibilidad,$det,$detalles_cargos);
		}
		/* ============================================================================ */
		unset($det);
		unset($detalles_cargos);

		/* ================================= SUBDIRECTOR =============================== */
		if($insc['nro_documento_subdir']){
			$det = toba::consulta_php('co_personas')->get_resumen_director($insc['nro_documento_subdir']);
			$detalles_cargos = toba::consulta_php('co_personas')->get_cargos_persona($insc['nro_documento_subdir'],TRUE);
			$datos['direccion'][] = array_merge($det,array('cargos'=>$detalles_cargos),array('rol'=>'Sub-Director'));

			//comparo los datos del co-director con los datos de admisibilidad
			$this->set_datos_admisibilidad($datos_admisibilidad,$det,$detalles_cargos);
		}
		/* ============================================================================ */

		//obtengo los detalles del tipo de beca seleccionado
		$det_tipo_beca = toba::consulta_php('co_becas')->get_tipos_beca(array('id_tipo_beca'=>$insc['id_tipo_beca']));
		if(count($det_tipo_beca)){
			$datos['detalles_tipo_beca'] = $det_tipo_beca[0];
		}

		//obtengo la edad del aspirante
		$datos['edad_asp'] = toba::consulta_php('co_personas')->get_edad(array('nro_documento' => $insc['nro_documento']),date('Y-12-31'));

		//Debe ser Magister/Doctor o bien tener categor�a 3 o superior de incentivod
		$datos_admisibilidad['grado_categoria'] = 
			($datos_admisibilidad['nivel_academico'] >= 6 || $datos_admisibilidad['cat_incentivos'] <= 3);
		$datos['ctrl_admisibilidad'] = $datos_admisibilidad;
		
		if(	isset($datos['detalles_tipo_beca']['debe_adeudar_hasta']) 
			   && $datos['detalles_tipo_beca']['debe_adeudar_hasta'])
		{
			$datos['materias_adeuda'] = 
			(($insc['materias_plan'] - $insc['materias_aprobadas']) <= $datos['detalles_tipo_beca']['debe_adeudar_hasta']);
		}

		// Puede que esto sea temporal (categorizaci�n transitoria de incentivos)
		$datos['solic_trans_inc_dir'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos($insc['nro_documento_dir'], 1); // EL 1 est� hardcodeado porque se hizo a las apuradas
		if ($datos['solic_trans_inc_dir']) {
			$datos['solic_trans_inc_dir']['docum'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos_documentacion($datos['solic_trans_inc_dir']['categoria'], 1);
		}

		if (!empty($insc['nro_documento_codir'])) {
			$datos['solic_trans_inc_codir'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos($insc['nro_documento_codir'], 1); // EL 1 est� hardcodeado porque se hizo a las apuradas
			
			if ($datos['solic_trans_inc_codir']) {
				$datos['solic_trans_inc_codir']['docum'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos_documentacion($datos['solic_trans_inc_codir']['categoria'], 1);
			}
		}
		
		if (!empty($insc['nro_documento_subdir'])) {
			$datos['solic_trans_inc_subdir'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos($insc['nro_documento_subdir'], 1); // EL 1 est� hardcodeado porque se hizo a las apuradas

			if ($datos['solic_trans_inc_subdir']) {
				$datos['solic_trans_inc_subdir']['docum'] = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos_documentacion($datos['solic_trans_inc_subdir']['categoria'], 1);
			}
		}

		$datos['url']  = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$datos['ruta'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$template = $this->armar_template(__DIR__ . '/template_admisibilidad.php',$datos);
		$pantalla->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__guardar()
	{
		$this->get_datos('inscripcion')->sincronizar();
		$this->get_datos('inscripcion')->resetear();
		$this->set_pantalla('pant_seleccion');

	}

	function evt__volver()
	{
		$this->get_datos('inscripcion')->resetear();
		$this->set_pantalla('pant_seleccion');
	}



	//-----------------------------------------------------------------------------------
	//---- AUXILIARES -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	

	function set_datos_admisibilidad(&$datos_admisibilidad,$det,$detalles_cargos)
	{
		//guardo el mayor nivel academico
		if( ! $datos_admisibilidad['nivel_academico']){
			$datos_admisibilidad['nivel_academico'] = $det['id_nivel_academico'];
		}else{
			if($datos_admisibilidad['nivel_academico'] < $det['id_nivel_academico']){
				$datos_admisibilidad['nivel_academico'] = $det['id_nivel_academico'];
			}
		}
		
		//guardo la categor�a de Incentivos
		if( ! $datos_admisibilidad['cat_incentivos']){
			$datos_admisibilidad['cat_incentivos'] = $det['cat_incentivos'];
		}else{
			//Si la categoria que tengo hasta ahora, es mayor que la actual, se asigna la actual
			if($datos_admisibilidad['cat_incentivos'] > $det['cat_incentivos']){
				$datos_admisibilidad['cat_incentivos'] = $det['cat_incentivos'];
			}
		}

		//verifico si existe mayor dedicacion
		foreach($detalles_cargos as $cargo){
			if(in_array($cargo['dedicacion'],array('EXCL','SEMI'))){
				$datos_admisibilidad['mayor_dedicacion'] = TRUE;
			}
		}
	}

	function get_datos($relacion = NULL, $tabla = NULL)
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