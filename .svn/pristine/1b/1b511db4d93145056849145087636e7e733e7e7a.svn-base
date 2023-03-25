<?php
class cu_orden_merito extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida){
		$columnas = array(
			'cuil'     => array('clave'    => 'cuil'    , 'titulo'=>'CUIL'),
			'director' => array('clave'    => 'director', 'titulo'=>'Director')
		);
		$this->agregar_columnas($columnas);
		parent::vista_excel($salida);
		
	}
}

?>