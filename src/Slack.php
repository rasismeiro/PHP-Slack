<?php namespace ConnorVG\Slack;

class Slack
{

	protected $apikey;
	protected $dynamic;
	protected $verified_only;

	protected $utilities;

	/**
	 * Create a new Slack instance.
	 *
	 * @return void
	 */
	public function __construct($apikey, $dynamic = true, $verified_only = true)
	{
		$this->apikey = $apikey;
		$this->dynamic = $dynamic;
		$this->verified_only = $verified_only;
	}

	/**
	 * Static Variables
	 */

	protected static $commandMetadata = [
		'users' => [
			'list' => [false, []]
		],
		'channels' => [
			'history' => [true, []],
			'mark' => [true, []],
			'list' => [true, []]
		],
		'files' => [
		//	'upload' => true,
			'list' => [true, []]
		],
		'im' => [
			'history' => [true, []],
			'list' => [false, []]
		],
		'groups' => [
			'history' => [true, []],
			'list' => [true, []]
		],
		'search' => [
			'all' => [true, []],
			'files' => [true, []],
			'messages' => [true, []]
		],
		'chat' => [
			'postMessage' => [true, [
				'username' => 'ConnorBot',
				'icon_emoji' => ':octocat:'
			]]
		],
		'auth' => [
			'test' => [false, []]
		]
	];

	protected static $instances = [];
	protected static $tokens = [];

	/**
	 * Static Getter
	 */
	
	public static function getInstance($apikey = null, $dynamic = false)
	{
		if ($apikey === null)
			return null;

		if (isset(static::$instances[$apikey]))
			return static::$instances[$apikey];

		static::$instances[$apikey] = new Slack($apikey, $dynamic);
		return static::$instances[$apikey];
	}

	public static function getTokenInstance($token)
	{
		if (isset(static::$tokens[$token]))
			return static::getInstance(static::$tokens[$token]);

		foreach (static::$instances as $slack)
			if (!$slack->verified_only)
			{
				static::$tokens[$token] = $slack->apikey;

				return $slack;
			}

		return null;
	}

	public static function installToken($token, $apikey)
	{
		static::$tokens[$token] = $apikey;
	}

	/**
	 * Utilities
	 */
	
	protected static function get_web_page($url, $params) {
		try {
			$ch = curl_init();

			if (FALSE === $ch)
				throw new Exception('failed to initialize');

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$params_string = '';
			foreach (array_keys($params) as $param)
				$params_string .= '&' . $param . '=' . static::urlify($params[$param]);

			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);

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

	protected static function build_url($command, $token)
	{
		return 'https://slack.com/api/' . $command . '?token=' . $token;
	}

	protected static function command_supported($command)
	{
		$command = explode('.', $command);
		$last = $command[count($command) - 1];
		$currentCommand = static::$commandMetadata;

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
		$currentCommand = static::$commandMetadata;

		foreach ($command as $subCommand)
			$currentCommand = $currentCommand[$subCommand];

		return $currentCommand[$last][0];
	}

	protected static function get_default_payload($command)
	{
		$command = explode('.', $command);
		$last = array_pop($command);
		$currentCommand = static::$commandMetadata;

		foreach ($command as $subCommand)
			$currentCommand = $currentCommand[$subCommand];

		return $currentCommand[$last][1];
	}

	public static function hasError($data)
	{
		return $data === null or !is_array($data) or !array_key_exists('ok', $data) or $data['ok'] === 0;
	}

	/**
	 * Methods
	 */

	public function isDynamic()
	{
		return $this->dynamic;
	}

	public function utilities()
	{
		if ($this->utilities === null)
			$this->utilities = new SlackUtilities($this);

		return $this->utilities;
	}

	public function send($payload)
	{
		$response = json_decode(static::get_web_page(static::build_url($payload->command, $this->apikey), $payload->data), true);
		if ($this->dynamic)
			return new SlackResponse($response);
		else
			return $response;
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

		return $message;
	}
}