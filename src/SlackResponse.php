<?php namespace ConnorVG\Slack;

class SlackResponse implements \ArrayAccess, \Countable, \Iterator
{
	
	protected $data;

	protected static function dyanamicise_array($array)
	{
		foreach ($array as $key => $value)
			if (is_array($value))
				$array[$key] = new SlackResponse($array[$key]);

		return $array;
	}

	public function __construct($data = [])
	{
		$this->data = static::dyanamicise_array($data);
	}

	public function __get($varName)
	{
		if (!array_key_exists($varName, $this->data))
			return null;
		else
			return $this->data[$varName];
	}

	public function __set($varName, $value)
	{
		$this->data[$varName] = $value;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset))
			$this->data[] = $value;
		else
			$this->__set($offset, $value);
	}

	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	public function offsetGet($offset) {
		return $this->__get($offset);
	}
 
	public function key() {
		return key($this->data);
	}

	public function current() {
		return current($this->data);
	}

	public function next() {
		next($this->data);
	}

	public function rewind() {
		reset($this->data);
	}

	public function valid() {
		return current($this->data);
	}

	public function count() {
		return count($this->data);
	}

	public function hasError()
	{
		return $data == null or !isset($data['ok']) or $data['ok'] !== true;
	}
}