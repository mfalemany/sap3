<?php
class ci_evaluacion_proyectos extends sap_ci
{
	protected $s__volver_a;
	protected $s__tabla_en_edicion;

	function bloquear_edicion(&$form)
	{
		if($this->pantalla()->existe_evento('cerrar_evaluacion')){
			$this->pantalla()->evento('cerrar_evaluacion')->anular();	
		}
		if($this->pantalla()->existe_evento('guardar')){
			$this->pantalla()->evento('guardar')->anular();
		}
		$form->set_solo_lectura();
	}

	// -------------------------------------------------------------------------------------
	// --------------- EVENTOS DEL CI ------------------------------------------------------
	// -------------------------------------------------------------------------------------
	function evt__guardar()
	{
		
		$dep = $this->datos('proyectos')->esta_cargada() ? 'proyectos' : 'programas';
		$this->datos($dep)->sincronizar();
		//$this->datos($dep)->resetear();	
	}

	function evt__cerrar_evaluacion()
	{
		
		$dep = (strpos($this->s__tabla_en_edicion,'programa') !== FALSE) ? 'programas' : 'proyectos';
		$this->datos($dep,$this->s__tabla_en_edicion)->set(array('estado'=>'C'));
		$this->evt__guardar();
		$this->controlador()->set_pantalla('pant_seleccion');
		$this->controlador()->dep('ci_evaluacion_seleccion')->set_pantalla($this->s__volver_a);
	}

	function evt__cancelar()
	{
		
		$dep = $this->datos('proyectos')->esta_cargada() ? 'proyectos' : 'programas';
		$this->datos($dep)->resetear();
		$this->controlador()->set_pantalla('pant_seleccion');
		$this->controlador()->dep('ci_evaluacion_seleccion')->set_pantalla($this->s__volver_a);

	}


	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PROYECTO NUEVO" -----------------------------
	// ---------------------------------------------------------------------------------


	function conf__form_eval_pi_nuevo(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluaci?. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('proyectos','sap_proy_pi_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('proyectos','sap_proy_pi_eval')->set_cursor($evaluacion[0]);
			
			//Si la evaluacion ya est?cerrada, no puede hacer cambios
			if($evaluacion[0]['estado'] == 'C'){
				////trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_pi_nuevo",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}
			
			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_pi_nuevo__modificacion($datos)
	{
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('proyectos','sap_proy_pi_eval')->set($datos);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PDTS NUEVO" -----------------------------
	// ---------------------------------------------------------------------------------

	function conf__form_eval_pdts_nuevo(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluaci?. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('proyectos','sap_proy_pdts_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('proyectos','sap_proy_pdts_eval')->set_cursor($evaluacion[0]);

			//Si la evaluacion ya estÃ¡ cerrada, no puede hacer cambios
			if($evaluacion[0]['estado'] == 'C'){
				//trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_pdts_nuevo",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}
			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_pdts_nuevo__modificacion($datos)
	{
		
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('proyectos','sap_proy_pdts_eval')->set($datos);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PROGRAMA NUEVO" -----------------------------
	// ---------------------------------------------------------------------------------

	function conf__form_eval_programa_nuevo(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluación. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('programas','sap_programa_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('programas','sap_programa_eval')->set_cursor($evaluacion[0]);

			//Si la evaluacion ya estÃ¡ cerrada, no puede hacer cambios
			if($evaluacion[0]['estado'] == 'C'){
				//trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_programa_nuevo",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}

			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_programa_nuevo__modificacion($datos)
	{
		
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('programas','sap_programa_eval')->set($datos);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PROYECTO INFORME" ---------------------------
	// ---------------------------------------------------------------------------------

	function conf__form_eval_pi_informe(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluaci?. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('proyectos','sap_proy_pi_informe_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('proyectos','sap_proy_pi_informe_eval')->set_cursor($evaluacion[0]);
			//Si la evaluacion ya estÃ¡ cerrada, no puede hacer cambios
			if(isset($evaluacion[0]['estado']) && $evaluacion[0]['estado'] == 'C'){
				//trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_pi_informe",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}
			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_pi_informe__modificacion($datos)
	{
		
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('proyectos','sap_proy_pi_informe_eval')->set($datos);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PDTS INFORME" -------------------------------
	// ---------------------------------------------------------------------------------

	function conf__form_eval_pdts_informe(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluaci?. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('proyectos','sap_proy_pdts_informe_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('proyectos','sap_proy_pdts_informe_eval')->set_cursor($evaluacion[0]);

			//Si la evaluacion ya estÃ¡ cerrada, no puede hacer cambios
			if($evaluacion[0]['estado'] == 'C'){
				//trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_pdts_informe",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}


			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_pdts_informe__modificacion($datos)
	{
		
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('proyectos','sap_proy_pdts_informe_eval')->set($datos);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "EVAL. PROGRAMA INFORME" ---------------------------
	// ---------------------------------------------------------------------------------

	function conf__form_eval_programa_informe(sap_ei_formulario $form)
	{
		//un proyecto puede tener mas de una evaluaci?. Establezco un criterio, y obtengo la evaluacion
		//realizada por el usuario actualmente logueado. Si existe tal, seteo el cursor del datos_tabla
		$evaluacion = $this->datos('programas','sap_programa_informe_eval')->get_filas(array('nro_documento_evaluador'=>toba::usuario()->get_id()));
		if(count($evaluacion)){
			$this->datos('programas','sap_programa_informe_eval')->set_cursor($evaluacion[0]);

			//Si la evaluacion ya estÃ¡ cerrada, no puede hacer cambios
			if($evaluacion[0]['estado'] == 'C'){
				//trigger_error("Llamando a Bloquear Edicion desde conf__form_eval_programa_informe",  E_USER_WARNING);
				$this->bloquear_edicion($form);
			}


			$form->set_datos($evaluacion[0]);	
		}
	}

	function evt__form_eval_programa_informe__modificacion($datos)
	{
		
		$datos['nro_documento_evaluador'] = toba::usuario()->get_id();
		$this->datos('programas','sap_programa_informe_eval')->set($datos);
	}

	
	// ---------------------------------------------------------------------------------
	// ------------------- PANTALLA "SELECCION DE INFORME A EVALUAR" -------------------
	// ---------------------------------------------------------------------------------

	function conf__cu_seleccion_informe(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		//determina a que pantalla se regresa ante un evento
		$this->s__volver_a = 'pant_informes';
		
		if($this->datos('proyectos')->esta_cargada()){
			$proy = $this->datos('proyectos','sap_proyectos')->get();
			$tipo = $proy['tipo'];
		}else{
			$proy = $this->datos('programas','sap_programas')->get();
			$tipo = 'C';
		}
		//si es un proyecto, tiene un ID, sino es un programa
		$id = (isset($proy['id'])) ? $proy['id'] : $proy['codigo'];


		$filtro = array('id'=>$id,'tipo'=>$tipo);
		$cuadro->set_datos(toba::consulta_php('co_proyectos')->get_informes_proyecto($filtro));

	}

	function evt__cu_seleccion_informe__seleccion($seleccion)
	{
		
		$filtro = array('id_informe'=>$seleccion['id_informe']);
		switch ($seleccion['tipo']) {
			case '0':
				$id = $this->datos('proyectos','sap_proyectos_pi_informe')->get_id_fila_condicion($filtro);
				$this->datos('proyectos','sap_proyectos_pi_informe')->set_cursor($id[0]);
				$this->set_pantalla('pant_eval_pi_informe');
				break;
			case 'D':
				$id = $this->datos('proyectos','sap_proyectos_pdts_informe')->get_id_fila_condicion($filtro);
				$this->datos('proyectos','sap_proyectos_pdts_informe')->set_cursor($id[0]);
				$this->set_pantalla('pant_eval_pdts_informe');
				break;
			case 'C':
				$id = $this->datos('programas','sap_programa_informe')->get_id_fila_condicion($filtro);
				$this->datos('programas','sap_programa_informe')->set_cursor($id[0]);
				$this->set_pantalla('pant_eval_programa_informe');
				break;
			default:
				# code...
				break;
		}
	}

	function conf_evt__cu_seleccion_informe__ver_pdf_informe(toba_evento_usuario $evento, $fila)
	{
		//Parametros: id_informe,id,codigo,tipo,fecha_presentacion,estado
		if($this->soy_admin()){
			$evento->mostrar();
		}else{
			//params[5] contiene 'A' o 'C' (estado abierto o cerrado del informe)
			$params = explode('||',$evento->get_parametros());
			if($params[5] == 'C'){
				$evento->mostrar();
			}else{
				$evento->ocultar();	
			}
		}
	}

	function conf_evt__cu_seleccion_informe__seleccion(toba_evento_usuario $evento, $fila)
	{
		
		$evento->mostrar();
		$params = $evento->get_parametros();
		list($id_informe,$id_proyecto,$codigo,$tipo,$fecha_presentacion) = explode('||',$params);
		$fecha = date_create($fecha_presentacion);
		//el limite de evaluacion es el 30 de abril del a? siguiente a la presentacion
		$limite_evaluacion = date(($fecha->format('Y')+1).'-04-30');
		
		if( ! $this->soy_admin()){
			if(date('Y-m-d') > $limite_evaluacion){
				$evento->ocultar();
			}else{
				$id = (in_array($tipo,array('0','D'))) ? $id_proyecto : $codigo;
				//Se muestra solo la lupa en el último informe presentado.
				if($id_informe == toba::consulta_php('co_proyectos')->get_id_ultimo_informe($id,$tipo)){
					$evento->mostrar();	
				}else{
					$evento->ocultar();
				}
			}
		}else{
			$evento->mostrar();	
		}
	}

	

	// ---------------------------------------------------------------------------------
	// ------------------- CONFIGURACI? DE PANTALLAS ----------------------------------
	// ---------------------------------------------------------------------------------

	function conf__pant_eval_pi_nuevo()
	{
		$this->s__tabla_en_edicion = 'sap_proy_pi_eval';
		$this->configurar_pantalla('pi','nuevo');
	}
	function conf__pant_eval_pdts_nuevo()
	{
		$this->s__tabla_en_edicion = 'sap_proy_pdts_eval';
		$this->configurar_pantalla('pdts','nuevo');
	}
	function conf__pant_eval_programa_nuevo()
	{
		$this->s__tabla_en_edicion = 'sap_programa_eval';
		$this->configurar_pantalla('programa','nuevo');
	}

	function conf__pant_eval_pi_informe()
	{
		$this->s__tabla_en_edicion = 'sap_proy_pi_informe_eval';
		$this->configurar_pantalla('pi','informe');	
	}
	function conf__pant_eval_pdts_informe()
	{
		$this->s__tabla_en_edicion = 'sap_proy_pdts_informe_eval';
		$this->configurar_pantalla('pdts','informe');
	}
	function conf__pant_eval_programa_informe()
	{
		$this->s__tabla_en_edicion = 'sap_programa_informe_eval';
		$this->configurar_pantalla('programa','informe');
	}

	function configurar_pantalla($tipo,$estado)
	{
		$base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		//esta linea determina a que pantalla volver cuando se confirma o cancela la operacion actual
		$this->s__volver_a = ($estado == 'nuevo') ? 'pant_nuevos' : 'pant_informes';
		//SI ES UN PI O PDTS RECIBE EL MISMO TRATO.
		if(in_array($tipo,array('pi','pdts'))){
			if($this->datos('proyectos')->esta_cargada()){
				$proyecto = $this->datos('proyectos','sap_proyectos')->get();
				$integrantes = toba::consulta_php('co_proyectos')->get_integrantes(array('id'=>$proyecto['id']));
				$contenido = ($estado == 'nuevo') ? toba::consulta_php('co_proyectos')->get_contenido($proyecto['id']) : NULL;

				$tipos_proyecto = array('0'=>'Proyecto','D'=>'PDTS');

				$form = '[dep id=form_eval_'.$tipo.'_'.$estado.']';

				$archivo_proyecto = ($estado == 'nuevo') ? NULL : 'Contenido.zip';

				$parametros = array(
					'titulo_proyecto'       => $proyecto['descripcion'],
					'tipo_proyecto'         => $tipos_proyecto[$proyecto['tipo']],
					'codigo_proyecto'       => $proyecto['codigo'],
					'id_proyecto'           => $proyecto['id'],
					'fecha_desde'           => date_create($proyecto['fecha_desde'])->format('d/m/Y'),
					'fecha_hasta'           => date_create($proyecto['fecha_hasta'])->format('d/m/Y'),
					'entidad_financiadora'  => $proyecto['entidad_financiadora'],
					'formulario_evaluacion' => $form,
					'integrantes'           => $integrantes,
					'contenido'             => $contenido,
					'estado'                => $estado,
					'archivo_proyecto'      => '/documentos/proyectos/'.$proyecto['codigo'].'/Contenido.zip'
				);
			}
		}else{ //SE TRATA DISTINTO SI ES UN PROGRAMA
			if($this->datos('programas','sap_programas')->esta_cargada()){
				$programa = $this->datos('programas','sap_programas')->get();
			}
			$form = '[dep id=form_eval_programa_'.$estado.']';
			$parametros = array(
					'titulo_proyecto'       => $programa['denominacion'],
					'tipo_proyecto'         => 'Programa',
					'codigo_proyecto'       => $programa['codigo'],
					'id_proyecto'           => '--',
					'fecha_desde'           => date_create($programa['fecha_desde'])->format('d/m/Y'),
					'fecha_hasta'           => date_create($programa['fecha_hasta'])->format('d/m/Y'),
					'entidad_financiadora'  => 'Sec. Gral. de Ciencia y T?nica - Universidad Nacional del Nordeste',
					'formulario_evaluacion' => $form,
					'estado'                => $estado,
					'archivo_proyecto'      => "/documentos/proyectos/".$programa['codigo']."/Contenido.zip"
				);
					
		}
		$tpl = __DIR__ . "/template_evaluacion.php";
		$this->pantalla()->set_template($this->armar_template($tpl,$parametros));
	}

	// ---------------------------------------------------------------------------------
	// ------------------- ADAPTADOR PARA MANEJO DE DATOS ------------------------------
	// ---------------------------------------------------------------------------------

	function datos($relacion,$tabla=NULL)
	{

		return ($tabla) ? $this->dep($relacion)->tabla($tabla) : $this->dep($relacion);
	}

	// ---------------------------------------------------------------------------------
	// ------------------- EXTENSI? JAVASCRIPT ----------------------------------------
	// ---------------------------------------------------------------------------------

	function extender_objeto_js()
	
	{
		
		echo "
		//============== Si se está evaluando un PROGRAMA NUEVO ================================
		if( typeof({$this->dep('form_eval_programa_nuevo')->objeto_js}) != 'undefined'){
			//obtengo el objeto JS y los efs
			obj = {$this->dep('form_eval_programa_nuevo')->objeto_js};
			formulario_actual = 'form_eval_programa_nuevo';
			agregar_listeners(obj);
		}
		//============== Si se está evaluando un PI NUEVO ================================
		if( typeof({$this->dep('form_eval_pi_nuevo')->objeto_js}) != 'undefined'){
			//obtengo el objeto JS y los efs
			obj = {$this->dep('form_eval_pi_nuevo')->objeto_js};
			formulario_actual = 'form_eval_pi_nuevo';
			agregar_listeners(obj);
		}
		//============== Si se está evaluando un PDTS NUEVO ================================
		if( typeof({$this->dep('form_eval_pdts_nuevo')->objeto_js}) != 'undefined'){
			$('#puntaje_total_contenedor').remove();
			$('.importante').remove();
			obj = {$this->dep('form_eval_pdts_nuevo')->objeto_js};
		}
		//============== Si se está evaluando un INFORME DE PROGRAMA ================================
		if( typeof({$this->dep('form_eval_programa_informe')->objeto_js}) != 'undefined'){
			$('#puntaje_total_contenedor').remove();
			$('.importante').remove();
			formulario_actual = 'form_eval_programa_informe';
		}
		//============== Si se está evaluando un INFORME DE PI ================================
		if( typeof({$this->dep('form_eval_pi_informe')->objeto_js}) != 'undefined'){
			$('#puntaje_total_contenedor').remove();
			$('.importante').remove();
			formulario_actual = 'form_eval_pi_informe';
		}
		//============== Si se está evaluando un INFORME DE PDTS ================================
		if( typeof({$this->dep('form_eval_pdts_informe')->objeto_js}) != 'undefined'){
			$('#puntaje_total_contenedor').remove();
			$('.importante').remove();
			formulario_actual = 'form_eval_pdts_informe';
		}

		function agregar_listeners(obj){
			efs = obj.efs();
			//agrego un listener para cuando cambien de valor
			$.each(efs,function(clave,elem){
				//solo para aquellos que en su nombre contengan 'punt'
				if(clave.indexOf('punt') >= 0){
					obj.ef(clave).cuando_cambia_valor('actualizarPuntaje()');
				}
			});	
		}
		
		function actualizarPuntaje(){
			var total = 0;
			var clasificacion;
			//recorro todos los elementos y los sumo al total
			$.each(efs,function(clave,elem){
				if(clave.indexOf('punt') >= 0 && typeof(obj.ef(clave).get_estado()) == 'number'){
					total += parseFloat(obj.ef(clave).get_estado());
				}
			});

			
			puntos = total/10;
			
			switch(true){
				case (total < 55):
					clasificacion = 'No aprobado';
					valor_combo = 'N';
					break;
				case (total < 75):
					clasificacion = 'Aprobado - Bueno';
					valor_combo = 'B';
					break;
				case (total < 95):
					clasificacion = 'Aprobado - Muy bueno';
					valor_combo = 'M';
					break;
				case (total >= 95):
					clasificacion = 'Aprobado - Excelente';
					valor_combo = 'E';
					break;	
			}

			$('#puntaje_total_numero').html(parseFloat(puntos).toFixed(0));
			$('#puntaje_total_descripcion').html(clasificacion);

			//Asigno el puntaje total obtenido al select de evaluación
			if(typeof({$this->objeto_js}.dep(formulario_actual).ef('result_final_evaluacion')) != 'undefined'){
				if(typeof(valor_combo) != 'undefined'){
					{$this->objeto_js}.dep(formulario_actual).ef('result_final_evaluacion').set_solo_lectura(false);
					{$this->objeto_js}.dep(formulario_actual).ef('result_final_evaluacion').set_estado(valor_combo);
				}
				{$this->objeto_js}.dep(formulario_actual).ef('result_final_evaluacion').set_solo_lectura(true);
			}

		}";
	}

}
?>