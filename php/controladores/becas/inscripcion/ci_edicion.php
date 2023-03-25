<?php
class ci_edicion extends sap_ci
{
	protected $s__estado_inicial;
	protected $s__insc_actual;
	protected $s__convocatoria;

	//protected $s__detalles_inscripcion;
	function conf()
	{
		$this->agregar_notificacion('<b>Informaci�n importante</b>: una vez que cierre su postulaci�n, su director/a deber� acceder al sistema (con su respectivo usuario) para avalar la postulaci�n. De la misma manera lo har�n: <ul><li>El director del proyecto dentro del cu�l se enmarca su proyecto de beca</li><li>El Secretario de Investigaci�n de la unidad acad�mica correspondiente</li><li>El Decano/Director de Instituto correspondiente</li></ul> <br> 
			<div style="color:#F00; text-align:center; font-weight:bold;">No debe imprimir, firmar ni presentar ninguna documentaci�n en papel.</div><div class="centrado">El proceso se realiza completamente en linea.</div>','warning');
		$es_admin = $this->soy_admin();

		$inscripcion = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		if ($inscripcion) {
			//obtengo los datos de la inscripcion
			$this->s__insc_actual = $inscripcion;
			toba::memoria()->set_dato('inscripcion_actual', $inscripcion);
			$this->get_datos('alumno')->cargar(['nro_documento' => $inscripcion['nro_documento']]);

			//si la inscripci�n no est� abierta...
			if($this->s__insc_actual['estado'] != 'A'){
				$this->bloquear_formularios();
				$this->controlador()->pantalla()->agregar_evento('ver_comprobante');
			}

			//Si el tipo de beca de la solicitud actual, se encuentra inactivo, el solicitante ya no podr� realizar modificaciones a la misma
			$estado = toba::consulta_php('co_tablas_basicas')->get_campo('be_tipos_beca','estado',array('id_tipo_beca'=>$this->s__insc_actual['id_tipo_beca']));
			//Estado == INACTIVO??
			if($estado == 'I'){
				$this->bloquear_formularios();
			}

			// TODO: Sacar esto... es para "automatizar" el cambio de nombre de los cuils
			$ruta = '/mnt/datos/cyt/docum_personal/'.$inscripcion['nro_documento'];
			if (file_exists($ruta.'/CUIL.pdf')) {
				rename($ruta.'/CUIL.pdf', $ruta.'/cuil.pdf');
			}
		}else{
			unset($this->s__insc_actual);
			toba::memoria()->eliminar_dato('inscripcion_actual', $inscripcion);
			$this->controlador()->pantalla()->eliminar_evento('cerrar_inscripcion');
			$this->controlador()->pantalla()->eliminar_evento('eliminar');
		}
		
		//se carga solamente si se est� editando una inscripci�n existente
		unset($this->s__convocatoria);

		//si se est� modificando una inscripci�n, es necesario validar algunas cosas...
		if(isset($this->s__insc_actual)){
			//y los datos de la convocatoria
			$this->s__convocatoria = toba::consulta_php('co_convocatoria_beca')->get_convocatorias(['id_convocatoria' => $this->s__insc_actual['id_convocatoria']]);
			
			// si ya pas� la fecha de fin de la convocatoria, no se puede editar la inscripcion (salvo que llame pekermannnn o seas administrador)
			if ($this->s__convocatoria['fecha_hasta'] < date('Y-m-d') && !$es_admin) {
				//bloqueo el formulario para evitar que se modifiquen  los datos
				$this->dep('form_inscripcion')->agregar_notificacion('No se pueden modificar los datos de la inscripci�n debido a que finaliz� la convocatoria.','warning');
				$this->bloquear_formularios();

				//elimino todas las pantallas que no sean el formulario de inscripci�n
				$this->pantalla()->eliminar_tab('pant_alumno');

				//elimino los eventos que me permiten alterar los datos de la inscripcion
				$this->controlador()->pantalla()->eliminar_evento('eliminar');
				if( ! $es_admin){
					$this->controlador()->pantalla()->eliminar_evento('guardar');
				}else{
					$this->controlador()->pantalla()->agregar_notificacion('El bot�n guardar se est� mostrando solamente porque usted es administrador','warning');
				}
			}
		}
		//si se est?cargando una nueva inscripci?, se valida si es un usuario normal o un admin
		if( ! $es_admin){
			//si es un usuario normal, solo puede cargar una solicitud para s?mismo
			$this->dep('form_inscripcion')->ef('nro_documento')->set_estado(toba::usuario()->get_id());
			$this->dep('form_inscripcion')->set_solo_lectura(array('nro_documento'));

		}
	}

	private function bloquear_formularios()
	{
		if( $this->soy_admin()){
			$this->controlador()->pantalla()->agregar_notificacion('El bot�n guardar se est� mostrando solamente porque usted es administrador','warning');
			return;
		}
		//obtengo todos los formularios que dependen del CI
		$deps = $this->get_dependencias_clase('form');
		foreach($deps as $dep){
			//y los marco como solo lectura (el usuario no puede modificar nada)
			$this->dep($dep)->set_solo_lectura();
			//y si es un ML, desactivo el agregado de filas
			if(method_exists($this->dep($dep), 'desactivar_agregado_filas')){
				$this->dep($dep)->desactivar_agregado_filas();
			}
		}
		//adem?, elimino todos los eventos que puedan modificar la solicitud
		$this->controlador()->pantalla()->eliminar_evento('eliminar');
		$this->controlador()->pantalla()->eliminar_evento('guardar');
		$this->controlador()->pantalla()->eliminar_evento('cerrar_inscripcion');

	}

	//-----------------------------------------------------------------------------------
	//---- form_inscripcion -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_inscripcion(sap_ei_formulario $form)
	{
		//Asigno el template al formulario
		$template = __DIR__ . "/templates/template_form_inscripcion.php";
		$template = $this->armar_template($template,array());
		$form->set_template($template);

		/* SE EVAL�A SI HAY QUE CARGAR SOLO LA OPCION DE CONVOCATORIA ACTUAL O TODAS LAS ANTERIORES */
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$opciones['nopar'] = '-- Seleccione --';
		if($insc){
			$opciones[$insc['id_convocatoria']] = toba::consulta_php('co_convocatoria_beca')->get_campo('convocatoria',$insc['id_convocatoria']);
		}else{
			$convs = toba::consulta_php('co_convocatoria_beca')->get_convocatorias(array(),TRUE);
			foreach ($convs as $conv) {
				$opciones[$conv['id_convocatoria']] = $conv['convocatoria']; 
			}
		}
		/* ================================================================================ */

		$form->ef('id_convocatoria')->set_opciones($opciones);

		if(isset($this->s__insc_actual)){
			
			// Extraigo la informaci�n interna para setear el/los campos correspondientes
			if (isset($this->s__insc_actual['informacion_interna']) && $this->s__insc_actual['informacion_interna']) {
				$informacion_interna = json_decode($this->s__insc_actual['informacion_interna'], true);
				if (json_last_error() == 0) {
					$this->s__insc_actual['subtipo_beca'] = $informacion_interna['subtipo_beca'];
				}
			}

			//se bloquean las opciones de convocatorias para que el usuario no pueda modicarlos
			$form->set_solo_lectura(array('id_tipo_beca','id_convocatoria','subtipo_beca'));
			

			//asigno los datos al formulario
			$form->set_datos($this->s__insc_actual);

			$efs_involucrados = array('archivo_insc_posgrado','titulo_carrera_posgrado','nombre_inst_posgrado','carrera_posgrado','fecha_insc_posgrado');
			//verifico si el tipo de beca requiere o no una inscripcion a posgrado.
			$requiere = toba::consulta_php('co_tablas_basicas')->tipo_beca_requiere_posgrado($this->s__insc_actual['id_tipo_beca']);
			//en caso de no requerir inscripci? a Posgrado, desactivo todos los efs relacionados
			if(!$requiere){
				
				foreach ($efs_involucrados as $ef) {
					if($form->existe_ef($ef)){
						$form->desactivar_efs($ef);
					}
				}
			}else{
				$form->set_efs_obligatorios($efs_involucrados);
			}

		}
		
	}

	function evt__form_inscripcion__modificacion($datos)
	{
		//TODO: parametrizar este valor
		$becas_tienen_subtipo = [11];
		if (!in_array($datos['id_tipo_beca'], $becas_tienen_subtipo)) {
			unset($datos['subtipo_beca']);
		}

		$estado_previo       = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$informacion_interna = $estado_previo ? json_decode($estado_previo['informacion_interna'], true) : [];

		/* ================== UPLOAD ARCHIVOS =================== */
		$base = toba::consulta_php('helper_archivos')->get_dir_doc_por_convocatoria_beca();
		$ruta = $base . $datos['id_convocatoria'] . '/' . $datos['id_tipo_beca'] . '/' .$datos['nro_documento'].'/';
		$efs_archivos = [
			['ef' => 'archivo_analitico',     'descripcion' => 'Certificado Analitico',                        'nombre' => 'Cert. Analitico.pdf'],
			['ef' => 'archivo_insc_posgrado', 'descripcion' => 'Const. Inscripcion a Posgrado(o compromiso) ', 'nombre' => 'Insc. o Compromiso Posgrado.pdf'],
		];
		
		toba::consulta_php('helper_archivos')->procesar_campos($efs_archivos,$datos,$ruta);
		/* ========================================================================= */

		$datos['estado']     = !empty($this->s__insc_actual['estado'])     ? $this->s__insc_actual['estado']     : 'A';
		$datos['fecha_hora'] = !empty($this->s__insc_actual['fecha_hora']) ? $this->s__insc_actual['fecha_hora'] : date('Y-m-d H:i:s');
		$datos['es_titular'] = 'S';
		
		// Solo se calcula el puntaje cuando la inscripci�n est� abierta
		if ( !isset($estado_previo['estado']) || $estado_previo['estado'] != 'C' ) {
			$datos['puntaje'] = $this->calcular_puntaje_academico($datos);
		}

		if (!empty($datos['subtipo_beca'])) {
			$informacion_interna          = array_merge($informacion_interna, ['subtipo_beca' => $datos['subtipo_beca']]);
			$datos['informacion_interna'] = json_encode($informacion_interna);
		}
		
		$this->get_datos('inscripcion','inscripcion_conv_beca')->set($datos);

		//esta funcion carga los datos del alumno (si es posible encontrarlo en wl WS o en la base local). En caso contrario env? al usuario a la pantalla de carga de datos de alumno
		if( ! $this->existe_persona($datos['nro_documento']) ){
			$this->get_datos('alumno')->resetear();
			$this->get_datos('alumno','persona')->set(array(
				'nro_documento' => $datos['nro_documento']
			));
			$this->set_pantalla('pant_alumno');

			throw new toba_error('El Nro. de Documento del alumno ingresado no se corresponde con ning�n alumno registrado en el sistema. Por favor, complete los datos personales solicitados a continuaci�n.');
		}
	}

	private function existe_persona($nro_documento)
	{
		return toba::consulta_php('co_personas')->existe_persona($nro_documento);
	}


	/**
		* Esta funcion genera los registros que tienen que ver con la inscriopcion, pero que pertenecen a otras tablas, 
		* como por ejemplo, los registros en la tabla 'requisitos_insc' que registra cuales de los requisitos de esa
		* convocatoria fueron entregados por el alumno
	**/
	function generar_registros_relacionados()
	{
		//si ya tiene los requisitos generados, no se generan nuevamente
		if($this->get_datos('inscripcion','requisitos_insc')->get_filas()){
			return;
		}

		if(isset($this->s__insc_actual)){
			$insc = $this->s__insc_actual;
		}else{
			//si no se est?modificando una solicitud existente, utilizo los datos recien cargados al datos_tabla
			$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		}

		//verifico si ya se crearon los registros para el cumplimiento de requisitos
		$requisitos_inscripcion = toba::consulta_php('co_inscripcion_conv_beca')->get_requisitos_insc(
			$insc['id_convocatoria'],
			$insc['id_tipo_beca'],
			$insc['nro_documento']
		);
		
		//la insercion de los requisitos iniciales se realiza solo una vez
		if($requisitos_inscripcion){
			return;
		}
		
		$requisitos = toba::consulta_php('co_inscripcion_conv_beca')->get_requisitos_iniciales($insc['id_convocatoria']);
		
		foreach($requisitos as $requisito){
			$this->get_datos('inscripcion','requisitos_insc')->nueva_fila($requisito);
		}
	}
	
	//-----------------------------------------------------------------------------------
	//---- form_alumno ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_alumno(sap_ei_formulario $form)
	{	
		//desactivo los efs innecesarios para un alumno
		$efs_a_desactivar = ['id_disciplina','archivo_cvar','id_nivel_academico', 'id_tipo_doc', 'telefono', 'archivo_titulo_grado'];
		foreach ($efs_a_desactivar as $ef) {
			if($form->existe_ef($ef)){
				$form->desactivar_efs([$ef]);		
			}
		}
		
		//Si no se est� editando un alumno, esta variable se pasa al template
		$alumno = [];

		//si existe una inscripci�n actual
		if($this->s__insc_actual){
			$alumno = $this->get_datos('alumno','persona')->get();
			
			//El campo archivo_dni no se guarda en la BD, solo se valida con la existencia del archivo
			$ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
			
			//Verifico existencia del DNI
			$archivo = $ruta_base."/docum_personal/".$alumno['nro_documento']."/dni.pdf";
			if(file_exists($archivo)){
				$alumno['archivo_dni'] = 'dni.pdf';
			}else{
				unset($alumno['archivo_dni']);
			}

			//Verifico existencia del CUIL
			if (toba::consulta_php('helper_archivos')->existe_documento_personal($alumno['nro_documento'], 'cuil')) {
				$alumno['archivo_cuil'] = 'cuil.pdf';
			} else{
				unset($alumno['archivo_cuil']);
			}

			// Esta l�nea hay que sacar (ya no se usa)
			unset($alumno['archivo_titulo_grado']);

			//Verifico existencia del CVAR
			if (toba::consulta_php('helper_archivos')->existe_documento_personal($alumno['nro_documento'], 'cvar')) {
				$alumno['archivo_cuil'] = 'cvar.pdf';
			} else{
				unset($alumno['cvar']);
			}

			$form->set_datos($alumno);
			$form->set_solo_lectura(array('nro_documento'));
		}

		$template = $this->armar_template(__DIR__ . '/templates/template_form_persona.php',$alumno);
		$form->set_template($template);
	}

	function evt__form_alumno__modificacion($datos)
	{
		if(isset($datos['cuil'])){
			$datos['cuil'] = str_replace("-","",$datos['cuil']);
		}
		$efs_archivos = array(array('ef'          => 'archivo_titulo_grado',
							 	    'descripcion' => 'Titulo de Grado',
							 	    'nombre'      => 'Titulo Grado.pdf') ,
							  array('ef'          => 'archivo_cuil',
							  	    'descripcion' => 'Constancia de CUIL',
							  	    'nombre'      => 'cuil.pdf'),
							  array('ef'          => 'archivo_dni',
							 	    'descripcion' => 'Copia de DNI',
							 	    'nombre'      => 'dni.pdf')
							);
							 
		$ruta = 'docum_personal/'.$datos['nro_documento'].'/';
		toba::consulta_php('helper_archivos')->procesar_campos($efs_archivos,$datos,$ruta);
		$this->sincronizar_datos_persona($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_plan_trabajo ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_plan_trabajo(sap_ei_formulario $form)
	{
		if($this->get_datos('inscripcion','plan_trabajo')->get()){
			$form->set_datos($this->get_datos('inscripcion','plan_trabajo')->get());
		}
	}

	function evt__form_plan_trabajo__modificacion($datos)
	{
		if($datos['doc_probatoria']){
			//detalles de la inscripcion
			$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
			//conformaci? de la ruta donde se almacenar?el plan de trabajo
			$ruta = 'becas/doc_por_convocatoria/'.$insc['id_convocatoria'].'/'.$insc['id_tipo_beca'].'/'.$insc['nro_documento'].'/';
			//campos que contienen archivos
			$efs_archivos = array(array('ef'          => 'doc_probatoria',
										'descripcion' => 'Plan de Trabajo',
										'nombre'      => "Plan de Trabajo.pdf"
										));
			toba::consulta_php('helper_archivos')->procesar_campos($efs_archivos,$datos,$ruta);
		}else{
			unset($datos['doc_probatoria']);
		}
		$this->get_datos('inscripcion','plan_trabajo')->set($datos);
	}

	function conf__pant_plan_trabajo(toba_ei_pantalla $pantalla)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		
		if($insc){
			$template = "<table width='100%'>
							<tr>
								<td><h1 class='centrado sombreado'>".quote($insc['titulo_plan_beca'])."</h1></td>
							</tr>
							<tr>
								<td>[dep id=form_plan_trabajo]</td>
							</tr>
						</table>";
			$pantalla->set_template($template);	
		}
		
	}


	//-----------------------------------------------------------------------------------
	//---- form_activ_docentes ----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_activ_docentes(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_activ_docentes')->get_filas()){
			$datos = $this->get_datos('alumno','antec_activ_docentes')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
		
		//se arma un array para cargar el combo "anio_egreso"
		$anios['nopar'] = "Vigente";
		for($i=date("Y");$i>(date("Y")-50);$i--){
			$anios[$i] = $i;
		}
		//se llena el combo "anio_egreso"
		$form_ml->ef('anio_egreso')->set_opciones($anios);
		//solo se quita la opcion "Vigente", y se llena el combo "anio_ingreso"
		unset($anios['nopar']);
		$form_ml->ef('anio_ingreso')->set_opciones($anios);
	}


	function evt__form_activ_docentes__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/activ_docente/";
		
		$campos = array(
						array('nombre' => 'anio_ingreso'),
						array('nombre' => 'anio_egreso', 'defecto' => 'Actualidad'),
						array('nombre' => 'institucion'),
						array('nombre' => 'cargo')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		
		$this->get_datos('alumno','antec_activ_docentes')->procesar_filas($datos);

		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas, como antec_activ_docentes
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_estudios_afines ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_estudios_afines(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		$datos = $this->get_datos('alumno','antec_estudios_afines')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
		
		//se arma un array para cargar los combos de a?is
		for($i=date("Y");$i>(date("Y")-50);$i--){
			$anios[$i] = $i;
		}
		$form_ml->ef('anio_desde')->set_opciones($anios);
		$form_ml->ef('anio_hasta')->set_opciones($anios);
	}

	function evt__form_estudios_afines__modificacion($datos)
	{
		
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/estudios_afines/";
		$campos = array(
						array('nombre' => 'anio_desde'),
						array('nombre' => 'anio_hasta', 'defecto' => 'Actualidad'),
						array('nombre' => 'institucion'),
						array('nombre' => 'titulo')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_estudios_afines')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}


	//-----------------------------------------------------------------------------------
	//---- form_becas_obtenidas ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_becas_obtenidas(form_ml_becas_obtenidas $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_becas_obtenidas')->get_filas()){
			$datos = $this->get_datos('alumno','antec_becas_obtenidas')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_becas_obtenidas__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/becas_obtenidas/";
		$campos = array(
						array('nombre' => 'fecha_desde'),
						array('nombre' => 'fecha_hasta'),
						array('nombre' => 'institucion'),
						array('nombre' => 'tipo_beca')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_becas_obtenidas')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_trabajos_publicados -----------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_trabajos_publicados(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_trabajos_publicados')->get_filas()){
			$datos = $this->get_datos('alumno','antec_trabajos_publicados')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_trabajos_publicados__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/trabajos_publicados/";
		$campos = array(
						array('nombre' => 'fecha'),
						array('nombre' => 'autores')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_trabajos_publicados')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_present_reuniones -------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_present_reuniones(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_present_reuniones')->get_filas()){
			$datos = $this->get_datos('alumno','antec_present_reuniones')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_present_reuniones__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/presentacion_reuniones/";
		$campos = array(
						array('nombre' => 'fecha'),
						array('nombre' => 'autores')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_present_reuniones')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_conoc_idiomas -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_conoc_idiomas(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_conoc_idiomas')->get_filas()){
			$datos = $this->get_datos('alumno','antec_conoc_idiomas')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_conoc_idiomas__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/conocimiento_idiomas/";
		$campos = array(
						array('nombre' => 'idioma')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_conoc_idiomas')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();

	}

	//-----------------------------------------------------------------------------------
	//---- form_otras_actividades -------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_otras_actividades(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_otras_actividades')->get_filas()){
			$datos = $this->get_datos('alumno','antec_otras_actividades')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_otras_actividades__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/otras_actividades/";
		$campos = array(
						array('nombre' => 'institucion'),
						array('nombre' => 'actividad')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_otras_actividades')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_part_dict_cursos --------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_part_dict_cursos(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_particip_dict_cursos')->get_filas()){
			$datos = $this->get_datos('alumno','antec_particip_dict_cursos')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_part_dict_cursos__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/part_dict_cursos/";
		$campos = array(
						array('nombre' => 'fecha'),
						array('nombre' => 'institucion')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_particip_dict_cursos')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_cursos_perfec_aprob -----------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cursos_perfec_aprob(sap_ei_formulario_ml $form_ml)
	{
		$this->get_datos('alumno')->cargar(array('nro_documento'=>$this->s__insc_actual['nro_documento']));
		if($this->get_datos('alumno','antec_cursos_perfec_aprob')->get_filas()){
			$datos = $this->get_datos('alumno','antec_cursos_perfec_aprob')->get_filas();
			$form_ml->set_datos($datos);
			$this->s__estado_inicial = $datos;
		}
	}

	function evt__form_cursos_perfec_aprob__modificacion($datos)
	{
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();
		$ruta = "doc_probatoria/".$insc['nro_documento']."/cursos_perfec_aprob/";
		$campos = array(
						array('nombre' => 'fecha'),
						array('nombre' => 'institucion')
						);
		
		toba::consulta_php('helper_archivos')->procesar_ml_con_archivos($this->s__estado_inicial,$datos,$ruta,$campos,'doc_probatoria');
		$this->get_datos('alumno','antec_cursos_perfec_aprob')->procesar_filas($datos);
		//se sincroniza porque el datos tabla que referencia a "sap_personas" se utiliza tanto para alumnos como para director, codirector y subdirector. Esto provoca que el datos_relacion "Alumno" se resetee entre cambios de pesta? y provocando la perdida de las tablas hijas
		$this->get_datos('alumno')->sincronizar();
	}


	/**
		* Retorna un datos_relaci? (si no se especifica ninguna tabla en particular), sino, devuelve el datos tabla solicitado
		* @param  string $tabla Nombre de la tabla que se desea obtener (null para obtener el datos_relacion)
		* @return datos_tabla o datos_relacion 
		*/
	function get_datos($relacion=null,$tabla=NULL)
	{
		return $this->controlador()->get_datos($relacion,$tabla);
	}

	/**
		* Retorna el nombre y apellido de un docente
		* @param  array              $datos     Array asociativo que contiene el tipo_doc y el nro_doc
		* @param  toba_ajax_respuesta $respuesta Respuesta que se env? al cliente
		*/
	function ajax__get_persona($datos, toba_ajax_respuesta $respuesta)
	{
		if( ! toba::consulta_php('co_personas')->existe_persona($datos['nro_documento'])){
			$respuesta->set(array('persona'=>NULL,'error'=>TRUE,'campo'=>$datos['campo']));
		}
		$ayn = toba::consulta_php('co_personas')->get_ayn($datos['nro_documento']);
		if( ! $ayn){
			$respuesta->set(array('persona'=>NULL,'error'=>TRUE,'campo'=>$datos['campo']));
		}else{
			$respuesta->set(array('persona'=>$ayn,'error'=>FALSE,'campo'=>$datos['campo']));
		}
		
	}

	function ajax__get_prom_hist_egresados($datos, toba_ajax_respuesta $respuesta)
	{
		$prom = toba::consulta_php('co_tablas_basicas')->get_prom_hist_egresados($datos['id_carrera']);
		if($prom){
			$respuesta->set(array('prom_hist_egresados'=>$prom,'error'=>FALSE));
		}else{
			$respuesta->set(array('error'=>TRUE));
		}
	}

	function ajax__get_disciplinas_incluidas($id_area_conocimiento, toba_ajax_respuesta $respuesta)
	{
		$respuesta->set(toba::consulta_php('co_becas')->get_disciplinas_incluidas($id_area_conocimiento));
	}

	function ajax__validar_edad($params, toba_ajax_respuesta $respuesta)
	{
		//$mensaje = ($this->edad_permitida_para_beca($params['id_tipo_doc'],$params['nro_documento'],$params['id_tipo_beca']))? TRUE : FALSE;
		$permitida = $this->edad_permitida_para_beca($params['nro_documento'],$params['id_tipo_beca']);
		if( ! $permitida){
			$respuesta->set(array('error'=>1,'mensaje'=>'La persona indicada como postulante supera la edad l�mite para el tipo de beca al que intenta inscribirse. Por ello, su postulaci�n podr�a resultar no admitida.' ));
		}else{
			$respuesta->set(array('error'=>0,'mensaje'=>'La edad del postulante es correcta'));
		}
		
	}

	function ajax__validar_minimo_materias_exigidas($params, toba_ajax_respuesta $respuesta)
	{
		$exigido = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('beca_pregrado_porcentaje_min_aprobacion');
		if($exigido){
			if( ($params['materias_aprobadas'] / $params['materias_plan'] * 100) < floatval($exigido) ){
				$respuesta->set(array('error'=>1,'mensaje'=>"Usted no cumple con el $exigido% de materias aprobadas exigidas para este tipo de becas. Por ello, su postulaci�n podr�a resultar no admitida"));
			}else{
				$respuesta->set(array('error'=>0,'mensaje'=>'El postulante tiene las materias minimas exigidas'));
			}
		}
	}

	function ajax__requiere_inscripcion_posgrado($params, toba_ajax_respuesta $respuesta)
	{
		$requiere = toba::consulta_php('co_tablas_basicas')->tipo_beca_requiere_posgrado($params);
		$respuesta->set($requiere);
		
	}

	function edad_permitida_para_beca($nro_documento, $id_tipo_beca)
	{
		$edad_limite  = toba::consulta_php('co_tablas_basicas')->get_campo('be_tipos_beca','edad_limite',array('id_tipo_beca' => $id_tipo_beca));
		//se asegura que exista la persona en la BD local, sino, lo busca en WS
		if( ! toba::consulta_php('co_personas')->existe_persona($nro_documento)){
			return NULL;
		}

		$edad_persona =  $this->get_edad($nro_documento,date('Y-12-31'));
		if($edad_persona){
			return $edad_persona <= $edad_limite;
		}else{
			return NULL;
		}
	}

	function get_edad($nro_documento, $fecha)
	{
		$persona = array('nro_documento'=>$nro_documento);
		return toba::consulta_php('co_personas')->get_edad($persona,$fecha);
	}



	function calcular_puntaje_academico($inscripcion)
	{
		if( ! $inscripcion['id_tipo_beca']){
			return false;
		}
		//Si es un tipo de beca que no suma puntaje por antecedentes academicos, suma 0.
		if( ! toba::consulta_php('co_tablas_basicas')->tipo_beca_suma_puntaje_academico($inscripcion['id_tipo_beca'])){
			return 0;
		}
		$factor = toba::consulta_php('co_tablas_basicas')->get_campo('be_tipos_beca','factor',array('id_tipo_beca' => $inscripcion['id_tipo_beca']));
		if(!$factor){
			return false;
		}
		$puntaje = ($factor * $inscripcion['prom_hist']) - 
					  ($inscripcion['prom_hist_egresados'] / $inscripcion['prom_hist']) +  
				      ($inscripcion['prom_hist_egresados'] * $inscripcion['prom_hist'] / 100);
		return round ($puntaje,3);

	}

	function generar_nro_carpeta(){
		$insc = $this->get_datos('inscripcion','inscripcion_conv_beca')->get();

		//si ya tiene numero de carpeta asignado no se hace nada
		if(isset($insc['nro_carpeta'])){
			return;
		}

		$nro = toba::consulta_php('co_inscripcion_conv_beca')->get_ultimo_nro_carpeta($insc['id_convocatoria'],$insc['id_tipo_beca']);
		$this->get_datos('inscripcion','inscripcion_conv_beca')->set(array('nro_carpeta' => $nro));
	}

	function sincronizar_datos_persona($datos)
	{
		//reseteo el datos table, sincronizo, y vuelvo al estado original
		$this->get_datos('alumno','persona')->resetear();
		$this->get_datos('alumno','persona')->cargar(array('nro_documento'=>$datos['nro_documento']));

		$this->get_datos('alumno','persona')->set($datos);
		$this->get_datos('alumno','persona')->sincronizar();


		$this->get_datos('alumno','persona')->resetear();
		$this->get_datos('alumno','persona')->cargar(array('nro_documento'=>$datos['nro_documento']));
	}

	//se usa para llenar los tres formularios de categoria conicet
	private function get_categoria_conicet($nro_documento)
	{
		return toba::consulta_php('co_cat_conicet_persona')->get_categoria_persona($nro_documento);
	}

	//se usa para setear los tres formularios de categorias conicet
	private function set_categoria_conicet($datos){
		if(isset($datos['id_cat_conicet']) && isset($datos['lugar_trabajo'])){
			$cat = toba::consulta_php('co_cat_conicet_persona')->get_categoria_persona($datos['nro_documento']);
			if(count($cat)){
				$this->get_datos(NULL,'cat_conicet_persona')->cargar(array('nro_documento'=>$datos['nro_documento']));
			}
			$this->get_datos(NULL,'cat_conicet_persona')->set($datos);
			$this->get_datos(NULL,'cat_conicet_persona')->sincronizar();
			$this->get_datos(NULL,'cat_conicet_persona')->resetear();
		}

	}
		
}
?>