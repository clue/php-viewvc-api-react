# clue/viewvc-api-react [![Build Status](https://travis-ci.org/clue/php-viewvc-api-react.svg?branch=master)](https://travis-ci.org/clue/php-viewvc-api-react)

Simple, async API-like access to your [ViewVC](http://viewvc.org/) web interface (Subversion/CVS browser), built on top of [React PHP](http://reactphp.org/).

> Note: This project is in early alpha stage! Feel free to report any issues you encounter.

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

// many more…
```

Listing all available actions is out of scope here, please refer to the [class outline](src/Client.php).

#### Processing

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

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/viewvc-api-react": "dev-master"
    }
}
```

## License

MIT
