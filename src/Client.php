<?php

namespace Clue\React\ViewVcApi;

use RuntimeException;
use UnderflowException;
use SimpleXMLElement;
use InvalidArgumentException;
use Clue\React\Buzz\Browser;
use Clue\React\Buzz\Message\Response;
use React\Promise\Deferred;
use Clue\React\ViewVcApi\Io\Parser;
use Clue\React\ViewVcApi\Io\Loader;

class Client
{
    private $url;
    private $brower;

    public function __construct($url, Browser $browser, Parser $parser = null, Loader $loader = null)
    {
        if ($parser === null) {
            $parser = new Parser();
        }
        if ($loader === null) {
            $loader = new Loader();
        }

        // TODO: do not follow redirects
//         $browser = $this->browser->withOptions(array(
//             'follow_redirects' => false
//         ));

        $this->url = $url;
        $this->browser = $browser;
        $this->parser = $parser;
        $this->loader = $loader;
    }

    public function fetchFile($path, $revision = null)
    {
        if (substr($path, -1) === '/') {
            return $this->reject(new InvalidArgumentException('File path MUST NOT end with trailing slash'));
        }

        $url = $path . '?view=co';
        if ($revision !== null) {
            $url .= '&revision=' . $revision;
        }

        // TODO: fetching a directory redirects to path with trailing slash
        // TODO: status returns 200 OK, but displays an error message anyways..
        // TODO: see not-a-file.html
        // TODO: reject all paths with trailing slashes

        return $this->fetch($url);
    }

    public function fetchDirectory($path, $revision = null)
    {
        if (substr($path, -1) !== '/') {
            return $this->reject(new InvalidArgumentException('Directory path MUST end with trailing slash'));
        }

        $url = $path;

        if ($revision !== null) {
            $url .= '?pathrev=' . $revision;
        }

        // TODO: path MUST end with trailing slash
        // TODO: accessing files will redirect to file with relative location URL (not supported by clue/buzz-react)

        return $this->fetchXml($url)->then(function (SimpleXMLElement $xml) {
            // TODO: reject if this is a file, instead of directory => contains "Log of" instead of "Index of"
            // TODO: see is-a-file.html

            return $xml;
        })->then(array($this->parser, 'parseDirectoryListing'));
    }

    public function fetchPatch($path, $r1, $r2)
    {
        $url = $path . '?view=patch&r1=' . $r1 . '&r2=' . $r2;

        return $this->fetch($url);
    }

    public function fetchRevisionPrevious($path, $revision)
    {
        return $this->fetchAllPreviousRevisions($path)->then(function ($revisions) use ($revision) {
            if (!isset($revisions[$revision])) {
                throw new UnderflowException('Unable to find previous version of given revision');
            }

            return $revisions[$revision];
        });
    }

    public function fetchAllPreviousRevisions($path)
    {
        return $this->fetchLogXml($path)->then(array($this->parser, 'parseLogRevisions'));
    }

    private function fetchLogXml($path)
    {
        $url = $path . '?view=log';

        return $this->fetchXml($url);
    }

    private function fetchXml($url)
    {
        return $this->fetch($url)->then(array($this->loader, 'loadXmlString'));
    }

    private function fetch($url)
    {
        return $this->browser->get($this->url . $url)->then(
            function (Response $response) {
                return (string)$response->getBody();
            },
            function ($error) {
                throw new RuntimeException('Unable to fetch from ViewVC', 0, $error);
            }
        );
    }

    private function reject($with)
    {
        $deferred = new Deferred();
        $deferred->reject($with);

        return $deferred->promise();
    }
}
