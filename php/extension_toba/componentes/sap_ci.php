<?php
class sap_ci extends toba_ci
{
	private $dias = array(0=>'domingo',1=>'lunes',2=>'martes',3=>'mi�rcoles',4=>'jueves',5=>'viernes',6=>'s�bado');
	private $meses = array(1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre');
	private $cat_incentivos = array(null=>'No categorizado',1=>'Categor�a I',2=>'Categor�a II',3=>'Categor�a III',4=>'Categor�a IV',5=>'Categor�a V');

	function ini()
	{
		spl_autoload_register('static::cargador_clases_propio');
	}

	static function cargador_clases_propio($clase)
	{
		$rutas   = ['negocio', 'consultas', 'generadores_pdf/grupos', 'generadores_pdf/proyectos', 'generadores_pdf/comunicaciones', 'generadores_pdf/becas'];
		$php_dir = toba::proyecto()->get_path_php();
		
		foreach ($rutas as $ruta) {
			$ruta_clase = sprintf('%s/%s/%s.php', $php_dir, $ruta, $clase);
			
			if (file_exists($ruta_clase)) {
				require_once($ruta_clase); 
				return;
			}
			
		}
	}

	private function recorrer_datos(&$template, $datos)
	{
		foreach($datos as $clave => $valor){
			if(is_array($valor)){
				$this->recorrer_datos($template,$valor);
			}else{
				$template = str_replace('{{'.strtoupper($clave).'}}',$valor,$template);	
			}
		}
	}
	/**
	 * =============================================================================
	 * ELIMINAR HASTA ACA 
	 * =============================================================================
	 */

	function es_pdf($detalles_archivo)
	{
		return (mime_content_type($detalles_archivo['tmp_name']) == 'application/pdf');
	}

	//ESTA ES LA FUNCI�N QUE TIENE QUE QUEDAR
	function armar_template($archivo,$datos)
	{	
		ob_start();
		include $archivo;
		return ob_get_clean();
	}

	protected function get_dia($dia)
	{
		return $this->dias[$dia];
	}
	protected function get_mes($mes)
	{
		return $this->meses[$mes];
	}

	protected function get_meses()
	{
		return $this->meses;
	}
	protected function get_mes_desc($mes)
	{
		return $this->meses[intval($mes)];
	}
	protected function get_fecha_texto($time)
	{
		$dia = $this->get_dia(date('N',$time));
		$mes = $this->get_mes(date('n',$time));
		return $dia.' '.date('d').' de '.$mes.' de '.date('Y');
	}

	protected function fecha_dmy($fecha_ymd)
	{
		$fecha = new Datetime($fecha_ymd);
		return $fecha->format('d-m-Y');
	}

	protected function soy_admin()
	{
		//Perfiles admintidos como "Administrador"
		$perfiles_administrador = array('admin','admin_limitado');
		//Perfiles que tiene asignados el usuario actualmente logueado
		$perfiles_usuario = toba::usuario()->get_perfiles_funcionales();
		//Si tiene al menos uno de los perfiles de administrador, se retorna TRUE
		foreach ($perfiles_administrador as $perfil){
			if(in_array($perfil,$perfiles_usuario)){
				return TRUE;
			}
		}
		return FALSE;
	}


	function calcular_edad($fechanacimiento){
		list($anio,$mes,$dia) = explode("-",$fechanacimiento);
		$anio_diferencia  = date("Y") - $anio;
		$mes_diferencia = date("m") - $mes;
		$dia_diferencia   = date("d") - $dia;
		if ($dia_diferencia < 0 || $mes_diferencia < 0)
		$anio_diferencia--;
		return $anio_diferencia;
	}

	function no_cache()
	{
		header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	/**
	 * Convierte el campo 'custom_params' (originalmente un json) en un array, y lo retorna. 
	 * DEVUELVE SOLO LOS CUSTOMS PARAMS (como array)
	 * @param  array  $registro Array de una dimension (un registro que tenga el campo 'custom_params')
	 * @return array            Array de custom_params
	 */
	protected function get_custom_params($registro)
	{
		if (!isset($registro['custom_params'])) {
			return [];
		}

		return json_decode($registro['custom_params'], true);
	}

	/**
	 * Devuelve el registro recibido como par�metro, pero le suma al array los customs params. 
	 * DEVUELVE EL REGISTRO ORIGINAL CON LOS CUSTOMS PARAMS INCORPORADOS COMO INDICES
	 * @param  array  $registro Array de una dimension (un registro que tenga el campo 'custom_params')
	 * @return array            Array de custom_params
	 */
	protected function extract_custom_params($registro)
	{
		if (!isset($registro['custom_params'])) {
			return $registro;
		}

		$custom_params = json_decode($registro['custom_params'], true);
		return array_merge($registro, $custom_params);
	}

	public function log($mensaje, $prefix = '')
	{
		toba::consulta_php('helper_archivos')->log($mensaje, $prefix);
	}




	
}
?>