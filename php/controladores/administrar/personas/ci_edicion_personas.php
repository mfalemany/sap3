<?php
class ci_edicion_personas extends sap_ci
{
	//-----------------------------------------------------------------------------------
	//---- form_personas ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__form_persona(sap_ei_formulario $form)
	{
		$ruta = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$datos = $this->get_datos('personas','personas')->get();

		if ($datos) {
			$dni = $datos['nro_documento'];
			if (toba::consulta_php('helper_archivos')->existe_documento_personal($dni, 'dni')) {
				$datos['archivo_dni'] = true;
			} else {
				unset($datos['archivo_dni']);
			}

			if (toba::consulta_php('helper_archivos')->existe_documento_personal($dni, 'cvar')) {
				$datos['archivo_cvar'] = true;
			} else {
				unset($datos['archivo_cvar']);
			}

			if (toba::consulta_php('helper_archivos')->existe_documento_personal($dni, 'cbu')) {
				$datos['archivo_cbu'] = true;
			} else {
				unset($datos['archivo_cbu']);
			}

			if (toba::consulta_php('helper_archivos')->existe_documento_personal($dni, 'cuil')) {
				$datos['archivo_cuil'] = true;
			} else {
				unset($datos['archivo_cuil']);
			}
		}
		
		$template = $this->armar_template(__DIR__ . '/formularios/template_form_persona.php',$datos);
		$form->set_template($template);
		
		if($datos){
			$form->set_datos($datos);
			$form->set_solo_lectura(array('nro_documento'));
		}else{
			$this->controlador()->pantalla()->eliminar_evento('eliminar');
		}

	}

	function evt__form_persona__modificacion($datos)
	{
		$ruta = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$carpeta = 'docum_personal/'.$datos['nro_documento']."/";

		//Proceso el DNI
		if($datos['archivo_dni'] != NULL){
			if($datos['archivo_dni']['name']){
				toba::consulta_php('helper_archivos')->subir_archivo($datos['archivo_dni'],$carpeta,'dni.pdf',array('pdf'));
				$datos['archivo_dni'] = 'dni.pdf';	
			}else{
				toba::consulta_php('helper_archivos')->eliminar_archivo($ruta . '/' . $carpeta . 'dni.pdf');
			}
		}
		//Proceso el CVAR
		if($datos['archivo_cvar'] != NULL){
			if($datos['archivo_cvar']['name']){
				toba::consulta_php('helper_archivos')->subir_archivo($datos['archivo_cvar'],$carpeta,'cvar.pdf',array('pdf'));
				$datos['archivo_cvar'] = 'cvar.pdf';	
			}else{
				$datos['archivo_cvar'] = NULL;
				toba::consulta_php('helper_archivos')->eliminar_archivo($ruta . '/' . $carpeta . 'cvar.pdf');
			}
		}
		//Proceso el CBU
		if($datos['archivo_cbu'] != NULL){
			if($datos['archivo_cbu']['name']){
				toba::consulta_php('helper_archivos')->subir_archivo($datos['archivo_cbu'],$carpeta,'cbu.pdf',array('pdf'));
				unset($datos['archivo_cbu']); //No se guarda en BD
			}else{
				unset($datos['archivo_cbu']); //No se guarda en BD
				toba::consulta_php('helper_archivos')->eliminar_archivo($ruta . '/' . $carpeta . 'cbu.pdf');
			}
		}

		//Proceso el CUIL
		if($datos['archivo_cuil'] != NULL){
			if($datos['archivo_cuil']['name']){
				toba::consulta_php('helper_archivos')->subir_archivo($datos['archivo_cuil'],$carpeta,'cuil.pdf',array('pdf'));
				unset($datos['archivo_cuil']); //No se guarda en BD
			}else{
				unset($datos['archivo_cuil']); //No se guarda en BD
				toba::consulta_php('helper_archivos')->eliminar_archivo($ruta . '/' . $carpeta . 'cuil.pdf');
			}
		}
		$this->get_datos('personas','personas')->set($datos);
		
	}
	//-----------------------------------------------------------------------------------
	//---- ml_cargos --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_cargos(sap_ei_formulario_ml $form_ml)
	{
		if($this->get_datos('personas','cargos')->get_filas()){
			$form_ml->set_datos($this->get_datos('personas','cargos')->get_filas());	
		}
	}

	function evt__ml_cargos__modificacion($datos)
	{
		foreach($datos as $indice => $valor){
			switch (substr($datos[$indice]['cargo'],3,1)) {
				case 'E':
					$datos[$indice]['dedicacion'] = 'EXCL';
					break;
				case 'S':
					$datos[$indice]['dedicacion'] = 'SEMI';
					break;
				case '1':
					$datos[$indice]['dedicacion'] = 'SIMP';
					break;
			}
		}
		$this->get_datos('personas','cargos')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_cat_incentivos ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_cat_incentivos(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('personas','cat_incentivos')->get_filas();

		// Esto se usa para que, al momento de guardar, sepamos si se hicieron cambios
		$this->controlador()->set_cat_incentivos_cargadas(array_column($datos, 'categoria', 'convocatoria'));
		
		if($this->get_datos('personas','cat_incentivos')->get_filas()){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_cat_incentivos__modificacion($datos)
	{
		$this->get_datos('personas','cat_incentivos')->procesar_filas($datos);
	}

	function conf__form_cat_conicet(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('personas','cat_conicet_persona')->get();
		if ($datos) {
			$form->set_datos($datos);
			$this->controlador()->set_cat_conicet_cargada($datos);
		} 
	}

	function evt__form_cat_conicet__modificacion($datos)
	{
		if($datos['id_cat_conicet'] && $datos['lugar_trabajo']){
			$this->get_datos('personas','cat_conicet_persona')->set($datos);
		}else{
			if($datos['id_cat_conicet'] || $datos['lugar_trabajo']){
				throw new toba_error("Si carga una categor�a CONICET, debe cargar ambos campos: categor�a y lugar de trabajo.");
			}
		}
	}

	//-----------------------------------------------------------------------------------
	//---- cu_becas ---------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__cu_becas(sap_ei_cuadro $cuadro)
	{
		$persona = $this->get_datos('personas','personas')->get();
		$cuadro->set_datos(toba::consulta_php('co_becas')->get_becas_otorgadas_becario($persona['nro_documento']));

	}


	//-----------------------------------------------------------------------------------
	//---- Datos ------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($relacion='personas',$tabla=NULL)
	{
		return ($tabla) ? $this->controlador()->dep($relacion)->tabla($tabla) : $this->controlador()->dep($relacion);
	}




}
?>