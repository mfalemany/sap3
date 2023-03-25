<?php
class ci_orden_merito extends sap_ci
{
	protected $s__filtro;

	function conf()
	{

		if( ! $this->soy_admin()){
			$id_conv = toba::consulta_php('co_convocatoria_beca')->get_id_ultima_convocatoria(TRUE);
			$filtro = array('id_convocatoria'=>$id_conv);
			$this->s__filtro = (isset($this->s__filtro)) ? array_merge($this->s__filtro,$filtro) : $filtro;
		}
		
	}
	//-----------------------------------------------------------------------------------
	//---- cu_orden_merito --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cu_orden_merito(sap_ei_cuadro $cuadro)
	{
		$datos = toba::consulta_php('co_comision_asesora')->get_orden_merito($this->s__filtro);
		$cuadro->set_datos($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_filtro ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_filtro(sap_ei_formulario $form)
	{
		$filtro = (isset($this->s__filtro)) ? $this->s__filtro : array();
		$form->set_datos($filtro);
		if( ! $this->soy_admin()) $form->set_solo_lectura('id_convocatoria');
	}

	function evt__form_filtro__filtrar($datos)
	{
		$this->s__filtro = $datos;
	}

	function evt__form_filtro__cancelar()
	{
		unset($this->s__filtro);
	}

	function extender_objeto_js(){
		echo "
			var form = {$this->objeto_js}.dep('form_filtro');
			if (typeof form !== 'undefined') {
				
				toggle_subtipo_beca();
				
				// Escucho el cambio del tipo de beca, para saber si tengo que activar el campo 'cupo'
				form.ef('id_tipo_beca').cuando_cambia_valor('toggle_subtipo_beca()');
					
				
			};

			function toggle_subtipo_beca() {
				// Tipos de beca que tienen cupos (agregar al array se se agregan nuevos)
				requieren_filtro_cupo = ['11'];

				if (requieren_filtro_cupo.indexOf(form.ef('id_tipo_beca').get_estado()) !== -1) {
					form.ef('cupo_beca').mostrar();
				} else {
					form.ef('cupo_beca').set_estado('nopar');
					form.ef('cupo_beca').ocultar();
				}
			}
		";
	}

}
?>