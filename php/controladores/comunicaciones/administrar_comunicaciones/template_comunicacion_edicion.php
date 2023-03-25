<fieldset style="margin-top: 8px; background-color:#F2F3FF">
	<legend style="font-size:1.4em;">Poster 
		<span style="font-size:10px;">(No debe completar estos dos campos. Los mismos serán asignados automáticamente al guardar la comunicación)</span>
	</legend> 
	[ef id=orden_poster]
	[ef id=id]
</fieldset>
<fieldset style="margin-top: 8px; background-color:#F2F3FF">
	<legend style="font-size:1.4em;">Comunicaci&oacute;n Cient&iacute;fica</legend>
	<p>[ef id=titulo]</p><p>[ef id=resumen]</p>
	
	<p>[ef id=extendido]</p>
	<?php if( ! (isset($datos['id']) && $datos['id']) ) : ?>
		<p style="font-size:1.1em;">Antes de poder subir su artículo extendido, debe guardar su comunicación. Aquí puede descargar la plantilla que le permitirá elaborar el extendido. Aunque la plantilla se encuentra en formato Word, el archivo que usted suba al sistema <b>debe estar en formato PDF</b>
		</p>
	<?php endif; ?>
	<?php if( ! isset($datos['estado']) || $datos['estado'] != 'C') : ?>
		<p style="text-align:center;">
			<a class="boton_enlace" href="/documentos/perm_docs/plantilla_comunicacion_extendido.docx" target="_BLANK">Descargar plantilla de artículo extendido</a>
		</p>
	<?php endif; ?>
	<p>Ingrese las palabras clave separadas por coma (por ejemplo: suelo,humedad,nordeste,nutrientes)</p>
	<p>[ef id=palabras_clave]</p>
	
	<p>Puede buscar a su director por apellido, nombres o número de documento:</p>
	<p>[ef id=nro_documento_dir]</p>
	<p>[ef id=nro_documento_codir]</p>

</fieldset>
<fieldset style="margin-top: 8px; background-color:#F2F3FF">
	<legend style="font-size:1.4em;">Detalles de la Beca</legend>
	<p>[ef id=sap_area_beca_id]</p>
	<p>[ef id=sap_tipo_beca_id]</p>
	<p>[ef id=resolucion]</p>
	<p>[ef id=periodo_desde][ef id=periodo_hasta]</p>
	<p>[ef id=proyecto_id]</p>
	<p>[ef id=sap_dependencia_id]</p>
</fieldset>

<p>[ef id=asistio_jornada]</p>
