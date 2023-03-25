<?php
class co_comision_asesora
{
	function es_integrante_comision_asesora($nro_documento)
	{
		$sql = "select * from be_comision_asesora_integrante where id_convocatoria = (
					SELECT MAX(id_convocatoria) FROM be_comision_asesora_integrante
				) and nro_documento = " . quote($nro_documento);

		$resultado = toba::db()->consultar($sql);
		return (count($resultado) > 0);
	}

	function get_comisiones_asesoras($filtro = array())
	{
		$where = array();
		if (isset($filtro['id_convocatoria'])) {
			$where[] = "ca.id_convocatoria = ".quote($filtro['id_convocatoria']);
		}
		$sql = "SELECT
			ca.id_area_conocimiento,
			ca.id_convocatoria,
			ac.nombre AS area_conocimiento,
			cb.convocatoria
		FROM be_comision_asesora as ca
		LEFT JOIN be_convocatoria_beca as cb on ca.id_convocatoria = cb.id_convocatoria
		LEFT JOIN sap_area_conocimiento as ac on ac.id = ca.id_area_conocimiento
		ORDER BY ca.id_convocatoria";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	function get_integrantes_comision($filtro = [])
	{
		$where = [];

		$sql = "SELECT inte.nro_documento, per.apellido||', '||per.nombres AS evaluador
				FROM be_comision_asesora_integrante AS inte
				LEFT JOIN sap_personas AS per ON per.nro_documento = inte.nro_documento
				ORDER BY 2";

		if (isset($filtro['id_convocatoria'])) {
			$where[] = "inte.id_convocatoria = ".quote($filtro['id_convocatoria']);
		}
		if (isset($filtro['id_area_conocimiento'])) {
			$where[] = "inte.id_area_conocimiento = ".quote($filtro['id_area_conocimiento']);
		}

		if (count($where)) {
			$sql = sql_concatenar_where($sql, $where);
		}

		return toba::db()->consultar($sql);
	}

	public function get_integrantes_ultima_comision_conformada($filtro = [])
	{
		// Obtengo el ID de la ultima convocatoria que tenga comisi�n conformada
		$sql = "SELECT max(id_convocatoria) AS id_convocatoria
				FROM be_comision_asesora_integrante
				WHERE id_convocatoria IN (
					SELECT id_convocatoria FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3
				)";
		$resultado = toba::db()->consultar_fila($sql);

		if (!empty($resultado['id_convocatoria'])) {
			$filtro['id_convocatoria'] = $resultado['id_convocatoria'];
			return $this->get_integrantes_comision($filtro);
		}
		return [];
	}

	public function get_integrantes_ultima_comision_conformada_area_usuario()
	{
		$id_area_conocimiento = $this->get_area_conocimiento_evaluador(toba::usuario()->get_id());
		return $this->get_integrantes_ultima_comision_conformada(['id_area_conocimiento' => $id_area_conocimiento]);
	}



	function get_criterios_evaluacion($inscripcion)
	{
		$where = array();
		if(isset($inscripcion['id_convocatoria'])){
			$where[] = 'cri.id_convocatoria = '.$inscripcion['id_convocatoria'];
		}
		if(isset($inscripcion['id_tipo_beca'])){
			$where[] = 'cri.id_tipo_beca = '.$inscripcion['id_tipo_beca'];
		}
		$sql = "SELECT * FROM be_tipo_beca_criterio_eval AS cri";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	public function get_criterios_y_subcriterios_evaluacion_por_convocatoria($id_convocatoria)
	{
		if (!$id_convocatoria) {
			return [];
		}

		$id_convocatoria = quote($id_convocatoria);

		// Obtengo la tabla de criterios y subcriterios para esa convocatoria
		$sql = "SELECT cri.id_convocatoria, 
						cri.id_tipo_beca, 
						cri.id_criterio_evaluacion, 
						cri.criterio_evaluacion, 
						cri.puntaje_maximo AS puntaje_maximo_criterio, 
						sub.id_subcriterio_evaluacion,
						sub.descripcion AS subcriterio_evaluacion,
						sub.referencia, 
						sub.maximo AS puntaje_maximo_subcriterio
				FROM be_tipo_beca_criterio_eval AS cri
				LEFT JOIN be_subcriterio_evaluacion AS sub 
				    ON sub.id_convocatoria         = cri.id_convocatoria
				    AND sub.id_tipo_beca           = cri.id_tipo_beca
				    AND sub.id_criterio_evaluacion = cri.id_criterio_evaluacion
				WHERE cri.id_convocatoria = $id_convocatoria";
		$criterios = toba::db()->consultar($sql);

		// Re-indexo el array $criterios para que sea mas f�cil/r�pido buscar en �l, luego
		$criterios_clave = [];
		foreach ($criterios as $criterio) {
			$indice = $criterio['id_criterio_evaluacion'] . '-' . $criterio['id_subcriterio_evaluacion'];
			$criterios_clave[$indice] = $criterio;
		}

		return $criterios_clave;
	}

	/**
	 * Retorna los criterios y subcriterios de evaluaci�n para una convocatoria y tipo de beca
	 *
	 * @param int $id_convocatoria
	 * @param int $id_tipo_beca
	 *
	 * @return array
	 */
	public function get_criterios_y_subcriterios($id_convocatoria, $id_tipo_beca)
	{
		$id_convocatoria = quote($id_convocatoria);
		$id_tipo_beca    = quote($id_tipo_beca);
		
		// Obtengo los criterios y subcriterios para esta convocatoria y tipo de beca
		$sql = "SELECT * 
				FROM be_tipo_beca_criterio_eval AS cri
				LEFT JOIN be_subcriterio_evaluacion AS sub 
					ON sub.id_convocatoria         = cri.id_convocatoria
				    AND sub.id_tipo_beca           = cri.id_tipo_beca
				    AND sub.id_criterio_evaluacion = cri.id_criterio_evaluacion
				WHERE cri.id_convocatoria = $id_convocatoria
				AND cri.id_tipo_beca = $id_tipo_beca";

		return toba::db()->consultar($sql);
	}

	public function get_subcriterios_evaluacion($id_criterio_evaluacion)
	{
		if (!is_numeric($id_criterio_evaluacion) || $id_criterio_evaluacion < 0) {
			return [];
		}

		$sql = "SELECT * FROM be_subcriterio_evaluacion WHERE id_criterio_evaluacion = " . $id_criterio_evaluacion;
		return toba::db()->consultar($sql);
	}

	function get_criterio_descripcion($id_criterio_evaluacion)
	{
		return toba::db()->consultar_fila("SELECT criterio_evaluacion FROM be_tipo_beca_criterio_eval WHERE id_criterio_evaluacion = ".quote($id_criterio_evaluacion));
	}

	function get_puntaje_maximo($id_criterio_evaluacion)
	{
		return toba::db()->consultar_fila("SELECT puntaje_maximo FROM be_tipo_beca_criterio_eval WHERE id_criterio_evaluacion = ".quote($id_criterio_evaluacion));
	}

	function get_ayn_evaluador($nro_documento)
	{
		return toba::db()->consultar_fila("SELECT apellido||', '||nombres FROM sap_personas WHERE nro_documento = ".quote($nro_documento));
	}

	function get_detalles_dictamen($inscripcion, $tipo_dictamen = 'C')
	{
		$tipo_dictamen = $tipo_dictamen ? quote($tipo_dictamen) : quote('C');
		$sql = "SELECT 
				det.id_criterio_evaluacion, 
				cri.criterio_evaluacion, 
				det.puntaje as asignado, 
				cri.puntaje_maximo, 
				dic.justificacion_puntajes, 
				dic.evaluadores,
				dic.desglose_puntajes
			from be_dictamen as dic
			left join be_dictamen_detalle as det 
			    on det.tipo_dictamen = dic.tipo_dictamen
			    and det.nro_documento = dic.nro_documento
			    and det.id_tipo_beca = dic.id_tipo_beca
			    and det.id_convocatoria = dic.id_convocatoria
			left join be_tipo_beca_criterio_eval as cri on cri.id_criterio_evaluacion = det.id_criterio_evaluacion
			WHERE dic.nro_documento = ".quote($inscripcion['nro_documento'])."
			AND dic.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
			AND dic.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
			AND dic.tipo_dictamen = $tipo_dictamen";
		return toba::db()->consultar($sql);
	}

	function get_dictamen($inscripcion)
	{
		$sql = "SELECT dic.nro_documento, 
						dic.id_convocatoria, 
						dic.id_tipo_beca, 
						dic.justificacion_puntajes, 
						dic.usuario_id,
						dic.puntaje_asignado,
						dic.desglose_puntajes,
			        ARRAY_TO_STRING( 
			            (SELECT ARRAY_AGG(UPPER(apellido)||', '||nombres) AS evaluador 
			            FROM sap_personas 
			            --where nro_documento = any (string_to_array(dic.evaluadores,'/'))
			            WHERE nro_documento = ANY (STRING_TO_ARRAY(dic.evaluadores,'/'))) 
			        ,'/') AS evaluadores,
			        per.apellido||', '||per.nombres AS usuario,
			    	originalidad, 
					claridad_formulacion_obj,
					adecuacion_disenio,
					factibilidad,
					calidad_de_propuesta
			    FROM be_dictamen AS dic
			    LEFT JOIN sap_personas AS per ON per.nro_documento = dic.usuario_id
			    WHERE dic.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
			    AND dic.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
			    AND dic.nro_documento = ".quote($inscripcion['nro_documento'])."
			    AND dic.tipo_dictamen = 'C'";
		return toba::db()->consultar_fila($sql);
	}

	//retorna el id de area de conocimiento del usuario recibido como parámetro
	function get_area_conocimiento_evaluador($nro_documento)
	{
		$sql = "SELECT id_area_conocimiento 
				FROM be_comision_asesora_integrante 
				WHERE nro_documento = ".quote($nro_documento) . "
				AND id_convocatoria = (
					SELECT MAX(id_convocatoria) 
					FROM be_comision_asesora_integrante 
					WHERE nro_documento = " . quote($nro_documento) . ")";
		$resultado = toba::db()->consultar_fila($sql);
		return (count($resultado)) ? $resultado['id_area_conocimiento'] : '';
	}

	/**
	 * Obtiene todos los detalles que se le muestran al postulante en la opción de seguimiento. Esto incluye el resultado de la admisibilidad, la evaluación de comision y junta
	 */
	function get_detalles_seguimiento($inscripcion)
	{
		$cumplimientos = toba::consulta_php('co_becas')->get_cumplimientos_becario(
			$inscripcion['nro_documento'],$inscripcion['id_convocatoria'],$inscripcion['id_tipo_beca']
		);
		
		$where  = "WHERE padre.nro_documento = ".quote($inscripcion['nro_documento'])."
					AND padre.id_tipo_beca    = ".quote($inscripcion['id_tipo_beca'])."
					AND padre.id_convocatoria = ".quote($inscripcion['id_convocatoria']);

		$sql = "SELECT admisible, beca_otorgada, observaciones FROM be_inscripcion_conv_beca AS padre $where"; 
		$adm = toba::db()->consultar_fila($sql);

		$sql                    = "SELECT puntaje FROM be_inscripcion_conv_beca as padre $where";
		$resultado              = toba::db()->consultar_fila($sql);
		$inscripcion['puntaje'] = $resultado['puntaje']; 

		

		/* =============================== DICTAMEN DE COMISION ======================================*/

		$criterios = $this->get_criterios_y_subcriterios_evaluacion_por_convocatoria($inscripcion['id_convocatoria']);

		// Obtengo el dictamen
		$sql               = "SELECT * FROM be_dictamen AS padre $where AND tipo_dictamen = 'C' LIMIT 1";
		$dictamen          = toba::db()->consultar_fila($sql); 
		if ($dictamen) {
			$desglose_puntajes = json_decode($dictamen['desglose_puntajes'], true);
			
			// Re-ordeno los criterios de evaluaci�n, y le agrego las descripciones para mostrar
			$puntajes_asignados = [];
			foreach ($desglose_puntajes as $criterio_puntaje) {
				$id_criterio = $criterio_puntaje['id'];
				
				$indice    = $criterios[$id_criterio]['criterio_evaluacion'];
				$subindice = $criterios[$id_criterio]['subcriterio_evaluacion'];
				
				$puntajes_asignados[$indice]['puntaje_maximo_criterio']  = $criterios[$id_criterio]['puntaje_maximo_criterio'];
				$puntajes_asignados[$indice]['subcriterios'][$subindice] = array_merge($criterios[$id_criterio], ['puntaje_asignado' => $criterio_puntaje['valor']]);
			}

			$dictamen['desglose_puntajes_con_descripciones'] = $puntajes_asignados;
			$datos['comision'] = $dictamen;

			// Obtengo los nombres de los evaluadores
			$datos['comision']['evaluadores'] = array_map(function($nro_documento_evaluador){
				return toba::consulta_php('co_personas')->get_ayn($nro_documento_evaluador);
			}, explode('/', $datos['comision']['evaluadores']) );
		} else {
			$datos['comision'] = null;
		}

		/* =============================== DICTAMEN DE JUNTA =========================================*/

		/* =============================== FALTA DESDE ACA PARA ABAJO ======================================*/
		$sql = "SELECT puntaje_asignado, desglose_puntajes, justificacion_puntajes as justificacion 
				FROM be_dictamen as padre 
				$where 
				AND padre.tipo_dictamen = 'J'";

		$datos['junta'] = toba::db()->consultar_fila($sql);

		if ($datos['junta']) {
			$desglose_puntajes = json_decode($datos['junta']['desglose_puntajes'], true);
			
			// Ordeno los criterios
			$criterios         = $this->get_criterios_evaluacion($inscripcion);
			foreach ($criterios as $criterio) {
				$criterios_evaluacion[$criterio['id_criterio_evaluacion']] = $criterio;
			}

			// Re-ordeno los criterios de evaluaci�n, y le agrego las descripciones para mostrar
			$puntajes_asignados = [];
			foreach ($desglose_puntajes as $criterio_puntaje) {
				$id_criterio = $criterio_puntaje['id'];
				$puntajes_asignados[$id_criterio] = array_merge($criterios_evaluacion[$id_criterio], ['puntaje_asignado' => $criterio_puntaje['valor']]);
			}

			$datos['junta']['desglose_puntajes'] = $puntajes_asignados;
		}

		return array(
			'admisibilidad' => $adm, 
			'dictamen'      => $datos, 
			'inscripcion'   => $inscripcion, 
			'cumplimientos' => $cumplimientos
		);
	}

	function get_orden_merito($filtro = array())
	{
		$where = array();
		if(isset($filtro['id_convocatoria'])){
			$where[] = 'insc.id_convocatoria = '.quote($filtro['id_convocatoria']);
		}
		if(isset($filtro['id_tipo_beca'])){
			$where[] = 'insc.id_tipo_beca = '.quote($filtro['id_tipo_beca']);

			// TODO: si hay mas tipos de beca que tengan cupos distintos, 
			// hay que hacer un refactor de esto. Esto se hizo para salir del apuro (Noviembre 2022)
			// P/D: Si est�s leyendo esto en el futuro, ya sabr�s como nos fue en el mundial 
			// (hoy perdimos contra Arabia Saudita, un baj�n) 
			if (!empty($filtro['cupo_beca'])) {
				if ($filtro['cupo_beca'] == 'D' && $filtro['id_tipo_beca'] == 11) {
					$where[] = "insc.informacion_interna ILIKE '%\"subtipo_beca\":\"D\"%'";
				}
				if ($filtro['cupo_beca'] == 'G' && $filtro['id_tipo_beca'] == 11) {
					$where[] = "insc.informacion_interna ILIKE '%\"subtipo_beca\":\"G\"%'";
				}
			}
		}
		if(isset($filtro['postulante'])){
			$where[] = 'per.apellido||per.nombres ilike '.quote('%'.$filtro['postulante'].'%');
		}
		if(isset($filtro['id_area_conocimiento'])){
			$where[] = 'insc.id_area_conocimiento = '.quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['id_dependencia'])){
			$where[] = 'insc.id_dependencia = '.quote($filtro['id_dependencia']);
		}
		if(isset($filtro['lugar_trabajo_becario'])){
			$where[] = 'insc.lugar_trabajo_becario = '.quote($filtro['lugar_trabajo_becario']);
		}
		if(isset($filtro['beca_otorgada'])){
			if ($filtro['beca_otorgada'] == 'N') {
				$where[] = 'not exists (select * from be_becas_otorgadas where nro_documento = insc.nro_documento and id_convocatoria = insc.id_convocatoria and id_tipo_beca = insc.id_tipo_beca)';
			}
		}
		

		$sql = "SELECT  insc.id_convocatoria,
		    		insc.id_tipo_beca,
		    		CASE (SELECT suma_puntaje_academico FROM be_tipos_beca WHERE id_tipo_beca = insc.id_tipo_beca)
		    			WHEN 'S' THEN insc.puntaje
		    			ELSE 0 END AS puntaje_academico,
		    		insc.id_area_conocimiento,
		    		insc.beca_otorgada,
		    		bo.fecha_desde,
		    		bo.fecha_hasta,
		    		bo.fecha_toma_posesion,
		    		bo.nro_resol,
		    		bo.estado AS estado_beca_otorgada,
		    		CASE bo.estado 
		    			WHEN 'A' THEN 'Otorgada' 
		    			WHEN 'B' THEN 'Baja' 
		    			ELSE 'No otorgada' END AS estado_beca_otorgada_desc,
		    		ac.nombre AS area_conocimiento,
		    		per.nro_documento,
		    		per.cuil,
		    		per.apellido,
		    		per.nombres,
		            upper(per.apellido)||', '||initcap(per.nombres) AS postulante,
		            tipbec.tipo_beca,
		            dep.nombre AS facultad,
		            dep.id AS id_dependencia,
		            lugtrab.nombre AS lugar_trabajo,
		            CASE WHEN insc.id_tipo_beca > 1 THEN lugtrab.nombre ELSE dep.nombre END AS lugar,
		            insc.lugar_trabajo_becario,
		            dir.apellido||', '||dir.nombres AS director,
		            --Cuando no se considera el puntaje acad�mica, se retorna la leyenda 'No corresponde'
		            CASE WHEN ((SELECT suma_puntaje_academico FROM be_tipos_beca WHERE id_tipo_beca = insc.id_tipo_beca) = 'N') 
		            		THEN 'No corresponde' 
		            		ELSE insc.puntaje::varchar END AS puntaje,
					--Puntaje de Comisi�n
					dictamen_comision.puntaje_asignado AS puntaje_comision,
					-- Puntaje junta
					dictamen_junta.puntaje_asignado AS puntaje_junta,
					-- Puntaje Total
					(CASE WHEN (dictamen_junta.puntaje_asignado IS NOT null) 
						THEN insc.puntaje + dictamen_junta.puntaje_asignado::decimal 
					    ELSE insc.puntaje + dictamen_comision.puntaje_asignado::decimal END
					)::decimal AS puntaje_final
		    FROM be_inscripcion_conv_beca AS insc
		    LEFT JOIN sap_personas AS per on per.nro_documento = insc.nro_documento
		    LEFT JOIN sap_personas AS dir on dir.nro_documento = insc.nro_documento_dir
		    LEFT JOIN be_tipos_beca AS tipbec on tipbec.id_tipo_beca = insc.id_tipo_beca
		    LEFT JOIN sap_dependencia AS dep on dep.id = insc.id_dependencia
		    LEFT JOIN sap_dependencia AS lugtrab on lugtrab.id = insc.lugar_trabajo_becario
		    LEFT JOIN sap_area_conocimiento AS ac on ac.id = insc.id_area_conocimiento
		    LEFT JOIN be_dictamen AS dictamen_comision ON 
		    	dictamen_comision.id_convocatoria = insc.id_convocatoria AND
		    	dictamen_comision.id_tipo_beca    = insc.id_tipo_beca AND
		    	dictamen_comision.nro_documento   = insc.nro_documento AND 
		    	dictamen_comision.tipo_dictamen   = 'C' 
		    LEFT JOIN be_dictamen AS dictamen_junta ON 
		    	dictamen_junta.id_convocatoria    = insc.id_convocatoria AND
		    	dictamen_junta.id_tipo_beca       = insc.id_tipo_beca AND
		    	dictamen_junta.nro_documento      = insc.nro_documento AND
		    	dictamen_junta.tipo_dictamen      = 'J'
		    LEFT JOIN be_becas_otorgadas AS bo ON 
		    	bo.id_convocatoria  = insc.id_convocatoria AND
		    	bo.id_tipo_beca     = insc.id_tipo_beca AND
		    	bo.nro_documento    = insc.nro_documento
		    where insc.estado  = 'C'
		    and insc.admisible = 'S'
		    AND dictamen_comision.puntaje_asignado IS NOT null";

			if(isset($filtro['campos_ordenacion'])){
				foreach ($filtro['campos_ordenacion'] as $campo => $orden) {
					$criterios_orden[] = $campo." ".$orden;
				}
				$sql .= " order by ".implode(', ',$criterios_orden);
			}else{
				$sql .= " order by puntaje_final DESC";
			}

			
		if(isset($filtro['cantidad_becas']) && is_numeric($filtro['cantidad_becas']) ){
			$sql .= " LIMIT ".$filtro['cantidad_becas'];	
		}
			
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
		
	}

	/**
	 * Recibe un listado de criterios y subcriterios (tal como se almacena en la BD) y los oderna 
	 * para que sea mas sencillo su acceso y recorrido
	 *
	 * @param array $criterios
	 *
	 * @return array
	 */
	public function ordenar_criterios_y_subcriterios($criterios)
	{
		$resultado = [];
		foreach ($criterios as $criterio) {
			$id_criterio    = $criterio['id_criterio_evaluacion'];
			$id_subcriterio = $criterio['id_subcriterio_evaluacion'];

			if (!isset($resultado[$id_criterio])) {
				$resultado[$id_criterio] = [
					'criterio_puntaje_maximo' => $criterio['puntaje_maximo'],
					'criterio_descripcion'    => $criterio['criterio_evaluacion'],
				];
			}
			$resultado[$id_criterio]['subcriterios'][$id_subcriterio] = [
				'subcriterio_puntaje_maximo' => $criterio['maximo'],
				'subcriterio_descripcion'    => $criterio['descripcion'],
			];
		}
		return $resultado;
	}

	/**
	 * Recibe un json de desglose de puntajes, tal como se guarda en la BD, 
	 * y lo traduce a sus descripciones correspondientes
	 *
	 * @param  int    $id_convocatoria
	 * @param  int    $id_tipo_beca
	 * @param  string $json_desglose_puntajes
	 *
	 * @return array
	 */
	public function describir_desglose_puntajes($id_convocatoria, $id_tipo_beca, $json_desglose_puntajes)
	{
		$criterios = $this->get_criterios_y_subcriterios($id_convocatoria, $id_tipo_beca);
		$criterios = $this->ordenar_criterios_y_subcriterios($criterios);
		$puntajes  = [];

		// Convierto el json en array y empiezo a reemplazar uno por uno, el ID por su descripci�n
		$desglose_puntajes  = json_decode($json_desglose_puntajes, true);
		$puntajes_asignados = [];

		foreach ($desglose_puntajes as $puntaje) {
			$ids         = explode('-', $puntaje['id']);
			$criterio_id = $ids[0];
			
			// Se valida la existencia de subcriterio porque en dict�menes de junta no existe (eval�an solo el criterio general)
			if (isset($ids[1])) {
				$subcriterio_id             = isset($ids[1]) ? $ids[1] : '';
				$subcriterio_descripcion    = $criterios[$id_criterio]['subcriterios'][$id_subcriterio]['subcriterio_descripcion'];
			}

			$puntajes[] = [
				'criterio_id'             => $criterio_id,
				'criterio_descripcion'    => $criterios[$criterio_id]['criterio_descripcion'],
				'subcriterio_id'          => $subcriterio_id,
				'subcriterio_descripcion' => $subcriterio_descripcion,
				'puntaje_asignado'        => $puntaje['valor'],
			];
		}
		return $puntajes;
	}

	/*$sql = "SELECT 
					(SELECT puntaje FROM be_inscripcion_conv_beca WHERE nro_documento = dic.nro_documento AND id_tipo_beca = dic.id_tipo_beca AND id_convocatoria = dic.id_convocatoria) AS puntaje_inicial,
					(SELECT sum(puntaje) FROM be_dictamen_detalle WHERE nro_documento = dic.nro_documento AND id_tipo_beca = dic.id_tipo_beca AND id_convocatoria = dic.id_convocatoria AND tipo_dictamen = 'C') AS puntaje_comision,
					(SELECT justificacion_puntajes FROM be_dictamen WHERE nro_documento = dic.nro_documento AND id_tipo_beca = dic.id_tipo_beca AND id_convocatoria = dic.id_convocatoria AND tipo_dictamen = 'C') AS justificacion_comision,
					(SELECT sum(puntaje) FROM be_dictamen_detalle WHERE nro_documento = dic.nro_documento AND id_tipo_beca = dic.id_tipo_beca AND id_convocatoria = dic.id_convocatoria AND tipo_dictamen = 'J') AS puntaje_junta,
					(SELECT justificacion_puntajes FROM be_dictamen WHERE nro_documento = dic.nro_documento AND id_tipo_beca = dic.id_tipo_beca AND id_convocatoria = dic.id_convocatoria AND tipo_dictamen = 'J') AS justificacion_junta,
					array_to_string( 
					    (SELECT array_agg(upper(apellido)||', '||nombres) AS evaluador 
					    FROM sap_personas 
					    WHERE nro_documento = ANY (string_to_array(dic.evaluadores,'/'))) 
					,'/') AS evaluadores
				FROM be_dictamen AS dic
				WHERE dic.nro_documento = ".quote($inscripcion['nro_documento'])."
				AND dic.id_tipo_beca    = ".quote($inscripcion['id_tipo_beca'])."
				AND dic.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND tipo_dictamen = 'C'";*/

	

}
?>