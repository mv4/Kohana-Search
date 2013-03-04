<?php defined('SYSPATH') or die('No direct script access.');
// Load phpMorphy
if ($path = Kohana::find_file('vendor/phpmorphy-0.3.7/src', 'common')) {
            require_once 'vendor/phpmorphy-0.3.7/src/common.php';
}
// Load Zend's Autoloader
if ($path = Kohana::find_file('vendor/Zend/Loader', 'Autoloader')){
	ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(dirname(dirname($path))));
	require_once 'vendor/Zend/Loader/Autoloader.php';
	Zend_Loader_Autoloader::getInstance();
}


