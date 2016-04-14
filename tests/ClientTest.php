<?php

use Clue\React\ViewVcApi\Client;
use React\Promise;
use Clue\React\Buzz\Browser;
use Psr\Http\Message\RequestInterface;
use RingCentral\Psr7\Response;

class ClientTest extends TestCase
{
    private $uri = 'http://viewvc.example.org/';
    private $sender;
    private $client;

    public function setUp()
    {
        $this->sender = $this->getMockBuilder('Clue\React\Buzz\Io\Sender')->disableOriginalConstructor()->getMock();

        $browser = new Browser($this->getMock('React\EventLoop\LoopInterface'), $this->sender);

        $this->client = new Client($browser->withBase($this->uri));
    }

    public function testInvalidDirectory()
    {
        $promise = $this->client->fetchDirectory('invalid');
        $this->expectPromiseReject($promise);
    }

    public function testInvalidFile()
    {
        $promise = $this->client->fetchFile('invalid/');
        $this->expectPromiseReject($promise);
    }

    public function testFetchFile()
    {
        $response = new Response(200, array(), '# hello', '1.0', 'OK');

        $this->expectRequest($this->uri . 'README.md?view=co')->will($this->returnValue(Promise\resolve($response)));

        $promise = $this->client->fetchFile('README.md');

        $this->expectPromiseResolveWith('# hello', $promise);
    }

    public function testFetchFileStream()
    {
        $response = new Response(200, array(), '# hello', '1.0', 'OK');

        $this->expectRequest($this->uri . 'README.md?view=co')->will($this->returnValue(Promise\reject()));

        $stream = $this->client->fetchFileStream('README.md');

        $this->assertInstanceOf('React\Stream\ReadableStreamInterface', $stream);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidFileStream()
    {
        $this->client->fetchFileStream('invalid/');
    }

    public function testFetchFileExcessiveSlashesAreIgnored()
    {
        $this->expectRequest($this->uri . 'README.md?view=co')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchFile('/README.md');

        $this->expectPromiseReject($promise);
    }

    public function testFetchFileRevision()
    {
        $this->expectRequest($this->uri . 'README.md?view=co&pathrev=1.0')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchFile('/README.md', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryRevision()
    {
        $this->expectRequest($this->uri . 'directory/?pathrev=1.0')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchDirectory('/directory/', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryAttic()
    {
        $this->expectRequest($this->uri . 'directory/?hideattic=0')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchDirectory('/directory/', null, true);

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryRevisionAttic()
    {
        $this->expectRequest($this->uri . 'directory/?pathrev=1.1&hideattic=0')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchDirectory('/directory/', '1.1', true);

        $this->expectPromiseReject($promise);
    }

    public function testFetchLogRevision()
    {
        $this->expectRequest($this->uri . 'README.md?view=log&pathrev=1.0')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchLog('/README.md', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchPatch()
    {
        $this->expectRequest($this->uri . 'README.md?view=patch&r1=1.0&r2=1.1')->will($this->returnValue(Promise\reject()));

        $promise = $this->client->fetchPatch('/README.md', '1.0', '1.1');

        $this->expectPromiseReject($promise);
    }

    private function expectRequest($uri)
    {
        $that = $this;

        return $this->sender->expects($this->once())->method('send')->with($this->callback(function (RequestInterface $request) use ($that, $uri) {
            $that->assertEquals('GET', $request->getMethod());
            $that->assertEquals($uri, $request->getUri());

            return true;
        }));
    }
}
