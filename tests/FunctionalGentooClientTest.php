<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use Clue\React\Block;
use Clue\React\Buzz\Message\ResponseException;

class FunctionalGentooClientTest extends TestCase
{
    private $loop;
    private $viewvc;

    public function setUp()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('TLS (HTTPS) not supported by your platform (HHVM?)');
        }

        $url = 'https://sources.gentoo.org/cgi-bin/viewvc.cgi/gentoo/';

        $this->loop = LoopFactory::create();
        $browser = new Browser($this->loop);

        // check connectivity to given URL only once
        static $error = null;
        if ($error === null) {
            try {
                Block\await($browser->get($url), $this->loop);
                $error = false;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        if ($error !== false) {
            $this->markTestSkipped('Unable to reach Gentoo ViewVC: ' . $error);
        }

        $this->viewvc = new Client($browser->withBase($url));
    }

    public function testFetchDirectoryAttic()
    {
        $path = '/';

        $promise = $this->viewvc->fetchDirectory($path, null, true);
        $files = Block\await($promise, $this->loop);

        $this->assertEquals(array('misc/', 'src/', 'users/', 'xml/', '.frozen'), $files);
    }

    public function testFetchFileDeletedShowsLastState()
    {
        $file = '.frozen';

        $promise = $this->viewvc->fetchFile($file);
        $contents = Block\await($promise, $this->loop);

        $this->assertEquals("robbat2\n", $contents);
    }
}
