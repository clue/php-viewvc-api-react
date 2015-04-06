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
        // clean up HTML to safe XML
        $html = tidy_repair_string($html, array(
            'output-xml' => true,
            'input-xml'  => true,
        ));

        // clean up namespace declaration
        $html = str_replace('xmlns="', 'ns="', $html);

        return new SimpleXMLElement($html);
    }
}
