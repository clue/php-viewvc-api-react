<?php

namespace Clue\React\ViewVcApi;

use RuntimeException;
use UnderflowException;
use SimpleXMLElement;
use InvalidArgumentException;
use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\Promise;
use Clue\React\ViewVcApi\Io\Parser;
use Clue\React\ViewVcApi\Io\Loader;
use Rize\UriTemplate;
use Clue\React\Promise\Stream;

class Client
{
    private $brower;
    private $parser;
    private $loader;
    private $uri;

    public function __construct(Browser $browser, Parser $parser = null, Loader $loader = null, UriTemplate $uri = null)
    {
        if ($parser === null) {
            $parser = new Parser();
        }
        if ($loader === null) {
            $loader = new Loader();
        }
        if ($uri === null) {
            $uri = new UriTemplate();
        }

        // TODO: do not follow redirects
//         $browser = $this->browser->withOptions(array(
//             'follow_redirects' => false
//         ));

        $this->browser = $browser;
        $this->parser = $parser;
        $this->loader = $loader;
        $this->uri = $uri;
    }

    public function fetchFile($path, $revision = null)
    {
        if (substr($path, -1) === '/') {
            return Promise\reject(new InvalidArgumentException('File path MUST NOT end with trailing slash'));
        }

        // TODO: fetching a directory redirects to path with trailing slash
        // TODO: status returns 200 OK, but displays an error message anyways..
        // TODO: see not-a-file.html
        // TODO: reject all paths with trailing slashes

        return $this->fetch(
            $this->uri->expand(
                '{+path}?view=co{&pathrev}',
                array(
                    'path' => $path,
                    'pathrev' => $revision
                )
            )
        );
    }

    /**
     * Reads the file contents of the given file path as a readable stream
     *
     * This works for files of arbitrary sizes as only small chunks have to
     * be kept in memory. The resulting stream is a well-behaving readable stream
     * that will emit the normal stream events.
     *
     * @param string      $path
     * @param string|null $revision
     * @return ReadableStreamInterface
     * @throws InvalidArgumentException
     * @see self::fetchFile()
     */
    public function fetchFileStream($path, $revision = null)
    {
        if (substr($path, -1) === '/') {
            throw new InvalidArgumentException('File path MUST NOT end with trailing slash');
        }

        // TODO: fetching a directory redirects to path with trailing slash
        // TODO: status returns 200 OK, but displays an error message anyways..
        // TODO: see not-a-file.html
        // TODO: reject all paths with trailing slashes

        return Stream\unwrapReadable(
            $this->browser->withOptions(array('streaming' => true))->get(
                $this->uri->expand(
                    '{+path}?view=co{&pathrev}',
                    array(
                        'path' => $path,
                        'pathrev' => $revision
                    )
                )
            )->then(function (ResponseInterface $response) {
                // the body implements ReadableStreamInterface, so let's just return this to the unwrapper
                return $response->getBody();
            })
        );
    }

    public function fetchDirectory($path, $revision = null, $showAttic = false)
    {
        if (substr($path, -1) !== '/') {
            return Promise\reject(new InvalidArgumentException('Directory path MUST end with trailing slash'));
        }

        // TODO: path MUST end with trailing slash
        // TODO: accessing files will redirect to file with relative location URL (not supported by clue/buzz-react)

        return $this->fetchXml(
            $this->uri->expand(
                '{+path}{?pathrev,hideattic}',
                array(
                    'path' => $path,
                    'pathrev' => $revision,
                    'hideattic' => $showAttic ? '0' : null
                )
            )
        )->then(function (SimpleXMLElement $xml) {
            // TODO: reject if this is a file, instead of directory => contains "Log of" instead of "Index of"
            // TODO: see is-a-file.html

            return $xml;
        })->then(array($this->parser, 'parseDirectoryListing'));
    }

    public function fetchPatch($path, $r1, $r2)
    {
        return $this->fetch(
            $this->uri->expand(
                '{+path}?view=patch{&r1,r2}',
                array(
                    'path' => $path,
                    'r1' => $r1,
                    'r2' => $r2
                )
            )
        );
    }

    public function fetchLog($path, $revision = null)
    {
        // TODO: invalid revision shows error page, but HTTP 200 OK

        return $this->fetchXml(
            $this->uri->expand(
                '{+path}?view=log{&pathrev}',
                array(
                    'path' => $path,
                    'pathrev' => $revision
                )
            )
        )->then(array($this->parser, 'parseLogEntries'));
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
        return $this->fetchXml(
            $this->uri->expand(
                '{+path}?view=log',
                array(
                    'path' => $path
                )
            )
        );
    }

    private function fetchXml($url)
    {
        return $this->fetch($url)->then(array($this->loader, 'loadXmlString'));
    }

    private function fetch($url)
    {
        return $this->browser->get($url)->then(
            function (ResponseInterface $response) {
                return (string)$response->getBody();
            },
            function ($error) {
                throw new RuntimeException('Unable to fetch from ViewVC', 0, $error);
            }
        );
    }
}
