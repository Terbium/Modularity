<?php
/**
 * @package Terbium/Modularity
 * @author Volodimyr Terion Kornilov <mail@terion.name>
 * @copyright Copyright (c) 2013 by Terion
 * @license MIT
 * @version 1.0
 * @access public
 */

namespace Terbium\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class Modularity extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'modularity'; }

}