<style>
	#pant_aval{
		justify-content: space-around;
		background-color: #FFFFFF;
		display: flex;
		flex-wrap: wrap;
		font-size: 16px;
		margin: 10px;
		padding: 10px;
	}
	.seccion{
		border: 1px solid #AAA;
		margin: 10px 10px;
		width: 45%;

	}
	.seccion div{
		margin: 5px 0px;
	}
	.seccion .titulo_seccion{
		background-color: #6468a6;
		color:#FFF;
		font-weight: bold;
		padding: 3px 0px 3px 10px;

	}
	.seccion .cuerpo_seccion{
		padding: 2px 5px 2px 30px;
	}
	.label{
		font-weight: bolder;
		color: #8B1010;
		text-decoration: underline;
	}
	#detalles_proyecto{
		flex-grow: 1;
	}
	#resultado_aval{
		background-color: #9ecfda;
		margin-top: 20px;
		width: 100%;
	}
</style>
<?php $categorias = array('1'=>'I','2'=>'II','3'=>'III','4'=>'IV','5'=>'V'); ?>
<?php 
	extract($datos); 
?>
<div id="pant_aval">
	<div id="detalles_beca" class="seccion">
		<div class="titulo_seccion">Detalles de la inscripción</div>
		<div class="cuerpo_seccion">
			<div><span class="label">Postulante:</span> <?php echo $postulante; ?></div>
			<div><span class="label">Tipo de Beca:</span> <?php echo ucfirst(strtolower($tipo_beca)); ?></div>
			<div>
				<span class="label">Director:</span> <?php echo $director ?>
				<?php if($cat_incentivos_dir) : ?>
					<?php echo " (Cat. " . $categorias[$cat_incentivos_dir] . ")"; ?>	
				<?php endif ?>
			</div>
			<div>
				<span class="label">Co-Director:</span> <?php echo $codirector; ?>
				<?php if($cat_incentivos_codir) : ?>
					<?php echo " (Cat. " . $categorias[$cat_incentivos_codir] . ")"; ?>	
				<?php endif ?>
			</div>
			<div><span class="label">Sub-Director:</span> <?php echo $subdirector; ?></div>
			<div><span class="label">Título del Plan de Beca:</span> <?php echo $titulo_plan_beca; ?></div>
			<div>
				<span class="label">Plan de trabajo propuesto:</span> 
				<?php 
					$archivo = sprintf('%s/becas/doc_por_convocatoria/%s/%s/%s/Plan de Trabajo.pdf',$datos['ruta_documentos'],$datos['id_convocatoria'],$datos['id_tipo_beca'],$datos['nro_documento']);
					
					$url = sprintf('%s/becas/doc_por_convocatoria/%s/%s/%s/Plan de Trabajo.pdf',$datos['url_documentos'],$datos['id_convocatoria'],$datos['id_tipo_beca'],$datos['nro_documento']);
					
					if(file_exists($archivo)) : ?>
						<a href="<?php echo $url; ?>" target="_BLANK">Ver documento</a>
					<?php endif; ?>
			</div>

		</div>
	</div>
	<div id="detalles_postulante" class="seccion">
		<div class="titulo_seccion">Detalles del postulante</div>
		<div class="cuerpo_seccion">
			<div><span class="label">Edad:</span> <?php echo ($fecha_nac) ? $this->calcular_edad($fecha_nac)." años" : '---';?></div>
			<div><span class="label">Carrera:</span> <?php echo ucwords(strtolower($carrera)); ?></div>


			<div><span class="label">Cantidad de materias aprobadas:</span> <?php echo $materias_aprobadas; ?> (<?php echo (round($materias_aprobadas/$materias_plan*100,0)) ?>% de la carrera)</div>
			<div><span class="label">Año de ingreso a la carrera:</span> <?php echo $anio_ingreso; ?></div>
			<div><span class="label">Promedio histórico:</span> <?php echo $prom_hist; ?></div>
			<div><span class="label">Lugar Trabajo Becario:</span> <?php echo ucwords(strtolower($lugar_trabajo_becario)); ?></div>
		</div>
	</div>
	<div id="detalles_proyecto" class="seccion">
		<div class="titulo_seccion">Detalles del Proyecto en el cuál participará el postulante</div>
		<div class="cuerpo_seccion">
			<div><span class="label">Proyecto</span>: (<?php echo $proyecto_codigo; ?>) - <?php echo $proyecto_desc; ?></div>
			<div>
				<?php if($director_proyecto) : ?>
				<span class="label">Director</span>: <?php echo $director_proyecto; ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<span class="label">Cat. Incentivos</span>: <?php echo ($cat_incentivos_dir_proyecto) ? $this->cat_incentivos[$cat_incentivos_dir_proyecto]: 'Sin información'; ?>
			<?php endif; ?>
			</div>
			<div>
				<span class="label">Desde</span>: <?php echo $this->fecha_dmy($proy_fecha_desde); ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="label">Hasta</span>: <?php echo $this->fecha_dmy($proy_fecha_hasta); ?>
			</div>
		</div>
	</div>
	<div id="resultado_aval">
		[dep id=form_aval_beca]
	</div>
</div>