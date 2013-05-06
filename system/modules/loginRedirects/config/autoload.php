<?php

/**
 * loginRedirects for Contao Open Source CMS
 *
 * Copyright (C) 2013 Kirsten Roschanski
 * Copyright (C) 2011 MEN AT WORK <http://www.men-at-work.de/> 
 *
 * @package    loginRedirects
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MENATWORK',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Library
	'MENATWORK\LoginRedirects\LoginRedirects'         => 'system/modules/loginRedirects/library/LoginRedirects/LoginRedirects.php',
	'MENATWORK\LoginRedirects\LoginRedirectsCallback' => 'system/modules/loginRedirects/library/LoginRedirects/LoginRedirectsCallback.php',
));
