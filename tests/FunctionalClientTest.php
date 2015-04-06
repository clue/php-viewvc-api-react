<?php

use Clue\React\ViewVcApi\Client;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Buzz\Browser;
use React\Promise\PromiseInterface;
use Clue\React\Block\Blocker;

class FunctionalClientTest extends TestCase
{
    private $loop;
    private $viewvc;
    private $blocker;

    public function setUp()
    {
        $url = 'https://svn.apache.org/viewvc/';

        $this->loop = LoopFactory::create();
        $this->blocker = new Blocker($this->loop);
        $browser = new Browser($this->loop);

        $this->viewvc = new Client($url, $browser);
    }

    public function testFetchDirectory()
    {
        $path = 'jakarta/ecs/';

        $promise = $this->viewvc->fetchDirectory($path);
        $files = $this->waitFor($promise);

        $this->assertEquals(array('branches/', 'tags/', 'trunk/'), $files);
    }

    public function testFetchFile()
    {
        $file = 'jakarta/ecs/tags/V1_0/src/java/org/apache/ecs/AlignType.java';
        $revision = '168703';

        $promise = $this->viewvc->fetchFile($file, $revision);
        $recipe = $this->waitFor($promise);

        $this->assertStringStartsWith('/*', $recipe);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFetchFileInvalid()
    {
        $file = 'asdasd';
        $revision = '123';

        $promise = $this->viewvc->fetchFile($file, $revision);
        $this->waitFor($promise);
    }

    public function testFetchRevisionPrevious()
    {
        $file = 'jakarta/ecs/tags/V1_0/src/java/org/apache/ecs/AlignType.java';
        $revision = '168703';

        $promise = $this->viewvc->fetchRevisionPrevious($file, $revision);
        $revision = $this->waitFor($promise);

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
        $this->waitFor($promise);
    }

    private function waitFor(PromiseInterface $promise)
    {
        return $this->blocker->awaitOne($promise);
    }
}
