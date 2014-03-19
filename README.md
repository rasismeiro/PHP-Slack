PHP-Slack
=========

> An Eloquent ORM styled PHP implementation for the Slack API w/ Laravel binds to help out Laravel lovers.

### Supported API Functions

* users
	* list
* channels
	* history
	* list
	* mark
* files
	* list
* im
	* history
	* list
* groups
	* history
	* list
* search
	* all
	* files
	* messages
* chat
	* postMessage
* auth
	* test

### Usage

**If not using Laravel, you'll need to pass your Slack API Token to the Slack constructor**

Every (non-simple) API call starts with a prepare function, this is where you declare your method. You do this like so:
```php
Slack::prepare('COMMAND');
```

This will return a SlackPayload object which will be used later for attributing and sending to api.slack.

*NOTE: If the command you pass to prepare is malformed or not-supported then the return value will be `NULL`*

Once you have a SlackPayload object, you may do everything in a nice natural way. You simply set the variables (refer to [Slack API](https://api.slack.com/), this does differ from command to command) then send it.

To set variables, it is very simple. Just do:
```php
payloadObject->set($key, $val);
```

You can also do:
```php
payloadObject->set(['key1' => 'value1', 'key2' => 'value2' /* etc */]);
```

To send it, all you need to do is:
```
$response = payloadObject->send();
```

The response object will contain a PHP array equilivent to the JSON response from Slack API.

You may want to check if there was an error, to do this just do:
```php
$hasError = Slack::hasError($payloadObject);	// returns a bool
```

This allows you to, if you're confident you'll get everything correct, this:
```php
$lastTenMessages = Slack::prepare('channels.history')->set(['channel' => 'CHANNEL_CODE', 'count' => 10])->send();
```

*NOTE: Setter and helper functions in SlackPayload objects always return it's self to allow for chaining*

### Helpers

One thing to note is that there are a few helper methods, both for special use and general use.

#### General

* `$payloadObject->message`

	Recieves: String OR Array.

	This will auto-magically set the message to be an attachment or text object dependant on the type of data you send. Array -> Attachment, String -> Text.
* `$payloadObject->channel`

	Sets the target channel.
* `$payloadObject->username` and `$payloadObject->from`

	Sets the Bot's username.
* `$payloadObject->to`

	Recieves: String OR Array.

	This will auto-magically apply 'NAME: ' to the start of the sent message with formatted user linking. If an array is passed then it will format them as so: 'USER1, USER2, USER3: '.
* `$payloadObject->emoji`

	Set's the bot's icon (Emoji only, EG: ': octocat :' *NO SPACES*).
* `$payloadObject->icon`

	Set's the bot's icon (URL only, EG: 'example.com/example.png').

#### Special

* `Slack::message`

	Recieves: $message (String or Array), $channel, $from, $to, $icon_emoji, $icon_url.

	This will auto-magically create a message based on the given parameters and return the SlackPayload.

### Response

You have two choices when it comes to the response object, the default is an array that directly represents the JSON response. The second option is a 'Dynamic Response' which allows you to access the data as so: `$response->members[0]->name` rather than `$response['members'][0]['name']`.

To dynamic response must be enabled via the `slack.dynamic` bool config in `config/app.php`.

### Utilities

One major addition to my Slack implementation is the 'Utilities' feature. This allows us to very quickly poll Slack's API and have the data cached so when ever we use it we don't have to re-poll Slack. You can even clear the cache by doing this:
```php
//	Clear the full cache
$utilities->clear();

//	Clear a set of key's caches
$utilities->clear([ 'users.list', 'channels.list' /* etc */ ]);

//	Clear one key's cache
$utilities->clear('files.list');
```

This class is accessed on a Slack instance level. Basically, just do:
```php
//	For an instance
$slack->utilities();

//	For the Laravel Facade
Slack::utilities();
```

Utilites contains methods for getting all lists (by just using their initial key):
```php
//	Gets all users
$utilities->users();

//	Gets all groups
$utilities->groups();

/* etc */
```

Each of these functions has a complimentary non-plural version which allows you to specify a target:
```php
//	A user
public function user($name = null, $id = null);

//	A channel
public function channel($name = null, $id = null);

//	A file
public function file($name = null, $id = null);

//	An im
public function im($id = null, $user = null);

//	A group
public function group($name = null, $id = null);
```

Finally, we have auth-specific functions:
```php
//	Does $slack->prepare('auth.test')->send()
public function auth()

//	Returns the 'url' param from the 'auth.test' packet
public function auth_url()

//	Returns the 'team' param from the 'auth.test' packet
public function auth_team()

//	Returns the 'team_id' param from the 'auth.test' packet
public function auth_team_id()

//	Returns the 'user' param from the 'auth.test' packet
public function auth_user()

//	Returns the 'user_id' param from the 'auth.test' packet
public function auth_user_id()
```

### Incoming Webhooks

Support for incoming webhooks is fully natural feeling, just like outgoing API side of this package. All you have to do is pass the params from the api request (sent from Slack) into a SlackIncoming object. In Laravel, we simply do:
```php
$packet = new ConnorVG\Slack\SlackIncoming(Input::all());
```

With this packet, we can do a lot of cool things. The most important ones, though, are these:
```php
$errored = $packet->hasError();
$slack = $packet->slack();
```

If `$errored` is true then that means the data is malformed or data is missing, this is a sign of a faked request (non-Slack request).

If `$slack` is null then that means the packet was from an non-verified source (as determined by your initial config of Slack).

Now that we have that out of the way, all you have to do is:
```php
$slack->ANY_SLACK_FEATURE();
```

Because the $slack object is a full instance of Slack (as if you created it yourself) with the correct api key set to respond to the sender, ready to go.

All we need to know now is the contents of `$packet`, the contents are everything that Slack sends to you. This includes the Channel ID it came from, the User ID it came from, the Text that was sent etc.

One thing to note, it is slightly different to the main stuff sent from Slack, I actually store the recieved Text in `source_text` and have Text as a formatted piece of text that basically has the `trigger_word` removed from it.

To access any of these variables, simply do something like:
```php
$packet->channel_id;
$packet->user;
$packet->token;
```

### Recommended

This works amazingly with [ConnorVG/PHP-WTF](https://github.com/ConnorVG/PHP-WTF), an example of usage would be this:

The command:
```php
WTF::addCommand('wolframalpha Query:source', function($wtf, $executer, $args)
{
	$answer = WA::easyQuery($args[0]);

	$executer->slack()->message($answer, $executer->channel_id, 'Wolfram|Alpha', $executer->user_id, ':equation')->send();
});
WTF::addAlias('wolframalpha', 'wa');
```

The `Incoming Webhook`
```php
Route::post('slack', function()
{
	$packet = new ConnorVG\Slack\SlackIncoming(Input::all());
	$ret = WTF::Execute($packet->text, $packet);

	if (!$ret[0])
	{ /* THERE WAS AN ERROR */ }

	return '';
});
```

The webhook:
<p align="center">
<img src="http://puu.sh/7Bojk.png">
</p>

The usage:

<img src="http://puu.sh/7A9HC.png">

*NOTE: This sample uses [ConnorVG/Laravel-WolframAlpha](https://github.com/ConnorVG/Laravel-WolframAlpha) and [laravel/laravel](https://github.com/laravel/laravel)*

### Composer setup

In the `require` key of `composer.json` file add the following
```javascript
"connorvg/php-slack": "dev-master"
```

Run the Composer update comand
```bash
$ composer update
```

*NOTE: I advise you don't use dev-master versions and specify a stable release (E.G: 1.0)*

### Laravel

If you're using laravel, add this service provider:
```php
'ConnorVG\Slack\SlackServiceProvider'
```

Also, this Facade:
```php
'Slack' => 'ConnorVG\Slack\SlackFacade'
```

You'll need to add your preferences to `config/app.php`:
```php
'slack' => array(
//	Your unique Slack API token
	'apikey' => 'XXX',

// Whether or not to limit incoming webhook messages to verified only
	'verified_only' => true,

//	The tokens you wish to associate with API keys
	'installs' => [
		'TOKEN' => 'APIKEY'
	],

//	Wether or not to use my Dynamic Response model
	'dynamic' => true
)
```
