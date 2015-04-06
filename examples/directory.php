<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;

require __DIR__ . '/../vendor/autoload.php';

$url = 'https://svn.apache.org/viewvc/';
$path = isset($argv[1]) ? $argv[1] : '/';

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

$client = new Client($url, $browser);

$client->fetchDirectory($path)->then('var_dump', 'var_dump');

$loop->run();
