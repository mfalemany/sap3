<?php
class ci_publicar_comunicacion extends sap_ci
{
protected $s__filtro;

	//-----------------------------------------------------------------------------------
	//---- cu_comunicacion --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_comunicacion(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		if (isset($this->s__filtro)){
			$id_convocatoria = ($this->s__filtro['convocatoria'] == "todas") ? NULL : $this->s__filtro['convocatoria'];
			$filtro = array(
				'evaluacion_id' => '5,7',
				'area_conocimiento_id' => $this->s__filtro['area_conocimiento_id'],
				'id_convocatoria' => $id_convocatoria,
				'estado_convocatoria' => 'P'
			);
			try {
				$datos = toba::consulta_php('co_comunicaciones')->get_comunicaciones_estado_eval($filtro);
				$cuadro->set_datos($datos);
			} catch (Exception $ex) {
				toba::notificacion()->agregar($ex->getMessage());
			}

		}
	}

	//-----------------------------------------------------------------------------------
	//---- frm_filtro -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__frm_filtro(sap_ei_formulario $form)
	{
      /* == Se obtienen todas las convocatorias == */
      $convocatorias = toba::consulta_php('co_convocatorias')->get_convocatorias_becarios();
      
      $opciones['todas'] = "Todas las convocatorias";
      foreach($convocatorias as $convocatoria){
        $opciones[$convocatoria['id']] = $convocatoria['nombre'];
      }

      $form->ef('convocatoria')->set_opciones(count($opciones) ? $opciones : array());
      /* ===================================================================== */
      
       if (isset($this->s__filtro)) {
            $form->set_datos($this->s__filtro);
      }
	}

	function evt__frm_filtro__filtrar($datos)
	{
              $this->s__filtro = $datos;
              unset($this->s__base);
	}

	function evt__frm_filtro__cancelar()
	{
             unset($this->s__filtro);
	}

	

	function servicio__imprimir()
	{
		$params = toba::memoria()->get_parametros();
		if( ! isset($params['id'])) die ('No se recibió un identificador de comunicacion para generar su reporte');
		
		$punto = toba::puntos_montaje()->get('proyecto');
		toba_cargador::cargar_clase_archivo($punto->get_id(), 'generadores_pdf/comunicaciones/formulario_comunicacion.php' , 'sap' );
		
		$comunicacion = toba::consulta_php('co_comunicaciones')->get_detalle_comunicacion($params['id']);
		$reporte = new Formulario_comunicacion($comunicacion);
		
		$reporte->mostrar();
	}

}
?>