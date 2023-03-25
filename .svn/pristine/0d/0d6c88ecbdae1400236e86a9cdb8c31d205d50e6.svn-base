<?php
class ci_becarios_externos extends sap_ci
{
	protected $informe_actual;
	protected $seleccionado;
	protected $proyecto;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf()
	{
		$this->informe_actual = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
		if($this->informe_actual['estado'] == 'C'){
			//$this->bloquear_edicion();
		}
		$this->proyecto = $this->controlador()->get_proyecto();
	}

	function conf__pant_seleccion(toba_ei_pantalla $pantalla)
	{
		$filtro = array('id_proyecto' => $this->informe_actual['id_proyecto']);
		
		$datos['becarios'] = toba::consulta_php('co_becas')->get_becarios($filtro);
		$datos['proyecto_codigo'] = $this->proyecto['codigo'];
		$template = $this->armar_template(__DIR__ . "/../templates/template_formacion_rrhh.php",$datos);
		$this->pantalla()->set_template($template);
	}


	//-----------------------------------------------------------------------------------
	//---- Eventos del CI ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__guardar()
	{
		try {
			//Asigno el campo que falta en la tabla pi_inf_trab_pub (conectando la tabla de informes y de publicaciones)
			$informe = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
			$this->get_datos('pdts_inf_becario_ext')->set(array('id_informe'=>$informe['id_informe']));
			
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			toba::notificacion()->info('Guardado con éxito!');			
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->error('No se pudo agregar: ' . $e->get_mensaje_motor());
		}
	}
	function evt__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}
	function evt__agregar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_edicion');
		
	}

	//-----------------------------------------------------------------------------------
	//---- cu_becarios_externos ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_becarios_externos(sap_ei_cuadro $cuadro)
	{
		$becarios = toba::consulta_php('co_proyectos_informes')->get_becarios_externos('pdts',array('id_informe'=>$this->informe_actual['id_informe']));
		foreach($becarios as &$becario){
			$unidad = ($becario['duracion'] > 1) ? ' años' : ' año';
			$becario['duracion_desc'] = sprintf("%s %s",$becario['duracion'],$unidad);
		}
		$cuadro->set_datos($becarios);
	}

	function evt__cu_becarios_externos__seleccion($seleccion)
	{
		$this->get_datos()->cargar($seleccion);
		$this->set_pantalla('pant_edicion');
	}

	function evt__cu_becarios_externos__borrar($seleccion)
	{
		$this->informe_actual = $this->controlador()->get_datos('proyectos_pdts_informe')->get();
		try {
			$this->get_datos()->cargar($seleccion);
			$this->get_datos('pdts_inf_becario_ext')->cargar(array('id_informe'=>$this->informe_actual['id_informe']));
			$this->get_datos()->eliminar_todo();
			$this->get_datos()->resetear();
			toba::notificacion()->info('Eliminado con éxito!');			
		} catch (toba_error_db $e) {
			toba::notificacion()->error('No se pudo eliminar: ' . $e->get_mensaje());
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_becario_externo ---------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_becario_externo(sap_ei_formulario $form)
	{

		$datos = $this->get_datos('sap_becario_externo')->get();
		if($datos){
			$form->set_datos($datos);
		}
	}

	function evt__form_becario_externo__modificacion($datos)
	{
		$this->get_datos('sap_becario_externo')->set($datos);
	}

		//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos_becarios')->tabla($tabla) : $this->dep('datos_becarios');
	}

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