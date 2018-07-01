<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use function array_shift;
use function file_put_contents;
use SimpleXMLElement;

abstract class AbstractConfigFile
{
    protected const ATTR_OPTION_NAME = 'name';
    protected const ATTR_OPTION_VALUE = 'value';

    protected function asXml()
    {
        return simplexml_load_file($this->getFileLocation());
    }

    protected function writeBack(SimpleXMLElement $toWrite)
    {
        $dom = dom_import_simplexml($toWrite)->ownerDocument;
        $dom->formatOutput = true;
        $xml = $dom->saveXML($dom->documentElement);
        file_put_contents($this->getFileLocation(), $xml);
    }

    protected function getAttr(SimpleXMLElement $element, string $attributeName): string
    {
        return (string)$element[$attributeName];
    }

    protected function hasAttr(SimpleXMLElement $element, string $attributeName): bool
    {
        return isset($element[$attributeName]);
    }

    protected function getOption(SimpleXMLElement $options, string $optionName): ?string
    {
        foreach ($options as $option) {
            if ($this->getAttr($option, CodeStyleConfig::ATTR_OPTION_NAME) === $optionName) {
                return $this->getAttr($option, CodeStyleConfig::ATTR_OPTION_VALUE);
            }
        }
        return null;
    }

    abstract protected function getFileLocation(): string;
}
