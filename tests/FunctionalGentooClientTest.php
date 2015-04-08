<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use React\Promise\PromiseInterface;
use Clue\React\Block\Blocker;

class FunctionalGentooClientTest extends TestCase
{
    private $loop;
    private $viewvc;
    private $blocker;

    public function setUp()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('TLS (HTTPS) not supported by your platform (HHVM?)');
        }

        $url = 'https://sources.gentoo.org/cgi-bin/viewvc.cgi/gentoo/';

        $this->loop = LoopFactory::create();
        $this->blocker = new Blocker($this->loop);
        $browser = new Browser($this->loop);

        // check connectivity to given URL only once
        static $checked = null;
        if ($checked === null) {
            try {
                $this->waitFor($browser->head($url));
                $checked = true;
            } catch (Exception $e) {
                $checked = false;
            }
        }

        if (!$checked) {
            $this->markTestSkipped('Unable to reach Gentoo ViewVC');
        }

        $this->viewvc = new Client($url, $browser);
    }

    public function testFetchDirectoryAttic()
    {
        $path = '/';

        $promise = $this->viewvc->fetchDirectory($path, null, true);
        $files = $this->waitFor($promise);

        $this->assertEquals(array('misc/', 'src/', 'users/', 'xml/', '.frozen'), $files);
    }

    public function testFetchFileDeletedShowsLastState()
    {
        $file = '.frozen';

        $promise = $this->viewvc->fetchFile($file);
        $contents = $this->waitFor($promise);

        $this->assertEquals("robbat2\n", $contents);
    }

    private function waitFor(PromiseInterface $promise)
    {
        return $this->blocker->awaitOne($promise);
    }
}
