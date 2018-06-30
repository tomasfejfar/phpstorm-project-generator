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
        return $this->getOption(
            $this->asXml()->state->option,
            self::PREFERRED_PROJECT_CODE_STYLE
        );
    }

    public function isPerProjectSettings(): bool
    {
        $perProject = $this->getOption(
            $this->asXml()->state->option,
            CodeStyleConfig::USE_PER_PROJECT_SETTINGS
        );
        if ($perProject === null) {
            return false;
        }

        return (bool)$perProject;
    }

    protected function getFileLocation(): string
    {
        return $this->ideaDirectory . CodeStyleConfig::PATH;
    }
}
