<?php

namespace Clue\React\ViewVcApi;

class Parser
{
    public function parseDirectoryListing($xml)
    {
        $files = array();

        // iterate over all anchor elements with an href attribute
        foreach ($xml->xpath('//tr/td/a[@name]') as $anchor) {
            $name = (string)$anchor['name'];

            // append slash to directory names
            $href = (string)$anchor['href'];
            if (substr($href, -1) === '/' || strpos($href, '/?') !== false) {
                $name .= '/';
            }

            $files []= $name;
        }

        return $files;
    }

    public function parseLogRevisions($xml)
    {
        $revisions = array();

        // iterate over all anchor elements with an href attribute
        foreach ($xml->xpath('//a[@href]') as $anchor) {
            // text label of anchor element
            $text = trim((string)$anchor);

            // only look for links to previous revision
            if (substr($text, 0, 8) !== 'previous') {
                continue;
            }

            // href contains r1 and r2 as query parameters
            $href = (string)$anchor['href'];
            $pos = strpos($href, '?');
            if ($pos === false) {
                continue;
            }

            $args = array();
            parse_str(substr($href, $pos + 1), $args);

            // all links containing r2 are links to previous revision
            if (isset($args['r2'])) {
                $revisions[$args['r2']] = $args['r1'];
            }
        }

        return $revisions;
    }
}
