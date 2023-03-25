<?php
class ci_calificacion_transitoria extends sap_ci
{
	const CATEGORIAS = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV'];

	protected $s__categoria;
	protected $s__llamado;
	protected $s__contenido_ml;

	function conf()
	{
		
	}
	//-----------------------------------------------------------------------------------
	//---- CONF PANTALLAS ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	public function conf__pant_seleccion_categoria(toba_ei_pantalla $pantalla)
	{
		$solicitud = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos(toba::usuario()->get_id(), 2);
		$requisitos = null;

		if ($solicitud) {
			$form = $this->dep('form_calificacion');
			$form->set_solo_lectura();
			$form->eliminar_evento('solicitar');
			$form->agregar_notificacion('Usted ha registrado una solicitud de Categoría ' . self::CATEGORIAS[$solicitud['categoria']] . ' para esta convocatoria');
			
			$requisitos = toba::consulta_php('co_becas')->get_solicitud_transitoria_incentivos_documentacion($solicitud['categoria'], $solicitud['id_llamado']);
		}

		$template = $this->armar_template(__DIR__ . '/template_pant_seleccion_categoria.php', ['solicitud' => $solicitud, 'requisitos' => $requisitos]);
		$pantalla->set_template($template);
	}

	public function conf__pant_documentacion(toba_ei_pantalla $pantalla)
	{
		$template = $this->armar_template(__DIR__ . '/template_pant_documentacion.php', []);
		$pantalla->set_template($template);
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos del CI ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	public function evt__guardar()
	{

	}

	public function evt__cancelar()
	{
		unset($this->s__contenido_ml);
		$this->set_pantalla('pant_seleccion_categoria');
	}

	//-----------------------------------------------------------------------------------
	//---- form_calificacion ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_calificacion(sap_ei_formulario $form)
	{
		

	}

	function evt__form_calificacion__solicitar($datos)
	{
		$this->s__categoria  = $datos['categoria'];
		$this->s__llamado    = $datos['id_llamado'];
		$this->set_pantalla('pant_documentacion');
	}

	//-----------------------------------------------------------------------------------
	//---- ml_documentacion -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__ml_documentacion(sap_ei_formulario_ml $form_ml)
	{
		// Cuando hay un error, esta variable se guarda para que el usuario no pierda 
		// el estado de todo lo que cargó
		if (!empty($this->s__contenido_ml)) {
			// Si hay un estado anterior, se toma como base (para seguir con la carga 
			// y corregir lo que haya estado mal)
			$form_ml->set_datos($this->s__contenido_ml);
			return;
		}

		// Si no hay estado anterior, se generan todas las lineas que debe cargar 
		// (en base a los requisitos cargados en la BD para la categoria seleccionada)

		if (!$this->s__categoria || empty($this->s__llamado)) {
			toba::notificacion()->error('No se ha seleccionado una categoría de incentivos, o no hay una convocatoria abierta.');
			$form_ml->set_solo_lectura();
			$this->pantalla()->eliminar_evento('guardar');
		}
		
		// Se deja el método en co_becas, pero obtiene los requisitos de cualquier convocatoria
		$requisitos = toba::consulta_php('co_becas')->get_requisitos_categoria_incentivos_transitoria($this->s__llamado, $this->s__categoria, true);
		
		foreach ($requisitos as $requisito) {
			$form_ml->agregar_registro([
				'id'         => $requisito['id'],
				'requisito'  => $requisito['requisito'],
				'documental' => $requisito['documental'],
			]);
		}
	}

	function evt__ml_documentacion__modificacion($datos)
	{
		$usuario    = toba::usuario()->get_id();
		$id_llamado = $this->s__llamado;
		$this->s__contenido_ml = $datos;

		$ruta = toba::consulta_php('helper_archivos')->get_dir_docum_personal();
		$ruta .= $usuario . '/categoria_transitoria_incentivos/';

		foreach ($datos as $fila) {
	
			if (!$fila['archivo']['error']) {
				$extension = toba::consulta_php('helper_archivos')->get_extension_archivo($fila['archivo']['tmp_name']);
				$extension = $extension ? $extension : ''; 
				$nombre_archivo_destino = $id_llamado . '-' . $fila['id'] . '.' . $extension;
				$resultado              = toba::consulta_php('helper_archivos')->subir_archivo($fila['archivo'], $ruta, $nombre_archivo_destino, ['pdf','png','jpg','jpeg']);
				
				if (!$resultado) {
					$error = true;
				}

			} else {
				//Logueo el error
				$this->log('Falló la subida del archivo de documentación probatoria del requisito \''.$fila['requisito'].'\' (ID: '.$fila['id'].') con tipo ' . $fila['archivo']['type'] . ' y nombre ' . $fila['archivo']['name'], 'CATEGORIA_INCENTIVOS_TRANSITORIA');
			}
		}

		//Obtengo la dirección de correo de contacto
		$direccion = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('direccion_mail_rrhh');

		try {
			$this->get_datos('sap_cat_inc_transitorio')->set([
				'nro_documento'   => $usuario,
				'id_llamado'      => $id_llamado,
				'categoria'       => $this->s__categoria,
				'fecha_solicitud' => date('Y-m-d'),
			]);
			$this->get_datos()->sincronizar();
			
			toba::consulta_php('co_grupos')->cargar_excepcion_director_grupos($usuario, 'Excepción automática por haber solicitado una categoría transitoria de incentivos (Solicitada: Categoría ' . self::CATEGORIAS[$this->s__categoria] . '). Solicitado el ' . date('d-m-Y'));

			$this->log('El usuario ' . toba::usuario()->get_id() . ' ha solicitado una categoria transitoria de incentivos. Categoria solicitada: ' . $this->s__categoria , 'CATEGORIA_INCENTIVOS_TRANSITORIA');
		} catch (toba_error_db $e) {
			$this->log('No se pudo guardar la solicitud de categoria ' . $this->s__categoria . '. Motivo  del error: ' . $e->get_mensaje_motor() . ' ---- ' . $e->get_sql_ejecutado, 'CATEGORIA_INCENTIVOS_TRANSITORIA');
			toba::notificacion()->error('Ocurrió un error al intentar registrar su solicitud. Por favor, comuniquese via mail a '.$direccion.' para obtener mas detalles sobre cómo solucionar este inconveniente');
			return;
		} catch (Exception $e) {
			$this->log('No se pudo guardar la solicitud de categoria ' . $this->s__categoria . '. Motivo  del error: ' . $e->getMessage(), 'CATEGORIA_INCENTIVOS_TRANSITORIA');
			toba::notificacion()->error('Ocurrió un error al intentar registrar su solicitud. Por favor, comuniquese via mail a '.$direccion.' para obtener mas detalles sobre cómo solucionar este inconveniente');
			return;
		}
		

		if (!$error) {
			toba::notificacion()->info('Su solicitud se ha guardado con éxito! La información y documentación proporcionadas, estarán disponibles para los evaluadores correspondientes.');
		} else {
			toba::notificacion()->info('Su solicitud se ha guardado con éxito! De todas formas, es posible que alguno de los archivos subidos haya contenido errores. En breve, la SGCyT se pondrá en contacto con usted si es necesario corregirlo.');
		}
		// Vamos al inicio
		$this->set_pantalla('pant_seleccion_categoria');

		// Si todo salió bien, elimino esta variable porque no será necesaria 
		unset($this->s__contenido_ml);
	}

	//-----------------------------------------------------------------------------------
	//---- Auxiliares -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	protected function get_datos($tabla = null)
	{
		return ($tabla) ? $this->dep('datos')->tabla($tabla) : $this->dep('datos');
	}

	protected function bloquear_formularios()
	{
		$this->dep('form_calificacion')->set_solo_lectura();
		$this->dep('form_calificacion')->eliminar_evento('solicitar');
	}

	function extender_objeto_js()
	{
		echo "
		var form = {$this->objeto_js}.dep('ml_documentacion');
		if (typeof form != 'undefined') {
			var filas = form.filas()
			var permitidos = ['application/pdf','image/png', 'image/jpeg', 'image/jpg'];
			for (id_fila in filas) {
			    form.ef('archivo').ir_a_fila(filas[id_fila]).cuando_cambia_valor(function(event){
			    	if (typeof event.target.files[0] != 'undefined') {
			    		console.log('Formato seleccionado: ', event.target.files[0].type);
			    		if (permitidos.indexOf(event.target.files[0].type) == -1) {
			    			notificacion.mostrar_ventana_modal('Formato no permitido','El archivo seleccionado no tiene un formato válido. Si continúa la carga, esto generará un error general y deberá cargar todos los archivos nuevamente. Le sugerimos reemplazar el archivo por otro (PDF, PNG o JPG)','500px');
			    		}
			    	}
			    });
			}

		}

		";
	}
}
?>