<?php

use Clue\React\ViewVcApi\Client;

class ClientTest extends TestCase
{
    private $url;
    private $browser;
    private $client;

    public function setUp()
    {
        $this->url = 'http://viewvc.example.org/';
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
}
