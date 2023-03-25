<style>
	#contenedor_inf{
		margin: 10px auto;
		width:75%; 
	}
	.cuadro_warning{
		 background-color: #fdff80; 
		 border: 2px solid #fb6565;
		 font-size: 1.4em; 
		 padding: 5px 10px; 
		 text-align: justify; 
	}
	.cuadro_ref{
		border: 1px solid #6bc0d8;
		font-size:  1em;
		padding:  5px 10px;
	}
	.cuadro_ref p{
		margin: 2px;
	}
	#detalles_proyecto{
		font-size: 1.2em;
		margin-top: 10px;
	}
	#detalles_proyecto .titulo{
		font-weight: bold;
		padding:  2px 5px;
		text-decoration: underline;
	}
	#detalles_proyecto table tr{
		line-height: 2.2rem;
	}
	#eval_integrantes select{
		width: 90%;
	}
	#aclaracion_pie{
		text-align: right;
		font-size: 1.2rem;
		color: #ca1d1d;
		font-weight: bold;
	}
</style>

<div id="contenedor_inf">
	<div class="separador_seccion_form">Informe de Actividades <?php echo $datos['informacion_anio']; ?></div>
	<div class="cuadro_warning">
		<p>La información consignada en este Informe tiene el carácter de Declaración Jurada. Autorizo su verificación cuando la Universidad o la Secretaría General de Ciencia y Técnica lo consideren pertinente.</p>
		<div style="font-size:1.2rem;">[dep id=form_acuerdo]</div>
	</div>	
	<p class='aclaracion_form'>Información del proyecto</p>
	<div id="detalles_proyecto">
		<table>
			<tr>
				<td class="titulo">Proyecto: </td>
				<td colspan="3"><?php echo $datos['proyecto_codigo']; ?></td>
			</tr>
			<tr>
				<td class="titulo">Denominación: </td>
				<td colspan="3"><?php echo $datos['proyecto_denominacion']; ?></td>
			</tr>
			<tr>
				<td class="titulo">Fecha inicio: </td>
				<td><?php echo $datos['proyecto_inicio']; ?></td>
				<td class="titulo" style="text-align:right;">Fecha finalización:</td>
				<td><?php echo $datos['proyecto_fin']; ?></td>
			</tr>
		</table>
	</div>
	<div>
		<p class='aclaracion_form'>Tipo de informe que se presentará</p>
		<div class="cuadro_ref">
			<p style="    background-color: #bbe2ec; color: #37616d; padding:2px 0px 2px 10px;">Referencias</p>
			<p><b>Año 1:</b> Primer informe de seguimiento</p>
			<p><b>Año 2:</b> Primer informe de avance </p>
			<p><b>Año 3:</b> Segundo informe de seguimiento</p>
			<p><b>Año 4:</b> Informe final (si el proyecto fue prorrogado se presenta informe de segundo avance)</p>
			<p><b>Año 5:</b> Informe final</p>
		</div>
		<p>[dep id=form_pdts]</p>
	</div>

	<div class="separador_seccion_form">Evaluación de Integrantes</div>
	<div id="eval_integrantes">
		[dep id=ml_integ_pdts]
	</div>
	<?php if( ! $datos['es_carga_inicial']) : ?>
		<div id="aclaracion_pie">* Al presionar Guardar, se habilitarán los ítems a cargar del informe.</div>	
	<?php endif; ?>
</div>
