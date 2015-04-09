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

    public function testLogSubversion()
    {
        $xml = $this->loadXml('is-a-file.html');

        $log = $this->parser->parseLogEntries($xml);

        $this->assertCount(3, $log);

        // second entry has previous
        $entry = $log[1];
        $this->assertEquals('168695', $entry['revision']);
        $this->assertEquals(new DateTime('Tue Jun 22 02:15:08 1999 UTC'), $entry['date']);
        $this->assertEquals('(unknown author)', $entry['author']);
        $this->assertEquals('168694', $entry['previous']);
        $this->assertEquals('jakarta/ecs/branches/ecs/src/java/org/apache/ecs/AlignType.java', $entry['original']);
        $this->assertEquals(4131, $entry['size']);

        // last entry has no previous
        $entry = $log[2];
        $this->assertEquals('168694', $entry['revision']);
        $this->assertEquals(new DateTime('Tue Jun 22 02:15:08 1999 UTC'), $entry['date']);
        $this->assertEquals('jonbolt', $entry['author']);
        $this->assertFalse(isset($entry['previous']));
    }

    public function testLogCvsBranches()
    {
        $xml = $this->loadXml('log-file-cvs-branches.html');

        $log = $this->parser->parseLogEntries($xml);

        $this->assertCount(4, $log);

        // second entry is on vendor branch
        $entry = $log[1];
        $this->assertEquals('1.1.1.2', $entry['revision']);
        $this->assertEquals(new DateTime('Tue Nov 5 05:48:37 2002 UTC'), $entry['date']);
        $this->assertEquals('abcosico', $entry['author']);
        $this->assertEquals('1.1.1.1', $entry['previous']);
        $this->assertEquals(array('ICIS', 'IRRI', 'MAIN', 'avendor', 'bbu'), $entry['branches']);
        $this->assertEquals(array('arelease', 'v1'), $entry['tags']);
        $this->assertTrue(isset($entry['vendor']));

        // last entry is branchpoint for all branches and is not on vendor branch
        $entry = $log[3];
        $this->assertEquals('1.1', $entry['revision']);
        $this->assertEquals(array('ICIS', 'IRRI', 'MAIN', 'avendor', 'bbu'), $entry['branchpoints']);
        $this->assertFalse(isset($entry['vendor']));
    }

    public function testLogCvsDeleted()
    {
        $xml = $this->loadXml('log-file-cvs-deleted.html');

        $log = $this->parser->parseLogEntries($xml);

        $this->assertCount(6, $log);

        // second entry is a delete
        $entry = $log[1];
        $this->assertEquals('1.5', $entry['revision']);
        $this->assertEquals('1.4', $entry['previous']);
        $this->assertTrue($entry['deleted']);
    }

    private function loadXml($file)
    {
        return $this->loader->loadXmlFile(__DIR__ . '/../fixtures/' . $file);
    }
}
