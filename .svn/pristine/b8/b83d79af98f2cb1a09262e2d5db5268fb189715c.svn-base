<?php
class ci_convocatorias extends sap_ci
{
	protected $s__datos_filtro;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(sap_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}

	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(sap_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos(toba::consulta_php('co_convocatoria_beca')->get_convocatorias($this->s__datos_filtro,false));
		} else {
			$cuadro->set_datos(toba::consulta_php('co_convocatoria_beca')->get_convocatorias(array(),false));
		}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(sap_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$datos = $this->dep('datos')->tabla('convocatoria_beca')->get();
			
			$form->set_datos($datos);
			$form->set_solo_lectura(array('id_tipo_convocatoria'));
		} else {
			$this->pantalla()->eliminar_evento('eliminar');
		}
	}

	function evt__formulario__modificacion($datos)
	{
		$errores = $this->validar($datos);
		if(count($errores)){
			$errores = implode('/n',$errores);
			throw new toba_error('Ocurrieron los siguientes erores: '.$errores,'','Ocurrieron algunos errores');
		}else{
			$this->dep('datos')->tabla('convocatoria_beca')->set($datos);	
		}
		
	}

	//---- ml_requisitos -------------------------------------------------------------------

	function conf__ml_requisitos(sap_ei_formulario_ml $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('requisitos_convocatoria')->get_filas());

			$datos = $this->dep('datos')->tabla('convocatoria_beca')->get(); 
			if(toba::consulta_php('co_convocatoria_beca')->existen_inscripciones(
				$datos['id_convocatoria']
			)){
				$form->agregar_notificacion('No se pueden realizar modificaciones a los requisitos porque ya existen inscripciones registradas a esta convocatoria','warning');
				$form->set_solo_lectura();
				$form->desactivar_agregado_filas();
			}
		}
	}	

	function evt__ml_requisitos__modificacion($datos)
	{
		$this->dep('datos')->tabla('requisitos_convocatoria')->procesar_filas($datos);	
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->resetear();
	}

	function evt__eliminar()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__guardar()
	{
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	private function validar($datos)
	{
		extract($datos);
		$errores = array();
		if($fecha_desde >= $fecha_hasta){
			$errores[] = 'El campo \'Fecha Desde\' debe ser menor que el campo \'Fecha Hasta\'';
		}
		if($fecha_hasta > $limite_movimientos){
			$errores[] = 'El campo \'Limite de Movimientos\' debe ser mayor o igual que el campo \'Fecha Hasta\'';
		}
		return $errores;
		
	}

}

?>