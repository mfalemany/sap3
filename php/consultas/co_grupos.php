<?php
class co_grupos
{
	const ESTADO_ACTIVO        = 'ACTI';
	const ESTADO_EN_EVALUACION = 'EVAL';
	const ESTADO_INSCRIPTO     = 'INSC';
	const ESTADO_GUARDADO      = 'GUAR';
	const ESTADO_A_CAMBIAR     = 'CAMB';
	const ESTADO_DESAPROBADO   = 'DESA';

	/**
	 * Devuelve TRUE si la persona ya se encuentra asignada como coordinador de un grupo de investigacion
	 * @param string $nro_documento 
	 * @return boolean
	 */
	function es_coordinador($nro_documento){
		$sql = "SELECT * FROM sap_grupo WHERE nro_documento_coordinador = ".quote($nro_documento)." AND (fecha_fin > now() OR fecha_fin is null) LIMIT 1";
		$resultado = toba::db()->consultar($sql);
		return (count($resultado)) ? TRUE : FALSE;
	}

	/**
	 * Determina si una persona fue asignada como evaluador de algun grupo de investigaci�n.
	 * (si existe en la tabla sap_grupo_evaluador)
	 * @param  string $nro_documento
	 * @return bool
	 */
	public function es_evaluador_grupos($nro_documento)
	{
		$sql = "SELECT COUNT(*) AS cantidad_grupos_evaluando 
				FROM sap_grupo_evaluador
				WHERE nro_documento = " . quote($nro_documento);

		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cantidad_grupos_evaluando'] > 0);
	}

	public function get_estado($id_grupo)
	{
		$sql       = "SELECT id_estado FROM sap_grupo WHERE id_grupo = " . quote($id_grupo) . " LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['id_estado'];	
	}

	function esta_exceptuado($nro_documento)
	{
		$sql = "SELECT * FROM sap_excepcion_dir WHERE nro_documento = ".quote($nro_documento)." AND aplicable = 'G'  LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado;
	}

	/**
	 * Retorna un valor booleano que indica si el grupo con el ID recibido, ya se encuentra inscripto como grupo en alguna convocatoria (Los grupos se inscriben solo una vez, despues solo presentan planes de trabajo)
	 * @param  integer $id_grupo ID del grupo
	 * @return boolean           
	 */
	function esta_inscripto($id_grupo)
	{
		$sql = "SELECT fecha_inscripcion FROM sap_grupo WHERE id_grupo = ".quote($id_grupo)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return ( isset($resultado['fecha_inscripcion']) && $resultado['fecha_inscripcion'] );
	}

	function esta_vigente($id_grupo)
	{
		$sql = "SELECT * FROM sap_grupo WHERE vigente = 'S' AND id_grupo = ".quote($id_grupo)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return (count($resultado));
	}

	public function cargar_excepcion_director_grupos($nro_documento, $observaciones)
	{
		if ( ! (is_string($nro_documento) && strlen($nro_documento) > 0 && strlen($nro_documento) <= 15) ) {
			return false;
		}

		if (is_string($observaciones)) {
			$observaciones = substr($observaciones, 0, 299);
		} else {
			$observaciones = '';
		}

		$excepcion = $this->esta_exceptuado($nro_documento);
		if (!$excepcion) {
			$sql = "INSERT INTO sap_excepcion_dir VALUES (default, " . quote($nro_documento) . ", " . quote($observaciones) . ", 'G');";
		} else {
			// Esta variable calcula la longitud que sobra en el campo observaciones (para concatenar las observaciones anteriores que se pisan)
			$largo_observaciones_anteriores = 300 - (strlen($observaciones) + 38); 
			$sql = "UPDATE sap_excepcion_dir SET observaciones = " . quote($observaciones . ' --- Solicitud anterior reemplazada: ' . substr($excepcion['observaciones'], 0, $largo_observaciones_anteriores)) . ' WHERE nro_documento = ' . quote($nro_documento);
		}
		return toba::db()->ejecutar($sql);
	}

	public function existen_planes_presentados($id_grupo, $solo_cerrados = false)
	{
		$where = [];
		$sql = "SELECT count(*) AS cantidad_informes 
				FROM sap_grupo_informe 
				WHERE id_grupo = " . quote($id_grupo);
		
		if ($solo_cerrados) {
			$where[] = "estado = 'C'";
		}

		if (count($where)) {
			$sql = sql_concatenar_where($sql, $where);
		}
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cantidad_informes'] > 0);

	}

	function get_actividad_extension($id_grupo)
	{
		$id_grupo = quote($id_grupo);
		$sql = "SELECT * 
				FROM sap_grupo_extension 
				WHERE id_grupo = $id_grupo
				ORDER BY fecha_inicio DESC";
		return toba::db()->consultar($sql);
	}
	
	function get_actividad_publicacion($id_grupo)
	{
		$sql = "SELECT gp.*, tp.tipo_publicacion
				FROM sap_grupo_publicacion AS gp
				LEFT JOIN sap_tipo_publicacion AS tp ON gp.id_tipo_publicacion = tp.id_tipo_publicacion
				WHERE id_grupo = ".quote($id_grupo) . "
				ORDER BY anio DESC";
		return toba::db()->consultar($sql);
	}

	function get_actividad_transferencia($id_grupo)
	{
		$sql = "SELECT gt.*, tt.tipo_transferencia, CASE gt.sector WHEN 'R' THEN 'Privado' ELSE 'P�blico' END AS sector_desc
				FROM sap_grupo_transferencia AS gt
				LEFT JOIN sap_tipo_transferencia AS tt ON gt.id_tipo_transferencia = tt.id_tipo_transferencia
				WHERE id_grupo = ".quote($id_grupo) . "
				ORDER BY anio DESC";
		return toba::db()->consultar($sql);
	}
	
	function get_actividad_form_rrhh($id_grupo)
	{
		$sql = "SELECT gf.*, 
						per.apellido||', '||per.nombres AS persona,
						tf.tipo_formacion,
						CASE WHEN gf.id_entidad_beca IS NULL THEN 'Sin beca' else eb.entidad_beca end as entidad_beca
						
				FROM sap_grupo_form_rrhh AS gf
				LEFT JOIN sap_personas AS per USING(nro_documento)
				LEFT JOIN sap_tipo_form_rrhh AS tf USING (id_tipo_formacion)
				LEFT JOIN sap_entidad_beca AS eb USING(id_entidad_beca)
				WHERE id_grupo = ".quote($id_grupo) . "
				ORDER BY gf.fecha_inicio DESC";
		return toba::db()->consultar($sql);
	}

	function get_actividad_evento($id_grupo)
	{
		$sql = "SELECT evento, anio, 
					CASE alcance 
						WHEN 'R' THEN 'Regional' 
						WHEN 'N' THEN 'Nacional' 
						WHEN 'I' THEN 'Internacional' 
					END AS alcance
				FROM sap_grupo_evento AS ge
				WHERE id_grupo = ".quote($id_grupo) . "
				ORDER BY anio DESC";
		return toba::db()->consultar($sql);
	}

	function get_ayn($nro_documento)
	{
		$sql = "SELECT apellido||', '||nombres AS ayn FROM sap_personas WHERE nro_documento = ".quote($nro_documento);
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['ayn'];
	}

	/**
	 * Obtiene y retorna un listado de becarios de la SGCyT que est�n inclu�dos como integrantes en proyectos de
	 * investigaci�n declarados por un grupo de investigaci�n (formaci�n de recursos humanos del grupo)
	 * @param  int $id_grupo
	 * @return array
	 */
	public function get_becarios_proyectos_grupo($id_grupo)
	{
		$id_grupo = quote($id_grupo);
		$sql = "SELECT oto.fecha_desde AS beca_fecha_desde,
						oto.fecha_hasta AS beca_fecha_hasta,
						per.apellido,
						per.nombres,
						per.apellido || ', ' || per.nombres AS persona,
						tipbec.tipo_beca,
						proy.codigo AS codigo_proyecto,
						proy.descripcion
				FROM be_becas_otorgadas AS oto
				LEFT JOIN be_inscripcion_conv_beca AS insc
				    ON insc.nro_documento = oto.nro_documento
				    AND insc.id_convocatoria = oto.id_convocatoria
				    AND insc.id_tipo_beca = oto.id_tipo_beca
				LEFT JOIN sap_proyectos AS proy ON proy.id = insc.id_proyecto
				LEFT JOIN be_tipos_beca AS tipbec ON tipbec.id_tipo_beca = insc.id_tipo_beca
				LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
				WHERE (
					insc.id_proyecto IN (SELECT id_proyecto FROM sap_grupo_proyecto WHERE id_grupo = $id_grupo)
					OR
					insc.id_grupo = $id_grupo
				)";
		return toba::db()->consultar($sql);
	}
	
	function get_categoria($id_grupo)
	{
		$sql = "SELECT id_categoria FROM sap_grupo WHERE id_grupo = ".quote($id_grupo);
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['id_categoria'];
	}

	function get_categorias_grupo()
	{
		return toba::db()->consultar("SELECT * FROM sap_grupo_categoria");
	}

	public function get_convocatoria_anio_vigente()
	{
		// TODO: sacar esto despues de marzo 2023. Es temporal por una convocatoria prorrogada
		$convocatoria = $this->get_convocatorias();//(['anio' => date('Y')]);
		return count($convocatoria) ? $convocatoria[0] : null;
	}

	public function get_convocatorias($filtro = array())
	{
		$filtro        = array_merge($filtro, ['aplicable' => 'EQUIPOS']);
		$convocatorias = toba::consulta_php('co_convocatorias')->get_convocatorias($filtro);
		foreach ($convocatorias as &$convocatoria) {
			$custom_params = json_decode($convocatoria['custom_params'], true);
			$convocatoria  = $custom_params ? array_merge($convocatoria, $custom_params) : $convocatoria;
		}
		return $convocatorias;
	}
	
	function get_denominacion_grupo($id_grupo)
	{
		$sql = "SELECT denominacion FROM sap_grupo WHERE id_grupo = ".quote($id_grupo)." LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['denominacion'];
	}

	/**
	 * Retorna un array con todos los detalles del grupo cuyo ID coincide con el argumento recibido. El array contiene la denominaci?, la descripci?, los integrantes, etc.
	 * @param  integer $id_grupo ID del grupo que se busca
	 * @return array           Array con los detalles del grupo
	 */
	function get_detalles_grupo($id_grupo)
	{
		$sql = "SELECT gr.id_grupo, 
					gr.denominacion, 
					gr.descripcion, 
					gr.id_categoria,
					coord.apellido||', '||coord.nombres AS coordinador, 
					gr.nro_documento_coordinador,
					dep.nombre AS dependencia,
					ac.nombre AS area_conocimiento,
					gr.fecha_inicio,
					gr.fecha_inscripcion,
					gr.id_estado,
					gr.palabras_clave
				FROM sap_grupo AS gr
				LEFT JOIN sap_personas AS coord ON coord.nro_documento = gr.nro_documento_coordinador
				LEFT JOIN sap_dependencia AS dep ON dep.id = gr.id_dependencia
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = gr.id_area_conocimiento
				WHERE gr.id_grupo = ".quote($id_grupo);
		return toba::db()->consultar_fila($sql);
	}

	function get_entidades_beca()
	{
		return toba::db()->consultar("SELECT * FROM sap_entidad_beca");
	}

	function get_evaluacion_ultimo_informe($id_grupo)
	{
		$sql = "SELECT resultado
				FROM sap_grupo_informe_evaluacion AS gr
				WHERE gr.id_grupo = ".quote($id_grupo)."
				AND gr.id_convocatoria = (SELECT MAX(id_convocatoria) FROM sap_grupo_informe_evaluacion WHERE id_grupo = gr.id_grupo)";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['resultado'];
	}

	public function get_evaluadores_y_grupos_asignados($filtro = array()) 
	{
		$where = [];
		$sql = "SELECT 
				    ge.id_grupo,
				    per.nro_documento, 
				    per.apellido||', '||per.nombres AS ayn,
				    gr.denominacion
				FROM sap_grupo_evaluador AS ge
				LEFT JOIN sap_personas AS per ON per.nro_documento = ge.nro_documento
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = ge.id_grupo
				ORDER BY per.apellido, per.nombres, gr.denominacion";

				
		if (isset($filtro['grupo']) && $filtro['grupo']) {
			$sql .= "WHERE ge.id_grupo IN (SELECT id_grupo FROM sap_grupo WHERE quitar_acentos(denominacion) ILIKE quitar_acentos('%" . $filtro['grupo'] . "%'))";
		}

		if (isset($filtro['evaluador']) && $filtro['evaluador']) {
			$evaluador = str_replace(' ','%', $filtro['evaluador']);
			$where[] = "per.nro_documento = " . quote($filtro['nro_documento']) . "
				OR quitar_acentos(per.apellido)||' '||quitar_acentos(per.nombres) ILIKE '%$evaluador%' 
				OR quitar_acentos(per.nombres)||' '||quitar_acentos(per.apellido) ILIKE '%$evaluador%'";
		}

		if (count($where)) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);

	}

	function get_estados_permiten_inscripcion()
	{
		return ['GUAR', 'DESA'];
	}

	function get_estados_permiten_presentacion_informe()
	{
		//Pueden presentar informe aquellos que est? activos, o aquellos que se le solicitaron moficiaciones
		return ['CAMB', 'ACTI'];
	}

	function get_estados_no_permiten_edicion()
	{
		//Pueden presentar informe aquellos que est? activos, o aquellos que se le solicitaron moficiaciones
		return ['INSC', 'EVAL'];
	}
	/**
	 * Los estados enumerados por este m�todo, son los estados que NO SE CONSIDERAN al momento de determinar
	 * cuantos grupos integra una persona.
	 * @return array
	 */
	function get_estados_grupos_no_considera_integrantes()
	{
		return ['GUAR','FINA','DESA'];
	}

	function get_evaluaciones($id_grupo)
	{
		//Obtengo los datos del grupo
		$sql = "SELECT gr.*, gc.categoria  
				FROM sap_grupo AS gr
				LEFT JOIN sap_grupo_categoria AS gc ON gc.id_categoria = gr.id_categoria
				WHERE gr.id_grupo = ".quote($id_grupo);
		$grupo = toba::db()->consultar_fila($sql);
		//Obtengo los datos de los informes y sus evaluaciones
		$sql = "SELECT *, CASE gie.resultado WHEN 'A' THEN 'Aprobado' WHEN 'D' THEN 'Desaprobado' WHEN 'P' THEN 'En proceso de evaluaci�n' ELSE 'Sin Evaluaci�n' END as resultado_desc
				FROM sap_grupo_informe AS gi
				LEFT JOIN sap_grupo_informe_evaluacion AS gie
					ON gi.id_grupo = gie.id_grupo
					AND gi.id_convocatoria = gie.id_convocatoria
				WHERE gi.id_grupo = ".quote($id_grupo);
		$informes = toba::db()->consultar($sql);
		return array('grupo'=>$grupo,'informes'=>$informes);
	}

	function get_excepciones()
	{
		$sql = "SELECT per.nro_documento, 
				coalesce(per.apellido,'Sin apellido')||', '||
				coalesce(per.nombres,'Sin nombre') AS ayn, 
				ex.observaciones
				FROM sap_excepcion_dir AS ex
				LEFT JOIN sap_personas AS per ON per.nro_documento = ex.nro_documento
				WHERE ex.aplicable = 'G'";
		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna todos un array con todos los grupos que hayan presentado un informe en la ?ltima convocatoria
	 * @param  array  $filtro Filtros para la consulta
	 * @return array         Array de grupos a evaluar
	 */
	function get_grupos_a_evaluar($filtro = array())
	{
		//Obtengo la ?ltima convocatoria
		$conv = toba::db()->consultar_fila("SELECT max(id) AS id FROM sap_convocatoria WHERE aplicable = 'EQUIPOS'");
		$conv = $conv['id'];

		$where = array();
		if(isset($filtro['id_dependencia'])){
			$where[] = 'gr.id_dependencia = '.quote($filtro['id_dependencia']);
		}
		if(isset($filtro['id_area_conocimiento'])){
			$where[] = 'gr.id_area_conocimiento = '.quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['id_categoria'])){
			$where[] = 'gr.id_categoria = '.quote($filtro['id_categoria']);
		}
		if(isset($filtro['coordinador'])){
			$where[] = '(per.apellido ILIKE '.quote('%'.$filtro['coordinador'].'%')." OR per.nombres ilike ".quote('%'.$filtro['coordinador'].'%').")";
		}
		if(isset($filtro['evaluador'])){
			$where[] = 'gr.id_grupo IN (SELECT id_grupo FROM sap_grupo_evaluador WHERE nro_documento = ' . quote($filtro['evaluador']) . ')';
		}

		$estados_eval = implode(',', array_map(function($estado){
			return quote($estado);
		},[self::ESTADO_EN_EVALUACION, self::ESTADO_INSCRIPTO]));

		$sql = "SELECT $conv AS id_convocatoria,
						gr.*, 
						gc.categoria,
						dep.nombre AS dependencia, 
						ac.nombre AS area_conocimiento,
						per.apellido||', '||per.nombres as coordinador 
				FROM sap_grupo AS gr 
				LEFT JOIN sap_grupo_categoria as gc ON gc.id_categoria = gr.id_categoria
				LEFT JOIN sap_dependencia AS dep ON dep.id = gr.id_dependencia
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = gr.id_area_conocimiento
				LEFT JOIN sap_personas AS per ON per.nro_documento = gr.nro_documento_coordinador
				WHERE gr.id_estado IN ($estados_eval) -- El grupo debe estar en estado de evaluaci�n
				AND EXISTS (SELECT * FROM sap_grupo_informe WHERE id_convocatoria = $conv AND id_grupo = gr.id_grupo)
				AND gr.fecha_inscripcion IS NOT NULL
				AND aval_fecha IS NOT NULL";
		$sql = sql_concatenar_where($sql,$where);
		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna un array con todos los grupos (si no se estableci? un filtro) o aquellos que coincidan con el criterio de filtro.
	 * @param  array  $filtro Array de condiciones a filtrar
	 * @return array         Array de grupos
	 */
	function get_grupos($filtro = array())
	{
		$where = array();
		//Subconsulta que aparece repetidamente.
		$estado_evaluacion_ultimo_plan = "
				SELECT resultado 
				FROM sap_grupo_informe_evaluacion AS eva 
				WHERE eva.id_convocatoria = (SELECT max(id_convocatoria) FROM sap_grupo_informe WHERE id_grupo = gr.id_grupo AND estado = 'C')
				AND id_grupo = gr.id_grupo";

		if(isset($filtro['nro_documento_coordinador'])){
			$where[] = 'gr.nro_documento_coordinador = '.quote($filtro['nro_documento_coordinador']);
		}
		if(isset($filtro['id_grupo'])){
			$where[] = 'gr.id_grupo = '.quote($filtro['id_grupo']);
		}
		if(isset($filtro['denominacion'])){
			$where[] = 'quitar_acentos(gr.denominacion) ilike quitar_acentos('.quote('%'.$filtro['denominacion'].'%').')';
		}	
		if(isset($filtro['coordinador'])){
			$where[] = 'per.apellido ilike '.quote('%'.$filtro['coordinador'].'%'). ' OR per.nombres ilike '.quote('%'.$filtro['coordinador'].'%');
		}
		if(isset($filtro['integrante'])){
			$where[] = "id_grupo IN (SELECT id_grupo FROM sap_grupo_integrante WHERE nro_documento = ".quote($filtro['integrante'])."AND (fecha_fin > current_date OR fecha_fin is null))";
		}
		
		if(isset($filtro['id_dependencia'])){
			$where[] = 'gr.id_dependencia = '.quote($filtro['id_dependencia']);
		}
		if(isset($filtro['id_area_conocimiento'])){
			$where[] = 'gr.id_area_conocimiento = '.quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['id_categoria'])){
			$where[] = 'gr.id_categoria = '.quote($filtro['id_categoria']);
		}
		if(isset($filtro['solo_inscriptos']) && $filtro['solo_inscriptos'] == 1){
			$where[] = 'gr.fecha_inscripcion is not null';
		}
		if(isset($filtro['vigentes']) && $filtro['vigentes'] == 1){
			$where[] = 'gr.vigente = \'S\'';
		}
		if(isset($filtro['anio_inscripcion'])){
			$where[] = 'extract(year from fecha_inscripcion) = '.quote($filtro['anio_inscripcion']);
		}
		if(isset($filtro['patron'])){
			$where[] = 'quitar_acentos(gr.denominacion) ilike quitar_acentos('.quote('%'.$filtro['patron'].'%').')';
		}
		if(isset($filtro['estado_evaluacion_ultimo_plan'])){
			$where[] = "(".$estado_evaluacion_ultimo_plan.") = " . quote($filtro['estado_evaluacion_ultimo_plan']);
		}
		if(isset($filtro['id_estado'])){
			$where[] = "gr.id_estado = " . quote($filtro['id_estado']);
		}

		if(isset($filtro['codigo_proyecto'])){
			$where[] = "EXISTS (
							SELECT * 
							FROM sap_grupo_proyecto 
							WHERE id_proyecto IN (
								SELECT id 
								FROM sap_proyectos 
								WHERE codigo = " . quote($filtro['codigo_proyecto']) . "
							) AND id_grupo = gr.id_grupo
						)";
		}		

		if(isset($filtro['estado_inscripcion'])){
			//Si se solicitaron solo los inscriptos
			if($filtro['estado_inscripcion'] == 'I'){
				$where[] = 'gr.fecha_inscripcion IS NOT NULL';
			}else{
				$where[] = 'gr.fecha_inscripcion IS NULL';
			}
		}
		//Los grupos acreditados son aquellos que tuvieron la ?ltima evaluaci�n positiva (y al menos una). No se incluyen entonces a: grupos que no tienen ninguna evaluaci�n, o que teniendo evaluaciones, la ?ltima no es aprobada
		if(isset($filtro['solo_acreditados']) && $filtro['solo_acreditados']){
			$where[] = "exists (
						select * 
						from sap_grupo_informe_evaluacion 
						where id_grupo = gr.id_grupo 
						and id_convocatoria = (select max(id_convocatoria) from sap_grupo_informe_evaluacion where id_grupo = gr.id_grupo)
						and resultado = 'A'
					)";
		}

		$sql = "SELECT gr.*,
						per.apellido||', '||per.nombres AS coordinador,
						dep.nombre AS dependencia,
						ac.nombre AS area_conocimiento,
						cat.categoria,
						ge.estado,
						CASE (" . $estado_evaluacion_ultimo_plan . ") 
							WHEN 'A' THEN 'Aprobado'
							WHEN 'P' THEN 'En proceso de evaluaci�n'
							WHEN 'D' THEN 'Desaprobado'
							ELSE 'Sin evaluaci�n' END AS estado_evaluacion_ultimo_plan,
						CASE WHEN LENGTH(gr.denominacion) > 50 
							THEN SUBSTRING(gr.denominacion, 1, 50) || '...'
							ELSE gr.denominacion END AS denominacion_corta
				FROM sap_grupo AS gr
				LEFT JOIN sap_personas AS per ON per.nro_documento = gr.nro_documento_coordinador
				LEFT JOIN sap_dependencia AS dep ON dep.id = gr.id_dependencia
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = gr.id_area_conocimiento
				LEFT JOIN sap_grupo_categoria AS cat ON cat.id_categoria = gr.id_categoria
				LEFT JOIN sap_grupo_estado AS ge ON gr.id_estado = ge.id_estado";

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna el ID y la denominaci? de un grupo. M?odo ?til para usar con EF Combo Editable
	 * @param  [string] $criterio patr? de b?squeda
	 * @return [string]           Denominaci? del/los grupos que coincidan con el patr? buscado
	 */
	function get_grupo_busqueda($criterio,$solo_vigentes = FALSE,$solo_anios_anteriores = FALSE)
	{
		$sql = "SELECT id_grupo, denominacion
				FROM sap_grupo 
				WHERE quitar_acentos(denominacion) ILIKE ".quote("%".$criterio."%");
		if($solo_anios_anteriores){
			$sql .= " AND extract(year from fecha_inscripcion) < extract(year from current_date)";
		}
		if($solo_vigentes){
			$sql .= " AND (fecha_fin is null OR fecha_fin >= current_date)";
		}
				
		return toba::db()->consultar($sql);
	}

	function get_grupos_combo_editable($patron){
		return $this->get_grupos(array('patron'=>$patron));
	}

	/**
	 * Retorna la denominaci? del grupo cuyo ID coincide con el recibido como par?etro.
	 * @param  [integer] $id_grupo ID del grupo que se busca
	 * @return [string]           Denominaci? del grupo
	 */
	function get_grupo_denominacion($id_grupo){
		$sql = "SELECT denominacion FROM sap_grupo WHERE id_grupo = ".quote($id_grupo);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['denominacion']) ? $resultado['denominacion'] : 'Grupo no encontrado';
	}

	public function get_grupo_estados()
	{
		return toba::db()->consultar("SELECT * FROM sap_grupo_estado ORDER BY estado ASC");
	}

	function get_grupo_busqueda_vigentes($criterio)
	{
		return $this->get_grupo_busqueda($criterio,TRUE,TRUE);
	}

	/**
	 * Retorna un array con los datos de los integrantes del grupo
	 * @param  integer $id_grupo ID del grupo del cual se quieren obtener los integrantes
	 * @return array           Array de integrantes, con datos b?icos como apellido y nombres, nro_documento, etc.
	 */
	function get_integrantes($id_grupo)
	{
		$sql = "SELECT per.apellido,
						per.nombres,
						per.apellido||', '||per.nombres AS integrante, 
						per.nro_documento, 
						inte.fecha_inicio,
						inte.fecha_fin,
						rol.rol,
						rol.id_rol

				FROM sap_grupo_integrante AS inte
				LEFT JOIN sap_personas AS per ON per.nro_documento = inte.nro_documento
				LEFT JOIN sap_grupo_rol AS rol ON rol.id_rol = inte.id_rol
				WHERE inte.id_grupo = ".quote($id_grupo)."
				AND (inte.fecha_fin > current_date OR inte.fecha_fin is null)
				ORDER BY integrante";
		return toba::db()->consultar($sql);
	}

	/**
	 * Busca el patr? recibido como parametro y lo compara con los apellidos y nombres de todos los integrantes de grupos.
	 * @param  string $patron Patr? a buscar
	 * @return array         Coincidencias 
	 */
	function get_integrantes_grupos($patron)
	{
		$sql = "SELECT per.nro_documento, per.apellido||', '||per.nombres AS ayn 
				FROM sap_personas AS per
				WHERE (per.apellido ILIKE '%$patron%' OR per.nombres ILIKE '%$patron%'OR per.nro_documento ILIKE '%$patron%')
				AND EXISTS (SELECT * FROM sap_grupo_integrante WHERE nro_documento = per.nro_documento AND (fecha_fin > current_date OR fecha_fin is null))";
		return toba::db()->consultar($sql);

	}

	function get_lineas_investigacion($id_grupo)
	{
		$sql = "SELECT linea_investigacion FROM sap_grupo_linea_investigacion WHERE id_grupo = ".quote($id_grupo);
		$resultado = toba::db()->consultar($sql);
		$lineas = array();
		foreach ($resultado as $linea) {
			$lineas[] = $linea['linea_investigacion'];
		}
		return $lineas;
	}

	function get_plan_trabajo($id_grupo, $id_convocatoria)
	{
		$sql = "SELECT *
				FROM sap_grupo_informe AS gi
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = gi.id_grupo
				WHERE gi.id_grupo      = " . quote($id_grupo) . "
				AND gi.id_convocatoria = " . quote($id_convocatoria);
		return toba::db()->consultar_fila($sql);
	}

	function get_planes_trabajo($id_grupo)
	{
		$sql = "SELECT gi.*, 
					conv.nombre as convocatoria,
					gie.resultado,
					CASE gie.resultado 
						WHEN 'A' THEN 'Aprobado'
						WHEN 'P' THEN 'Solitaron modificaciones'
						WHEN 'D' THEN 'Desaprobado'
						END AS resultado_desc,
					gie.observaciones,
					'(' || eva.nro_documento || ') ' || eva.apellido || ', ' || eva.nombres AS evaluador
				FROM sap_grupo_informe AS gi
				LEFT JOIN sap_convocatoria AS conv ON conv.id = gi.id_convocatoria
				LEFT JOIN sap_grupo_informe_evaluacion AS gie 
					ON gie.id_convocatoria = gi.id_convocatoria
					AND gie.id_grupo = gi.id_grupo
				LEFT JOIN sap_personas AS eva ON eva.nro_documento = gie.nro_documento_evaluador 
				WHERE gi.id_grupo = " . quote($id_grupo);
		return toba::db()->consultar($sql);
	}

	function get_proyectos_grupo($id_grupo,$solo_vigentes=FALSE)
	{
		$sql = "SELECT  pr.id,
						pr.id AS id_proyecto,
						pr.tipo,
						pr.codigo, 
						pr.descripcion,
						pr.fecha_desde,
						pr.fecha_hasta,
						CASE WHEN pr.fecha_hasta < current_date THEN 'Finalizado' ELSE 'Vigente' END AS estado
				FROM sap_grupo_proyecto AS gp
				LEFT JOIN sap_proyectos AS pr ON pr.id = gp.id_proyecto
				WHERE gp.id_grupo = ".quote($id_grupo)."
				UNION
				SELECT null AS id, null AS id_proyecto, 
					'Externo' AS codigo, 
					'9' AS tipo,
					denominacion,
					pe.fecha_desde,
					pe.fecha_hasta,
					CASE WHEN pe.fecha_hasta < current_date THEN 'Finalizado' ELSE 'Vigente' END AS estado
				FROM sap_proyectos_externos AS pe
				WHERE id_grupo = ".quote($id_grupo);
		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna los roles disponibles para integrantes de grupos
	 * @return array Array de roles disponibles
	 */
	function get_roles()
	{
		$sql = "SELECT * FROM sap_grupo_rol";
		return toba::db()->consultar($sql);
	}

	function get_tipos_extension()
	{
		return toba::db()->consultar("SELECT * FROM sap_tipo_extension");
	}

	function get_tipos_publicacion($filtro = array())
	{
		$where = array();
		$sql = "SELECT * FROM sap_tipo_publicacion";
		
		if(isset($filtro['disponible_proyectos']) && $filtro['disponible_proyectos']){
			$where[] = "disponible_proyectos = " . quote($filtro['disponible_proyectos']);
		}

		if(count($where)){
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	//Shorcut de get_tipos_publicacion
	function get_tipos_publicacion_proyectos()
	{
		return $this->get_tipos_publicacion(array('disponible_proyectos'=>'S'));
	}

	function get_tipos_transferencia()
	{
		return toba::db()->consultar("SELECT * FROM sap_tipo_transferencia");
	}
	function get_tipos_form_rrhh()
	{
		return toba::db()->consultar("SELECT * FROM sap_tipo_form_rrhh");
	}

	/**
	 * Retorna los ID de los grupos de los cuales una persona es integrante
	 * @param  varchar $nro_documento N?mero de documento de la persona que se busca
	 * @param  boolea $historico Indica si se deben considerar tambien grupos en los cuales la persona fue integrante (pero ya no)
	 * @param  array $omitir   Array de id_grupo que no se consideran al buscar (sirve para resolver la pregunta "Aparte de este/estos grupo/s, en que otros grupos est??")
	 * @return array                Array que contiene los IDs de los grupos de los cuales la persona es integrante
	 */
	function grupos_es_integrante($nro_documento,$historico = false,$omitir=array()){
		//Estos grupos no se consideran por tener un estado "no activo"
		$grupos_ignorar = $this->get_estados_grupos_no_considera_integrantes();
		$grupos_ignorar = array_map(function($elem){
			return quote($elem);
		}, $grupos_ignorar);
		$grupos_ignorar = implode(',',$grupos_ignorar);

		$sql = "SELECT gi.id_grupo,gr.denominacion 
				FROM sap_grupo_integrante AS gi
				LEFT JOIN sap_grupo AS gr ON gr.id_grupo = gi.id_grupo
				WHERE gr.id_estado NOT IN ($grupos_ignorar)
				AND gi.nro_documento = ".quote($nro_documento);

		if(!$historico){
			$sql .= " AND (gi.fecha_fin > current_date OR gi.fecha_fin is null)"; 
		}
		if($omitir){
			$omitir = implode(',',$omitir);
			$sql .= " AND gi.id_grupo NOT IN (".$omitir.")";
		}
		$grupos = array();
		return toba::db()->consultar($sql);
	}

	/**
	 * Determina si un grupo est? en estado y condiciones de presentar un informe
	 * @param  int     $id_grupo ID del grupo
	 * @return boolean
	 */
	public function grupo_debe_presentar_informe($id_grupo)
	{
		$grupo = $this->get_detalles_grupo($id_grupo);
		if (!in_array($grupo['id_estado'], $this->get_estados_permiten_presentacion_informe())) {
			return false;
		}
		if (!isset($grupo['fecha_inscripcion'])) {
			return false;
		}
		//A? actual, menos el a? siguiente al de inscripcion
		//Se considera que el grupo inicia al a? siguiente de la convocatoria, por eso se suma uno
		$anio_actual            = date('Y');
		$anio_inscripcion_grupo = date('Y', strtotime($grupo['fecha_inscripcion']));

		// TODO: sacar esto despues de marzo 2023. Es temporal por una convocatoria prorrogada
		if ($anio_inscripcion_grupo == 2019) {
			return true;
		}

		$anios_antiguedad_grupo = $anio_actual - ($anio_inscripcion_grupo + 1);
		$esta_en_anio_par       = ($anios_antiguedad_grupo % 2 === 0);

		//Si estamos en un a? par (en la vida del grupo)
		return ($esta_en_anio_par && $anios_antiguedad_grupo >= 2);
	}

	public function grupo_puede_inscribirse($id_grupo)
	{
		$sql       = "SELECT id_estado FROM sap_grupo WHERE id_grupo = " . quote($id_grupo) . " LIMIT 1";
		$resultado = toba::db()->consultar_fila($sql);
		return (isset($resultado['id_estado']) && in_array($resultado['id_estado'], $this->get_estados_permiten_inscripcion()));

	}

	public function permite_cambio_categoria($id_grupo)
	{
		if (!is_numeric($id_grupo)) {
			throw new toba_error('No se encontr� un grupo con el identificador proporcionado');
		}

		$grupo = toba::db()->consultar_fila("SELECT * FROM sap_grupo WHERE id_grupo = " . quote($id_grupo));

		if (!$grupo) {
			throw new toba_error('No se encontr� un grupo con el identificador proporcionado');
		}

		if ($grupo['id_estado'] == self::ESTADO_INSCRIPTO || $grupo['permite_cambio_categoria']) {
			return true;
		}

		return false;
	}

	function presento_informe($id_grupo,$id_convocatoria)
	{
		$sql = "SELECT * FROM sap_grupo_informe WHERE id_grupo = ".quote($id_grupo)." AND id_convocatoria = ".quote($id_convocatoria)." AND estado = 'C' LIMIT 1";
		return (toba::db()->consultar($sql));
	}

	function puede_coordinar($usuario)
	{
		if($this->esta_exceptuado($usuario)){
			return TRUE;
		}
		if(! toba::consulta_php('co_personas')->es_docente($usuario,TRUE)){
			return FALSE;
		}
		if( ! (toba::consulta_php('co_personas')->tiene_mayor_dedicacion($usuario) || 
			  toba::consulta_php('co_personas')->tiene_cargo_conicet_con_asiento_en_unne($usuario)) ){
			return FALSE;
		}
		$cat = toba::consulta_php('co_personas')->get_categoria_incentivos($usuario);
		if($cat == NULL || ! in_array($cat,array(1,2,3))){
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Este m?odo determina si un usuario (solamente) puede ver la opcion de "Grupos" en el men?, independientemente de si puede o no coordinar.
	 * @return boolean puede_ver_opcion_grupos
	 */
	function puede_ver_opcion_grupos($nro_documento)
	{
		return toba::consulta_php('co_proyectos')->dirige_proyectos($nro_documento,TRUE);
	}

	public function retirar_aval($id_grupo)
	{
		if (!is_numeric($id_grupo)) return false;
		return toba::db()->ejecutar('UPDATE sap_grupo SET aval_fecha = null WHERE id_grupo = ' . $id_grupo);
	}

	function tiene_evaluaciones($id_grupo)
	{
		$sql = "SELECT * FROM sap_grupo_informe_evaluacion WHERE id_grupo = ".quote($id_grupo)." LIMIT 1";
		return (toba::db()->consultar($sql));
	}

	/* ================================================== */
	

	




}
?>