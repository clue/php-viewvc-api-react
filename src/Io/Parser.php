<?php

namespace Clue\React\ViewVcApi\Io;

use SimpleXMLElement;

class Parser
{
    public function parseDirectoryListing(SimpleXMLElement $xml)
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

    public function parseLogRevisions(SimpleXMLElement $xml)
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
            $args = $this->linkParameters((string)$anchor['href']);

            // all links containing r2 are links to previous revision
            if (isset($args['r2'])) {
                $revisions[$args['r2']] = $args['r1'];
            }
        }

        return $revisions;
    }

    /**
     * Parse log entries from given XML document
     *
     * @param SimpleXMLElement $xml
     * @throws \UnexpectedValueException
     * @return array
     * @link https://gforge.inria.fr/scm/viewvc/viewvc.org/template-authoring-guide.html#variables-log
     */
    public function parseLogEntries(SimpleXMLElement $xml)
    {
        $entries = array();

        foreach ($xml->xpath('//div[pre]') as $div) {
            /* @var $div SimpleXMLElement */

            // skip "(vendor branch)" "em" tag if found
            $off = ((string)$div->em[0] === '(vendor branch)') ? 1 : 0;

            $entry = array(
                // revision is first "strong" element (subversion wraps this in "a" element)
                'revision' => (string)$this->first($div->xpath('.//strong[1]')),
                // date is in first "em" element
                'date' => new \DateTime((string)$div->em[0 + $off]),
                // author is in second "em" element
                'author' => (string)$div->em[1 + $off],
                // message is in only "pre" element
                'message' => (string)$div->pre
            );

            // ease parsing each line by splitting on "br" element, skip static rows for revision/date
            $parts = explode('<br />', substr($div->asXML(), 5, -6));
            unset($parts[0], $parts[1]);

            foreach ($parts as $part) {
                $part = new SimpleXMLElement('<body>' . $part . '</body>');
                $str = (string)$part;

                if (substr($str, 0, 7) === 'Diff to') {
                    $value = array();

                    foreach ($part->xpath('.//a') as $a) {
                        $text = (string)$a;
                        $pos = strrpos($text, ' ');

                        // text should be "previous X.Y", otherwise ignore "(colored)" with no blank
                        if ($pos !== false) {
                            $value[substr($text, 0, $pos)] = substr($text, $pos + 1);
                        }
                    }

                    $entry['diff'] = $value;
                } elseif (substr($str, 0, 7) === 'Branch:' || substr($str, 0, 9) === 'CVS Tags:' || substr($str, 0, 17) === 'Branch point for:') {
                    $value = array();

                    foreach ($part->xpath('.//a/strong') as $a) {
                        $value []= (string)$a;
                    }

                    $key = $str[0] === 'B' ? ($str[6] === ':' ? 'branches' : 'branchpoints') : 'tags';
                    $entry[$key] = $value;
                } elseif (substr($str, 0, 13) === 'Changes since') {
                    // "strong" element contains "X.Y: +1 -2 lines"
                    $value = (string)$part->strong;
                    $pos = strpos($value, ':');

                    // previous revision is before colon
                    $entry['previous'] = substr($value, 0, $pos);

                    // changes are behind colon
                    $entry['changes'] = substr($value, $pos + 2);
                } elseif (substr($str, 0, 14) === 'Original Path:') {
                    $entry['original'] = (string)$part->a->em;
                } elseif (substr($str, 0, 12) === 'File length:') {
                    $entry['size'] = (int)substr($str, 13);
                } elseif (isset($part->strong->em) && (string)$part->strong->em === 'FILE REMOVED') {
                    $entry['deleted'] = true;
                }
            }

            // previous is either set via "changes since" or link to "diff to" previous
            if (isset($entry['diff']['previous'])) {
                $entry['previous'] = $entry['diff']['previous'];
            }

            if ($off) {
                $entry['vendor'] = true;
            }

            $entries []= $entry;
        }

        return $entries;
    }

    private function first(array $a)
    {
        return $a[0];
    }

    private function linkParameters($href)
    {
        $args = array();
        $pos = strpos($href, '?');

        if ($pos !== false) {
            parse_str(substr($href, $pos + 1), $args);
        }

        return $args;
    }
}
