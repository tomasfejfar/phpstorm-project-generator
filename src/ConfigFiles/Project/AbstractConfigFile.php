<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use DOMElement;
use QueryPath;
use QueryPath\DOMQuery;
use SimpleXMLElement;
use function dom_import_simplexml;
use function file_put_contents;
use function simplexml_load_string;

abstract class AbstractConfigFile
{
    protected const ATTR_OPTION_NAME = 'name';
    protected const ATTR_OPTION_VALUE = 'value';

    protected function asXml(): DOMQuery
    {
        return QueryPath::with('<?xml version="1.0"?>' . file_get_contents($this->getFileLocation()));
    }

    protected function writeBack(DOMQuery $toWrite)
    {
        $xmlString = $toWrite->xml();
        $dom = dom_import_simplexml(simplexml_load_string($xmlString))->ownerDocument;
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $xml = $dom->saveXML($dom->documentElement);
        file_put_contents($this->getFileLocation(), $xml);
    }

    protected function getOptionValue(string $selector, string $optionName): ?string
    {
        $perProjectElement = $this
            ->asXml()
            ->find('state option')
            ->filterCallback($this->getFilterByOptionNameClosure($optionName))
        ;
        if (count($perProjectElement)) {
            return $perProjectElement->get(0)->getAttribute(self::ATTR_OPTION_VALUE);
        }
        return null;
    }

    protected function setOption(string $rootSelector, string $optionName, string $optionValue): void
    {
        $xml = $this
            ->asXml();

        $selector = $rootSelector . ' option';
        $elements = $xml->find($selector)
            ->filterCallback($this->getFilterByOptionNameClosure($optionName))
        ;
        if (count($elements) > 0) {
            $elements->attr(self::ATTR_OPTION_VALUE, $optionValue);
        } else {
            $el = new SimpleXMLElement('<option/>');
            $el[self::ATTR_OPTION_NAME] = $optionName;
            $el[self::ATTR_OPTION_VALUE] = $optionValue;
            $xml->find($rootSelector)
                ->append($el)
            ;
        }
        $this->writeBack($xml);
    }

    protected function unsetOption(string $rootSelector, string $optionName): void
    {
        $xml = $this
            ->asXml();
        $selector = $rootSelector . ' option';
        $xml->find($selector)
            ->filterCallback($this->getFilterByOptionNameClosure($optionName))
            ->remove()
        ;
        $this->writeBack($xml);
    }

    abstract protected function getFileLocation(): string;

    private function getFilterByOptionNameClosure(string $optionName)
    {
        return function ($index, DOMElement $element) use ($optionName) {
            $hasAttribute = $element->hasAttribute(self::ATTR_OPTION_NAME);
            $attributeValueMatches = $element->getAttribute(self::ATTR_OPTION_NAME) === $optionName;
            return $hasAttribute && $attributeValueMatches;
        };
    }
}
