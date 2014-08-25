<?php

namespace Baliame\Utils\Putter\Implementation\Xml;

use Baliame\Utils\Interoperability\TypeNormalization;
use Baliame\Utils\Putter\PutterInterface;

class XmlPutter implements PutterInterface
{
    /**
     * @var \DOMDocument $document
     * The document we're editing. Does not change.
     */
    protected $document;

    /**
     * @var \DOMNode $baseNode
     * The base element. Can be created automatically via the constructor.
     */
    protected $baseNode;

    /**
     * @var \DOMNode $current
     * The node we're currently editing. May differ from $base.
     */
    protected $current;

    /**
     * @param null|\DOMDocument $document
     *   The document which will be edited. If null, one is created.
     * @param string|\DOMNode|null $base
     *   Either a string which signifies that a base element with the provided
     *   name should be created, or a DOMNode if one has already been
     *   created. If no base node should be created, pass null - in this case,
     *   the document will be the base node.
     * @param string|null $value
     *   If $base is a string, this is the value which will be assigned to the
     *   node, or nothing if value is null.
     */
    public function __construct(&$document = null, $base = null, $value = null)
    {
        if ($document === null) {
            $document = new \DOMDocument('1.0', 'UTF-8');
        }
        $this->document = $document;
        if ($base instanceof \DOMNode) {
            $this->baseNode = $base;
        } elseif (is_string($base)) {
            if (empty($value)) {
                $this->baseNode = $document->createElement($base);
            } else {
                $this->baseNode = $document->createElement($base, $value);
            }
        } elseif ($base === null) {
            $this->baseNode = $this->document;
        } else {
            throw new \InvalidArgumentException('Expecting base tag to be DOMNode, string or null.');
        }
        $this->current = $this->baseNode;
    }

    /**
     * Creates multiple elements under the current elements with the same tag name.
     *
     * @param string $name
     *   The tag name to create the tags with.
     * @param array $strings
     *   An associative array of strings.
     * @return array
     *   An array of DOMNodes created.
     */
    public function outputMultipleStrings($name, $strings = null)
    {
        $elements = [];

        foreach ($strings as $lang => $value) {
            $element = $this->outputSingleOptionalString($name, $value);
            if ($element !== null) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with tagname $name.
     * Returns the new element on success, null if $value was null.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode|null
     *   The new element on success, null if $value was null.
     */
    public function outputSingleOptionalString($name, $value, $attributes = array())
    {
        if ($value !== null) {
            return $this->outputSingleNullableString($name, $value, $attributes);
        } else {
            return null;
        }
    }

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
     * @return \DOMNode
     */
    public function outputSingleNullableString($name, $value, $attributes = array())
    {
        if (static::isCDATARequired($value)) {
            $child = $this->document->createElement($name);
            $text = $this->document->createTextNode($value);
            $child->appendChild($text);
        } else {
            $child = $this->document->createElement($name, $value);
        }
        $this->current->appendChild($child);
        $this->proceed($child);
        $this->addAttributes($attributes);
        $this->returnToParent();
        return $child;
    }

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
    public static function isCDATARequired($text)
    {
        return (strpos($text, '&') !== false) || (strpos($text, '<') !== false) || (strpos($text, '>') !== false);
    }

    /**
     * Sets the currently modified DOM element to the target.
     *
     * @param \DOMNode $target
     */
    public function proceed($target)
    {
        $this->current = $target;
    }

    /**
     * Adds all attributes in the $attributes array to the current element.
     * All already existing attributes will be overwritten.
     * Attributes with null values are not added.
     *
     * @param array $attributes
     *   An associative array of attribute names and values.
     */
    public function addAttributes($attributes = array())
    {
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->current->setAttribute($key, $value);
        }
    }

    /**
     * Sets the currently modified DOM element to the parent of the current one.
     */
    public function returnToParent()
    {
        $this->current = $this->current->parentNode;
    }

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
     *   An array of DOMNodes created.
     */
    public function outputMultipleAttributeStrings($name, $attributeName, $strings = null)
    {
        $elements = [];

        foreach ($strings as $lang => $value) {
            $element = $this->outputSingleSubElement($name, [$attributeName => $value], false);
            if ($element !== null) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Create a new sub-element under the current element. Useful for structures
     * where certain DOM trees don't have complete object structure.
     *
     * @param $name
     *   The tag name of the element to output.
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     * @param bool $proceed
     *
     * @return \DOMNode
     *   Returns the newly created element.
     */
    public function outputSingleSubElement($name, $attributes = array(), $proceed = true)
    {
        $element = $this->document->createElement($name);
        $this->current->appendChild($element);
        $this->proceed($element);
        $this->addAttributes($attributes);
        if (!$proceed) {
            $this->returnToParent();
        }
        return $element;
    }

    /**
     * Creates multiple elements under the current elements with the same tag name and different xml:lang.
     *
     * @param string $name
     *   The tag name to create the tags with.
     * @param array $strings
     *   An associative array of strings, keyed by language.
     * @return array
     *   An array of DOMNodes created.
     */
    public function outputMultipleStringsByLanguage($name, $strings = null)
    {
        $elements = [];

        foreach ($strings as $lang => $value) {
            $attributes = [];
            if ($lang !== XmlSerializableInterface::LANGUAGE_UNDEFINED) {
                $attributes['xml:lang'] = $lang;
            }
            $element = $this->outputSingleOptionalString($name, $value, $attributes);
            if ($element !== null) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Creates multiple elements under the current elements with different xml:lang.
     *
     * @param array $objects
     *   An associative array of objects, keyed by language.
     * @return array
     *   An array of DOMNodes created.
     */
    public function outputMultipleObjectsByLanguage($objects = null)
    {
        $elements = [];

        foreach ($objects as $lang => $object) {
            $attributes = [];
            if ($lang !== XmlSerializableInterface::LANGUAGE_UNDEFINED) {
                $attributes['xml:lang'] = $lang;
            }
            $element = $this->outputSingleOptionalObject($object);
            $this->proceed($element);
            $this->addAttributes($attributes);
            $this->returnToParent();
            if ($element !== null) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Outputs an element which implements asXmlObject().
     * Optional implies that if the object is null or their XML
     * representation is null, null is returned.
     * An exception is still thrown is $object is not an instance of
     * Element and is not null.
     *
     * @param XmlSerializableInterface|null $object
     *
     * @return \DOMNode|null
     *   The child node or null if the child node was null.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleOptionalObject($object)
    {
        if ($object === null) {
            return null;
        } elseif (!($object instanceof XmlSerializableInterface)) {
            throw new \InvalidArgumentException('Referenced object is not an instance of XmlSerializableInterface.');
        }
        $value = $object->asXmlObject($this);
        /*if ($value === null) {
            return null;
        }
        $this->current->appendChild($value);*/
        return $value;
    }

    /**
     * Like addAttributes, but runs each value through normalization.
     * @see XmlPutter::addAttributes
     *
     * @param array $attributes
     */
    public function addBooleanAttributes($attributes = array())
    {
        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->current->setAttribute($key, TypeNormalization::normalizeBooleanToString($value));
        }
    }

    /**
     * Resets the currently modified DOM element to the original base element.
     */
    public function reset()
    {
        $this->current = $this->baseNode;
    }

    /**
     * Forcibly appends a DOM text node to the current node.
     *
     * @param $text
     *
     * @return \DOMText
     */
    public function outputText($text)
    {
        $textNode = $this->document->createTextNode($text);
        $this->append($textNode);
        return $textNode;
    }

    /**
     * Appends the provided DOM tree to the current element.
     *
     * @param \DOMNode $tree
     */
    public function append($tree)
    {
        $this->current->appendChild($tree);
    }

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with tagname $name.
     * Returns the new element on success, throws an exception if value is null.
     * The boolean value will be automatically converted into the Demandware-
     * compatible 'true' or 'false strings'.
     *
     * @param string $name
     * @param boolean $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode
     *   The new element on success.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredBoolean($name, $value, $attributes = array())
    {
        if ($value !== null) {
            return $this->outputSingleNullableBoolean($name, $value, $attributes);
        } else {
            if ($this->baseNode instanceof \DOMElement) {
                throw new \InvalidArgumentException("Element $name in {$this->baseNode->tagName} cannot be null.");
            }
            else {
                throw new \InvalidArgumentException("Element $name not found.");
            }
        }
    }

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
     * @return \DOMNode
     */
    public function outputSingleNullableBoolean($name, $value, $attributes = array())
    {
        return $this->outputSingleNullableString($name, TypeNormalization::normalizeBooleanToString($value), $attributes);
    }

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with tagname $name.
     * Returns the new element on success, null if $value was null.
     * The boolean value will be automatically converted into the Demandware-
     * compatible 'true' or 'false strings'.
     *
     * @param string $name
     * @param boolean $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode|null
     *   The new element on success, null if $value was null.
     */
    public function outputSingleOptionalBoolean($name, $value, $attributes = array())
    {
        if ($value !== null) {
            return $this->outputSingleNullableBoolean($name, $value, $attributes);
        } else {
            return null;
        }
    }

    /**
     * Legacy support. Remove the same time as the two functions below.
     * A fancy wrapper around outputSingleNullableString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see XmlPutter::outputSingleNullableString
     */
    public function outputSingleNullableFloat($name, $value, $attributes = array())
    {
        return $this->outputSingleNullableString($name, $value, $attributes);
    }

    /**
     * Legacy support. Remove the same time as parseSingleRequiredFloat.
     * A fancy wrapper around outputSingleRequiredString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see XmlPutter::outputSingleRequiredString
     * @see XmlParser::parseSingleRequiredFloat
     */
    public function outputSingleRequiredFloat($name, $value, $attributes = array())
    {
        return $this->outputSingleRequiredString($name, $value, $attributes);
    }

    /**
     * Appends an element to $document, under $parent if $value is not null,
     * with tagname $name.
     * Returns the new element on success, throws an exception if value is null.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode
     *   The new element on success.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredString($name, $value, $attributes = array())
    {
        if ($value !== null) {
            return $this->outputSingleNullableString($name, $value, $attributes);
        } else {
            if ($this->baseNode instanceof \DOMElement) {
                throw new \InvalidArgumentException("Element $name in {$this->baseNode->tagName} cannot be null.");
            }
            else {
                throw new \InvalidArgumentException("Element $name cannot be null.");
            }
        }
    }

    /**
     * Legacy support. Remove the same time as parseSingleOptionalFloat.
     * A fancy wrapper around outputSingleOptionalString.
     *
     * @param string $name
     * @param string $value
     * @param array $attributes
     *   An optional, associative array of attributes to set.
     *
     * @return \DOMNode
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated deprecated since 0.2
     * @see XmlPutter::outputSingleOptionalString
     * @see XmlParser::parseSingleOptionalFloat
     */
    public function outputSingleOptionalFloat($name, $value, $attributes = array())
    {
        return $this->outputSingleOptionalString($name, $value, $attributes);
    }

    /**
     * Outputs an element which implements asXmlObject().
     * Required implies that if the object is null or their XML
     * representation is null, an error is raised.
     *
     * @param XmlSerializableInterface|null $object
     *
     * @return \DOMNode
     *   The child node.
     *
     * @throws \InvalidArgumentException
     */
    public function outputSingleRequiredObject($object)
    {
        if ($object === null) {
            throw new \InvalidArgumentException('Provided object must not be null.');
        } elseif (!($object instanceof XmlSerializableInterface)) {
            throw new \InvalidArgumentException('Referenced object is not an instance of Element.');
        }
        $value = $object->asXmlObject($this);
        if ($value === null) {
            throw new \InvalidArgumentException('Requested object is required, but its XML representation is null.');
        }
        //$this->current->appendChild($value);
        return $value;
    }

    /**
     * Outputs each object in an array of objects.
     * Acts like optional if an object is null or its representation is null.
     * @see outputSingleOptionalObject
     *
     * @param $objects
     * @return array
     *   The array of DOMNodes created.
     */
    public function outputMultipleObjects($objects)
    {
        $elements = array();

        foreach ($objects as $object) {
            $element = $this->outputSingleOptionalObject($object);
            if ($element !== null) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * @return \DOMNode
     */
    public function getBaseNode()
    {
        return $this->baseNode;
    }

    /**
     * @return \DOMNode
     */
    public function getCurrentNode()
    {
        return $this->current;
    }

    /**
     * Adds the language attribute to the current node if necessary.
     *
     * @param string $language
     */
    public function outputLanguage($language)
    {
        if ($language === XmlSerializableInterface::LANGUAGE_UNDEFINED) {
            return;
        } else {
            $this->addAttributes(['xml:lang' => $language]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleNullableDate($name, $value, $attributes = array())
    {
        $this->outputSingleNullableString($name, TypeNormalization::normalizeDateToString($value), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleRequiredDate($name, $value, $attributes = array())
    {
        $this->outputSingleRequiredString($name, TypeNormalization::normalizeDateToString($value), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleOptionalDate($name, $value, $attributes = array())
    {
        $this->outputSingleOptionalString($name, TypeNormalization::normalizeDateToString($value), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleNullableTime($name, $value, $attributes = array())
    {
        $this->outputSingleNullableString($name, TypeNormalization::normalizeTimeToString($value), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleRequiredTime($name, $value, $attributes = array())
    {
        $this->outputSingleRequiredString($name, TypeNormalization::normalizeTimeToString($value), $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function outputSingleOptionalTime($name, $value, $attributes = array())
    {
        $this->outputSingleOptionalString($name, TypeNormalization::normalizeTimeToString($value), $attributes);
    }
}