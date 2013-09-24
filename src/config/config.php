<?php
/**
 * @package Terion/Modularity
 * @author Volodimyr Terion Kornilov <mail@terion.name>
 * @copyright Copyright (c) 2013 by Terion
 * @license MIT
 * @version 1.0
 * @access public
 */

return array(
	// Set the path to modules directory
	'modules_directory' => app_path('modules'),

	// Define if to use DB for modules management
	'use_db' => false,

	// If 'use_db' is set to false, list of enabled modules will be fetched from 'enabled_modules'
	// Set it to empty array to enable all modules
	// Set it to false to disable all modules
	'enabled_modules' => array(),

	// If 'strict_mode' is set to true, absence of a module, listed as enabled, will generate an exception
	'strict_mode' => true
);