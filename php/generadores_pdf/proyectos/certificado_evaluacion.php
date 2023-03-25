<?php 
class Certificado_evaluacion extends FPDF
{
	function __construct($datos)
	{
		parent::__construct();
		// ei_arbol($datos);
		// return;
		extract($datos);
		
		//Formato A4 y Apaisado
		$this->AddPage('Landscape','A4');

		//Agrego la Imagen de fondo
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/";
        $archivo = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('archivo_certif_eval_proy');

	    $plantilla = $path.$archivo;
	    #ei_arbol($plantilla);
	    if(!file_exists($plantilla)){
	    	throw new Exception("No se encontró el archivo de plantilla para el certificado.");
	    }
	    $this->Image($plantilla,0,0,300);

		//agrego una fuente importada de Google Fonts
		$this->addFont('elegante','','JacquesFrancois-Regular.php');
		//agrego la fuente de la UNNE
		//$this->addFont('unne','','english.php');
		//agrego la fuente comun
		$this->addFont('lobster','','Lobster-Regular.php');

		//Encabezado con el nombre de la universidad
		$this->SetFont('lobster','',28);
		$nombre = ucwords(strtolower($evaluador['apellido'].", ".$evaluador['nombres']));
		if (strlen($nombre) > 30) {
			$nom=explode(" ", $evaluador['nombres']);
			$nombre = ucwords(strtolower($evaluador['apellido'].", ".$nom[0]));
		}
		if (is_numeric($evaluador['nro_documento'])) {
			$dni = number_format($evaluador['nro_documento'],0,',','.');
		}
		$this->setXY(60,72);
		$this->Cell(234,10,$nombre." - DNI Nº: ".$dni,0,0,'C',false);
	
		//Detalle de la evaluaci? y disciplina
		// $tipo = '';
		// switch($datos[0]['tipo']){
		// 	case 'PI':
		// 		$tipo = 'Proyectos de InvestigaciÃ³n';
		// 	break;
		// 	case 'PDTS':
		// 		$tipo = 'Proyectos de Desarrollo T. y S.';
		// 	break;
		// 	case 'Programa':
		// 		$tipo = 'Programas de I. y D.';
		// 	break;
		// }
		$ult_conv = toba::consulta_php('co_convocatorias')->get_convocatorias(array('aplicable' => 'PROYECTOS'))[0];
		$anio = substr($ult_conv['fecha_hasta'], 0, 4);
		$this->SetFont('times','BI',20);
		$this->setXY(60,97);
		switch ($evaluador['tipo_evaluador']) {
			case 'T':
				$evaluo = 'Proyectos e Informes de I+D';
				break;
			case 'P':
				$evaluo = 'Proyectos de I+D';
				break;
			case 'I':
				$evaluo = 'Informes de I+D';
				break;
			case 'L':
				$evaluo = 'Informes de I+D';
				break;
		}
		$this->MultiCell(234,9, $evaluo . " de la Convocatoria ".$anio."\nen la disciplina: \""	.$evaluador['disciplina']."\"",0,'C',false);
/*
		//c?igo
		$this->SetFont('times','IB',16);
		$this->setXY(53,89);
		$this->Cell(234,10,$datos['codigo'],0,0,'C',false);

		//Nombre de la Persona
		$this->SetFont('elegante','',18);
		$this->setXY(54,99);
		$this->Cell(234,10,ucwords(strtolower($datos['director'])),0,0,'C',false);

		//Nombre de la Convocatoria
		$this->SetFont('arial','B',14);
		$this->setXY(54,120);
		$this->Cell(234,10,$datos['convocatoria'],0,0,'C',false);*/

	}

	function mostrar()
	{
		$this->Output('I','Certificado.pdf');
	}
}
?>