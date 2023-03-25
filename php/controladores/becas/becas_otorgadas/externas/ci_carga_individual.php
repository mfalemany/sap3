<?php
class ci_carga_individual extends sap_ci
{
	protected $s__datos;

	//-----------------------------------------------------------------------------------
	//---- form_beca_externa ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_beca_externa(sap_ei_formulario $form)
	{
		if(isset($this->s__datos)){
			$form->set_datos($this->s__datos);
		}

	}

	function evt__form_beca_externa__guardar($datos)
	{
		$this->s__datos = $datos;
		$inscripcion = array(
			'id_convocatoria'      => $datos['id_convocatoria'],
			'id_tipo_beca'         => $datos['id_tipo_beca'],
			'nro_documento'        => $datos['nro_documento'],
			'nro_documento_dir'    => $datos['nro_documento_dir'],
			'nro_documento_codir'  => $datos['nro_documento_codir'],
			'nro_documento_subdir' => $datos['nro_documento_subdir'],
			'id_dependencia'       => $datos['id_dependencia'],
			'es_titular'           => $datos['es_titular'],
			'id_proyecto'          => $datos['id_proyecto'],
			'titulo_plan_beca'     => $datos['titulo_plan_beca'],
			'admisible'            => 'S',
			'estado'               => 'A',
		);
		$beca = array(
			'id_convocatoria'     => $datos['id_convocatoria'],
			'id_tipo_beca'        => $datos['id_tipo_beca'],
			'nro_documento'       => $datos['nro_documento'],
			'fecha_desde'         => $datos['fecha_desde'],
			'fecha_hasta'         => $datos['fecha_hasta'],
			'fecha_toma_posesion' => $datos['fecha_desde'],
			'nro_resol'           => $datos['nro_resol']
		);
		try {
			$this->dep('datos')->tabla('inscripcion_conv_beca')->set($inscripcion);
			$this->dep('datos')->tabla('becas_otorgadas')->set($beca);
			$this->dep('datos')->sincronizar();
			unset($this->s__datos);
			toba::notificacion()->agregar('Se ha otorgado la beca!','info');
			$this->dep('datos')->resetear();
		} catch (Exception $e) {
			toba::notificacion()->agregar('Ocurrió el siguiente error: '.$e->getMessage());
		}
	}

	function extender_objeto_js(){
		echo "
			form = {$this->dep('form_beca_externa')->objeto_js};
			form.evt__validar_datos = function(){
				console.log(Date.parse(form.ef('fecha_desde').get_estado()), Date.parse(form.ef('fecha_hasta').get_estado()))
				if(Date.parse(form.ef('fecha_desde').get_estado()) > Date.parse(form.ef('fecha_hasta').get_estado())){
					alert('La fecha hasta debe ser mayor o igual a la fecha desde');
					return false;
				}
				return true;
				
			}
		";
	}
}

?>