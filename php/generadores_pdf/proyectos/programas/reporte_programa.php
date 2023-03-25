<?php 
class Reporte_programa extends FPDF
{
	protected $datos;

	function __construct($datos)
	{
		parent::__construct();
		//El reporte se muestra completo, salvo que se especifique lo contrario
		$datos['ver_completo'] = (isset($datos['ver_completo'])) ? $datos['ver_completo'] : TRUE;
		$this->datos = $datos;
		//Formato A4 y Vertical
		$this->AddPage('Portrait','A4');
		$this->SetAutoPageBreak(true,30);

		/* ======== ENCABEZADO: DATOS BÁSICOS DEL PROGRAMA =========================== */
		$this->setXY(10,40);
		$this->imprimir_titulo_cuadro('Denominación');
		$this->SetFont('arial','',8);
		$this->SetTextColor(0,0,0);
		$this->MultiCell(190,7,$this->datos['denominacion'],1,'C',false);
		$this->imprimir_subtitulo('Director/a del Programa:');
		$this->SetFont('arial','',8);
		$this->SetTextColor(0,0,0);
		$this->MultiCell(190,7,strtoupper($this->datos['dir_apellido']) . ", " . $this->datos['dir_nombres'],1,'C',false);
		$this->Ln();
		$this->imprimir_subtitulo('Periodo de ejecución:');
		$this->SetFont('arial','',8);
		$this->SetTextColor(0,0,0);
		$periodo = "Inicio: ".date('d/m/Y',strtotime($this->datos['fecha_desde']))." - Fin: ".date('d/m/Y',strtotime($this->datos['fecha_hasta']));
		$this->MultiCell(190,7,$periodo,1,'C',false);
		$this->Ln();

		/* =========================================================================== */
		$this->proyectos($datos['proyectos']);
		$this->Ln();
		
		if(isset($datos['dependencia']) && $datos['dependencia']){
			$this->imprimir_titulo_cuadro('Facultad/Instituto');
			$this->SetFont('arial','',10);
			$this->Cell(190,5,$datos['dependencia'],0,1,'C',false);
			$this->Ln();
		}


		if(isset($datos['area-subarea']) && $datos['area-subarea']){
			$this->imprimir_titulo_cuadro('Área y Subárea de Investigación');
			$this->SetFont('arial','',10);
			$this->Cell(190,5,$datos['area-subarea']['area_tematica'].": ".$datos['area-subarea']['subarea'],0,1,'C',false);
			$this->Ln();
		}

		//Si el reporte es llamado desde ámbitos públicos, no se muestran algunas cosas
		if( ! $this->datos['ver_completo']) return;
		
		$this->detalles_programa($datos);
		$this->Ln();
	}


	function proyectos($detalles)
	{
		$this->imprimir_titulo_cuadro('Proyectos que conforman el Programa');
		$ancho_cols = array(15,120,10,45);
		/* ENCABEZADO DE LA TABLA */
		$this->SetTextColor(255,255,255);
		$this->Cell($ancho_cols[0],5,"Código",1,0,'C',true);
		$this->Cell($ancho_cols[1],5,"Denominación",1,0,'C',true);
		$this->Cell($ancho_cols[2],5,"Tipo",1,0,'C',true);
		$this->Cell($ancho_cols[3],5,"Director/a",1,0,'C',true);
		$this->Ln();


		
		/* CUERPO DE LA TABLA */
		$this->SetFont('arial','',6);
		$this->SetTextColor(0,0,0);
		foreach ($detalles as $proyecto) {
			//var_dump($proyecto);
			$this->Cell($ancho_cols[0],5,$proyecto['codigo'],1,0,'C',false);
			$this->Cell($ancho_cols[1],5,substr($proyecto['descripcion_corta'],11),1,0,'L',false);
			switch ($proyecto['tipo']) {
			case '0':
				$this->Cell($ancho_cols[2],5,"PI",1,0,'C',false);
				break;
			case 'D':
				$this->Cell($ancho_cols[2],5,"PDTS",1,0,'C',false);
				break;
			case '9':
				$this->Cell($ancho_cols[2],5,"Ext.",1,0,'C',false);
				break;
			default:
				$this->Cell($ancho_cols[2],5,"Otro",1,0,'C',false);
				break;
			}
			$this->Cell($ancho_cols[3],5,$proyecto['director'],1,0,'L',false);
			$this->Ln();

		}
	}

	function detalles_programa($detalles)
	{

		/* ====================== OBJETIVO GENERAL ============================ */
		if(isset($detalles['objetivos']) && $detalles['objetivos']){
			$this->imprimir_titulo_cuadro('Objetivo General');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['objetivos'],0,'J',false);
			$this->Ln();
		}
		/* ====================== FUNDAMENTACION ============================ */
		if(isset($detalles['fundamentacion']) && $detalles['fundamentacion']){
			$this->imprimir_titulo_cuadro('Fundamentación');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['fundamentacion'],0,'J',false);
			$this->Ln();
		}

		/* ====================== ARTICULACION ============================ */
		if(isset($detalles['articulacion']) && $detalles['articulacion']){
			$this->imprimir_titulo_cuadro('Articulación');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['articulacion'],0,'J',false);
			$this->Ln();
		}
		/* ====================== TRANSFERENCIA ============================ */
		if(isset($detalles['transferencia']) && $detalles['transferencia']){
			$this->imprimir_titulo_cuadro('Transferencia');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['transferencia'],0,'J',false);
			$this->Ln();
		}
		/* ====================== IMPACTO ============================ */
		if(isset($detalles['impacto']) && $detalles['impacto']){
			$this->imprimir_titulo_cuadro('Impacto');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['impacto'],0,'J',false);
			$this->Ln();
		}
		/* ================= ABORDAJE INTERDISCIPLINARIO ================== */
		if(isset($detalles['abordaje_interdisc']) && $detalles['abordaje_interdisc']){
			$this->imprimir_titulo_cuadro('Abordaje Interdisciplinario');
			$this->SetFont('arial','',10);
			$this->MultiCell(190,5,$detalles['abordaje_interdisc'],0,'J',false);
			$this->Ln();
		}
	}


	function Header()
	{
		$path = toba::proyecto()->get_www();
        $path = $path['path']."img/logo_membrete.png";
		$this->Image($path,10,13,55,20);
		$this->SetFont('arial','BU',17);

		$this->setXY(10,26);
		$alin = (strlen($this->datos['codigo']) > 6) ? 'R' : 'C' ;
		$this->Cell(190,7,"Programa ".$this->datos['codigo'],0,1,$alin,false);	
		$this->Line(10,39,200,39);
		$this->setXY(10,42);

	}

	function imprimir_titulo_cuadro($texto)
	{
		$this->SetFont('arial','B',9);
		$this->SetFillColor(110,123,188);
		$this->SetTextColor(255,255,255);
		$this->Cell(190,7,$texto,1,1,'C',true);	
		//Vuelvo el color a negro
		$this->SetTextColor(0,0,0);
	}	

	function imprimir_subtitulo($texto)
	{
		$this->SetFont('arial','BI',9);
		$this->SetFillColor(110,123,188);
		$this->SetTextColor(255,255,255);
		$this->Cell(190,7,$texto,1,1,'L',true);	
		//Vuelvo el color a negro
		$this->SetTextColor(0,0,0);
	}
	
	function Footer()
	{
		$this->SetY(-15);
		$this->Cell(190,5,'Página '.$this->PageNo(),0,1,'C',false);
	}
	function mostrar()
	{
		$this->Output('I','Programa.pdf');
	}
		
}
?>