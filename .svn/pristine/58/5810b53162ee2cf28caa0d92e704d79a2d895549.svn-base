<?php
class ci_otorgamiento extends sap_ci
{
	protected $s__filtro;
	
	


	//-----------------------------------------------------------------------------------
	//---- ml_postulantes ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_postulantes(sap_ei_formulario_ml $form_ml)
	{
	
		$form_ml->agregar_notificacion('Los cambios se aplicarán solo a los registros que tengan la casilla \'Seleccionado\' activada','info');

		$filtro = array_merge(array('beca_otorgada' => 'N'), $this->s__filtro);
		//Si la distribución de las becas es por facultad, los resultados se muestran ordenados de esa manera
		if($filtro['distribucion'] == 'F'){
			$filtro['campos_ordenacion'] = array('id_dependencia'=>'desc','puntaje_final'=>'desc');
		}

		unset($filtro['cantidad_becas']);
		$postulantes = toba::consulta_php('co_comision_asesora')->get_orden_merito($filtro);

		$primeros = array();
						
		foreach($postulantes as $fila => $postulante){
			if($filtro['distribucion'] == 'F'){
				$lugar = $postulante['lugar'];
				
				//Se calculan los primeros N por facultad (se calcula solo una vez)
				if(! isset($primeros[$lugar])){
					$primeros[$lugar] = array_filter($postulantes,function($p) use ($lugar){
						return ($p['lugar'] == $lugar);
					});
					$primeros[$lugar] = array_slice($primeros[$lugar],0,$this->s__filtro['cantidad_becas']);
				}
				//Si está entre los primeros N, se lo marca como seleccionado
				if(in_array($postulante['nro_documento'],array_column($primeros[$lugar],'nro_documento'))){
					$postulante['seleccionado'] = 1;	
				}
			}else{
				$primeros = array_slice($postulantes,0,$this->s__filtro['cantidad_becas']);	
				//Lo mismo, pero en un ranking general (cuando no es por facultad)
				if(in_array($postulante['nro_documento'],array_column($primeros,'nro_documento'))){
					$postulante['seleccionado'] = 1;	
				}
			}
			$form_ml->agregar_registro($postulante);	
			//$datos[] = $postulante;

		}
		//$form_ml->set_datos($datos);
		//$this->get_datos('becas_otorgadas')->cargar_con_datos($datos);

	}

	function evt__ml_postulantes__modificacion($datos)
	{
		$becariosAAgregar = [];

		foreach($datos as $clave => $postulante){
			if($datos[$clave]['seleccionado'] && $datos[$clave]['fecha_desde'] && $datos[$clave]['fecha_hasta']){
				$becariosAAgregar[$clave] = [
					'nro_documento'         => $postulante['nro_documento'],
					'id_convocatoria'       => $this->s__filtro['id_convocatoria'],
					'id_tipo_beca'          => $this->s__filtro['id_tipo_beca'],
					'fecha_desde'           => $postulante['fecha_desde'],
					'fecha_hasta'           => $postulante['fecha_hasta'],
					'estado'                => 'A',
					'apex_ei_analisis_fila' => 'A',
				];
			}
		}

		if (count($becariosAAgregar)) {
			$this->get_datos('becas_otorgadas')->procesar_filas($becariosAAgregar);
		} else {
			throw new toba_error('No hay becarios seleccionados para otorgamiento');
		}
		
	}

	//-----------------------------------------------------------------------------------
	//---- form_filtro ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_filtro(sap_ei_formulario $form)
	{	
		if(isset($this->s__filtro)){
			$form->set_datos($this->s__filtro);
		}
	}

	function evt__form_filtro__filtrar($datos)
	{
		$this->s__filtro = $datos;
		$this->set_pantalla('pant_otorgamiento');
	}
	function evt__form_filtro__cancelar()
	{
		unset($this->s__filtro);
	}


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__guardar()
	{
		try {
			$this->get_datos()->sincronizar();
			$this->get_datos()->resetear();
			toba::notificacion()->info('Se han guardado correctamente las becas otorgadas');
			$this->set_pantalla('pant_seleccion');
		} catch (toba_error_db $e) {
			toba::notificacion()->warning('Ocurrió el siguiente error: ' . $e->get_mensaje());			
		} catch (Exception $e) {
			toba::notificacion()->warning('Ocurrió el siguiente error: ' . $e->getMessage());
		}
	}

	function evt__cancelar()
	{
		$this->get_datos()->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function get_datos($tabla = NULL)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}


}
?>