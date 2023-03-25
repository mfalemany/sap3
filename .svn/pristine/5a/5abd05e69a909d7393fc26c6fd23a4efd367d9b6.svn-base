<?php
class ci_informes extends sap_ci
{
	protected $s__proyecto;
	protected $carga_habilitada;
	
	//-----------------------------------------------------------------------------------
	//---- Configuraci? del CI ---------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function conf()
	{
		//Determina si est? "abierta" la carga de informes (se configura en tablas b?icas)
		$permite_carga = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('carga_informes_proyecto');
		if($permite_carga === FALSE) throw new toba_error('No se defini? el par?etro \'carga_informes_proyecto\' en la configuraci? del sistema. Por favor, definalo y asigne unos de los siguientes valores: "S" o "N".');
		
		//Par?etro cargado en tablas b?icas que indica si esta "abierta la convocatoria" a informes
		if($permite_carga == 'N'){
			if($this->pantalla()->existe_evento('nuevo_informe')){
				if( ! $this->soy_admin()){
					$this->pantalla()->eliminar_evento('nuevo_informe');
				}else{
					$this->agregar_notificacion('Se puede agregar un nuevo informe solo porque el usuario es administrador','info');
				}
			}
		}
		
		//En la primera ejecuci?, el ID de proyecto viene como parametro del evento vinculador. Luego, se mantiene en sesi? (y el parametro del evento llega vac?)
		if( ! isset($this->s__proyecto)){
			$params = toba::memoria()->get_parametros();
			if(isset($params['id'])){
				$this->s__proyecto = toba::consulta_php('co_proyectos')->get_detalles_proyecto($params['id']);
			}else{
				//En este punto, no se recibi? un ID de proyecto, pero pudo haberse recibido un ID de informe (para generar el PDF por ejemplo)
				if( ! isset($params['id_informe']) ){
					throw new toba_error("No se ha seleccionado un proyecto para gestionar sus informes");
				}
			}	
		}
		if( ! isset($this->s__proyecto['id'])) return;
		/* ======================================================================== */
		//   Determino si permito o no agregar un nuevo informe para este proyecto
		/* ======================================================================== */
		if(toba::consulta_php('co_proyectos_informes')->existe_informe_abierto($this->s__proyecto['id'])){
			if($this->pantalla()->existe_evento('nuevo_informe') && !$this->soy_admin()){
				$this->pantalla()->eliminar_evento('nuevo_informe');
			}
		}
		//Calcula la antiguedad del proyecto, y teniendo en cuenta el a? actual, determina si es posible presentar un informe
		if( ! $this->puede_presentar_informe()){
			if($this->pantalla()->existe_evento('nuevo_informe')){
				if( ! $this->soy_admin()){
					$this->pantalla()->eliminar_evento('nuevo_informe');
				}else{
					$this->agregar_notificacion('Se puede agregar un nuevo informe solo porque el usuario es administrador','info');
				}

			}
		}
		/* ======================================================================== */
		
	}

	function evt__nuevo_informe()
	{
		
		$datos_iniciales = array(
			'id_proyecto' => $this->s__proyecto['id'],
			'estado'      => 'A'
		);

		//Determino si es un nuevo informe de PI, PDTS o Programa
		switch ($this->s__proyecto['tipo']) {
			case '0':
				$this->get_datos_pi()->resetear();
				$this->get_datos_pi('proyectos')->cargar(array('id' => $this->s__proyecto['id']));
				$this->get_datos_pi('proyectos_pi_informe')->set($datos_iniciales);
				$this->set_pantalla('pant_edicion_pi');
				break;
			case 'D':
				$this->get_datos_pdts()->resetear();
				$this->get_datos_pdts('proyectos')->cargar(array('id' => $this->s__proyecto['id']));
				$this->get_datos_pdts('proyectos_pdts_informe')->set($datos_iniciales);
				$this->set_pantalla('pant_edicion_pdts');
				break;
			default:
				toba::notificacion()->warning('El tipo de proyecto seleecionado no admite presentaci? de informes');
		}
	}


	//-----------------------------------------------------------------------------------
	//---- cu_informes ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_informes(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		if( ! isset($this->s__proyecto['id'])) return;
		$filtro = array('id_proyecto'=>$this->s__proyecto['id']);
		$tipos = array('0' => 'pi','D' => 'pdts');
		$tipo = $tipos[$this->s__proyecto['tipo']];
		$cuadro->set_datos(toba::consulta_php('co_proyectos_informes')->get_informes($tipo, $filtro));
	}

	function evt__cu_informes__seleccion($seleccion)
	{
		//Determino si es un nuevo informe de PI, PDTS o Programa
		switch ($this->s__proyecto['tipo']) {
			case '0':
				$this->cargar_dr_pi($seleccion);
				$this->set_pantalla('pant_edicion_pi');
				break;
			case 'D':
				$this->cargar_dr_pdts($seleccion);
				$this->set_pantalla('pant_edicion_pdts');
				break;
		}

	}

	function conf_evt__cu_informes__seleccion(toba_evento_usuario $evento, $fila)
	{
		if( ! isset($this->s__proyecto['tipo'])) return;
		$tipos = array('0'=>'pi','D'=>'pdts');
		$tipo = $tipos[$this->s__proyecto['tipo']];
		$params = explode('||',$evento->get_parametros());
		$informe = toba::consulta_php('co_proyectos_informes')->get_detalles_informe($tipo,$params[0]);
		if(isset($informe['fecha_presentacion']) && $informe['fecha_presentacion'] < '2021-06-01'){
			if( ! $this->soy_admin()){
				$evento->ocultar();
			}else{
				$evento->mostrar();	
			}
		}else{
			$evento->mostrar();
		}
	}

	function evt__cu_informes__abrir($seleccion)
	{
		if( ! isset($this->s__proyecto['tipo'])) return;
		$tipos = array('0'=>'pi','D'=>'pdts');
		$tipo = $tipos[$this->s__proyecto['tipo']];
		toba::consulta_php('co_proyectos_informes')->marcar_abierto($tipo,$seleccion['id_informe']);
	}

	function conf_evt__cu_informes__abrir(toba_evento_usuario $evento, $fila)
	{
		if( ! $this->soy_admin()){
			$evento->ocultar();
		}else{
			$params = explode('||',$evento->get_parametros());
			//params[1] contiene 'A' o 'C' (estado abierto o cerrado del informe)
			if($params[1] == 'C'){
				$evento->mostrar();
			}else{
				$evento->ocultar();	
			}
		}
	}

	function conf_evt__cu_informes__ver_pdf(toba_evento_usuario $evento, $fila)
	{

		if($this->soy_admin()){
			$evento->mostrar();
		}else{
			//params[1] contiene 'A' o 'C' (estado abierto o cerrado del informe)
			$params = explode('||',$evento->get_parametros());
			if($params[1] == 'C'){
				$evento->mostrar();
			}else{
				$evento->ocultar();	
			}
		}
	}

	//-----------------------------------------------------------------------------------
	//---- AUXILIARES -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	protected function get_datos($datos_relacion, $tabla = NULL)
	{
		return ($tabla) ? $this->dep($datos_relacion)->tabla($tabla) : $this->dep($datos_relacion);
	}

	function get_datos_pi($tabla = NULL)
	{
		return $this->get_datos('datos_pi',$tabla);
	}

	function get_datos_pdts($tabla = NULL)
	{
		return $this->get_datos('datos_pdts',$tabla);
	}

	/**
	 * Retorna la variable de instancia "$this->s__proyecto". Se utiliza como getter para clases de controladores hijos
	 */
	function get_proyecto()
	{
		return $this->s__proyecto;
	}

	
	function cargar_dr_pi($criterio)
	{
		$this->get_datos_pi('proyectos')->cargar(array('id' => $this->s__proyecto['id']));
		//Esto es porque $criterio trae todas las claves del cuadro (y solo se admite el ID informe para cargar el DT)
		$criterio = array('id_informe' => $criterio['id_informe']);

		$this->get_datos_pi('proyectos_pi_informe')->cargar($criterio);
		$evaluaciones = toba::consulta_php('co_proyectos_informes')->get_evaluaciones($criterio['id_informe'],'pi');
		$this->get_datos_pi('sap_proyecto_integrante_eval')->cargar_con_datos($evaluaciones);
	}


	function cargar_dr_pdts($criterio)
	{
		$this->get_datos_pdts('proyectos')->cargar(array('id' => $this->s__proyecto['id']));
		//Esto es porque $criterio trae todas las claves del cuadro (y solo se admite el ID informe para cargar el DT)
		$criterio = array('id_informe' => $criterio['id_informe']);
		$this->get_datos_pdts('proyectos_pdts_informe')->cargar($criterio);
		$evaluaciones = toba::consulta_php('co_proyectos_informes')->get_evaluaciones($criterio['id_informe'],'pdts');
		$this->get_datos_pdts('sap_proyecto_integrante_eval')->cargar_con_datos($evaluaciones);
	}

	function puede_presentar_informe()
	{
		$desde = (new Datetime($this->s__proyecto['fecha_desde']))->format('Y');
		$hasta = (new Datetime($this->s__proyecto['fecha_hasta']))->format('Y');
		$hoy = (new Datetime())->format('Y');
		$duracion = $hasta - $desde + 1;

		//El momento actual debe ser: al menos un a? posterior al inicio del proyecto, y como mucho un a? posterior a la fecha de fin
		return ( ($hoy - $desde) >= 1 &&  ($hoy - $desde) <= $duracion);
	}

	//-----------------------------------------------------------------------------------
	//---- SERVICIOS --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------	
	function servicio__ver_pdf()
	{
		$params = toba::memoria()->get_parametros();

		if( ! isset($params['id_informe'])) die ('No se recibi? un ID de informe para generar su reporte');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/proyectos/informes/reporte_informe_proyecto.php' , 'sap' );
		
		//Detalles del informe
		$informe = toba::consulta_php('co_proyectos_informes')->get_reporte_informe($params['tipo_proyecto'], $params['id_informe']);

		$reporte = new Reporte_informe_proyecto($informe);
		
		$reporte->mostrar();
	}



}
?>