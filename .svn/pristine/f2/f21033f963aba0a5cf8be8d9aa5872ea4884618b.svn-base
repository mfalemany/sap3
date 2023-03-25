<?php extract($datos); ?>
<style>
	.conceder_categoria{
		margin-top: 20px;
	}

	.conceder_titulo{
		font-size: 2.0rem;
    	text-align: center;
    	font-weight: bold;
	}
	.conceder_subtitulo{
		margin-top: 25px;
		margin-bottom: 10px;
		font-size: 1.7rem;
		font-weight: bold;
		color: #c32323;
	}
	.count_otorgados{
		margin: 0px 0px 4px 30px;
		font-size: 1.2rem;
		color: #4a4444;
		background-color: #feffb3;
		display: block;
		padding: 3px 10px;
		max-width: 500px;
	}
</style>
<div class="justificacion_puntajes">[ef id=justificacion_puntajes]</div>
<?php $categorias = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V']; ?>
<?php if (!empty($solicitud_categoria_dir) || !empty($solicitud_categoria_codir) || !empty($solicitud_categoria_subdir)) : ?>

	<div class="conceder_categoria">
		
		<div class="conceder_titulo"> Calificación transitoria de incentivos </div>
		
		<?php if (!empty($solicitud_categoria_dir)) : ?>
			<div class="conceder_subtitulo">
				Director: 
				<?php if ($categoria_actual_dir) : ?>
					tiene Categoría <?php echo $categorias[$categoria_actual_dir]; ?>
				<?php else: ?>
					No está categorizado
				<?php endif; ?>
				y solicitó Categoría <?php echo $categorias[$solicitud_categoria_dir['categoria']]; ?>
			</div>
			<div>
				[ef id=categoria_concedida_dir]
			</div>
			<?php if(!empty($categorias_concedidas_dir)) : ?>
				<?php foreach ($categorias_concedidas_dir as $cat => $evaluadores) : ?>
					<?php if ($cat === 'N') : ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores decidieron no aceptar esta solicitud, y mantener la situación original</p>
					<?php else: ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores concedieron la Categoría <?php echo $categorias[$cat]; ?> ante esta solicitud</p>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<div>
				
			</div>

		<?php endif; ?>
		
		<?php if (!empty($solicitud_categoria_codir)) : ?>
			<div class="conceder_subtitulo">
				Co-Director: 
				<?php if ($categoria_actual_codir) : ?>
					tiene Categoría <?php echo $categorias[$categoria_actual_codir]; ?>
				<?php else: ?>
					No está categorizado
				<?php endif; ?>
				y solicitó Categoría <?php echo $categorias[$solicitud_categoria_codir['categoria']]; ?>
			</div>
			<div>
				[ef id=categoria_concedida_codir]
			</div>
			<?php if(!empty($categorias_concedidas_codir)) : ?>
				<?php foreach ($categorias_concedidas_codir as $cat => $evaluadores) : ?>
					<?php if ($cat === 'N') : ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores decidieron no aceptar esta solicitud, y mantener la situación original</p>
					<?php else: ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores concedieron la Categoría <?php echo $categorias[$cat]; ?> ante esta solicitud</p>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>
		
		<?php if (!empty($solicitud_categoria_subdir)) : ?>
			<div class="conceder_subtitulo">
				Sub-Director: 
				<?php if ($categoria_actual_subdir) : ?>
					tiene Categoría <?php echo $categorias[$categoria_actual_subdir]; ?>
				<?php else: ?>
					No está categorizado
				<?php endif; ?>
				y solicitó Categoría <?php echo $categorias[$solicitud_categoria_subdir['categoria']]; ?>
			</div>
			<div>
				[ef id=categoria_concedida_subdir]
			</div>
			<?php if(!empty($categorias_concedidas_subdir)) : ?>
				<?php foreach ($categorias_concedidas_subdir as $cat => $evaluadores) : ?>
					<?php if ($cat === 'N') : ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores decidieron no aceptar esta solicitud, y mantener la situación original</p>
					<?php else: ?>
						<p class="count_otorgados"><?php echo count($evaluadores); ?> evaluadores concedieron la Categoría <?php echo $categorias[$cat]; ?> ante esta solicitud</p>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>
		<div class="justificacion_puntajes">
			<p>[ef id=motivo_rechazo_solicitud_categoria]</p>
		</div>

	
	</div>
<?php endif; ?>