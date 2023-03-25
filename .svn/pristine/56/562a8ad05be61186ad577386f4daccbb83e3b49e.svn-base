<?php setlocale(LC_ALL,"es_ES"); ?>
<style type="text/css">
	#contenedor_detalles_grupo{
		font-family: verdana tahoma times;
	}
	#denominacion{
		font-family: verdana tahoma times;
		font-size: 2.4rem;
		font-weight: bold;
		margin: 10px 0px;
		text-align: center;
	}
	#descripcion{
		font-family: verdana tahoma times;
		font-size: 1.4rem;
		letter-spacing: 1px;
		line-height: 1.8rem;
		margin: 3px 10px 10px 10px;
		text-align: justify;
	}
	#info_complementaria{
		align-content: space-between;
		align-items: center;
		border-bottom: 1px solid #7e1a1a;
		border-top: 1px solid #7e1a1a;
		color: #7e1a1a;
		display: flex;
		flex-wrap: nowrap;
		font-size: 1.4rem;
		font-weight: bold;
		justify-content: space-evenly;
		margin: 15px 0px;
		padding: 5px 0px;
	}
	.detalles{
		font-size: 1.5em;
	}
	.detalles div{
		margin: 15px 5px;
	}
	#lineas_investigacion{
		text-align: justify;
	}
	#lineas_investigacion ul li{
		padding-right: 30px;
	}

	#detalles_coordinador table {
		border-collapse: collapse;
		margin: 10px auto;
		width: 90%;
	}

	#detalles_coordinador table tr td{
		border: 1px solid grey;
		padding: 3px 10px;
	}

	#detalles_coordinador table tr td:nth-child(2){
		text-align: center;
	}

	#detalles_coordinador table tr td a{
		text-decoration: none;
		color: #1745c9;
	}


</style>
<div id="contenedor_detalles_grupo">
	<div id='denominacion'>
		<?php echo $datos['grupo']['denominacion']; ?>
	</div>
	<?php if(isset($datos['grupo']['descripcion']) && $datos['grupo']['descripcion']): ?>
		<div id='descripcion'>
			<?php echo $datos['grupo']['descripcion']; ?>
		</div>
	<?php endif; ?>
	<div id="info_complementaria">
		<span><u><b>Facultad</b></u>: <?php echo $datos['grupo']['dependencia']; ?></span>
		<span><u><b>Área de Conocimiento</b></u>: <?php echo $datos['grupo']['area_conocimiento']; ?></span>
	</div>
	<div class="detalles">
			
		<div id="palabras_clave">
			<span><u><b>Palabras clave</b></u>: <?php echo $datos['grupo']['palabras_clave']; ?></span>
		</div>
		<div id="inicio_actividades">
			<span><u><b>Inicio de actividades</b></u>: <?php echo ucfirst(strftime('%B %Y', strtotime($datos['grupo']['fecha_inicio']))); ?></span>
		</div>
		<div id="lineas_investigacion">
			<p><u><b>Líneas de Investigación</b></u>:</p>
			<ul>
				<?php foreach ($datos['lineas_investigacion'] as $linea) : ?>
					<li><?php echo $linea; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		
		
		<div id="detalles_coordinador">
			<p><u><b>Coordinador del Grupo</b></u>: <?php echo $datos['detalles_coordinador']['ayn']; ?></p>
				
			<ul>
				<li>Categoría Incentivos: <?php echo $datos['detalles_coordinador']['categoria_desc']; ?></li> 
			
				<?php if (isset($datos['cat_trans_solicitada'])) : ?>
				<li>Categoría transitoria solicitada: <?php echo $this->cat_incentivos[$datos['cat_trans_solicitada']['categoria']]; ?></li>
				<?php endif ?>

			</ul>

			<?php if (isset($datos['cat_trans_solicitada'])) : ?>
			<table>
				<thead>
					<th>Requisitos presentados para esta solicitud</th>
					<th></th>
				</thead>
				<tbody>
					<?php foreach ($datos['documentacion'] as $documentacion) : ?>
					<tr>
						<td><?php echo $documentacion['requisito']; ?></td>
						<td><a href="<?php echo toba::consulta_php('helper_archivos')->url_base(); ?>docum_personal/<?php echo $datos['detalles_coordinador']['nro_documento']; ?>/categoria_transitoria_incentivos/<?php echo $documentacion['id_llamado']; ?>-<?php echo $documentacion['id']; ?>.pdf" target="_BLANK">Ver documento</a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		
		</div>

	</div>
</div>

