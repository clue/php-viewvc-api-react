<?php

use Clue\React\ViewVcApi\Io\Parser;
use Clue\React\ViewVcApi\Io\Loader;

class ParserTest extends TestCase
{
    private $loader;
    private $parser;

    public function setUp()
    {
        $this->loader = new Loader();
        $this->parser = new Parser();
    }

    public function testLogRevisions()
    {
        $xml = $this->loadXml('is-a-file.html');

        $revisions = $this->parser->parseLogRevisions($xml);

        $this->assertEquals(array('168703' => '168695', '168695' => '168694'), $revisions);
    }

    public function testDirectoryListing()
    {
        $xml = $this->loadXml('listing.html');

        $files = $this->parser->parseDirectoryListing($xml);

        $this->assertEquals(array('images/', 'stylesheets/', 'index.xml', 'velocity.properties'), $files);
    }

    private function loadXml($file)
    {
        return $this->loader->loadXmlFile(__DIR__ . '/../fixtures/' . $file);
    }
}
