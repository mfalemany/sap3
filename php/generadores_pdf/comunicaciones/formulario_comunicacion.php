<?php 
class Formulario_comunicacion extends FPDF
{
	function __construct($comunicacion = array())
	{
		//echo html_entity_decode($comunicacion['resumen'],ENT_COMPAT,'ISO-8859-1');die;
		
		parent::__construct();
		
		//Formato A4 y Orientación Normal
		$this->AddPage('Portrait','A4');
		
		//Agrego el logo de la UNNE
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
	    $this->Image($path,10,5,70,25);
	    
	    //Configuraciones
	    $alto_linea = 5;
		$ancho_etiq = 25;
	    $bordes = 0;

	    $desde = date('d/m/Y',strtotime($comunicacion['periodo_desde']));
	    $hasta = date('d/m/Y',strtotime($comunicacion['periodo_hasta']));
	    
	    //Linea gris
	    $this->SetDrawColor(180,180,180);
		$this->Line(10,30,200,30); 

		//Nombre de la Convocatoria
		$this->setXY(10,33);
		$this->SetFont('Arial','BU',12);
		$this->Cell(0,$alto_linea,$comunicacion['convocatoria_desc'],0,1,'C');

		$this->Ln();
		//Detalles de la comunicacion
		
		$this->SetFont('Arial','',9);
		$this->Cell($ancho_etiq,$alto_linea,'Orden Poster:',$bordes,0,'R');
		$this->Cell(16,$alto_linea,$comunicacion['orden_poster'],$bordes,0,'L');
		$this->SetFont('Arial','',7);
		$this->Cell(17,$alto_linea,'(ID: ' . $comunicacion['id'] .")",$bordes,1,'L');

		$this->SetFont('Arial','B',10);
		$this->Cell($ancho_etiq,$alto_linea,'Autor:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,$comunicacion['autor'],$bordes,1,'L');

		/*$this->SetFont('Arial','',8);
		$this->Cell($ancho_etiq,$alto_linea,"Mail: ",$bordes,0,'R');
		$this->Cell(0,$alto_linea,$comunicacion['mail'],$bordes,1,'L');
		$this->Cell($ancho_etiq,$alto_linea,"Teléfono: ",$bordes,0,'R');
		$this->Cell(0,$alto_linea,$comunicacion['celular'],$bordes,1,'L');*/

		

		$this->Cell($ancho_etiq,$alto_linea,'Título:',$bordes,0,'R');
		$this->MultiCell(0,$alto_linea,$comunicacion['titulo'],$bordes,'J');
		
		$this->SetFont('Arial','',10);
		$this->Cell($ancho_etiq,$alto_linea,'Director:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,$comunicacion['director'],$bordes,1,'L');

		if(isset($comunicacion['codirector']) && $comunicacion['codirector']){
			$this->SetFont('Arial','',10);
			$this->Cell($ancho_etiq,$alto_linea,'Co-Director:',$bordes,0,'R');
			$this->Cell(0,$alto_linea,$comunicacion['codirector'],$bordes,1,'L');
		}

		$this->SetFont('Arial','',9);
		$this->Cell($ancho_etiq,$alto_linea,'Palabras clave:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,$comunicacion['palabras_clave'],$bordes,1,'L');
		$this->Ln();
//		$this->Line($this->getX(),$this->getY(),$this->getX()+190,$this->gety());
		$this->Cell($ancho_etiq,$alto_linea,'Área de Beca:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,ucwords(strtolower($comunicacion['area_beca_desc'])),$bordes,1,'L');

		$this->Cell($ancho_etiq,$alto_linea,'Tipo Beca:',$bordes,0,'R');
		$this->Cell(90,$alto_linea,ucwords(strtolower($comunicacion['tipo_beca_desc'])),$bordes,0,'L');
		$this->Cell($ancho_etiq,$alto_linea,'Periodo:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,"$desde al $hasta",$bordes,1,'L');

		$this->Cell($ancho_etiq,$alto_linea,'Lugar de trabajo:',$bordes,0,'R');
		$this->Cell(0,$alto_linea,ucwords(strtolower($comunicacion['dependencia_desc'])),$bordes,1,'L');

		$this->Cell($ancho_etiq,$alto_linea,'Proyecto:',$bordes,0,'R');
		$this->MultiCell(0,$alto_linea,"(".$comunicacion['proyecto_codigo'].") ".$comunicacion['proyecto_descripcion'],$bordes,'L');

		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell($ancho_etiq,$alto_linea,'Resumen:',$bordes,1,'L');
		$alto_linea = 4;
		$this->SetFont('Arial','',9);
		$this->MultiCell(0,$alto_linea,$comunicacion['resumen'],$bordes,'J');



		//Leyenda fecha y hora de generación
		$this->SetFont('Arial','',8);
		$this->Text(10,290,'Contacto: ' . $comunicacion['mail'] . "      Tel: " .$comunicacion['celular']);
		$this->Text(155,290,'Generado el '.date('d/m/Y')." a las ".date('H:i'));
	}

	function saltar($lineas)
	{
		$this->y += $lineas; 
	}

	function mostrar()
	{
		$this->Output('I','Formulario.pdf');
	}
}

?>