<?php 
class cu_informes_estado extends sap_ei_cuadro
{
	function vista_excel(toba_vista_excel $salida)
	{
		$salida->set_nombre_archivo('Reporte de Informes de Becas.xls');
		$this->agregar_columnas(array(
			'mail_postulante'  => array('clave' => 'mail_postulante', 'titulo' => 'Mail Postulante'),
			'director'         => array('clave' => 'director',        'titulo' => 'Director'),
			'mail_director'    => array('clave' => 'mail_director',   'titulo' => 'Mail Director')
								));
		parent::vista_excel($salida);
	}
}

 ?>