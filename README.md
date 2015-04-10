# clue/viewvc-api-react [![Build Status](https://travis-ci.org/clue/php-viewvc-api-react.svg?branch=master)](https://travis-ci.org/clue/php-viewvc-api-react)

Simple, async API-like access to your [ViewVC](http://viewvc.org/) web interface (Subversion/CVS browser), built on top of [React PHP](http://reactphp.org/).

## Quickstart example

Once [installed](#install), you can use the following code to fetch a directory
listing from the given ViewVC URL:

```php
$loop = React\EventLoop\Factory::create();
$browser = new Clue\React\Buzz\Browser($loop);
$client = new Client('http://example.com/viewvc/', $browser);

$client->fetchDirectory('/')->then(function ($files) {
    echo 'Files: ' . implode(', ', $files) . PHP_EOL;
});

$loop->run();
```

See also the [examples](examples).

## Usage

### Client

The `Client` is responsible for assembling and sending HTTP requests to the remote ViewVC web interface.
It requires a [`Browser`](https://github.com/clue/php-buzz-react#browser) object
bound to the main [`EventLoop`](https://github.com/reactphp/event-loop#usage)
in order to handle async requests:

```php
$loop = React\EventLoop\Factory::create();
$browser = new Clue\React\Buzz\Browser($loop);

$client = new Client($url, $browser);
```

If you need custom DNS or proxy settings, you can explicitly pass a
custom [`Browser`](https://github.com/clue/php-buzz-react#browser) instance.

#### Actions

ViewVC does not officially expose an API. However, its REST-like URLs make it
easy to construct the right requests and scrape the results from its HTML
output.
All public methods resemble these respective actions otherwise available in the
ViewVC web interface.

```php
$client->fetchDirectory($path, $revision = null);
$client->fetchFile($path, $revision = null);
$client->fetchPatch($path, $r1, $r2);
$client->fetchLog($path, $revision = null);

// many more…
```

All actions support async operation by returning [promises](#promises).

Listing all available actions is out of scope here, please refer to the [class outline](src/Client.php).

#### Promises

Sending requests is async (non-blocking), so you can actually send multiple requests in parallel.
ViewVC will respond to each request with a response message, the order is not guaranteed.
Sending requests uses a [Promise](https://github.com/reactphp/promise)-based interface that makes it easy to react to when a request is fulfilled (i.e. either successfully resolved or rejected with an error).

```php
$client->fetchFile($path)->then(
    function ($contents) {
        // file contents received
    },
    function (Exception $e) {
        // an error occured while executing the request
    }
});
```

If this looks strange to you, you can also use the more traditional [blocking API](#blocking).

#### Blocking

As stated above, this library provides you a powerful, async API by default.

If, however, you want to integrate this into your traditional, blocking environment,
you should look into also using [clue/block-react](https://github.com/clue/php-block-react).

The resulting blocking code could look something like this:

```php
$loop = React\EventLoop\Factory::create();
$browser = new Clue\React\Buzz\Browser($loop);
$blocker = new Clue\React\Block\Blocker($loop);

$client = new Client($url /* change me */, $browser);
$promise = $client->fetchFile($path /* change me */);

try {
    $contents = $blocker->awaitOne($promise);
    // file contents received
} catch (Exception $e) {
    // an error occured while executing the request
}
```

Refer to [clue/block-react](https://github.com/clue/php-block-react#readme) for more details.

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/viewvc-api-react": "~0.2.0"
    }
}
```

## License

MIT
