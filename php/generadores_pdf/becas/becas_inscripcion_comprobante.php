<?php 
class Becas_inscripcion_comprobante extends FPDF
{
	protected $x;
	protected $y;
	protected $nombre_pdf;

	function __construct($datos)
	{
		parent::__construct();
		//Formato A4 y Apaisado
		$this->SetTopMargin(35);
		$this->nombre_pdf = $datos['beca']['nro_carpeta'].".pdf";
		$this->SetAutoPageBreak(true,20);
		$this->AddPage('Portrait','A4');
		
		/* ---------- PAR?METROS DEL FORMULARIO ---------------*/
		$params = array('ancho_total'       => 190,
						'ancho_titulo'      => 30,
						'alto_fila'         => 5,
						'alin_rotulo'       => 'R',
						'bordes'            => 'T', //bordes superior e inferior
						'distancia_cuadros' => 3);
		$params['ancho_info'] = $params['ancho_total'] - $params['ancho_titulo'];
		/* ----------------------------------------------------*/

		//Car?ula
		$caratula = array('tipo_beca'              => $datos['beca']['tipo_beca'],
						  'requiere_posgrado'      => $datos['beca']['requiere_insc_posgrado'],
						  'nombre_dependencia'     => $datos['postulante']['nombre_dependencia'],
						  'nro_documento'          => $datos['postulante']['nro_documento'],
						  'postulante'             => $datos['postulante']['apellido'].', '.$datos['postulante']['nombres'],
						  'cuil'                   => $datos['postulante']['cuil'],
						  'director'               => $datos['director']['apellido'].', '.$datos['director']['nombres'],
						  'area_conocimiento'      => $datos['beca']['area_conocimiento'],
						  'nro_carpeta'            => $datos['beca']['nro_carpeta'],
						  'convocatoria'           => $datos['beca']['convocatoria'],
						  'lugar_trabajo'          => $datos['beca']['lugar_trabajo_becario']);
						  

		if(isset($datos['codirector'])){
			$caratula['codirector'] = $datos['codirector']['apellido'].', '.$datos['codirector']['nombres']; 
		}
		if(isset($datos['subdirector'])){
			$caratula['subdirector'] = $datos['subdirector']['apellido'].', '.$datos['subdirector']['nombres']; 
		}
		$this->SetFillColor(200,200,200);

		$this->caratula($caratula,$params);

		//agrego otra hoja
		$this->AddPage('Portrait','A4');

		
		

		//Encabezado nota
		$this->encabezado_nota($datos);

		//Cuadro Postulante
		$this->SetFont('','',7);
		$this->Ln();
		$this->postulante($datos['postulante'],$params);

		//Cuadro Beca
		$this->Ln();
		$this->beca($datos['beca'],$params);

		
		//Cuadros Direcci?
		//Director
		$this->Ln();
		$this->direccion($datos['director'],$params,'director');
				
		//Codirector
		if(isset($datos['codirector'])){
			$this->Ln();
			$this->justificacion_codireccion($datos['postulante']['justif_codirector'],$params);
			$this->Ln();
			$this->direccion($datos['codirector'],$params,'codirector');
		}
		//Subdirector
		if(isset($datos['subdirector'])){
			$this->Ln();
			$this->direccion($datos['subdirector'],$params,'subdirector');
		}

		//Proyecto
		$this->Ln();
		$this->proyecto($datos['proyecto'],$params);

		//Firma postulante
		$this->Ln();
		$this->firma_postulante();

		//Aval Directores
		$this->Ln();
		$this->aval_directores();

		//Aval Autoridades UA
		$this->Ln();
		$this->aval_autoridades_ua();

		//agrego otra hoja
		$this->AddPage('Portrait','A4');

		$caratula['criterios_eval']           = $datos['criterios_eval'];
		$caratula['suma_puntaje_academico']   = $datos['beca']['suma_puntaje_academico'];
		$caratula['puntaje_academico_maximo'] = $datos['beca']['puntaje_academico_maximo'];
		$caratula['puntaje']                  = $datos['postulante']['puntaje'];
		$params['dictamen_comision']          = isset($datos['dictamen_comision']) ? $datos['dictamen_comision'] : array(); 
		$params['comision_evaluadores']       = isset($datos['comision_evaluadores']) ? $datos['comision_evaluadores'] : array(); 
		$params['dictamen_junta']             = isset($datos['dictamen_junta']) ? $datos['dictamen_junta'] : array(); 

		$this->comision($caratula,$params);

		//agrego otra hoja
		$this->AddPage('Portrait','A4');
		$this->junta($caratula,$params);

		//informe de archivos subidos
		$this->informe_archivos_subidos($caratula);

		//agrego otra hoja
		//$this->AddPage('Portrait','A4');
		$this->talon_postulante($caratula,$params);
			
	}

	function caratula($datos,$params)
	{
		//$x = $this->GetX();
		//$y = $this->GetY();

		//$this->setXY(10,43);
		//Convocatoria
		$this->setFont('','BU',14);
		$this->Cell(190,6,strtoupper($datos['convocatoria']),0,1,'C',false);
		//$this->setXY($x,$y);

		extract($params);
		
		//manejan la posici? del cuadro
		$ancho = 190;
		$alto = 130;

		//alto de la linea
		$alto_linea = 8;

		/* N?ERO CARPETA */
		$this->Ln();
		$this->setFont('','B',17);
		$this->Cell($ancho,$alto_linea,$datos['postulante'],1,1,'C',true);
		
		//Cuandro
		/*$y += $alto_linea;
		$this->Rect($x,$y,$ancho,$alto,'DF');*/
		
		$ancho_etiq = 40;
		//Area Conocimiento
		$this->setFont('','B',10);
		$this->Cell($ancho_etiq,$alto_linea,'Àrea Conocimiento ',1,0,'C',false);
		$this->setFont('','',10);
		$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['area_conocimiento'],1,1,'L',false);
		
		//Facultad / Instituto
		$this->setFont('','B',10);
		$this->Cell($ancho_etiq,$alto_linea,'Facultad',1,0,'C',false);
		$this->setFont('','',10);
		/*Si la beca requiere inscripci? a posgrado, el campo facultad es el lugar de trabajo de postulante. En cambio, si la beca no requiere inscripcion a posgrado, como es el caso de las becas de PREGRADO, la facultad es el lugar donde estudia el postulante */
		if($datos['requiere_posgrado'] == 'S'){
			$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['lugar_trabajo'],1,1,'L',false);	
		}else{
			$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['nombre_dependencia'],1,1,'L',false);
		}
		
		
		//Director
		$this->setFont('','B',10);
		$this->Cell($ancho_etiq,$alto_linea,'Director ',1,0,'C',false);
		$this->setFont('','',10);
		$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['director'],1,1,'L',false);
		
		if(isset($datos['codirector'])){
			//Director
			$this->setFont('','B',10);
			$this->Cell($ancho_etiq,$alto_linea,'Co-Director ',1,0,'C',false);
			$this->setFont('','',10);
			$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['codirector'],1,1,'L',false);
		}
		if(isset($datos['subdirector'])){
			//Director
			$this->setFont('','B',10);
			$this->Cell($ancho_etiq,$alto_linea,'Sub-Director ',1,0,'C',false);
			$this->setFont('','',10);
			$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['subdirector'],1,1,'L',false);
		}

		//Tipo de Beca
		$this->setFont('','B',10);
		$this->Cell($ancho_etiq,$alto_linea,'Nro. Carpeta',1,0,'C',false);
		$this->setFont('','',10);
		$this->Cell($ancho-$ancho_etiq,$alto_linea,$datos['nro_carpeta'],1,1,'L',false);
		
		$this->Ln();
		$this->Ln();
		$this->SetFont('','',7);
		//Lugar y Fecha
		//$this->Cell(75,$alto_linea,'','B',0,'C',false);
		//$this->SetX($this->GetX()+40);
		//$this->Cell(75,$alto_linea,'','B',1,'C',false);
		
		//$this->Cell(75,$alto_linea,'Lugar y Fecha',0,0,'C',false);
		//$this->SetX($this->GetX()+40);
		//$this->Cell(75,$alto_linea,'Firma del Postulante',0,1,'C',false);

		//Linea para corte
		$this->Ln();
		//$this->Cell($ancho,$alto_linea,'Recorte sobre esta linea, y pegue la caratula en la tapa de su carpeta.','T',1,'C',false);
	}

	function encabezado_nota($datos)
	{
		$generales = $datos['generales'];
		$tratamiento = ($generales['genero_secretario'] == 'M') ? 'Señor Secretario' : 'Señora Secretaria';
		//Convocatoria
		$this->setFont('','B',8);
		$this->Cell(190,4,$tratamiento . " General de Ciencia y Ténica", 0, 1, 'L', false);
		$this->Cell(190,4,"de la Universidad Nacional del Nordeste", 0, 1, 'L', false);
		$this->Cell(190,4,$generales['nombre_secretario'], 0, 1, 'L', false);
		$this->setFont('','BU',8);
		$this->Cell(190,4,"SU DESPACHO", 0, 1, 'L', false);
		$this->setFont('','',8);
		$this->MultiCell(190,5,"\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tTengo el agrado de dirigirme a Ud. con el objeto de solicitar mi inscripción al Concurso de Becas de Investigación del corriente año.\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tA tal efecto adjunto los datos y documentación requeridos en el formulario que acompaña la presente solicitud:",0,'L',false);
	}

	function postulante($datos,$params)
	{
		extract($params);

		$this->setFont('','B');
		$this->Cell(190,7,'POSTULANTE',1,1,'C',true);	
		/* APELLIDO Y NOMBRES */
		$this->Cell($ancho_titulo,$alto_fila,'Apellido y Nombre:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['apellido'].", ".$datos['nombres'],$bordes,1,'L',false);	
		/* NRO DE DOCUMENTO - CUIL -FECHA NACIMIENTO */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Nro. Documento:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['nro_documento'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'CUIL:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['cuil'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Fecha Nacimiento:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['fecha_nac'],$bordes,1,'L',false);
		/* FACULTAD/INSTITUTO */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Facultad/Instituto:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['nombre_dependencia'],$bordes,1,'L',false);
		/* CELULAR Y TELEFONO */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Celular:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['celular'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Teléfono fijo:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['telefono'],$bordes,1,'L',false);
		/* Cantidad de materias aprobadas por el postulante */
		$this->setFont('','B');
		$this->Cell(60,$alto_fila,'Cantidad de Materias Aprobadas:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(35,$alto_fila,$datos['materias_aprobadas'],$bordes,0,'L',false);
		/* Promedio Hist?ico del Postulante */
		$this->setFont('','B');
		$this->Cell(60,$alto_fila,'Promedio Histórico del Postulante (incluye aplazos):',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(35,$alto_fila,$datos['prom_hist'],$bordes,1,'L',false);
		/* Cantidad de Materias que tiene el plan de estudios */
		$this->setFont('','B');
		$this->Cell(60,$alto_fila,'Cantidad de materias del Plan de Estudios:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(35,$alto_fila,$datos['materias_plan'],$bordes,0,'L',false);
		/* Promedio Hist?ico Egresados */
		$this->setFont('','B');
		$this->Cell(60,$alto_fila,'Promedio Histórico de Egresados:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(35,$alto_fila,$datos['prom_hist_egresados'],$bordes,1,'L',false);
		/* Aval de la Direcci? Gesti? Estudios */
		
		/*
		$this->Cell(190,4,'Firma del responsable de la Dirección Gestión Estudios',1,1,'C',true);
		$this->Cell(50 ,10,'',1,0,'',false);
		$this->Cell(90,10,'',1,0,'',false);
		$this->Cell(50,10,'',1,1,'',false);
		$this->Cell(50,6,'Firma',1,0,'C',false);
		$this->Cell(90,6,'Aclaración',1,0,'C',false);
		$this->Cell(50,6,'Cargo',1,1,'C',false);
		*/
	}

	function beca($datos,$params)	
	{
		extract($params);

		$this->setFont('','B');
		$this->Cell(190,7,'DATOS DE LA BECA',1,1,'C',true);	
		/* Convocatoria y Tipo de beca */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Convocatoria:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['convocatoria'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Tipo de Beca:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['tipo_beca'],$bordes,1,'L',false);
		/* Area de conocimiento */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Área de Conocimiento:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['area_conocimiento'],$bordes,1,'L',false);
		/* T?ulo plan beca */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Título del Plan de Beca:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->MultiCell($ancho_info,$alto_fila-1,$datos['titulo_plan_beca'],$bordes,'J',false);
		/* LUGAR DE TRABAJO Y AREA */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Lugar trabajo becario:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo)),$alto_fila,$datos['lugar_trabajo_becario'],$bordes,0,'L',false);
	}

	function justificacion_codireccion($justificacion,$params)
	{
		extract($params);
		$this->setFont('','B',6);
		$this->Cell(190,4,'JUSTIFICACIÓN CODIRECTOR/SUBDIRECTOR',1,1,'C',true);	
		$this->setFont('','',6);
		$this->Cell($ancho_total,$alto_fila,'El Director de Beca deberá justificar la inclusión de un Co-Director y/o Sub-Director de Beca, expresando claramente los motivos que hacen necesaria tal inclusión.',$bordes,1,'C',false);
		$this->setFont('','B',7);
		$this->Cell($ancho_titulo,$alto_fila,'Justificación:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->MultiCell($ancho_info,$alto_fila-1,$justificacion,$bordes,'J',false);
	}

	function direccion($datos,$params,$tipo)
	{
		extract($params);

		$this->setFont('','B');
		$this->Cell(190,7,strtoupper($tipo),1,1,'C',true);	
		/* APELLIDO Y NOMBRES */
		$this->Cell($ancho_titulo,$alto_fila,'Apellido y Nombre:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['apellido'].", ".$datos['nombres'],$bordes,1,'L',false);	
		/* NRO DE DOCUMENTO - CUIL */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Nro. Documento:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['nro_documento'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'CUIL:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['cuil'],$bordes,1,'L',false);
		/* CELULAR Y TELEFONO Y MAIL*/
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Celular:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['celular'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Mail:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['mail'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Teléfono fijo:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*3)) / 3,$alto_fila,$datos['telefono'],$bordes,1,'L',false);
		/* NIVEL Académico Y CAT. INCENTIVOS*/
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Máx. Grado Académico:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['nivel_academico'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Cat. Incentivos:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		if($datos['catinc']){
			$cat_inc = array('1'=>'I','2'=>'II','3'=>'III','4'=>'IV','5'=>'V');
			$datos['catinc'] = 'Categoría '.$cat_inc[$datos['catinc']].' (Convocatoria '.$datos['catinc_conv'].')';
		}else{
			$datos['catinc'] = 'No categorizado';
		}
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['catinc'],$bordes,1,'L',false);
		/* CATEGORIA CONICET Y LUGAR TRABAJO CONICET */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Cat. CONICET:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['catconicet'],$bordes,0,'L',false);
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Lugar Trabajo (CONICET):',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['lugar_trabajo_conicet'],$bordes,1,'L',false);
	}

	function proyecto($datos,$params)
	{
		extract($params);

		$this->setFont('','B');
		$this->Cell(190,7,'PROYECTO ACREDITADO',1,1,'C',true);	
		
		/* Convocatoria y Tipo de beca */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Código:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['codigo'],$bordes,0,'L',false);
		/* Convocatoria y Tipo de beca */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Fecha finaliz.:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell(($ancho_total-($ancho_titulo*2)) / 2,$alto_fila,$datos['fecha_hasta'],$bordes,1,'L',false);
		/* APELLIDO Y NOMBRES */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Director:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['apellido'].", ".$datos['nombres'].' (DNI: '.$datos['nro_documento'].')',$bordes,1,'L',false);
		/* T?ulo plan beca */
		$this->setFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Proyecto:',$bordes,0,$alin_rotulo,false);
		$this->setFont('','');
		$this->MultiCell($ancho_info,$alto_fila-1,$datos['proyecto'],$bordes,'J',false);	
	}

	function firma_postulante()
	{
		/*
		$this->setFont('','B');
		$this->Cell(190,5,'FIRMA DEL POSTULANTE',1,1,'C',true);	
		$this->setFont('','');
		$this->MultiCell(190,4,'Declaro conocer el Reglamento de Becas de Investigación de la Universidad Nacional del Nordeste y aceptar cada una de las obligaciones que de él derivan, comprometiéndome a su cumplimiento en caso de que me fuera otorgada la Beca solicitada',1,1,'',false);
		$this->Cell(50 ,19,'',1,0,'',false);
		$this->Cell(140,19,'',1,1,'',false);
		$this->Cell(50,6,'Firma del postulante',1,0,'C',false);
		$this->Cell(140,6,'Lugar y fecha',1,1,'C',false);
		*/
	}

	function aval_directores()
	{
		/*
		$this->setFont('','B');
		$this->Cell(190,5,'AVAL DE DIRECTORES',1,1,'C',true);
		$this->setFont('','');	
		$this->MultiCell(190,4,'Declaro conocer el Reglamento de Becas de Investigación de la Universidad Nacional del Nordeste, las obligaciones que de él derivan y dejo constancia que avalo el Plan de Trabajo del Postulante',1,1,'',false);
		
		$ancho = 190/4;
		$this->Cell($ancho ,19,'',1,0,'',false);
		$this->Cell($ancho ,19,'',1,0,'',false);
		$this->Cell($ancho ,19,'',1,0,'',false);
		$this->Cell($ancho ,19,'',1,1,'',false);
		$this->Cell($ancho,6,'Firma del Director',1,0,'C',false);
		$this->Cell($ancho,6,'Firma del Co-Director',1,0,'C',false);
		$this->Cell($ancho,6,'Firma del Sub-Director',1,0,'C',false);
		$this->Cell($ancho,6,'Firma del Director del Proyecto',1,1,'C',false);
		*/
	}

	function aval_autoridades_ua()
	{
		/*
		$this->setFont('','B');
		$this->Cell(190,5,'AVAL DE AUTORIDADES DE LA FACULTAD/INSTITUTO',1,1,'C',true);	
		$this->setFont('','');
		$this->MultiCell(190,4,'Por la presente presto mi conformidad para que, en caso de ser otorgada la beca solicitada, el postulante pueda realizar el trabajo de investigación propuesto, en el lugar indicado precedentemente',1,1,'',false);
		
		$ancho = 190/2;
		$this->Cell($ancho ,19,'',1,0,'',false);
		$this->Cell($ancho ,19,'',1,1,'',false);
		$this->Cell($ancho,6,'Firma del Secretario de Investigación',1,0,'C',false);
		$this->Cell($ancho,6,'Firma del Decano',1,1,'C',false);
		*/
	}

	function comision($datos,$params)
	{
		extract($params);
		//verifico los datos del dictamen de comisión (si existen)
		if (count($dictamen_comision)) {
			//La justificaciòn es la misma en todos los items
			$justif_puntajes = $dictamen_comision[0]['justificacion_puntajes'];
			foreach ($dictamen_comision as $puntaje_asignado) {
				$puntajes[$puntaje_asignado['id_criterio_evaluacion']] = $puntaje_asignado['asignado'];
			}
		}
		$this->SetFont('','BU',20);
		$this->Cell($ancho_total,$alto_fila*2,'DICTAMEN DE COMISIÓN ASESORA',0,1,'C',false);	
		$this->SetFont('','B',9);
		$this->cabecera_postulacion($datos,$params);

		$this->setFont('','B');
		$this->Cell($ancho_total,$alto_fila,'DICTAMEN DE LA COMISIÓN ASESORA',1,1,'C',true);	
		$this->setFont('','');
		$this->Cell($ancho_total,$alto_fila,'De acuerdo con los criterios establecidos por el Reglamento de Becas de Investigación (Resol. Nro. 368/16 C.S.)',1,1,'L',false);	
		
		$alto_fila = $alto_fila*2;
		$this->SetFont('','B',7);
		$this->Cell($ancho_total/3*2,$alto_fila,'CRITERIO DE EVALUACIÒN DE BECAS',1,0,'C',true);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'PUNTAJE MÁXIMO',1,0,'C',true);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'PUNTAJE OTORGADO',1,1,'C',true);	
		$this->setFont('','B',12);
		if($datos['suma_puntaje_academico'] == 'S'){
			//Puntaje Académico
			$this->Cell($ancho_total/3*2,$alto_fila,'Puntaje Académico',1,0,'L',false);	
			$this->Cell(($ancho_total/3)/2,$alto_fila,intval($datos['puntaje_academico_maximo']),1,0,'C',false);	
			$this->Cell(($ancho_total/3)/2,$alto_fila,$datos['puntaje'],1,1,'C',false);	
		}
		foreach($datos['criterios_eval'] as $criterio){
			//Puntaje Académico
			$this->Cell($ancho_total/3*2,$alto_fila,$criterio['criterio_evaluacion'],1,0,'L',false);	
			$this->Cell(($ancho_total/3)/2,$alto_fila,intval($criterio['puntaje_maximo']),1,0,'C',false);	
			$this->Cell(($ancho_total/3)/2,$alto_fila,(isset($puntajes)) ? $puntajes[$criterio['id_criterio_evaluacion']] : '',1,1,'C',false);		
		}

		/*
		
		//Antecedentes en investigación y docencia
		$this->Cell($ancho_total/3*2,$alto_fila,'Antecedentes en Investigación y Docencia',1,0,'L',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'10',1,0,'C',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'',1,1,'C',false);	
		//Direccion
		$this->Cell($ancho_total/3*2,$alto_fila,'Direcci?',1,0,'L',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'20',1,0,'C',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'',1,1,'C',false);	
		//Plan de Trabajo
		$this->Cell($ancho_total/3*2,$alto_fila,'Plan de Trabajo',1,0,'L',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'20',1,0,'C',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,'',1,1,'C',false);
		*/
		//Puntaje Total
		$this->Cell(($ancho_total/3*2)+(($ancho_total/3)/2),$alto_fila,'PUNTAJE TOTAL',1,0,'L',false);	
		$this->Cell(($ancho_total/3)/2,$alto_fila,(isset($puntajes)) ? (array_sum($puntajes) + $datos['puntaje']) : '',1,1,'C',false);	
		
		//Justificacion del Puntaje
		$this->SetFont('','',9);
		$this->Cell($ancho_total,$alto_fila-4,'Justificación del Puntaje Otorgado:','TRL',1,'L',false);	
		$this->MultiCell($ancho_total,$alto_fila-5,(isset($justif_puntajes)) ? $justif_puntajes : '','BLR','L',false);	
		
		//Lugar y fecha
		$this->Cell($ancho_total,$alto_fila,'Lugar y fecha:','B',1,'L',false);	
		//Evaluadores
		$this->Cell($ancho_total/3*2,$alto_fila,'EVALUADORES',1,0,'C',true);	
		$this->Cell($ancho_total/3*1,$alto_fila,'FIRMA',1,1,'C',true);	
		
		//Los administradores pueden ver los evaluadores. Si no es admin, se imprimen solo los renglones para completar
		if (isset($comision_evaluadores)) {
			foreach($comision_evaluadores as $evaluador){
				$this->Cell($ancho_total/3*2,$alto_fila-2,$evaluador,1,0,'L',false);	
				$this->Cell($ancho_total/3*1,$alto_fila-2,'',1,1,'C',false);
			}			
		} else {
			for($i=0;$i<4;$i++){
				$this->Cell($ancho_total/3*2,$alto_fila-2,'',1,0,'C',false);	
				$this->Cell($ancho_total/3*1,$alto_fila-2,'',1,1,'C',false);		
			}
		} 


	}

	function junta($datos,$params)
	{
		extract($params);
		$puntajes = null;
		if (isset($params['dictamen_junta'][0]['desglose_puntajes_array'])) {
			$puntajes = $params['dictamen_junta'][0]['desglose_puntajes_array'];
		}

		$justificacion = $params['dictamen_junta'][0]['justificacion_puntajes'];

		$this->SetFont('','BU',20);
		$this->Cell($ancho_total,$alto_fila*2,'DICTAMEN DE JUNTA COORDINADORA',0,1,'C',false);	
		$this->SetFont('','B',9);
		$this->cabecera_postulacion($datos,$params);

		$alto_fila = $alto_fila*2;
		$this->SetFont('','B');
		//Opinion de la junta coordinadora
		$this->Cell($ancho_total,$alto_fila-4,'OPINIÓN DE LA JUNTA COORDINADORA',1,1,'C',true);	
		if ($puntajes) {
			foreach ($puntajes as $puntaje) {
				$this->Cell($ancho_total,$alto_fila,$puntaje['criterio_descripcion'] . ': ' . $puntaje['puntaje_asignado'] . ' puntos.',1,1,'L',false);	
			}
		} else {
			$this->Cell($ancho_total,$alto_fila*4,'',1,1,'L',false);
		}
		//Observaciones
		$this->Cell($ancho_total,$alto_fila-4,'OBSERVACIONES',1,1,'C',true);	
		$this->MultiCell($ancho_total,$alto_fila,$justificacion,1,'L',false);
		
		//Lugar y fecha
		$this->Cell($ancho_total,$alto_fila,'Lugar y fecha:','B',1,'L',false);	
		//e
		$this->Cell($ancho_total/3*2,$alto_fila,'EVALUADORES',1,0,'C',true);	
		$this->Cell($ancho_total/3*1,$alto_fila,'FIRMA',1,1,'C',true);	
		for($i=0;$i<5;$i++){
			$this->Cell($ancho_total/3*2,$alto_fila-3,'',1,0,'C',false);	
			$this->Cell($ancho_total/3*1,$alto_fila-3,'',1,1,'C',false);		
		}
	}

	function cabecera_postulacion($datos,$params){
		extract($params);
		$this->setFont('','B');
		$this->Cell($ancho_total,$alto_fila,'DATOS DE LA POSTULACIÓN',1,1,'C',true);	

		$this->setFont('','');
		//Apellido, nombres y cuil
		$tmp = $datos['postulante'].' ('.$datos['cuil'].')';
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Postulante','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila,$tmp,'B',1,'L',false);

		//Director
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Director','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell((($ancho_total - $ancho_titulo*2)/2),$alto_fila,$datos['director'],'B',0,'L',false);

		//Area de conocimiento
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Área','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell((($ancho_total - $ancho_titulo*2)/2),$alto_fila,$datos['area_conocimiento'],'B',1,'L',false);
		

		//Area de conocimiento, convocatoria y numero de carpeta
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Convocatoria','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['convocatoria'],'B',1,'L',false);
		//nro de carpeta
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Nro. Carpeta','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell((($ancho_total - $ancho_titulo*2)/2),$alto_fila,$datos['nro_carpeta'],'B',0,'L',false);
		
		//Tipo de beca
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Tipo de Beca','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell((($ancho_total - $ancho_titulo*2)/2),$alto_fila,$datos['tipo_beca'],'B',0,'L',false);
		$this->Ln();
		$this->Ln();

	}

	function talon_postulante($datos,$params)
	{
		/*
		extract($params);
		$alto_fila += 2;
		$this->setFont('','B',13);
		$this->Cell($ancho_total,$alto_fila+5,$datos['convocatoria']."     --Talón para el usuario--",1,1,'C',true);	

		$this->setFont('','',9);
		//Apellido, nombres y cuil
		$tmp = $datos['postulante'].' ('.$datos['cuil'].')';
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Postulante','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila,$tmp,'B',1,'L',false);

		//director
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Director','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['director'],'B',1,'L',false);

		//lugar y fecha
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila+3,'Lugar y fecha','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila+3,'','B',1,'L',false);

		//Texto fijo
		$this->SetFont('','');
		$this->MultiCell($ancho_total,$alto_fila,'Ha presentado a la Secretaría General de Ciencia y Ténica, la documentación requerida como postulante del Concurso de Becas de Investigación.-','B','L',false);	
		
		//tipo de beca
		$this->SetFont('','B');
		$this->Cell($ancho_titulo,$alto_fila,'Tipo de Beca','B',0,'R',false);	
		$this->SetFont('','');
		$this->Cell($ancho_info,$alto_fila,$datos['tipo_beca'],'B',1,'L',false);
		
		$this->setFont('','B');
		$this->Cell($ancho_total,$alto_fila,"A llenar por el recepcionista",1,1,'C',true);	

		$ancho = 190/3;
		$this->SetFont('','B',15);
		$this->Cell($ancho,19,$datos['nro_carpeta'],1,0,'C',false);
		$this->SetFont('','B',7);
		$this->Cell($ancho*2,19,'',1,1,'',false);
		$this->Cell($ancho,6,'Número de Carpeta',1,0,'C',false);
		$this->Cell($ancho*2,6,'Firma del recepcionista',1,1,'C',false);

		$this->setFont('','',8);
		$this->MultiCell($ancho_total,$alto_fila,'-En caso de ser otorgada la beca, se deberá realizar el acto de toma de posesiòn antes del 1 de Marzo de '.(intval(date("Y"))+1).'-
De no ser otorgada la beca, la documentación deberá ser retirada dentro de los treinta (30) días corridos de dictada la Resolución de adjudicación del Consejo Superior. Caso contrario será destruida..-','BLR','L',true);	
	*/
	}


	function informe_archivos_subidos($datos)
	{
		$archivos = toba::consulta_php('co_inscripcion_conv_beca')->get_informe_archivos_subidos($datos['nro_documento']);

		if(count($archivos)){
			//agrego otra hoja
			$this->AddPage('Portrait','A4');

			//Nombre legible de las tablas consultadas
			$tabs = array('be_antec_activ_docentes'         => 'Actividades Docentes',
							'be_antec_becas_obtenidas'      => 'Becas Obtenidas',
							'be_antec_conoc_idiomas'        => 'Conocimiento de Idiomas',
							'be_antec_cursos_perfec_aprob'  => 'Cursos de Perfeccionamiento Aprobados',
							'be_antec_estudios_afines'      => 'Estudios Afines',
							'be_antec_otras_actividades'    => 'Otras Actividades',
							'be_antec_particip_dict_cursos' => 'Participación en el Dictado de Cursos',
							'be_antec_present_reuniones'    => 'Presentación en Reuniones de Comunicaciones Científicas',
							'be_antec_trabajos_publicados'  => 'Trabajos Publicados'
						);
			//maneja un contador con el total de archivos subidos
			$total = 0;

			
			$this->SetFont('','B',12);
			$this->SetFillColor(200,200,200);
			$this->Cell(190,7,"Resumen de Documentación Probatoria Cargada",1,1,'C',true);
			$this->Cell(150,5,$datos['postulante'],1,0,'C',false);
			$this->Cell(40,5,$datos['nro_carpeta'],1,1,'C',false);
			$this->Ln();

			foreach($archivos as $categoria => $documentacion){
				$this->SetFont('','B',12);
				$this->SetFillColor(200,200,200);
				//Nombre de la categor? donde fue subido el archivo
				$this->Cell(190,6,$tabs[$categoria],1,1,'C',true);
				
				$this->SetFont('','',8);
				$this->SetFillColor(256,256,256);

				$parcial = 1;
				foreach($documentacion as $archivo){
					$desc = (strlen($archivo['descripcion']) > 90) ? substr($archivo['descripcion'],0,90)."(...)" : $archivo['descripcion'];
					$doc  = (strlen($archivo['doc_probatoria']) > 30) ? substr($archivo['doc_probatoria'],0,30)."(...).pdf" : $archivo['doc_probatoria'];
					$this->Cell(130,5,$parcial.") ".$desc,1,0,'L',false);
					$parcial++;
					$total++;
					$this->Cell(60,5,$doc,1,1,'C',true);
				}
			}

			$this->SetFont('','B',10);
			$this->SetFillColor(200,200,200);
			$this->Cell(190,6,"Total de archivos cargados: ".$total,1,1,'C',true);
			$this->Ln();
			
			/*
			$this->SetFont('','B',9);
			$this->setX(90);
			$this->Cell(100,19,'',1,1,'',false);
			$this->setX(90);
			$this->Cell(100,6,'Firma y aclaración del postulante',1,0,'C',false);
			*/
		}
		
	}

	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,19,5,80,30);
		$this->SetFont('arial','BU',13);
		$this->Cell(190,10,'SOLICITUD DE INSCRIPCIÓN - CONCURSO DE BECAS DE INVESTIGACIÓN',0,1,'C',false);	
		$this->SetFont('arial','',7);
	}	

	function Footer()
	{
		$this->SetXY(84,-10);
		//Se usa el menos uno porque la car?ula no debe tener el numero 1
		$this->Cell(40,5,'Página '.($this->PageNo() - 1),0,0,'C',false);
	}
	function mostrar()
	{
		$this->Output('I',$this->nombre_pdf);
	}
}
?>