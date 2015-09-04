<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use Clue\React\Block;

class FunctionalApacheClientTest extends TestCase
{
    private $loop;
    private $viewvc;

    public function setUp()
    {
        $url = 'http://svn.apache.org/viewvc/';

        $this->loop = LoopFactory::create();
        $browser = new Browser($this->loop);

        $this->viewvc = new Client($browser->withBase($url));
    }

    public function testFetchDirectory()
    {
        $path = 'jakarta/ecs/';

        $promise = $this->viewvc->fetchDirectory($path);
        $files = Block\await($promise, $this->loop);

        $this->assertEquals(array('branches/', 'tags/', 'trunk/'), $files);
    }

    public function testFetchFile()
    {
        $file = 'jakarta/ecs/tags/V1_0/src/java/org/apache/ecs/AlignType.java';
        $revision = '168703';

        $promise = $this->viewvc->fetchFile($file, $revision);
        $recipe = Block\await($promise, $this->loop);

        $this->assertStringStartsWith('/*', $recipe);
    }

    public function testFetchFileOldFileNowDeletedButRevisionAvailable()
    {
        $file = 'commons/STATUS';
        $revision = '1';

        $promise = $this->viewvc->fetchFile($file, $revision);
        $contents = Block\await($promise, $this->loop);

        $this->assertStringStartsWith('APACHE COMMONS', $contents);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFetchFileInvalid()
    {
        $file = 'asdasd';
        $revision = '123';

        $promise = $this->viewvc->fetchFile($file, $revision);
        Block\await($promise, $this->loop);
    }

    public function testFetchRevisionPrevious()
    {
        $file = 'jakarta/ecs/tags/V1_0/src/java/org/apache/ecs/AlignType.java';
        $revision = '168703';

        $promise = $this->viewvc->fetchRevisionPrevious($file, $revision);
        $revision = Block\await($promise, $this->loop);

        $this->assertEquals('168695', $revision);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFetchRevisionUnknownBase()
    {
        $file = 'jakarta/ecs/tags/V1_0/src/java/org/apache/ecs/AlignType.java';
        $revision = 'xyz';

        $promise = $this->viewvc->fetchRevisionPrevious($file, $revision);
        Block\await($promise, $this->loop);
    }
}
