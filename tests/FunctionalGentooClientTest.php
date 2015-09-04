<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use Clue\React\Block;

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
        static $checked = null;
        if ($checked === null) {
            try {
                Block\await($browser->head($url), $this->loop);
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
