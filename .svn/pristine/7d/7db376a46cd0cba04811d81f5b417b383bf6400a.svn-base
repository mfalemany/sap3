<style>
	#form_container{
		border: 3px solid #3881da;
		border-radius: 10px;
		margin: 10px auto;
		min-width: 530px;
		padding: 5px 10px;
		width: 60%;
	}
	#form_container p{
		font-size: 16px;
		line-height: 20px;
		text-align: justify;
	}
	#requisitos_container{
	    align-content: flex-end;
	    align-items: flex-start;
		display: flex;
	    flex-direction: row;
	    flex-wrap: wrap;
	    justify-content: space-evenly;
	    margin-top: 20px;
	    padding: 5px 10px;
		
	}
	.tabla_requisitos{
		border-collapse: collapse;
		font-size: 15px;
		margin-top: 20px;
		width: 45%;
	}
	.tabla_requisitos caption{
		font-size: 1.5em;
		font-weight: bold;
	}
	.tabla_requisitos tr:nth-child(odd){
		background-color: #f1f1f1;
	}
	.tabla_requisitos tr td, 
	.tabla_requisitos tr th{
		border:  1px solid #333;
		padding: 4px 6px;
	}

	.tabla_requisitos tr th{
		text-align: center;
		background-color: #3a79c7;
		color: #FFF;
	}
	.tabla_requisitos tr td{
		vertical-align: top;
	}
	.tabla_requisitos tr td:first-child{
		text-align: justify;
	}
	#overlay_contenido{
		font-size: 1.4rem;
	}
	#archivos_subidos {
		width:  90%;
		margin: 20px auto;
	}

	#archivos_subidos a{
		display: block;
		line-height: 2em;
	}

</style>
<div id="form_container">
	<p>Los Docentes no categorizados o que consideren desactualizada su categor&iacute;a, podr&aacute;n obtener una <b>calificaci&oacute;n transitoria</b>, solo a los fines de la convocatoria seleccionada, mediante el cumplimiento de todos los requisitos detallados y presentar toda la documentaci&oacute;n correspondiente indicada. Recuerde que debe subir <b>solamente un archivo en cada ítem</b>.</p>
	<div>
		[dep id=form_calificacion]
	</div>
</div>

<?php 
	if (!empty($datos['solicitud'])) {

		$url  = toba::consulta_php('helper_archivos')->url_base();
		$ruta = toba::consulta_php('helper_archivos')->ruta_base();

		$ubicacion    = 'docum_personal/' . $datos['solicitud']['nro_documento'] . '/categoria_transitoria_incentivos'; 
		$ruta_carpeta = $ruta . $ubicacion;
		$url_carpeta = $url . $ubicacion;

		if (is_dir($ruta_carpeta)) {

			// Re-indexo requisitos
			$requisitos = [];
			foreach ($datos['requisitos'] as $requisito) {
				$requisitos[$requisito['id']] = $requisito;
			}			
		
			$carpeta = opendir($ruta_carpeta);

			while ($archivo = readdir($carpeta)) {
				if (!in_array($archivo, ['.', '..'])) {
					$id_requisito = substr($archivo, 2, 2);
					if (substr($archivo, 0, 2) === $datos['solicitud']['id_llamado'] . '-' ) {
						$archivos[] = ['archivo' => $archivo, 'requisito' => $requisitos[$id_requisito]];
					}
				}
			}
		}
	}
?>
<?php if (!empty($datos['solicitud'])) : ?>

<div id="archivos_subidos">
	<h3>Archivos cargados:</h3>
	<ul>
		<?php foreach ($archivos as $archivo) : ?>
			<li> 
				<b>Requisito</b> 
				<a target="_BLANK" href="<?php echo $url_carpeta; ?>/<?php echo $archivo['archivo'] ?>">
					<?php echo($archivo['requisito']['requisito']); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<?php endif; ?>