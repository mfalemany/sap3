<?php
class cuadro_comunicaciones extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida )
	{
		$columnas = array(
			'titulo'          => array('clave' => 'titulo',          'titulo' => 'Titulo'),
			'mail'            => array('clave' => 'mail',            'titulo' =>'Mail Becario'),
			'mail_director'   => array('clave' => 'mail_director',   'titulo' =>'Mail Director'),
			'codirector'      => array('clave' => 'codirector',      'titulo' =>'Co-Director'),
			'mail_codirector' => array('clave' => 'mail_codirector', 'titulo' =>'Mail Co-Director')
		);
		$this->eliminar_columnas(['titulo_corto']);
		$this->agregar_columnas($columnas);
		parent::vista_excel($salida);
		
		
		//$this->generar_salida("excel", $salida);
	}
}

?>