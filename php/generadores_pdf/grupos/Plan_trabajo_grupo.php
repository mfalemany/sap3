<?php 
class Plan_trabajo_grupo extends FPDF
{
	protected $datos;

	function __construct($datos = array())
	{
		parent::__construct();
		
		$this->datos = $datos;
		$alto_linea  = 5;
		
		//Formato A4 y Apaisado
		$this->AddPage('Portrait','A4');
		
		$this->SetTextColor(150,150,150);
		$this->SetFont('Arial','BU',12);
		$this->MultiCell(0,$alto_linea,$datos['denominacion'],0,'C');

		$this->Ln();

		$this->SetTextColor(0,0,0);
		$this->SetFont('Arial','B',15);
		$this->Cell(0,$alto_linea+4,'Plan de Trabajo',1,1,'C');

		$items = [
			['etiqueta' => 'Extensi�n'           , 'indice' => 'extension'    ],
			['etiqueta' => 'Publicaciones'       , 'indice' => 'publicaciones'],
			['etiqueta' => 'Transferencia'       , 'indice' => 'transferencia'],
			['etiqueta' => 'Formaci�n RR.HH.'    , 'indice' => 'form_rrhh'    ],
			['etiqueta' => 'Organizaci�n Eventos', 'indice' => 'org_eventos'  ],
			['etiqueta' => 'Proyectos'           , 'indice' => 'proyectos'    ],
		];

		$this->Ln();

		foreach ($items as $item) {
			$this->SetFont('Arial','BU',10);
			$this->Cell(0,$alto_linea,$item['etiqueta'],0,1,'L');
			$this->SetFont('Arial','',9);

			if ($datos[$item['indice']]) {
				$this->MultiCell(0,$alto_linea,$datos[$item['indice']],0,'J');	
			} else {
				$this->MultiCell(0,$alto_linea,'Sin informaci�n declarada',0,'J');
			}

			$this->Ln();
		}
	}

	function Header()
	{
		//Agrego el logo de la UNNE
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
	    $this->Image($path,10,5,70,25);

	    $this->SetDrawColor(180,180,180);
		$this->Line(10,30,200,30); //Linea gris

		
		$this->SetFont('Arial','',10);
		$this->setXY(10,33);

		//Se agrega la convocatoria (es el año de la fecha de inscripción) y la fecha de impresión (ambas en un lugar fijo)
		$this->SetTextColor(150,150,150);
		$fecha = date('d-m-Y', strtotime($this->datos['fecha_presentacion']));
		$this->Text(140,29,'Fecha de presentaci�n: ' . $fecha);
	}

	function Footer()
	{
		$this->setY(280);
		$this->Cell(0,5,'Secretar�a General de Ciencia y T�cnica - UNNE', 0,1,'C');
		if (isset($this->datos['direccion_mail_grupos'])) {
			$this->Cell(0,5,'Contacto: ' . $this->datos['direccion_mail_grupos'], 0,0,'C');
		}
		
	}

	function saltar($lineas)
	{
		$this->y += $lineas; 
	}

	function mostrar()
	{
		$this->Output('I','Plan de trabajo.pdf');
	}
}

?>