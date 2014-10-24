<?php namespace EscapeWork\Assets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class AssetsServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $app = $this->app;

        $this->package('escapework/laravel-asset-versioning', 'laravel-asset-versioning', realpath(__DIR__ . '/../..'));

		$this->app['escapework.asset'] = $this->app->share(function($app)
        {
            return new Asset($app, $app['config'], $app['cache.store']);
        });

        $this->app['escapework.asset.command'] = $this->app->share(function($app)
        {
            return new Commands\AssetDistCommand($app['config'], $app['files'], $app['cache.store'], array(
                'app'    => app_path(),
                'public' => public_path(),
            ));
        });

        $this->commands('escapework.asset.command');

        $this->app->booting(function()
        {
            $loader = AliasLoader::getInstance();

            $loader->alias('Asset', 'EscapeWork\Assets\Facades\Asset');
        });

        $this->app['events']->listen('cache:cleared', function() use($app)
        {
            $app['artisan']->call('asset:dist');
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('escapework.asset');
	}

}
