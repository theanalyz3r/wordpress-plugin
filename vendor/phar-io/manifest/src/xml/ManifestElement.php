<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

namespace PharIo\Manifest;

use DOMElement;
use DOMNodeList;

class ManifestElement {
    const XMLNS = 'https://phar.io/xml/manifest/1.0';

    /**
     * @var DOMElement
     */
    private $element;

    /**
     * ContainsElement constructor.
     *
     * @param DOMElement $element
     */
    public function __construct(DOMElement $element) {
        $this->element = $element;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws ManifestElementException
     */
    protected function getAttributeValue($name) {
        if (!$this->element->hasAttribute($name)) {
            throw new ManifestElementException(
                sprintf(
                    'Attribute %s not set on element %s',
                    $name,
                    $this->element->localName
                )
            );
        }

        return $this->element->getAttribute($name);
    }

    /**
     * @param $elementName
     *
     * @return DOMElement
     *
     * @throws ManifestElementException
     */
    protected function getChildByName($elementName) {
        $element = $this->element->getElementsByTagNameNS(self::XMLNS, $elementName)->item(0);

        if (!$element instanceof DOMElement) {
            throw new ManifestElementException(
                sprintf('Element %s missing', $elementName)
            );
        }

        return $element;
    }

    /**
     * @param $elementName
     *
     * @return DOMNodeList
     *
     * @throws ManifestElementException
     */
    protected function getChildrenByName($elementName) {
        $elementList = $this->element->getElementsByTagNameNS(self::XMLNS, $elementName);

        if ($elementList->length === 0) {
            throw new ManifestElementException(
                sprintf('Element(s) %s missing', $elementName)
            );
        }

        return $elementList;
    }

    /**
     * @param string $elementName
     *
     * @return bool
     */
    protected function hasChild($elementName) {
        return $this->element->getElementsByTagNameNS(self::XMLNS, $elementName)->length !== 0;
    }
}
