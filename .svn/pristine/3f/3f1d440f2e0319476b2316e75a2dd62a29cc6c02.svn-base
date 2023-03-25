<style type="text/css">
	.referencias th{
		 text-align: center; 
		 background-color: #5f63a4; 
		 color:#FFF;
		 min-width: 100px;
	}
	.referencias tr td, .referencias tr th{
		border:  1px solid black;
		padding: 3px;
	}
	.referencias tr:nth-child(odd){
		background-color: #DEDEDE;
	}

	.referencias{
		 border-collapse: collapse;

	}
</style>
<table align="center" border="1" cellpadding="1" cellspacing="1" style="width: 90%">
	<caption>
		<span style="font-size:16px;"><strong>Formato del archivo que debe cargar</strong></span></caption>
	<tbody>
		<tr>
			<td style="text-align: center; background-color: rgb(255, 255, 102);">
				<strong><span style="font-size:16px;">nro_documento</span></strong></td>
			<td style="text-align: center; background-color: rgb(255, 255, 102);">
				<strong><span style="font-size:16px;">nro_documento_dir</span></strong></td>
			<td style="text-align: center;">
				<strong><span style="font-size:16px;">nro_documento_codir</span></strong></td>
			<td style="text-align: center;">
				<strong><span style="font-size:16px;">area_conocimiento</span></strong></td>
			<td style="text-align: center;">
				<strong><span style="font-size:16px;">dependencia</span></strong></td>
			<td style="text-align: center;">
				<strong><span style="font-size:16px;">condicion</span></strong></td>
			<td style="text-align: center;">
				<strong><span style="font-size:16px;">titulo_plan_beca</span></strong></td>
		</tr>
		<tr>
			<td style="text-align: center;">
				<span style="font-size:16px;">32405039</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">17248342</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">12125859</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">3</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">14</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">titular</span></td>
			<td>
				<span style="font-size:16px; font-size:0.9em;">El uso del lenguaje como instrumento central de las pol&iacute;ticas p&uacute;blicas</span></td>
		</tr>
		<tr>
			<td style="text-align: center;">
				<span style="font-size:16px;">368686042</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">16320605</span></td>
			<td style="text-align: center;">&nbsp;
				</td>
			<td style="text-align: center;">
				<span style="font-size:16px;">7</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">9</span></td>
			<td style="text-align: center;">
				<span style="font-size:16px;">suplente</span></td>
			<td>
				<span style="font-size:16px; font-size:0.9em;">Estudio de suelos en la regi&oacute;n norte de Argentina</span></td>
		</tr>
	</tbody>
</table>
<div style="font-size:15px; background-color:white;">
	<p>* Los campos resaltados en color amarillo son obligatorios (el archivo debe contener esa columna, y sus valores no pueden estar vacíos).</p>
	<p>Deben respetarse los nombres de las columnas, tal como se indica en el cuadro de arriba. El orden de las columnas puede variar, pero no sus nombres (el sistema utiliza las cabeceras de columnas para identificar cada campo). La primera fila del archivo es considerada como cabeceras.</p>
	<p>El formato del archivo cargado debe ser .CSV (puede abrir cualquier Excel, y luego hacer click en "Guardar Como" y elegir el formato CSV).</p>
	<p>Los números de documento no deben contener puntos (aunque si los contiene, el sistema los eliminará).</p>
</div>

<h3 class="separador_seccion_form">Referencias para algunas columnas:</h3>
<div style="display:flex; justify-content: center;">
	<div style="display:block; width: 45%; margin: 10px 0px; ">
	<h4>Facultades/Institutos</h4>
	<p>Utilice los siguientes numeros para identificar las facultades en su archivo:</p>
	<table class="referencias">
		<th>Facultad/Instituto</th>
		<th>Identificador</th>
	<?php foreach($datos['ids_facultades'] as $dependencia) : ?>
		<tr>
			<td><?php echo $dependencia['nombre']; ?></td>
			<td class="centrado"><?php echo $dependencia['id']; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
	</div>
	<div style="display:block; width: 45%; margin: 10px 0px; ">
	<h4>Áreas de Conocimiento</h4>
	<p>Utilice los siguientes numeros para identificar las áreas de conocimiento en su archivo:</p>
	<table class="referencias">
		<th class="centrado">Área de Conocimiento</th><th>Identificador</th>
	<?php foreach($datos['areas_conocim'] as $area) : ?>
		<tr>
			<td><?php echo $area['nombre']; ?></td>
			<td class="centrado"><?php echo $area['id']; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
	</div>
</div>
<p>[dep id=form_carga_masiva]</p>