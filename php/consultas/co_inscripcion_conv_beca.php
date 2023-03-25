<?php
class co_inscripcion_conv_beca
{
	function existe_beca_otorgada($nro_documento, $id_convocatoria, $id_tipo_beca){
		$sql = "SELECT *
				FROM be_becas_otorgadas
				WHERE id_convocatoria = ".quote($id_convocatoria)."
				AND id_tipo_beca = ".quote($id_tipo_beca)."
				AND nro_documento = ".quote($nro_documento)."
				LIMIT 1";

		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado !== FALSE);
	}

	function existe_inscripcion($nro_documento, $id_convocatoria, $id_tipo_beca){
		$sql = "SELECT *
				FROM be_inscripcion_conv_beca 
				WHERE id_convocatoria = ".quote($id_convocatoria)."
				AND id_tipo_beca = ".quote($id_tipo_beca)."
				AND nro_documento = ".quote($nro_documento)."
				LIMIT 1";

		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado !== FALSE);
	}

	function get_antecedentes_postulante($nro_documento)
	{
		$dni = quote($nro_documento);
		$sql = "SELECT cargo, anio_ingreso, anio_egreso, doc_probatoria 
				FROM be_antec_activ_docentes 
				WHERE nro_documento = $dni";
		$antec['activ_docentes'] = toba::db()->consultar($sql);

		$sql = "SELECT tipo_beca, fecha_desde, fecha_hasta, doc_probatoria 
				FROM be_antec_becas_obtenidas 
				WHERE nro_documento = $dni";
		$antec['becas_obtenidas'] = toba::db()->consultar($sql);

		$sql = "SELECT idioma, doc_probatoria 
				FROM be_antec_conoc_idiomas 
				WHERE nro_documento = $dni";
		$antec['conoc_idiomas'] = toba::db()->consultar($sql);

		$sql = "SELECT tema, fecha, doc_probatoria 
				FROM be_antec_cursos_perfec_aprob 
				WHERE nro_documento = $dni";
		$antec['cursos_perfec_aprob'] = toba::db()->consultar($sql);

		$sql = "SELECT titulo, anio_desde, anio_hasta, doc_probatoria 
				FROM be_antec_estudios_afines 
				WHERE nro_documento = $dni";
		$antec['estudios_afines'] = toba::db()->consultar($sql);

		$sql = "SELECT actividad, substr(titulo_tema,0,75) AS titulo_tema, doc_probatoria  
				FROM be_antec_otras_actividades 
				WHERE nro_documento = $dni";
		$antec['otras_actividades'] = toba::db()->consultar($sql);

		$sql = "SELECT institucion, fecha, doc_probatoria 
				FROM be_antec_particip_dict_cursos 
				WHERE nro_documento = $dni";
		$antec['part_dict_cursos'] = toba::db()->consultar($sql);

		$sql = "SELECT substr(titulo_trabajo,0,75) AS titulo_trabajo, fecha, doc_probatoria
				FROM be_antec_present_reuniones 
				WHERE nro_documento = $dni";
		$antec['presentacion_reuniones'] = toba::db()->consultar($sql);

		$sql = "SELECT substr(datos_publicacion,0,75) AS datos_publicacion, fecha, doc_probatoria 
				FROM be_antec_trabajos_publicados 
				WHERE nro_documento = $dni";
		$antec['trabajos_publicados'] = toba::db()->consultar($sql);

		return $antec;

	}

	function get_estado_aval_solicitud($inscripcion){
		$sql = "SELECT 
				av.aval_secretaria, 
				av.aval_decanato, 
				av.aval_director, 
				av.aval_dir_proyecto,
				director_avalo.apellido||', '||director_avalo.nombres AS director_avalo_desc,
				dir_proyecto_avalo.apellido||', '||dir_proyecto_avalo.nombres AS dir_proyecto_avalo_desc,
				secretario_avalo.apellido||', '||secretario_avalo.nombres AS secretario_avalo_desc,
				decano_avalo.apellido||', '||decano_avalo.nombres AS decano_avalo_desc
			FROM be_inscripcion_avales AS av
			LEFT JOIN sap_personas AS director_avalo ON director_avalo.nro_documento = av.director_avalo
			LEFT JOIN sap_personas AS dir_proyecto_avalo ON dir_proyecto_avalo.nro_documento = av.dir_proyecto_avalo
			LEFT JOIN sap_personas AS secretario_avalo ON secretario_avalo.nro_documento = av.secretario_avalo
			LEFT JOIN sap_personas AS decano_avalo ON decano_avalo.nro_documento = av.decano_avalo
			WHERE av.nro_documento = ".quote($inscripcion['nro_documento'])."
			AND av.id_tipo_beca    = ".quote($inscripcion['id_tipo_beca'])."
			AND av.id_convocatoria = ".quote($inscripcion['id_convocatoria']);
		return toba::db()->consultar_fila($sql);
	
	}

	function get_inscripcion($id_convocatoria, $id_tipo_beca, $nro_documento)
	{
		$sql = "SELECT * 
				FROM be_inscripcion_conv_beca AS insc 
				WHERE insc.id_convocatoria = " . quote($id_convocatoria) . "
				AND   insc.id_tipo_beca    = " . quote($id_tipo_beca) . "
				AND   insc.nro_documento   = " . quote($nro_documento);
		return toba::db()->consultar_fila($sql);
	}

	function get_inscripciones($filtro = array())
	{
		$where = array();
		if(isset($filtro['id_tipo_doc'])){
			$where[] = 'becario.id_tipo_doc = '.quote($filtro['id_tipo_doc']);	
		}
		if(isset($filtro['nro_documento'])){
			$where[] = 'insc.nro_documento = '.quote($filtro['nro_documento']);	
		}
		if(isset($filtro['postulante'])){
			$where[] = '( 
				(quitar_acentos(becario.apellido) ||\'%\'|| quitar_acentos(becario.nombres)) ILIKE quitar_acentos('.quote('%' . str_replace(' ','%', $filtro['postulante']).'%').")
				OR 
				(insc.nro_documento = " . quote($filtro['postulante']) . ")
				)";
		}
		if(isset($filtro['director'])){
			$where[] = '(
				nro_documento_dir IN (SELECT nro_documento FROM sap_personas WHERE (quitar_acentos(apellido) ||\'%\'|| quitar_acentos(nombres)) ILIKE quitar_acentos('.quote('%' . str_replace(' ','%', $filtro['director']).'%').'))
				OR nro_documento_codir        IN (SELECT nro_documento FROM sap_personas WHERE (quitar_acentos(apellido) ||\'%\'|| quitar_acentos(nombres)) ILIKE quitar_acentos('.quote('%' . str_replace(' ','%', $filtro['director']).'%').'))
				OR nro_documento_subdir       IN (SELECT nro_documento FROM sap_personas WHERE (quitar_acentos(apellido) ||\'%\'|| quitar_acentos(nombres)) ILIKE quitar_acentos('.quote('%' . str_replace(' ','%', $filtro['director']).'%')."))
				)";
		}

		if(isset($filtro['id_convocatoria'])){
			$where[] = 'insc.id_convocatoria = '.quote($filtro['id_convocatoria']);	
		}
		if(isset($filtro['id_area_conocimiento'])){
			$where[] = 'area.id = '.quote($filtro['id_area_conocimiento']);	
		}
		if(isset($filtro['id_tipo_beca'])){
			$where[] = 'insc.id_tipo_beca = '.quote($filtro['id_tipo_beca']);	
		}
		if(isset($filtro['nro_carpeta'])){
			$where[] = 'lower(insc.nro_carpeta) = lower('.quote($filtro['nro_carpeta']).")";	
		}
		if(isset($filtro['id_tipo_convocatoria'])){
			$where[] = 'tip_con.id_tipo_convocatoria = '.quote($filtro['id_tipo_convocatoria']);	
		}
		if(isset($filtro['id_dependencia'])){
			$where[] = "((insc.id_tipo_beca = 1 AND id_dependencia = " . quote($filtro['id_dependencia']) . ") 
						OR 
			            (insc.id_tipo_beca > 1 AND lugar_trabajo_becario = " . quote($filtro['id_dependencia']) . "))";
		}
		if(isset($filtro['admisible'])){
			if($filtro['admisible'] == 'P'){
				$where[] = 'insc.admisible IS null';
			}else{
				$where[] = 'insc.admisible = '.quote($filtro['admisible']);	
			}
			
		}
		if(isset($filtro['beca_otorgada'])){
			$where[] = "insc.beca_otorgada = ".quote($filtro['beca_otorgada']);
		}
		if(isset($filtro['estado'])){
			$where[] = 'insc.estado = '.quote($filtro['estado']);	
		}
		if(isset($filtro['estado_evaluacion'])){
			$clausula = ($filtro['estado_evaluacion'] == 'N') ? 'IS NULL' : 'IS NOT NULL';
			$where[] = "dictamen_comision.puntaje_asignado $clausula";	
		}
		if(isset($filtro['evaluado_junta'])){
			$clausula = ($filtro['evaluado_junta'] == 'N') ? 'IS NULL' : 'IS NOT NULL';
			$where[] = "dictamen_junta.puntaje_asignado $clausula"; 
		}
		// Una postulación es "evaluable" por cualquiera de las comisiones hasta el momento en que
		// se otorgan las becas de esa convocatoria. Una vez que sale la resolución de otorgamiento
		// no deben poder modificarse los puntajes asignados
		if(isset($filtro['evaluable_por_comisiones']) && $filtro['evaluable_por_comisiones']){
			$where[] = 'insc.id_convocatoria NOT IN (SELECT id_convocatoria FROM be_becas_otorgadas)';
		}

		if (isset($filtro['con_dictamen_comision']) && $filtro['con_dictamen_comision']) {
			$where[] = "dictamen_comision.puntaje_asignado IS NOT NULL";
		}

		// TODO: Sacar esto despues del 24 de febrero. Es para permitir SOLAMENTE la 
		// evaluación de dos postulaciones que pidieron reconsideración
		if (isset($filtro['con_dictamen_comision']) && $filtro['con_dictamen_comision']) {
			$where[] = 'insc.nro_documento IN (\'37698692\',\'36194692\')';
		}

		$sql = "
			SELECT
				insc.id_dependencia,
				dep.nombre as dependencia,
				insc.id_tipo_beca,
				tip_bec.tipo_beca,
				insc.nro_documento,
				initcap(becario.apellido)||', '||initcap(becario.nombres) as becario,
				(select localidad from be_localidades where id_localidad = becario.id_localidad) as residencia,
				becario.cuil as becario_cuil,
				becario.mail as mail_becario,
				insc.nro_documento_dir,
				initcap(director.apellido)||', '||initcap(director.nombres) as director,
				director.mail as mail_director,
				insc.nro_documento_codir,
				initcap(codirector.apellido)||', '||initcap(codirector.nombres) as codirector,
				insc.nro_documento_subdir,
				initcap(subdirector.apellido)||', '||initcap(subdirector.nombres) as subdirector,
				becario.id_tipo_doc,
				insc.id_convocatoria,
				conv.convocatoria,
				insc.fecha_hora,
				insc.admisible,
				case (insc.admisible) when 'S' then 'Admitido' when 'N' then 'No admitido' else 'Sin admisibilidad' end as admisible_desc,
				insc.puntaje,
				insc.beca_otorgada,
				area.nombre as area_conocimiento,
				insc.id_area_conocimiento,
				insc.titulo_plan_beca,
				insc.justif_codirector,
				carr.carrera as carrera,
				insc.materias_plan,
				insc.materias_aprobadas,
				insc.prom_hist_egresados,
				insc.prom_hist,
				insc.carrera_posgrado,
				insc.nombre_inst_posgrado,
				insc.titulo_carrera_posgrado,
				insc.archivo_insc_posgrado,
				insc.area_trabajo,
				insc.nro_carpeta,
				insc.observaciones,
				insc.estado,
				insc.id_proyecto,
				case insc.estado when 'A' then 'Abierta' when 'C' then 'Cerrada' else 'No definido' end as estado_desc,
				insc.cant_fojas,
				insc.es_titular,
				lugtrab.nombre AS lugar_trabajo_becario,
				dictamen_comision.puntaje_asignado as puntaje_comision,
				dictamen_junta.puntaje_asignado as puntaje_junta,
				CASE WHEN dictamen_comision.puntaje_asignado IS NULL THEN 'N' else 'S' end as evaluado_comision,
				CASE WHEN dictamen_junta.puntaje_asignado    IS NULL THEN 'N' else 'S' end as evaluado_junta
			FROM be_inscripcion_conv_beca as insc
			LEFT JOIN be_convocatoria_beca as conv on conv.id_convocatoria = insc.id_convocatoria
			LEFT JOIN sap_personas as becario on becario.nro_documento = insc.nro_documento
			LEFT JOIN sap_personas as director ON director.nro_documento = insc.nro_documento_dir
			LEFT JOIN sap_personas as codirector ON codirector.nro_documento = insc.nro_documento_codir
			LEFT JOIN sap_personas as subdirector ON subdirector.nro_documento = insc.nro_documento_subdir
			LEFT JOIN be_tipos_beca as tip_bec on tip_bec.id_tipo_beca = insc.id_tipo_beca
			LEFT JOIN be_tipos_convocatoria as tip_con ON (tip_con.id_tipo_convocatoria = tip_bec.id_tipo_convocatoria AND tip_con.id_tipo_convocatoria = conv.id_tipo_convocatoria)
			LEFT JOIN sap_dependencia as dep ON (insc.id_dependencia = dep.id)
			LEFT JOIN sap_area_conocimiento as area ON (insc.id_area_conocimiento = area.id)
			LEFT JOIN be_carreras as carr ON (insc.id_carrera = carr.id_carrera)
			LEFT JOIN sap_dependencia AS lugtrab ON lugtrab.id = insc.lugar_trabajo_becario 
			LEFT JOIN be_dictamen AS dictamen_comision ON dictamen_comision.id_convocatoria = insc.id_convocatoria AND dictamen_comision.id_tipo_beca = insc.id_tipo_beca AND dictamen_comision.nro_documento = insc.nro_documento AND dictamen_comision.tipo_dictamen = 'C'
			LEFT JOIN be_dictamen AS dictamen_junta    ON dictamen_junta.id_convocatoria    = insc.id_convocatoria AND dictamen_junta.id_tipo_beca    = insc.id_tipo_beca AND dictamen_junta.nro_documento    = insc.nro_documento AND dictamen_junta.tipo_dictamen    = 'J'";

		if(isset($filtro['campos_ordenacion'])){
			$sql .= " ORDER BY ".$filtro['campos_ordenacion'];
		}else{
			$sql .= " ORDER BY admisible,becario";
		}
		
		if(count($where)){
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	function get_requisitos_insc($id_convocatoria,$id_tipo_beca,$nro_documento)
	{
		$sql = "SELECT 	req_con.id_requisito, 
				        req_con.requisito, 
				        case req_con.obligatorio when 'S' then 'SI' when 'N' then 'NO' end AS obligatorio, 
				        req_ins.cumplido, 
				        req_ins.fecha
				FROM be_requisitos_insc AS req_ins
				LEFT JOIN be_requisitos_convocatoria AS req_con on req_ins.id_requisito = req_con.id_requisito
				WHERE req_ins.nro_documento = ".quote($nro_documento)."
				AND req_ins.id_tipo_beca = ".quote($id_tipo_beca)."
				AND req_con.id_convocatoria = ".quote($id_convocatoria)."
				order by req_con.id_requisito";
		return toba::db()->consultar($sql);
	}

	function get_requisitos_iniciales($id_convocatoria)
	{
		$sql = "SELECT 	id_requisito, 
						id_convocatoria,
						'N' as cumplido,
						null as fecha 
				FROM be_requisitos_convocatoria 
				WHERE id_convocatoria = ".quote($id_convocatoria);
		return toba::db()->consultar($sql);
	}

	/**
	 * Obtiene un numero de carpeta que no est?en uso. El nombre de la funci? se debe a que anteriormente la funci? devolv? el ?ltimo n?mero que se hab? asignado (con la intenci? de incrementarlo en una unidad al nuevo registro). Despues, la funci? fue modificada para que, en caso de eliminarse una inscripci? a beca, y exista un "hueco" entre los n?meros de carpeta, esta funci? pueda re-asignarlos y lograr una secuencia limpia.
	 * Es posible que esta funci? falle (
	 * asigne dos numeros de carpeta a un mismo proyecto) cuando se guardan simultaneamente. Se deja a prueba de la primera convocatoria (A? 2018)
	 * @param integer $id_convocatoria 
	 * @param integer $id_tipo_beca 
	 * @return String String formateado para asignar como n?mero de carpeta a una nueva inscripci?.
	 */
	
	function get_ultimo_nro_carpeta($id_convocatoria,$id_tipo_beca)
	{
		//se obtiene el prefijo de carpeta para el tipo de beca actual
		$prefijo = toba::consulta_php('co_becas')->get_campo('be_tipos_beca', 'prefijo_carpeta', ['id_tipo_beca' => $id_tipo_beca]);
		if(!$prefijo){
			throw new toba_error("No se ha definido un prefijo de carpeta para el tipo de beca seleccionado. Por favor, pongase en contacto con la Secretar�a General de Ciencia y T�cnica");
		}
		// Esta consulta retorna un string con formato json conteniendo todos los numeros de carpetas existentes.
		$sql = "SELECT array_to_json(array_agg(nro_carpeta)) as existentes
				FROM be_inscripcion_conv_beca
				WHERE id_convocatoria = ".quote($id_convocatoria)."
				AND id_tipo_beca = ".quote($id_tipo_beca);

		$resultado = toba::db()->consultar_fila($sql);

		if(count($resultado)){
			//convierto el resultado de la consulta a un array
			$existentes = json_decode($resultado['existentes']);

			//una vez que tengo todos los numeros de carpeta existentes en un array, lo recorro hasta encontrar uno que no exista. Esto permite llenar los "huecos" generados por la eliminaci? de numeros de carpetas.
			for($i=1;$i<9999;$i++){
				//genero el numero de tres digitos con relleno de ceros: 001, 002, 003, etc...
				$nro = sprintf("%'.03d",$i);
				//lo concateno con el prefijo de carpeta de la beca seleccionada
				$nro_carpeta = $prefijo."-".$nro;

				//si ya existe, contin?o el bucle hasta encontrar uno que no existe
				if(is_array($existentes)){
					if(in_array($nro_carpeta,$existentes)){
						continue;
					}else{
						//si no existe, BINGO!! Se retorna ese numero de carpeta para ser utilizado
						return $nro_carpeta;
					}
				}else{
					return $prefijo."-001";
				}
			}
		}else{
			//si no obtuve resultados, es porque se est?cargando el primer numero de carpeta
			return $prefijo."-001";
		}
	}
	function get_estado_solicitud($id_convocatoria,$id_tipo_beca,$nro_documento){
		$sql = "SELECT estado 
				FROM be_inscripcion_conv_beca 
				WHERE id_convocatoria = ".quote($id_convocatoria)."
				AND id_tipo_beca = ".quote($id_tipo_beca)."
				AND nro_documento = ".quote($nro_documento);

		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['estado'];
	}

	function get_campo($campos,$filtro = array())
	{
		$where = array();
		if(count($filtro)){
			foreach($filtro as $campo => $valor){
				$where[] = $campo." = ".quote($valor);
			}	
		}
		if(!is_array($campos)){
			$campos = array($campos);
		}
		$sql = "SELECT ".implode(',',$campos)." FROM be_inscripcion_conv_beca";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	/**
	 * Obtiene todos los datos necesarios para generar el comprobante de inscripci? que presenta el alumno en papel, con los avales de las autoridades necesarias.
	 * @param array $inscripcion Array que contiene el id_convocatoria, el id_tipo_beca y el nro_documento que identifican a una inscripci?
	 * @return array
	 */
	function get_detalles_comprobante($inscripcion = array())
	{
		$detalles = array();
		/* ============================================================================================== */
		$sql = "SELECT 
					per.apellido,
					per.nombres,
					insc.nro_documento,
					insc.id_dependencia,
					dep.nombre as nombre_dependencia,
					coalesce(per.cuil,'No declarado') AS cuil,
					per.fecha_nac,
					coalesce(per.celular,'No declarado') AS celular,
					coalesce(per.telefono,'No declarado') AS telefono,
					per.mail,
					insc.prom_hist_egresados, 
					insc.prom_hist,
					insc.nro_documento_codir,
					insc.nro_documento_subdir,
					insc.justif_codirector,
					insc.justif_subdirector,
					insc.materias_plan,
					insc.materias_aprobadas,
					insc.puntaje,
					insc.informacion_interna,
					carr.carrera,
					dictamen_comision.puntaje_asignado AS puntaje_comision,
					dictamen_junta.puntaje_asignado AS puntaje_junta
				FROM be_inscripcion_conv_beca AS insc
				LEFT JOIN be_dictamen AS dictamen_comision ON dictamen_comision.id_convocatoria = insc.id_convocatoria AND dictamen_comision.id_tipo_beca = insc.id_tipo_beca AND dictamen_comision.nro_documento = insc.nro_documento AND dictamen_comision.tipo_dictamen = 'C'
				LEFT JOIN be_dictamen AS dictamen_junta    ON dictamen_junta.id_convocatoria    = insc.id_convocatoria AND dictamen_junta.id_tipo_beca    = insc.id_tipo_beca AND dictamen_junta.nro_documento    = insc.nro_documento AND dictamen_junta.tipo_dictamen    = 'J'
				LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
				LEFT JOIN sap_dependencia AS dep ON dep.id = insc.id_dependencia
				LEFT JOIN be_carreras AS carr ON carr.id_carrera = insc.id_carrera
				WHERE per.nro_documento = ".quote($inscripcion['nro_documento'])."
				AND insc.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND insc.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
				AND insc.estado <> 'A'
				LIMIT 1";
		$detalles['postulante'] = toba::db()->consultar_fila($sql);

		/* ============================================================================================== */
		$sql = "SELECT insc.nro_carpeta, 
						insc.id_convocatoria,
						insc.id_tipo_beca,
						insc.informacion_interna AS insc_informacion_interna,
						conv.convocatoria, 
						tipbec.tipo_beca, 
						areacon.nombre AS area_conocimiento, 
						insc.titulo_plan_beca,
						insc.lugar_trabajo_becario AS lugar_trabajo_becario_id,
						lugtrab.nombre AS lugar_trabajo_becario,
						insc.area_trabajo,
						tipbec.requiere_insc_posgrado,
						tipbec.suma_puntaje_academico,
						tipbec.puntaje_academico_maximo,
						gr.denominacion as denominacion_grupo,
						gr.fecha_inicio as fecha_inicio_grupo,
						gr.fecha_fin as fecha_fin_grupo
				FROM be_inscripcion_conv_beca AS insc
				LEFT JOIN sap_grupo AS gr on gr.id_grupo = insc.id_grupo
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = insc.id_convocatoria
				LEFT JOIN be_tipos_beca AS tipbec ON tipbec.id_tipo_beca = insc.id_tipo_beca
				LEFT JOIN sap_area_conocimiento AS areacon ON areacon.id = insc.id_area_conocimiento
				LEFT JOIN sap_dependencia AS lugtrab ON lugtrab.id = insc.lugar_trabajo_becario
				WHERE insc.nro_documento = ".quote($inscripcion['nro_documento'])."
				AND insc.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND insc.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
				AND insc.estado <> 'A'
				LIMIT 1";
		$detalles['beca'] = toba::db()->consultar_fila($sql);
		/* ============================================================================================== */
		$sql = "SELECT cri.id_convocatoria,
						cri.id_tipo_beca,
						cri.id_criterio_evaluacion,
						cri.puntaje_maximo,
						cri.criterio_evaluacion
				FROM be_tipo_beca_criterio_eval AS cri
				WHERE id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND id_tipo_beca = ".quote($inscripcion['id_tipo_beca']);
		$detalles['criterios_eval'] = toba::db()->consultar($sql);



		/* ============================================================================================== */
		/*-------------- PROMEDIOS (SE OBTIENE DE LA VARIABLE $persona) ---------------------
		- promedio_hist_carrera
		- promedio_hist_alumno */

		$detalles['promedio'] = array('prom_hist_egresados' => $detalles['postulante']['prom_hist_egresados'],
										'prom_hist'         => $detalles['postulante']['prom_hist']);
		
		/* ============================================================================================== */
		$detalles['director'] = $this->get_detalles_director($inscripcion);

		/* ============================================================================================== */
		if($detalles['postulante']['nro_documento_codir']){
			$detalles['codirector'] = $this->get_detalles_director($inscripcion,'codir');	
		}

		/* ============================================================================================== */
		if($detalles['postulante']['nro_documento_subdir']){
			$detalles['subdirector'] = $this->get_detalles_director($inscripcion,'subdir');	
		}

		/* ============================================================================================== */
		$sql = "SELECT proy.descripcion AS proyecto,
						proy.id,
						proy.codigo, 
						per.nro_documento,
						per.apellido, 
						per.nombres,
						proy.fecha_hasta
				FROM be_inscripcion_conv_beca AS insc
				LEFT JOIN sap_proyectos AS proy ON proy.id = insc.id_proyecto
				LEFT JOIN sap_proyecto_integrante AS pint 
					ON pint.id_proyecto = proy.id
					AND pint.id_funcion = (SELECT id_funcion FROM sap_proyecto_integrante_funcion WHERE identificador_perfil = 'D')
					AND pint.fecha_desde = (SELECT max(fecha_desde) FROM sap_proyecto_integrante WHERE id_funcion = pint.id_funcion AND id_proyecto = proy.id)
				LEFT JOIN sap_personas AS per ON per.nro_documento = pint.nro_documento
				WHERE insc.nro_documento = ".quote($inscripcion['nro_documento'])."
					AND insc.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
					AND insc.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
					AND insc.estado <> 'A'
					LIMIT 1";
		$detalles['proyecto'] = toba::db()->consultar_fila($sql);

		/* ============================================================================================== */

		$detalles['generales'] = [
			'nombre_secretario'   => toba::consulta_php('co_tablas_basicas')->get_parametro_conf('nombre_secretario'),
			'genero_secretario'   => toba::consulta_php('co_tablas_basicas')->get_parametro_conf('genero_secretario'),
			'direccion_mail_rrhh' => toba::consulta_php('co_tablas_basicas')->get_parametro_conf('direccion_mail_rrhh'),
		];
		
		return $detalles;

		
	}

	function get_detalles_director($inscripcion, $tipo = 'dir')
	{
		$sql = "SELECT 
					insc.nro_documento_".$tipo." AS nro_documento, 
					per.apellido, 
					per.nombres, 
					per.cuil, 
					coalesce(per.celular,'No declarado') as celular, 
					coalesce(per.telefono,'No declarado') as telefono,
					coalesce(per.mail,'No declarado') as mail,
					nivac.nivel_academico,
					coalesce(catcon.cat_conicet,'No declarado') as catconicet,
					coalesce(catconper.lugar_trabajo,'No declarado') as lugar_trabajo_conicet,
					catinc.categoria AS catinc,
					coalesce(catinc.convocatoria) AS catinc_conv
					FROM be_inscripcion_conv_beca AS insc
					LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento_".$tipo."
					LEFT JOIN be_niveles_academicos AS nivac ON nivac.id_nivel_academico = per.id_nivel_academico
					LEFT JOIN be_cat_conicet_persona AS catconper ON catconper.nro_documento = per.nro_documento
					LEFT JOIN be_cat_conicet AS catcon ON catcon.id_cat_conicet = catconper.id_cat_conicet
					LEFT JOIN sap_cat_incentivos AS catinc ON catinc.nro_documento = per.nro_documento 
					                                      AND catinc.convocatoria  = (SELECT MAX(convocatoria) FROM sap_cat_incentivos WHERE nro_documento = per.nro_documento)
					WHERE insc.nro_documento = ".quote($inscripcion['nro_documento'])."
					AND insc.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
					AND insc.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
					AND insc.estado <> 'A'
					LIMIT 1";
		return toba::db()->consultar_fila($sql);

	}

	public function abrir_solicitud($filtro=array())
	{
		if(isset($filtro['nro_documento']) && isset($filtro['id_convocatoria']) && isset($filtro['id_tipo_beca'])){
			$sql_abrir = "UPDATE be_inscripcion_conv_beca SET estado = 'A'
					 WHERE nro_documento = ".quote($filtro['nro_documento'])." 
					 AND id_convocatoria = ".quote($filtro['id_convocatoria'])." 
					 AND id_tipo_beca = ".quote($filtro['id_tipo_beca']);	
			$sql_borrar = "DELETE FROM be_inscripcion_avales
					WHERE nro_documento = ".quote($filtro['nro_documento'])." 
					AND id_convocatoria = ".quote($filtro['id_convocatoria'])." 
					AND id_tipo_beca = ".quote($filtro['id_tipo_beca']);	
			return (toba::db()->ejecutar($sql_abrir) && toba::db()->ejecutar($sql_borrar));
		}else{
			return FALSE;
		}
	}

	function dirige_beca_otorgada($nro_documento)
	{
		$sql = "SELECT * FROM be_inscripcion_conv_beca 
				WHERE (nro_documento_dir = ".quote($nro_documento)." 
				OR nro_documento_codir = ".quote($nro_documento)." 
				OR nro_documento_subdir = ".quote($nro_documento).")
				AND beca_otorgada = 'S'";
		return count(toba::db()->consultar($sql));
	}

	/*
	Obtiene todos los tipos de publicaci�n (para la pesta�a "Trabajos Publicados")
	 */
	function get_tipos_publicacion()
	{
		$sql = "SELECT id_tipo_publicacion, tipo_publicacion FROM sap_tipo_publicacion WHERE disponible_becas = '1'";
		return toba::db()->consultar($sql);
	}

		/*
	Obtiene todos los tipos de comunicaci�n cient�fica (para la pesta�a "Present. Reuniones Cient�ficas")
	 */
	function get_tipos_comunicacion_cient()
	{
		$sql = "SELECT id_tipo_comunicacion, tipo_comunicacion FROM sap_tipo_comunicacion_cient";
		return toba::db()->consultar($sql);
	}

	/* ======================== ANTECEDENTES DECLARADOS ================================== */

	function get_antec_activ_docentes($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_activ_docentes AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_estudios_afines($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_estudios_afines AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_becas_obtenidas($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_becas_obtenidas AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_trabajos_publicados($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT ant.*, tip.tipo_publicacion 
				FROM be_antec_trabajos_publicados AS ant
				LEFT JOIN sap_tipo_publicacion AS tip ON tip.id_tipo_publicacion = ant.id_tipo_publicacion";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}


	function get_antec_present_reuniones($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT ant.*, tip.tipo_comunicacion
				FROM be_antec_present_reuniones AS ant
				LEFT JOIN sap_tipo_comunicacion_cient AS tip ON tip.id_tipo_comunicacion = ant.id_tipo_comunicacion";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_conocimiento_idiomas($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT id_conocimiento_idioma,idioma, nro_documento,doc_probatoria,
		case lectura when 1 then 'Muy bueno' when 2 then 'Bueno' when 3 then 'Aceptable' end as lectura,     
		case escritura when 1 then 'Muy bueno' when 2 then 'Bueno' when 3 then 'Aceptable' end as escritura,
		case conversacion when 1 then 'Muy bueno' when 2 then 'Bueno' when 3 then 'Aceptable' end as conversacion,
		case traduccion when 1 then 'Muy bueno' when 2 then 'Bueno' when 3 then 'Aceptable' end as traduccion 
		FROM be_antec_conoc_idiomas AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_otras_actividades($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_otras_actividades AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}
	function get_antec_particip_dict_cursos($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_particip_dict_cursos AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_antec_cursos_perfeccionamiento($filtro = array())
	{
		$where = array();
		if(isset($filtro['nro_documento'])){
			$where[] = "ant.nro_documento = ".quote($filtro['nro_documento']);
		}
		$sql = "SELECT * FROM be_antec_cursos_perfec_aprob AS ant";
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_campos($campos,$tabla,$condicion,$fila_unica=TRUE)
	{
		if(!is_array($campos) || count($campos) == 0 || strlen($tabla) == 0){
			return array();
		}
		$campos = implode(',',$campos);
		$sql = "SELECT $campos FROM $tabla WHERE $condicion";
		return ($fila_unica) ? toba::db()->consultar_fila($sql) : toba::db()->consultar($sql);
		
	}

	function get_informe_archivos_subidos($nro_documento)
	{
		$sql = "select institucion||' - '||cargo as descripcion, doc_probatoria, 'be_antec_activ_docentes' as tabla from be_antec_activ_docentes as descripcion where nro_documento = '$nro_documento'
				union
				select institucion||' - '||tipo_beca||' ('||fecha_desde||' a '||fecha_hasta||')' as descripcion, doc_probatoria, 'be_antec_becas_obtenidas' as tabla from be_antec_becas_obtenidas where nro_documento = '$nro_documento'
				union
				select idioma as descripcion, doc_probatoria , 'be_antec_conoc_idiomas' as tabla from be_antec_conoc_idiomas where nro_documento = '$nro_documento'
				union
				select tema as descripcion, doc_probatoria, 'be_antec_cursos_perfec_aprob' as tabla from be_antec_cursos_perfec_aprob where nro_documento = '$nro_documento'
				union
				select titulo as descripcion, doc_probatoria, 'be_antec_estudios_afines' as tabla from be_antec_estudios_afines where nro_documento = '$nro_documento'
				union
				select titulo_tema as descripcion, doc_probatoria, 'be_antec_otras_actividades' as tabla from be_antec_otras_actividades where nro_documento = '$nro_documento'
				union
				select fecha||' - '||institucion as descripcion, doc_probatoria, 'be_antec_particip_dict_cursos' as tabla from be_antec_particip_dict_cursos where nro_documento = '$nro_documento'
				union
				select titulo_trabajo as descripcion, doc_probatoria, 'be_antec_present_reuniones' as tabla from be_antec_present_reuniones where nro_documento = '$nro_documento'
				union
				select datos_publicacion as descripcion, doc_probatoria, 'be_antec_trabajos_publicados' as tabla from be_antec_trabajos_publicados where nro_documento = '$nro_documento'";
		$archivos = toba::db()->consultar($sql);
		if(count($archivos)){
			foreach ($archivos as $archivo) {
				$resumen[$archivo['tabla']][] = array('descripcion'=>$archivo['descripcion'],
														'doc_probatoria' => $archivo['doc_probatoria']);
			}
			return $resumen;
		}else{
			return array();
		}

	}

	

	
}
?>