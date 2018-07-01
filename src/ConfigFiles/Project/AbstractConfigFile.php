<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use SimpleXMLElement;
use function file_put_contents;

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
        $dom->preserveWhiteSpace = false;
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

    protected function getOptionValue(SimpleXMLElement $options, string $optionName): ?string
    {
        foreach ($options as $option) {
            if ($this->getAttr($option, CodeStyleConfig::ATTR_OPTION_NAME) === $optionName) {
                return $this->getAttr($option, CodeStyleConfig::ATTR_OPTION_VALUE);
            }
        }
        return null;
    }

    protected function setOption(SimpleXMLElement $parentElement, string $optionName, string $optionValue): void
    {
        $option = null;
        if (isset($parentElement->option)) {
            $option = $this->getOption($parentElement->option, $optionName);
        }
        if ($option === null) {
            $option = $parentElement->addChild('option');
        }
        $option[self::ATTR_OPTION_NAME] = $optionName;
        $option[self::ATTR_OPTION_VALUE] = $optionValue;
    }

    protected function unsetOption(SimpleXMLElement $options, string $optionName): void
    {
        foreach ($options as $key => $option) {
            if ($this->getAttr($option, CodeStyleConfig::ATTR_OPTION_NAME) === $optionName) {
                $dom = dom_import_simplexml($option);
                $dom->parentNode->removeChild($dom);
            }
        }
    }

    protected function getOption(SimpleXMLElement $options, string $optionName): ?SimpleXMLElement
    {
        foreach ($options as $option) {
            if ($this->getAttr($option, CodeStyleConfig::ATTR_OPTION_NAME) === $optionName) {
                return $option;
            }
        }
        return null;
    }

    abstract protected function getFileLocation(): string;
}
