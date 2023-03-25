<?php

class helper_archivos
{
	//Prefijo DIR para carpetas contenedoras
	const DIR_DOC_POR_CONVOCATORIA_BECA = 'becas/doc_por_convocatoria/';
	const DIR_DOCUM_PERSONAL            = 'docum_personal/';
	
	const DOCUMENTO_DNI  = 'dni';
	const DOCUMENTO_CVAR = 'cvar';
	const DOCUMENTO_CUIL = 'cuil';
	const DOCUMENTO_CBU  = 'cbu';

	const DOCUMENTOS_PERSONALES = [
		self::DOCUMENTO_DNI,
		self::DOCUMENTO_CVAR,
		self::DOCUMENTO_CUIL,
		self::DOCUMENTO_CBU,
	];

	// RUTAS DOCUMENTOS PERSONALES
	const RUTAS_DOCUMENTOS_PERSONLES = [
		self::DOCUMENTO_DNI  => self::DIR_DOCUM_PERSONAL . '%s/' . self::DOCUMENTO_DNI  . '.pdf',
		self::DOCUMENTO_CVAR => self::DIR_DOCUM_PERSONAL . '%s/' . self::DOCUMENTO_CVAR . '.pdf',
		self::DOCUMENTO_CUIL => self::DIR_DOCUM_PERSONAL . '%s/' . self::DOCUMENTO_CUIL . '.pdf',
		self::DOCUMENTO_CBU  => self::DIR_DOCUM_PERSONAL . '%s/' . self::DOCUMENTO_CBU  . '.pdf',
	];

	const TIPOS_MIME = [
		'application/msword'            => 'doc' ,
		'application/msword'            => 'docx',
		'image/gif'                     => 'gif' ,
		'image/png'                     => 'png' ,
		'image/jpeg'                    => 'jpg' ,
		'image/jpeg'                    => 'jpeg',
		'application/pdf'               => 'pdf' ,
		'application/vnd.ms-powerpoint' => 'ppt' ,
		'application/vnd.ms-powerpoint' => 'pptx',
		'application/x-rar-compressed'  => 'rar' ,
		'application/zip'               => 'zip' ,
		'text/csv'						=> 'csv'
	];


	/**
	 * Obtiene y retorna, a partir del tipo MIME del archivo, su extensi�n
	 * @param  string  Ruta hacia el archivo del cu�l se quiere obtener la extensi�n
	 * @return mixed
	 */
	public function get_extension_archivo($archivo)
	{
		if (!is_string($archivo)) {
			return false;
		}

		if (!file_exists($archivo)) {
			return false;
		}

		$tipo_mime_archivo = strtolower(mime_content_type($archivo));

		return array_key_exists($tipo_mime_archivo, self::TIPOS_MIME) ? self::TIPOS_MIME[$tipo_mime_archivo] : false;
	}

	function subir_archivo($detalles,$carpeta,$nombre_archivo,$tipos_permitidos = array())
	{
		$nombre_archivo = str_replace(array('/','%','\\','/',':','*','?','<','>','|'), '-', $nombre_archivo);
		if(!count($detalles)){
			return;
		}
		
		if( ! is_dir($this->ruta_base().$carpeta)){
			if( ! mkdir($this->ruta_base().$carpeta,0777,TRUE)){
				throw new toba_error('No se puede crear el directorio '.$carpeta.' en el directorio navegable del servidor. Por favor, pongase en contacto con el administrador del sistema');
				return false;
			}
		}
		if(substr($carpeta, strlen($carpeta)-1,1) == '/'){
			$carpeta = substr($carpeta,0,strlen($carpeta)-1);
		}
		
		$tipos_mime = self::TIPOS_MIME;

		//Si existe un listado de tipos permitidos
		if(count($tipos_permitidos)){
			//Obtengo el tipo MIME del archivo subido
			$tipo_archivo = strtolower(mime_content_type($detalles['tmp_name']));

			if(in_array($tipos_mime[$tipo_archivo], $tipos_permitidos)){
				return move_uploaded_file($detalles['tmp_name'], $this->ruta_base().$carpeta."/".$nombre_archivo);
			}else{
				throw new toba_error('El archivo subido no tiene un formato permitido ('.$tipo_archivo.')');
			} 
		}else{
			//Si no se especific� ningun tipo de archivo en particular
			return move_uploaded_file($detalles['tmp_name'], $this->ruta_base().$carpeta."/".$nombre_archivo);
		}
	}

	function eliminar_archivo($archivo)
	{
		if(file_exists($archivo)){
			unlink($archivo);
		}
	}
	function eliminar_archivos_usuario($nro_documento)
	{

	}

	function procesar_campos($efs_archivos,&$datos_form,$ruta)
	{
		foreach($efs_archivos as $archivo){
			if(array_key_exists($archivo['ef'], $datos_form)){
				if($datos_form[$archivo['ef']]){
					if( ! $this->subir_archivo($datos_form[$archivo['ef']],utf8_encode($ruta),$archivo['nombre']) ){
						toba::notificacion()->agregar('No se pudo cargar el archivo '.$archivo['descripcion'].'. Por favor, intentelo nuevamente. Si el problema persiste, pongase en contacto con la Secretar�a General de Ciencia y T�cnica');
					}else{
						$datos_form[$archivo['ef']] = $archivo['nombre'];
					}
				}else{
					unset($datos_form[$archivo['ef']]);
				}	
			}
		}
	}

	/**
	 * Esta funcion procesa los archivos involucrados en un formulario ML. Por cada linea pasada al ML, esta funcion procesa si se trata de un Alta, Baja o Modificaci?, y en consecuencia, Sube, Modifica o Elimina archivos vinculados a cada linea. Recibe como par?etros el estado inicial del ML, el estado luego de la moficiacion, la ruta donde se almacenar? los archivos y los nombres de los campos del ML que se utilizar? para darle el nombre a cada archivo
	 * @param  array $estado_inicial_ml     Estado del ML al cargar el formulario
	 * @param  array $estado_actual_ml      Estado del ML luego de que el usuario realiza cambios
	 * @param  string $ruta                 Ruta donde se almacenar?/eliminaran los archivos involucrados
	 * @param  array $campos_nombre_archivo Campos del ML que se utilizan para formatear el nombre del archivo subido
	 * @param  string $nombre_input         Nombre del ef_upload que contiene el/los archivos subidos
	 * @return void                        
	 */
	function procesar_ml_con_archivos($estado_inicial_ml,&$estado_actual_ml,$ruta,$campos_nombre_archivo,$nombre_input)
	{

		//para cada linea de actividad docente
		foreach($estado_actual_ml as $fila => $item){
			
			//se genera el nombre del archivo
			/*$nombre = '';
			foreach($campos_nombre_archivo as $campo){
				//agrega un gui? medio entre cada palabra del nombre
				if(strlen($nombre)>0){
					$nombre .= "-";
				}
				$nombre .=  ($item[$campo['nombre']]) ? $item[$campo['nombre']] : $campo['defecto'];
			}*/
			//Genero un nombre ?nico para el archivo PDF
			$tmp = microtime();
			$tmp = explode(' ',$tmp);
			$micro = substr($tmp[0],2,8);
			$nombre = $tmp[1].$micro.".pdf";
			
			

			// =========== ALTA Y MODIFICACI? ===============
			
			if($item['apex_ei_analisis_fila'] == 'A' || $item['apex_ei_analisis_fila'] == 'M'){
				if($item[$nombre_input]){
					//en el caso de una modificaci?, se elimina el archivo previo
					if(isset($estado_inicial_ml)){
						if(array_key_exists($nombre_input, $estado_inicial_ml) && $estado_inicial_ml[$nombre_input]){
							$this->eliminar_archivo($ruta,$estado_inicial_ml[$nombre_input]);	
						}
					}
					
					//se sube el nuevo archivo
					if( ! $this->subir_archivo($item[$nombre_input],$ruta,utf8_encode($nombre),array('pdf'))){
						//se utiliza substr y strlen para quitar el ".pdf" al final del nombre de la actividad
						toba::notificacion()->agregar("No se pudo subir la documentaci? probatoria correspondiente a la actividad: ".substr($nombre,0,(strlen($nombre)-4) ) );
					}
					$estado_actual_ml[$fila][$nombre_input] = $nombre;
				}else{
					if(file_exists($ruta.utf8_encode($estado_inicial_ml[$fila][$nombre_input])) && is_file($ruta.utf8_encode($estado_inicial_ml[$fila][$nombre_input]) )){
						rename($ruta.utf8_encode($estado_inicial_ml[$fila][$nombre_input]),$ruta.utf8_encode($nombre) );
						$estado_actual_ml[$fila][$nombre_input] = $nombre;
					}else{
						//esta linea sirve para que el formulario (cuando no se define un nuevo archivo) no pise el estado anterior
						unset($estado_actual_ml[$fila][$nombre_input]);		
					}
					
				}
			}
				
			//si se est?dando de baja un registro, se busca su nombre de archivo y se lo elimina tambien
			if($item['apex_ei_analisis_fila'] == 'B'){
				foreach($estado_inicial_ml as $linea){
					if($linea['x_dbr_clave'] == $fila){
						$archivo = $ruta.utf8_encode($linea[$nombre_input]);
						$this->eliminar_archivo($archivo);
					}
				}
			}
		}
	}

	function ruta_base()
	{
		return toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos') . '/';
	}

	function url_base()
	{
		return toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos') . '/';
	}


	//Funci�n que registra una linea de log
	public function log($mensaje, $prefix = '')
	{
		if (!is_string($mensaje)) {
			return false;
		}

		$prefix = $prefix ? $prefix  . '-' : ''; 

		$log_path = $this->ruta_base() . 'logs/' . $prefix . date('Ym') . 'sap.log';
		$log      = fopen($log_path, 'a');
		fwrite($log, date('Y-m-d H:i:s') . ' | Usuario: ' . toba::usuario()->get_id() . ' | ' . preg_replace('/\s+/', ' ', $mensaje) . PHP_EOL);
		fclose($log);
	}

	public function get_archivos_log()
	{
		$carpeta  = opendir($this->ruta_base() . 'logs');
		$archivos = [];
		while ($archivo = readdir($carpeta)) {
		    if ($archivo != '.' && $archivo != '..') {
		    	$archivos[] = ['log' => $archivo];
		    }
		}
		return $archivos; 
	}

	/* =============== EXISTE PDF? (Documentaci�n Personl) ===================== */
	public function existe_documento_personal($nro_documento, $cual)
	{
		// Si no existe ese documento, no hay mucho para hacer...
		if ( ! in_array($cual, self::DOCUMENTOS_PERSONALES)) return false;
		
		// Reemplazo, en la ruta, el nro_documento recibido
		$ruta_documento = sprintf(self::RUTAS_DOCUMENTOS_PERSONLES[$cual], $nro_documento);
		
		// Retorno si existe o no
		return file_exists($this->ruta_base() . $ruta_documento);
	}


	public function get_path_documento_personal($nro_documento, $cual)
	{
		// Si no existe ese documento, no hay mucho para hacer...
		if ( ! in_array($cual, self::DOCUMENTOS_PERSONALES)) return false;
		
		// Reemplazo, en la ruta, el nro_documento recibido
		$ruta_documento = sprintf(self::RUTAS_DOCUMENTOS_PERSONLES[$cual], $nro_documento);
		
		// Retorno la ruta
		return $this->url_base() . $ruta_documento . '?v=' . time(); //TODO: evitar el cach� desde apache
	}

	public function get_dir_doc_por_convocatoria_beca()
	{
		return self::DIR_DOC_POR_CONVOCATORIA_BECA;
	}

	public function get_dir_docum_personal()
	{
		return self::DIR_DOCUM_PERSONAL;
	}






}

?>