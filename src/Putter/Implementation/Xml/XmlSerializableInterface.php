<?php

namespace Baliame\Utils\Putter\Implementation\Xml;

interface XmlSerializableInterface
{
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
}
