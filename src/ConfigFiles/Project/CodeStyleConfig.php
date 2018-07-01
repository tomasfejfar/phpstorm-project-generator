<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles\Project;

class CodeStyleConfig extends AbstractConfigFile
{
    public const DIR = '/codeStyles';
    public const FILE = 'codeStyleConfig.xml';
    public const PATH = self::DIR . '/' . self::FILE;
    private const USE_PER_PROJECT_SETTINGS = 'USE_PER_PROJECT_SETTINGS';
    private const PREFERRED_PROJECT_CODE_STYLE = 'PREFERRED_PROJECT_CODE_STYLE';

    /** @var string */
    protected $ideaDirectory;

    public function __construct(
        string $ideaDirectory
    ) {
        $this->ideaDirectory = $ideaDirectory;
    }

    public function getPrefferedProjectCodeStyle(): ?string
    {
        return $this->getOptionValue(
            $this->asXml()->state->option,
            self::PREFERRED_PROJECT_CODE_STYLE
        );
    }

    public function isPerProjectSettings(): bool
    {
        $perProject = $this->getOptionValue(
            $this->asXml()->state->option,
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
        $parentElement = $xml->state;
        $this->setOption($parentElement, self::USE_PER_PROJECT_SETTINGS, 'true');
        $this->unsetOption($xml->state->option, self::PREFERRED_PROJECT_CODE_STYLE);
        $this->writeBack($xml);
    }

    protected function getFileLocation(): string
    {
        return $this->ideaDirectory . CodeStyleConfig::PATH;
    }
}
