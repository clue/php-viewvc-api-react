# clue/viewvc-api-react [![Build Status](https://travis-ci.org/clue/php-viewvc-api-react.svg?branch=master)](https://travis-ci.org/clue/php-viewvc-api-react)

Simple, async API-like access to your [ViewVC](http://viewvc.org/) web interface (Subversion/CVS browser), built on top of [React PHP](http://reactphp.org/).

**Table of Contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
  * [Client](#client)
    * [Actions](#actions)
    * [Promises](#promises)
    * [Blocking](#blocking)
    * [Streaming](#streaming)
* [Install](#install)
* [License](#license)

## Quickstart example

Once [installed](#install), you can use the following code to fetch a directory
listing from the given ViewVC URL:

```php
$loop = React\EventLoop\Factory::create();
$browser = new Clue\React\Buzz\Browser($loop);
$client = new Client($browser->withBase('http://example.com/viewvc/'));

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

$client = new Client($browser->withBase('http://example.com/viewvc/'));
```

The `Client` API uses relative URIs to reference files and directories in your
ViewVC installation, so make sure to apply the base URI as depicted above.

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
use Clue\React\Block;

$loop = React\EventLoop\Factory::create();
$browser = new Clue\React\Buzz\Browser($loop);

$client = new Client($browser->withBase($uri /* change me */));
$promise = $client->fetchFile($path /* change me */);

try {
    $contents = Block\await($promise, $loop);
    // file contents received
} catch (Exception $e) {
    // an error occured while executing the request
}
```

Refer to [clue/block-react](https://github.com/clue/php-block-react#readme) for more details.

#### Streaming

The following API endpoint resolves with the file contents as a string:

```php
$client->fetchFile($path);
````

Keep in mind that this means the whole string has to be kept in memory.
This is easy to get started and works reasonably well for smaller files.

For bigger files it's usually a better idea to use a streaming approach,
where only small chunks have to be kept in memory.
This works for (any number of) files of arbitrary sizes.

The following API endpoint complements the default Promise-based API and returns
an instance implementing `ReadableStreamInterface` instead:

```php
$stream = $client->fetchFileStream($path);

$stream->on('data', function ($chunk) {
    echo $chunk;
});

$stream->on('error', function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
});

$stream->on('close', function () {
    echo '[DONE]' . PHP_EOL;
});
```

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/viewvc-api-react": "~0.3.0"
    }
}
```

## License

MIT
