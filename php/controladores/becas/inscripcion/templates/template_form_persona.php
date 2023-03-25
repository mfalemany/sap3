
<style type="text/css">
	.contenedor_enlace{
		background-color: #5e92c7;
	    text-align: center;
	    vertical-align: middle !important;
	}
	.contenedor_enlace a{
		color: #FFFFFF;
		cursor: pointer;
		display: inline-block;
		font-size: 1.4em;
		font-weight: bold;
		text-decoration: none;
		text-shadow: 1px 1px 1px #222;
		width: 100%;
	}
	.tabla_documentacion_personal{
		max-width: 70%;
		margin:  20px auto;
	}
</style>
<br>
<fieldset>
	<legend>Información Personal</legend>
	<table style="max-width: 70%;">
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
			<td>[ef id=sexo]</td>
		</tr>
		<tr>
			<td>[ef id=fecha_nac]</td>
			<td>[ef id=mail]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=id_disciplina]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=id_nivel_academico]</td>
		</tr>
		<tr>
			<td>[ef id=barrio]</td>
			<td>[ef id=celular]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=nacionalidad]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=id_localidad]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=domicilio_calle]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=domicilio_numero]</td>
		</tr>
		<tr>	
			<td colspan="2">[ef id=domicilio_piso]</td>
		</tr>
		<tr>
			<td colspan="2">[ef id=domicilio_dpto]</td>
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
			<td class="<?php echo ($datos['archivo_dni']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['archivo_dni']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'dni'); ?>">Ver DNI</a>
				<?php else: ?>
					-- No se ha cargado el DNI --
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>[ef id=archivo_cuil]</td>
			<td class="<?php echo ($datos['archivo_cuil']) ? 'contenedor_enlace' : '';?> centrado">
				<?php if ($datos['archivo_cuil']) : ?>
				<a target="_BLANK" href="<?php echo toba::consulta_php('helper_archivos')->get_path_documento_personal($datos['nro_documento'], 'cuil'); ?>">Ver CUIL</a>
				<?php else: ?>
					-- No se ha cargado el CUIL --
				<?php endif; ?>
			</td>
		</tr>
	</table>
</fieldset>
<br>