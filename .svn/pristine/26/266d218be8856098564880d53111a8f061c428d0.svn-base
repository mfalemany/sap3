<?php 
	class GeneradorMultiple{
		protected $params;

		function __construct($params)
		{
			//Obtengo los parámetros recibidos
			$this->params = $params;

			//Me aseguro de haber recibido al menos un tipo de reporte
			if( ! isset($this->params['reporte'])) die('No se ha indicado ningun tipo de reporte a generar');
			
			switch ($this->params['reporte']) {
				case 'reporte_proyecto':
					$this->generar_reporte_proyecto();		
					break;
				default:
					die('El tipo de reporte recibido no se encuentra entre los disponibles');
					break;
			}
		}
		
		function generar_reporte_proyecto()
		{
			if( ! isset($this->params['id_proyecto'])) die('No se ha indicado un ID de Proyecto para generar su reporte');
			
			//Obtengo la ruta al directorio PHP del proyecto
			$ruta = toba::proyecto()->get_path_php();
			try {
				require_once($ruta . '/generadores_pdf/proyectos/reporte_proyecto.php');
			} catch (Exception $e) {
				die('No se ha podido cargar la libreria responsable de generar el reporte de proyectos.' . $e->getMessage());	
			}
			$proyecto = toba::consulta_php('co_proyectos')->get_reporte_proyecto($this->params['id_proyecto']);
			$proyecto['ver_completo'] = FALSE;
			$reporte = new Reporte_proyecto($proyecto);
			$reporte->mostrar();
		}
	}

	$generador_reporte = new GeneradorMultiple(toba::memoria()->get_parametros());
?>