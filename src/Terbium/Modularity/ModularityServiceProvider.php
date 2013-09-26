<?php namespace Terbium\Modularity;

use Illuminate\Support\ServiceProvider;

class ModularityServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// init the package
		$this->package('terbium/modularity');
		// run it
		$this->app['modularity'];
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// bind the singletone
		$this->app['modularity'] = $this->app->share(function($app){
			return new Modularity($app['files'], $app['config'], $app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('modularity');
	}

}