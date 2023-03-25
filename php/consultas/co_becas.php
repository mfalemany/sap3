<?php 
class co_becas{
	const SUBTIPO_BECA_DOCENTES  = 'D';
	const SUBTIPO_BECA_GRADUADOS = 'G';
	
	const SUBTIPOS_BECA = [
		self::SUBTIPO_BECA_DOCENTES  => 'Docentes',
		self::SUBTIPO_BECA_GRADUADOS => 'Graduados',
	];


	function actualizar_campos($registros,$valores)
	{
		//Se quitan todos los valores que hayan venido con NULL
		$valores = array_filter($valores, function($elem){
			return ($elem !== null && $elem !== '');
		});
		$consultas = array();
		$set = array();
		foreach ($valores as $campo => $valor) {
			if($valor){
				$set[] = $campo . " = " . quote($valor); 
			}
		}
		$set = implode(',',$set);
		foreach($registros as $registro){	
			$sql = "UPDATE be_becas_otorgadas 
					SET $set 
					WHERE id_convocatoria = " . quote($registro['id_convocatoria']) ."
					AND id_tipo_beca      = " . quote($registro['id_tipo_beca']) ."
					AND nro_documento     = " . quote($registro['nro_documento']);
			$consultas[] = $sql;
		}

		try {
			toba::db()->abrir_transaccion();
			foreach ($consultas as $consulta) {
				toba::db()->ejecutar($consulta);
			}
			toba::db()->cerrar_transaccion();
			return TRUE;
		} catch (toba_error_db $e) {
			return $e->get_mensaje();
		}
		
	}

	function avalar_decanato_postulacion($postulacion,$resultado){
		$fecha = date('Y-m-d H:i:s');
		$sql = 'UPDATE be_inscripcion_avales 
				SET aval_decanato = '.quote($resultado).',
				aval_decanato_fecha = '.quote($fecha).',
				decano_avalo = '.quote(toba::usuario()->get_id()).'
				WHERE nro_documento = '.quote($postulacion['nro_documento']).'
				AND id_tipo_beca    = '.quote($postulacion['id_tipo_beca']).'
				AND id_convocatoria = '.quote($postulacion['id_convocatoria']);
		return toba::db()->ejecutar($sql);
	}

	function avales_activos(){
		$sql = "
		SELECT avales_activos FROM be_convocatoria_beca WHERE id_convocatoria = (
			SELECT max(id_convocatoria) FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3
		)";
		$resultado = toba::db()->consultar_fila($sql);
		return $resultado['avales_activos'];	
	}

	function borrar_recibos($filtro = array())
	{
		if(count($filtro) == 0) throw new toba_error('No se pueden eliminar recibos de sueldo sin establecer un criterio de filtro');
		$where = array();
		$sql = 'SELECT * FROM be_recibos_sueldo';
		if(isset($filtro['mes']) && $filtro['mes']){
			$where[] = "extract(month from fecha_emision) = {$filtro['mes']}";
		}
		if(isset($filtro['anio']) && $filtro['anio']){
			$where[] = "extract(year from fecha_emision) = {$filtro['anio']}";
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		$recibos = toba::db()->consultar($sql);
		
		if(count($recibos) == 0) return;

		foreach($recibos as $recibo){
			$fecha = new Datetime($recibo['fecha_emision']);
			$archivo = sprintf('%s_%u_%s.pdf',$recibo['nro_documento'],$recibo['id_recibo'],$fecha->format('d-m-Y'));
			toba::consulta_php('helper_archivos')->eliminar_archivo('recibos_sueldo/'.$archivo);
			toba::db()->ejecutar('DELETE FROM be_recibos_sueldo WHERE id_recibo = '.quote($recibo['id_recibo']));
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

	/* Es una subconsulta que se repite en varios lugares */
	function dirige_y_no_avalo($nro_documento){
		return " ((insc.nro_documento_dir = " .quote($nro_documento) . ") 
				AND
				(NOT EXISTS (select * 
				from be_inscripcion_avales as a
				join be_inscripcion_conv_beca as i using (nro_documento,id_convocatoria, id_tipo_beca)
				where i.nro_documento = insc.nro_documento 
				and i.id_convocatoria = insc.id_convocatoria	
				and i.id_tipo_beca = insc.id_tipo_beca
				and i.nro_documento_dir = " .quote($nro_documento) . ")))";
	}

	function es_director_beca($nro_documento)
	{
		$sql = "SELECT * FROM be_inscripcion_conv_beca 
				WHERE nro_documento_dir = " . quote($nro_documento) . "
				OR nro_documento_codir  = " . quote($nro_documento);
		return (count(toba::db()->consultar($sql)));
	}

	function existen_convocatorias_vigentes()
	{
		$sql = "select count(*) as cant from be_convocatoria_beca where current_date between fecha_desde and fecha_hasta";
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cant'] > 0);

	}

	function existen_inscripciones($id_convocatoria,$id_tipo_beca = NULL)
	{
		$sql = "SELECT count(*) AS cantidad 
		FROM be_inscripcion_conv_beca 
		WHERE id_convocatoria = ".quote($id_convocatoria);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['cantidad'] > 0);
	}
	//Retorna todos los a�os distintos donde se registraron cumplimientos de obligaciones. Se usa para filtros
	function get_anios_cumplimientos()
	{
		return toba::db()->consultar('SELECT DISTINCT anio FROM be_cumplimiento_obligacion ORDER BY 1 DESC');

	}

	function get_anios_convocatorias()
	{
		$sql = "select distinct extract(year from fecha_desde) as anio 
				FROM be_convocatoria_beca
				ORDER BY anio DESC";
		
		return toba::db()->consultar($sql);
	}

	//Retorna todos los a�os distintos donde se registraron informes de becas. Se usa para filtros
	function get_anios_present_informes()
	{
		return toba::db()->consultar('SELECT DISTINCT EXTRACT(year FROM fecha_presentacion) AS anio FROM be_informe_beca ORDER BY 1 DESC');

	}

	/**
	 * Obtiene todods los a�os distintos para los cuales hay recibos de sueldo (se usa para filtros)
	 * @return array Array con todos los a�os distintos
	 */
	function get_anios_recibos_sueldo()
	{
		return toba::db()->consultar("SELECT DISTINCT EXTRACT(year FROM fecha_emision) AS anio FROM be_recibos_sueldo");
	}

	function get_areas_conocimiento()
	{
		$sql = "SELECT id, descripcion, nombre, aplicable, disciplinas_incluidas
				FROM sap_area_conocimiento WHERE aplicable = 'BECARIOS'";
		return toba::db()->consultar($sql);
	}

	function get_areas_conocimiento_becas()
	{
		$sql = "SELECT id, descripcion, nombre, aplicable, disciplinas_incluidas
				FROM sap_area_conocimiento
				WHERE prefijo_orden_poster is not null";
		return toba::db()->consultar($sql);
	}

	function get_avales_realizados($nro_documento){
		$dni = quote($nro_documento);
		$sql = "SELECT 
					conv.convocatoria,
					tb.tipo_beca,
					per.apellido||', '||per.nombres AS postulante,
					dir.apellido||', '||dir.nombres AS director,
					codir.apellido||', '||codir.nombres AS codirector,
					avalista_dir_beca.apellido||', '||avalista_dir_beca.nombres AS director_avalo_desc,
					avalista_dir_proy.apellido||', '||avalista_dir_proy.nombres AS dir_proyecto_avalo_desc,
					dep.nombre AS facultad,
					lt.nombre AS lugar_trabajo,
					CASE WHEN (av.director_avalo = $dni OR $dni IN (insc.nro_documento_dir, insc.nro_documento_codir)) THEN 
							(CASE av.aval_director WHEN true THEN 'Avalado' ELSE 'Rechazado' END)
						WHEN av.secretario_avalo = $dni THEN 
							(CASE av.aval_secretaria WHEN true THEN 'Avalado' ELSE 'Rechazado' END)
						WHEN av.decano_avalo = $dni THEN 
							(CASE av.aval_decanato WHEN true THEN 'Avalado' ELSE 'Rechazado' END)
						WHEN av.dir_proyecto_avalo = $dni THEN 
							(CASE av.aval_dir_proyecto WHEN true THEN 'Avalado' ELSE 'Rechazado' END)
					END AS resultado
				FROM be_inscripcion_avales AS av
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON insc.nro_documento = av.nro_documento
					AND insc.id_convocatoria = av.id_convocatoria
					AND insc.id_tipo_beca = av.id_tipo_beca
				LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_personas AS codir ON codir.nro_documento = insc.nro_documento_codir
				LEFT JOIN sap_personas AS avalista_dir_beca ON avalista_dir_beca.nro_documento = av.director_avalo
				LEFT JOIN sap_personas AS avalista_dir_proy ON avalista_dir_proy.nro_documento = av.dir_proyecto_avalo
				LEFT JOIN sap_dependencia AS dep ON dep.id = insc.id_dependencia
				LEFT JOIN sap_dependencia AS lt ON lt.id = insc.lugar_trabajo_becario
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = insc.id_convocatoria
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = insc.id_tipo_beca
				WHERE ((av.director_avalo = $dni OR av.secretario_avalo = $dni OR av.decano_avalo = $dni OR av.dir_proyecto_avalo = $dni)
				OR (av.aval_director IS true AND $dni IN (insc.nro_documento_dir, insc.nro_documento_codir)))

				-- Solo convocatorias de CYT
				AND insc.id_convocatoria = (
					SELECT MAX(id_convocatoria) from be_convocatoria_beca WHERE id_tipo_convocatoria = (
						SELECT id_tipo_convocatoria FROM be_tipos_convocatoria WHERE tipo_convocatoria = 'CYT-UNNE'
					)
				)
				ORDER BY insc.id_convocatoria DESC, tipo_beca, postulante";

		return toba::db()->consultar($sql);

	}

	function get_becarios($filtro = array())
	{
		$where = array();
		$sql = "SELECT per.nro_documento,
					per.apellido||', '||per.nombres AS becario,
					coalesce(dir.apellido||', '||dir.nombres,'No declarado') AS director,
					codir.apellido||', '||codir.nombres AS codirector,
					subdir.apellido||', '||subdir.nombres AS subdirector,
					insc.titulo_plan_beca,
					tb.tipo_beca,
					oto.fecha_desde,
					oto.fecha_hasta
				FROM be_becas_otorgadas AS oto
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON insc.id_convocatoria = oto.id_convocatoria
					AND insc.id_tipo_beca = oto.id_tipo_beca
					AND insc.nro_documento = oto.nro_documento
				LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_personas AS codir ON codir.nro_documento = insc.nro_documento_codir
				LEFT JOIN sap_personas AS subdir ON subdir.nro_documento = insc.nro_documento_subdir
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = insc.id_tipo_beca";
		if(isset($filtro['id_proyecto'])){
			$where[] = 'insc.id_proyecto = ' . quote($filtro['id_proyecto']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}
	function get_becarios_vigentes($filtro = array())
	{
		$where = array();
		$sql = "SELECT per.nro_documento, 
					per.apellido||', '||per.nombres AS becario,
					(select apellido||', '||nombres from sap_personas where nro_documento = ins.nro_documento_dir) as director, 
					conv.convocatoria,
					ins.id_dependencia, 
					ins.id_convocatoria,
					dep.nombre AS dependencia,
					ins.id_tipo_beca,
					tb.tipo_beca,
					ot.fecha_desde,
					ot.fecha_hasta
				FROM be_becas_otorgadas AS ot
				LEFT JOIN be_inscripcion_conv_beca AS ins 
					ON ins.nro_documento = ot.nro_documento
					AND ins.id_convocatoria = ot.id_convocatoria
					AND ins.id_tipo_beca = ot.id_tipo_beca
				LEFT JOIN sap_personas AS per ON per.nro_documento = ins.nro_documento
				LEFT JOIN sap_dependencia AS dep ON dep.id = ins.id_dependencia
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = ins.id_tipo_beca
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = ins.id_convocatoria
				-- Esta condici�n evita que se muestren los becarios antes que salga la resoluci�n oficial
				WHERE conv.publicar_resultados = 'S'
				-- Esto evita que se muestren becas viejas (solo se muestran hasta despues de 90 dias de finalizada la beca)
				AND (ot.fecha_hasta + 90) > current_date ";

		if(isset($filtro['nro_documento_dir'])){
			$where[] = "(ins.nro_documento_dir = ".quote($filtro['nro_documento_dir'])." OR ins.nro_documento_codir = ".quote($filtro['nro_documento_dir'])." OR ins.nro_documento_subdir = ".quote($filtro['nro_documento_dir']).")";
		}
		if(isset($filtro['director'])){
			$where[] = "ins.nro_documento_dir in (
							SELECT nro_documento 
							FROM sap_personas 
							WHERE quitar_acentos(apellido) ilike '%'||quitar_acentos(".quote($filtro['director']).")||'%'
							OR quitar_acentos(nombres) ilike '%'||quitar_acentos(".quote($filtro['director']).")||'%'
						)";
		}
		if(isset($filtro['becario'])){
			$where[] = "ins.nro_documento in (
							SELECT nro_documento 
							FROM sap_personas 
							WHERE quitar_acentos(apellido) ilike '%'||quitar_acentos(".quote($filtro['becario']).")||'%'
							OR quitar_acentos(nombres) ilike '%'||quitar_acentos(".quote($filtro['becario']).")||'%'
						)";
		}

		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}

		return toba::db()->consultar($sql);
	}

	function get_becas_otorgadas($filtro = array())
	{
		$where = array();
		$sql = "SELECT 
			insc.nro_documento,
			insc.id_convocatoria,
			insc.id_tipo_beca,
			oto.fecha_desde,
			oto.fecha_hasta,
			oto.fecha_toma_posesion,
			oto.nro_resol,
			per.nro_documento,
			per.apellido||', '||per.nombres AS postulante,
			per.mail as mail_becario,
			per.cuil as cuil_becario, 
			per.sexo AS sexo_postulante,
			CASE per.sexo WHEN 'M' THEN 'Masculino' WHEN 'F' THEN 'Femenino' WHEN 'X' THEN 'No binario' ELSE 'No declarado' END AS sexo_postulante_desc,
			insc.nro_documento_dir,
			dir.apellido||', '||dir.nombres AS director,
			dir.mail as mail_director,
			codir.apellido||', '||codir.nombres AS codirector,
			conv.convocatoria,
			tb.tipo_beca,
			dep.nombre AS dependencia,
			trab.nombre AS lugar_trabajo,
			(SELECT provincia FROM be_provincias WHERE id_provincia = (SELECT id_provincia FROM be_localidades WHERE id_localidad = per.id_localidad)) AS residencia

		FROM be_becas_otorgadas AS oto
		LEFT JOIN be_inscripcion_conv_beca AS insc USING (id_convocatoria, id_tipo_beca, nro_documento)
		LEFT JOIN sap_dependencia AS dep ON dep.id = insc.id_dependencia
		LEFT JOIN sap_dependencia AS trab ON trab.id = insc.lugar_trabajo_becario
		LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
		LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
		LEFT JOIN sap_personas AS codir ON codir.nro_documento = insc.nro_documento_codir
		LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = insc.id_convocatoria
		LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = insc.id_tipo_beca
		ORDER BY per.apellido, per.nombres";
		if(isset($filtro['id_convocatoria']) && $filtro['id_convocatoria']){
			$where[] = "oto.id_convocatoria = ".quote($filtro['id_convocatoria']);
		}
		if(isset($filtro['id_tipo_beca']) && $filtro['id_tipo_beca']){
			$where[] = "oto.id_tipo_beca = ".quote($filtro['id_tipo_beca']);
		}
		if(isset($filtro['solo_vigentes']) && $filtro['solo_vigentes']){
			$where[] = "current_date BETWEEN oto.fecha_desde AND oto.fecha_hasta";
		}
		if(isset($filtro['id_area_conocimiento']) && $filtro['id_area_conocimiento']){
			$where[] = "insc.id_area_conocimiento = " . quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['nro_documento']) && $filtro['nro_documento']){
			$where[] = "insc.nro_documento = " . quote($filtro['nro_documento']);
		}
		if(isset($filtro['dnis_postulantes']) && $filtro['dnis_postulantes']){
			$dnis = implode(',',array_map('quote',explode(',',$filtro['dnis_postulantes'])));
			$where[] = "per.nro_documento in ($dnis)";
		}
		if(isset($filtro['becario']) && $filtro['becario']){
			$where[] = "per.apellido       ILIKE quitar_acentos(" .quote('%'.$filtro['becario'].'%'). ")
					 OR per.nombres        ILIKE quitar_acentos(" .quote('%'.$filtro['becario'].'%'). ")
					 OR per.nro_documento  ILIKE quitar_acentos(" .quote('%'.$filtro['becario'].'%'). ")";
		}
		if(isset($filtro['director']) && $filtro['director']){
			$where[] = "dir.apellido       ILIKE quitar_acentos(" .quote('%'.$filtro['director'].'%'). ")
					 OR dir.nombres        ILIKE quitar_acentos(" .quote('%'.$filtro['director'].'%'). ")
					 OR dir.nro_documento  ILIKE quitar_acentos(" .quote('%'.$filtro['director'].'%'). ")";
		}
		if (isset($filtro['con_informe_presentado']) && $filtro['con_informe_presentado']) {
			$where[] = 'EXISTS (SELECT * FROM be_informe_beca 
						WHERE nro_documento = oto.nro_documento
						AND id_convocatoria = oto.id_convocatoria
						AND id_tipo_beca = oto.id_tipo_beca)';
		}
		if (isset($filtro['id_dependencia']) && is_numeric($filtro['id_dependencia'])) {
			$where[] = "(insc.id_dependencia = " . quote($filtro['id_dependencia']) . "
			OR insc.lugar_trabajo_becario = " . quote($filtro['id_dependencia']) . ")";
		}

		if(isset($filtro['id_tipo_convocatoria']) && $filtro['id_tipo_convocatoria']){
			$where[] = "conv.id_tipo_convocatoria = " . quote($filtro['id_tipo_convocatoria']);
		}

		if(count($where)){
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	function get_becas_otorgadas_becario($nro_documento)
	{
		$sql = "SELECT per.nro_documento,
					conv.id_convocatoria,
					conv.convocatoria, 
					tip.tipo_beca,
					tip.id_tipo_beca,
					oto.fecha_desde,
					oto.fecha_hasta,
					oto.fecha_toma_posesion,
					per.apellido||', '||per.nombres AS becario,
					dir.apellido||', '||dir.nombres AS director,
					CASE 
						WHEN oto.id_tipo_beca = 1  THEN fac.nombre
						WHEN oto.id_tipo_beca <> 1 THEN trab.nombre
						END AS dependencia,
					proy.codigo AS codigo_proyecto,
					trab.nombre AS lugar_trabajo
				FROM be_becas_otorgadas AS oto
				LEFT JOIN be_inscripcion_conv_beca AS insc
				ON insc.id_convocatoria = oto.id_convocatoria
				AND insc.id_tipo_beca = oto.id_tipo_beca
				AND insc.nro_documento = oto.nro_documento
				LEFT JOIN sap_personas AS per ON per.nro_documento = insc.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_dependencia AS fac ON fac.id = insc.id_dependencia
				LEFT JOIN sap_dependencia AS trab ON trab.id = insc.lugar_trabajo_becario
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = insc.id_convocatoria
				LEFT JOIN be_tipos_beca AS tip ON tip.id_tipo_beca = insc.id_tipo_beca
				LEFT JOIN sap_proyectos AS proy ON proy.id = insc.id_proyecto
				WHERE oto.nro_documento = " . quote($nro_documento) ."
				ORDER BY oto.fecha_desde DESC";

		return toba::db()->consultar($sql);

	}

	function get_campo($tabla, $campo, $filtro = array())
	{
		$where = array();
		$sql = "SELECT $campo FROM $tabla";

		if(isset($filtro['id_convocatoria'])){
			$where[] = 'id_convocatoria = ' . quote($filtro['id_convocatoria']);
		}

		if(isset($filtro['id_tipo_beca'])){
			$where[] = 'id_tipo_beca = ' . quote($filtro['id_tipo_beca']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}

		$resultado = toba::db()->consultar_fila($sql);
		if( array_key_exists($campo,$resultado)){
			if($resultado[$campo]){
				return $resultado[$campo];
			}
		}
		return FALSE;
	}

	public function get_categoria_transitoria_concedida_en_dictamen($nro_documento, $id_convocatoria)
	{
/*		$rol_camposDB = [
			'director'    => ['insc' => 'nro_documento_dir'   , 'dict' => 'categoria_concedida_dir'   ],
			'codirector'  => ['insc' => 'nro_documento_codir' , 'dict' => 'categoria_concedida_codir' ],
			'subdirector' => ['insc' => 'nro_documento_subdir', 'dict' => 'categoria_concedida_subdir'],
		];

		if (!array_key_exists($rol, $rol_camposDB) {
			return false;
		}*/
		$roles = ['dir', 'codir', 'subdir'];

		$id_convocatoria = quote($id_convocatoria);
		$dni             = quote($nro_documento);
		
		$sql = [];
		foreach ($roles as $rol) {
			$sql[] = "SELECT evaluadores, categoria_concedida_{$rol} AS categoria
						FROM be_dictamen AS dic
						WHERE dic.id_convocatoria = $id_convocatoria
						AND categoria_concedida_{$rol} IS NOT NULL
						AND (id_convocatoria, id_tipo_beca, nro_documento) IN (
						    SELECT id_convocatoria, id_tipo_beca, nro_documento 
						    FROM be_inscripcion_conv_beca 
						    WHERE id_convocatoria = $id_convocatoria
						    AND nro_documento_{$rol} = $dni
						)";
		}
		$sql = implode(' UNION ', $sql);
		toba::consulta_php('helper_archivos')->log($sql, 'TEST');
		$resultado = toba::db()->consultar($sql);


		$categorias = [];
		foreach ($resultado as $categoria_otorgada) {
			
			$evaluadores = explode('/', $categoria_otorgada['evaluadores']);

			if (isset($categorias[$categoria_otorgada['categoria']])) {
				$categorias[$categoria_otorgada['categoria']] = array_values(array_unique(array_merge($categorias[$categoria_otorgada['categoria']], $evaluadores )));
			} else {
				$categorias[$categoria_otorgada['categoria']] = $evaluadores;
			}
		}
		$this->loguear_escenarios_no_deseados($categorias, $nro_documento);
		return $categorias;
	}

	function get_convocatorias($filtro=array(),$solo_vigentes=TRUE)
	{
		$where = array();
		if (isset($filtro['id_tipo_convocatoria'])) {
			$where[] = "conv.id_tipo_convocatoria = ".quote($filtro['id_tipo_convocatoria']);
		}
		if (isset($filtro['id_convocatoria'])) {
			$where[] = "conv.id_convocatoria = ".quote($filtro['id_convocatoria']);
		}
		if (isset($filtro['convocatoria'])) {
			$where[] = "conv.convocatoria ILIKE ".quote('%'.$filtro['convocatoria'].'%');
		}
		if (isset($filtro['anio'])) {
			$where[] = quote($filtro['anio'])." between extract(year from conv.fecha_desde) and extract(year from conv.fecha_hasta)";
		}
		
		$sql = "SELECT
			conv.id_convocatoria,
			tip.id_tipo_convocatoria,
			tip.tipo_convocatoria,
			conv.convocatoria,
			conv.fecha_desde,
			conv.fecha_hasta,
			conv.limite_movimientos
		FROM be_convocatoria_beca as conv
		LEFT JOIN be_tipos_convocatoria as tip on tip.id_tipo_convocatoria = conv.id_tipo_convocatoria
		WHERE 1=1";
		$sql .= $solo_vigentes ? " AND current_date BETWEEN conv.fecha_desde AND conv.fecha_hasta" : "";
		$sql .= " ORDER BY conv.fecha_desde DESC";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}

		return toba::db()->consultar($sql);
	}

	/**
	 * Retorna un listado de convocatorias para la cual no existan inscripciones realizadas.
	 * @return array Array de convocatorias (vigentes o no) que no hayan registrado inscripciones
	 */
	function get_convocatorias_sin_inscripciones()
	{
		
	}

	function get_convocatorias_todas($filtro = array())
	{
		return $this->get_convocatorias($filtro,FALSE);
	}

	function get_criterios_evaluacion($filtro = [])
	{
		$where = [];

		$sql = "SELECT cri.id_convocatoria, 
						cri.id_tipo_beca, 
						cri.id_criterio_evaluacion, 
						cri.puntaje_maximo, 
						cri.criterio_evaluacion,
						conv.convocatoria,
						tip.tipo_beca,
						cri.puntaje_maximo
				FROM be_tipo_beca_criterio_eval AS cri
				LEFT JOIN be_tipos_beca AS tip ON tip.id_tipo_beca = cri.id_tipo_beca
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = cri.id_convocatoria
				ORDER BY cri.id_convocatoria DESC, cri.id_tipo_beca ASC, cri.id_criterio_evaluacion";

		if (!empty($filtro['id_convocatoria'])) {
			$where[] = "cri.id_convocatoria = " . quote($filtro['id_convocatoria']);
		}

		if (!empty($filtro['id_tipo_beca'])) {
			$where[] = "cri.id_tipo_beca = " . quote($filtro['id_tipo_beca']);
		}

		if (!empty($filtro['id_criterio_evaluacion'])) {
			$where[] = "cri.id_criterio_evaluacion = " . quote($filtro['id_criterio_evaluacion']);
		}

		if (count($where)) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	// TODO: sacar esto cuando pase la convocatoria de becas donde se evaluan las catetorias transitorias de incentivos
	private function loguear_escenarios_no_deseados($categorias, $nro_documento){
		
		// Si count($categorias) > 1 es porque a una persona se la evalu� con/se le concedieron dos categorias distintas
		// Este escenario no es deseable porque puede hacer que los becarios se quejen: a uno le asignen puntos por una
		// categoria de su director, y a otro becario se le asigne mas o menos puntaje por el mismo director.
		if (count($categorias) > 1) {
			$director              = toba::consulta_php('co_personas')->get_ayn($nro_documento);
			$categorias_concedidas = implode(', ', array_keys($categorias));
			toba::consulta_php('helper_archivos')->log('El director ' . $director . ' fue evaluado con dos categor�as distintas: ' . $categorias_concedidas, 'EVAL_CAT_TRANSITORIA');
		}

		
		// Si una categoria no fue aceptada y el docente no tiene categoria I, II o III, se lo debe excluir de la direccion (es un caso grave)
		if (array_key_exists('N', $categorias)) {
			$director           = toba::consulta_php('co_personas')->get_ayn($nro_documento);
			$categoria_director = toba::consulta_php('co_personas')->get_categoria_incentivos($nro_documento);
			
			if (!$categoria_director || in_array($categoria_director, [4,5])) {
				toba::consulta_php('helper_archivos')->log('Al director ' . $director . ' no le aceptaron su categoria transitoria y NO PUEDE DIRIGIR LA BECA', 'EVAL_CAT_TRANSITORIA');
			}
		}
	}

	/**
	 * Este funci�n es �til para obtener solamente las dependencias declaradas en una convocatoria de becas.
	 * Sirve para usar en combos de selecci�n, y as� evitar listar al usuario un monton de dependencias
	 * para las cuales no va a haber resultados
	 * @return array
	 */
	public function get_dependencias_por_convocatoria($id_convocatoria = null)
	{
		if (!$id_convocatoria) {
			$id_convocatoria = $this->get_id_ultima_convocatoria(true);
			if (!$id_convocatoria) return [];
		}

		$id_convocatoria = quote($id_convocatoria);

		// EL case se usa por si la dependencia no tiene universidad asignada (rompe al listar en TOBA)
		$sql = "SELECT DISTINCT id, sigla, nombre, CASE WHEN universidad_dependencia IS NOT NULL THEN universidad_dependencia ELSE nombre END AS universidad_dependencia 
				FROM (
					/* JOIN con insc.id_dependencia */
					SELECT dep.id, uni.sigla, dep.nombre, uni.sigla||' - '||dep.nombre AS universidad_dependencia
					FROM be_inscripcion_conv_beca AS insc
					LEFT JOIN sap_dependencia AS dep ON dep.id = insc.id_dependencia
					LEFT JOIN be_universidades AS uni ON uni.id_universidad = dep.id_universidad
					WHERE insc.id_convocatoria = $id_convocatoria
					UNION
					/* JOIN con insc.lugar_trabajo_becario */
					SELECT dep.id, uni.sigla, dep.nombre, uni.sigla||' - '||dep.nombre AS universidad_dependencia
					FROM be_inscripcion_conv_beca AS insc
					LEFT JOIN sap_dependencia AS dep ON dep.id = insc.lugar_trabajo_becario
					LEFT JOIN be_universidades AS uni ON uni.id_universidad = dep.id_universidad
					WHERE insc.id_convocatoria = $id_convocatoria
				) AS tmp
				ORDER By 2, 3";
		return toba::db()->consultar($sql);

	}

	function get_detalles_beca($id_convocatoria,$id_tipo_beca,$nro_documento)
	{
		$filtro = array('id_convocatoria' => $id_convocatoria,
						'id_tipo_beca'    => $id_tipo_beca,
						'nro_documento'   => $nro_documento);

		$resultado = $this->get_becas_otorgadas($filtro);
		return (count($resultado)) ? $resultado[0] : FALSE;
	}

	function get_detalles_informe($id_informe)
	{
		$where = array();

		$sql = "SELECT 
					--Postulante
					per.nro_documento,
					per.apellido||', '||per.nombres as postulante,
					--Director
					dir.nro_documento AS nro_documento_dir,
					dir.apellido||', '||dir.nombres as director,
					--Co-Director
					codir.nro_documento AS nro_documento_codir,
					codir.apellido||', '||codir.nombres as codirector,
					--Sub-Director
					subdir.nro_documento AS nro_documento_subdir,
					subdir.apellido||', '||subdir.nombres as subdirector,
					insc.titulo_plan_beca,
					conv.convocatoria,
					tb.tipo_beca AS tipo_beca_desc,
					oto.nro_resol,
					CASE ib.tipo_informe WHEN 'A' THEN 'Avance' WHEN 'F' THEN 'Final' ELSE 'No identificado' END as tipo_informe_desc,
					ac.nombre AS area_conocimiento_desc,
					ib.id_informe,
					ib.fecha_presentacion,
					CASE ie.resultado WHEN 'A' THEN 'Aprobado' WHEN 'M' THEN 'A modificar' WHEN 'N' THEN 'No aprobado' ELSE 'No evaluado' END as evaluacion_desc,
					ie.observaciones,
					ie.fecha AS fecha_evaluacion
				FROM be_informe_beca AS ib 
				LEFT JOIN be_informe_evaluacion AS ie ON ib.id_informe = ie.id_informe
				LEFT JOIN be_becas_otorgadas AS oto 
					ON oto.id_convocatoria = ib.id_convocatoria
					AND oto.id_tipo_beca   = ib. id_tipo_beca
					AND oto.nro_documento  = ib.nro_documento
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON insc.id_convocatoria = ib.id_convocatoria
					AND insc.id_tipo_beca   = ib. id_tipo_beca
					AND insc.nro_documento  = ib.nro_documento
				LEFT JOIN sap_personas AS per ON per.nro_documento = ib.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_personas AS codir ON codir.nro_documento = insc.nro_documento_codir
				LEFT JOIN sap_personas AS subdir ON subdir.nro_documento = insc.nro_documento_subdir
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = ib.id_convocatoria
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = ib.id_tipo_beca
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = insc.id_area_conocimiento
				LEFT JOIN sap_dependencia AS dep ON dep.id = (SELECT CASE insc.id_tipo_beca WHEN 1 THEN insc.id_dependencia ELSE insc.lugar_trabajo_becario END)
				WHERE ib.id_informe = " . quote($id_informe) . "
				ORDER BY postulante
				LIMIT 1";

		$resultado['informe'] = toba::db()->consultar_fila($sql);

		//Obtengo los evaluadores
		$sql = "SELECT apellido, nombres, nro_documento FROM sap_personas WHERE nro_documento IN (
    				SELECT UNNEST(string_to_array(evaluadores,','))
    				FROM be_informe_evaluacion AS ie
    				WHERE id_informe = " . quote($id_informe) . "
    			)";
		$resultado['evaluadores'] = toba::db()->consultar($sql);

		return $resultado;
	}

		/**
	 * Para una determinada convocatoria y tipo de beca, retorna cuales son los informes que deben presentarse 
	 */
	
	public function get_detalle_informes_por_convocatoria_y_tipo_beca($id_convocatoria, $id_tipo_beca)
	{
		if (!($id_convocatoria && $id_tipo_beca)) return false;

		$infomes_a_presentar = [];
		$ordinales           = [
			'1' => 'Primer', 
			'2' => 'Segundo',
			'3' => 'Tercer',
			'4' => 'Cuarto',
			'5' => 'Quinto',
			'6' => 'Sexto',
			'7' => 'Septimo',
			'8' => 'Octavo',
			'9' => 'Noveno',
		];

		// Obtengo los detalles del tipo de beca
		$sql       = "SELECT * FROM be_tipos_beca WHERE id_tipo_beca = " . quote($id_tipo_beca);
		$tipo_beca = toba::db()->consultar_fila($sql);

		// Obtengo los detalles de la convocatoria
		$sql          = "SELECT * FROM be_convocatoria_beca WHERE id_convocatoria = " . quote($id_convocatoria);
		$convocatoria = toba::db()->consultar_fila($sql);

		// Obtengo la fecha de inicio de beca para esta convocatoria y tipo de beca (se considera la que tiene mayor numero de ocurrencias)
		$sql = "SELECT COUNT(*), fecha_desde 
				FROM be_becas_otorgadas 
				WHERE id_convocatoria = " . quote($id_convocatoria) . "
				AND id_tipo_beca = " . quote($id_tipo_beca) . "
				GROUP BY fecha_desde
				ORDER BY 1 DESC
				LIMIT 1";
		$beca = toba::db()->consultar_fila($sql);
		
		$cantidad_informes_a_presentar = intval($tipo_beca['duracion_meses']) / intval($tipo_beca['meses_present_avance']);
		
		for ($i=1; $i <= $cantidad_informes_a_presentar ; $i++) { 
			if ($i == $cantidad_informes_a_presentar) {
				$nombre_informe = 'Informe Final';
			} else {
				$nombre_informe = $ordinales[$i] . ' Avance';
			} 

			// Calculo la fecha a partir de la cual deber�a poder presentarse el informe
			$inicio_beca              = new Datetime($beca['fecha_desde']);
			$meses_transcurridos      = $i * $tipo_beca['meses_present_avance'];
			$intervalo_a_sumar        = new DateInterval('P'. $meses_transcurridos . 'M');
			$fecha_presentacion_desde = $inicio_beca->add($intervalo_a_sumar);

			$informes_a_presentar[] = [
				'numero_informe' => $i,
				'nombre_informe' => $nombre_informe,
				'fecha_desde'    => $fecha_presentacion_desde->format('Y-m-d'),
			];
		}

		return $informes_a_presentar;

	}

	function get_disciplinas_incluidas($id_area_conocimiento)
    {
    	$sql = "SELECT disciplinas_incluidas FROM sap_area_conocimiento WHERE id = ".quote($id_area_conocimiento);
    	$resultado = toba::db()->consultar_fila($sql);
    	return count($resultado) ? $resultado['disciplinas_incluidas'] : '';
    }

	function get_duracion_meses_beca($inscripcion)
	{
		$sql = "SELECT 
					--Extraigo la cantidad de a�os de la beca, y lo multiplico por 12 (para pasarlo a meses)
					(EXTRACT(YEAR FROM AGE(fecha_hasta,fecha_desde))*12) +
					--y le sumo los meses restantes
					(EXTRACT(MONTH FROM AGE(fecha_hasta,fecha_desde))) AS duracion
				FROM be_becas_otorgadas AS bo
				WHERE bo.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND bo.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."
				AND bo.nro_documento = ".quote($inscripcion['nro_documento']);
		$res = toba::db()->consultar_fila($sql);
		return $res['duracion'];

	}

	function get_id_ultima_convocatoria($solo_propias = FALSE)
	{
		$sql = "select max(id_convocatoria) as id_convocatoria from be_convocatoria_beca";
		$sql .= ($solo_propias) ? ' where id_tipo_convocatoria = 3' : '';
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['id_convocatoria']) ? $resultado['id_convocatoria'] : NULL;
	}

	function get_inscripciones_pendientes_aval($filtro = array())
	{
		if(isset($filtro['cargos'])){
			$cargos_dep = array_column($filtro['cargos'],'id_dependencia','identificador');
		}
		if(isset($filtro['dirigido_por'])){
			$usuario = quote($filtro['dirigido_por']);
		}

		$where = array();

		if(isset($usuario) && $usuario){
			$sql_dir = "SELECT
				    i.id_convocatoria,
				    i.id_tipo_beca,
				    i.nro_documento
				FROM be_inscripcion_conv_beca AS i
				WHERE i.estado = 'C' 
				AND i.id_convocatoria = (SELECT MAX(id_convocatoria) FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3)
				AND NOT EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_director IS NOT NULL
				    LIMIT 1
				)
				AND (i.nro_documento_dir  = $usuario
				OR i.nro_documento_codir  = $usuario
				OR i.nro_documento_subdir = $usuario)";

			$sql_dir_proy = "SELECT
				    i.id_convocatoria,
				    i.id_tipo_beca,
				    i.nro_documento
				FROM be_inscripcion_conv_beca AS i
				WHERE i.estado = 'C' 
				AND i.id_convocatoria = (SELECT MAX(id_convocatoria) FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3)
				--Que SI haya sido avalado por el director de la beca
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_director IS NOT NULL
				    LIMIT 1
				)
				--Que no haya sido avalado por el director del proyecto (osea, pendiente de aval)
				AND NOT EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_dir_proyecto IS NOT NULL
				    LIMIT 1
				)
				--Que el usuario sea Director, Co-director o Sub-director del proyecto declarado
				AND $usuario IN (
				    SELECT inte.nro_documento
				    FROM sap_proyecto_integrante AS inte
				    WHERE inte.id_funcion IN (
				        SELECT id_funcion 
				        FROM sap_proyecto_integrante_funcion 
				        WHERE identificador_perfil in ('D','C','S')
				    )
				    AND inte.id_proyecto = i.id_proyecto
				    -- Que sea el �ltimo director (o codirector o subdirector)
				    -- Evito que pueda ser avalado por directores que ya no est�n vigentes
				    AND inte.fecha_desde = (
				    	SELECT fecha_desde
				    	FROM sap_proyecto_integrante_funcion
				    	WHERE id_proyecto = inte.id_proyecto 
				    	AND id_funcion = inte.id_funcion
				    	ORDER BY fecha_desde DESC
				    )
				)";

			$sql_secre = "SELECT
				    i.id_convocatoria,
				    i.id_tipo_beca,
				    i.nro_documento
				FROM be_inscripcion_conv_beca AS i
				WHERE i.estado = 'C' 
				AND i.id_convocatoria = (SELECT MAX(id_convocatoria) FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3)
				--Que SI haya sido avalado por el director de la beca
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_director IS NOT NULL
				    LIMIT 1
				)
				--Que SI haya sido avalado por el director del proyecto 
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_dir_proyecto IS NOT NULL
				    LIMIT 1
				)
				--Que NO haya sido avalado por el Secretario de investigaci�n
				AND NOT EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_secretaria IS NOT NULL
				    LIMIT 1
				)
				--Que el usuario sea Secretario de Investigaci�n de la unidad acad�mica
				AND $usuario = (
				    SELECT nro_documento 
				    FROM sap_dependencia_autoridad 
				    WHERE (
				        (id_dependencia = i.id_dependencia AND i.id_tipo_beca = 1) 
				        OR (id_dependencia = i.lugar_trabajo_becario AND i.id_tipo_beca > 1)
				    )
				    AND id_cargo = (select id_cargo from sap_dependencia_cargos where identificador = 'SECRE')
				)";

			$sql_deca = "SELECT
				    i.id_convocatoria,
				    i.id_tipo_beca,
				    i.nro_documento
				FROM be_inscripcion_conv_beca AS i
				WHERE i.estado = 'C' 
				AND i.id_convocatoria = (SELECT MAX(id_convocatoria) FROM be_convocatoria_beca WHERE id_tipo_convocatoria = 3)
				--Que SI haya sido avalado por el director de la beca
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_director IS NOT NULL
				    LIMIT 1
				)
				--Que SI haya sido avalado por el director del proyecto 
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_dir_proyecto IS NOT NULL
				    LIMIT 1
				)
				--Que SI haya sido avalado por el Secretario de investigaci�n
				AND EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_secretaria IS NOT NULL
				    LIMIT 1
				)
				--Que NO haya sido avalado por el Decano
				AND NOT EXISTS (
				    SELECT * 
				    FROM be_inscripcion_avales
				    WHERE nro_documento = i.nro_documento
				    AND id_tipo_beca = i.id_tipo_beca
				    AND id_convocatoria = i.id_convocatoria
				    AND aval_decanato IS NOT NULL
				    LIMIT 1
				)
				--Que el usuario sea Decane :) de la unidad acad�mica
				AND $usuario = (
				    SELECT nro_documento 
				    FROM sap_dependencia_autoridad 
				    WHERE (
				        (id_dependencia = i.id_dependencia AND i.id_tipo_beca = 1) 
				        OR (id_dependencia = i.lugar_trabajo_becario AND i.id_tipo_beca > 1)
				    )
				    AND id_cargo IN (select id_cargo from sap_dependencia_cargos where identificador IN ('DECANO','DIRECTOR') )
				)";

			$sql = implode(' UNION ',array($sql_dir,$sql_dir_proy,$sql_secre,$sql_deca));
			$sql_completo = "SELECT 
				insc.id_convocatoria,
				insc.id_tipo_beca,
				insc.nro_documento,
				postulante.apellido||', '||postulante.nombres as postulante,
				director.apellido||', '||director.nombres as director,
				codirector.apellido||', '||codirector.nombres as codirector,
				tipbec.tipo_beca as tipo_beca_desc,
				insc.titulo_plan_beca,
				car.carrera
				FROM ($sql) AS pendientes
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON insc.nro_documento = pendientes.nro_documento
					AND insc.id_convocatoria = pendientes.id_convocatoria
					AND insc.id_tipo_beca = pendientes.id_tipo_beca
				LEFT JOIN sap_personas AS postulante on postulante.nro_documento = insc.nro_documento
				LEFT JOIN sap_personas AS director on director.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_personas AS codirector on codirector.nro_documento = insc.nro_documento_codir
				LEFT JOIN be_carreras AS car on car.id_carrera = insc.id_carrera
				LEFT JOIN sap_proyectos AS proy on proy.id = insc.id_proyecto
				LEFT JOIN be_tipos_beca AS tipbec on tipbec.id_tipo_beca = insc.id_tipo_beca";

		}

		if(count($where)){
			$sql_completo = sql_concatenar_where($sql_completo,$where);
		}
		
		return toba::db()->consultar($sql_completo);
	}

	function get_informes($filtro = array())
	{
		$where = array();

		$sql = "SELECT 
					ib.id_informe,
					ib.fecha_presentacion,
					ib.nro_informe,
					ib.tipo_informe,
					CASE ib.tipo_informe
						WHEN 'A' THEN 'Avance' 
						WHEN 'F' THEN 'Final' 
						ELSE 'No Presentado' END AS tipo_informe_desc,
					per.nro_documento,
					per.apellido||', '||per.nombres AS postulante,
					per.mail AS mail_postulante,
					dir.apellido||', '||dir.nombres AS director,
					dir.mail AS mail_director,
					conv.id_convocatoria,
					conv.convocatoria,
					tb.id_tipo_beca,
					tb.tipo_beca,
					ac.nombre AS area_conocimiento_desc,
					ie.resultado AS resultado_eval,
					CASE ie.resultado 
						WHEN 'A' THEN 'Aprobado' 
						WHEN 'M' THEN 'A modificar' 
						WHEN 'N' THEN 'No aprobado' 
						ELSE 'No evaluado' END as evaluacion_desc
				FROM be_becas_otorgadas AS oto 
				LEFT JOIN be_informe_beca AS ib 
					ON oto.id_convocatoria = ib.id_convocatoria
					AND oto.id_tipo_beca   = ib. id_tipo_beca
					AND oto.nro_documento  = ib.nro_documento";
				
				if(isset($filtro['nro_informe']) && $filtro['nro_informe']){
					$sql .= " AND ib.nro_informe = " . quote($filtro['nro_informe']);
				}

				$sql .= " LEFT JOIN be_informe_evaluacion AS ie ON ib.id_informe = ie.id_informe
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON insc.id_convocatoria = oto.id_convocatoria
					AND insc.id_tipo_beca   = oto. id_tipo_beca
					AND insc.nro_documento  = oto.nro_documento
				LEFT JOIN sap_personas AS per ON per.nro_documento = oto.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = oto.id_convocatoria
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = oto.id_tipo_beca
				LEFT JOIN sap_area_conocimiento AS ac ON ac.id = insc.id_area_conocimiento
				ORDER BY postulante";

		if(isset($filtro['postulante'])){
			$where[] = "per.apellido||per.nombres ILIKE " . quote('%' . trim(str_replace(array(' ',','),'',$filtro['postulante'])) . '%');
		}	
		if(isset($filtro['id_dependencia'])){
			$dep = quote($filtro['id_dependencia']);
			$where[] = "((insc.id_dependencia = $dep AND insc.id_tipo_beca = 1) OR (insc.lugar_trabajo_becario = $dep AND insc.id_tipo_beca <> 1))";
		}
		if(isset($filtro['id_area_conocimiento'])){
			$where[] = "insc.id_area_conocimiento = " . quote($filtro['id_area_conocimiento']);
		}
		if(isset($filtro['id_convocatoria'])){
			$where[] = "insc.id_convocatoria = " . quote($filtro['id_convocatoria']);
		}
		if(isset($filtro['id_tipo_beca'])){
			$where[] = "insc.id_tipo_beca = " . quote($filtro['id_tipo_beca']);
		}
		if(isset($filtro['estado_evaluacion'])){
			$where[] = ($filtro['estado_evaluacion'] == 'E') ? 'ie.resultado IS NOT NULL' : 'ie.resultado IS NULL';
		}
		 
		if(isset($filtro['estado_presentacion'])) {
			if ($filtro['estado_presentacion'] == 'N') {
				$where[] = 'ib.fecha_presentacion IS NULL';
			} else {
				$where[] = 'ib.fecha_presentacion IS NOT NULL';
			}
		} 
		if(isset($filtro['resultado_evaluacion'])){
			$where[] = "ie.resultado IS NOT NULL AND ie.resultado = " . quote($filtro['resultado_evaluacion']);
		}
		
		if(isset($filtro['anio_presentacion'])){
			$where[] = "EXTRACT(year FROM ib.fecha_presentacion) = " . quote($filtro['anio_presentacion']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql, $where);
		}

		return toba::db()->consultar($sql);
	}

	public function get_solicitud_transitoria_incentivos($nro_documento, $id_llamado)
	{

		
		if (!($nro_documento && $id_llamado)) {
			return [];
		}

		$sql = 'SELECT * 
				FROM sap_cat_inc_transitorio AS cit 
				WHERE cit.nro_documento = ' . quote($nro_documento) . '
				AND cit.id_llamado = ' . quote($id_llamado) . '
				LIMIT 1';
		return toba::db()->consultar_fila($sql);
	}

	public function get_solicitud_transitoria_incentivos_becas($nro_documento, $id_convocatoria)
	{	
		$sql = "SELECT id 
				FROM sap_cat_inc_transitorio_llamado 
				WHERE id_convocatoria = " . quote($id_convocatoria) . " 
				AND aplica_a_becas = true";
		$resultado = toba::db()->consultar_fila($sql);
		return $this->get_solicitud_transitoria_incentivos($nro_documento, $resultado['id']);
	}

	public function get_solicitud_transitoria_incentivos_documentacion($id_categoria, $id_llamado)
	{
		$sql = "SELECT * 
				FROM sap_cat_inc_trans_documentacion 
				WHERE aplica_a_categoria = " . quote($id_categoria) . "
				AND id_llamado = " . quote($id_llamado);
		return toba::db()->consultar($sql); 
	}

	public function get_subcriterios_evaluacion($id_convocatoria, $id_tipo_beca)
	{
		$id_convocatoria = quote($id_convocatoria);
		$id_tipo_beca    = quote($id_tipo_beca);

		$sql = "SELECT *
				FROM be_tipo_beca_criterio_eval AS cri
				LEFT JOIN be_subcriterio_evaluacion AS sub 
				    ON sub.id_convocatoria = cri.id_convocatoria
				    AND sub.id_tipo_beca = cri.id_tipo_beca
				    AND sub.id_criterio_evaluacion = cri.id_criterio_evaluacion
				WHERE cri.id_convocatoria = $id_convocatoria
				AND cri.id_tipo_beca = $id_tipo_beca";
		return toba::db()->consultar($sql);
	}

	function get_tipos_beca($filtro = array())
	{
		$where = array();
		if(isset($filtro['id_tipo_beca'])){
			$where[] = 'tip.id_tipo_beca = '.quote($filtro['id_tipo_beca']);
		}
		if(isset($filtro['solo_activas'])){
			$where[] = 'tip.solo_activas = true';
		}
		$sql = "SELECT
			tip.id_tipo_beca,
			tip_con.tipo_convocatoria,
			tip.tipo_beca,
			'('||tip_con.tipo_convocatoria||') '||tip.tipo_beca as tipo_conv_tipo_beca,
			tip.duracion_meses,
			tip.meses_present_avance,
			tip.cupo_maximo,
			tip.id_color,
			tip.factor,
			tip.edad_limite,
			tip.prefijo_carpeta,
			col.color,
			tip.requiere_insc_posgrado,
			tip.debe_adeudar_hasta,
			tip.suma_puntaje_academico,
			tip.puntaje_academico_maximo	
		FROM be_tipos_beca as tip	
		LEFT JOIN be_tipos_convocatoria as tip_con ON tip.id_tipo_convocatoria = tip_con.id_tipo_convocatoria
		LEFT JOIN be_color_carpeta as col on col.id_color = tip.id_color
		ORDER BY tipo_beca";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db()->consultar($sql);
	}

	function marcar_cumplido($detalles)
	{
		return $this->insertar_cumplimiento($detalles, true);
	}

	function marcar_no_cumplido($detalles)
	{
		return $this->insertar_cumplimiento($detalles, false);
	}

	public function obtener_criterios_ordenados($id_convocatoria, $id_tipo_beca)
	{
		$criterios = $this->get_subcriterios_evaluacion($id_convocatoria, $id_tipo_beca);
		$criterios_ordenados = [];
		
		
		foreach ($criterios as $criterio) {
			if (!isset($criterios_ordenados[$criterio['id_criterio_evaluacion']])) {
				$criterios_ordenados[$criterio['id_criterio_evaluacion']] = [
					'id_criterio_evaluacion' => $criterio['id_criterio_evaluacion'],
					'id_convocatoria'        => $criterio['id_convocatoria'],
					'id_convocatoria'        => $criterio['id_convocatoria'],
					'id_tipo_beca'           => $criterio['id_tipo_beca'],   
					'criterio_evaluacion'    => $criterio['criterio_evaluacion'],
					'puntaje_maximo'         => $criterio['puntaje_maximo'],
				];
			}

			$criterios_ordenados[$criterio['id_criterio_evaluacion']]['subcriterios'][] = [
				'id_subcriterio_evaluacion'  => $criterio['id_subcriterio_evaluacion'],
				'descripcion'                => $criterio['descripcion'],
				'referencia'                 => $criterio['referencia'],
				'maximo'                     => $criterio['maximo'],
			];
		}
		
		return $criterios_ordenados;
	}	

	function insertar_cumplimiento($detalles, $cumplido)
	{
		$sql = "INSERT INTO be_cumplimiento_obligacion (
				nro_documento,
				id_convocatoria,
				id_tipo_beca,
				mes,
				anio,
				fecha_carga, 
				cumplido) 
			VALUES (".
				quote($detalles['nro_documento']).",".
				quote($detalles['id_convocatoria']).",".
				quote($detalles['id_tipo_beca']).",".
				quote($detalles['mes']).",".
				quote($detalles['anio']).",".
				quote(date('Y-m-d')).",".
				var_export($cumplido,1).")";
		return toba::db()->ejecutar($sql);
	}

	function eliminar_cumplimiento($filtro_borrado)
	{
		$sql = "DELETE FROM be_cumplimiento_obligacion 
				WHERE nro_documento   = " . quote($filtro_borrado['nro_documento']) . "
				AND   id_convocatoria = " . quote($filtro_borrado['id_convocatoria']) . "
				AND   id_tipo_beca    = " . quote($filtro_borrado['id_tipo_beca']) . "
				AND   mes             = " . quote($filtro_borrado['mes']) . "
				AND   anio            = " . quote($filtro_borrado['anio']);
		return toba::db()->ejecutar($sql);
	}

	function get_resultado_cumplimiento($beca,$mes,$anio)
	{
		$sql = "SELECT cumplido 
				FROM be_cumplimiento_obligacion
				WHERE nro_documento = ".quote($beca['nro_documento'])."
				AND id_tipo_beca = ".quote($beca['id_tipo_beca'])."
				AND id_convocatoria = ".quote($beca['id_convocatoria'])."
				AND mes = $mes
				AND anio = $anio";
		$resultado = toba::db()->consultar_fila($sql);
		return isset($resultado['cumplido']) ? (bool)$resultado['cumplido'] : null;
	}

	function get_resumen_inscripcion($inscripcion)
	{
		$sql = "SELECT 	
				postulante.nro_documento,
				upper(postulante.apellido)||', '||postulante.nombres as postulante,
			    upper(director.apellido)||', '||director.nombres as director,
			    (select categoria from sap_cat_incentivos where nro_documento = director.nro_documento and convocatoria = (select max(convocatoria) from sap_cat_incentivos where nro_documento = director.nro_documento)) as cat_incentivos_dir,
			    coalesce(upper(codirector.apellido)||', '||codirector.nombres,'No tiene') as codirector,
			    (select categoria from sap_cat_incentivos where nro_documento = codirector.nro_documento and convocatoria = (select max(convocatoria) from sap_cat_incentivos where nro_documento = codirector.nro_documento)) as cat_incentivos_codir,
			    coalesce(upper(subdirector.apellido)||', '||subdirector.nombres,'No tiene') as subdirector,
				tipbec.tipo_beca,
				car.carrera,
				postulante.fecha_nac,
				insc.nro_documento_dir,
				insc.nro_documento_codir,
				insc.nro_documento_subdir,
				insc.id_convocatoria,
				insc.id_tipo_beca,
				insc.titulo_plan_beca,
				insc.materias_aprobadas,
				insc.anio_ingreso,
				insc.materias_plan,
				insc.prom_hist,
				proy.descripcion AS proyecto_desc,
				proy.codigo AS proyecto_codigo,
				proy.id AS proyecto_id,
				proy.fecha_desde AS proy_fecha_desde,
				proy.fecha_hasta AS proy_fecha_hasta,
				lugtrab.nombre AS lugar_trabajo_becario
				FROM be_inscripcion_conv_beca AS insc
				LEFT JOIN sap_personas AS postulante ON postulante.nro_documento = insc.nro_documento
				LEFT JOIN sap_personas AS director ON director.nro_documento = insc.nro_documento_dir
				LEFT JOIN sap_personas AS codirector ON codirector.nro_documento = insc.nro_documento_codir
				LEFT JOIN sap_personas AS subdirector ON subdirector.nro_documento = insc.nro_documento_subdir
				LEFT JOIN be_carreras AS car ON car.id_carrera = insc.id_carrera
				LEFT JOIN sap_proyectos AS proy ON proy.id = insc.id_proyecto
				LEFT JOIN be_tipos_beca AS tipbec ON tipbec.id_tipo_beca = insc.id_tipo_beca
				LEFT JOIN sap_dependencia AS lugtrab ON lugtrab.id = insc.lugar_trabajo_becario
				WHERE insc.nro_documento = ".quote($inscripcion['nro_documento'])."
				AND insc.id_convocatoria = ".quote($inscripcion['id_convocatoria'])."
				AND insc.id_tipo_beca = ".quote($inscripcion['id_tipo_beca'])."";

		$resultado = toba::db()->consultar_fila($sql);
		$sql = "SELECT UPPER(per.apellido)||', '||per.nombres AS director_proyecto,
				   (SELECT categoria FROM sap_cat_incentivos WHERE nro_documento = per.nro_documento ORDER BY convocatoria DESC LIMIT 1) AS cat_incentivos_dir_proyecto
				FROM sap_proyecto_integrante AS inte
				LEFT JOIN sap_personas AS per ON per.nro_documento = inte.nro_documento
				WHERE inte.id_proyecto = ".$resultado['proyecto_id']."
				AND inte.id_funcion = (SELECT id_funcion FROM sap_proyecto_integrante_funcion WHERE identificador_perfil = 'D') 
				ORDER BY fecha_desde DESC LIMIT 1";
		$dir = toba::db()->consultar_fila($sql);
		$dir_proyecto = ($dir) ? $dir : array('director_proyecto'=>NULL,'cat_incentivos_dir_proyecto'=>NULL);
		return array_merge($resultado,$dir_proyecto);
	}

	public function get_requisitos_categoria_incentivos_transitoria($id_llamado, $id_categoria, $solo_activos = true)
	{
		$sql = "SELECT * 
				FROM sap_cat_inc_trans_documentacion 
				WHERE aplica_a_categoria = " . quote($id_categoria) . "
				AND id_llamado = " . quote($id_llamado);
		if ($solo_activos) {
			$sql .= ' AND activo = 1';
		}

		return toba::db()->consultar($sql);
	}

	function get_requisitos_categoria_incentivos_transitoria_becas($id_convocatoria, $id_categoria, $solo_activos = true)
	{
		//echo __FUNCTION__; var_dump($id_convocatoria); var_dump($id_categoria);
		$sql = $sql = "SELECT id 
				FROM sap_cat_inc_transitorio_llamado 
				WHERE id_convocatoria = " . quote($id_convocatoria) . " 
				AND aplica_a_becas = true";
		
		$resultado = toba::db()->consultar_fila($sql);
		return $this->get_requisitos_categoria_incentivos_transitoria($resultado['id'], $id_categoria);
	}

	function get_tipos_beca_por_convocatoria($id_convocatoria, $solo_activas = false)
	{
		$sql = "SELECT tb.id_tipo_beca, tb.tipo_beca
				FROM be_convocatoria_beca as cb
				LEFT JOIN be_tipos_convocatoria as tc on tc.id_tipo_convocatoria = cb.id_tipo_convocatoria
				LEFT JOIN be_tipos_beca as tb on tb.id_tipo_convocatoria = cb.id_tipo_convocatoria
				WHERE cb.id_convocatoria = ".quote($id_convocatoria);
				if ($solo_activas) {
					$sql .= " AND tb.estado = 'A'";
				}
		return toba::db()->consultar($sql);
	}

	function get_tipos_beca_activas_por_convocatoria($id_convocatoria)
	{
		return $this->get_tipos_beca_por_convocatoria($id_convocatoria, true);
	}

	function get_tipos_beca_por_tipo_convocatoria($id_tipo_convocatoria)
	{
		$sql = "SELECT tb.id_tipo_beca, tb.tipo_beca
				FROM be_convocatoria_beca as cb
				LEFT JOIN be_tipos_convocatoria as tc on tc.id_tipo_convocatoria = cb.id_tipo_convocatoria
				LEFT JOIN be_tipos_beca as tb on tb.id_tipo_convocatoria = cb.id_tipo_convocatoria
				WHERE cb.id_tipo_convocatoria = ".quote($id_tipo_convocatoria)."
				AND tb.estado = 'A'";
		return toba::db()->consultar($sql);
	}



	function requiere_posgrado($id_tipo_beca)
	{
		//si no se recibi ningun tipo de beca, se asume la respuesta "SI"
		if(!$id_tipo_beca){
			return TRUE;
		}
		$resultado = toba::db()->consultar_fila("SELECT requiere_insc_posgrado FROM be_tipos_beca WHERE id_tipo_beca = ".quote($id_tipo_beca));
		if(count($resultado) > 0){
			return ($resultado['requiere_insc_posgrado'] == 'S') ? TRUE : FALSE;
		}else{
			return TRUE;
		}
	}

	public function suma_puntaje_academico($id_tipo_beca)
	{
		$sql = "SELECT suma_puntaje_academico FROM be_tipos_beca WHERE id_tipo_beca = ".quote($id_tipo_beca);
		$resultado = toba::db()->consultar_fila($sql);
		return ($resultado['suma_puntaje_academico'] == 'S');
	}


	
	function get_cumplimientos_mes($filtro = array())
	{
		if( ! (isset($filtro['mes']) && isset($filtro['anio']))){
			throw new toba_error('Debe establecer un mes y a�o');
		}
		$where = array();

		$fecha_ref = sprintf('%s-%02d-15', $filtro['anio'], $filtro['mes']);

		$sql = "SELECT 
					conv.convocatoria,
					tb.tipo_beca,
					insc.nro_documento,
					per.apellido||', '||per.nombres as postulante,
					per.mail,
					insc.nro_documento_dir,
					dir.apellido||', '||dir.nombres as director,
					dir.mail AS mail_director,
					CASE 
						WHEN co.cumplido = TRUE THEN 'Cumplido'
						WHEN co.cumplido = FALSE THEN 'NO Cumplido'
						WHEN co.cumplido IS NULL THEN 'No registrado'
						END AS cumplido,
					co.fecha_carga,
					bo.fecha_desde,
					bo.fecha_hasta
				FROM be_becas_otorgadas AS bo 
				LEFT JOIN be_inscripcion_conv_beca AS insc 
					ON  insc.nro_documento   = bo.nro_documento
					AND insc.id_convocatoria = bo.id_convocatoria
					AND insc.id_tipo_beca    = bo.id_tipo_beca
				LEFT JOIN sap_personas AS per ON per.nro_documento = bo.nro_documento
				LEFT JOIN sap_personas AS dir ON dir.nro_documento = insc.nro_documento_dir
				LEFT JOIN be_convocatoria_beca AS conv ON conv.id_convocatoria = bo.id_convocatoria
				LEFT JOIN be_tipos_beca AS tb ON tb.id_tipo_beca = bo.id_tipo_beca
				LEFT JOIN be_cumplimiento_obligacion AS co 
					ON  co.nro_documento   = bo.nro_documento
					AND co.id_convocatoria = bo.id_convocatoria
					AND co.id_tipo_beca    = bo.id_tipo_beca
					AND co.mes = {$filtro['mes']}
					AND co.anio = {$filtro['anio']}
				WHERE " . quote($fecha_ref) . " BETWEEN bo.fecha_desde AND bo.fecha_hasta
				ORDER BY convocatoria, tipo_beca, postulante";

		if(isset($filtro['id_dependencia']) && $filtro['id_dependencia']){
			$where[] = "insc.id_dependencia = " . quote($filtro['id_dependencia']);
		}
		
		if(isset($filtro['estado_cumplimiento']) && $filtro['estado_cumplimiento']){
			if($filtro['estado_cumplimiento'] == 'C'){
				$where[] = 'co.cumplido = true';
			}
			if($filtro['estado_cumplimiento'] == 'N'){
				$where[] = 'co.cumplido = false';
			}
			if($filtro['estado_cumplimiento'] == 'S'){
				$where[] = 'co.cumplido IS NULL';
			}
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		return toba::db()->consultar($sql);
	}

	function get_cumplimientos_becario($nro_documento,$id_convocatoria,$id_tipo_beca){
		$sql = "SELECT *,
					CASE mes 
						WHEN '01' THEN 'Enero'
						WHEN '02' THEN 'Febrero'
						WHEN '03' THEN 'Marzo'
						WHEN '04' THEN 'Abril'
						WHEN '05' THEN 'Mayo'
						WHEN '06' THEN 'Junio'
						WHEN '07' THEN 'Julio'
						WHEN '08' THEN 'Agosto'
						WHEN '09' THEN 'Septiembre'
						WHEN '10' THEN 'Octubre'
						WHEN '11' THEN 'Noviembre'
						WHEN '12' THEN 'Diciembre'
					END AS mes_desc,
					cumplido
				FROM be_cumplimiento_obligacion
				WHERE nro_documento = ".quote($nro_documento)."
				AND id_convocatoria = ".quote($id_convocatoria)."
				AND id_tipo_beca = ".quote($id_tipo_beca)."
				ORDER BY anio DESC, mes DESC";
		return toba::db()->consultar($sql);
	}

	function get_recibos_sueldo($filtro = array())
	{
		$where = array();
		$sql = "SELECT per.apellido||', '||per.nombres AS becario, rec.* 
				FROM be_recibos_sueldo AS rec
				LEFT JOIN sap_personas AS per ON per.nro_documento = rec.nro_documento
				ORDER BY becario";
		if(isset($filtro['becario']) && $filtro['becario']){
			$where[] = "(per.apellido      ILIKE quitar_acentos(".quote('%'.$filtro['becario'].'%').") OR 
						per.nombres       ILIKE quitar_acentos(".quote('%'.$filtro['becario'].'%').") OR
						per.nro_documento = ".quote($filtro['becario']).")"; 
		}
		if(isset($filtro['mes']) && $filtro['mes']){
			$where[] = 'EXTRACT(month FROM rec.fecha_emision) = ' . quote($filtro['mes']);
		}
		if(isset($filtro['anio']) && $filtro['anio']){
			$where[] = 'EXTRACT(year FROM rec.fecha_emision) = ' . quote($filtro['anio']);
		}
		if(count($where)){
			$sql = sql_concatenar_where($sql,$where);
		}
		
		return toba::db()->consultar($sql);
	}

	function registrar_recibo($detalles)
	{
		if( ! (isset($detalles['nro_documento']) 
			&& isset($detalles['id_recibo']) 
			&& isset($detalles['fecha_emision']) 
		)) {
			throw new toba_error('No se han recibido los parametros para registrar el recibo de sueldo');
		}
		$sql = sprintf("INSERT INTO be_recibos_sueldo VALUES ('%s',%u,'%s') ON CONFLICT DO NOTHING",$detalles['nro_documento'],$detalles['id_recibo'],$detalles['fecha_emision']);
		return toba::db()->ejecutar($sql);
	}
}