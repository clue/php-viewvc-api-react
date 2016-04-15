<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use React\Stream\Stream;

require __DIR__ . '/../vendor/autoload.php';

$url = 'https://svn.apache.org/viewvc/';
$path = isset($argv[1]) ? $argv[1] : '/';
$revision = isset($argv[2]) ? $argv[2] : null;

$loop = LoopFactory::create();

// $dns = '10.52.166.2';
// $resolver = new React\Dns\Resolver\Factory();
// $resolver = $resolver->createCached($dns, $loop);
// $connector = new React\SocketClient\Connector($loop, $resolver);

// $socks = new \Clue\React\Socks\Client($loop, '127.0.0.1', 9050);
// $socks->setResolveLocal(false);
// $connector = $socks->createConnector();

// $sender = Clue\React\Buzz\Io\Sender::createFromLoopConnectors($loop, $connector);
$sender = null;

$browser = new Browser($loop, $sender);

$client = new Client($browser->withBase($url));

if (substr($path, -1) === '/') {
    $client->fetchDirectory($path, $revision)->then('print_r', 'printf');
} else {
    //$client->fetchFile($path, $revision)->then('print_r', 'printf');

    $stream = $client->fetchFileStream($path, $revision);

    // any errors
    $stream->on('error', 'printf');

    // pipe stream into STDOUT
    $out = new Stream(STDOUT, $loop);
    $out->pause();
    $stream->pipe($out);
}

$loop->run();
