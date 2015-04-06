<?php

namespace Clue\React\ViewVcApi\Io;

use SimpleXMLElement;

class Loader
{
    public function loadXmlFile($path)
    {
        return $this->loadXmlString(file_get_contents($path));
    }

    public function loadXmlString($html)
    {
        // fix invalid markup of help link in footer of outdated ViewVC versions
        $html = str_replace('Help</strong></td>', 'Help</a></strong></td>', $html);

        // replace unneeded HTML entities
        $html = str_replace('&nbsp;', ' ', $html);

        // clean up namespace declaration
        $html = str_replace('xmlns="', 'ns="', $html);

        return new SimpleXMLElement($html);
    }
}
