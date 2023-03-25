<?php
class ci_metas_alcanzadas extends sap_ci
{
	protected $informe_actual;
	protected $seleccionado;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		$this->informe_actual = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
		if($this->informe_actual['estado'] == 'C'){
			$this->bloquear_edicion();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cancelar()
	{
	}

	function evt__agregar()
	{
	}

	function evt__guardar()
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_metas -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_avances(sap_ei_formulario $form)
	{
		$datos = $this->controlador()->get_datos('proyectos_pdts_informe')->get($this->informe_actual['id_proyecto'],TRUE);
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_avances__modificacion($datos)
	{
		$this->controlador()->get_datos('proyectos_pdts_informe')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_metas ---------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_metas(sap_ei_formulario $form)
	{
		
		$objetivos = toba::consulta_php('co_proyectos')->get_obj_especificos($this->informe_actual['id_proyecto'],TRUE);
		$objetivos = array_column($objetivos, 'obj_especifico', 'id_obj_especifico');
		$objetivos['nopar'] = '-- Seleccione --';
		$form->ef('id_obj_especifico')->set_opciones($objetivos);
		
		//Convierto en mayúsculas las primeras letras de cada mes
		$meses = array_map(function($mes){return ucfirst($mes);}, $this->get_meses());
		$form->ef('mes')->set_opciones($meses);
		
		//Obtengo los anios posibles
		$proyecto = $this->controlador()->get_proyecto();
		$desde = (new Datetime($proyecto['fecha_desde']))->format('Y');
		$hasta = (new Datetime($proyecto['fecha_hasta']))->format('Y');
		
		for ($anio = $desde; $anio<=$hasta; $anio++) { 
			$anios[$anio] = $anio; 
		}
		
		$form->ef('anio')->set_opciones($anios);

		$datos = $this->dep('sap_pdts_inf_meta_alc')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_metas__modificacion($datos)
	{
		$this->informe_actual = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
		$datos['id_informe'] = $this->informe_actual['id_informe'];
		unset($datos['id_obj_especifico']);
		try {
			$this->dep('sap_pdts_inf_meta_alc')->resetear();
			$this->dep('sap_pdts_inf_meta_alc')->set($datos);
			$this->dep('sap_pdts_inf_meta_alc')->sincronizar();
			$this->dep('sap_pdts_inf_meta_alc')->resetear();
			toba::notificacion()->info('Agregado con éxito!');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: '.$e->get_mensaje_motor());
		}
	}


	//-----------------------------------------------------------------------------------
	//---- cu_metas_alcanzadas ----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_metas_alcanzadas(sap_ei_cuadro $cuadro)
	{
		$datos = toba::consulta_php('co_proyectos_informes')->get_metas_alcanzadas($this->informe_actual['id_informe']);
		foreach($datos as &$meta){
			//reemplazo los números de meses por sus nombres literales
			$meta['mes'] = ucfirst($this->get_mes($meta['mes']));
		}
		$cuadro->set_datos($datos);
	}

	function evt__cu_metas_alcanzadas__borrar($seleccion)
	{
		$this->informe_actual = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
		$seleccion['id_informe'] = $this->informe_actual['id_informe'];
		
		try {
			$this->dep('sap_pdts_inf_meta_alc')->resetear();
			$this->dep('sap_pdts_inf_meta_alc')->cargar($seleccion);
			$this->dep('sap_pdts_inf_meta_alc')->eliminar();
			$this->dep('sap_pdts_inf_meta_alc')->sincronizar();
			$this->dep('sap_pdts_inf_meta_alc')->resetear();
			toba::notificacion()->info('Borrado con éxito!');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('Ocurrió el siguiente error: '.$e->get_mensaje());
		}
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	

	function bloquear_edicion()
	{
		foreach($this->pantalla()->get_lista_dependencias() as $id_dep){
			if(get_class($this->dep($id_dep)) == 'sap_ei_formulario'){
				$this->dep($id_dep)->set_solo_lectura();
			}
			if(get_class($this->dep($id_dep)) == 'sap_ei_formulario_ml'){
				$this->dep($id_dep)->set_solo_lectura();
				$this->dep($id_dep)->set_borrar_en_linea(FALSE);
				$this->dep($id_dep)->desactivar_agregado_filas();
			}
			if(get_class($this->dep($id_dep)) == 'sap_ei_cuadro'){
				foreach($this->dep($id_dep)->get_eventos_sobre_fila() as $evento){
					if($evento->get_id() == 'borrar'){
						$evento->desactivar();
					}
				}
			}
			if($this->pantalla()->existe_evento('agregar')){
				$this->pantalla()->eliminar_evento('agregar');
			}
			if($this->pantalla()->existe_evento('guardar')){
				$this->pantalla()->eliminar_evento('guardar');
			}
		}
	}



	



}
?>