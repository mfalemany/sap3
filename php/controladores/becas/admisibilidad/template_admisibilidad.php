<?php extract($datos); ?>
<style>
	*{
		box-sizing: border-box;
	}
	#cabecera_admisibilidad{

	}
	#detalles_postulante{
		font-size: 1.3em;
		margin: 5px 0px;
		text-align:center;
	}
	#detalles_postulante p{
		margin:0px;
		padding:0px;
	}
	#detalles_postulante ul{
		margin:0px;
		padding:0px;
	}
	#detalles_postulante ul li{
		margin:0px;
		padding:0px;	
	}

	#puntaje{
		background-color: #5e6192;
		color: #fff;
		font-size: 1.3em;
		font-weight: bold;
		padding: 3px 0px;
		text-align: center;
		text-shadow: 1px 1px 1px #000;
	}
	#contenedor_admisibilidad{
		align-content: center;
		display: flex;
		flex-direction: column;
		flex-wrap: nowrap;
		justify-content: space-evenly;
	}
	#contenedor_admisibilidad #direccion{
		width: 100%;
	}
	#contenedor_admisibilidad #direccion a{
		text-decoration:  none;
	}
	#contenedor_admisibilidad #direccion table{
		border-collapse: collapse;
		width:  100%;
	}
	#contenedor_admisibilidad #direccion table th{
		border-collapse: collapse;
		border: 1px solid black;
		background-color:  #8185ce;;
		color: #FFF;
		font-size: 1.1em;
		padding: 4px 0px;
		text-align: center;
		text-shadow: 1px 1px 1px black;
		
	}
	#contenedor_admisibilidad #direccion table tr td{
		border: 1px solid black;
		font-size:  1.2em;
		padding: 5px 0px;
		text-align: center;
	}
	#contenedor_admisibilidad #direccion #cargos{
		list-style: none;
		margin: 0px;
		padding: 0px;
	}
	#contenedor_admisibilidad #direccion #cargos li{
		margin:  0px;
		padding: 0px;
	}

	#contenedor_admisibilidad #detalles{
		display: flex;
		flex-direction:  row;
		flex-wrap: nowrap;
		justify-content: space-evenly;	
		margin-bottom: 7px;
	}
	#contenedor_admisibilidad #detalles #documentacion{
		display: flex;
		flex-direction:  column;
		width: 18%;
	}
	#contenedor_admisibilidad #detalles #documentacion a{
		background-color: #FFF;
		box-shadow: 1px 1px 2px black;
		display: block;
		font-size: 1.2em;
		margin: 5px auto;
		padding: 3px 0px;
		text-align: center;
		text-decoration: none;
		width: 90%;

	}
	#contenedor_admisibilidad #detalles #controles{
		width: 22%;
	}
	#contenedor_admisibilidad #detalles #controles p{
		background-color: #35ff7c;
		box-shadow: 1px 1px 2px black;
		display: block;
		font-size: 1.2em;
		margin: 5px auto;
		padding: 3px 0px;
		text-align: center;
		text-decoration: none;
		width: 90%;
	}
	#contenedor_admisibilidad #detalles #formulario{
		
	}
	#contenedor_admisibilidad #requisitos{
		width:  100%;
	}
	.seccion{
		border:  1px solid #444;
		box-shadow: 2px 2p 2px 10px black;
		margin-bottom: 10px;
	}
	.seccion .titulo_seccion{
		background-color: #5e6192;
		color: #FFF;
		font-size:  2em;
		text-align: center;
		text-shadow:  1px 1px 1px #000;
	}
	.fallo{
		background-color: #ff5b2b !important;
		color: #fbff00;
		font-weight: bold;
	}
	.vigente{
		color: green;
	}
	.vencido{
		color: red;
	}

	.tabla_solic_trans_inc_dir{
		border-collapse: collapse;
		margin: 15px auto;
		width:  80%;
	}

	.tabla_solic_trans_inc_dir tr{
		border-collapse: collapse;
		border: 1px solid black;
		background-color:  #FFF;
		font-size: 1.2em;
		padding: 2px 10px;
	}

	.tabla_solic_trans_inc_dir tr:nth-child(odd){
		background-color: #EEE;




	}
	.tabla_solic_trans_inc_dir tr td{
		padding: 0px 10px;
	}

	.tabla_solic_trans_inc_dir tr td a{
		background-color: #5e6192;
		box-shadow: 1px 1px 2px black;
		color: #FFF;
		display: block;
		font-size: 1.2em;
		margin: 7px;
		padding: 2px 25px;
		text-align: center;
		text-decoration: none;
		width: 90%;
		
	}
	

	.categoria_solicitada{
		font-size: 1.7em;
		text-align: center;
		font-weight: bolder;
		margin: 10px 0px;


	}


</style>
<div id="cabecera_admisibilidad">
	<h2 class='separador_seccion_form'><?php echo $postulante ?></h2>
	<div id="puntaje">
		<?php 
			$subtipos_beca       = ['G' => 'Graduados', 'D' => 'Docentes'];
			$informacion_interna = json_decode($datos['inscripcion']['beca']['insc_informacion_interna'], true);
			$subtipo_beca_desc   = !empty($informacion_interna['subtipo_beca']) ? "({$subtipos_beca[$informacion_interna['subtipo_beca']]})" : '';
		?>
		<?php echo $detalles_tipo_beca['tipo_beca']; ?> <?php echo $subtipo_beca_desc; ?> - Puntaje inicial: <?php echo $inscripcion['postulante']['puntaje']; ?> puntos
	</div>
	<div id="detalles_postulante">
		<p>
			<?php if($inscripcion['beca']['id_tipo_beca'] == 1) : ?>
				Facultad/Instituto: <?php echo $inscripcion['postulante']['nombre_dependencia']; ?>
			<?php else: ?>
				Lugar de trabajo: <?php echo $inscripcion['beca']['lugar_trabajo_becario']; ?>
			<?php endif; ?>
		</p>
		<p>
			<ul>
				<li>Mail: 
					<?php if($inscripcion['postulante']['mail']) : ?>
						<a href="mailto:<?php echo $inscripcion['postulante']['mail']; ?>" target="_BLANK">
							<?php echo $inscripcion['postulante']['mail']; ?>
						</a>
					<?php else: ?>
						No declarado
					<?php endif; ?>
				</li>
				<li>Celular: 
					<?php if($inscripcion['postulante']['celular']) : ?>
						<a href="https://web.whatsapp.com/send?phone=549<?php echo $inscripcion['postulante']['celular']; ?>" target="_BLANK">
							<?php echo str_replace(array('-',' ','(',')'), '', $inscripcion['postulante']['celular']); ?>
						</a>
					<?php else: ?>
						No declarado
					<?php endif; ?>
				</li>
				
				<?php if (!empty($informacion_interna['subtipo_beca']) && $informacion_interna['subtipo_beca'] == 'D') : ?>
				<li>
					Cargos: 
						<ul>
						<?php 
							$postulante_cargos = [];

							foreach ($datos['postulante_cargos'] as $cargo) {
								$time_desde = strtotime($cargo['fecha_desde']);
								$time_hasta = strtotime($cargo['fecha_hasta']);
								
								$fecha_desde = new DateTime('@'.$time_desde);
								$fecha_hasta = new DateTime('@'.$time_hasta);
								
								echo "<li>" . sprintf('%s-%s (%s al %s) en %s', 
																$cargo['cargo'], 
																$cargo['dedicacion'], 
																date_format($fecha_desde, 'd-m-Y'), 
																date_format($fecha_hasta, 'd-m-Y'), 
																$cargo['dependencia']) . "</li>";
							
							}
						?>

						</ul>
				</li>
				<?php endif; ?>
			</ul>
		</p>
	</div>
</div>


<div id="contenedor_admisibilidad">
	
	<div id="direccion" class="seccion">
		<div class="titulo_seccion">Direcci�n</div>
		<table>
			<th>Apellido y Nombres</th>
			<th>Rol</th>
			<th>Max. Grado</th>
			<th>Cat. Incentivos</th>
			<th>Cat. Conicet</th>
			<th>Cargos vigentes</th>
			<th>CVAr</th>
			<?php foreach($datos['direccion'] as $directivo) : ?>
			<tr>
				<td><?php echo $directivo['apellido']; ?>, <?php echo $directivo['nombres']; ?></td>
				<td><?php echo $directivo['rol']; ?></td>
				<td><?php echo $directivo['nivel_academico']; ?></td>
				<td><?php echo $directivo['cat_incentivos_descripcion']; ?></td>
				<td><?php echo $directivo['cat_conicet']; ?></td>
				<td>
					<ul id="cargos">
						<?php foreach($directivo['cargos'] as $cargo) : ?>
							
							<li class="<?php echo (strtotime($cargo['fecha_hasta']) > time()) ? 'vigente': 'vencido'; ?>">
							<?php echo sprintf('%s (%s) en %s (hasta el %s)', 
								$cargo['cargo'], 
								$cargo['dedicacion'], 
								$cargo['dependencia'], 
								date('d/m/Y',strtotime($cargo['fecha_hasta']))); 
							?>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
				<td>
					<?php 
						$ubicacion= "/docum_personal/".$directivo['nro_documento']."/cvar.pdf";
						if(file_exists($ruta.$ubicacion)) : 
					?>
						<a target="_BLANK" href="<?php echo $url.$ubicacion; ?>">Ver CVAr</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
	
	<div id="detalles">
		<div id="documentacion" class="seccion">
			<div class="titulo_seccion">Documentaci�n</div>
			<?php $insc = array_merge($inscripcion['postulante'],$inscripcion['beca']); ?>
			<?php $ubicacion = sprintf('/docum_personal/%s/dni.pdf',$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
				<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">DNI</a>
			<?php endif; ?>
				
			<?php $ubicacion = sprintf('/docum_personal/%s/CUIL.pdf',$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
			<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">CUIL</a>
			<?php endif; ?>

			<?php $ubicacion = sprintf('/docum_personal/%s/cuil.pdf',$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
			<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">CUIL</a>
			<?php endif; ?>

			<?php $ubicacion = sprintf('/docum_personal/%s/Titulo Grado.pdf',$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
				<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">T�tulo de Grado</a>
			<?php endif; ?>
				
			<?php $ubicacion = sprintf('/becas/doc_por_convocatoria/%s/%s/%s/Cert. Analitico.pdf',$insc['id_convocatoria'],$insc['id_tipo_beca'],$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
			<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">Anal�tico</a>
			<?php endif; ?>

			<?php $ubic_posgr = sprintf('/becas/doc_por_convocatoria/%s/%s/%s/Insc. o Compromiso Posgrado.pdf',$insc['id_convocatoria'],$insc['id_tipo_beca'],$insc['nro_documento']);
			if(file_exists($ruta.$ubic_posgr)) : ?>
			<a class="" href="<?php echo $url.$ubic_posgr; ?>" target="_BLANK">Insc./Comp. Posgrado</a>
			<?php endif; ?>
			
			<?php $ubicacion = sprintf('/becas/doc_por_convocatoria/%s/%s/%s/Plan de Trabajo.pdf',$insc['id_convocatoria'],$insc['id_tipo_beca'],$insc['nro_documento']);
			if(file_exists($ruta.$ubicacion)) : ?>
			<a class="" href="<?php echo $url.$ubicacion; ?>" target="_BLANK">Plan de Trabajo</a>
			<?php endif; ?>
		</div>


		<div id="controles"  class="seccion">
			<div class="titulo_seccion">Controles</div>
			<p>
				Edad: <?php echo $edad_asp; ?> a�os.
			</p>
			<p class=<?php echo ( ! $ctrl_admisibilidad['mayor_dedicacion']) ? 'fallo' : '';?>>Mayor Dedicaci�n</p>
			<p class=<?php echo ( ! $ctrl_admisibilidad['grado_categoria'])  ? 'fallo' : '';?>>Grado/Categor�a</p>

			<?php if($detalles_tipo_beca['requiere_insc_posgrado'] == 'S') : ?>
				<p class="<?php echo ( ! file_exists($ruta.$ubic_posgr)) ? 'fallo' : '';  ?>">
					Inscripci�n o compromiso a un posgrado
				</p>
			<?php endif; ?>
			
			<?php if($detalles_tipo_beca['debe_adeudar_hasta']) : ?>
				<?php //echo $materias_adeuda; ?>
				<p class="<?php echo ( ! $materias_adeuda) ? 'fallo' : '';  ?>">
					Cantidad de materias que adeuda
					(<?php echo ($insc['materias_plan'] - $insc['materias_aprobadas']); ?> / 
					<?php echo $detalles_tipo_beca['debe_adeudar_hasta']; ?>)
				</p>
			<?php endif; ?>

			<?php $porcentaje_aprobacion = round($insc['materias_aprobadas'] / $insc['materias_plan'] * 100); ?>
			<p class="<?php echo ($porcentaje_aprobacion < 50) ? 'fallo' : ''; ?>">Materias Aprobadas: 
				<?php echo $insc['materias_aprobadas']; ?> / <?php echo $insc['materias_plan']; ?> ( <?php echo $porcentaje_aprobacion; ?>%)
			</p>
			
			<div class="centrado">Avales</div>
			<p class=<?php echo ( ! $avales['aval_director'])     ? 'fallo' : '';?>>Aval Director</p>
			<p class=<?php echo ( ! $avales['aval_dir_proyecto']) ? 'fallo' : '';?>>Aval Director del Proyecto</p>
			<p class=<?php echo ( ! $avales['aval_secretaria'])   ? 'fallo' : '';?>>Aval Secretar�a Investigaci�n</p>
			<p class=<?php echo ( ! $avales['aval_decanato'])     ? 'fallo' : '';?>>Aval Decanato / Dir. Instituto</p>
		</div>
		<div id="formulario" class="seccion">
			<div class="titulo_seccion">Admisibilidad</div>
			[dep id=form_admisibilidad]	
			<div style="text-align: center;">
				<a href="<?php echo $url; ?>/becas/estaticos/reglamento.pdf" target="_BLANK">Consultar reglamento</a>
			</div>
		</div>
	</div>
	<?php 
		$cats  = array(1=>'Categor�a I',2=>'Categor�a II',3=>'Categor�a III');
		$url   = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos');
		$ruta  = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
		$ruta_docum_personal = $url . '/docum_personal';

	?>
	<div class="seccion">
		<div class="titulo_seccion">Director de la beca: Calificaci�n Transitoria de Incentivos</div>
		<?php if ($datos['solic_trans_inc_dir']) : ?>
			<div class="categoria_solicitada">Categor�a solicitada: <?php echo $cats[$datos['solic_trans_inc_dir']['categoria']]; ?></div>
			<table class="tabla_solic_trans_inc_dir">
				<?php foreach ($datos['solic_trans_inc_dir']['docum'] as $docum): ?>
					<tr>
						<td>
							<?php echo $docum['requisito']; ?> 
						</td>
						<td>
							<a target="_BLANK" href="<?php echo $ruta_docum_personal . '/' . $datos['solic_trans_inc_dir']['nro_documento'] . '/categoria_transitoria_incentivos/' . $docum['id_convocatoria'] . '-' . $docum['id'];  ?>">Ver</a>
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		<?php else: ?>
			<div class="categoria_solicitada">No Solicitado</div>
		<?php endif; ?>
	</div>

	<div class="seccion">
		<div class="titulo_seccion">Co-Director de la beca: Calificaci�n Transitoria de Incentivos</div>
		<?php if ($datos['solic_trans_inc_codir']) : ?>
			<div class="categoria_solicitada">Categor�a solicitada: <?php echo $cats[$datos['solic_trans_inc_codir']['categoria']]; ?></div>
			<table class="tabla_solic_trans_inc_dir">
				<?php foreach ($datos['solic_trans_inc_codir']['docum'] as $docum): ?>
					<tr>
						<td>
							<?php echo $docum['requisito']; ?> 
						</td>
						<td>
							<a target="_BLANK" href="<?php echo $ruta_docum_personal . '/' . $datos['solic_trans_inc_codir']['nro_documento'] . '/categoria_transitoria_incentivos/' . $docum['id_convocatoria'] . '-' . $docum['id'];  ?>">Ver</a>
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		<?php else: ?>
			<div class="categoria_solicitada">No Solicitado</div>
		<?php endif; ?>
	</div>

	<div class="seccion">
		<div class="titulo_seccion">Sub-Director de la beca: Calificaci�n Transitoria de Incentivos</div>
		<?php if ($datos['solic_trans_inc_subdir']) : ?>
			<div class="categoria_solicitada">Categor�a solicitada: <?php echo $cats[$datos['solic_trans_inc_subdir']['categoria']]; ?></div>
			<table class="tabla_solic_trans_inc_dir">
				<?php foreach ($datos['solic_trans_inc_subdir']['docum'] as $docum): ?>
					<tr>
						<td>
							<?php echo $docum['requisito']; ?> 
						</td>
						<td>
							<a target="_BLANK" href="<?php echo $ruta_docum_personal . '/' . $datos['solic_trans_inc_subdir']['nro_documento'] . '/categoria_transitoria_incentivos/' . $docum['id_convocatoria'] . '-' . $docum['id'];  ?>">Ver</a>
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		<?php else: ?>
			<div class="categoria_solicitada">No Solicitado</div>
		<?php endif; ?>
	</div>

		
	
	<div id="requisitos">
		[dep id=ml_requisitos]		
	</div>
</div>