<?php namespace ConnorVG\Slack;

class SlackUtilities
{

	protected $slack;

	protected $cache = [
		'users.list' => null,

		'channels.list' => null,

		'files.list' => null,

		'im.list' => null,

		'groups.list' => null,

		'auth.test' => null
	];

	protected function cachable($command)
	{
		return array_key_exists($command, $this->cache);
	}

	protected function precache($command)
	{
		$response = $this->slack->prepare($command)->send();
		$this->cache[$command] = $response;

		return $response;
	}

	protected function cached($command)
	{
		return $this->cache[$command] ?: $this->precache($command);
	}

	public function __construct($slack)
	{
		$this->slack = $slack;
	}

	public function clear($keys = null)
	{
		if ($keys === null)
			foreach (array_keys($this->cache) as $key)
				$this->cache[$key] = null;

		if (!is_array($keys))
			$keys = [ $keys ];

		foreach ($keys as $key)
			if (array_key_exists($key, $this->cache))
				$this->cache[$key] = null;
	}

	public function users()
	{
		return $this->cached('users.list')->members;
	}

	public function user($name = null, $id = null)
	{
		$users = $this->users();

		if ($this->slack->isDynamic())
			foreach ($users as $user)
				if ($user->name == $name or $user->id == $id)
					return $user;
		else
			foreach ($users as $user)
				if ($user['name'] == $name or $user['id'] == $id)
					return $user;

		return null;
	}

	public function channels()
	{
		return $this->cached('channels.list')->channels;
	}

	public function channel($name = null, $id = null)
	{
		$channels = $this->channels();

		if ($this->slack->isDynamic())
			foreach ($channels as $channel)
				if ($channel->name == $name or $channel->id == $id)
					return $channel;
		else
			foreach ($channels as $channel)
				if ($channel['name'] == $name or $channel['id'] == $id)
					return $channel;

		return null;
	}

	public function files()
	{
		return $this->cached('files.list')->files;
	}

	public function file($name = null, $id = null)
	{
		$files = $this->files();

		if ($this->slack->isDynamic())
			foreach ($files as $file)
				if ($file->name == $name or $file->id == $id)
					return $file;
		else
			foreach ($files as $file)
				if ($file['name'] == $name or $file['id'] == $id)
					return $file;

		return null;
	}

	public function ims()
	{
		return $this->cached('im.list')->ims;
	}

	public function im($id = null, $user = null)
	{
		$ims = $this->ims();

		if ($this->slack->isDynamic())
			foreach ($ims as $im)
				if ($im->id == $id or $im->user == $user)
					return $im;
		else
			foreach ($ims as $im)
				if ($im['id'] == $id or $im['user'] == $user)
					return $im;

		return null;
	}

	public function groups()
	{
		return $this->cached('groups.list')->groups;
	}

	public function group($name = null, $id = null)
	{
		$groups = $this->cached('groups.list');

		if ($this->slack->isDynamic())
			foreach ($groups as $group)
				if ($group->name == $name or $group->id == $id)
					return $group;
		else
			foreach ($groups as $group)
				if ($group['name'] == $name or $group['id'] == $id)
					return $group;

		return null;
	}

	public function auth()
	{
		return $this->cached('auth.test');
	}

	public function auth_url()
	{
		return $this->cached('auth.test')->url;
	}

	public function auth_team()
	{
		return $this->cached('auth.test')->team;
	}

	public function auth_team_id()
	{
		return $this->cached('auth.test')->team_id;
	}

	public function auth_user()
	{
		return $this->cached('auth.test')->user;
	}

	public function auth_user_id()
	{
		return $this->cached('auth.test')->user_id;
	}
}