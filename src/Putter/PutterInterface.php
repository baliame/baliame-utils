<?php

namespace Baliame\Utils\Putter;


interface PutterInterface
{
    /**
     * Helper function to determine if CDATA is required.
     * CDATA is required in cases where &, > or < is found in the string.
     *
     * Note that since Demandware exports fail to escape XML special
     * characters properly (such as &), ALL entities are disregarded in
     * the parameter of this function. In practice, this means that any
     * properly escaped XML entities (such as &amp;) will cause this
     * function to return true.
     *
     * @param string $text
     *   The text to check.
     * @return boolean
     *   True if CDATA is required.
     */
    public static function isCDATARequired($text);

    /**
     * Creates multiple elements under the current elements with the same tag name.
     *
     * @param string $name
     *   The tag name to create the tags with.
     * @param array $strings
     *   An associative array of strings.
     * @return array
     *   An array of objects created.
     */
    public function outputMultipleStrings($name, $strings = null);

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, null if $value was null.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object|null
     *   The new element on success, null if $value was null.
     */
    public function outputSingleOptionalString($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent with value $value.
     * Returns the new element on success.
     * The child will be added regardless of the emptiness of $value.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleNullableString($name, $value, $attributes = array());

    /**
     * Sets the currently modified element to the target.
     *
     * @param object $target
     */
    public function proceed($target);

    /**
     * Adds all attributes in the $attributes array to the current element.
     * All already existing attributes will be overwritten.
     * Attributes with null values are not added.
     *
     * @param array $attributes
     *   An associative array of attribute names and values.
     */
    public function addAttributes($attributes = array());

    /**
     * Sets the currently modified DOM element to the parent of the current one.
     */
    public function returnToParent();

    /**
     * Creates multiple elements under the current elements with the same tag name.
     * Each tag has the string assigned as a named attribute instead of the value.
     *
     * @param string $name
     *   The tag name to create the tags with.
     * @param string $attributeName
     *   The attribute name for the value.
     * @param array $strings
     *   An associative array of strings.
     * @return array
     *   An array of objects created.
     */
    public function outputMultipleAttributeStrings($name, $attributeName, $strings = null);

    /**
     * Create a new sub-element under the current element. Useful for structures
     * where certain subtrees don't have complete object structure.
     *
     * @param $name
     *   The tag name of the element to output.
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     * @param bool $proceed
     *
     * @return object
     *   Returns the newly created element.
     */
    public function outputSingleSubElement($name, $attributes = array(), $proceed = true);

    /**
     * Creates multiple elements under the current elements with the same tag name and different xml:lang.
     *
     * @param string $name
     *   The tag name to create the tags with.
     * @param array $strings
     *   An associative array of strings, keyed by language.
     * @return array
     *   An array of objects created.
     */
    public function outputMultipleStringsByLanguage($name, $strings = null);

    /**
     * Creates multiple elements under the current elements with different xml:lang.
     *
     * @param array $objects
     *   An associative array of objects, keyed by language.
     * @return array
     *   An array of objects created.
     */
    public function outputMultipleObjectsByLanguage($objects = null);

    /**
     * Outputs an element which implements asXmlObject().
     * Optional implies that if the object is null or their XML
     * representation is null, null is returned.
     * An exception is still thrown is $object is not an instance of
     * Element and is not null.
     *
     * @param object|null $object
     *
     * @return object|null
     *   The child node or null if the child node was null.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleOptionalObject($object);

    /**
     * Like addAttributes, but runs each value through normalization.
     * @see PutterInterface::addAttributes
     *
     * @param array $attributes
     */
    public function addBooleanAttributes($attributes = array());

    /**
     * Resets the currently modified DOM element to the original base element.
     */
    public function reset();

    /**
     * Forcibly appends a text node to the current node.
     *
     * @param $text
     *
     * @return object
     */
    public function outputText($text);

    /**
     * Appends the provided subtree to the current element.
     *
     * @param object $tree
     */
    public function append($tree);

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, throws an exception if value is null.
     * The boolean value will be automatically converted into the Demandware-
     * compatible 'true' or 'false strings'.
     *
     * @param string $name
     * @param boolean $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     *   The new element on success.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredBoolean($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent with value $value.
     * Returns the new element on success.
     * The child will be added regardless of the emptiness of $value.
     * The boolean value will be automatically converted into the Demandware-
     * compatible 'true' or 'false strings'.
     *
     * @param string $name
     * @param boolean $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleNullableBoolean($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, null if $value was null.
     * The boolean value will be automatically converted into the Demandware-
     * compatible 'true' or 'false strings'.
     *
     * @param string $name
     * @param boolean $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object|null
     *   The new element on success, null if $value was null.
     */
    public function outputSingleOptionalBoolean($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent with value $value.
     * Returns the new element on success.
     * The child will be added regardless of the emptiness of $value.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 dates.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleNullableDate($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success,, throws an exception if value is null.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 dates.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleRequiredDate($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, null if $value was null.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 dates.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return null|object
     */
    public function outputSingleOptionalDate($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent with value $value.
     * Returns the new element on success.
     * The child will be added regardless of the emptiness of $value.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 times.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleNullableTime($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success,, throws an exception if value is null.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 times.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     */
    public function outputSingleRequiredTime($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, null if $value was null.
     * The date value will be automatically converted into the Demandware-
     * compatible ISO 8601 times.
     *
     * @param string $name
     * @param int $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return null|object
     */
    public function outputSingleOptionalTime($name, $value, $attributes = array());

    /**
     * Legacy support. Remove the same time as the two functions below.
     * A fancy wrapper around outputSingleNullableString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see PutterInterface::outputSingleNullableString
     */
    public function outputSingleNullableFloat($name, $value, $attributes = array());

    /**
     * Legacy support. Remove the same time as parseSingleRequiredFloat.
     * A fancy wrapper around outputSingleRequiredString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see PutterInterface::outputSingleRequiredString
     * @see ParserInterface::parseSingleRequiredFloat
     */
    public function outputSingleRequiredFloat($name, $value, $attributes = array());

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with name $name.
     * Returns the new element on success, throws an exception if value is null.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return object
     *   The new element on success.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredString($name, $value, $attributes = array());

    /**
     * Legacy support. Remove the same time as parseSingleOptionalFloat.
     * A fancy wrapper around outputSingleOptionalString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return null|object
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see PutterInterface::outputSingleOptionalString
     * @see ParserInterface::parseSingleOptionalFloat
     */
    public function outputSingleOptionalFloat($name, $value, $attributes = array());

    /**
     * Outputs an element which implements asXmlObject().
     * Required implies that if the object is null or their XML
     * representation is null, an error is raised.
     *
     * @param object|null $object
     *
     * @return object
     *   The child node.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredObject($object);

    /**
     * Outputs each object in an array of objects.
     * Acts like optional if an object is null or its representation is null.
     * @see outputSingleOptionalObject
     *
     * @param $objects
     * @return array
     *   The array of objects created.
     */
    public function outputMultipleObjects($objects);

    /**
     * @return object
     */
    public function getBaseNode();

    /**
     * @return object
     */
    public function getCurrentNode();

    /**
     * Adds the language attribute to the current node if necessary.
     *
     * @param string $language
     */
    public function outputLanguage($language);
} 