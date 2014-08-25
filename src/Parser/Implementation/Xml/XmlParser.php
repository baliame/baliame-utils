<?php

namespace Baliame\Utils\Parser\Implementation\Xml;
use Baliame\Utils\Interoperability\TypeNormalization;
use Baliame\Utils\Parser\ParserException;
use Baliame\Utils\Parser\ParserInterface;
use Baliame\Utils\Putter\Implementation\Xml\XmlSerializableInterface;

/**
 * Parses raw XML files produces by Demandware into an OO representation.
 */
class XmlParser implements ParserInterface
{
    /**
     * @var \DOMNode
     */
    protected $current;

    /**
     * @var \DOMNode
     */
    protected $baseNode;

    /**
     * Constructs the parser and optionally checks if the base tag matches an expected one.
     *
     * @param \DOMNode $baseNode
     *   The node being parsed.
     * @param $nodeName
     *   The expected tagname of the node, or null to skip this checking.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\DOMNode $baseNode, $nodeName = null)
    {
        if (empty($baseNode)) {
            throw new \InvalidArgumentException('Base node of a parser cannot be null.');
        }
        $this->current = $baseNode;
        if ($nodeName !== null) {
            $this->checkNodeName($nodeName);
        }
        $this->baseNode = $baseNode;
    }

    /**
     * Parser safeguard check; here to ease the code coverage strain for 100%
     * tested.
     *
     * Basically, compares the tagName of the provided DOMElement with a string.
     *
     * @param string $expectedTagName
     *   The expected tag name.
     * @param bool $graceful
     *   If true, fail gracefully and do not throw exceptions.
     *
     * @return true
     *
     * @throws XmlStructureException
     *   If the root tagName does not match the provided string, this exception
     *   is thrown (if not set to fail gracefully).
     */
    public function checkNodeName($expectedTagName, $graceful = false)
    {
        if ($this->current->nodeName != $expectedTagName) {
            if ($graceful) {
                return false;
            }
            $this->raiseException(
                'Expected root node "' . $expectedTagName . '", observed "' . $this->current->nodeName . '"',
                ParserException::CODE_ROOT_MISMATCH
            );
        }
        return true;
    }

    /**
     * Function used to raise the appropriate exception for the parser
     * implementation.
     *
     * @param $message
     * @param int $code
     * @throws XmlStructureException
     */
    public function raiseException($message, $code = -1)
    {
        throw new XmlStructureException($this->current, $message, $code);
    }

    /**
     * Parses raw XML into the object-oriented representation of the Demandware
     * entities.
     *
     * @param string $xml
     * @param XmlConnectorInterface $connector
     *
     * @return object
     *
     * @throws XmlStructureException
     */
    public static function load($xml, $connector)
    {
        // Instantiate the \DOMDocument object from the raw XML string.
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        if (!@$document->loadXML($xml)) {
            throw new XmlStructureException(null, 'Unable to load the XML from string.');
        }

        // Get the root node so that we know which entities are containes in the
        // XML file, see the static $baseTagAssignment property.

        /**
         * @var \DOMElement $rootNode
         */
        $rootNode = $document->childNodes->item(0);
        if ($className = $connector->getClassNameForRoot($rootNode->tagName)) {
            $parser = new static($rootNode);
            return call_user_func(array($className, 'parse'), $parser);
        } else {
            throw new XmlStructureException(
                $rootNode, 'Entity collection class not mapped for root node: ' . $rootNode->tagName
            );
        }
    }

    /**
     * Parses the text from the current node.
     *
     * @return string
     */
    public function parseText()
    {
        return $this->current->nodeValue;
    }

    /**
     * Moves the parser back to the original node it was constructed with.
     */
    public function reset()
    {
        $this->current = $this->baseNode;
    }

    /**
     * Returns true if a child element exists.
     *
     * @param string $tagName
     *
     * @return bool
     */
    public function childExists($tagName)
    {
        $childNodes = $this->getChildNodes($tagName);
        return count($childNodes) > 0;
    }

    /**
     * Returns all child elements matching the tag name.
     *
     * NOTE: We don't use \DOMElement::getElementsByTagName() because it
     * recurses into the child nodes whereas we only want to get the direct
     * descendants that match the passed tag name.
     *
     * @param string $tagName
     *
     * @return \DOMNode[]
     */
    public function getChildNodes($tagName)
    {
        $childNodes = array();

        foreach ($this->current->childNodes as $childNode) {
            if ($childNode->nodeName == $tagName) {
                $childNodes[] = $childNode;
            }
        }

        return $childNodes;
    }

    /**
     * Returns true if an attribute exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function attributeExists($name)
    {
        if ($this->current instanceof \DOMElement) {
            /**
             * @var \DOMElement $current
             */
            $current = $this->current;
            return $current->hasAttribute($name);
        }
        return false;
    }

    /**
     * Returns an associative array of values keyed by language.
     *
     * @param string $tagName
     *
     * @return array
     *   An associative array of values keyed by language.
     *
     * @throws XmlStructureException
     */
    public function parseMultipleStringsByLanguage($tagName)
    {
        $values = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                if (!$childNode->hasAttribute('xml:lang')) {
                    $language = XmlSerializableInterface::LANGUAGE_UNDEFINED;
                } else {
                    $language = $childNode->getAttribute('xml:lang');
                }
                $values[$language] = $childNode->nodeValue;
            }
        }

        return $values;
    }

    /**
     * Returns an associative array of objects keyed by language.
     *
     * @param string $tagName
     * @param string $handlerClass
     *   The class in charge of parsing the objects.
     * @param array $additionalParameters
     *   An array of additional parameters to pass to the parse function.
     * @param string $parserFunction
     *
     * @return array
     *   An associative array of objects keyed by language.
     *
     * @throws XmlStructureException
     */
    public function parseMultipleObjectsByLanguage(
        $tagName,
        $handlerClass = '',
        $additionalParameters = array(),
        $parserFunction = 'parse'
    ) {
        $values = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                if (!$childNode->hasAttribute('xml:lang')) {
                    $language = XmlSerializableInterface::LANGUAGE_UNDEFINED;
                } else {
                    $language = $childNode->getAttribute('xml:lang');
                }

                $this->setCurrentNode($childNode);
                $arguments = array_merge([$this], $additionalParameters);
                try {
                    $values[$language] = call_user_func_array([$handlerClass, $parserFunction], $arguments);
                } catch (XmlStructureException $e) {
                    $this->returnToParent();
                    throw $e;
                }
                $this->returnToParent();
            }
        }

        return $values;
    }

    /**
     * Sets the current base node.
     *
     * @param $node \DOMNode
     */
    public function setCurrentNode($node)
    {
        $this->current = $node;
    }

    /**
     * Moves the base node one level up.
     */
    public function returnToParent()
    {
        if ($this->current->parentNode) {
            $this->current = $this->current->parentNode;
        }
    }

    /**
     * Returns the boolean representation of the child node, throws an exception
     * if the child node is missing.
     *
     * @param string $tagName
     *
     * @return bool|null
     *   The boolean representation child node, null if the child is missing.
     *
     * @throws XmlStructureException
     */
    public function parseSingleRequiredBoolean($tagName)
    {
        $boolean = $this->parseSingleOptionalBoolean($tagName);
        if ($boolean === null) {
            throw new XmlStructureException($this->current, 'Missing required child node: ' . $tagName);
        }

        return $boolean;
    }

    /**
     * Returns the boolean representation of the child node, null if the child
     * node is missing.
     *
     * @param string $tagName
     * @param mixed $default
     *   A default value if the value is not found.
     *
     * @return bool|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleOptionalBoolean($tagName, $default = null)
    {
        $boolean = null;

        $string = static::parseSingleOptionalString($tagName);
        if ($string !== false) {
            $boolean = TypeNormalization::normalizeStringToBoolean($string);
        }

        if ($boolean === null && $default !== null) {
            $boolean = $default;
        }

        return $boolean;
    }

    /**
     * Returns the value of the child node, false if the child node is missing.
     *
     * @param string $tagName
     * @param mixed $default
     *
     * @return string|false
     *   The value of the child node, false of the value does not exist.
     *
     * @throws XmlStructureException
     */
    public function parseSingleOptionalString($tagName, $default = null)
    {
        $string = null;

        try {
            $childNode = $this->getSingleChild($tagName);
            $string = $childNode->nodeValue;
        } catch (XmlStructureException $e) {
            if ($e->getCode() == ParserException::CODE_MULTIPLE_FOUND) {
                throw $e;
            }
            $string = $default;
        }

        return $string;
    }

    /**
     * Returns the float value of the child node, throws an exception if the
     * child node is missing.
     *
     * @param string $tagName
     *
     * @return float|null
     *   The float representation child node, null if the child is missing.
     *
     * @deprecated since version 0.1.8 - all floats are now strings
     *
     * @see https://github.com/acquia/demandware-cartridge-client-php/issues/30
     */
    public function parseSingleRequiredFloat($tagName)
    {
        return $this->parseSingleRequiredString($tagName);
    }

    /**
     * Returns the value of the child node, an exception is thrown if the child
     * node is missing.
     *
     * @param string $tagName
     *
     * @return string
     *   The value of the child node.
     *
     * @throws XmlStructureException
     */
    public function parseSingleRequiredString($tagName)
    {
        $value = $this->parseSingleOptionalString($tagName);
        if ($value === false) {
            throw new XmlStructureException($this->current, 'Missing required child node: ' . $tagName);
        }

        return $value;
    }

    /**
     * Returns the float value of the child node, false if the child node is
     * missing.
     *
     * @param string $tagName
     * @param mixed $default
     *
     * @return float|null
     *   The float representation child node, null if the child is missing.
     *
     * @deprecated since version 0.1.8 - all floats are now strings
     *
     * @see https://github.com/acquia/demandware-cartridge-client-php/issues/30
     */
    public function parseSingleOptionalFloat($tagName, $default = null)
    {
        return $this->parseSingleOptionalString($tagName, $default);
    }

    /**
     * Returns an object modeling the data in the child node, throws an
     * exception if the child node is missing.
     *
     * @param string $tagName
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return mixed
     *   An instance of $handlerClass, false if the child node is missing.
     *
     * @throws XmlStructureException
     */
    public function parseSingleRequiredObject(
        $tagName,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    ) {
        $object = $this->parseSingleOptionalObject($tagName, $handlerClass, $additionalParameters, $parserFunction);
        if ($object === false) {
            $this->raiseException('Missing required child node: ' . $tagName, ParserException::CODE_NOT_FOUND);
        }

        return $object;
    }

    /**
     * Returns an object modeling the data in the child node.
     *
     * @param string $tagName
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param mixed $default
     * @param string $parserFunction
     *
     * @return mixed
     *   An instance of $handlerClass, $default if the child node is missing.
     *
     * @throws XmlStructureException
     */
    public function parseSingleOptionalObject(
        $tagName,
        $handlerClass = '',
        array $additionalParameters = array(),
        $default = null,
        $parserFunction = 'parse'
    ) {
        $object = false;

        $original = $this->current;

        try {
            $this->proceedToSingleChild($tagName);
            $arguments = array_merge(array($this), $additionalParameters);
            try {
                $object = call_user_func_array(array($handlerClass, $parserFunction), $arguments);
            } catch (XmlStructureException $e) {
                $this->returnToParent();
                throw $e;
            }
            $this->returnToParent();
        } catch (XmlStructureException $e) {
            if ($e->getCode() !== ParserException::CODE_NOT_FOUND || $e->getElement() !== $original) {
                throw $e;
            }
        }

        if ($object === false) {
            $object = $default;
        }

        return $object;
    }

    /**
     * Moves the base node to the single child with the provided name.
     *
     * @param $tagName
     *
     * @throws XmlStructureException
     */
    public function proceedToSingleChild($tagName)
    {
        $element = $this->getSingleChild($tagName);
        $this->current = $element;
    }

    /**
     * Returns the first child element matching the tag name.
     *
     * NOTE: We don't use \DOMElement::getElementsByTagName() because it
     * recurses into the child nodes whereas we only want to get the direct
     * descendants that match the passed tag name.
     *
     * @param string $tagName
     *
     * @return \DOMNode
     *
     * @throws XmlStructureException
     */
    public function getSingleChild($tagName)
    {
        $childNodes = $this->getChildNodes($tagName);
        $numNodes = count($childNodes);

        if (!$numNodes) {
            $this->raiseException('Child node not found: ' . $tagName, ParserException::CODE_NOT_FOUND);
        } elseif ($numNodes > 1) {
            $this->raiseException('Multiple child nodes found: ' . $tagName, ParserException::CODE_MULTIPLE_FOUND);
        } else {
            return $childNodes[0];
        }
        return null;
    }

    /**
     * Returns objects modeling the data in all child nodes matching the tag
     * name.
     *
     * @param string $tagName
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return array
     *   Instances of $handlerClass
     *
     * @throws XmlStructureException
     */
    public function parseMultipleObjects(
        $tagName,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    ) {
        $objects = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            $this->setCurrentNode($childNode);
            $arguments = array_merge(array($this), $additionalParameters);
            try {
                $objects[] = call_user_func_array(array($handlerClass, $parserFunction), $arguments);
            } catch (XmlStructureException $e) {
                $this->returnToParent();
                throw $e;
            }
            $this->returnToParent();
        }

        return $objects;
    }

    /**
     * Returns objects modeling the data in all child nodes matching the tag
     * name, keyed by an appropriate attribute.
     *
     * @param string $tagName
     * @param string $attributeName
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return array
     *   Instances of $handlerClass, keyed by attribute $attributeName
     *
     * @throws XmlStructureException
     */
    public function parseMultipleObjectsByAttribute(
        $tagName,
        $attributeName,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    ) {
        $objects = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                $this->setCurrentNode($childNode);
                $arguments = array_merge(array($this), $additionalParameters);
                try {
                    $objects[$childNode->getAttribute($attributeName)] = call_user_func_array(
                        array($handlerClass, $parserFunction),
                        $arguments
                    );
                } catch (XmlStructureException $e) {
                    $this->returnToParent();
                    throw $e;
                }
                $this->returnToParent();
            }
        }

        return $objects;
    }

    /**
     * Returns an array of values from child nodes.
     *
     * @param string $tagName
     *
     * @return array
     */
    public function parseMultipleStrings($tagName)
    {
        $values = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            $values[] = $childNode->nodeValue;
        }

        return $values;
    }

    /**
     * Returns an attribute's values across all child nodes matching the tag
     * name.
     *
     * @param string $tagName
     * @param string $attributeName
     *
     * @return array
     */
    public function parseMultipleAttributeStrings($tagName, $attributeName)
    {
        $values = array();

        $childNodes = $this->getChildNodes($tagName);
        foreach ($childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                $values[] = $childNode->getAttribute($attributeName);
            }
        }

        return $values;
    }

    /**
     * Returns the boolean representation of an attribute, returns a default
     * value if the attribute is not set.
     *
     * @param string $attributeName
     * @param bool|null $default
     *   The default value returned if the attribute is not set.
     *
     * @return bool|null
     *   The boolean representation child node, null if the attribute is missing.
     *
     * @throws \Exception
     */
    public function parseOptionalBooleanAttribute($attributeName, $default = null)
    {
        if (!($this->current instanceof \DOMElement)) {
            throw new \Exception('Invalid state: current node does not support attributes.');
        }
        /**
         * @var \DOMElement $current
         */
        $current = $this->current;
        if ($current->hasAttribute($attributeName)) {
            $value = TypeNormalization::normalizeStringToBoolean($current->getAttribute($attributeName));
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Returns the boolean representation of an attribute, throws an exception if not set.
     *
     * @param string $attributeName
     *
     * @return bool|null
     *   The boolean representation child node.
     *
     * @throws \Exception
     */
    public function parseRequiredBooleanAttribute($attributeName)
    {
        if (!($this->current instanceof \DOMElement)) {
            throw new \Exception('Invalid state: current node does not support attributes.');
        }
        /**
         * @var \DOMElement $current
         */
        $current = $this->current;
        if ($current->hasAttribute($attributeName)) {
            $value = TypeNormalization::normalizeStringToBoolean($current->getAttribute($attributeName));
        } else {
            throw new XmlStructureException("Missing boolean attribute: $attributeName");
        }

        return $value;
    }

    /**
     * Returns an attribute if set, throws and exception otherwise.
     *
     * @param string $attributeName
     *
     * @return string
     *   The value of the attribute, null if it is not set.
     *
     * @throws XmlStructureException|\Exception
     */
    public function parseRequiredAttribute($attributeName)
    {
        $value = $this->parseOptionalAttribute($attributeName);
        if ($value === null) {
            throw new XmlStructureException($this->getCurrentNode(), "Missing required attribute: $attributeName");
        }
        return $value;
    }

    /**
     * Returns an attribute if set, NULL otherwise.
     *
     * @param string $attributeName
     * @param string $default
     *
     * @return string|null
     *   The value of the attribute, null if it is not set.
     *
     * @throws \Exception
     */
    public function parseOptionalAttribute($attributeName, $default = null)
    {
        $value = null;
        if (!($this->current instanceof \DOMElement)) {
            throw new \Exception('Invalid state: current node does not support attributes.');
        }
        /**
         * @var \DOMElement $current
         */
        $current = $this->current;
        if ($current->hasAttribute($attributeName)) {
            $value = $current->getAttribute($attributeName);
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Returns the current base node.
     *
     * @return \DOMNode
     */
    public function getCurrentNode()
    {
        return $this->current;
    }

    /**
     * Parses the language of the current node. Returns LANGUAGE_UNDEFINED if
     * not set.
     *
     * @return string
     */
    public function parseLanguage()
    {
        $lang = $this->parseOptionalAttribute('xml:lang');
        if ($lang === null) {
            return XmlSerializableInterface::LANGUAGE_UNDEFINED;
        } else {
            return $lang;
        }
    }

    /**
     * Gets the count of child nodes on the current node.
     *
     * @return int
     */
    public function getChildCount()
    {
        return $this->current->childNodes->length;
    }

    /**
     * Gets the nth child node, indexed from 0.
     * If $advance is true, also advances the position.
     *
     * @param int $pos
     * @param bool $advance
     * @return \DOMNode
     */
    public function getChildNodeByPosition($pos, $advance = false)
    {
        $item = $this->current->childNodes->item($pos);
        if ($advance) {
            $this->setCurrentNode($item);
        }
        return $item;
    }

    /**
     * Returns the node which was used to instantiate the parser.
     *
     * @return \DOMNode
     */
    public function getRootNode()
    {
        return $this->baseNode;
    }

    /**
     * Returns the timestamp representation of the child node, throws an exception
     * if the child node is missing.
     *
     * @param string $name
     *
     * @return int|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleRequiredDate($name)
    {
        $value = $this->parseSingleRequiredString($name);
        return TypeNormalization::normalizeStringToDate($value);
    }

    /**
     * Returns the timestamp representation of the child node, null if the child
     * node is missing.
     *
     * @param string $name
     * @param mixed $default
     *   A default value if the value is not found.
     *
     * @return int|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleOptionalDate($name, $default = null)
    {
        $value = $this->parseSingleOptionalString($name, $default);
        if ($value === $default) {
            return $default;
        }
        return TypeNormalization::normalizeStringToDate($value);
    }

    /**
     * {@inheritdoc}
     */
    public function parseSingleRequiredTime($name)
    {
        $value = $this->parseSingleRequiredString($name);
        return TypeNormalization::normalizeStringToTime($value);
    }

    /**
     * {@inheritdoc}
     */
    public function parseSingleOptionalTime($name, $default = null)
    {
        $value = $this->parseSingleOptionalString($name, $default);
        if ($value === $default) {
            return $default;
        }
        return TypeNormalization::normalizeStringToTime($value);
    }
}
