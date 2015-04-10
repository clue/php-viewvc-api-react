<?php

use Clue\React\ViewVcApi\Client;
use Clue\React\Buzz\Message\Response;
use Clue\React\Buzz\Message\Body;

class ClientTest extends TestCase
{
    private $url;
    private $browser;
    private $client;

    public function setUp()
    {
        $this->url = 'http://viewvc.example.org';
        $this->browser = $this->getMockBuilder('Clue\React\Buzz\Browser')->disableOriginalConstructor()->getMock();
        $this->client = new Client($this->url, $this->browser);
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
        $response = new Response('HTTP/1.0', 200, 'OK', null, new Body('# hello'));
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/README.md?view=co'))->will($this->returnValue($this->createPromiseResolved($response)));

        $promise = $this->client->fetchFile('README.md');

        $this->expectPromiseResolveWith('# hello', $promise);
    }

    public function testFetchFileExcessiveSlashesAreIgnored()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/README.md?view=co'))->will($this->returnValue($this->createPromiseRejected()));

        $client = new Client($this->url . '/', $this->browser);
        $promise = $client->fetchFile('/README.md');

        $this->expectPromiseReject($promise);
    }

    public function testFetchFileRevision()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/README.md?view=co&pathrev=1.0'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchFile('/README.md', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryRevision()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/directory/?pathrev=1.0'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchDirectory('/directory/', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryAttic()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/directory/?hideattic=0'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchDirectory('/directory/', null, true);

        $this->expectPromiseReject($promise);
    }

    public function testFetchDirectoryRevisionAttic()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/directory/?pathrev=1.1&hideattic=0'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchDirectory('/directory/', '1.1', true);

        $this->expectPromiseReject($promise);
    }

    public function testFetchLogRevision()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/README.md?view=log&pathrev=1.0'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchLog('/README.md', '1.0');

        $this->expectPromiseReject($promise);
    }

    public function testFetchPatch()
    {
        $this->browser->expects($this->once())->method('get')->with($this->equalTo('http://viewvc.example.org/README.md?view=patch&r1=1.0&r2=1.1'))->will($this->returnValue($this->createPromiseRejected()));

        $promise = $this->client->fetchPatch('/README.md', '1.0', '1.1');

        $this->expectPromiseReject($promise);
    }
}
