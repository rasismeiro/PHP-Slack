<?php namespace ConnorVG\Slack;

class SlackPayload
{

	/**
	 * Payload data
	 */
	public $data;
	public $command;
	public $to;

	/**
	 * Create a new slackpayload instance.
	 *
	 * @return void
	 */
	public function __construct($slack, $data = [])
	{
		$this->slack = $slack;

		$this->command = $data['command'];

		unset($data['command']);

		if (isset($data['data']))
			$data = $data['data'];

		$this->data = $data;
	}

	/**
	 * Static Variables
	 */

	protected static $attachement_keys = [
		'fallback',
		'text',
		'pretext',
		'color',
		'fields'
	];

	/**
	 * Utilities
	 */

	protected static function has_attachment_key($data)
	{
		foreach (static::$attachement_keys as $key)
			if (array_key_exists($key, $data))
				return true;

		return false;
	}

	protected static function format_attachments($data)
	{
		if (static::has_attachment_key($data))
			return $data;

		$formatted = [];

		foreach ($data as $partial)
			$formatted[] = [ 'text' => $partial ];

		return $formatted;
	}

	/**
	 * Methods
	 */

	public function send()
	{
		if ($this->to !== null)
		{
			$old = isset($this->data['text']) ? $this->data['text'] : '';
			$this->data['text'] = $this->to . $old;

			$ret = $this->slack->send($this);

			$this->data['text'] = $old;

			return $ret;
		}
		else return $this->slack->send($this);
	}

	public function set($key, $val = null)
	{
		if (!is_array($key))
			$key = [ $key => $val ];

		foreach ($key as $k => $v)
			if ($v === null)
			{
				if (array_key_exists($k, $this->data))
					unset($this->data[$k]);
			}
			else
				$this->data[$k] = $v;

		return $this;
	}

	public function un_set($keys)
	{
		if (!is_array($keys))
			$keys = [ $keys ];

		$data = [];
		foreach ($keys as $key)
			$data[] = [ $key, null ];

		return $this->set($data);
	}

	public function message($data)
	{
		if (is_array($data))
			return $this->set(['attachments' => static::format_attachments($data)]);
		else
			return $this->set(['text' => $data]);
	}

	public function channel($channel)
	{
		return $this->set(['channel' => $channel]);
	}

	public function username($username)
	{
		return $this->set(['username' => $username]);
	}

	public function from($username)
	{
		return $this->username($username);
	}

	public function to($users)
	{
		if (!is_array($users))
			$users = [ $users ];

		$to = '';
		$cnt = count($users);
		for ($i = 0; $i < $cnt; $i++)
		{
			if ($i !== 0)
				$to = $to . ', ';

			$to = $to . '<@' . $users[$i] . '>';
		}
		$this->to = $to . ': ';

		return $this;
	}

	public function emoji($emoji)
	{
		return $this->set(['icon_emoji' => $emoji, 'icon_url' => null]);
	}

	public function icon($url)
	{
		return $this->set(['icon_url' => $url, 'icon_emoji' => null]);
	}
}