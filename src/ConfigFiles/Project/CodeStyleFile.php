<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use DOMElement;
use Masterminds\HTML5\Elements;
use QueryPath\DOMQuery;

class CodeStyleFile extends AbstractConfigFile
{
    const CODESTYLE_ATTR_NAME = 'name';

    /** @var string */
    private $fileLocation;

    public function __construct(
        string $fileLocation
    ) {
        $this->fileLocation = $fileLocation;
    }

    public function getCodeStyleName()
    {
        return $this->asXml()->get(0)->getAttribute(self::CODESTYLE_ATTR_NAME);
    }

    protected function getFileLocation(): string
    {
        return $this->fileLocation;
    }
}
