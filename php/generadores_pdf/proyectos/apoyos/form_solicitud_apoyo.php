<?php 
class Form_solicitud_apoyo extends FPDF
{
	protected $datos;
	protected $sangria = "                                  ";

	function __construct($datos)
	{
		$this->datos = $datos;
		parent::__construct();
		$this->SetLeftMargin(30);
		
		//Formato A4 y Vertical
		$this->AddPage('Portrait','A4');
		$this->SetAutoPageBreak(true,30);


		$ancho_linea = 5;
		$this->dirigido_a();
		$this->cuerpo();

		
	}

	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,10,13,61,25);
		$this->SetFont('arial','BU',16);
		$this->setXY(65,20); 
		$this->Cell(100,10,'Solicitud de Apoyo Económico',0,1,'C',false);	
		$this->Cell(170,10,'Convocatoria '.$this->datos['anio'],0,1,'C',false);	
	}

	function cuerpo()
	{
		$this->Ln();
		$this->SetFont('arial','',10);
		$this->SetFillColor(200,200,200);
		$this->MultiCell(170,5,$this->sangria."Por medio de la presente solicito a usted, se otorgue el Apoyo Económico Anual para el Desarrollo de Proyectos, teniendo en cuenta la siguiente información:",0,'L',false);
		
		$this->detalles_proyecto();
		$this->responsable_fondos();
		$this->necesidades_presupuestarias();

		$this->Ln();
		$this->SetFont('arial','',10);
		$this->SetFillColor(200,200,200);
		$this->MultiCell(170,5,$this->sangria."Declaro conocer la normativa vigente sobre rendición de cuentas de la SGCyT y me comprometo a presentar la misma en tiempo y forma, o en su defecto, devolver el fondo recibido en concepto de Apoyo Económico.",0,'L',false);
		$this->Ln();
		$this->MultiCell(170,5,$this->sangria."Sin otro particular, me despido de usted muy atentamente.",0,'L',false);
		/*$this->Ln(10);
		$this->MultiCell(170,5,$this->sangria."(SE DEBE ADJUNTAR impreso, el PDF del comprobante de la Clave Bancaria Uniforme (CBU) del responsable de los fondos, emitida por el Banco Patagonia)",0,'L',false);
		//*/
		$this->firmas();
		
	}

	function dirigido_a()
	{
		$this->setY(45);
		$this->SetFont('arial','',8);
		$this->Cell(170,5,"Lugar y fecha:_____________________________________________",0,1,'R',false);
		

		$this->SetFont('arial','B',10);
		$linea1 = ($this->datos['conf']['genero_sec'] == 'F') ? 'A la Señora' : 'Al Señor';
		$linea2 = ($this->datos['conf']['genero_sec'] == 'F') ? 'Secretaria' : 'Secretario';
		
		$this->Cell(190,5,$linea1,0,1,'L',false);
		$this->Cell(190,5,$linea2." General de Ciencia y Ténica",0,1,'L',false);
		$this->Cell(190,5,$this->datos['conf']['nombre_sec'],0,1,'L',false);
		$this->Cell(190,5,'S               /               D:',0,1,'L',false);
	}

	function detalles_proyecto()
	{
		$this->Ln();
		$this->SetFont('arial','',9);
		$this->Cell(170,5,'Proyecto',1,1,'C',true);	
		$this->MultiCell(170,5,$this->datos['proyecto_desc'],1,'J',false);
		$this->Cell(40,7,'Código: '.$this->datos['proyecto_codigo'],1,0,'L',false);	
		$this->Cell(40,7,'Desde: '.date('d-m-Y',strtotime($this->datos['proyecto_desde'])),1,0,'L',false);	
		$this->Cell(90,7,'Hasta: '.date('d-m-Y',strtotime($this->datos['proyecto_hasta'])),1,1,'L',false);	
		$this->Cell(170,7,'Director/a: '.$this->datos['director'].' (DNI: '.$this->datos['nro_documento_director'].')',1,1,'L',false);
		if(isset($this->datos['codirector']) && $this->datos['codirector']){
			$this->Cell(170,7,'Codirector/a: '.$this->datos['codirector'].' (DNI: '.$this->datos['nro_documento_codirector'].')',1,1,'L',false);
		}

	}

	function responsable_fondos()
	{
		$this->Ln();	
		$this->Cell(170,5,'Responsable de los Fondos',1,1,'C',true);	
		$this->Cell(170,7,'Apellido y nombre: ' . $this->datos['responsable_fondos_desc'].' (DNI: '.$this->datos['nro_documento_resp_fondos'].')',1,1,'L',false);
		//Si el solicitante no se va a responsabilizar de los fondos, necesita el aval de la persona designada.
		if(trim($this->datos['nro_documento_resp_fondos']) != trim($this->datos['conf']['dni_solicitante'])){
			$this->Cell(170,7,'Firma y aclaración: ',1,1,'L',false);
		}
		
		
	}

	function necesidades_presupuestarias()
	{
		$this->Ln();	
		$this->Cell(170,5,'Necesidades Presupuestarias',1,1,'C',true);	
		$total = 0;
		foreach ($this->datos['nec_presupuestarias'] as $necesidad) {
			$this->Cell(70,5,$necesidad['rubro'],1,0,'L',false);
			$this->Cell(100,5,'$'.number_format($necesidad['monto'],2,',','.'),1,1,'L',false);
			$total += doubleval($necesidad['monto']);
		}
		$this->Cell(70,7,'Total solicitado',1,0,'L',true);
		$this->Cell(100,7,'$'.number_format($total,2,',','.'),1,1,'L',false);
		
	}

	function firmas()
	{
		$this->SetXY(100,250);
		$this->Cell(70,5,'_________________________________________',0,1,'C',false);
		$this->SetX(100);
		$this->Cell(70,5,$this->datos['conf']['ayn_solicitante'],0,1,'C',false);
		$this->SetX(100);
		$this->Cell(70,5,'D.N.I.: '.$this->datos['conf']['dni_solicitante'],0,1,'C',false);
	}

	function Footer()
	{
		$this->SetY(-30);
		$this->SetFont('arial','',9);
		$this->MultiCell(170,5,"(SE DEBE ADJUNTAR impreso, el PDF del comprobante de la Clave Bancaria Uniforme (CBU) del responsable de los fondos, emitida por el Banco Patagonia)",0,'C',false);
		$this->SetFont('arial','',7);
		$this->SetY(-15);
		$this->Cell(170,5,'Página '.$this->PageNo(),0,1,'C',false);
	}

	function mostrar()
	{
		$this->Output('I','Evaluaciones.pdf');
	}
}
?>