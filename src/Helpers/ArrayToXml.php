<?php declare(strict_types=1);

/**
 * Based on Stack-Overflow questions and answers
 *
 * @link      http://stackoverflow.com/questions/99350/passing-php-associative-arrays-to-and-from-xml
 * @package   Spin
 */

namespace Spin\Helpers;

class ArrayToXml
{
  private string $version;
  private string $encoding;

  /**
   * Construct ArrayToXML object with selected version and encoding
   *
   * for available values check XMLWriter docs
   * http://www.php.net/manual/en/function.xMLwriter-start-document.php
   *
   * @param string $xmlVersion  XML Version, default 1.0
   * @param string $xmlEncoding XML Encoding, default UTF-8
   */
  public function __construct(string $xmlVersion = '1.0', string $xmlEncoding = 'UTF-8')
  {
    $this->version = $xmlVersion;
    $this->encoding = $xmlEncoding;
  }

  /**
   * Build an XML Data Set
   *
   * @param array $data                 Associative Array containing values to
   *                                    be parsed into an XML Data Set(s)
   * @param string $startElement        Root Opening Tag, default data
   *
   * @return false|string XML String containing values
   */
  public function buildXML(array $data, string $startElement = 'data'): false|string
  {
    $xml = new \XMLWriter();
    $xml->openMemory();
    $xml->startDocument($this->version, $this->encoding);
    $xml->startElement($startElement);

    $data = $this->writeAttr($xml, $data);
    $this->writeEl($xml, $data);

    $xml->endElement();
    return $xml->outputMemory();
  }

  /**
   * Write keys in $data prefixed with @ as XML attributes, if $data is an
   * array. When an @ prefixed key is found, a '%' key is expected to indicate
   * the element itself, and '#' prefixed key indicates CDATA content
   *
   * @param \XMLWriter $xml object
   * @param array $data     with attributes filtered out
   *
   * @return array          $data | $nonAttributes
   */
  protected function writeAttr(\XMLWriter $xml, array $data): array
  {
    $nonAttributes = [];
    foreach ($data as $key => $val) {
      //handle an attribute with elements
      if ($key[0] === '@') {
        $xml->writeAttribute(substr($key, 1), $val);
      } else if ($key[0] === '%') {
        if (\is_array($val)) {
          $nonAttributes = $val;
        } else {
          $xml->text($val);
        }
      } elseif ($key[0] === '#') {
        if (\is_array($val)) {
          $nonAttributes = $val;
        } else {
          $xml->startElement(substr($key, 1));
          $xml->writeCData($val);
          $xml->endElement();
        }
      } else if($key[0] === "!") {
        if (\is_array($val)) {
          $nonAttributes = $val;
        } else {
          $xml->writeCData($val);
        }
      } else { //ignore normal elements
        $nonAttributes[$key] = $val;
      }
    }
    return $nonAttributes;
  }

  /**
   * Write XML as per Associative Array
   *
   * @param \XMLWriter $xml object
   * @param array $data     Associative Data Array
   */
  protected function writeEl(\XMLWriter $xml, array $data): void
  {
    foreach ($data as $key => $value) {
      if (\is_array($value) && !$this->isAssoc($value)) { //numeric array
        foreach ($value as $itemValue) {
          if (\is_array($itemValue)) {
            $xml->startElement($key);
            $itemValue = $this->writeAttr($xml, $itemValue);
            $this->writeEl($xml, $itemValue);
            $xml->endElement();
          } else {
            $itemValue = $this->writeAttr($xml, $itemValue);
            $xml->writeElement($key, (string) $itemValue);
          }
        }
      } else if (\is_array($value)) { //associative array
        $xml->startElement($key);
        $value = $this->writeAttr($xml, $value);
        $this->writeEl($xml, $value);
        $xml->endElement();
      } else { //scalar
        $value = $this->writeAttr($xml, $value);
        $xml->writeElement($key, (string) $value);
      }
    }
  }

  /**
   * Check if array is associative with string based keys FROM:
   * http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential/4254008#4254008
   *
   * @param array $array Array to check
   *
   * @return     bool
   */
  protected function isAssoc(array $array): bool
  {
    return (bool)\count(\array_filter(\array_keys($array), 'is_string'));
  }
}
