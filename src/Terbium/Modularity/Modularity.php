<?php
/**
 * @package Terbium/Modularity
 * @author Volodimyr Terion Kornilov <mail@terion.name>
 * @copyright Copyright (c) 2013 by Terion
 * @license MIT
 * @version 1.0
 * @access public
 */

namespace Terbium\Modularity;

use \Illuminate\Foundation\Application;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Config\Repository;


class Modularity {

	/**
	 * App instance
	 *
	 * @var \App
	 */
	protected $app;

	/**
	 * Filesystem instance
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Config instance
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Directory containing modules
	 *
	 * @var string
	 */
	protected $modulesDirectory;

	/**
	 * List of enabled modules
	 *
	 * @var array
	 */
	protected $modules = null;

	/**
	 * List of all present modules
	 *
	 * @var array
	 */
	protected $allModules = null;

	/**
	 * Class constructor
	 *
	 * @param \Illuminate\Filesystem\Filesystem $filesystem
	 * @param \Illuminate\Config\Repository $config
	 * @return void
	 */
	public function __construct(Filesystem $filesystem, Repository $config, Application $app)
	{
		$this->filesystem = $filesystem;
		$this->config = $config;
		$this->app = $app;

		//Check and set modules containing directory
		if ( $this->modulesDir() === false ) {
			throw new ModularityException('Modules directory is not readable');
		}

		$this->fetchModules();

		$this->registerModules();

	}

	/**
	 * Check and set modules containing directory
	 *
	 * @return bool|string
	 */
	private function modulesDir()
	{
		$path = $this->getModulesDir();
		if (is_readable($path)) {
			$this->modulesDirectory = $path;
			return $path;
		}

		return false;
	}

	/**
	 * Get the path to modules dir
	 *
	 * @return string
	 */
	public function getModulesDir() {
		return $this->config->get('modularity::modules_directory');
	}

	/**
	 * Get the list of modules and fill $this->modules array
	 */
	private function fetchModules() {

		$this->modules = $this->listEnabledModules();

	}

	/**
	 * List enabled modules depending on config
	 *
	 * @return array
	 */
	public function listEnabledModules() {

		$enabledModules = array();

		$enabledModulesConfig = array();

		$listFrom = $this->config->get('modularity::use_db') ? 'db' : 'config';

		$strict = $this->config->get('modularity::strict_mode');

		if ( $listFrom === 'config' ) {
			$enabledModulesConfig = $this->config->get('modularity::enabled_modules');
		}
		else {
			// TODO: Сделать парсер из бд
			$enabledModulesConfig = array();
		}

		if ($enabledModulesConfig === false) {
			// All modules disabled: we will return empty array
			// Nothing to do at this point
		}
		elseif ( empty($enabledModulesConfig) ) {
			// All modules enabled: return list of all modules available
			$enabledModules = $this->listAllModules();
		}
		else {
			$allModules = $this->listAllModules();
			foreach ($enabledModulesConfig as $mc ) {
				if ( $this->checkModule($mc) ) {
					$enabledModules[] = $mc;
				}
				elseif ( $strict ) {
					throw new ModularityNotFoundException("Module {$mc} not found");
				}
			}
		}

		return $enabledModules;

	}

	/**
	 * Check if the module exists
	 *
	 * @param string $module
	 * @return bool
	 */
	private function checkModule($module) {
		$modDir = str_finish($this->modulesDirectory, '/') . $module;
		return ($this->filesystem->exists($modDir) && $this->filesystem->isDirectory($modDir));
	}

	/**
	 * List all modules present in modules dir
	 *
	 * @return array
	 */
	public function listAllModules() {
		if ( ! is_null($this->allModules) ) {
			return $this->allModules;
		}

		$mods = array();
		$dirs = $this->filesystem->directories($this->modulesDirectory);
		foreach ($dirs as $dir) {
			$mods[] = pathinfo($dir, PATHINFO_BASENAME);
		}
		$this->allModules = $mods;
		return $this->allModules;
	}

	// TODO: modulesInfo() — все модули со статусом и детальной информацией

	/**
	 * Register the modules
	 */
	private function registerModules() {

		foreach ($this->modules as $module) {
			$this->registerModule($module);
		}

	}

	/**
	 * Register a module
	 */
	public function registerModule($module) {
		// Register the module as a package
		$this->config->package(
			'app/' . $module,
			str_finish($this->modulesDirectory, '/') . $module . '/config'
		);

		// Add routes
		$routes = str_finish($this->modulesDirectory, '/') . $module . '/routes.php';
		if ( $this->filesystem->exists($routes) ) {
			$this->filesystem->getRequire($routes);
		}

		// Run module boot
		$boot = str_finish($this->modulesDirectory, '/') . $module . '/boot.php';
		if ( $this->filesystem->exists($boot) ) {
			$this->filesystem->getRequire($boot);
		}
	}


}