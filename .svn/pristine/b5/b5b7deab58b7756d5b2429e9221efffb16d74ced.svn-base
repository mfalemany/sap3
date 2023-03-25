<?php
//Este script utiliza la libreria PdfParser para acceder al texto de los PDF y renombrarlos 
require_once 'vendor/autoload.php';
class ProcesadorRecibos{
	protected $lector;

	function __construct(){
		$this->lector = new \Smalot\PdfParser\Parser();
	}

	function encontrar_dni($texto){
		$cuil = preg_match_all('/[0-9]{2}-[0-9]{8}-[0-9]{1}/',$texto,$encontrados);	
		if(isset($encontrados[0])){
			foreach ($encontrados[0] as $cuil) {
				if($cuil != '30-99900421-7'){
					return substr($cuil,3,8);
				} 
			}
		}
	}
	function encontrar_mes($texto){
		$meses = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');	
		foreach ($meses as $indice => $mes) {
			$pos = strpos($texto,$mes);
			if($pos && $pos >= 0){
				//El texto de los recibos dice por ejemplo: enerode2020. Con esto, se obtiene el mes y el año
				return substr($texto,($pos+strlen($mes)+2),4)."-".str_pad($indice+1,2,'0',STR_PAD_LEFT).$mes;
			}
		} die;
	}

	/**
	 * Recibe los archivos subidos desde el cliente y los procesa para renombrarlos.
	 * @param  array $archivos Array de archivos (generalmente la variable $_FILES)
	 * @return array           retorna un array con los totales procesados
	 */
	function procesar($archivos){
		$subidos = array();
		$errores = array();

		for($i = 0; $i < count($archivos['name']); $i++){
			//Si no es un PDF o tuvo error
			if($archivos['type'][$i] != 'application/pdf' || $archivos['error'][$i] != 0){
				$errores[] = array('motivo' => 'No es un PDF o tiene errores', 'archivo' => $archivos['name'][$i]);
				continue;
			}

			$subidos[$i] = array(
				'name'      => $archivos['name'][$i],
				'type'      => $archivos['type'][$i],
				'tmp_name'  => $archivos['tmp_name'][$i],
				'error'     => $archivos['error'][$i],
				'errorsize' => $archivos['size'][$i]
			);
		}

		foreach ($subidos as $archivo) {
			$texto = $this->lector->parseFile($archivo['tmp_name']);
			$texto = str_replace(' ','',strtolower($texto->getText()));
			$dni = $this->encontrar_dni($texto);
			$mes = $this->encontrar_mes($texto);
			$recibo = $this->encontrar_nro_recibo($texto);
			if($dni && $mes && $recibo){
				if( ! move_uploaded_file($archivo['tmp_name'],"recibos_procesados/{$dni}_{$recibo}_{$mes}.pdf")){
					$errores[] = array('motivo' => 'Error al mover el archivo','archivo' => $archivos['name'][$i]);
				}
			}else{
				$errores[] = array('motivo' => 'No se pudieron obtener los parametros del PDF', 'archivo' => $archivos['name'][$i]);
			}
		}

		return $errores;
	}

	function encontrar_nro_recibo($texto){
		$pos = strpos($texto, 'recibodesueldon?mero:');
		return ($pos >= 0) ? substr($texto,$pos+22,5) : FALSE;
	}

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Publicar Recibos</title>
	<meta charset="utf-8">
</head>
<body>

<?php 
	if(isset($_FILES) && array_key_exists('archivos', $_FILES)){
		$a = new ProcesadorRecibos();
		$errores = $a->procesar($_FILES['archivos']);
		if(count($errores)){
			echo "Se produjeron algunos errores:<br>";
			foreach ($errores as $error) {
				echo "{$error['archivo']}: {$error['motivo']}<br>";
			}
		}else{
			echo "Todos los archivos fueron subidos con ?ito";
		}
	}
?>
	<form action="index.php" method="POST" enctype="multipart/form-data">
		<input type="file" name="archivos[]" multiple>
		<input type="submit" value="Enviar">
	</form>
</body>
</html>



