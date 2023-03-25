<?php

class helper
{
	/**
	 * Ordena los elementos de un array tomando como referencia la columna recibida como argumento
	 * @param  array  $array              
	 * @param  string $columna_ordenacion 
	 * @return mixed                      
	 */
	function ordenar_array($array, $columna_ordenacion)
	{
		if ( ! is_array($array) ) return false;

		usort($array, function($a, $b) use ($columna_ordenacion){
			if ($a[$columna_ordenacion] > $b[$columna_ordenacion]) return 1;
			if ($a[$columna_ordenacion] == $b[$columna_ordenacion]) return 0;
			if ($a[$columna_ordenacion] < $b[$columna_ordenacion]) return -1;
		});

		return $array;
	}

	public function utf8_decode_recursive_array($entrada)
	{
		if (!is_array($entrada)) {
			return false;
		}

		$utf_decoded = [];
		
		foreach ($entrada as $clave => $valor) {
			if (is_string($valor) || is_numeric($valor)) {
				$utf_decoded[$clave] = utf8_decode($valor);
			} else {
				$utf_decoded[$clave] = $this->utf8_decode_recursive_array($valor);
			}
		}

		return $utf_decoded;
	}

	function quitar_acentos($string)
	{
		if ( ! is_string($string)) {
			return $string;
		}
		$originales = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú'];
		$reemplazos = ['a','e','i','o','u','A','E','I','O','U'];
		return str_replace($originales,$reemplazos,$string);
	}
}

?>