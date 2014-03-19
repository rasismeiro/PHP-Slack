<?php namespace ConnorVG\Slack;

use Illuminate\Support\ServiceProvider;

class SlackServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('connorvg/slack');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerSlack(); 
	}

	/**
	 * Register the application bindings.
	 *
	 * @return void
	 */
	protected function registerSlack()
	{
		$this->app->bind('slack', function($app)
		{
			return new Slack($app['config']->get('app.slack.apikey'), $app['config']->get('app.slack.dynamic') ?: false);
		});
	}
}