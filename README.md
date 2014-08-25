# Parser utilities

This library includes a standardized, data structure agnostic parser and putter interface for PHP, as well as an XML
implementation.

## Usage

All input is done through the ParserInterface and all output is done through the PutterInterface.
Both of these utilities provide optionality, multiplicity and type-correctness.

The following code snippet provides an overview of how simple it is to create an arbitrary format of document:

```php

use Baliame\Utils\Putter\PutterInterface;

// When called with an instance of the included XmlPutter, the ID 'foo', and strings ['bar', 'baz', 'qux']
// this will output the following DOM:
// <strings id="foo">
//   <string>bar</string>
//   <string>baz</string>
//   <string>qux</string>
// </strings>
function outputFoo(PutterInterface $putter, $id, array $strings) {
    $putter->outputSingleSubElement('strings', ['id' => $id]);
    $putter->outputMultipleStrings('string', $strings);
    $putter->returnToParent();
}
```