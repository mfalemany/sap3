<?php
class ci_mis_proyectos extends sap_ci
{
	protected $s__seleccion;
	protected $s__volver_a;
	protected $s__evaluaciones;
	protected $s__es_admin;
	protected $s__filtro;

	public function conf()
	{
		if( ! $this->soy_admin()){
			/* SI NO HAY UNA CONVOCATORIA ABIERTA, SE ELIMINA EL BOTÓN PARA CREAR UN PROYECTO */
			if(!toba::consulta_php('co_proyectos')->hay_convocatoria_abierta()){
				if($this->pantalla()->existe_evento('nuevo_proyecto')){
					$this->pantalla()->eliminar_evento('nuevo_proyecto');
				}
			}
			if($this->pantalla()->existe_dependencia('filtro_proyectos')){
				$this->pantalla('pant_seleccion_proyecto')->eliminar_dep('filtro_proyectos');
			}
		}else{
			$this->s__es_admin = true;	
		}

		if($this->pantalla()->existe_dependencia('filtro_proyectos') && toba::memoria()->existe_dato('filtro_proyectos')){
			$this->s__filtro = toba::memoria()->get_dato('filtro_proyectos');
		}
	}

	//Permite acceder a la variable protegida $s__seleccion desde los controladores hijo
	function get_seleccion()
	{
		return isset($this->s__seleccion) ? $this->s__seleccion['id'] : FALSE;
	}
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_seleccion_instancia(toba_ei_pantalla $pantalla)
	{
		$this->s__volver_a = 'pant_seleccion_proyecto';
	}

	function conf__pant_informe_evaluacion(toba_ei_pantalla $pantalla)
	{
		$this->s__volver_a = 'pant_seleccion_instancia';
		if(!$this->s__evaluaciones){
			$this->set_pantalla($this->s__volver_a);
			return;
		}
		$archivo = __DIR__.'/template_proyectos.php';
		//echo $this->s__evaluaciones['ruta_template'];
		$template = $this->armar_template($archivo, $this->s__evaluaciones);
		$this->pantalla()->set_template($template);
	}
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__nuevo_proyecto()
	{
		$this->dep('ci_detalles_proyecto')->dep('datos')->resetear();
		$this->set_pantalla('pant_detalles_proyecto');
	}

	function evt__volver()
	{
		$this->set_pantalla($this->s__volver_a);
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_proyectos -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_proyectos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);

		}
	}

	function evt__filtro_proyectos__filtrar($datos)
	{
		$this->s__filtro = $datos;
		toba::memoria()->set_dato('filtro_proyectos',$datos);
	}

	function evt__filtro_proyectos__cancelar()
	{
		unset($this->s__filtro);
		toba::memoria()->eliminar_dato('filtro_proyecto');
	}

	//-----------------------------------------------------------------------------------
	//---- cu_proyectos -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_proyectos(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		//si es admin, puede ver todos los proyectos (sin filtro). Si no lo es, solo ve los suyos
		$cond_propiedad = array('dirigido_por'=>toba::usuario()->get_id());
		
		$filtro = (isset($this->s__filtro)) ? $this->s__filtro : array();
		
		$filtro = ($this->s__es_admin) ? $filtro : array_merge($filtro,$cond_propiedad);
		$cuadro->set_datos(
			toba::consulta_php('co_proyectos')->get_proyectos($filtro)
		);
		
	}

	function evt__cu_proyectos__seleccion($seleccion)
	{
		$this->s__seleccion = $seleccion;
		$this->set_pantalla('pant_seleccion_instancia');
	}

	//Se define como vinculo en toba_editor (en el evento mismo)
	function evt__cu_proyectos__informes($seleccion)
	{
		
	}
	

	// se muestra el evento solamente cuando existe anexo del proyecto
	function conf_evt__cu_proyectos__ver_anexo(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		$anexo = toba::consulta_php('helper_archivos')->ruta_base().'proyectos/'.$params[1].'/anexo.pdf';
		if(file_exists($anexo)){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	// se muestra el evento solamente cuando el proyecto está cerrado
	function conf_evt__cu_proyectos__ver_reporte(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		$estado = toba::consulta_php('co_proyectos')->get_estado_proyecto($params[0]);
		//var_dump($estado);
		if($estado == 'C'){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	function evt__cu_proyectos__editar($seleccion)
	{
		$this->dep('ci_detalles_proyecto')->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_detalles_proyecto');
	}
	function conf_evt__cu_proyectos__editar(toba_evento_usuario $evento,$fila)
	{ 
		if($this->s__es_admin){
			$evento->mostrar();
			return;
		}
		$claves = explode('||',$evento->get_parametros());
		$id_proyecto = $claves[0];
		$codigo = $claves[1];
		
		
		$resultado = toba::consulta_php('co_proyectos')->get_campo(array('convocatoria_anio'),array('id'=>$id_proyecto));
		if($resultado[0]['convocatoria_anio'] == date('Y')){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}
	
	function conf_evt__cu_proyectos__abrir_solicitud(toba_evento_usuario $evento,$fila)
	{ 
		$claves = explode('||',$evento->get_parametros());
		$estado = toba::consulta_php('co_proyectos')->get_estado_proyecto($claves[0]);
		if($estado === 'C' && in_array('admin',toba::usuario()->get_perfiles_funcionales())){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	function conf_evt__cu_proyectos__informes(toba_evento_usuario $evento,$fila)
	{ 
		$claves = explode('||',$evento->get_parametros());
		$tipo = toba::consulta_php('co_proyectos')->get_tipo_proyecto($claves[0]);
		if(in_array($tipo, array('0','D')) ){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}
	}

	// http://cvar
	function evt__cu_proyectos__abrir_solicitud($seleccion)
	{
		$this->dep('ci_detalles_proyecto')->get_datos()->cargar($seleccion);
		$this->dep('ci_detalles_proyecto')->get_datos('proyectos')->set(array('estado'=>'A'));
		$this->dep('ci_detalles_proyecto')->get_datos()->sincronizar();
		$this->dep('ci_detalles_proyecto')->get_datos()->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- cu_instancias ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_instancias(sap_ei_cuadro $cuadro)
	{
		$r = toba::consulta_php('co_proyectos')->presentaciones_evaluacion_proyecto($this->s__seleccion['id']);
		//ei_arbol($r);
		$cuadro->set_datos($r);
	}

	function evt__cu_instancias__seleccion($seleccion)
	{
		$evaluaciones = toba::consulta_php('co_proyectos')->get_detalle_evaluaciones_realizadas($seleccion);

		if($evaluaciones){
			$this->s__evaluaciones = $evaluaciones;
			$this->set_pantalla('pant_informe_evaluacion');
		}else{
			toba::notificacion()->agregar('No se registraron evaluaciones para la instancia seleccionada.','info');
		}
	}


	//NO SE PORQUE EL EVENTO SE CAPTURA ACÁ, EN EL CONTROLADOR (DEBERÍA ATRAPARSE EN EL CI HIJO)
	function servicio__imprimir_formulario()
	{
		$proyecto = $this->dep('ci_detalles_proyecto')->get_datos('proyectos')->get();
		$datos = toba::consulta_php('co_proyectos')->get_detalles_proyecto($proyecto['id']);
		$reporte = new Formulario_proyecto($datos);
		$reporte->mostrar();
	}

	// SERVICIO QUE GENERA EL PDF DEL FORMULARIO DEL PROYECTO
	function servicio__ver_reporte()
	{
		$seleccion = toba::memoria()->get_parametros();
		$datos = toba::consulta_php('co_proyectos')->get_reporte_proyecto($seleccion['id']);
		$reporte_proyecto = new Reporte_proyecto($datos);

		$reporte_proyecto->mostrar();

	}
	// SERVICIO QUE MUESTRA EL ANEXO DEL PROYECTO SUBIDO POR EL USUARIO
	function servicio__ver_anexo()
	{
		$params = toba::memoria()->get_parametros();
		$url_archivo = '/documentos/proyectos/'.$params['codigo'].'/anexo.pdf';
		header("Location: ".$url_archivo);

	}
	


}
?>