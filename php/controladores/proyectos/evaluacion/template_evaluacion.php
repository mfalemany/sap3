<style type="text/css">
	
</style>
<?php //ei_arbol($datos['integrantes']); ?>
<div id="evaluacion_proyecto">
	<div id="titulo_proyecto">
		<h1><?php echo $datos['titulo_proyecto']; ?></h1>	
	</div>
	<div id="cuerpo_proyecto">
		<table>
			<tr>
				<td><b>Código de <?php echo $datos['tipo_proyecto']; ?>:</b></td>
				<td><?php echo $datos['codigo_proyecto']; ?> <span style="font-size: 0.6em;">(ID: <?php echo $datos['id_proyecto']; ?>)</span></td>
				<td><b>Vigencia:</b> <?php echo $datos['fecha_desde']; ?> al <?php echo $datos['fecha_hasta']; ?></td>
			</tr>
			<tr>
				<td><b>Entidad Financiadora:</b></td>
				<td colspan=2><?php echo $datos['entidad_financiadora']; ?></td>
			</tr>
		</table>
		<div></div>
		<?php if($datos['tipo_proyecto'] != 'Programa'): ?>
			
			<table id="integrantes_proyecto">
				<caption>INTEGRANTES DEL <span style="text-transform:uppercase;"><?php echo $datos['tipo_proyecto']; ?></span></caption>
				<tr>
					<td class="cabecera_tabla">Apellido y Nombre</td>
					<td class="cabecera_tabla">Rol</td>
					<td class="cabecera_tabla">Horas dedicación</td>
					<!-- La columna del CVAr solo se muestra en proyectos nuevos, no en informes -->
					<?php if($datos['estado'] == 'nuevo') : ?>
					<td class="cabecera_tabla">CV</td>
					<?php endif; ?>
				</tr>

				<?php $ruta = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('ruta_base_documentos');
					$url  = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('url_base_documentos'); 
					foreach($datos['integrantes'] as $integrante): ?>
					<tr>
						<td><?php echo $integrante['apellido'].", ".$integrante['nombres']; ?></td>
						<td class="centrado"><?php echo $integrante['funcion']; ?></td>
						<td class="centrado"><?php echo $integrante['horas_dedicacion_desc']; ?></td>
						<!-- La columna del CVAr solo se muestra en proyectos nuevos, no en informes -->
						<?php if($datos['estado'] == 'nuevo') : ?>
						<td class="centrado">
							<?php $archivo = "/docum_personal/".$integrante['nro_documento']."/cvar.pdf"; ?>
							
							<?php if (file_exists($ruta.$archivo)): ?>
								<a href="<?php echo $url.$archivo; ?>" target="_BLANK">Ver</a>
							<?php endif; ?>	
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</table>

		<?php endif; ?>


<!-- 		<?php if( ! (isset($datos['contenido']) && $datos['contenido']) ) : ?>
			<div id="archivo">
				<a href="<?php echo $datos['archivo_proyecto']; ?>" target="_BLANK">Descargar contenido del <?php echo $datos['tipo_proyecto']; ?></a>
			</div>
		<?php endif; ?> -->

		
		<div class="form_evaluacion_proyecto">
			<div class='importante'>Los puntajes de cada ítem van desde 0 a 20 puntos.</div>
			<?php echo $datos['formulario_evaluacion']; ?>
			<div id="puntaje_total_contenedor">
				<div>Puntaje Total: <span id="puntaje_total_numero"></span> (<span id="puntaje_total_descripcion"></span>)
				</div>
				<div style="font-size: 0.6em; margin-top:15px; width: 50%; text-align: left;">El puntaje total del proyecto tiene un máximo en 100 pts., valorándose al proyecto con el resultado de dividir dicho puntaje en 10 y redondeando a un número entero:
					<ul style="text-align: left;">
						<li>1 - 5: No aprobado</li>
						<li>6 - 7: Aprobado - Bueno</li>
						<li>8 - 9: Aprobado - Muy bueno</li>
						<li>10: Aprobado - Excelente</li>
					</ul>

				</div>
			</div>
		</div>
	</div>
	

</div>