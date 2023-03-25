<?php extract($datos); ?>
<style>
	#explicacion{
	    background-color: #fffb89;
	    box-shadow: 0px 0px 5px 1px #AAA;
		color: #783232;
		font-size: 1.3rem;
	    font-weight: bold;
	    line-height: 2.5rem;
	    margin: 7px auto;
	    padding: 3px 10px;
	    word-wrap: break-word;
	    width: 93%;
	}
	#proyectos_control_condiciones{
	    align-items: flex-start;
	    box-sizing: border-box;
		display: flex;
	    flex-wrap: wrap;
	    flex-direction: row;
	    font-size: 1.3rem;
	    justify-content: space-evenly;
	    padding: 5px;
	}

	#proyectos_control_condiciones > div{
		border:  1px solid #777;
		margin: 5px;
		padding:  5px;
		width: 45%;
	}

	h3{
		background-color: #4d66ad;
		color: #FFF;
		margin: 0px;
		padding: 2px 0px 2px 10px;
		text-shadow: 1px 1px 1px black;
	}
	
	li{
		padding-right: 34px;
	}

	li.cumplido{
		list-style-image: url('/toba_2.7/img/aplicar.png');
	}

	li.no_cumplido{
		list-style-image: url('/toba_2.7/img//error.png');
	}
</style>
<div id="explicacion">
	Esta pestaña le ofrece la posibilidad de controlar si la información cargada hasta el momento cumple con las condiciones requeridas para la presentación definitiva de su proyecto de investigación.
</div>
<div id="proyectos_control_condiciones">
	<div>
		<h3>Dirección del proyecto</h3>
		<ul id="errores_curriculums">
			<?php if (count($direccion)) : ?>
				<?php foreach ($direccion as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">Los integrantes que conforman la dirección del proyecto, cumplen con todas las condiciones exigidas</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Campos obligatorios</h3>
		<ul id="errores_campos_obligatorios">
			<?php if (count($campos_obligatorios)) : ?>
				<?php foreach ($campos_obligatorios as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">Todos los campos obligatorios están completos</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Cargos docentes</h3>
		<ul id="errores_campos_obligatorios">
			<?php if (count($cargos_docentes)) : ?>
				<?php foreach ($cargos_docentes as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">Todos los cargos docentes fueron validados con éxito</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Información de Recursos Humanos</h3>
		<ul id="errores_registros_auxiliares">
			<?php if (count($registros_auxiliares)) : ?>
				<?php foreach ($registros_auxiliares as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">En la pestaña 'Recursos Humanos', la información está completa</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Integrantes de proyectos</h3>
		<ul id="errores_integrantes_proyectos">
			<?php if (count($integrantes_proyectos)) : ?>
				<?php foreach ($integrantes_proyectos as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">Ningún integrante incumple las condiciones de pertenencia a proyectos de investigación</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Funciones de los integrantes</h3>
		<ul id="errores_validacion_perfiles">
			<?php if (count($funciones_integrantes)) : ?>
				<?php foreach ($funciones_integrantes as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">El proyecto incluye integrantes con todas las funciones requeridas</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Presentación de currículos</h3>
		<ul id="errores_curriculums">
			<?php if (count($existencia_curriculum)) : ?>
				<?php foreach ($existencia_curriculum as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">Todos los integrantes del proyecto tienen su CV cargado</li>
			<?php endif; ?>
		</ul>
	</div>
	<div>
		<h3>Integrantes</h3>
		<ul id="errores_integrantes">
			<?php if (count($integrantes)) : ?>
				<?php foreach ($integrantes as $error): ?>
					<li class="no_cumplido">
						<?php echo $error; ?>
					</li>
				<?php endforeach ?>
			<?php else : ?>
				<li class="cumplido">El proyecto está integrado correctamente</li>
			<?php endif; ?>
		</ul>
	</div>
</div>