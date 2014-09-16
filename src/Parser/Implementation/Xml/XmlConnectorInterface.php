<?php

namespace Baliame\Utils\Parser\Implementation\Xml;

interface XmlConnectorInterface
{
    /**
     * @param string $rootElementName
     *   Root element name.
     * @return string|null
     *   The fully qualified path of the assigned class, or null if unassigned.
     */
    public function getClassNameForRoot($rootElementName);
}
