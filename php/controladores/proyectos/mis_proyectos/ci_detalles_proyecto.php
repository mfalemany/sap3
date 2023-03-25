<?php
class ci_detalles_proyecto extends sap_ci
{
	protected $s__duracion;
	protected $s__id_area;
	protected $s__id_area_tematica;
	protected $s__id_proyecto;
	protected $s__auxiliares;
	protected $s__estado;

	function conf()
	{
		if(!$this->get_datos()->esta_cargada() || ! $this->soy_admin() ){
			if($this->pantalla()->existe_evento('eliminar')){
				$this->pantalla()->eliminar_evento('eliminar');	
			}
		}
		/* ======= ESTO ES TEMPORAL HASTA QUE EST� TERMINADO EL CONJUNTO DE VALIDACONES ======= */
		/*if($this->pantalla()->existe_evento('cerrar_presentacion')){
			$this->pantalla()->eliminar_evento('cerrar_presentacion');	
		}*/
		/*======================================================================================*/



		/* SI NO HAY UNA CONVOCATORIA ABIERTA, SE ELIMINA EL BOT�N PARA CREAR UN PROYECTO */
		if(!toba::consulta_php('co_proyectos')->hay_convocatoria_abierta()){
			//elimino los eventos que posibilitan guardar
			if( ! $this->soy_admin()){
				if($this->pantalla()->existe_evento('guardar')){
					$this->pantalla()->eliminar_evento('guardar');
				}
				if($this->pantalla()->existe_evento('cerrar_presentacion')){
					$this->pantalla()->eliminar_evento('cerrar_presentacion');	
				}	
			}
		}

		/*SI NO HAY OBJETIVOS ESPEC�FICOS DECLARADOS, NO SE MUESTRA LA PANTALLA DE CRONOGRAMA*/
		if(!$this->get_datos('proyecto_obj_especifico')->get_filas()){
			$this->pantalla()->eliminar_tab('pant_cronograma');
		}

		//Obtengo los datos cargados
		$datos = $this->get_datos('proyectos')->get();
		//Solo si existen datos
		if($datos){
			/* ======================= Si est� cerrado, se bloquea todo =====================================*/
			if(isset($datos['estado'])){
				$this->s__estado = $datos['estado'];	
				if($datos['estado'] == 'C'){
					$this->bloquear_edicion();
				}else{
					//Si no est� cerrada la solicitud, no se puede imprimir el formulario
					if($this->pantalla()->existe_evento('imprimir_formulario')){
						$this->pantalla()->eliminar_evento('imprimir_formulario');	
					}
				}
			}
			
			/* =======================Cargo los registros auxiliares =========================================*/
			if(isset($datos['id'])){
				//Bloqueo la posibilidad de modificar la Unidad Acad�mica
				$this->dep('form_proyecto')->set_solo_lectura('sap_dependencia_id');

				$this->s__id_proyecto = $datos['id'];
				$funciones = array(
					array('auxiliar' => 'tesistas'   , 'tabla' => 'sap_proyecto_tesista'),
					array('auxiliar' => 'alumnos'    , 'tabla' => 'sap_proyecto_alumno'),
					array('auxiliar' => 'becarios'   , 'tabla' => 'sap_proyecto_becario'),
					array('auxiliar' => 'apoyo'      , 'tabla' => 'sap_proyecto_apoyo'),
					array('auxiliar' => 'inv_externo', 'tabla' => 'sap_proyecto_inv_externo')
				);
				foreach($funciones as $funcion){
					if( ! isset($this->s__auxiliares[$funcion['auxiliar']]) || ! $this->s__auxiliares[$funcion['auxiliar']] ){
						$this->s__auxiliares[$funcion['auxiliar']] = toba::consulta_php('co_proyectos')->get_miembros(array('id_proyecto' => $datos['id']),$funcion['tabla']);	
					}
				}

			}else{
				unset($this->s__id_proyecto);
				if($this->pantalla()->existe_evento('cerrar_presentacion')){
					$this->pantalla()->eliminar_evento('cerrar_presentacion');	
				}
				
			}
			/* ===============================================================================================*/

			if(isset($datos['fecha_desde']) && isset($datos['fecha_hasta'])){
				//Obtengo la duracion del proyecto en base a las fechas desde y hasta
				//$this->s__duracion = date('Y',strtotime($datos['fecha_hasta'])) - date('Y',strtotime($datos['fecha_desde']));  
				$this->s__duracion = (substr($datos['fecha_hasta'], 0,4) - substr($datos['fecha_desde'], 0,4)) +1;
			}
			//Esto se hace en conf__form_proyecto
			/*if(isset($datos['id_subarea'])){
				//obtengo el area del proyecto
				$this->s__id_area = toba::consulta_php('co_proyectos')->get_area(array('id_subarea'=>$datos['id_subarea']));
			}*/
			if(isset($datos['id_subarea_prioritaria'])){
				//obtengo el area "prioritaria o tem�tica" (area de programas) del proyecto
				$this->s__id_area_tematica = toba::consulta_php('co_programas')->get_area_de_subarea($datos['id_subarea_prioritaria']);
			}
			/* ===================================================================================== */
			// Dependiendo del tipo de proyecto que se est� cargando, se muestran/ocultan los 
			// formularios de PI y PDTS respectivamente
			
			if($datos['tipo'] == '0'){
				if($this->pantalla()->existe_dependencia('form_detalles_pdts')){
					$this->pantalla()->eliminar_dep('form_detalles_pdts');
					$this->pantalla()->eliminar_dep('ml_instituciones');
					$this->pantalla()->eliminar_dep('ml_agentes_financieros');
				}
			}
			if($datos['tipo'] == 'D'){
				if($this->pantalla()->existe_dependencia('form_detalles_pi')){
					$this->pantalla()->eliminar_dep('form_detalles_pi');
				}	
			}
			/* ===================================================================================== */
		}else{
			unset($this->s__duracion);
			unset($this->s__id_area);
			unset($this->s__id_area_tematica);
			unset($this->s__id_proyecto);
			unset($this->s__estado);
			if($this->pantalla()->existe_evento('cerrar_presentacion')){
				$this->pantalla()->eliminar_evento('cerrar_presentacion');	
			}
			if($this->pantalla()->existe_evento('imprimir_formulario')){
				$this->pantalla()->eliminar_evento('imprimir_formulario');	
			}
		}
	}

	function bloquear_edicion()
	{
		if( ! $this->soy_admin()){
			$forms = $this->get_dependencias_clase('form');
			foreach($forms as $form){
				
				$this->dep($form)->set_solo_lectura();
				if(strpos(get_class($this->dep($form)) ,'_ml') ){
					$this->dep($form)->desactivar_agregado_filas();
				}
				$this->pantalla()->eliminar_evento('guardar');
				$this->pantalla()->eliminar_evento('cerrar_presentacion');
			}
		}else{
			$this->agregar_notificacion('Los formularios son editables solamente porque el usuario actual es administrador.','warning');
		}
	}

	function validar_usuario_carga($nro_documento)
	{
		$integrantes = $this->get_datos('proyecto_integrante')->get_filas();
		$funciones = toba::consulta_php('co_proyectos')->get_funciones_integrantes();
		$funciones = array_column($funciones, 'identificador_perfil','id_funcion');

		if(!count($integrantes)){
			return FALSE;
		}

		$es_directivo = FALSE;
		foreach($integrantes as $integrante){
			if($integrante['nro_documento'] == $nro_documento){
				if(in_array($funciones[$integrante['id_funcion']], array('D','C','S') ) ){
					$es_directivo = TRUE;
				}	
			}
		}
		return $es_directivo;
	}

	/* =====================================================================================*/
	/* ============================== EVENTOS ==============================================*/
	/* =====================================================================================*/


	function evt__eliminar()
	{
		if($this->get_datos()->esta_cargada()){
			$this->get_datos()->eliminar_todo();
			$this->get_datos()->resetear();
			toba::notificacion()->agregar('Proyecto eliminado con �xito','info');
			$this->controlador()->set_pantalla('pant_seleccion_proyecto');	
		}
		
	}
	function evt__guardar($es_definitivo = FALSE)
	{
		//Por alg�n motivo, toba inyecta el string 'undefined' en el par�metro $es_definitivo
		if($es_definitivo==='undefined'){
			$es_definitivo = FALSE;
		}
		
		try {
			$datos = $this->get_datos('proyectos')->get();

			//Si no es un usuario administrador, debe estar asignado como directivo
			if( ! $this->soy_admin()){
				//El usuario actualmente logueado debe auto-declararse como parte directiva del proyecto
				if(!$this->validar_usuario_carga(toba::usuario()->get_id())){
					throw new toba_error("Para poder guardar el proyecto, el usuario actual debe estar asignado, en la solapa 'Integrantes', como: Director, Co-Director o Sub-Director");
				}	
			}
			
			
			//Si es un PI, se eliminan  los datos posibles de PDTS y viceversa
			if($datos['tipo'] == '0'){

				$this->get_datos('proyectos_pdts')->resetear();
			}else{
				$this->get_datos('proyectos_pi')->resetear();
			}	
							
			//Si no existe un ID, es porque se est� guardando uno nuevo
			if(!isset($datos['id'])){
				//Sincronizo con fuente
				toba::db()->ejecutar("BEGIN; LOCK TABLE sap_proyectos IN EXCLUSIVE MODE;");
				
				//Seteo por �nica vez los campos fijos 
				$this->get_datos('proyectos')->set(array(
					'convocatoria_anio'=>date('Y'),
					'entidad_financiadora'=>'Sec. Gral. de Ciencia y Tecnica - Universidad Nacional del Nordeste'

				));

				$this->get_datos()->sincronizar();
				$id = toba::db()->consultar_fila("SELECT max(id) as id FROM sap_proyectos;");
				toba::db()->ejecutar("COMMIT;");	
			}else{
				$id = $datos['id'];
				$this->get_datos()->sincronizar();
			}
			//se registran todos los cambios en las tablas auxiliares
			$this->registrar_cambios_integrantes($id);
			
			
			if($es_definitivo){
				$resultado_validacion = $this->validar_proyecto();
				if ($resultado_validacion['status'] == false) {
					toba::notificacion()->error('El proyecto no est� en condiciones de ser presentado de forma definitiva. Por favor, verifique los detalles en la pesta�a \'Control de Condiciones\'');
					$this->set_pantalla('pant_control');
					return;
				}
				//SI TODO FUE BIEN, SETEAR TRUE EL CAMPO "ESTADO"
				$this->get_datos('proyectos')->set(array("estado"=>"C"));
				$this->get_datos()->sincronizar();
			}

			//Y si todo sali� bien...
			toba::notificacion()->agregar('Los datos se guardaron con �xito','info');
			//se elimina la variable de sesi�n para que en el siguiente pedido de pagina, se carge desde el DT
			unset($this->s__auxiliares);
			
		} catch (toba_error_db $e) {
			toba::consulta_php('helper_archivos')->log('Error en carga de Proyectos: usuario ' . toba::usuario()->get_id() . ": Ocurri� el siguiente error: SQL-State: " . $e->get_sqlstate() . " - Mensaje: " . $e->get_mensaje() . " - SQL Ejecutado: " . $e->get_sql_ejecutado() . " - Mensaje Motor: " . $e->get_mensaje_motor());
			switch ($e->get_sqlstate()) {
				case 'db_23503':
					$mensaje = "Alguno de los integrantes que est� declarando, no tienen su informaci�n completa en la solapa 'Recursos Humanos'.";
					break;
				case 'db_23505':
					$mensaje = "Se est� intentando guardar informaci�n duplicada. Este error suele suceder cuando se cargan dos integrantes con la misma funci�n, o cuando se carga una tarea (del plan de tareas) en el mismo semestre y a�o, dos veces.";
					break;
				default:
					$mensaje = "Ocurri� un error no identificado. Por favor, comuniquese con la Secretar�a General de Ciencia y T�cnica para obtener ayuda.".$e->get_sqlstate().$e->get_mensaje().$e->get_sql_ejecutado().$e->get_mensaje_motor();
					break;
			}
			
			toba::notificacion()->agregar($mensaje);
		} catch (Exception $e) {
			toba::consulta_php('helper_archivos')->log('Error en carga de Proyectos: usuario ' . toba::usuario()->get_id() . ": Ocurri� el siguiente error: " . $e->getMessage());
			toba::notificacion()->agregar($e->getMessage());
		}

	}

	function evt__cerrar_presentacion()
	{
		$this->evt__guardar(TRUE);
	}

	function evt__cancelar()
	{
		unset($this->s__auxiliares);
		$this->get_datos()->resetear();
		$this->controlador()->set_pantalla('pant_seleccion_proyecto');
	}

	/* =====================================================================================*/
	/* ============================== PANT_FORM_PROYECTO ===================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- form_proyecto ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_proyecto(form_proyecto $form)
	{

		$datos = $this->get_datos('proyectos')->get();
		
		if($datos){
			/* ESTAS DOS CARGAS SE REALIZAN PORQUE SON COMBOS EN CASCADA, Y EL LA TABLA SOLO SE GUARDAN LOS ID DE SUBAREAS. SI SE CARGA TAL CUAL VIENE DE LA BASE, NO SE COMPLETAN LOS COMBOS PADRE (POR NO GUARDARSE), Y POR LO TANTO TAMPOCO SE CARGAN LOS COMBOS HIJOS */

			//Campos: 
			//  - id_area    es "Campo Disciplinar"
			//  - id_subarea es "Especialidad"
			if(isset($datos['id_subarea'])){
				//obtengo el area del proyecto
				$this->s__id_area = toba::consulta_php('co_proyectos')->get_area(array('id_subarea'=>$datos['id_subarea']));
				//Obtengo el area correspondiente del proyecto
				$datos['id_area'] = $this->s__id_area;
			}

			//Campos:
			// - id_area_tematica       es "�rea Tem�tica"
			// - id_subarea_prioritaria es "Sub-Area o Tema"
			if(isset($datos['id_subarea_prioritaria'])){
				//obtengo el area tem�tica del proyecto
				$datos['id_area_tematica'] = toba::consulta_php('co_programas')->get_area_de_subarea($datos['id_subarea_prioritaria']);
			}
			/* ================================ FIN DE LA CARGA DE LOS COMBOS ===========================*/


			
			//Obtengo la duraci�n del proyecto
				
			$datos['duracion'] = (substr($datos['fecha_hasta'], 0,4) - substr($datos['fecha_desde'], 0,4)) +1 ;
			
			//Bloqueo el campo "Tipo"
			$form->set_solo_lectura(array('tipo','duracion'));
			$form->set_datos($datos);
			
			//Completa el campo "Duracion" cuando ya hay datos completos
			$duracion_opcion = ($datos['duracion']>2) ? 4 : 2;
			$form->set_datos(array('duracion'=>$duracion_opcion));

		}else{
			//Si se esst� cargando por primera vez, se asigna autom�ticamente la fecha de inicio
			$desde = (date('Y')+1).'-01-01';
			$form->set_datos(array('fecha_desde'=>$desde));
			$form->desactivar_efs(array('tiene_anexo'));
		}

	}

	function evt__form_proyecto__modificacion($datos)
	{
		$proyecto = $this->get_datos('proyectos')->get();
		$id_proyecto = isset($proyecto['id']) ? $proyecto['id'] : NULL;

		//variable de sesion que sirve como base en varios c�lculos a lo largo de la carga
		$this->s__duracion = $datos['duracion'];
		//variable que almacena temporalmente el id_area (que no se almacena en datos_tabla)
		$this->s__id_area = $datos['id_area'];
		//variable que almacena temporalmente el id_area_tematica
		$this->s__id_area_tematica = $datos['id_area_tematica'];
		//se calcula la fecha_hasta del proyecto en base a la duraci�n seleccionada
		if(!isset($datos['fecha_hasta'])){
			$datos['fecha_hasta'] = $this->obtener_fecha_hasta($datos['fecha_desde'],$datos['duracion']);	
		}
		
		/* El indice 'tiene_anexo' se utiliza para cargar un archivo, pero despues de subir el archivo, el campo es transformado a TRUE o FALSE para ser almacenado en la base de datos. Como el nombre de archivo no var�a (se guarda siempre como 'anexo.pdf' dentro de la carpeta del codigo del proyecto, no es necesario guardar el nombre del archivo, pero si un flag que indique si est� cargado o no */

		//Si se carg� un anexo... 
		if(isset($datos['tiene_anexo']) && $datos['tiene_anexo'] && $id_proyecto){
			$codigo = toba::consulta_php('co_proyectos')->get_valor_campo('codigo',array('id'=>$id_proyecto));
			//Si se pudo obtener el codigo del proyecto, se utiliza como nombre de carpeta donde guardar
			if($codigo){
				//Si se subi� correctamente el anexo
				if(toba::consulta_php('helper_archivos')->subir_archivo($datos['tiene_anexo'],'proyectos/'.$codigo,"anexo.pdf",array('pdf')) ){
					//Marca el anexo como "Subido"
					$datos['tiene_anexo'] = 1;
				}	
			}else{
				throw new toba_error("Ocurri� un error al intentar obtener el c�digo de proyecto. El anexo no puede cargarse.");
			}
			
		}else{
			unset($datos['tiene_anexo']);
		}
		//toba::consulta_php('helper_archivos')->
		$this->get_datos('proyectos')->set($datos);
	}

	/* =====================================================================================*/
	/* ============================== PANT_INTEGRANTES =====================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- ml_integrantes ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_integrantes(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proyecto_integrante')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_integrantes__modificacion($datos)
	{
		/* En este punto, hay que re-generar todos los registros de las tablas auxiliares: 
		* proyecto_tesista, proyecto_becario, proyecto_alumno, proyecto_inv_externo, proyecto_apoyo
		*/
		
		$this->existen_duplicados($datos);
		$this->get_datos('proyecto_integrante')->procesar_filas($datos);

	}

	function evt__ml_integrantes__pedido_registro_nuevo()
	{
		//seteo la fecha de inicio del proyecto como fecha desde para los integrantes
		$datos = $this->get_datos('proyectos')->get();
		if($datos){
			$this->dep('ml_integrantes')->set_registro_nuevo(
				array(
					'fecha_desde'=>$datos['fecha_desde'],
					'fecha_hasta'=>$this->obtener_fecha_hasta($datos['fecha_desde'],$this->s__duracion)
				));	
		}
	}

	function conf_evt__ml_integrantes__editar_info(toba_evento_usuario $evento, $fila)
	{
		if( isset($this->s__estado) && $this->s__estado == 'C' && (!$this->soy_admin()) ){
			$evento->ocultar();
			return;
		}
		$filas = $this->dep('ml_integrantes')->get_datos();
		$indice_filas = array_column($filas,'x_dbr_clave');
		$indice = array_search($fila,$indice_filas);
		
		if($indice !== FALSE){
			if(isset($filas[$indice]['nro_documento']) && $filas[$indice]['nro_documento']){
				$params = $evento->vinculo()->get_parametros();
				$params['nro_documento'] = $filas[$indice]['nro_documento'];
				$params['efs_bloqueados'] = 'nro_documento,cuil,apellido,nombres';
				//$evento->vinculo()->set_parametros($evento->vinculo()->get_parametros());
				//$evento->vinculo()->agregar_parametro('nro_documento',$filas[$indice]['nro_documento']);
				$evento->vinculo()->set_parametros($params);
				$evento->mostrar();
				return;
			}
		}
		$evento->ocultar();
	}

	//-----------------------------------------------------------------------------------
	//---- form_justif_directores -------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__form_justif_directores(sap_ei_formulario $form)
	{	
		//aca vienen las justificaciones de director y codirector
		$datos_proyecto = $this->get_datos('proyectos')->get();
		//aca viene la justificacion de subdirector
		$datos_pi = $this->get_datos('proyectos_pi')->get();
		if(is_array($datos_pi) && is_array($datos_proyecto)){
			$datos = array_merge($datos_proyecto,$datos_pi);	
		}else{
			$datos = $datos_proyecto;
		}
		
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_justif_directores__modificacion($datos)
	{
		$this->get_datos('proyectos')->set($datos);
	}

	/* =====================================================================================*/
	/* ============================== PANT_DETALLES ========================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- form_detalles_pi -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_detalles_pi(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('proyectos_pi')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_detalles_pi__modificacion($datos)
	{
		$this->get_datos('proyectos_pi')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_detalles_pdts -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_detalles_pdts(sap_ei_formulario $form)
	{
		$datos = $this->get_datos('proyectos_pdts')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_detalles_pdts__modificacion($datos)
	{
		$this->get_datos('proyectos_pdts')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_instituciones -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_instituciones(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proy_pdts_institucion')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_instituciones__modificacion($datos)
	{
		$this->get_datos('proy_pdts_institucion')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_agentes_financieros -------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_agentes_financieros(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proyecto_agente_financiero')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__ml_agentes_financieros__modificacion($datos)
	{
		$this->get_datos('proyecto_agente_financiero')->procesar_filas($datos);
	}

	/* =====================================================================================*/
	/* ============================== PANT_RECURSOS_HUMANOS ================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- ml_tesistas ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_tesistas(sap_ei_formulario_ml $form_ml)
	{
		$this->configurar_formulario($form_ml,'P','tesistas');	
	}

	

	function evt__ml_tesistas__modificacion($datos)
	{
		foreach($datos as $tesista){
			$this->s__auxiliares['tesistas'][$tesista['nro_documento']] = array(
				'nro_documento' => $tesista['nro_documento'],
				'carrera'	   => $tesista['carrera'],
				'institucion'   => $tesista['institucion'],
				'anio_inicio'   => $tesista['anio_inicio']
			);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_becarios ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_becarios(sap_ei_formulario_ml $form_ml)
	{
		$this->configurar_formulario($form_ml,'B','becarios');
	}

	function evt__ml_becarios__modificacion($datos)
	{
		foreach($datos as $becario){
			$this->s__auxiliares['becarios'][$becario['nro_documento']] = array(
				'nro_documento' => $becario['nro_documento'],
				'id_tipo_beca'  => $becario['id_tipo_beca'],
				'anio_fin'	  => ($becario['anio_fin']) ? $becario['anio_fin'] : NULL,
				'anio_inicio'   => $becario['anio_inicio']
			);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_alumnos -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_alumnos(sap_ei_formulario_ml $form_ml)
	{
		$this->configurar_formulario($form_ml,'A','alumnos');
	}

	function evt__ml_alumnos__modificacion($datos)
	{
		foreach($datos as $alumno){
			$this->s__auxiliares['alumnos'][$alumno['nro_documento']] = array(
				'nro_documento'  => $alumno['nro_documento'],
				'id_carrera'	 => $alumno['id_carrera'],
				'porc_mat_aprob' => $alumno['porc_mat_aprob']
			);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_apoyo ---------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_apoyo(sap_ei_formulario_ml $form_ml)
	{
		$this->configurar_formulario($form_ml,'T','apoyo');
	}

	function evt__ml_apoyo__modificacion($datos)
	{
		foreach($datos as $apoyo){
			$this->s__auxiliares['apoyo'][$apoyo['nro_documento']] = array(
				'nro_documento'  => $apoyo['nro_documento'],
				'id_tipo_apoyo'	 => $apoyo['id_tipo_apoyo'],
				'id_dependencia'	 => $apoyo['id_dependencia']
			);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_inv_externos --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__ml_inv_externos(sap_ei_formulario_ml $form_ml)
	{
		$this->configurar_formulario($form_ml,'X','inv_externo');
	}

	function evt__ml_inv_externos__modificacion($datos)
	{
		foreach($datos as $inv_externo){
			$this->s__auxiliares['inv_externo'][$inv_externo['nro_documento']] = array(
				'nro_documento'  => $inv_externo['nro_documento'],
				'institucion'	 => $inv_externo['institucion'],
				'cargo_docente'	 => $inv_externo['cargo_docente']
			);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_investigadores ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_investigadores(sap_ei_formulario_ml $form_ml)
	{
		$integrantes = $this->get_datos('proyecto_integrante')->get_filas();
		if(count($integrantes)){
			$funcion = toba::consulta_php('co_proyectos')->get_funcion('I');
			$investigadores = array_filter($integrantes,function($integrante) use ($funcion){
				return ($integrante['id_funcion'] == $funcion['id_funcion']);
			});	
		}
		if(!isset($investigadores)){
			return;
		}

		$invs = array();


		foreach($investigadores as $investigador){
			$cargos = toba::consulta_php('co_personas')->get_cargos_persona($investigador['nro_documento'],TRUE);
			$lista = "<ul>";
			foreach ($cargos as $cargo) {
				$lista .= "<li>".$cargo['descripcion']." - ".$cargo['nombre']."</li>";	 
			}
			$lista .= "</ul>"; 
			$invs[] = array('nro_documento' => $investigador['nro_documento'],'cargos' => $lista);
			
		}
		$form_ml->set_datos($invs);
	}

	/* =====================================================================================*/
	/* ============================== PANT_NECES_PRESUP ====================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- form_necesidades_presup ------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_necesidades_presup(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proy_presupuesto')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}
	}

	function evt__form_necesidades_presup__modificacion($datos)
	{
		$this->get_datos('proy_presupuesto')->procesar_filas($datos);
	}

	/* =====================================================================================*/
	/* ============================== PANT_PLAN TAREAS =====================================*/
	/* =====================================================================================*/

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_plan_tareas(toba_ei_pantalla $pantalla)
	{
		if( ! $this->get_datos('proyecto_obj_especifico')->hay_cursor()){
			$this->pantalla()->eliminar_dep('ml_tareas');
			$this->pantalla()->eliminar_dep('ml_cronograma');
		}
		$template =  "<table width='100%'>
						<caption style='font-size:1.2em; font-weight:bold; background-color: #575b98; color:white; padding: 4px 0px;'>Cada objetivo espec�fico est� compuesto por una o varias tareas que lo componen. Una vez declarado cada objetivo espec�fico, debe detallar dichas tareas, haciendo click en el bot�n 'Editar objetivo espec�fico'.</caption>";
		$template .= "<tbody><tr><td>[dep id=ml_obj_especificos]</td></tr>";
		$template .= ($pantalla->existe_dependencia('ml_tareas')) ? "<tr style='margin-top:25px;'><td>[dep id=ml_tareas]</td></tr>" : "";
		$template .= ($pantalla->existe_dependencia('ml_cronograma')) ? "<tr style='margin-top:25px;'><td>[dep id=ml_cronograma]</td></tr>" : "";
		$template .= "</tbody></table>";
		$pantalla->set_template($template);
		
	}

	//-----------------------------------------------------------------------------------
	//---- ml_obj_especificos -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_obj_especificos(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proyecto_obj_especifico')->get_filas();
		foreach($datos as $clave => $objetivo){
			$orden[$clave] = $objetivo['id_obj_especifico']; 
		}
		//Ordena el segundo array, de la misma manera que el primero (ordenado en base al ID)
		array_multisort($orden,$datos);

		
		if($datos){
			$form_ml->set_datos($datos);
		}
		//Aclaraci�n para el usuario
		$form_ml->agregar_notificacion('Indicar la secuencia de metas parciales o hitos que determinan el avance del proyecto','info');
	}

	

	function evt__ml_obj_especificos__modificacion($datos)
	{
		$this->get_datos('proyecto_obj_especifico')->procesar_filas($datos);
	}
	function conf_evt__ml_obj_especificos__seleccion(toba_evento_usuario $evento, $fila)
	{
		if($this->get_datos('proyecto_obj_especifico')->hay_cursor()){
			$evento->desactivar();
		}else{
			$evento->activar();
		}
	}
	function evt__ml_obj_especificos__seleccion($datos)
	{
		$this->get_datos('proyecto_obj_especifico')->set_cursor($datos);
	}
	function get_obj_especificos()
	{
		if(isset($this->s__id_proyecto)){
			return toba::consulta_php('co_proyectos')->get_obj_especificos($this->s__id_proyecto,TRUE);
		}
	}

	//-----------------------------------------------------------------------------------
	//---- ml_tareas --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_tareas(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('obj_especifico_tarea')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
			//Obtengo el objetivo para mostrar su t�tulo en el formulario
			$objetivo = $this->get_datos('proyecto_obj_especifico')->get();
			$form_ml->set_titulo('Tareas relacionadas con el objetivo: '.$objetivo['obj_especifico']);
		}
		//Agrego una aclaraci�n para el usuario
		$form_ml->agregar_notificacion('Indicar la secuencia de actividades para el logro de este objetivo espec�fico','info');
	}

	function evt__ml_tareas__modificacion($datos)
	{
		$this->get_datos('obj_especifico_tarea')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_cronograma ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_cronograma(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('obj_especifico_tiempo')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}
		$form_ml->agregar_notificacion('Indicar el tiempo que demandar� la concreci�n del objetivo espec�fico. Puede indicar mas de un semestre para este objetivo.','info');

	}

	function evt__ml_cronograma__modificacion($datos)
	{
		$this->get_datos('obj_especifico_tiempo')->procesar_filas($datos);
		$this->get_datos('proyecto_obj_especifico')->resetear_cursor();
	}

	/* =====================================================================================*/
	/* ============================== PANT_CRONOGRAMA ======================================*/
	/* =====================================================================================*/
	function conf__pant_cronograma(toba_ei_pantalla $pantalla)
	{
		
		//Numeros ordinales para la generaci�n de cuadro cronograma
		$ordinal = array('1'=>'Primer','2'=>'Segundo','3'=>'Tercer','4'=>'Cuarto');
			
		//Objetivos espec�ficos del proyecto
		$objetivos = toba::consulta_php('co_proyectos')->get_obj_especificos($this->s__id_proyecto,FALSE);

		if(count($objetivos) && isset($this->s__id_proyecto)){
			$tiempos = toba::consulta_php('co_proyectos')->get_objetivos_tiempos($this->s__id_proyecto);
			
			$objs_tiempos = array();
			foreach($tiempos as $tiempo){
				$objs_tiempos[$tiempo['id_obj_especifico']][] = array('anio'=>$tiempo['anio'], 'semestre' => $tiempo['semestre']);
			}	 

			$anios_proyecto = $this->get_anios_proyecto();
		
			$datos = array(
				'duracion'	   => $this->s__duracion,
				'ordinal'		=> $ordinal,
				'objetivos'	  => $objetivos,
				'objs_tiempos'   => $objs_tiempos,
				'anios_proyecto' => $anios_proyecto
			);
			$template = __DIR__."/template_cronograma.php";
			$cronograma = $this->armar_template($template,$datos);
			$pantalla->set_template($cronograma);
		}else{
			$pantalla->agregar_notificacion('Para poder generar un cronograma, debe cargar tareas en la pesta�a \'Plan de Tareas\' y <b>guardar los cambios</b>','info');
		}
	}

	/* =====================================================================================*/
	/* ============================== PANT_CONTROL =========================================*/
	/* =====================================================================================*/

	function conf__pant_control(toba_ei_pantalla $pantalla)
	{
		$resultado_validacion = $this->validar_proyecto();
		$archivo_template     = __DIR__ . '/template_pantalla_control_condiciones.php';
		$template             = $this->armar_template($archivo_template, $resultado_validacion['errores']);
		$pantalla->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_eval_recusados ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_eval_recusados(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('proyecto_recusacion')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}		
	}

	function evt__ml_eval_recusados__modificacion($datos)
	{
		$this->get_datos('proyecto_recusacion')->procesar_filas($datos);
	}

	/* =====================================================================================*/
	/* ============================== JAVASCRIPT ===========================================*/
	/* =====================================================================================*/

	function extender_objeto_js()
	{
		echo "
			//LLAMADA DE ATENCI�N PARA QUE NO SE LE CIERRE LA SESI�N
			//Cada 15 minutos: 15 minutos por 60 segundos por 1000 (porque es en milesimas de segundo)
			setInterval(function(){
				alert('Asegurese de guardar, al menos, parcialmente los cambios que vaya realizando. En caso contrario, si su sesi�n finaliza por inactividad, podr�a perder toda la informaci�n cargada hasta el momento.');
			}, (15*60*1000) );
		";
	}

	/* =====================================================================================*/
	/* ============================== AUXILIARES DEL CI ====================================*/
	/* =====================================================================================*/
	/**
	 * Comportamiento comun a los formularios de carga de tablas auxiliares de integrantes. Todos los formularios de este tipo tienen el mismo comportamiento. Modifican el estado de la variable de sesi�n $this->s__auxiliares, en sus distintas dimensiones
	 * @param  toba_ei_formulario_ml &$formulario		  Formulario que se est� editando
	 * @param  string $identificador_perfil Letra que identifica el perfil que tiene el integrante
	 * @param  string $auxiliar			 String que representa la dimensi�n que hay que modificar del array $this->s__auxiliares
	 * @return void					   
	 */
	function configurar_formulario(&$formulario,$identificador_perfil,$auxiliar)
	{
		$integrantes = $this->get_datos('proyecto_integrante')->get_filas();
		$funcion = toba::consulta_php('co_proyectos')->get_funcion($identificador_perfil);
		
		$miembros = array_filter($integrantes,function($integrante) use ($funcion){
			return ($integrante['id_funcion'] == $funcion['id_funcion']);
		});
		//Agrego todos los miembros nuevos... los que ya estaban no se modifican
		foreach($miembros as $miembro){
			if( ! isset($this->s__auxiliares[$auxiliar][$miembro['nro_documento']])){
				$this->s__auxiliares[$auxiliar][$miembro['nro_documento']] = $miembro;
			}
		}
		//borro todos los miembros que estaban y ya no figuran
		if(isset($this->s__auxiliares[$auxiliar])){
			foreach ($this->s__auxiliares[$auxiliar] as $nro_documento => $miembro) {
				if( ! in_array($nro_documento,array_column($miembros,'nro_documento')) ){
					unset($this->s__auxiliares[$auxiliar][$nro_documento]);
				}
			}
			$formulario->set_datos($this->s__auxiliares[$auxiliar]);
		}
	}

	function get_datos($tabla = NULL){
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}
	function get_ayn($nro_documento)
	{
		return toba::consulta_php('co_personas')->get_ayn($nro_documento);
	}
	/**
	 * Funcion que recibe la fecha de inicio del proyecto y la duraci�n en a�os (un numero entero), y retorna una fecha en formato String que representa la fecha de finalizaci�n del proyecto
	 * @return [String] [Fecha de finalizaci�n del proyecto]
	 */
	function obtener_fecha_hasta($fecha_desde,$duracion)
	{
		return (substr($fecha_desde,0,4) + ($duracion-1))."-12-31"; 
		//C�lculo de la fecha de finalizaci�n del proyecto
		/*$fecha_desde = new DateTime($fecha_desde);
		$intervalo = new DateInterval('P'.$duracion.'Y'); //P2Y o P4Y dependiendo de la duracion
		
		$fecha_hasta = $fecha_desde->add($intervalo)->format('Y-m-d');*/

	}

	/**
	 * Valida todas las condiciones que debe cumplir el proyecto para ser presentado definitivamente
	 * @return [boolean] [TRUE si el proyecto cumple con todas las condiciones para ser presentado.]
	 */
	function validar_proyecto()
	{
		$integrantes = $this->get_datos('proyecto_integrante')->get_filas();
		$errores     = [];

		$errores['campos_obligatorios']   = $this->validar_campos_obligatorios();
		$errores['cargos_docentes']       = $this->validar_cargos_docentes($integrantes);
		$errores['registros_auxiliares']  = $this->validar_registros_auxiliares($integrantes);
		$errores['integrantes_proyectos'] = $this->validar_pertenencia_proyectos($integrantes);
		$errores['funciones_integrantes'] = $this->validar_perfiles($integrantes);
		$errores['existencia_curriculum'] = $this->tienen_curriculums($integrantes);
		
		$integrantes_validos = 0;
		
		//Obtengo un array con los ID de funcion, y como clave los identificadores de perfil
		$funciones = toba::consulta_php('co_proyectos')->get_funciones_integrantes();
		$funciones = array_column($funciones,'id_funcion','identificador_perfil');

		//Si o si debe haber un director, si no hay, la funci�n validar_perfiles genera un error
		$director = $this->obtener_miembros_perfil($integrantes,$funciones['D']);
		if(count($director)){
			$resultado = $this->validar_director(array_shift($director));
			if($resultado !== TRUE){
				$errores['direccion'][] = 'Validaci�n del Director: ' . $resultado;
			}else{
				$integrantes_validos++;
			}
		}

		//Se valida el resultado porque no es obligatorio que un proyecto tenga codirector
		$codirector = $this->obtener_miembros_perfil($integrantes,$funciones['C']);
		if(count($codirector)){
			$resultado = $this->validar_director(array_shift($codirector));
			if($resultado !== TRUE){
				$errores['direccion'][] = 'Validaci�n del Co-Director: ' . $resultado;
			}else{
				$integrantes_validos++;
			}
		}

		//Se valida el resultado porque no es obligatorio que un proyecto tenga codirector
		$subdirector = $this->obtener_miembros_perfil($integrantes,$funciones['S']);
		if(count($subdirector)){
			$resultado = $this->validar_subdirector(array_shift($subdirector));
			if($resultado !== TRUE){
				$errores['direccion'][] = 'Validaci�n del Sub-Director: ' . $resultado;
			}else{
				$integrantes_validos++;
			}
		}

		$doc_investigadores = $this->obtener_miembros_perfil($integrantes,$funciones['I']);
		if( (count($doc_investigadores) + count($subdirector)) < 2){
			$errores['integrantes'][] = 'El proyecto debe tener al menos dos integrantes con la funcion \'Docente Investigador\'';
		}else{
			$integrantes_validos += count($doc_investigadores);
		}

		$inv_externos = count($this->obtener_miembros_perfil($integrantes,$funciones['X']));

		

		if(count($integrantes) - $integrantes_validos - $inv_externos < 1){
			$errores['integrantes'][] = 'El proyecto debe tener al menos un integrante que no sea Director, Co-Director, Sub-Director ni Docente Investigador (Tesista, Alumno, Becario, Personal T�cnico-Profesional). No se consideran los Investigadores Externos';
		}

		
		$hubo_errores = false;
		//Recorro el array para determinar si alguno de sus indices contiene valores (si hubo error)
		foreach ($errores as $tipo_error => $detalle) {
			if (count($detalle)) {
				$hubo_errores = true;
				break;
			}
		}

		return [
			'status'  => !$hubo_errores,
			'errores' => $errores,
		];
	}

	function validar_cargos_docentes($integrantes)
	{
		$errores = [];
		foreach($integrantes as $integrante){
			//Si la persona es docente investigador debe tener cargo docente
			if(toba::consulta_php('co_proyectos')->get_identificador_perfil($integrante['id_funcion']) == 'I'){
				if( ! toba::consulta_php('co_personas')->es_docente($integrante['nro_documento'])){
					$ayn       = $this->get_ayn($integrante['nro_documento']);
					$errores[] = "El integrante $ayn est� definido como Docente Investigador, y no cuenta con cargos docentes activos en la UNNE";
				}
			}
		}
		return $errores;
	}

	function validar_pertenencia_proyectos($integrantes)
	{
		$errores = [];

		if(isset($integrantes[0])){
			$id_proyecto = $integrantes[0]['id_proyecto'];
		}

		foreach ($integrantes as $integrante) {
			if( ! toba::consulta_php('co_proyectos')->puede_integrar_nuevo_proyecto($integrante['nro_documento'],array($id_proyecto))){
				$ayn              = $this->get_ayn($integrante['nro_documento']);
				$fecha_referencia = (date('Y')+1).'-01-01';
				$proyectos        = toba::consulta_php('co_proyectos')->get_proyectos_que_integra_fecha($integrante['nro_documento'],$fecha_referencia);
				$lista            = implode(' / ',array_column($proyectos, 'codigo'));
				$errores[]        = "El integrante $ayn ya forma parte de ".count($proyectos)." proyectos. Solo puede integrar, como m�ximo, dos proyectos. Los proyectos que integra son: $lista";
			}
		}
		return $errores;
	}

	function validar_campos_obligatorios()
	{
		$errores       = [];
		$proyecto      = $this->get_datos('proyectos')->get();
		$tipo_proyecto = $proyecto['tipo'];

		$reglas = json_decode(utf8_encode(file_get_contents(__DIR__.'/validaciones.json')));
		foreach($reglas as $regla){
			//Si la regla no es aplicable a este tipo de proyecto, se la omite
			if( ! in_array($tipo_proyecto, $regla->aplicable_a)){
				continue;
			}
			$registros = $this->get_datos($regla->tabla)->get_filas();
			
			//Se valida la cantidad M�nima de registros
			if($regla->min_registros){
				if(count($registros) < $regla->min_registros){
					$errores[] = "Se debe cargar, como m�nimo {$regla->min_registros} registros en \"{$regla->nombre_mostrar}\". Se encontraron " . count($registros);
				}
			}
			//Se valida la cantidad M�xima de registros
			if($regla->max_registros){
				if(count($registros) > $regla->max_registros){
					$errores[] = "No se pueden ingresar mas de {$regla->max_registros} registros en \"{$regla->nombre_mostrar}\". Se encontraron " . count($registros);
				}
			}
			foreach($registros as $registro){
				foreach($regla->campos as $campo){
					//Si la validacion de ese campo depende de alguna condicion
					//Sirve para condicionar la validacion (por ejemplo, se valida el campo "Justif Codirector" si existe declarado un codirector)
					if(isset($campo->metodo_condicion) && $this->{$campo->metodo_condicion}()){
						if(!$registro[$campo->nombre]){
							$errores[] = "Falta completar el campo {$campo->leyenda} en la pantalla {$campo->pantalla}";
						}
						
					}
				}	
			}
			
		}

		return $errores;
	}

	/**
	 * Retorna los miembros que cumplen una determinada funcion, dentro de los integrantes recibidos como par�metros
	 * @param  [array] $integrantes 
	 * @param  [integer] $id_funcion  
	 * @return [array]			  
	 */
	function obtener_miembros_perfil($integrantes,$id_funcion)
	{
		return array_filter($integrantes,function($integrante) use ($id_funcion){
			return ($integrante['id_funcion'] == $id_funcion);
		});
	}

	/**
	 * Sirve para validar las condiciones que debe cumplir tanto el director como el co-director.
	 */
	function validar_director($persona)
	{
		
		$ayn = $this->get_ayn($persona['nro_documento']);
		if( ! toba::consulta_php('co_proyectos')->esta_exceptuado($persona['nro_documento'])){
			//Si no tiene un cargo vigente
			if(!count(toba::consulta_php('co_personas')->get_cargos_persona($persona['nro_documento'],true))){
				return "El integrante $ayn no tiene cargos vigentes registrados.";
			}

			//Si no tiene categor�a de incentivos 3 o superior (o maestria/doctorado)
			$nivel_academico = toba::consulta_php('co_personas')->get_nivel_academico($persona['nro_documento']);
			$nivel_academico = ($nivel_academico) ? $nivel_academico : array('orden'=>1); //Se asume el mas bajo

			//Se quita la consistencia del nivel acad�mico. Las UA deben pedir excepci�n por mail (y se cargan como excepciones en el sistema)
			if(toba::consulta_php('co_personas')->get_categoria_incentivos($persona['nro_documento']) > 3 
			/*&& $nivel_academico['orden'] < 5*/){ 
				return "El integrante $ayn no tiene Categor�a de Incentivos, o su categor�a es inferior a 3.";
			}
			//si no tiene mayor dedicacion
			if(!toba::consulta_php('co_personas')->tiene_mayor_dedicacion($persona['nro_documento'])){
				if( ! toba::consulta_php('co_personas')->get_cargo_conicet($persona['nro_documento'])){
					return "El integrante $ayn no tiene mayor dedicaci�n";
				}
			}
			return TRUE;
		}else{
			return TRUE;
		}
	}

	//tiene las validaciones necesarias para el sub-director (no son las mismas que el director y co-director)
	function validar_subdirector($persona)
	{
		$ayn = $this->get_ayn($persona['nro_documento']);
		if( ! toba::consulta_php('co_proyectos')->esta_exceptuado($persona['nro_documento'])){
			
			$tiene_cargo = true;
			$tiene_cargo = count(toba::consulta_php('co_personas')->get_cargos_persona($persona['nro_documento'],true));
			if( ! $tiene_cargo){
				$cat_conicet = toba::consulta_php('co_personas')->get_cargo_conicet($persona['nro_documento']);
				if( ! $cat_conicet){
					return "El integrante $ayn no tiene cargos docentes en la UNNE, ni cargos CONICET.";
				}else{
					$tiene_cargo = in_array($cat_conicet,array(2,5));
					if( ! $tiene_cargo){
						return "El integrante $ayn no tiene cargos docentes en la UNNE, ni es Adjunto o Superior en CONICET";
					}
				}
			}	

			if( ! toba::consulta_php('co_personas')->get_categoria_incentivos($persona['nro_documento'])){ 
				return "El integrante $ayn no tiene Categor�a de Incentivos";
			}
			return TRUE;
		}else{
			return TRUE;
		}
		
	}

	
	/**
	 * Valida las condiciones que deben cumplir los integrantes del proyecto en relaci�n a los perfiles que cumplen. Por ejemplo, solo puede haber (y debe haber) un director, mientras que otros perfiles como el subdirector y el codirector, son opcionales, pero en caso de existir, tambien deben ser �nicos.
	 * @param  array $integrantes		 Integrantes declarados por el usuario
	 * @return void					  Si bien esta funcion no retorna ning�n valor, en caso de error, lanza una excepcion de tipo Exception
	 */
	function validar_perfiles($integrantes)
	{
		$errores = [];

		//Matriz de validaciones que se hacen a los integrantes
		$perfiles_validacion = array(
				'D'=>array('unico'=>TRUE,'obligatorio'=>TRUE),
				'C'=>array('unico'=>TRUE,'obligatorio'=>FALSE),
				'S'=>array('unico'=>TRUE,'obligatorio'=>FALSE),
				'I'=>array('unico'=>FALSE,'obligatorio'=>FALSE)
			);
		//El array $perfiles, contiene la distribucion de funciones, es decir:
		// -El perfil 1, aparece 1 vez
		// -El perfil 2, aparece 1 vez
		// -El perfil 7 aparece 6 veces, etc.
		$perfiles = array_count_values(array_column($integrantes, 'id_funcion'));

		foreach ($perfiles_validacion as $identificador_perfil => $condiciones) {
			//Obtengo el ID de funcion que corresponde al perfil
			$funcion = toba::consulta_php('co_proyectos')->get_funcion($identificador_perfil);	

			//Es obligatorio?
			if($condiciones['obligatorio']){
				// Existe?
				if( ! array_key_exists($funcion['id_funcion'], $perfiles)){
					$errores[] = "Debe existir un integrante que tenga asigada la funci�n de {$funcion['funcion']}";
				}
			}
			//Deber�a ser unico?
			if($condiciones['unico']){
				//Es realmente �nico?
				
				if( isset($perfiles[$funcion['id_funcion']]) && $perfiles[$funcion['id_funcion']] > 1){
					$errores[] = "No puede existir mas de un integrante con la funci�n de {$funcion['funcion']}";
				}
			}
		}
		return $errores;
	}

	/**
	 * Retorna Verdadero si todos los integrantes recibidos como par�metros tienen cargado un CVar
	 * @param  [array] $integrantes [Array de integrantes]
	 * @return [boolean]			  []
	 */
	function tienen_curriculums($integrantes)
	{
		$errores = [];
		foreach ($integrantes as $integrante) {
			$archivo = toba::consulta_php('helper_archivos')->ruta_base()."/docum_personal/".$integrante['nro_documento']."/cvar.pdf";
			if( ! file_exists($archivo)){
				$ayn = $this->get_ayn($integrante['nro_documento']);
				$errores[] = "El integrante $ayn no tiene cargado un curriculum.";
			}
		}
		return $errores;
	}

	/**
	 * Por cada vez que se guarda el proyecto (se ejecuta el m�todo evt__guardar()), este m�todo se encarga de regenerar todos los registros de las tablas auxiliares de integrantes. Esto se debe a que, durante la carga, el usuario puede realizar modificaciones en las funciones de las personas, lo que hace que esa persona deje de existir en una tabla, y aparezca como nuevo en otra. Para evitar gestionar todas esas modificaciones, cuando el usuario guarda el proyecto, se elimina todo estado anterior y se vuelven a generar con los detalles que haya guardado el usuario. Durante la carga, las modificaciones realizadas se mantienen en la variable de sesion $this->s__auxiliares.
	 * @return vid 
	 */
	function registrar_cambios_integrantes($id_proyecto)
	{
		if( !isset($this->s__auxiliares)){
			return;
		}
		toba::consulta_php('co_proyectos')->eliminar_auxiliares($id_proyecto);
		$funciones = array(
			array('auxiliar'=>'tesistas',   'tabla'=>'sap_proyecto_tesista',	'identificador_perfil'=>'P'),
			array('auxiliar'=>'becarios',   'tabla'=>'sap_proyecto_becario',	'identificador_perfil'=>'B'),
			array('auxiliar'=>'alumnos',	'tabla'=>'sap_proyecto_alumno' ,	'identificador_perfil'=>'A'),
			array('auxiliar'=>'inv_externo','tabla'=>'sap_proyecto_inv_externo','identificador_perfil'=>'X'),
			array('auxiliar'=>'apoyo',	    'tabla'=>'sap_proyecto_apoyo',	    'identificador_perfil'=>'T')
		);
		
		foreach($funciones as $funcion){
			//obtengo el ID de la funcion
			$perfil = toba::consulta_php('co_proyectos')->get_funcion($funcion['identificador_perfil']);
			if(! array_key_exists($funcion['auxiliar'], $this->s__auxiliares)){
				continue;	
			}
			
			foreach($this->s__auxiliares[$funcion['auxiliar']] as $elementos){

				$cambios = array('id_proyecto' => $id_proyecto,'id_funcion' => $perfil['id_funcion']);
				
				foreach($elementos as $campo => $valor){
					//Si el campo viene vacio, se elimina el campo del insert
					if(strlen($valor) == 0){
						unset($cambios[$campo]);	
					}else{
						$cambios[$campo] = quote($valor);	
					}
				}

				$campos = implode(',',array_keys($cambios));
				$valores = implode(',',array_values($cambios));
				
				/*if($funcion['tabla'] == 'sap_proyecto_inv_externo'){
					echo "<h1>INSERT INTO {$funcion['tabla']} ($campos) VALUES ($valores)</h1>";	
				}*/
				$sql = "INSERT INTO {$funcion['tabla']} ($campos) VALUES ($valores)";
				
				//echo "Insertando registros de {$funcion['auxiliar']}: <br> $sql <hr>";
				
				toba::db()->ejecutar($sql);
			}
		}
	}

	/**
	 * Valida las condiciones de unicidad entre los integrantes. Un integrante puede estar definido mas de una vez, pero debe tener funciones distintas
	 * @param  array $integrantes Arreglo de integrantes cargados por el usuario
	 * @return void			  Si bien esta funcion no devuelve ningun valor, en caso de error lanza una excepcion de tipo toba_error
	 */
	function existen_duplicados($integrantes)
	{
		$mensajes = array();
		//Busco los nro_documento repetidos en la lista
		$duplicados = array_filter(array_count_values(array_column($integrantes, 'nro_documento')),function($num){
			return $num > 1;
		});
		//recorro cada uno de los integrantes que aparecen dos veces o mas
		foreach ($duplicados as $nro_documento => $cantidad) {
			//"use" hace que la variable "nro_documento" est� en el scope de la funcion que recibe array_filter()
			$ocurrencias = array_filter($integrantes,function($integrante) use ($nro_documento){
				return (isset($integrante['nro_documento'])) ? ($integrante['nro_documento'] == $nro_documento) : FALSE;
			});
			
			$iguales = array_filter(array_count_values(array_column($ocurrencias,'id_funcion')),function($num){
				return $num > 1;
			});
			if(count($iguales)){
				$mensajes[] = 'El integrante '.$this->get_ayn($nro_documento)." se declar� dos veces con la misma funci�n";
			}
			
		}
		if(count($mensajes)){
			$mensaje = "Se encontraron los siguientes problemas:<br>".implode("<br>",$mensajes);
			throw new toba_error($mensaje,'Los integrantes pueden declararse mas de una vez, pero deben tener funciones distintas. Por ejemplo, un integrante puede declararse como estudiante de grado (durante un periodo determinado), y declararse nuevamente como tesista de posgrado (en otro periodo posterior).','Integrantes duplicados');
		}

	}

	/**
	 * Retorna un array que contiene los a�os en los cuales tiene vigencia el proyecto. Esto depende de la fecha de inicio y de la duraci�n declarados por el usuario
	 * @return array Arreglo con los a�os de duraci�n del proyecto
	 */
	function get_anios_proyecto($fecha_desde = NULL,$duracion = NULL)
	{
		$duracion = (isset($this->s__duracion) && $this->s__duracion ) ? $this->s__duracion : $duracion;
		$opciones = array();

		//Funciona el try cuando el m�todo es llamado desde dentro de este CI. Arroja una excepci�n cuando "this->get_datos" da error al llamar desde otro CI. En ese caso hay que pasarle una "fecha_desde" para que funcione
		try {
			$proyecto = $this->get_datos('proyectos')->get();
			$anio_inicio = ($proyecto) ? date('Y',strtotime($proyecto['fecha_desde'])) : date('Y',strtotime($fecha_desde));
		} catch (Exception $e) {
			$anio_inicio = date('Y',strtotime($fecha_desde));
		}
		
		
		
		for($i=0; $i<$duracion; $i++){
			$opciones[] = array('anio'=>($anio_inicio+$i));
		}
		return $opciones;
	}

	function validar_registros_auxiliares($integrantes)
	{
		$errores = [];
		foreach($integrantes as $integrante){
			if( ! toba::consulta_php('co_proyectos')->tiene_registro_auxiliar($integrante) ){
				$ayn       = $this->get_ayn($integrante['nro_documento']);
				$errores[] = "El integrante $ayn no tiene informaci�n necesaria en la solapa 'Recursos Humanos'";
			}
		}
		return $errores;
	}

	function existe_rol_entre_integrantes($identificador_perfil)
	{
		$funciones = toba::consulta_php('co_proyectos')->get_funciones_integrantes();
		$id_funcion_buscada = array_column($funciones, 'id_funcion','identificador_perfil');
		//Obtengo el ID de la BD que representa la funcion de Co-Director, para ver si existe entre los integrantes
		$id_funcion_buscada = $id_funcion_buscada[$identificador_perfil];

		$integrantes = $this->get_datos('proyecto_integrante')->get_filas();
		$funciones_integrantes = array_column($integrantes,'id_funcion');
		return in_array($id_funcion_buscada, $funciones_integrantes);

	}

	function existe_codirector()
	{
		return $this->existe_rol_entre_integrantes('C');
	}

	function existe_subdirector()
	{
		return $this->existe_rol_entre_integrantes('S');
	}


	
}
?>