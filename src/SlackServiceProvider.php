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

		$this->installSlack();
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
			return Slack::getInstance($app['config']->get('app.slack.apikey'), $app['config']->get('app.slack.dynamic') ?: false, $app['config']->get('app.slack.verified_only') ?: false);
		});
	}

	protected function installSlack()
	{
		$installs = $this->app['config']->get('app.slack.installs');

		if ($installs !== null)
			foreach ($installs as $token => $key)
				Slack::installToken($token, $key);
	}
}