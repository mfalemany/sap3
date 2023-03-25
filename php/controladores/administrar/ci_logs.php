<?php
class ci_logs extends sap_ci
{
	protected $s__log;
	//-----------------------------------------------------------------------------------
	//---- cu_eventos_log ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/*function conf(){
		$cuils = ['20265260951','20121258596','20314597215','23223208444','20262351956','20236856985','20247406191','27137192298','27269692052','27178086214','27235699112','20266802103','20245577215','20323296821','27204489810','27233976836','27201833715','27246760557','23284766334','27241992816','27123674567','20295714981','20283354726','20139047932','20225482404','27243744674','20286662480','20148691585','27178326754'];
		foreach ($cuils as $cuil) {
			$cvar_getter = new Cvar_getter($cuil);
			$datos = $cvar_getter->get_datos('array');
			echo "<br>";
			echo strtoupper($datos['datosPersonales']['identificacion']['nombre'] . ' ' .$datos['datosPersonales']['identificacion']['apellido']);
			echo "<br>";
			echo utf8_decode($datos['datosExperticia']['resumen']);
		}
	}*/

	function conf__cu_eventos_log(sap_ei_cuadro $cuadro)
	{
		if (isset($this->s__log['log'])) {
			$ruta_log = toba::consulta_php('helper_archivos')->ruta_base() . 'logs/' . $this->s__log['log'];
			
			if(!file_exists($ruta_log)) {
				return;
			}
			
			$datos = file($ruta_log);
			if ($datos) {
				$logs  = [];
				foreach ($datos as $linea_log) {
					$evento = @iconv(mb_detect_encoding($linea_log),'ISO-8859-1',substr($linea_log,21));
					$logs[] = [
						'fecha_hora' => substr($linea_log, 0, 19), 
						'evento'     => ($evento !== false) ? $evento : substr($linea_log,21)
					];
				}
				$cuadro->set_datos($logs);
			}
			
		}
	}

	function evt__cu_eventos_log__seleccion($seleccion)
	{
	}

	function conf_evt__cu_eventos_log__seleccion(toba_evento_usuario $evento, $fila)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_filtro ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_filtro(sap_ei_formulario $form)
	{
		if (isset($this->s__log['log'])) {
			//Solo el campo log se guarda... los otros filtros se manejan con JS
			$form->set_datos(['log' => $this->s__log['log']]);
		}
	}

	function evt__form_filtro__ver_log($datos)
	{
		$this->s__log = $datos;
	}

	public function extender_objeto_js()
	{
		echo "
		var ef_filtro = {$this->get_objeto_js()}.dep('form_filtro').ef('filtro');
		var cuadro = {$this->get_objeto_js()}.dep('cu_eventos_log');
		var filtro = ef_filtro.input();
		filtro.addEventListener('keyup',function(e){
			var tabla = cuadro.cuerpo();
			var filas = tabla.querySelectorAll('tr');
			filas.forEach(function(fila){
				console.log(fila.textContent.indexOf(ef_filtro.get_estado()));
				if (fila.textContent.toLowerCase().indexOf(ef_filtro.get_estado().toLowerCase()) === -1) {
					fila.style.display = 'none';
				} else {
					fila.style.display = 'table-row';
				}
			});
		})";
	}

}
?>