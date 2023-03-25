<?php extract($datos); ?>
<style>
	fieldset{
		margin-bottom: 20px;
	}

	.tabla_subcriterios {
	    width: 80%;
	    margin: 10px auto;
	}

	.tabla_subcriterios tr:nth-child(odd){
		background-color: #efefef;
	}

	.criterio_descripcion{
		padding:  0px 25px 0px 10px;
	}

	.referencia{
		font-size: 1.1rem;
	    color: #555;
	    padding: 1px 0px 10px 10px;
	}

	input[type="number"]{
		font-size: 1.6rem;
	    max-width: 70px;
	    text-align: center;
	}
	.total_criterio{
		background-color: #b8ffc4;
		color: #706363;
		font-size: 1.7rem;
		font-weight: bold;
		padding: 7px 50px;
		text-align: right;
	}
	#documentos_pdf{
		margin-bottom: 20px;
	}

	.enlace_documento{
		padding: 2px 51px;
		text-decoration: none;
		cursor: pointer;
		background-color: #ffffa6;
		color: #444;
		border: 1px solid #999;
	}
	.enlace_documento:hover{
		font-weight: bold;
	}
</style>

<?php extract($datos); ?>

<div id="documentos_pdf">
		<a class="enlace_documento" target="_BLANK" href="http://sistema.cyt.unne.edu.ar/documentos/becas/estaticos/reglamento.pdf">Ver reglamento</a>
		<a class="enlace_documento" target="_BLANK" href="http://sistema.cyt.unne.edu.ar/documentos/becas/estaticos/pautas.pdf">Ver pautas de evaluación</a>
	</div>
<div id="form_criterios_propio">
	
	<?php foreach ($criterios as $criterio): ?>
		<fieldset>
			<legend><?php echo $criterio['criterio_evaluacion']; ?> (Máximo <?php echo $criterio['puntaje_maximo']; ?>)</legend>
			<table class="tabla_subcriterios">
			<?php foreach ($criterio['subcriterios'] as $subcriterio) : ?>
					<?php $id = $criterio['id_criterio_evaluacion'] . '-' . $subcriterio['id_subcriterio_evaluacion']; ?>
					<tr>
						<td class="criterio_descripcion"><?php echo $subcriterio['descripcion']; ?></td>
						<td>
							<input type="number" 
									class="puntaje-asignado"
									name="<?php echo $id; ?>" 
									id="<?php echo $id; ?>" 
									min="0"
									max="<?php echo ($subcriterio['maximo'] > 0) ? $subcriterio['maximo'] : '999'; ?>"
									onchange="recalcularTotales()"
									value="<?php echo isset($puntajes_asignados[$id]) ? $puntajes_asignados[$id] : "0"; ?>"
									> 
						</td>
					</tr>
					<tr>
						<td colspan="2" class="referencia"><?php echo nl2br($subcriterio['referencia']); ?></td>
					</tr>
			<?php endforeach ?>
			</table>
			<div class="total_criterio">
				Total asignado: <span id="total_<?php echo $criterio['id_criterio_evaluacion']; ?>">0</span> puntos.
			</div>
		</fieldset>
	<?php endforeach ?>

</div>

[dep id=form_dictamen]

<br>
[dep id=form_evaluadores]
<br>


