<?php 
class Reporte_informe_proyecto extends FPDF
{
	protected $datos;
	protected $alto_linea;
	protected $ancho_etiqueta;
	protected $align_etiqueta;

	function __construct($datos)
	{
		parent::__construct();
		$this->alto_linea = 6;
		$this->ancho_etiqueta = 50;
		$this->align_etiqueta = 'R';
		$this->datos = $datos;
		//Formato A4 y Vertical
		$this->AddPage('Portrait','A4');
		$this->SetAutoPageBreak(true,30);

		$this->detalles_proyecto($datos['proyecto']);
		$this->Ln();

		$this->detalles_informe($datos['informe']);
		$this->Ln();		

		$this->evaluaciones_integrantes($datos['eval_integrantes']);
		$this->Ln();

		$this->trabajos_publicaciones($datos['trab_publicacion']);
		$this->Ln();

		$this->trabajos_difundidos($datos['trab_difundido']);
		$this->Ln();

		$this->trabajos_transferidos($datos['trab_transferido']);
		$this->Ln();

		$this->direcciones_tesis($datos['direccion_tesis']);
		$this->Ln();

		$this->becarios($datos['becarios'],$datos['becarios_externos']);
		$this->Ln();

		$this->gestion_proyecto($datos['informe']);
		$this->Ln();

		if(array_key_exists('metas', $datos)){
			$this->metas_alcanzadas($datos['metas']);
			$this->Ln();			
		}



	}

	function detalles_proyecto($proyecto)
	{
		$this->SetFont('arial','',10);

		//Dos formas distintas de formatear la fecha
		$desde = date('d/m/Y',strtotime($proyecto['fecha_desde']));
		$hasta = (new Datetime($proyecto['fecha_hasta']))->format('d/m/Y');
		$duracion = date('Y',strtotime($proyecto['fecha_hasta'])) - date('Y',strtotime($proyecto['fecha_desde'])) + 1;
		$periodo = sprintf('%s a�os (%s al %s)',$duracion, $desde, $hasta);

		//Denominaci�n del proyecto
		$this->imprimir_titulo_cuadro('Proyecto');
		$this->SetFont('arial','',10);
		$this->MultiCell(190,$this->alto_linea,$proyecto['descripcion'],1,'J',false);
		//Periodo
		$this->imprimir_etiqueta('Duraci�n: ');
		$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$periodo,1,1,'L',false);
		
	}

	function detalles_informe($informe)
	{
		$fecha = ($informe['fecha_presentacion']) 
					? (new Datetime($informe['fecha_presentacion']))->format('d/m/Y') 
					: 'No presentado';
		$presentado_por = ($informe['presentado_por']) ? $informe['presentado_por_desc'] : 'No presentado';

		
		$this->imprimir_titulo_cuadro('Informe');
		$this->SetFont('arial','',10);

		//Tipo de informe
		$this->imprimir_etiqueta('Tipo de informe: ');
		$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$informe['tipo_informe'],1,1,'L',false);
		//Fecha de presentacion
		$this->imprimir_etiqueta('Fecha de presentaci�n: ');
		$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$fecha,1,1,'L',false);
		//presentado por
		$this->imprimir_etiqueta('Presentado por: ');
		$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$presentado_por,1,1,'L',false);
	}

	function evaluaciones_integrantes($evals)
	{
		$this->imprimir_titulo_cuadro('Evaluaci�n de integrantes');
		$this->SetFont('arial','',10);

		foreach($evals as $eval){
			//Tipo de informe
			$this->Cell(140,$this->alto_linea,$eval['integrante'],1,0,'L',false);
			$this->Cell(50,$this->alto_linea,$eval['evaluacion'],1,1,'C',false);
		}
	}

	function trabajos_publicaciones($trabajos)
	{
		$this->imprimir_titulo_cuadro('Producci�n (' . count($trabajos) . ' elementos)');
		$this->SetFont('arial','',10);

		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 37;

		$this->Ln();
		foreach($trabajos as $trabajo){
			//Tipo de informe
			$this->imprimir_etiqueta('T�tulo: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['titulo'],0,'L',false);
			$this->imprimir_etiqueta('Tipo de publicaci�n: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['tipo_publicacion'],0,1,'L',false);
			$this->imprimir_etiqueta('A�o: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['anio'],0,1,'L',false);
			$this->imprimir_etiqueta('Estado: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['estado_desc'],0,1,'L',false);

			if(isset($trabajo['url']) && $trabajo['url']){
				$this->imprimir_etiqueta('Enlace: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['url'],0,'L',false);
			}

			if($trabajo['info_complementaria']){
				$this->imprimir_etiqueta('Informaci�n adicional: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['info_complementaria'],0,'L',false);
			}

			$this->imprimir_etiqueta('Autor/es: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['autores'],0,'L',false);

			$this->linea();
			
		}
		$this->ancho_etiqueta = $valor_original;
	}

	function trabajos_difundidos($trabajos)
	{
		$this->imprimir_titulo_cuadro('Difusi�n (' . count($trabajos) . ' elementos)');
		$this->SetFont('arial','',10);

		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 31;

		$this->Ln();
		foreach($trabajos as $trabajo){
			//Tipo de informe
			$this->imprimir_etiqueta('T�tulo: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['titulo'],0,'L',false);
			
			$this->imprimir_etiqueta('A�o: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['anio'],0,1,'L',false);
			
			$this->imprimir_etiqueta('Evento: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['evento'],0,'L',false);
			
			$this->imprimir_etiqueta('Alcance: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['alcance_desc'],0,1,'L',false);

			if(isset($trabajo['url']) && $trabajo['url']){
				$this->imprimir_etiqueta('Enlace: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['url'],0,'L',false);
			}

			if($trabajo['info_complementaria']){
				$this->imprimir_etiqueta('Informaci�n adicional: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['info_complementaria'],0,'L',false);
			}

			$this->imprimir_etiqueta('Autor/es: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['autores'],0,'L',false);

			$this->linea();
			
		}
		$this->ancho_etiqueta = $valor_original;
	}

	function trabajos_transferidos($trabajos)
	{
		$this->imprimir_titulo_cuadro('Transferencia (' . count($trabajos) . ' elementos)');
		$this->SetFont('arial','',10);

		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 37;

		$this->Ln();
		foreach($trabajos as $trabajo){
			//Tipo de informe
			$this->imprimir_etiqueta('Descripci�n: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['descripcion'],0,'L',false);
			
			$this->imprimir_etiqueta('Destinatario: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['destinatario'],0,1,'L',false);
			
				
			if($trabajo['info_complementaria']){
				$this->imprimir_etiqueta('Informaci�n adicional: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['info_complementaria'],0,'L',false);
			}

			$this->imprimir_etiqueta('Autor/es: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$trabajo['autores'],0,'L',false);

			$this->linea();
			
		}
		$this->ancho_etiqueta = $valor_original;
	}

	function direcciones_tesis($dirigidos)
	{
		$this->imprimir_titulo_cuadro('Direcciones de Tesis (' . count($dirigidos) . ' elementos)');

		$this->SetFont('arial','',10);

		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 20;

		$this->Ln();
		foreach($dirigidos as $dirigido){
			//Tesista
			$this->imprimir_etiqueta('Tesista: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$dirigido['tesista'],0,1,'L',false);
			//Director
			$this->imprimir_etiqueta('Director: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$dirigido['director'],0,1,'L',false);
			//Carrera
			$this->imprimir_etiqueta('Carrera: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$dirigido['carrera'],0,1,'L',false);
			//T�tulo
			$this->imprimir_etiqueta('T�tulo: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$dirigido['titulo'],0,'L',false);
			//Etapa
			$this->imprimir_etiqueta('Etapa: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$dirigido['etapa_tesis'],0,1,'L',false);

			$this->linea();
			
		}
		$this->ancho_etiqueta = $valor_original;

	}

	function becarios($internos, $externos){
		$this->imprimir_titulo_cuadro('Formaci�n de Recursos Humanos (' . (count($internos)+count($externos)) . ' elementos)');

		$this->SetFont('arial','',10);
		
		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 40;

		$this->Ln();
		foreach($internos as $becario){
			//Tesista
			$this->imprimir_etiqueta('Becario: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['becario'],0,1,'L',false);
			//Director
			$this->imprimir_etiqueta('Director: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['director'],0,1,'L',false);
			//Co-Director
			if($becario['codirector']){
				$this->imprimir_etiqueta('Co-Director: ',0);
				$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['codirector'],0,1,'L',false);
			}
			//Sub-Director
			if($becario['subdirector']){
				//Co-Director
				$this->imprimir_etiqueta('Sub-Director: ',0);
				$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['subdirector'],0,1,'L',false);
			}
			//Plan de beca
			$this->imprimir_etiqueta('T�tulo plan beca: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['titulo_plan_beca'],0,'L',false);
			//Tipo de beca
			$this->imprimir_etiqueta('Tipo de beca: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['tipo_beca'],0,1,'L',false);
			//Duraci�n
			$this->imprimir_etiqueta('Duraci�n de la beca: ',0);
			$duracion = date('Y',strtotime($becario['fecha_hasta'])) -date('Y',strtotime($becario['fecha_desde'])); 
			$unidad = ($duracion == 1) ? 'a�o' : 'a�os';
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$duracion.' '.$unidad,0,1,'L',false);

			$this->linea();
			
		}

		foreach($externos as $becario){
			//Tesista
			$this->imprimir_etiqueta('Becario: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['becario'],0,1,'L',false);
			//Director
			$this->imprimir_etiqueta('Direcci�n: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['director'],0,1,'L',false);
			//Plan de beca
			$this->imprimir_etiqueta('T�tulo plan beca: ',0);
			$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['titulo_plan_beca'],0,'L',false);
			//Tipo de beca
			$this->imprimir_etiqueta('Tipo de beca: ',0);
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$becario['tipo_beca_externa'],0,1,'L',false);
			//Duraci�n
			$this->imprimir_etiqueta('Duraci�n de la beca: ',0);
			$duracion = date('Y',strtotime($becario['fecha_hasta'])) -date('Y',strtotime($becario['fecha_desde'])); 
			$unidad = ($duracion == 1) ? 'a�o' : 'a�os';
			$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$duracion.' '.$unidad,0,1,'L',false);

			$this->linea();
			
		}


		$this->ancho_etiqueta = $valor_original;
	}

	function gestion_proyecto($informe)
	{
		$this->imprimir_titulo_cuadro('Gesti�n del proyecto');
		$this->SetFont('arial','',10);
		$this->Ln();
		if( ! ($informe['equipam_mejoro_capac'] || $informe['estim_efic_inversiones'] || $informe['dificult_gestion']) ){
			$this->imprimir_titulo_seccion('No se han declarado detalles sobre la gesti�n del proyecto',0);
		}
		if($informe['equipam_mejoro_capac']){
			$this->imprimir_titulo_seccion('Medida en que el equipamiento adquirido mejor� la capacidad del grupo y de la instituci�n',0);
			$this->MultiCell(190,$this->alto_linea,$informe['equipam_mejoro_capac'],0,'J',false);
			$this->Ln();
		}
		if($informe['estim_efic_inversiones']){
			$this->imprimir_titulo_seccion('Ajuste entre lo presupuestado y lo efectivamente gastado o invertido, en funci�n de las metas alcanzadas',0);
			$this->MultiCell(190,$this->alto_linea,$informe['estim_efic_inversiones'],0,'J',false);
			$this->Ln();
		}
		if($informe['dificult_gestion']){
			$this->imprimir_titulo_seccion('Dificultades encontradas en la gesti�n del proyecto',0);
			$this->MultiCell(190,$this->alto_linea,$informe['dificult_gestion'],0,'J',false);
		}
		
	}

	function metas_alcanzadas($metas)
	{
		
		$meses = array(1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre');
		
		$this->imprimir_titulo_cuadro('Metas alcanzadas');
		$this->SetFont('arial','',10);
		
		$valor_original = $this->ancho_etiqueta;
		$this->ancho_etiqueta = 30;

		$this->Ln();
		$cumplidas = array();
		foreach($metas as $meta){
			$cumplidas[$meta['obj_especifico']][] = array(
				'tarea' => $meta['tarea'],
				'mes'   => $meta['mes'],
				'anio'  => $meta['anio']
			); 
		}
		foreach($cumplidas as $objetivo => $metas){
			$this->imprimir_titulo_seccion('Objetivo espec�fico: ' . $objetivo);
			foreach($metas as $indice => $meta){
				//Tarea
				$this->imprimir_etiqueta('Tarea: ',0);
				$this->MultiCell(190-$this->ancho_etiqueta,$this->alto_linea,$meta['tarea'],0,'L',false);
				
				//Cumplimiento efectivo
				$cumplido = sprintf('%s de %s',ucfirst($meses[$meta['mes']]),$meta['anio']);
				$this->imprimir_etiqueta('Cumplida en: ',0);
				$this->Cell(190-$this->ancho_etiqueta,$this->alto_linea,$cumplido,0,1,'L',false);
				//No es la �ltima meta, entonces se hace un salto de linea para que no queden tan juntos
				if($indice < count($metas)-1){
					$this->Ln();	
				}
			}
			$this->linea();
			
		}

	}

	function linea()
	{
		$this->SetDrawColor(150,150,150);
		$this->Ln();
		$this->line(20,$this->getY(),190,$this->getY());
		$this->Ln();
		$this->SetDrawColor(0,0,0);
	}


	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,10,13,55,20);

		$this->SetFont('arial','BU',15);
		$this->setXY(40,17);
		$this->Cell(130,7,"Informe de Proyecto ".$this->datos['proyecto']['codigo'],0,1,'C',false);	
		
		$this->SetFont('arial','B',10);
		$this->Cell(190,7,ucwords($this->datos['informe']['tipo_informe']),0,1,'C',false);	
		
		//$this->Line(10,39,200,39);
		$this->setXY(10,45);
	}

	function imprimir_titulo_seccion($texto)
	{
		$this->SetFont('arial','BU',10);
		$this->MultiCell(190,$this->alto_linea,$texto,0,'C',FALSE);	
		$this->SetFont('arial','',10);
	}
	function imprimir_titulo_cuadro($texto)
	{
		$this->SetFont('arial','B',9);
		$this->SetFillColor(110,123,188);
		$this->SetTextColor(255,255,255);
		$this->Cell(190,$this->alto_linea,$texto,1,1,'C',true);	
		//Vuelvo el color a negro
		$this->SetTextColor(0,0,0);
	}

	function imprimir_etiqueta($texto,$border=1)
	{
		$this->SetFont('arial','B',9);
		$this->Cell($this->ancho_etiqueta,$this->alto_linea,$texto,$border,0,$this->align_etiqueta,false);
		$this->SetFont('arial','',10);
	}	

	function Footer()
	{
		$this->SetY(-15);
		$this->Cell(190,5,'P�gina '.$this->PageNo(),0,1,'C',false);
	}
	function mostrar()
	{
		$this->Output('I','Informe.pdf');
	}
		
}
?>