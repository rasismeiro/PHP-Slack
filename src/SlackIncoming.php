<?php namespace ConnorVG\Slack;

class SlackIncoming
{
	protected $data = [
		'token' => null,

		'team_id' => null,

		'channel_id' => null,
		'channel_name' => null,

		'timestamp' => null,

		'user_id' => null,
		'user_name' => null,

		'text' => null,
		'source_text' => null,

		'trigger_word' => null
	];

	public function __construct($data = [])
	{
		$data = $data ?: [];

		foreach ($data as $key => $value)
			if (array_key_exists($key, $this->data))
				$this->data[$key] = $value;

		if ($this->text !== null && $this->trigger_word !== null)
		{
			$this->source_text = $this->text;
			$this->text = substr($this->text, strlen($this->trigger_word));
		}
	}

	public function __get($varName)
	{
		if (!array_key_exists($varName, $this->data))
			return $this->$$varName;
		else
			return $this->data[$varName];
	}

	public function __set($varName, $value)
	{
		$this->data[$varName] = $value;
	}

	public function hasError()
	{
		foreach ($this->data as $key => $value)
			if ($this->$key === null)
				return true;

		return false;
	}

	public function slack()
	{
		return Slack::getTokenInstance($this->token);
	}
}