<?php
class ci_carga_masiva_valores extends sap_ci
{
	protected $filtro;
	protected $s__valores;
	protected $s__registros_afectados;
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf()
	{

	}
	//-----------------------------------------------------------------------------------
	//---- Eventos del CI ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__aplicar()
	{
		//Esta linea limpia el array y mantiene solo aquellos elementos no nulos
		$this->s__valores = array_filter($this->s__valores);
		
		if ( ! (isset($this->s__valores) && count($this->s__valores))) {
			toba::notificacion()->info('No se establecieron nuevos valores para ning�n campo.');
			return;
		}
		$respuesta = toba::consulta_php('co_becas')->actualizar_campos($this->s__registros_afectados,$this->s__valores);
		if($respuesta === TRUE){
			toba::notificacion()->info('Se actualizaron todos los registros con �xito');
		}else{
			toba::notificacion()->error('Ocurri� el siguiente error: ' . $respuesta);
		}
		unset($this->s__valores);
		unset($this->s__registros_afectados);
		unset($this->filtro);
		$this->set_pantalla('pant_seleccion');
	}

	function evt__volver()
	{
		unset($this->filtro);
		unset($this->s__valores);
		unset($s__registros_afectados);
		$this->set_pantalla('pant_seleccion');
	}
	//-----------------------------------------------------------------------------------
	//---- form_filtro ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__form_filtro(sap_ei_formulario $form)
	{
		if(isset($this->filtro)){
			$form->set_datos($this->filtro);
			$form->eliminar_evento('filtrar');
			$form->agregar_notificacion('Para cambiar el los criterios del filtro, por favor, utilice el bot�n "Limpiar" y luego establezca nuevos criterios');
		}
	}

	function evt__form_filtro__filtrar($datos)
	{
		$this->filtro = $datos;
		$this->set_pantalla('pant_edicion');
	}
	function evt__form_filtro__cancelar()
	{
		unset($this->filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- form_valores -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function evt__form_valores__modificacion($datos)
	{
		$this->s__valores = $datos;
	}

	//-----------------------------------------------------------------------------------
	//---- cu_registros_afectados -------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_registros_afectados(sap_ei_cuadro $cuadro)
	{
		if (!isset($this->s__registros_afectados)) {
			if(isset($this->filtro) && $this->filtro){
				$this->s__registros_afectados = toba::consulta_php('co_becas')->get_becas_otorgadas($this->filtro);
				$cuadro->set_datos($this->s__registros_afectados);	
			}else{
				$cuadro->set_titulo('Debe aplicar alg�n filtro para obtener datos');
			}
		} else {
			$cuadro->set_datos($this->s__registros_afectados);
		}
	}

	function evt__cu_registros_afectados__borrar($seleccion)
	{
		$this->s__registros_afectados = array_filter($this->s__registros_afectados, function($registro) use ($seleccion){
			return ! ( 
					($registro['id_convocatoria'] == $seleccion['id_convocatoria'] ) &&
					($registro['id_tipo_beca']    == $seleccion['id_tipo_beca']    ) && 
					($registro['nro_documento']   == $seleccion['nro_documento']   )
				);
		});
	}

}
?>