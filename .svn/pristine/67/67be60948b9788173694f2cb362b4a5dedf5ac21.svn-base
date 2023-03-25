<?php
class ci_informes_pi extends sap_ci
{
	protected $informe_actual;
	protected $proyecto;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf()
	{
		//Info completa del proyecto actual
		$this->proyecto = $this->controlador()->get_proyecto();
		
		//Info del informe que estamos manipulando actualmente
		$this->informe_actual = $this->get_datos('proyectos_pi_informe')->get();
		if(!isset($this->informe_actual['id_informe'])){
			$this->ocultar_pantallas();
		}
		
		//Si no está seteado el tipo de informe, lo calculo
		if( ! (isset($this->informe_actual['id_tipo_informe']) && $this->informe_actual['id_tipo_informe'])){
			$this->informe_actual['id_tipo_informe'] = $this->calcular_tipo_informe($this->proyecto['fecha_desde'],$this->proyecto['fecha_hasta']);

		}
		
		if($this->informe_actual['id_tipo_informe'] === FALSE){
			toba::notificacion()->info('El proyecto no se encuentra en tiempo de presentación de informes');
			$this->controlador()->set_pantalla('pant_seleccion');
		}

		if(empty($this->informe_actual['id_informe'])){
			if($this->pantalla()->existe_evento('cerrar')){
				$this->pantalla()->eliminar_evento('cerrar');
			}
		}

		if($this->informe_actual['estado'] == 'C'){
			$this->bloquear_edicion();
		}else{
			$permite_carga = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('carga_informes_proyecto');
			//Parámetro cargado en tablas básicas que indica si esta "abierta la convocatoria" a informes
			if($permite_carga == 'N'){
				$this->bloquear_edicion();
			}
			
		}
		
		
		
	}

	function conf__pant_general(toba_ei_pantalla $pantalla)
	{
		//Ontengo el año por el cuál se está presentando el informe
		$fecha = (isset($this->informe_actual['fecha_presentacion']) && $this->informe_actual['fecha_presentacion'])
				 ? (new Datetime($this->informe_actual['fecha_presentacion']))->format('Y')-1
				 : date('Y')-1;
		
		$datos_template = array(
			'es_carga_inicial'      => isset($this->informe_actual['id_informe']),
			'informacion_anio'      => $fecha,
			'proyecto_denominacion' => $this->proyecto['descripcion'],
			'proyecto_codigo'       => $this->proyecto['codigo'],
			'proyecto_inicio'       => (new Datetime($this->proyecto['fecha_desde']))->format('d-m-Y'),
			'proyecto_fin'          => (new Datetime($this->proyecto['fecha_hasta']))->format('d-m-Y')
		);
		$template = $this->armar_template(__DIR__ . "/../templates/template_informacion_general_pi.php",$datos_template);
		$this->pantalla('pant_general')->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- EVENTOS DEL CI ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function evt__guardar()
	{
		try{
			$this->get_datos()->sincronizar();
			toba::notificacion()->info('Cambios guardados con éxito!');
		}catch(toba_error_db $e){
			toba::notificacion()->error('Ocurrió el siguiente error: ' . $e->get_mensaje());
		}
	}

	function evt__volver()
	{
		$this->get_datos()->resetear();
		$this->controlador()->set_pantalla('pant_seleccion');
	}

	function evt__cerrar()
	{
		$datos = $this->get_datos('proyectos_pi_informe')->get();
		if( ! (isset($datos['de_acuerdo']) && $datos['de_acuerdo'] != 0) ){
			toba::notificacion()->error('Para presentar un informe, debe marcar la casilla "Estoy de acuerdo" en la parte superior del formulario');
			return;
		}
		$validacion = $this->validar_obligatorios();
		if($validacion  !== TRUE){
			toba::notificacion()->warning('Faltan completar los siguientes campos obligatorios: '. $validacion);
			return;
		}
		$fecha_presentacion = (isset($datos['fecha_presentacion']) && $datos['fecha_presentacion'])
								? $datos['fecha_presentacion']
								: date('Y-m-d');
		$this->get_datos('proyectos_pi_informe')->set(
			array('fecha_presentacion' => $fecha_presentacion,
				  'estado'             => 'C',
				  'presentado_por'     => toba::usuario()->get_id()
				));
		toba::notificacion()->info('El informe se ha cerrado y presentado.');
		$this->evt__guardar(TRUE);
	}

	//-----------------------------------------------------------------------------------
	//---- form_acuerdo -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__form_acuerdo(sap_ei_formulario $form)
	{
		$form->set_datos($this->informe_actual);
	}

	function evt__form_acuerdo__modificacion($datos)
	{
		$this->get_datos('proyectos_pi_informe')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_pi ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pi(sap_ei_formulario $form)
	{
		$form->set_datos($this->informe_actual);

		if( ! isset($this->informe_actual['tipo_informe'])){
			$form->ef('id_tipo_informe')->set_estado($this->informe_actual['id_tipo_informe']);
		}	

		//Solo el administrador puede decidir la fecha de presentacion
		if( ! $this->soy_admin()){
			$form->desactivar_efs(array('fecha_presentacion'));
			$form->set_solo_lectura(array('id_tipo_informe'));
		}



	}

	function evt__form_pi__modificacion($datos)
	{
		$this->get_datos('proyectos_pi_informe')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_pi ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pi_gestion(sap_ei_formulario $form)
	{
		$form->set_template(file_get_contents(__DIR__ . '/../templates/template_form_pi_gestion.php'));
		$form->set_datos($this->informe_actual);
		if( ! array_key_exists('apoyo_monto_recibido',$this->informe_actual)){
			//Se calculan campos que el sistema podría sugerir (monto recibido en apoyos economicos y tipo de informe)
			$monto = toba::consulta_php('co_apoyos')->get_total_apoyo_a_proyecto($this->proyecto['id']);
			$form->ef('apoyo_monto_recibido')->set_estado($monto);
			$form->ef('apoyo_monto_recibido')->set_descripcion('El monto sugerido se corresponde con nuestros registros de apoyos económicos otorgados al proyecto actual');
		}else{

		}
	}

	function evt__form_pi_gestion__modificacion($datos)
	{
		$this->get_datos('proyectos_pi_informe')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_integ_pi ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_integ_pi(sap_ei_formulario_ml $form_ml)
	{

		if( ! $this->soy_admin()){
			$form_ml->desactivar_agregado_filas();
			$form_ml->set_solo_lectura(array('nro_documento'));
		}
		$datos = $this->get_datos('sap_proyecto_integrante_eval')->get_filas();

		if( ! $datos){
			//Si el DT no está cargado, verifico si existe el ID de informe (y busco en la BD sus registros)
			if(isset($this->informe_actual['id_informe'])){
				$evs = toba::consulta_php('co_proyectos_informes')->get_evaluaciones($this->informe_actual['id_informe'],'pi');
				$this->get_datos('sap_proyecto_integrante_eval')->cargar_con_datos($evs);	
			}else{
				//Sino, los genero por primera vez
				$this->generar_evaluaciones_integrantes();
			}
			$datos = $this->get_datos('sap_proyecto_integrante_eval')->get_filas(); 
		}
		if(count($datos)){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_integ_pi__modificacion($datos)
	{

		$this->get_datos('sap_proyecto_integrante_eval')->procesar_filas($datos);
		foreach($datos as $integrante){
			if($integrante['apex_ei_analisis_fila'] == 'A'){
				$this->get_datos('sap_proyecto_integrante_eval')->set_cursor($integrante['x_dbr_clave']);
				$this->get_datos('sap_pi_inf_integ_eval')->nueva_fila();
			}
		}
	}


	//-----------------------------------------------------------------------------------
	//---- AUXILIARES -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function get_datos($tabla = NULL)
	{
		return $this->controlador()->get_datos_pi($tabla);
	}

	function generar_evaluaciones_integrantes()
	{
		foreach($this->proyecto['integrantes'] as $integrante){
			$anio_desde = (new Datetime($integrante['fecha_desde']))->format('Y');
			$anio_hasta = (new Datetime($integrante['fecha_hasta']))->format('Y');
			//Si el integrante no participó (al menos) hasta el año pasado, se lo omite
			if($anio_hasta < (date('Y')-1) ) continue;
			//Si el integrante empezó a participar este año, se omite
			if($anio_desde > (date('Y')-1) ) continue;
			//Si el integrante es parte de la dirección del proyecto, se omite
			if(in_array($integrante['funcion'],array('Director','Co-Director','Sub-Director'))) continue;
			$nuevo = array('nro_documento'=>$integrante['nro_documento']);
			$id_fila = $this->get_datos('sap_proyecto_integrante_eval')->nueva_fila($nuevo);
			$this->get_datos('sap_proyecto_integrante_eval')->set_cursor($id_fila);
			$this->get_datos('sap_pi_inf_integ_eval')->nueva_fila();
		}
	}

	//Hay pestañas que no se muestran hasta que se guarde por primera vez el informe
	function ocultar_pantallas()
	{
		foreach($this->pantalla()->get_lista_tabs() as $pantalla => $objeto){
			if($pantalla != 'pant_general') $this->pantalla()->eliminar_tab($pantalla);
		}
	}

	function calcular_tipo_informe($fecha_desde,$fecha_hasta)
	{
		$tipos_informe = toba::consulta_php('co_proyectos_informes')->get_tipos_informe();	
		$tipos_informe = array_column($tipos_informe,'id_tipo_informe','nro_presentacion');
		
		$f1 = new Datetime($fecha_desde);
		$f2 = new Datetime($fecha_hasta);
		
		//Ejemplo Inicio: 2019, Final: 2022 (son cuatro años, incluyendo 2019), por eso se suma 1
		$duracion = $f2->format('Y') - $f1->format('Y') + 1;
		$tiempo_actual = date('Y') - $f1->format('Y');
		
		$indice = ($duracion <= 4) ? $tiempo_actual : ($tiempo_actual + 1);
		return array_key_exists($indice, $tipos_informe) ? $tipos_informe[$indice] : FALSE;
	}

	function validar_obligatorios()
	{
		$obligatorios = array(
			array('pantalla'=>'Gestión del Proyecto','dt'=>'proyectos_pi_informe','campo'=>'apoyo_monto_recibido','campo_desc'=>'Apoyo económico recibido')
		);

		$faltantes = array();
		foreach ($obligatorios as $obligatorio) {
			$registro = $this->get_datos($obligatorio['dt'])->get();
			if( ! (isset($registro[$obligatorio['campo']]) &&  $registro[$obligatorio['campo']] !== '')){
				$faltantes[$obligatorio['pantalla']][] = $obligatorio['campo_desc'];
				
			}
		}
		if(count($faltantes)){
			$mensaje = "<ul>";
			foreach($faltantes as $pantalla => $campos){
				$mensaje .= "<li>$pantalla: ".implode(', ',$campos)."</li>";
			}
			return $mensaje."</ul>";
		}else{
			return TRUE;
		}
	}

	function bloquear_edicion()
	{
		if($this->soy_admin()){
			$this->agregar_notificacion('Los formularios están habilitados solamente porque el usuario es administrador.','warning');
			return;
		}
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
			if($this->pantalla()->existe_evento('cerrar')){
				$this->pantalla()->eliminar_evento('cerrar');
			}
			if($this->pantalla()->existe_evento('guardar')){
				$this->pantalla()->eliminar_evento('guardar');
			}
		}
	}

	function get_proyecto(){
		return $this->proyecto;
	}



}
?>