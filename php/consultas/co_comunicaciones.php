<?php
class co_comunicaciones
{
	function becarios_pueden_ver_evaluaciones($id_convocatoria)
	{
		$convocatoria = $this->get_convocatoria($id_convocatoria);
		$convocatoria = $this->extract_custom_params($convocatoria);
		return ($convocatoria['becario_ve_evaluaciones'] == 'S');
	}

	function evaluacion_esta_abierta($id_convocatoria)
	{
		$convocatoria = $this->get_convocatoria($id_convocatoria);
		$convocatoria = $this->extract_custom_params($convocatoria);
		return ($convocatoria['evaluacion_abierta'] == 'S');
	}

	function get_detalle_comunicacion($id_comunicacion)
	{
		$resultado = $this->get_comunicaciones(array('id_comunicacion'=>$id_comunicacion));
		return (isset($resultado[0])) ? $resultado[0] : array();
	}

	function es_director_comunicacion($nro_documento)
	{
		$sql = "SELECT * 
				FROM sap_comunicacion 
				WHERE nro_documento_dir = " . quote($nro_documento) . "
				OR nro_documento_codir  = "  . quote($nro_documento) . " LIMIT 1";
		return (count(toba::db()->consultar($sql)) > 0);
	}

	function eliminar_aval_comunicacion($id_comunicacion)
	{
		$sql = 'UPDATE sap_comunicacion SET aval = null WHERE id = '.quote($id_comunicacion);
		return toba::db()->ejecutar($sql);
	}

	/**
	 * A partir de un registro de convocatoria de comunicaciones cient�ficas, extrae los custom_params
	 * y los asigna a indices del mismo arreglo (luego, elimina el indice "custom_params");
	 * @param  array $registro Registro original, con los custom_params en un indice, en formato json
	 * @return array           Registro original, con los custom_params anexados como parte del array
	 */
	function extract_custom_params($registro)
	{
		if (isset($registro['custom_params']) && $registro['custom_params']) {
			$custom_params = json_decode($registro['custom_params'],true);
			foreach ($custom_params as $indice => $valor) {
				$registro[$indice] = $valor;
			}
			unset($registro['custom_params']);
		}
		return $registro;
		
	}

	function get_comunicaciones($filtro = array())
	{

		$where = array();
		$sql = "SELECT com.id,
					com.titulo,
					SUBSTR(com.titulo,0,50)||'...' as titulo_corto,
					com.resumen,
					com.resolucion,
					com.sap_area_beca_id,
					ac.descripcion AS area_beca_desc,
					com.sap_tipo_beca_id,
					tb.descripcion AS tipo_beca_desc,
					com.sap_dependencia_id,
					dep.nombre AS dependencia_desc,
					com.periodo_desde,
					com.periodo_hasta,
					com.sap_convocatoria_id,
					conv.nombre AS convocatoria_desc,
					com.usuario_id,
					per.apellido||', '||per.nombres AS autor,
					per.mail,
					per.celular,
					com.nro_documento_dir,
					dir.apellido||', '||dir.nombres AS director,
					dir.mail AS mail_director,
					com.nro_documento_codir,
					codir.apellido||', '||codir.nombres AS codirector,
					codir.mail AS mail_codirector,
					com.orden_poster,
					com.proyecto_id,
					proy.codigo AS proyecto_codigo,
					proy.descripcion AS proyecto_descripcion,
					com.evaluador_poster,
					com.estado,
					CASE com.estado WHEN 'C' THEN 'Cerrada' WHEN 'A' THEN 'Abierta' ELSE 'Sin estado' END AS estado_desc,
					com.es_mejor_trabajo,
					com.palabras_clave,
					com.aval,
					(com.aval is not null)::boolean as avalado,
					CASE WHEN com.aval IS NOT NULL THEN 'Avalado' ELSE 'Pendiente' END AS aval_desc,
					(SELECT ev.nombre FROM sap_comunicacion_evaluacion AS ce LEFT JOIN sap_evaluacion AS ev ON ev.id = ce.sap_evaluacion_id WHERE sap_comunicacion_id = com.id ORDER BY fecha_hora DESC LIMIT 1) AS ultima_evaluacion
				FROM sap_comunicacion AS com
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = com.sap_area_beca_id
				LEFT JOIN sap_tipo_beca AS tb ON tb.id = com.sap_tipo_beca_id
				LEFT JOIN sap_dependencia AS dep ON dep.id = com.sap_dependencia_id
				LEFT JOIN sap_convocatoria AS conv ON conv.id = com.sap_convocatoria_id
				LEFT JOIN sap_proyectos AS proy ON proy.id = com.proyecto_id
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = com.nro_documento_dir
				LEFT JOIN sap_personas AS codir ON codir.nro_documento = com.nro_documento_codir
				LEFT JOIN sap_personas AS per ON per.nro_documento = com.usuario_id
				ORDER BY com.sap_convocatoria_id DESC, com.orden_poster ASC";
		if(isset($filtro['id'])){
			$where[] = 'com.id = ' . quote($filtro['id']);
		}
		if(isset($filtro['estado'])){
			$where[] = 'com.estado = ' . quote($filtro['estado']);
		}
		if(isset($filtro['estado_aval'])){
			$where[] = ($filtro['estado_aval'] == 'A') ? 'com.aval is not null' : 'com.aval is null';
		}
		if(isset($filtro['id_comunicacion'])){
			$where[] = 'com.id = '.quote($filtro['id_comunicacion']);
		}
		if(isset($filtro['id_convocatoria'])){
			$where[] = 'com.sap_convocatoria_id = '.quote($filtro['id_convocatoria']);
		}
		if(isset($filtro['usuario_id'])){
			$where[] = 'com.usuario_id = '.quote($filtro['usuario_id']);
		}
		if(isset($filtro['nro_documento_dir'])){
			$where[] = 'com.nro_documento_dir = '.quote($filtro['nro_documento_dir']);
		}
		if(isset($filtro['orden_poster'])){
			$where[] = 'com.orden_poster = '.quote($filtro['orden_poster']);
		}
		if(isset($filtro['sap_dependencia_id'])){
			$where[] = 'com.sap_dependencia_id = '.quote($filtro['sap_dependencia_id']);
		}
		if(isset($filtro['id_area_conocimiento']) && $filtro['id_area_conocimiento']){
			$where[] = 'com.sap_area_beca_id = ' . quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['id_tipo_beca']) && $filtro['id_tipo_beca']){
			$where[] = 'com.sap_tipo_beca_id = ' . quote($filtro['id_tipo_beca']);
		}
		if(isset($filtro['director']) && $filtro['director']){
			$where[] = 'com.nro_documento_dir in (SELECT nro_documento FROM sap_personas WHERE quitar_acentos(apellido) ILIKE quitar_acentos('.quote('%'.$filtro['director'].'%').') OR quitar_acentos(nombres) ILIKE quitar_acentos('.quote('%'.$filtro['director'].'%').') OR nro_documento ILIKE '.quote('%'.$filtro['director'].'%').')';
		}
		if(isset($filtro['autor']) && $filtro['autor']){
			$where[] = 'com.usuario_id in (SELECT nro_documento FROM sap_personas WHERE quitar_acentos(apellido) ILIKE quitar_acentos('.quote('%'.$filtro['autor'].'%').') OR quitar_acentos(nombres) ILIKE quitar_acentos('.quote('%'.$filtro['autor'].'%').') OR nro_documento ILIKE '.quote('%'.$filtro['autor'].'%').')';
		}
		if(isset($filtro['estado_eval']) && $filtro['estado_eval']){
			$where[] = '(SELECT sap_evaluacion_id 
						 FROM sap_comunicacion_evaluacion 
						 WHERE sap_comunicacion_id = com.id 
						 ORDER BY fecha_hora DESC LIMIT 1) = ' . quote($filtro['estado_eval']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}

		//Se limita a un resultado
		if(isset($filtro['id_comunicacion'])){
			$sql .= ' LIMIT 1';
		}
		return toba::db()->consultar($sql);
	}

	function get_comunicaciones_estado_eval($filtro = array()){
		$where = array();
		$sql = "SELECT
					com.id,
					com.titulo,
					per.mail,
					per.apellido||', '||per.nombres AS autor,
					per.mail,
					(	SELECT ce.observaciones 
						FROM sap_comunicacion_evaluacion ce 
						WHERE ce.sap_comunicacion_id = com.id 
						ORDER BY ce.id DESC limit 1) AS observacion_evaluacion,
					ac.nombre AS area_beca,
					tb.descripcion AS tipo_beca,
					com.orden_poster,
					com.evaluador_poster,
					case when (SELECT ce.sap_evaluacion_id FROM sap_comunicacion_evaluacion ce WHERE ce.sap_comunicacion_id = com.id ORDER BY ce.id DESC LIMIT 1) = 7 then 'EXPOSICION ORAL' else '' end as exposicion
				FROM sap_comunicacion AS com
				LEFT JOIN sap_area_conocimiento AS ac   ON ac.id   = com.sap_area_beca_id
				LEFT JOIN sap_tipo_beca         AS tb   ON tb.id   = com.sap_tipo_beca_id
				LEFT JOIN sap_convocatoria      AS conv ON conv.id = com.sap_convocatoria_id
				LEFT JOIN sap_personas AS per ON per.nro_documento = com.usuario_id";
		if(isset($filtro['evaluacion_id']) && $filtro['evaluacion_id']){
			$where[] = "(SELECT ce.sap_evaluacion_id FROM sap_comunicacion_evaluacion ce WHERE ce.sap_comunicacion_id = com.id ORDER BY ce.id DESC LIMIT 1) IN (".$filtro['evaluacion_id'].")";
		}else{
			$where[] = "com.id NOT IN (SELECT DISTINCT ce.sap_comunicacion_id FROM sap_comunicacion_evaluacion ce)";
		}
		if(isset($filtro['area_conocimiento_id'])){
			$where[] = "com.sap_area_beca_id = ".quote($filtro['area_conocimiento_id']);
		}

		if(isset($filtro['tipo_beca_id'])){
			$where[] = "com.sap_tipo_beca_id = " . $filtro['tipo_beca_id']; 
		}

		if(isset($filtro['estado_convocatoria'])){
			$where[] = "conv.estado = " . quote($filtro['estado_convocatoria']);
		}
		if(isset($filtro['id_convocatoria'])){
			$where[] = "com.sap_convocatoria_id = " .  quote($filtro['id_convocatoria']);
		}
		if(isset($filtro['estado'])){
			$where[] = "com.estado = " .  quote($filtro['estado']);
		}
		if(isset($filtro['estado_aval']) && $filtro['estado_aval']){
			if($filtro['estado_aval'] == 'A'){
				$where[] = 'com.aval is not null';	
			}
			if($filtro['estado_aval'] == 'N'){
				$where[] = 'com.aval is null';	
			}
		}
		

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
		

	}
	
	//YA EXISTE OTRA FUNCI�N MEJORADA: USAR get_comunicaciones_estado_eval EN LUGAR DE �STA
	static function get_comunicacionesByEstadoEvaluacion($evaluacion_id,$area_id,$tipo_beca_id,$estado_convocatoria='',$sap_convocatoria_id = NULL,$estado_comunicacion=NULL){
		//echo "Evaluacion id: ".$evaluacion_id." - Area id: ".$area_id." - Tipo de Beca id:".$tipo_beca_id." - Estado: ".$estado." - Sap Convoc ID:".$sap_convocatoria_id."<br>";
		$sql = "SELECT
					c.id,
					c.titulo,
					per.mail,
					per.apellido||', '||per.nombres AS autor,
					(SELECT ce.observaciones 
						FROM sap_comunicacion_evaluacion ce 
						WHERE ce.sap_comunicacion_id=c.id 
						ORDER BY ce.id DESC limit 1) AS observacion_evaluacion,
					a.nombre AS area_beca,
					t.descripcion AS tipo_beca,
					c.orden_poster,
					c.evaluador_poster,
					 case when (SELECT ce.sap_evaluacion_id 
					FROM sap_comunicacion_evaluacion ce 
					WHERE ce.sap_comunicacion_id = c.id 
					ORDER BY ce.id DESC LIMIT 1) = 7 -- SELECCIONADA
					then 'EXPOSICION ORAL'
					else ''
					end as exposicion
				FROM
					sap_comunicacion c
				LEFT JOIN sap_area_conocimiento a ON a.id=c.sap_area_beca_id
				LEFT JOIN sap_tipo_beca t ON t.id=c.sap_tipo_beca_id
				LEFT JOIN sap_convocatoria co on co.id=c.sap_convocatoria_id
				LEFT JOIN sap_personas AS per ON per.nro_documento = c.usuario_id
				WHERE 1=1";
		if ($evaluacion_id != ''){
			$sql .= " AND (SELECT ce.sap_evaluacion_id 
					FROM sap_comunicacion_evaluacion ce 
					WHERE ce.sap_comunicacion_id = c.id 
					ORDER BY ce.id DESC LIMIT 1) IN ({$evaluacion_id})";
		}else{
			$sql .= " AND c.id NOT IN (SELECT DISTINCT ce.sap_comunicacion_id 
											FROM sap_comunicacion_evaluacion ce)";
		}

		if ($area_id != ''){
			$sql .= " AND c.sap_area_beca_id = {$area_id}";
			   
		}
		if ($tipo_beca_id != ''){
			$sql .= " AND c.sap_tipo_beca_id = {$tipo_beca_id}";
			   
		}
		 if ($estado_convocatoria != ''){
			$sql .= " AND co.estado=" .  quote($estado_convocatoria);
		}
		if($sap_convocatoria_id){
			$sql .= " AND c.sap_convocatoria_id = " .  $sap_convocatoria_id;   
		}
		 if ($estado_comunicacion){
			$sql .= " AND c.estado=" .  quote($estado_comunicacion);
		}
		$sql .= " GROUP BY
						c.id,
						c.titulo,
						per.mail,
						a.nombre,
						t.descripcion
				   ORDER BY c.orden_poster ASC;";
		return consultar_fuente($sql);
	}
  

	static function get_comunicacionesConEstadoEvaluacion($usuario_id){
		$sql = "SELECT
					c.id,
					a.nombre AS area,
					titulo,
					per.mail,
					per.celular,
					t.descripcion AS tipo,
					p.codigo || ' - ' || p.descripcion AS proyecto_descripcion,
					d.nombre AS dependencia,
					c.orden_poster,
					(SELECT e.nombre 
						FROM sap_comunicacion_evaluacion ce 
						JOIN sap_evaluacion e ON e.id=ce.sap_evaluacion_id
						WHERE ce.sap_comunicacion_id=c.id 
						ORDER BY ce.id DESC limit 1) AS estado_evaluacion,
					(SELECT ce.observaciones 
						FROM sap_comunicacion_evaluacion ce 
						WHERE ce.sap_comunicacion_id=c.id 
						ORDER BY ce.id DESC limit 1) AS observacion_evaluacion,
					(SELECT ce.evaluadores 
						FROM sap_comunicacion_evaluacion ce 
						WHERE ce.sap_comunicacion_id=c.id 
						ORDER BY ce.id DESC limit 1) AS evaluadores
					
									 
				   
			FROM
					sap_comunicacion c
			LEFT JOIN sap_area_conocimiento as a ON a.id = c.sap_area_beca_id
			LEFT JOIN sap_tipo_beca t ON t.id = c.sap_tipo_beca_id
			LEFT JOIN sap_dependencia d ON d.id = c.sap_dependencia_id
			LEFT JOIN sap_proyectos p ON p.id = c.proyecto_id
			LEFT JOIN sap_personas AS per ON per.nro_documento = c.usuario_id
			
			WHERE c.usuario_id={$usuario_id}
			GROUP BY
					c.id,
					a.nombre,
					titulo,
					per.mail,
					per.celular,
					t.descripcion,
					p.descripcion,
					p.codigo,
					d.nombre
			ORDER BY c.id DESC;";
		return consultar_fuente($sql);
	}

	function get_convocatoria($id_convocatoria)
	{
		$datos = toba::consulta_php('co_convocatorias')->get_convocatorias(['id'=>$id_convocatoria]);
		if (count($datos)) {
			return array_map(function($convocatoria){
				return $this->extract_custom_params($convocatoria);
			}, $datos[0]);
		} else {
			return [];
		}
	}

	function get_convocatorias_todas()
	{
		$datos = toba::consulta_php('co_convocatorias')->get_convocatorias(['aplicable'=>'BECARIOS']);
		if (count($datos)) {
			return array_map(function($convocatoria){
				return $this->extract_custom_params($convocatoria);
			}, $datos);
		} else {
			return [];
		}
	}
	
	function get_evaluaciones_comunicacion($id_comunicacion){
		$sql = "SELECT
					ce.id,
					ce.evaluadores AS evaluadores,
					ce.observaciones AS observaciones,
					ce.fecha_hora AS fecha_hora,
					e.nombre AS evaluacion,
					ce.usuario_id
				FROM sap_comunicacion_evaluacion ce
				JOIN sap_evaluacion e ON e.id=ce.sap_evaluacion_id
				WHERE ce.sap_comunicacion_id = ".quote($id_comunicacion)."
				ORDER BY ce.id DESC;";
		return toba::db()->consultar($sql);
	}
	  static function get_comunicacionTituloById($comunicacion_id){
		 $sql = "SELECT
					CASE 
						WHEN (character_length(c.titulo)) > 150 THEN c.id || ' - ' || substr(c.titulo,0,150) || '...'
					ELSE c.id || ' - ' || c.titulo END as titulo
				FROM sap_comunicacion c
				WHERE c.id = {$comunicacion_id};";
		 return consultar_fuente($sql);
	}

	static function get_historial_comunicaciones($id_usuario)
	{
		$sql = "SELECT com.id,
					   com.orden_poster,
					   com.titulo, 
					   area.descripcion area_tipo_beca, 
					   tb.descripcion tipo_beca, 
					   conv.nombre convocatoria,
					   ce.sap_evaluacion_id,
					   eval.nombre evaluacion
				FROM sap_comunicacion as com
				LEFT JOIN sap_area_conocimiento as area on area.id = com.sap_area_beca_id
				LEFT JOIN sap_tipo_beca as tb on tb.id = com.sap_tipo_beca_id
				LEFT JOIN sap_convocatoria as conv on conv.id = com.sap_convocatoria_id
				LEFT JOIN sap_comunicacion_evaluacion as ce on ce.sap_comunicacion_id = com.id and ce.id = (select max(id) from sap_comunicacion_evaluacion where sap_comunicacion_id = com.id)
				LEFT JOIN sap_evaluacion as eval on eval.id = ce.sap_evaluacion_id
					AND ce.fecha_hora = (SELECT MAX(fecha_hora) 
											FROM sap_comunicacion_evaluacion 
											WHERE sap_comunicacion_id = com.id)
				WHERE com.usuario_id = ".quote($id_usuario)."
				ORDER BY com.id DESC";
		return consultar_fuente($sql);
	}

	function abrir_comunicacion($id)
	{
		return toba::db()->ejecutar("UPDATE sap_comunicacion SET estado = 'A', aval = null WHERE id = ".quote($id));
	}
	
	function get_reporte_certificados($filtro = NULL)
	{
		if(!$filtro){
			return array();
		}
		$sql = "SELECT nombre AS area,
						id_comunicacion, 
						orden_poster, 
						replace(titulo,chr(10),' ') AS titulo, 
						nombre_convocatoria,
						id_convocatoria,
						evaluacion,
						es_mejor_trabajo,
						autor,
						asistio_jornada
				FROM (
					SELECT ac.nombre, 
						com.id as id_comunicacion,
						eva.sap_evaluacion_id as evaluacion,
		
						com.es_mejor_trabajo,
						com.orden_poster, 
						com.titulo,
						com.sap_convocatoria_id as id_convocatoria,
						conv.nombre as nombre_convocatoria, 
						per.apellido||', '||per.nombres AS autor,
						com.asistio_jornada
					FROM sap_comunicacion_evaluacion AS eva
					LEFT JOIN sap_comunicacion AS com ON com.id = eva.sap_comunicacion_id
					LEFT JOIN sap_area_conocimiento AS ac ON ac.id = sap_area_beca_id
					LEFT JOIN sap_personas AS per ON per.nro_documento = com.usuario_id
					LEFT JOIN sap_convocatoria AS conv ON conv.id = com.sap_convocatoria_id ";
					if(isset($filtro['estado_evaluacion'])){
						switch ($filtro['estado_evaluacion']) {
							case 'S':
								$sql .= " where eva.sap_evaluacion_id = 7 "; 
								break;
							case 'A':
								$sql .= " where eva.sap_evaluacion_id = 5 "; 
								break;
							case 'T':
								$sql .= " where eva.sap_evaluacion_id in (7,5) "; 
								break;
							default:
								$sql .= " where eva.sap_evaluacion_id in (7,5) "; 
								break;
						}
					}else{
						$sql .= " where eva.sap_evaluacion_id in (7,5) "; 
					}
		if(isset($filtro['id_convocatoria'])){
			$sql .= "and com.sap_convocatoria_id = ".$filtro['id_convocatoria'];
		}
		if(isset($filtro['id_comunicacion'])){
			$sql .= "and com.id = ".$filtro['id_comunicacion'];
		}
		if(isset($filtro['nro_documento'])){
			$sql .= "and com.usuario_id = ".quote($filtro['nro_documento']);
		}

		if(isset($filtro['becario'])){
			$criterio = quote('%'.str_replace([',',' '],'%',$filtro['becario']).'%');
			$sql .= "and com.usuario_id IN (
						SELECT nro_documento 
						FROM sap_personas 
						WHERE (
							quitar_acentos(nombres) ILIKE quitar_acentos(".quote('%'.$filtro['becario'].'%').")
							OR quitar_acentos(apellido) ILIKE quitar_acentos(".quote('%'.$filtro['becario'].'%').")
							OR quitar_acentos(apellido)||quitar_acentos(nombres) ILIKE quitar_acentos($criterio)
							OR quitar_acentos(nombres)||quitar_acentos(apellido) ILIKE quitar_acentos($criterio)
							OR nro_documento ILIKE $criterio
						)
					)";
		}

		
		$sql.=" and eva.fecha_hora = (SELECT max(fecha_hora) FROM sap_comunicacion_evaluacion where sap_comunicacion_id = com.id)
					order by orden_poster asc
					) AS tmp
				group by area, id_comunicacion, orden_poster, titulo, nombre_convocatoria, id_convocatoria, evaluacion,es_mejor_trabajo,autor, asistio_jornada
				order by id_convocatoria desc, orden_poster asc";
		return toba::db()->consultar($sql);
	}


	function get_comunicacion($id)
	{
		$sql = "SELECT * FROM sap_comunicacion WHERE id = ".quote($id);
		return toba::db()->consultar_fila($sql);
	}

	function get_ultima_evaluacion($id_comunicacion)
	{
		$sql = "SELECT com.id, com.fecha_hora, com.sap_evaluacion_id, com.sap_comunicacion_id, com.evaluadores, com.observaciones, com.usuario_id, eva.nombre as evaluacion 
				FROM sap_comunicacion_evaluacion as com
				LEFT JOIN sap_evaluacion as eva ON eva.id = com.sap_evaluacion_id
				WHERE sap_comunicacion_id = ".quote($id_comunicacion)." ORDER BY fecha_hora DESC LIMIT 1";
		return toba::db()->consultar_fila($sql);
	}

	function solicitaron_modificaciones($id_comunicacion)
	{
		$ultima_eval = $this->get_ultima_evaluacion($id_comunicacion);
		return (isset($ultima_eval['evaluacion']) && strtolower($ultima_eval['evaluacion']) == 'a modificar');
	}

	function get_orden_poster($convocatoria,$area_conocimiento){
		$sql = "SELECT orden_poster as ultimo_id
				FROM sap_comunicacion
				WHERE sap_convocatoria_id = $convocatoria
				AND sap_area_beca_id = $area_conocimiento
				AND orden_poster IS NOT NULL
				ORDER BY id DESC
				LIMIT 1";
		$resultado = toba::db('sap')->consultar($sql);
		
		//eval�o si existe alg�n orden de poster anterior cargado
		if(isset($resultado[0]) && $resultado[0]['ultimo_id']){
			//divido el orden de poster para obtener el valor numerico (sin prefijo)
			$ultimo_id = explode('-',$resultado[0]['ultimo_id']);
			$ultimo_id = intval($ultimo_id[1]);     
		}else{
			$ultimo_id = NULL;
		}
		
		//obtengo el prefijo para el area de conocimiento seleccionada
		$prefijo = toba::db('sap')->consultar("SELECT descripcion, prefijo_orden_poster FROM sap_area_conocimiento WHERE id = $area_conocimiento LIMIT 1");
		//si no existe prefijo, se toma la descripcion completa
		$prefijo = ($prefijo[0]['prefijo_orden_poster']) ? $prefijo[0]['prefijo_orden_poster'] : $prefijo[0]['descripcion'];
		
		//armo el orden de poster con el formato deseado
		$id = ($ultimo_id) ? sprintf("%'03d", ($ultimo_id+1) ) : '001';
		return $prefijo."-".$id;
	}

	function existe_extendido($id_comunicacion)
	{
		$ruta = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$ruta .= "/comunicaciones/$id_comunicacion/extendido.pdf";  
		return file_exists($ruta);
	}

	function get_comunicaciones_pendientes_aval($filtro = array())
	{
		$where = array();
		$sql = "SELECT com.id, 
					per.apellido||', '||per.nombres AS autor, 
					dir.apellido||', '||dir.nombres AS director, 
					case when length(com.titulo) > 100 then substr(com.titulo,0,100)||'(...)' else com.titulo end as titulo,
					dep.nombre AS dependencia,
					com.aval
				FROM sap_comunicacion AS com
				LEFT JOIN sap_personas AS per ON per.nro_documento = com.usuario_id
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = com.nro_documento_dir
				LEFT JOIN sap_dependencia AS dep ON dep.id = com.sap_dependencia_id
				WHERE com.estado <> 'A'";
		if(isset($filtro['nro_documento_dir']) && $filtro['nro_documento_dir']){
			$where[] = 'nro_documento_dir = ' . quote($filtro['nro_documento_dir']) . " OR nro_documento_codir = " .  quote($filtro['nro_documento_dir']);
		}
		if(isset($filtro['id_convocatoria']) && $filtro['id_convocatoria']){
			$where[] = 'com.sap_convocatoria_id = ' . quote($filtro['id_convocatoria']);
		}
		
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function otorgar_aval($id_comunicacion)
	{
		$sql = "UPDATE sap_comunicacion SET aval = current_date WHERE id = ".quote($id_comunicacion);
		return toba::db()->ejecutar($sql);
	}
 
	
  
	
}
?>