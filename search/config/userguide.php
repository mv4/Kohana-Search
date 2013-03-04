<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// Leave this alone
	'modules' => array(

		// This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
		'search' => array(

			// Whether this modules userguide pages should be shown
			'enabled' => TRUE,

			// The name that should show up on the userguide index page
			'name' => 'Search',

			// A short description of this module, shown on the index page
			'description' => 'Морфологический поиск. Kohana 3.2 Zend Lucene Search, PhpMorphy 0.3.7',

			// Copyright message, shown in the footer for this module
			'copyright' => '&copy; 2008–2011 Kohana Team',
		)
	)
);
