<?php

use Clue\React\ViewVcApi\Io\Loader;

class LoaderTest extends TestCase
{
    private $loader;

    public function setUp()
    {
        $this->loader = new Loader();
    }

    /**
     * @dataProvider xmlFiles
     * @param string $path
     */
    public function testValidXmlFixtures($path)
    {
        $xml = $this->loader->loadXmlFile(__DIR__ . '/../fixtures/' . $path);
    }

    public function xmlFiles()
    {
        return array_filter(array_map(
            function ($path) {
                return (substr($path, -5) === '.html') ? array($path) : null;
            },
            scandir(__DIR__ . '/../fixtures/')
        ));
    }

    public function testHtmlEntities()
    {
        $str = '<p>&auml;&hellip;&nbsp;&copy;</p>';
        $xml = $this->loader->loadXmlString($str);

        // c3 a4 e2 80 a6 c2 a0 c2 a9
        $this->assertEquals('ä… ©', (string)$xml);
    }

    public function testLoadInvalidMarkupInputNotClosed()
    {
        $str = '<input type="hidden">';
        $xml = $this->loader->loadXmlString($str);

        $this->assertEquals('hidden', (string)$xml['type']);
    }

    public function testPrepareInvalidMarkupBrNotClosed()
    {
        $html = '<br>';
        $xml = $this->loader->loadXmlString($html);
    }

    public function testLoadInvalidMarkupSelectedAttributeNoValue()
    {
        $str = '<option selected>this</option>';
        $xml = $this->loader->loadXmlString($str);

        $this->assertEquals('selected', (string)$xml['selected']);
    }
}
