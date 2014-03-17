<?php namespace ConnorVG\Slack;

class Slack
{
	
	public $apikey;

	/**
	 * Create a new wholetextfunctions instance.
	 *
	 * @return void
	 */
	public function __construct($apikey)
	{
		$this->apikey = $apikey;
	}

	/**
	 * Static Variables
	 */

	protected static $supportedMetadata = [
		'users' => [
			'list' => false
		],
		'channels' => [
			'history' => true,
			'mark' => true,
			'list' => true
		],
		'files' => [
		//	'upload' => true,
			'list' => true
		],
		'im' => [
			'history' => true,
			'list' => false
		],
		'groups' => [
			'history' => true,
			'list' => true
		],
		'search' => [
			'all' => true,
			'files' => true,
			'messages' => true
		],
		'chat' => [
			'postMessage' => true
		],
		'auth' => [
			'test' => false
		]
	];

	protected static $defaults = [
		'users' => [
			'list' => []
		],
		'channels' => [
			'history' => [],
			'mark' => [],
			'list' => []
		],
		'files' => [
		//	'upload' => [],
			'list' => []
		],
		'im' => [
			'history' => [],
			'list' => []
		],
		'groups' => [
			'history' => [],
			'list' => []
		],
		'search' => [
			'all' => [],
			'files' => [],
			'messages' => []
		],
		'chat' => [
			'postMessage' => [
				'username' => 'ConnorBot',
				'icon_emoji' => ':octocat:'
			]
		],
		'auth' => [
			'test' => []
		]
	];

	/**
	 * Utilities
	 */

	protected static function get_web_page($url) {
		try {
			$ch = curl_init();

			if (FALSE === $ch)
				throw new Exception('failed to initialize');

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$content = curl_exec($ch);

			if (FALSE === $content)
				throw new Exception(curl_error($ch), curl_errno($ch));

			return $content;
		} catch(Exception $e) {

			trigger_error(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()),
				E_USER_ERROR);
		}

		return null;
	}

	protected static function urlify($data)
	{
		if (is_array($data))
			return urlencode(json_encode($data, true));
		else
			return urlencode($data);
	}

	protected static function build_url($command, $token, $params = [])
	{
		$url = 'https://bigbite.slack.com/api/' . $command . '?token=' . $token;

		foreach (array_keys($params) as $param)
			$url = $url . '&' . $param . '=' . static::urlify($params[$param]);

		return $url;
	}

	protected static function command_supported($command)
	{
		$command = explode('.', $command);
		$last = $command[count($command) - 1];
		$currentCommand = static::$supportedMetadata;

		foreach ($command as $subCommand)
		{
			if (is_array($currentCommand) && array_key_exists($subCommand, $currentCommand))
				$currentCommand = $currentCommand[$subCommand];
			else
				$currentCommand = null;

			if ($currentCommand === null)
				return false;
		}

		return true;
	}

	protected static function requires_payload($command)
	{
		$command = explode('.', $command);
		$last = array_pop($command);
		$currentCommand = static::$supportedMetadata;

		foreach ($command as $subCommand)
			$currentCommand = $currentCommand[$subCommand];

		return $currentCommand[$last];
	}

	protected static function get_default_payload($command)
	{
		$command = explode('.', $command);
		$last = array_pop($command);
		$currentCommand = static::$defaults;

		foreach ($command as $subCommand)
			$currentCommand = $currentCommand[$subCommand];

		return $currentCommand[$last];
	}

	public static function hasError($data)
	{
		return $data === null or !is_array($data) or !array_key_exists('ok', $data) or $data['ok'] === 0;
	}

	/**
	 * Methods
	 */

	public function send($payload)
	{
		return json_decode(static::get_web_page(static::build_url($payload->command, $this->apikey, $payload->data)), true);
	}

	public function prepare($command)
	{
		if (!static::command_supported($command))
			return null;

		if (!static::requires_payload($command))
			return new SlackPayload($this, [ 'command' => $command ]);

		return new SlackPayload($this, [ 'command' => $command, 'data' => static::get_default_payload($command) ]);
	}

	public function message($message, $channel, $from = null, $to = null, $icon_emoji = null, $icon_url = null)
	{
		$message = $this->prepare('chat.postMessage')->message($message)->channel($channel);

		if ($from !== null)
			$message->from($from);

		if ($to !== null)
			$message->to($to);

		if ($icon_emoji !== null)
			$message->emoji($icon_emoji);
		elseif ($icon_url !== null)
			$message->icon($icon_url);

		return $message->send();
	}
}