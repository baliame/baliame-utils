<?php

namespace Baliame\Utils\Putter\Implementation\Xml;

use Baliame\Utils\Parser\ParserInterface;
use Baliame\Utils\Putter\PutterInterface;

interface XmlSerializableInterface
{
    const LANGUAGE_DEFAULT = 'x-default';
    const LANGUAGE_UNDEFINED = 'und';

    /**
     * Renders the data structure as DOMDocument object.
     *
     * @param PutterInterface $putter
     * @return \DOMDocument
     */
    public function asXmlObject(PutterInterface $putter);

    /**
     * Renders the data structure as XML.
     *
     * @return string
     */
    public function asXml();

    /**
     * Returns the tag name of the XML node represented by this object.
     *
     * @return string
     */
    public function getTagName();

    /**
     * Parses XML into an instance of this class.
     *
     * @param ParserInterface $parser
     *
     * @return XmlSerializableInterface
     */
    public static function parse(ParserInterface $parser);
}
