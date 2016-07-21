<?php
 
 namespace BotTelegram;

use BotTelegram\Validators\BotValidator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;

class BotTelServiceProvider extends ServiceProvider {

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
	public function boot() {

		$this->app['validator']->resolver(function($translator, $data, $rules, $messages)
		{
			return new BotValidator($translator, $data, $rules, $messages);
		});
		
	}

	public function register()
	{
		$this->publishes([__DIR__ . '/../config/' => config_path() . "/"], 'config');
		$this->publishes([__DIR__ . '/../assets/' => public_path("assets")], 'public');

		// Routing
		if (! $this->app->routesAreCached()) {
			include __DIR__ . '/routes.php';
		}

		$this->loadViewsFrom(__DIR__.'/../views', 'bot-telegram');

		$this->app->singleton('BotTelegram', 'BotTelegram\bot\BotTelegram');

		view()->composer('bot-telegram::commands', 'BotTelegram\Composers\TagsComposer');

//		view()->composer('bot-telegram::commands', function($view) {
//			$view->with('test', 34555);
//			view()->share('js_files', static::$js_files);
//		});
		//		\Illuminate\Support\Facades\Validator::extend('eachFile', 'BotTelegram\Validators@eachFile');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	public function test() {
		return 'package';
	}

}
