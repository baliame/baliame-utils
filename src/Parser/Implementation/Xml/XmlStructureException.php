<?php

namespace Baliame\Utils\Parser\Implementation\Xml;

use Baliame\Utils\Parser\ParserException;

class XmlStructureException extends ParserException
{
    /**
     * @var \DOMNode|null
     */
    protected $element;

    public function __construct($element, $message = 'Unknown parser error', $code = -1)
    {
        parent::__construct($message, $code);
        $this->element = $element;
    }

    /**
     * @return \DOMNode
     */
    public function getElement()
    {
        return $this->element;
    }


}
