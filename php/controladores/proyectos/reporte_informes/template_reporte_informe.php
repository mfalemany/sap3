<style>
	table{
		border-collapse: collapse;
		margin: 15px auto;
		width: 95%;
	}
	table tr td{
		border: 1px solid #222;
		padding:  3px 2px;
	}
	.titulo{
		background-color: #285d9e;
		color: #FFF;
		text-align: center;
		font-size: 1.2em;
    	font-weight: bold;
	}
</style>
<section>
	<div class="separador_seccion_form">Informe</div>
	<article>
		<table>
			<tr class="titulo">
				<td colspan="4">Detalles del informe</td>
			</tr>
			<tr>
				<td>Proyecto</td>
				<td colspan=3>(<?php echo $datos['proyecto']['codigo']; ?>) <?php echo $datos['proyecto']['descripcion']; ?></td>
			</tr>
			<tr>
				<td>Desde</td>
				<td><?php echo (new Datetime($datos['proyecto']['fecha_desde']))->format('d-m-Y'); ?></td>
				<td>Hasta</td>
				<td><?php echo (new Datetime($datos['proyecto']['fecha_hasta']))->format('d-m-Y'); ?></td>
			</tr>
			<tr>
				<td>Tipo de informe: </td>
				<td><?php echo $datos['informe']['tipo_informe']; ?></td>
				<td>Fecha de presentación</td>
				<td><?php echo (new Datetime($datos['informe']['fecha_presentacion']))->format('d-m-Y'); ?></td>
			</tr>
			<?php if($datos['informe']['estado'] != 'C') : ?>
				<tr>
					<td>Presentado por: </td>
					<td><?php echo $datos['informe']['presentado_por_desc']; ?></td>
					<td>Fecha de presentación</td>
					<td><?php echo (new Datetime($datos['informe']['fecha_presentacion']))->format('d-m-Y'); ?></td>
				</tr>
			<?php else: ?>
				<tr>
					<td>Estado: </td>
					<td colspan="3">Abierto</td>
				</tr>
			<?php endif; ?>
		</table>
	</article>
	<div class="separador_seccion_form">Evaluación de Integrantes</div>
	<article>
		<table>
			<tr class="titulo">
				<td>Integrante</td>
				<td>Evaluacion</td>
			</tr>
			<?php foreach($datos['eval_integrantes'] as $eval) : ?>
				<tr>
					<td><?php echo $eval['integrante']; ?></td>
					<td><?php echo $eval['evaluacion']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</article>
	<div class="separador_seccion_form">Publicaciones</div>
	<article>
		<table>
			<tr class="titulo">
				<td>Año</td>
				<td>Título</td>
				<td>Tipo de Publicación</td>
				<td>Estado</td>
				<td>URL / Enlace</td>
			</tr>
			<?php foreach($datos['trab_publicacion'] as $trab) : ?>
				<tr>
					<td><?php echo $trab['anio']; ?></td>
					<td><?php echo $trab['titulo']; ?></td>
					<td><?php echo $trab['tipo_publicacion']; ?></td>
					<td><?php echo $trab['estado_desc']; ?></td>
					<?php if($trab['url']) : ?>
						<td><a href="<?php echo $trab['url']; ?>" target='_BLANK'>Ver</a></td>
					<?php else: ?>
						<td>Sin declarar</td>
					<?php endif; ?>

				</tr>
			<?php endforeach; ?>
		</table>
	</article>
	<div class="separador_seccion_form">Difusión</div>
	<article>
		<table>
			<tr class="titulo">
				<td>Año</td>
				<td>Título</td>
				<td>Evento</td>
				<td>Alcance</td>
				<td>URL / Enlace</td>
			</tr>
			<?php foreach($datos['trab_difundido'] as $trab) : ?>
				<tr>
					<td><?php echo $trab['anio']; ?></td>
					<td><?php echo $trab['titulo']; ?></td>
					<td><?php echo $trab['evento']; ?></td>
					<td><?php echo $trab['alcance_desc']; ?></td>
					<?php if($trab['url']) : ?>
						<td><a href="<?php echo $trab['url']; ?>" target='_BLANK'>Ver</a></td>
					<?php else: ?>
						<td>Sin declarar</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</table>
	</article>
	<div class="separador_seccion_form">Transferencia</div>
	<article>
		<table>
			<tr class="titulo">
				<td>Descripción</td>
				<td>Destinatario</td>
				<td>Tipo</td>
			</tr>
			<?php foreach($datos['trab_transferido'] as $trab) : ?>
				<tr>
					<td><?php echo $trab['descripcion']; ?></td>
					<td><?php echo $trab['destinatario']; ?></td>
					<td><?php echo $trab['tipo_transferencia']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</article>
	<div class="separador_seccion_form">Dirección de Tesis</div>
	<article>
		<table>
			<tr class="titulo">
				<td>Tesista</td>
				<td>Director</td>
				<td>Título</td>
				<td>Carrera</td>
				<td>Etapa</td>
			</tr>
			<?php foreach($datos['direccion_tesis'] as $tesis) : ?>
				<tr>
					<td><?php echo $tesis['tesista']; ?></td>
					<td><?php echo $tesis['director']; ?></td>
					<td><?php echo $tesis['titulo']; ?></td>
					<td><?php echo $tesis['carrera']; ?></td>
					<td><?php echo $tesis['etapa_tesis']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</article>
</section>