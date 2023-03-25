<?php
class ci_evaluacion_seleccion extends sap_ci
{
	protected $s__filtro_nuevos;
	protected $s__filtro_informes;

	//-----------------------------------------------------------------------------------
	//---- filtro_informes---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_informes(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro_informes)){
			$form->set_datos($this->s__filtro_informes);
		}
	}

	function evt__filtro_informes__filtrar($datos)
	{
		$this->s__filtro_informes = $datos;
	}

	function evt__filtro_informes__cancelar()
	{
		unset($this->s__filtro_informes);
	}
	//-----------------------------------------------------------------------------------
	//---- cu_informes ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_informes(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$this->s__filtro_informes['tiempo_inicio'] = 'anteriores';
		
		if(!in_array('admin',toba::usuario()->get_perfiles_funcionales())){
			$this->s__filtro_informes['usuario'] = toba::usuario()->get_id();	
		}
		
		$cuadro->set_datos(toba::consulta_php('co_proyectos')->get_proyectos_evaluar($this->s__filtro_informes));	
		
	}

	function evt__cu_informes__seleccion($seleccion)
	{
		$this->direccionar_evento($seleccion,'informe');
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_nuevos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_nuevos(sap_ei_formulario $form)
	{
		if(isset($this->s__filtro_nuevos)){
			$form->set_datos($this->s__filtro_nuevos);
		}
	}

	function evt__filtro_nuevos__filtrar($datos)
	{
		$this->s__filtro_nuevos = $datos;
	}

	function evt__filtro_nuevos__cancelar()
	{
		unset($this->s__filtro_nuevos);
	}

	//-----------------------------------------------------------------------------------
	//---- cu_nuevos --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__cu_nuevos(sap_ei_cuadro $cuadro)
	{
		$cuadro->desactivar_modo_clave_segura();
		$this->s__filtro_nuevos['tiempo_inicio'] = 'nuevos';
		if(!in_array('admin',toba::usuario()->get_perfiles_funcionales())){
			$this->s__filtro_nuevos['usuario'] = toba::usuario()->get_id();	
		}
		$cuadro->set_datos(toba::consulta_php('co_proyectos')->get_proyectos_evaluar($this->s__filtro_nuevos));
	}

	function evt__cu_nuevos__seleccion($seleccion)
	{
		$this->direccionar_evento($seleccion,'nuevo');
	}

	function conf_evt__cu_nuevos__ver_anexo(toba_evento_usuario $evento, $fila)
	{
		$params = explode('||',$evento->get_parametros());
		$anexo = toba::consulta_php('helper_archivos')->ruta_base().'proyectos/'.$params[1].'/anexo.pdf';
		if(file_exists($anexo)){
			$evento->mostrar();
		}else{
			$evento->ocultar();
		}

	}	
	

	function direccionar_evento($seleccion,$etapa){
		//es necesario recibir el tipo de proyecto para redirigir a la pantalla adecuada de evaluaci?
		if(!isset($seleccion['tipo'])){
			toba::notificacion()->agregar("No se puede determinar el tipo de proyecto seleccionado.");
			return;
		}
		$this->controlador()->set_pantalla('pant_edicion');
		switch ($seleccion['tipo']) {
			case '0':

				$this->datos('proyectos')->cargar(array('id'=>$seleccion['id']));
				$this->seleccionar_pantalla('pi',$etapa);
				break;
			case 'D':
				$this->datos('proyectos')->cargar(array('id'=>$seleccion['id']));
				$this->seleccionar_pantalla('pdts',$etapa);
				break;
			case 'C':
				$this->datos('programas')->cargar(array('codigo'=>$seleccion['codigo']));
				$this->seleccionar_pantalla('programa',$etapa);
				break;
		}
	}
	
	function seleccionar_pantalla($tipo,$etapa){
		if($etapa == 'nuevo'){
			$this->controlador()->dep('ci_evaluacion_proyectos')->set_pantalla('pant_eval_'.$tipo.'_nuevo');
		}else{
			$this->controlador()->dep('ci_evaluacion_proyectos')->set_pantalla('pant_seleccion_informe');
		}
	}

	function datos($relacion,$tabla = NULL)
	{
		$dr = $this->controlador()->dep('ci_evaluacion_proyectos')->dep($relacion);
		return ($tabla) ? $dr->tabla($tabla) : $dr;
	}

	

}
?>