<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

use PhpStormGen\ConfigFiles\CodeStylePathHelper;

class CodeStyleConfig extends AbstractConfigFile
{
    public const FILE = 'codeStyleConfig.xml';
    private const USE_PER_PROJECT_SETTINGS = 'USE_PER_PROJECT_SETTINGS';
    private const PREFERRED_PROJECT_CODE_STYLE = 'PREFERRED_PROJECT_CODE_STYLE';

    /** @var string */
    private $fileLocation;

    public function __construct(
        string $fileLocation
    ) {
        $this->fileLocation = $fileLocation;
    }

    public static function createLocalFromPathHelper(CodeStylePathHelper $helper): self
    {
        return new static($helper->getProjectCodeStyleConfigFilePath());
    }

    public function getPrefferedProjectCodeStyle(): ?string
    {
        return $this->getOptionValue(
            'state option',
            self::PREFERRED_PROJECT_CODE_STYLE
        );
    }

    public function isPerProjectSettings(): bool
    {
        $perProject = $this->getOptionValue(
            'state option',
            CodeStyleConfig::USE_PER_PROJECT_SETTINGS
        );
        if ($perProject === null) {
            return false;
        }

        return (bool)$perProject;
    }

    public function setCodeStylePerProject()
    {
        $xml = $this->asXml();
        $this->setOption('state', self::USE_PER_PROJECT_SETTINGS, 'true');
        $this->unsetOption('state', self::PREFERRED_PROJECT_CODE_STYLE);
        $this->writeBack($xml);
    }

    public function setCodeStyle(string $codeStyleName)
    {
        $this->unsetOption('state', self::USE_PER_PROJECT_SETTINGS);
        $this->setOption('state', self::PREFERRED_PROJECT_CODE_STYLE, $codeStyleName);
        //$this->writeBack($xml);
    }

    protected function getFileLocation(): string
    {
        return $this->fileLocation;
    }
}
