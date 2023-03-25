<style type="text/css">
	#contenedor{
		
	}
	#contenedor #detalles_grupo p{
		background-color: #5b5f9e;
		color:#FFF;
		text-align: center;
		font-size: 2em;
		margin: 0px;
		padding: 3px;
		text-shadow: 1px 1px 1px #000;
	}
	#contenedor #detalles_evaluaciones{
	}

	#contenedor #detalles_evaluaciones .detalle_evaluacion{
		background-color: #FFF;
		box-sizing: border-box;
		padding: 10px 0px 10px 20px;
		line-height: 1.1em;
	}
	#contenedor #detalles_evaluaciones .detalle_evaluacion .fecha_presentacion{
		background-color: #2c2b7d;
		box-sizing: border-box;
		color: #FFF;
		font-size: 1.4em;
		font-weight: bold;
		padding: 10px 0px 10px 10px;
	}
	.evaluacion{
		background-color: #ffde82;
		box-sizing: border-box;
		font-size: 1.2em;
		padding: 5px 5px;
	}
	.titulo_detalle{
		background-color: #eaeaff;
		font-weight: bold;
		padding: 5px;

	}
	.contenido, .evaluacion{
		font-size: 1.4em;
    	line-height: 1.4em;
	}
	.contenido p, .evaluacion p{
		margin:5px;
		text-align:justify;
	}


</style>
<div id="contenedor">
	<div id="detalles_grupo">
		<p>Denominación del Grupo: <?php echo $datos['grupo']['denominacion']; ?></p>
		<p>Categoría asignada: <?php echo $datos['grupo']['categoria']; ?></p>
	</div>
	<div id="detalles_evaluaciones">
		<?php foreach ($datos['informes'] as $informe): ?>
			<div class='detalle_evaluacion'>
				<div class='fecha_presentacion'>
					Informe presentado el 
					<?php echo date('d-m-Y',strtotime($informe['fecha_presentacion'])); ?>
				</div>
				<div class='contenido'>
					<div>
						<p class="titulo_detalle">Proyectos</p>
						<p><?php echo nl2br($informe['proyectos']); ?></p>
					</div>
					<div>
						<p class="titulo_detalle">Extensión</p>
						<p><?php echo nl2br($informe['extension']); ?></p>
					</div>
					<div>
						<p class="titulo_detalle">Publicaciones</p>
						<p><?php echo nl2br($informe['publicaciones']); ?></p>
					</div>
					<div>
						<p class="titulo_detalle">Transferencia</p>
						<p><?php echo nl2br($informe['transferencia']); ?></p>
					</div>
					<div>
						<p class="titulo_detalle">Formación de Recursos Humanos</p>
						<p><?php echo nl2br($informe['form_rrhh']); ?></p>
					</div>
					<div>
						<p class="titulo_detalle">Organización Reuniones Científicas</p>
						<p><?php echo nl2br($informe['org_eventos']); ?></p>
					</div>
				</div>
				<div class='evaluacion'>
					<p>
						<b><u>Resultado de la evaluación</u>: <?php echo $informe['resultado_desc']; ?></b></p>
					<p><u>Observaciones realizadas</u>: 
						<?php echo ($informe['observaciones']) ? 
							nl2br($informe['observaciones']) : 
							'Sin Observaciones'; 
						?>
						</p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script type="text/javascript">
	$('.colapsable .boton_colapsar').on('click',function(param){
		$(param.target).next().toggle(300);
	})
</script>