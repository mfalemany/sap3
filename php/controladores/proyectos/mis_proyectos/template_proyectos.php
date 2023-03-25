<style type="text/css">
	.evaluacion{
		margin-bottom: 30px;
	}
	.evaluacion table{
		width: 100%;
	}
	.evaluacion table caption{
		background-color: #e2e2e2;
		color: #000;
		font-size: 2.1em;
		font-weight: bold;
		padding: 10px 0px 10px 0px;
		text-shadow: 1px 1px 1px #d8d8d8;

	}
	.evaluacion table tr{

	}
	.evaluacion table th{
		font-size: 1.2em;
		background-color: #575b97;
		color:#FFF;
		text-align: center;
	}
	.evaluacion table tr td{
		border-collapse: collapse;
		box-sizing: border-box;
		border-bottom: 1px solid #222;
		font-size: 1.3em;
	}
	.evaluacion table tr td:nth-child(1){
		text-align: center;
		font-weight: bold;
	}
	.evaluacion table tr:nth-child(even){
		background-color: #e3e3fd;
	}
	.evaluacion table tfoot tr td{
		background-color: #da7373;;
		color:#FFF;
		font-weight: bold;
		text-align: center;
		padding:5px;
		font-size: 1.6em;
		text-shadow: 1px 1px 1px black;
		


	}


</style>
<?php 
$resultados = array('M'=>'Aprobado - Muy Bueno','B'=>'Aprobado - Bueno','E'=>'Aprobado - Excelente','N'=>'No aprobado','A'=>'Aprobado'); 	
?>

<div id="contenedor_evaluaciones">
	<?php foreach($datos as $key => $evaluacion): ?>
		<?php //ei_arbol($datos) ?>
		<div class='evaluacion'>
			<table>
				<?php $fecha_eval = strtotime($evaluacion['fecha_eval']); ?>
				<caption>
					<?php echo ($this->soy_admin()) 
						? $evaluacion['evaluador'] . " (DNI: {$evaluacion['nro_documento_evaluador']})" 
						: "Evaluaci�n N� " . ($key + 1); ?> 
				</caption>
				<th width="20%">Concepto</th>
				<th width=80%>Devoluci�n/Puntaje</th>
				<?php 
					$instancia = $evaluacion['instancia'];
					$tipo = ($evaluacion['tipo'] == '0') ? 'pi' : 'pdts';
					$metodo = $tipo.'_'.$instancia;
					$metodo($evaluacion);
				?>
				<?php if($instancia == 'inicial'): ?>
					<?php $color = ($evaluacion['result_final_evaluacion'] == 'N') ? "#dc0000": "#00c040";?>
					<tfoot>
						<tr><td colspan=2 style="background-color: <?php echo $color; ?>">Resultado final de la evaluaci�n: <?php echo $resultados[$evaluacion['result_final_evaluacion']];?></td></tr>
					</tfoot>
				<?php else: ?>
					<?php $color = ($evaluacion['satisfactorio'] == 'S') ? "#00c040" : "#dc0000";?>
					<tfoot>
						<tr><td colspan=2 style="background-color: <?php echo $color; ?>"><?php echo ($evaluacion['satisfactorio'] == 'S') ? 'Satisfactorio' : 'No Satisfactorio' ;?></td></tr>
					</tfoot>
				<?php endif; ?>
			</table>

			
		</div>
	<?php endforeach; ?>
</div>


<?php function pi_inicial($evaluacion){ ?>
	<tr>
		<td>Contenido tecnol�gico-Cient�fico</td>
		<td><?php echo $evaluacion['cont_tec_cientif_punt']; ?> puntos.</td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['cont_tec_cientif_justif']; ?></td>
	</tr>
	<tr>
		<td>Director/Co-Director</td>
		<td><?php echo $evaluacion['dir_codir_punt']; ?> puntos.</td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['dir_codir_justif']; ?></td>
	</tr>
	<tr>
		<td>Conformaci�n del Grupo</td>
		<td><?php echo $evaluacion['conf_grupo_punt']; ?> puntos.</td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['conf_grupo_justif']; ?></td>
	</tr>
	<tr>
		<td>Factibilidad</td>
		<td><?php echo $evaluacion['factibilidad_punt']; ?> puntos.</td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['factibilidad_justif']; ?></td>
	</tr>
	<tr>
		<td>Resultados Esperados</td>
		<td><?php echo $evaluacion['result_esp_punt']; ?> puntos.</td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['result_esp_justif']; ?></td>
	</tr>
	<tr>
		<td>Observaciones generales</td>
		<td><?php echo $evaluacion['observaciones']; ?></td>
	</tr>
<?php } ?>

<?php function pdts_inicial($evaluacion){ ?>
	<?php 
		$resultados = array('M'=>'Aprobado - Muy Bueno','B'=>'Aprobado - Bueno','E'=>'Aprobado - Excelente','N'=>'No aprobado','A'=>'Aprobado'); 
	?>
	<tr>
		<td>Novedad/Originalidad en el conocimiento</td>
		<td><?php echo $resultados[$evaluacion['nov_orig_conc_punt']]; ?> </td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['nov_orig_conc_justif']; ?></td>
	</tr>
	<tr>
		<td>Relevancia</td>
		<td><?php echo $resultados[$evaluacion['relevancia_punt']]; ?> </td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['relevancia_justif']; ?></td>
	</tr>
	<tr>
		<td>Demanda</td>
		<td><?php echo  $resultados[$evaluacion['demanda_punt']]; ?> </td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['demanda_justif']; ?></td>
	</tr>
	<tr>
		<td>Factibilidad T�cnica</td>
		<td><?php echo $resultados[$evaluacion['factib_tecnica_punt']]; ?> </td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['factib_tecnica_justif']; ?></td>
	</tr>
	<tr>
		<td>Factibilidad Econ�mica</td>
		<td><?php echo  $resultados[$evaluacion['factib_econom_punt']]; ?> </td>
	</tr>
	<tr>
		<td>Justificaci�n</td>
		<td><?php echo $evaluacion['factib_econom_justif']; ?></td>
	</tr>
	<tr>
		<td>Observaciones generales</td>
		<td><?php echo $evaluacion['observaciones']; ?></td>
	</tr>
<?php } ?>

<?php function pi_informe($evaluacion){ ?>
	<tr>
		<td>Producci�n</td>
		<td><?php echo $evaluacion['produccion']; ?> </td>
	</tr>
	<tr>
		<td>Transferencia y Divulgaci�n</td>
		<td><?php echo $evaluacion['transf_divulgacion']; ?></td>
	</tr>
	<tr>
		<td>Formaci�n de Recursos Humanos</td>
		<td><?php echo $evaluacion['form_rec_hum']; ?> </td>
	</tr>
	<tr>
		<td>Satisfactorio</td>
		<td><?php echo ($evaluacion['satisfactorio'] == 'S') ? 'Si' : 'No'; ?></td>
	</tr>
<?php } ?>

<?php function pdts_informe($evaluacion){ ?>
	<tr>
		<td>Avance del desarrollo</td>
		<td><?php echo $evaluacion['avance_desarrollo']; ?> </td>
	</tr>
	<tr>
		<td>Producci�n</td>
		<td><?php echo $evaluacion['produccion']; ?> </td>
	</tr>
	<tr>
		<td>Transferencia y Divulgaci�n</td>
		<td><?php echo $evaluacion['transf_divulgacion']; ?></td>
	</tr>
	<tr>
		<td>Formaci�n de Recursos Humanos</td>
		<td><?php echo $evaluacion['form_rec_hum']; ?> </td>
	</tr>
	<tr>
		<td>Satisfactorio</td>
		<td><?php echo ($evaluacion['satisfactorio'] == 'S') ? 'Si' : 'No'; ?></td>
	</tr>
<?php } ?>