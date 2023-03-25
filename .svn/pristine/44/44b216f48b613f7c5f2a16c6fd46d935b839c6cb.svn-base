<?php
class ci_apoyo_solicitud extends sap_ci
{
	protected $usuario;
	protected $s__filtro;
	protected $ruta_base;
	protected $url_base;
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
		$this->ruta_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$this->url_base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		
		$this->usuario = toba::usuario()->get_id();
		$conv = toba::consulta_php('co_convocatorias')->get_convocatorias_vigentes_apoyos();
		if( ! $conv){
			$this->ocultar_evento('nuevo');
		}
		//Solo el administrador puede filtrar datos
		if( ! $this->soy_admin()){
			if($this->pantalla()->existe_dependencia('filtro_apoyos')){
				$this->pantalla()->eliminar_dep('filtro_apoyos');	
			}
		}

		//if( ! es_admin($this->usuario)){
			if($this->get_datos()->esta_cargada()){
				$solicitud = $this->get_datos('sap_apoyo_solicitud')->get();
				if($solicitud['estado'] != 'A'){
					$this->ocultar_evento(array('cerrar_solicitud'));
					//Solo se bloquea cuando no es admin. El admin, luego de cerrada la solicitud, necesita cargar el numero de expediente de presentaci?.
					if( ! $this->soy_admin()){
						$this->bloquear_formularios();	
						$this->ocultar_evento(array('guardar'));
					}
				}
			}else{
				$this->ocultar_evento('cerrar_solicitud');
			}
		//}
	}

	function evt__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	function evt__guardar()
	{
		try {
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			$mensaje = $e->get_mensaje();
			if($e->get_sqlstate() == 'db_23505'){
				$mensaje = "Se está intentando guardar datos duplicados. Por favor, asegurese que no exista un pedido de aproyo económico para el proyecto que acaba de seleccionar. En caso de existir, debe editar el mismo (si fuera necesario) y cerrar la soicitud.";
			}
			toba::notificacion()->error('Ocurrió un error: '.$mensaje,'Vuelva a la pantalla anterior y verifique los pedidos que ya tiene cargados');
		}
	}

	function evt__nuevo()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_edicion');	
	}

	function evt__cerrar_solicitud()
	{
		$this->get_datos('sap_apoyo_solicitud')->set(array('estado'=>'C'));
		$this->evt__guardar();
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_apoyos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_apoyos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__filtro_apoyos__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__filtro_apoyos__cancelar()
	{
		unset($this->s__filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cu_apoyos --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_apoyos(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$filtro = (isset($this->s__filtro)) ? $this->s__filtro : array();
		if( ! $this->soy_admin()){
			$filtro = array('nro_documento'=>$this->usuario);
		}
		$cuadro->set_datos(toba::consulta_php('co_apoyos')->get_apoyos($filtro));
	}

	function evt__cu_apoyos__seleccion($seleccion)
	{
		//Se arma un array para la carga porque la variable selecci? trae otros campos, que sirven para los conf_evt del cuadro, pero no forman parte de la clave del registro
		$this->get_datos()->cargar(array('id_apoyo'=>$seleccion['id_apoyo']));
		$this->set_pantalla('pant_edicion');
	}

	function conf_evt__cu_apoyos__ver_formulario(toba_evento_usuario $evento, $fila)
	{
		$params = $evento->get_parametros();
		$params = explode('||',$params);
		//Si la solicitud est? cerrada y el pedido es de este a?, se muestra el formulario
		if( toba::consulta_php('co_apoyos')->get_estado_apoyo($params[0]) != 'A' && $params[1] == date('Y')){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	function evt__cu_apoyos__borrar($seleccion)
	{
		$this->get_datos()->resetear();
		$this->get_datos()->cargar($seleccion);
		$this->get_datos()->eliminar_todo();
		$this->get_datos()->resetear();
	}

	function conf_evt__cu_apoyos__ver_cbu(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||', $evento->get_parametros());
		$dni_resp_fondos = $params[2];
		if( ! file_exists("{$this->ruta_base}/docum_personal/$dni_resp_fondos/cbu.pdf")  ){
			$evento->ocultar();
		}else{
			$evento->mostrar();
		}
	}

	function servicio__ver_cbu()
	{
		$params = toba::memoria()->get_parametros();
		header("Location: {$this->url_base}/docum_personal/{$params['nro_documento_resp_fondos']}/cbu.pdf");
	}

	function conf_evt__cu_apoyos__borrar(toba_evento_usuario $evento, $fila)
	{
		if( ! $this->soy_admin()){
			$evento->ocultar();
		}else{
			$evento->mostrar();
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_apoyo_solicitud ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_apoyo_solicitud(sap_ei_formulario $form)
	{
		
		$conv = toba::consulta_php('co_convocatorias')->get_convocatorias_vigentes_apoyos();
		$conv_anio_ant = date('Y', strtotime($conv[0]['fecha_hasta']))-1;
		
		//Vigentes hasta el 31 de diciembre del a? pasado, solo propios y cerrados.
		$filtro = array('vigente_hasta' => $conv_anio_ant.'-12-31', 'solo_propios' => TRUE, 'estado' => 'C');
		

		if( ! $this->soy_admin()){
			$filtro['dirigido_por'] = $this->usuario;
		}
		
		$proyectos = toba::consulta_php('co_proyectos')->get_proyectos($filtro);
		foreach ($proyectos as $indice => $proyecto) {
			if($proyecto['fecha_desde'] < $conv_anio_ant.'-01-01' && toba::consulta_php('co_proyectos')->adeuda_informe($proyecto['id'],'',$conv_anio_ant))
			{
				unset($proyectos[$indice]);
			}
			if(toba::consulta_php('co_apoyos')->adeuda_rendicion($proyecto['codigo']))
			{
				unset($proyectos[$indice]);
			}
			if(toba::consulta_php('co_proyectos')->tiene_informe_desaprobado($proyecto['id'], array('Primer Avance','Segundo Avance','Final'),$conv_anio_ant))
			{
				unset($proyectos[$indice]);
			}
		}
		$proyectos = array_column($proyectos,'descripcion_corta','id');
		$opciones = array('nopar' => "-- Seleccione --");
		foreach ($proyectos as $id => $descripcion) {
			$opciones[$id] = $descripcion;
		}
		
		$form->ef('id_proyecto')->set_opciones($opciones);


		$datos = $this->get_datos('sap_apoyo_solicitud')->get();
		if($datos){
			$form->set_datos($datos);
		}

		$base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');

		$resp = $form->ef('responsable_fondos')->get_estado();
		if($resp){
			$ruta_cbu = $base."/docum_personal/".$resp."/cbu.pdf";
			if(file_exists($ruta_cbu)){
				$form->ef('constancia_cbu')->set_estado('cbu.pdf');
			}	
		}

		//Si no es admin, se oculta el EF (n?mero expediente). Si es admin, se desbloquea el EFS (bloqueado por estar cerrada la solicitud)
		if( ! $this->soy_admin() && $form->existe_ef('nro_expte_present')){
			$form->desactivar_efs(array('nro_expte_present'));
		}
	}	
	
	function evt__form_apoyo_solicitud__modificacion($datos)
	{
		if(isset($datos['constancia_cbu']) && $datos['constancia_cbu']){
			$dni = $this->dep('form_apoyo_solicitud')->ef('responsable_fondos')->get_estado();
			$ruta = "docum_personal/".$dni;
			toba::consulta_php('helper_archivos')->subir_archivo($datos['constancia_cbu'],$ruta,'cbu.pdf',array('pdf'));
			unset($datos['constancia_cbu']);
		}
		if( ! $this->get_datos('sap_apoyo_solicitud')->esta_cargada()){
			$datos['anio'] = date('Y');
		}
		$this->get_datos('sap_apoyo_solicitud')->set($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- ml_necesidades ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_necesidades(sap_ei_formulario_ml $form_ml)
	{
		$datos = $this->get_datos('sap_apoyo_presupuesto')->get_filas();
		if($datos){
			$form_ml->set_datos($datos);
		}

	}

	function evt__ml_necesidades__modificacion($datos)
	{
		$this->get_datos('sap_apoyo_presupuesto')->procesar_filas($datos);
	}

/*	function evt__ml_necesidades__pedido_registro_nuevo()
	{
		$this->dep('ml_necesidades')->set_registro_nuevo(array('id_rubro'=>4));
	}*/

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	
	function bloquear_formularios()
	{
		$formularios = $this->get_dependencias_clase('form');
		foreach($formularios as $formulario){
			$this->dep($formulario)->set_solo_lectura();
			if(strpos(get_class($this->dep($formulario)),'ml') !== FALSE){
				$this->dep($formulario)->desactivar_agregado_filas();
			}
		}
	}

	protected function ocultar_evento($evento)
	{
		if(is_array($evento)){
			foreach ($evento as $evt) {
				if($this->pantalla()->existe_evento($evt)){
					$this->pantalla()->eliminar_evento($evt);	
				}
			}
		}else{
			if($this->pantalla()->existe_evento($evento)){
				$this->pantalla()->eliminar_evento($evento);	
			}
		}
		
	}

	//-----------------------------------------------------------------------------------
	//---- Servicios a eventos de tipo VÃ­nculo ------------------------------------------
	//-----------------------------------------------------------------------------------
	function servicio__ver_formulario()
	{
		$params = toba::memoria()->get_parametros();
		$datos = toba::consulta_php('co_apoyos')->get_apoyos(array('id_apoyo'=>$params['id_apoyo']));
		if(count($datos)){
			$datos = $datos[0];
			$datos['conf']['nombre_sec'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('nombre_secretario');
			$datos['conf']['genero_sec'] = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('genero_secretario');
			/* ----------- USUARIO SOLICITANTE -------------------- */
			$dni = toba::usuario()->get_id();
			$datos['conf']['ayn_solicitante'] = toba::consulta_php('co_personas')->get_ayn($dni);
			$datos['conf']['dni_solicitante'] = $dni;
			/* ---------------------------------------------------- */

			$datos['nec_presupuestarias'] = toba::consulta_php('co_apoyos')->get_necesidades_presupuestarias($params['id_apoyo']);
			$formulario = new Form_solicitud_apoyo($datos);
			$formulario->mostrar();	
		}else{
			echo "No se pudo recuperar información relacionada con el ID de apoyo solicitado (ID: ".$params['id_apoyo'].")";
		}
		
	}


	



	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
		//setlocale(LC_MONETARY, 'es_AR');
		$maximo_otorgar = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('apoyo_econ_max_otorgar');
		if($maximo_otorgar){
			//$maximo_otorgar = money_format('%[ind_opc][ancho][.2][der][i]', $maximo_otorgar);
			if(function_exists('money_format')){
				$maximo_otorgar = money_format('%.2n', $maximo_otorgar);	
			}
		}
		
		$template = "<table style=\"width: 100%\">
						<tbody>
							<tr>
								<td>
									[dep id=form_apoyo_solicitud]</td>
							</tr>
							<tr>
								<td>
									[dep id=ml_necesidades]</td>
							</tr>";
		//Si no se estableci? el monto m?imo, no se agrega la aclaraci?
		if($maximo_otorgar){
			$template .= "<tr>
								<td style=\"text-align: right;\">
									<span style=\"color:red; font-size: 1.4em;\">
										El apoyo econ&oacute;mico ser&aacute; de hasta $".$maximo_otorgar." por proyecto
									</span>
							</td>
							</tr>";
		}
		$template .= "</tbody>
					</table>";
		$this->pantalla()->set_template($template);
	}

	function ajax__tiene_archivo_cbu($parametros,toba_ajax_respuesta $respuesta)
	{
		$base = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$ruta_cbu = $base."/docum_personal/".$parametros['nro_documento']."/cbu.pdf";
		$respuesta->set(array('existe_cbu'=>file_exists($ruta_cbu)));	
	}

	

	

}
?>