<?php
function config_ci($item, $index = ''){

    $config = get_instance()->config;
	if( !is_object($config) ){
		$config =  $config['Config'];
	}
	return $config->item($item, $index);
}