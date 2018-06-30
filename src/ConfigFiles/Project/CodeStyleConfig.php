<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use SimpleXMLElement;
use function simplexml_load_file;

class CodeStyleConfig
{
    public const DIR = '/codeStyles';
    public const FILE = 'codeStyleConfig.xml';
    public const PATH = self::DIR . '/' . self::FILE;

    /** @var string */
    private $ideaDirectory;

    const USE_PER_PROJECT_SETTINGS = 'USE_PER_PROJECT_SETTINGS';
    const PREFERRED_PROJECT_CODE_STYLE = 'PREFERRED_PROJECT_CODE_STYLE';
    const ATTR_OPTION_NAME = 'name';
    const ATTR_OPTION_VALUE = 'value';

    public function __construct(
        string $ideaDirectory
    ) {
        $this->ideaDirectory = $ideaDirectory;
    }

    public function isPerProjectSettings(): bool
    {
        $root = $this->getSimpleXml();
        foreach ($root->state->option as $option) {
            if ($this->getAttr($option, self::ATTR_OPTION_NAME) === self::USE_PER_PROJECT_SETTINGS) {
                $this->getAttr($option, self::USE_PER_PROJECT_SETTINGS) === 'true' ? true : false;
            }
        }
        return false;
    }

    public function getPrefferedProjectCodeStyle(): ?string
    {
        $root = $this->getSimpleXml();
        foreach ($root->state->option as $option) {
            if ($this->getAttr($option, self::ATTR_OPTION_NAME) === self::PREFERRED_PROJECT_CODE_STYLE) {
                return $this->getAttr($option, self::ATTR_OPTION_VALUE);
            }
        }
        return null;
    }

    private function getSimpleXml()
    {
        return simplexml_load_file($this->ideaDirectory . self::PATH);
    }

    private function getAttr(SimpleXMLElement $element, string $attributeName): string
    {
        return (string)$element[$attributeName];
    }

    private function hasAttr(SimpleXMLElement $element, string $attributeName): bool
    {
        return isset($element[$attributeName]);
    }
}
