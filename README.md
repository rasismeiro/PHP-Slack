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

	This will auto-magically send a message based on the given parameters.
	
### Composer setup

In the `require` key of `composer.json` file add the following

    "connorvg/php-slack": "dev-master"

Run the Composer update comand

    $ composer update

### Laravel

If you're using laravel, add this service provider:
```php
'ConnorVG\Slack\SlackServiceProvider'
```

Also, this Facade:
```php
'Slack' => 'ConnorVG\Slack\SlackFacade'
```

You'll need to add your Slack API token to `config/app.php`:
```php
'slackapikey' => 'XXX'
```
