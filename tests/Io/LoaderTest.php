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
}
