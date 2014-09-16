<?php

namespace Baliame\Utils\Parser;

abstract class ParserException extends \Exception
{
    const CODE_MULTIPLE_FOUND = 1;
    const CODE_NOT_FOUND = 2;
    const CODE_ROOT_MISMATCH = 17;
    /**
     * @var Object
     */
    protected $element;

    /**
     * @return Object
     */
    public function getElement()
    {
        return $this->element;
    }
}
