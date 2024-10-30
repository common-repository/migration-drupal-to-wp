<?php 	if ( ! defined( 'ABSPATH' ) ) exit;
	//Funcion para imprimir por pantalla, uso para el mensaje de error y los resultados que se obtienen como por ejemplo la conexion a las BD
	function migration_drupal_to_wp_print_container_12($variable){
	   echo "<div class=\"container\"><div class=\"col-md-12\"><center>".$variable."</center></div></div>";
	}

	//dump( $conection_wordpress, "Justo despues de montar la query");

	//Funcion de depuracion
	function migration_drupal_to_wp_dump($shit, $msg=""){
		echo "<pre>";
		print_r($shit);
		echo "</pre><script>console.log('PHP: $msg ".json_encode($shit)."')</script>";
	}
?>
