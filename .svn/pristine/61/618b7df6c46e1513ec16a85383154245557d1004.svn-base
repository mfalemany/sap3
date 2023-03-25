<br>
<fieldset>
	<legend>Información Personal</legend>
	<table>
		<tr>
			<td>[ef id=nro_documento]</td>
			<td>[ef id=cuil]</td>
		</tr>
		<tr>
			<td>[ef id=apellido]</td>
			<td>[ef id=estado_civil]</td>
		</tr>
		<tr>
			<td>[ef id=nombres]</td>
			<td>[ef id=nacionalidad]</td>
		</tr>
		<tr>
			<td>[ef id=fecha_nac]</td>
			<td>[ef id=sexo]</td>
		</tr>
		<tr>
			<td>[ef id=celular]</td>
			<td>[ef id=mail]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=id_disciplina]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=id_nivel_academico]</td>
		</tr>
		<tr>
			<td>[ef id=id_localidad]</td>
			<td>[ef id=barrio]</td>
		</tr>
		<tr>
			<td>[ef id=domicilio_calle]</td>
			<td>[ef id=domicilio_numero]</td>
		</tr>
		<tr>	
			<td>[ef id=domicilio_piso]</td>
			<td>[ef id=domicilio_dpto]</td>
		</tr>
	</table>
</fieldset>
<br>

<fieldset>
	<legend>Documentación Personal</legend>
	<div style="color:red; font-size:1.2rem; text-align:center; padding: 3px;;">Para visualizar los documentos, debe guardarlos previamente (si no guarda los cambios el sistema le mostrará la última versión subida)</div>
	<table class="tabla_documentacion_personal">
		<tr>
			<td>[ef id=archivo_dni]</td>
			<td class="<?php echo ($datos['existe_dni']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['existe_dni']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'dni'); ?>">Ver DNI</a>
				<?php else: ?>
					-- No se ha cargado el DNI --
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>[ef id=archivo_cbu]</td>
			<td class="<?php echo ($datos['existe_cbu']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['existe_cbu']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'cbu'); ?>">Ver CBU</a>
				<?php else: ?>
					-- No se ha cargado el CBU --
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>[ef id=archivo_cuil]</td>
			<td class="<?php echo ($datos['existe_cuil']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['existe_cuil']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'cuil'); ?>">Ver CUIL</a>
				<?php else: ?>
					-- No se ha cargado el CUIL --
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<p style="background-color: #dc3545; color: #FFFF; padding: 10px; font-size: 1.3rem; font-weight: bold;">
					Aclaración importante sobre la convocatoria a becas: para los postulantes a becas <u>NO es obligatoria</u> la carga del CVAr. Solo los directores de beca y de proyectos deben actualizar este documento.
			 	</p>
			</td> 
		</tr>
		<tr>
			<td >[ef id=archivo_cvar]</td>
			<td class="<?php echo ($datos['existe_cvar']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['existe_cvar']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'cvar'); ?>">Ver CVAR</a>
				<?php else: ?>
					-- No se ha cargado el CVAR --
				<?php endif; ?>
			</td>
		</tr>
	</table>
</fieldset>
<br>