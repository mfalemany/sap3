<style>
	#becarios table{
		border-collapse: collapse;
		width: 100%;
	}
	#becarios table tr,th{
		box-sizing: border-box;
		padding: 4px;
	}

	#becarios table tr td, th{
		border: 1px solid grey;
	}
	#becarios table tr td{
		box-sizing: border-box;
		min-height: 28px;
		padding: 0px 4px;
		
	}
	#becarios table th{
		background: linear-gradient(180deg, #27588a 0%, #4d80b5 45%, #27588a 100%);
		color: #FFF;
		text-align: center;
		text-shadow: 1px 1px 1px #222;;
	}

	#becarios table tr td:nth-child(1){
		width:20%; 
		text-align:left; 
		color:#A00; 
		font-weight: bold;
	}
	#becarios table tr td:nth-child(2){
		width:15%; 
		text-align:center;
	}
	#becarios table tr td:nth-child(3){
		margin: 3px;
		padding: 2px 5px;
		width:10%; 
		text-align:center;
	}
	#becarios table tr td:nth-child(4){
		margin: 3px;
		padding: 2px 5px;
		width:5%; 
		text-align:center;
	}
	#becarios table tr td:nth-child(5){
		font-size: 1.2em;
		margin: 3px;
		padding: 4px 10px;
		width:50%; 
		text-align:justify;
	}
	#aclaracion_pie{
		text-align: center;
		margin-top: 10px;
		color: #ca1d1d;
		font-weight: bold;
	}
</style>

<div class="separador_seccion_form">
	Becarios integrantes del proyecto (gestionados por la SGCyT - UNNE)
</div>
<div id="becarios">
	<table>
		<th>Becario</th><th>Dirección</th><th>Tipo de Beca</th><th>Duración</th><th>Título de Plan de Beca</th>
		
		<?php if(count($datos['becarios'])) : ?>
		
			<?php foreach($datos['becarios'] as $beca) : ?>
				<?php 
					$duracion = (new Datetime($beca['fecha_hasta']))->format('Y') - (new Datetime($beca['fecha_desde']))->format('Y'); 
					$direccion = 'Dir.: '.$beca['director'];
					$direccion .= ($beca['codirector']) ? ' / Codir.: '. $beca['codirector'] : ''; 
					$direccion .= ($beca['subdirector']) ? ' / Subdir.: '.$beca['subdirector'] : ''; 
				?>
				
				<tr>
					<td><?php echo $beca['becario']; ?></td>
					<td><?php echo $direccion; ?></td>
					<td><?php echo $beca['tipo_beca']; ?></td>
					<td><?php echo $duracion; ?> <?php echo ($duracion == 1) ? 'Año' : 'Años'; ?></td>
					<td><?php echo $beca['titulo_plan_beca']; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="5" style="text-align:center; font-size:bold; color:#555; padding: 5px;">No se incorporaron becarios a este proyecto</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php $mail = toba::consulta_php('co_tablas_basicas')->get_parametro_conf('direccion_mail_rrhh'); ?>
	<div id="aclaracion_pie">En caso de encontrar algún error u omisión en el listado de becarios, por favor comunicarse a la dirección <a href="mailto:<?php echo $mail; ?>?subject=<?php echo urlencode('Becarios del Proyecto ') . $datos['proyecto_codigo']; ?>" target="_BLANK"><?php echo $mail; ?></a></div>
</div>

<div class="separador_seccion_form">
	Becarios externos formados en el marco de este proyecto
</div>
<p>[dep id=cu_becarios_externos]</p>
