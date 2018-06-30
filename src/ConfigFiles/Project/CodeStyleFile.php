<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

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
        return $this->getAttr($this->asXml(), self::CODESTYLE_ATTR_NAME);
    }

    protected function getFileLocation(): string
    {
        return $this->fileLocation;
    }
}
