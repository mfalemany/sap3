<?php 
class Formulario_proyecto extends FPDF
{
	protected $datos;

	function __construct($datos)
	{
		parent::__construct();
		$this->datos = $datos;
		//Formato A4 y Vertical
		$this->AddPage('Portrait','A4');
		$this->SetAutoPageBreak(true,30);
		$this->cuerpo();
	}

	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,19,13,65,25);
		
		$this->SetFont('arial','',7);
		$this->setXY(130,10);
		$this->Cell(70,4,'Para ser llenado por la SGCyT',0,2,'C',false);
		$this->SetFont('arial','B',9);
		$this->Cell(70,6,'Código: '.$this->datos['codigo'],1,2,'C',false);
		$this->Cell(70,7,'Fecha de Presentación: ',1,0,'L',false);

		$this->SetFont('arial','B',14);
		$this->setXY(10,40);
		$tipo = ($this->datos['tipo'] == '0') ? 'PI' : 'PDTS';
		$this->SetFillColor(220,220,220);
		$this->Cell(190,9,'FORMULARIO DE PRESENTACIÓN DE '.$tipo,1,1,'C',true);	
		$this->SetFont('arial','B',9);
		$this->Cell(190,5,"Convocatoria {$this->datos['convocatoria_anio']}",0,0,'C',false);
		
	}

	function cuerpo()
	{
		$this->SetFillColor(220,220,220);

		/* ========= DENOMINACION DEL PROYECTO ================ */
		$this->SetFont('arial','B',9);
		$this->setXY(10,59);
		$this->Cell(190,7,'Denominación del Proyecto',1,1,'C',true);
		$this->SetFont('arial','',8);
		$this->MultiCell(190,5,$this->datos['descripcion'],1,'L',false);		


		$this->Ln();
		/* ========= TIPO DE PROYECTO Y FECHAS ================ */
		$this->SetFont('arial','B',9);
		$this->Cell(50,6,'Tipo de Proyecto: ',1,0,'R',true);
		$this->Cell(25,6,$this->datos['tipo_desc'],1,0,'C',false);
		$desde = date('d/m/Y',strtotime($this->datos['fecha_desde']) );
		$hasta = date('d/m/Y',strtotime($this->datos['fecha_hasta']) );
		$this->Cell(45,6,"Periodo del Proyecto: ",1,0,'R',true);
		$this->Cell(70,6,"Inicio: $desde - Fin: $hasta",1,1,'C',false);
		
		
		$this->Ln();
		/* ========= ÁREA Y SUBÁREA ================ */
		$this->SetFont('arial','B',9);
		$this->Cell(15,6,'Área: ',1,0,'R',true);
		$this->SetFont('arial','',9);
		$this->Cell(60,6,$this->datos['area'],1,0,'L',false);
		
		$this->SetFont('arial','B',9);
		$this->Cell(25,6,"Sub-Área: ",1,0,'R',true);
		$this->SetFont('arial','',9);
		$this->Cell(90,6,$this->datos['subarea'],1,1,'L',false);


		$this->Ln();
		/* ========= INTEGRANTES DEL PROYECTO ================ */
		$this->SetFont('arial','B',9);
		$this->Cell(190,7,'Integrantes del Proyecto',1,1,'C',true);
		$this->SetFont('arial','',8);
		$this->MultiCell(190,4,"Se deja asentado que se llevará a cabo la investigación de acuerdo con el Código de Núremberg y la Declaración de Hilsinki, con el objeto de respetar los derechos de las personas y salvaguardar su dignidad e integridad; que se respetarán los derechos de los animales y las normas éticas universalmente consensuadas a este respecto; que el desarrollo del proyecto no generarña impacto ambiental desfavorable y la conformidad expresa a lo establecido en la Ley Nº 25.626 (Habeas data). Asimismo, se declara conocer los términos de la Res. 641/98 C.S. sobre Propiedad Intelectual",1,'J',false);
		
		$this->Cell(30,7,'CUIL / DNI',1,0,'C',true);
		$this->Cell(70,7,'APELLIDO Y NOMBRES',1,0,'C',true);
		$this->Cell(40,7,'FUNCIÓN',1,0,'C',true);
		$this->Cell(50,7,'FIRMA',1,1,'C',true);
		foreach ($this->datos['integrantes'] as $integrante) {
			$this->Cell(30,6,$integrante['cuil'],1,0,'C',false);
			$this->Cell(70,6,$integrante['ayn'],1,0,'C',false);
			$this->Cell(40,6,$integrante['funcion'],1,0,'C',false);
			$this->Cell(50,6,'',1,1,'C',false);
		}

		
		$this->Ln();
		/* ========= APROBACIÓN COMITÉ? ================ */
		$this->SetFont('arial','',7);
		$this->Cell(70,4,'Para ser llenado por las autoridades de la U.A.',0,2,'C',false);
		$this->SetFont('arial','B',9);
		$this->Cell(170,6,'Requiere aprobación del Comité de Ética o Bioética?',1,0,'L',true);
		$this->Cell(10,6,'SI',1,0,'C',false);
		$this->Cell(10,6,'NO',1,0,'C',false);
		



	}

	function Footer()
	{
		$this->SetXY(10,-30);
		$this->Line(65,$this->getY(),145,$this->getY()); //Linea gris
		$this->setY($this->getY()+2);
		$this->Cell(0,5,'Aval Sec. Investigación / Dir. del Instituto',0,1,'C');
		$this->Cell(0,5,$this->datos['dependencia'],0,1,'C');
		$this->SetXY(10,-10);
		$this->Cell(0,5,'Página '.$this->PageNo(),0,0,'C',false);
	}
	function mostrar()
	{
		$this->Output('I','Proyecto '.$this->datos['codigo'].'.pdf');
	}
}
?>