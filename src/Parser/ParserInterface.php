<?php

namespace Baliame\Utils\Parser;

interface ParserInterface
{
    /**
     * Checks if the canonical name of the current node matches the passed string.
     * If $graceful is not true, throws an exception instead of returning false.
     *
     * @param string $name
     * @param bool $graceful
     *
     * @return bool
     * @throws \Exception
     */
    public function checkNodeName($name, $graceful = false);

    /**
     * Parses the string value of a node.
     *
     * @return string
     */
    public function parseText();

    /**
     * Sets the current base node.
     *
     * @param $node object
     */
    public function setCurrentNode($node);

    /**
     * Moves the parser back to the original node it was constructed with.
     */
    public function reset();

    /**
     * Returns true if a child element exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function childExists($name);

    /**
     * Returns true if an attribute exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function attributeExists($name);

    /**
     * Returns all child elements matching the tag name.
     *
     * @param string $name
     *
     * @return object[]
     */
    public function getChildNodes($name);

    /**
     * Moves the base node to the single child with the provided name.
     *
     * @param $name
     */
    public function proceedToSingleChild($name);

    /**
     * Returns the first child element matching the tag name.
     *
     * @param string $name
     *
     * @return object
     */
    public function getSingleChild($name);

    /**
     * Moves the base node one level up.
     */
    public function returnToParent();

    /**
     * Returns an associative array of values keyed by language.
     *
     * @param string $name
     *
     * @return array
     *   An associative array of values keyed by language.
     */
    public function parseMultipleStringsByLanguage($name);

    /**
     * Returns an associative array of objects keyed by language.
     *
     * @param string $name
     * @param string $handlerClass
     *   The class in charge of parsing the objects.
     * @param array $additionalParameters
     *   An array of additional parameters to pass to the parse function.
     * @param string $parserFunction
     *
     * @return array
     *   An associative array of objects keyed by language.
     */
    public function parseMultipleObjectsByLanguage(
        $name,
        $handlerClass = '',
        $additionalParameters = array(),
        $parserFunction = 'parse'
    );

    /**
     * Returns the boolean representation of the child node, throws an exception
     * if the child node is missing.
     *
     * @param string $name
     *
     * @return bool|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleRequiredBoolean($name);

    /**
     * Returns the boolean representation of the child node, null if the child
     * node is missing.
     *
     * @param string $name
     * @param mixed $default
     *   A default value if the value is not found.
     *
     * @return bool|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleOptionalBoolean($name, $default = null);

    /**
     * Returns the timestamp representation of the child node, throws an exception
     * if the child node is missing.
     *
     * @param string $name
     *
     * @return int|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleRequiredDate($name);

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
    public function parseSingleOptionalDate($name, $default = null);

    /**
     * Returns the timestamp representation of the child node, throws an exception
     * if the child node is missing.
     *
     * @param string $name
     *
     * @return int|null
     *   The boolean representation child node, null if the child is missing.
     */
    public function parseSingleRequiredTime($name);

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
    public function parseSingleOptionalTime($name, $default = null);

    /**
     * Returns the value of the child node, false if the child node is missing.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return string|false
     *   The value of the child node, false of the value does not exist.
     */
    public function parseSingleOptionalString($name, $default = null);

    /**
     * Returns the float value of the child node, throws an exception if the
     * child node is missing.
     *
     * @param string $name
     *
     * @return float|null
     *   The float representation child node, null if the child is missing.
     *
     * @deprecated since version 0.1.8 - all floats are now strings
     *
     * @see https://github.com/acquia/demandware-cartridge-client-php/issues/30
     */
    public function parseSingleRequiredFloat($name);

    /**
     * Returns the value of the child node, an exception is thrown if the child
     * node is missing.
     *
     * @param string $name
     *
     * @return string
     *   The value of the child node.
     */
    public function parseSingleRequiredString($name);

    /**
     * Returns the float value of the child node, false if the child node is
     * missing.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return float|null
     *   The float representation child node, null if the child is missing.
     *
     * @deprecated since version 0.1.8 - all floats are now strings
     *
     * @see https://github.com/acquia/demandware-cartridge-client-php/issues/30
     */
    public function parseSingleOptionalFloat($name, $default = null);

    /**
     * Returns an object modeling the data in the child node, throws an
     * exception if the child node is missing.
     *
     * @param string $name
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return mixed
     *   An instance of $handlerClass, false if the child node is missing.
     */
    public function parseSingleRequiredObject(
        $name,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    );

    /**
     * Returns an object modeling the data in the child node.
     *
     * @param string $name
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param mixed $default
     * @param string $parserFunction
     *
     * @return mixed
     *   An instance of $handlerClass, $default if the child node is missing.
     */
    public function parseSingleOptionalObject(
        $name,
        $handlerClass = '',
        array $additionalParameters = array(),
        $default = null,
        $parserFunction = 'parse'
    );

    /**
     * Returns objects modeling the data in all child nodes matching the tag
     * name.
     *
     * @param string $name
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return array
     *   Instances of $handlerClass
     */
    public function parseMultipleObjects(
        $name,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    );

    /**
     * Returns objects modeling the data in all child nodes matching the tag
     * name, keyed by an appropriate attribute.
     *
     * @param string $name
     * @param string $attributeName
     * @param string $handlerClass
     * @param array $additionalParameters
     * @param string $parserFunction
     *
     * @return array
     *   Instances of $handlerClass, keyed by attribute $attributeName
     */
    public function parseMultipleObjectsByAttribute(
        $name,
        $attributeName,
        $handlerClass = '',
        array $additionalParameters = array(),
        $parserFunction = 'parse'
    );

    /**
     * Returns an array of values from child nodes.
     *
     * @param string $name
     *
     * @return array
     */
    public function parseMultipleStrings($name);

    /**
     * Returns an attribute's values across all child nodes matching the tag
     * name.
     *
     * @param string $name
     * @param string $attributeName
     *
     * @return array
     */
    public function parseMultipleAttributeStrings($name, $attributeName);

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
    public function parseOptionalBooleanAttribute($attributeName, $default = null);

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
    public function parseRequiredBooleanAttribute($attributeName);

    /**
     * Returns an attribute if set, throws and exception otherwise.
     *
     * @param string $attributeName
     *
     * @return string
     *   The value of the attribute, null if it is not set.
     *
     * @throws \Exception
     */
    public function parseRequiredAttribute($attributeName);

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
    public function parseOptionalAttribute($attributeName, $default = null);

    /**
     * Returns the current base node.
     *
     * @return object
     */
    public function getCurrentNode();

    /**
     * Parses the language of the current node. Returns LANGUAGE_UNDEFINED if
     * not set or undeterminable in the parsed data structure.
     *
     * @return string
     */
    public function parseLanguage();

    /**
     * Function used to raise the appropriate exception for the parser
     * implementation.
     *
     * @param $message
     * @param int $code
     * @throws ParserException
     */
    public function raiseException($message, $code = -1);

    /**
     * Gets the count of child nodes on the current node.
     *
     * @return int
     */
    public function getChildCount();

    /**
     * Gets the nth child node, indexed from 0.
     * If $advance is true, also advances the position.
     *
     * @param int $pos
     * @param bool $advance
     * @return object
     */
    public function getChildNodeByPosition($pos, $advance = false);

    /**
     * Returns the node which was used to instantiate the parser.
     *
     * @return object
     */
    public function getRootNode();
}
