<?php 
class Evaluacion_informe_beca extends FPDF
{
	protected $datos;
	
	function __construct($datos)
	{
		//para ver las fechas en español (afecta a la funcion strftime usada mas abajo)
		setlocale(LC_TIME, "es_ES");
		parent::__construct();
		$this->datos = $datos;
		//Formato A4 y Apaisado
		$this->AddPage('Portrait','A4');
		$this->setAuthor('Secretaría General de Ciencia y Técnica - UNNE',true);

		$this->cuerpo();
	}

	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,19,13,65,25);
		
		//Subtitulo del logo
		$this->SetFont('arial','',9);
		$this->setXY(22,35);
		$this->Cell(190,9,'Secretaría General de Ciencia y Técnica',0,1,'L',false);	

		$this->SetFont('arial','B',14);
		$this->setXY(10,45);
		$this->SetFillColor(220,220,220);
		$this->Cell(190,9,'DICTAMEN DE EVALUACIÓN - INFORMES DE BECAS',1,1,'C',true);

		$this->Ln();
	}

	function cuerpo()
	{
		$ancho_etiq = 50;
		$alto_fila = 8;
		$font_size = 10;
		$this->SetFillColor(220,220,220);

		//Comisión Asesora
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Comisión Asesora",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,$this->datos['informe']['area_conocimiento_desc'],1,1,'L',false);	

		$this->Ln();

		//Título: Datos del Becario
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190, $alto_fila," Datos del Becario",1,1,'L',true);	
		//Convocatoria
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Convocatoria",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,$this->datos['informe']['convocatoria'],1,1,'L',false);
		//Tipo de Beca
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Tipo de Beca",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,ucwords(strtolower($this->datos['informe']['tipo_beca_desc'])) . " - Resolución: " . $this->datos['informe']['nro_resol'] ,1,1,'L',false);		
		//Tipo de Informe
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Tipo de Informe",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,$this->datos['informe']['tipo_informe_desc'],1,1,'L',false);
		//Becario
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Becario",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,ucwords(strtolower($this->datos['informe']['postulante'])),1,1,'L',false);
		//Director
		$this->SetFont('arial','',$font_size);
		$this->Cell($ancho_etiq, $alto_fila,"Director",1,0,'C',false);	
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-$ancho_etiq, $alto_fila,ucwords(strtolower($this->datos['informe']['director'])),1,1,'L',false);
		//Co-Director
		if(isset($this->datos['informe']['codirector']) && $this->datos['informe']['codirector']){
			$this->SetFont('arial','',$font_size);
			$this->Cell($ancho_etiq, $alto_fila,"Co-Director",1,0,'C',false);	
			$this->SetFont('arial','B',$font_size);
			$this->Cell(190-$ancho_etiq, $alto_fila,ucwords(strtolower($this->datos['informe']['codirector'])),1,1,'L',false);
		}
		//Sub-Director
		if(isset($this->datos['informe']['subdirector']) && $this->datos['informe']['subdirector']){
			$this->SetFont('arial','',$font_size);
			$this->Cell($ancho_etiq, $alto_fila,"Sub-Director",1,0,'C',false);	
			$this->SetFont('arial','B',$font_size);
			$this->Cell(190-$ancho_etiq, $alto_fila,ucwords(strtolower($this->datos['informe']['subdirector'])),1,1,'L',false);
		}
		//Tiulo del Plan de Beca
		$this->SetFont('arial','B',$font_size-1);
		// $y1 e $y2 sirven para saber la altura del MultiCell, para asignarlo despues a su etiqueta previa
		$y1 = $this->getY();
		$this->setX($ancho_etiq+10);
		$this->MultiCell(190-$ancho_etiq, $alto_fila-3,$this->datos['informe']['titulo_plan_beca'],1,'L',false);
		$y2 = $this->getY();
		
		//Vuelvo al punto vertical donde se inició la impresión del multicell
		$this->SetFont('arial','',$font_size);
		$this->setY($y1);
		$this->Cell($ancho_etiq, $y2-$y1,"Título del Plan de Beca",1,1,'C',false);	
		
		$this->Ln(); 

		//Título: Dictamen
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190, $alto_fila," Dictamen",1,1,'L',true);	

		//Dictamen - Observaciones
		$this->SetFont('arial','',$font_size-1);
		//LTR = LEFT - TOP - RIGHT (los bordes que quiero que tenga)
		$this->MultiCell(190, $alto_fila-3,$this->datos['informe']['observaciones'],'LTR','J',false);
		$this->SetFont('arial','',$font_size);
		$this->Cell(19, $alto_fila,"Resultado: ",'L',0,'L',false);
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190-19, $alto_fila,strtoupper($this->datos['informe']['evaluacion_desc']),'R',1,'L',false);
		//Fecha
		$fec = $this->datos['informe']['fecha_evaluacion'];
		$this->Cell(190, $alto_fila,"Corrientes, " . strftime('%d de %B de %Y', strtotime($fec)),'LRB',1,'R',false);

		$this->Ln(); 

		//Título: Evaluadores
		$this->SetFont('arial','B',$font_size);
		$this->Cell(190, $alto_fila," Evaluadores",1,1,'L',true);	
		$this->SetFont('arial','',$font_size-1);
		foreach ($this->datos['evaluadores'] as $evaluador) {
			$this->Cell(190, $alto_fila-1," - " . $evaluador['apellido'] . ', ' . $evaluador['nombres'] . ' - DNI: ' . $evaluador['nro_documento'],1,1,'L',false);

		}

		
	}

	function mostrar()
	{
		$this->Output('I','EvaluaciÃ³n de Informe de Beca.pdf');
	}
}
?>