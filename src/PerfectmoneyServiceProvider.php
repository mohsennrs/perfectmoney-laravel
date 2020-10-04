<?php 
namespace Package\Perfectmoney;

use Illuminate\Support\ServiceProvider;
use Package\Perfectmoney\Perfectmoney;

/**
 * summary
 */
class PerfectmoneyServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->loadMigrationsFrom(__DIR__.'/Database/migrations');
		$this->loadRoutesFrom(__DIR__.'/Routes/web.php');

		$this->loadViewsFrom(__DIR__.'/Views', 'mohsen-nurisa/perfectmoney-laravel');
		$this->publishes([
	        __DIR__.'/Config/perfectmoney.php' => config_path('perfectmoney.php'),
	    ]);

	    $this->app->bind('perfectmoney', function ($app) {
	        return new Perfectmoney();
	    });
	}

	public function register()
	{
		
	}
}
?>