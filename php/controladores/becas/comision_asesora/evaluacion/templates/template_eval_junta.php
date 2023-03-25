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
	#tabla_puntajes_comision{
		width: 100%;
	}

	#tabla_puntajes_comision table{
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 20px;
	}

	#tabla_puntajes_comision table tr{

	}
	#tabla_puntajes_comision table tr:nth-child(even) {
		background-color: #f5f8fd;
	}
	#tabla_puntajes_comision table tr td{
		border: 1px solid grey;
		padding: 4px 10px;
	}
	.titulo_criterio{
		background-color: #FFF;
		font-weight: bold;
		text-align: center;
	}
	.titulo_subcriterio{
		font-size: 0.9em;	
	}

</style>
<?php extract($datos); ?>
<h2 class="centrado">Puntajes asignados por la Comisión Asesora</h2>
<div id="tabla_puntajes_comision">
	<table>
		<tr class="cabecera_tabla">
			<td>Criterio</td>
			<td>Sub-Criterio</td>
			<td>Referencia</td>
			<td>Puntaje Otorgado</td>
			<td>Total Criterio</td>
		</tr>

		<?php $criterios_en_cuadro = []; ?>
		
		<?php foreach($criterios as $criterio => $detalles_criterio) : ?>
			
			<?php foreach ($detalles_criterio as $subcriterio => $detalles_subcriterio) : ?>
			
				<?php $id = $detalles_subcriterio['id_criterio_evaluacion'] . '-' . $detalles_subcriterio['id_subcriterio_evaluacion']; ?>
			
				<tr>
					<?php if (!in_array($criterio, $criterios_en_cuadro)) : ?>
						<td class="titulo_criterio" rowspan="<?php echo count($detalles_criterio); ?>"><?php echo $criterio; ?> (Max. <?php echo $detalles_subcriterio['puntaje_maximo']; ?>)</td>
					<?php endif ?>
					<td class="titulo_subcriterio"><?php echo $subcriterio; ?></td>
					<td><?php echo nl2br($detalles_subcriterio['referencia']); ?></td>
					<td class="centrado"><?php echo $desglose_puntajes[$id]; ?></td>

					<!-- TOTAL DEL CRITERIO (SUMA DE LOS PUNTAJES INTERMEDIOS) -->
					<?php if (!in_array($criterio, $criterios_en_cuadro)) : ?>
						<td class="titulo_criterio" rowspan="<?php echo count($detalles_criterio); ?>"><?php echo $detalles_subcriterio['total_criterio']; ?></td>
					<?php endif ?>

					<?php $criterios_en_cuadro[] = $criterio; ?>
				</tr>
			
			<?php endforeach; ?>
		
		<?php endforeach; ?>
	</table>

	<div id="evaluadores">
		<h3>Evaluadores</h3>
		<ul>
			<?php foreach(explode('/', $dictamen_comision['evaluadores']) as $evaluador) : ?>
				<li><?php echo $evaluador; ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<p>
		<u><b>Justificación de los puntajes asignados:</b></u> 
		<?php echo $dictamen_comision['justificacion_puntajes']; ?>
	</p>
</div>
<h2 class="centrado">Dictamen de Junta Coordinadora</h2>
<div id="documentos_pdf">
	<a class="enlace_documento" target="_BLANK" href="http://sistema.cyt.unne.edu.ar/documentos/becas/estaticos/reglamento.pdf">Ver reglamento</a>
	<a class="enlace_documento" target="_BLANK" href="http://sistema.cyt.unne.edu.ar/documentos/becas/estaticos/pautas.pdf">Ver pautas de evaluación</a>
</div>

[dep id=form_dictamen]