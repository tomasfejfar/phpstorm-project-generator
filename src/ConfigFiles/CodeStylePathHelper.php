<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles;

use PhpStormGen\ConfigFiles\Project\CodeStyleConfig;

class CodeStylePathHelper
{
    public const DIR_CODE_STYLES = 'codeStyles';

    /** @var string */
    private $templateDirectory;

    /** @var string */
    private $globalConfigDirectory;

    /** @var string */
    private $ideaDirectory;

    const FILENAME_PROJECT_CODE_STYLE = 'Project.xml';

    public function __construct(
        string $templateDirectory,
        string $globalConfigDirectory,
        string $ideaDirectory
    ) {
        $this->templateDirectory = PathUtil::sanitizePath($templateDirectory);
        $this->globalConfigDirectory = PathUtil::sanitizePath($globalConfigDirectory);
        $this->ideaDirectory = PathUtil::sanitizePath($ideaDirectory);
    }

    public function getGlobalCodeStyleFile(string $filename): string
    {
        return PathUtil::pathFromSegments([
            $this->getGlobalCodeStyleDir(),
            $filename,
        ]);
    }

    public function getGlobalCodeStyleDir(): string
    {
        return PathUtil::pathFromSegments([
            $this->globalConfigDirectory,
            self::DIR_CODE_STYLES,
        ]);
    }

    public function getProjectCodeStyleConfigFilePath()
    {
        return PathUtil::pathFromSegments([
            $this->ideaDirectory,
            self::DIR_CODE_STYLES,
            CodeStyleConfig::FILE,
        ]);
    }

    public function getLocalPerProjectCodeStyleFile(): string
    {
        return PathUtil::pathFromSegments([
            $this->getIdeaDirectory(),
            CodeStylePathHelper::DIR_CODE_STYLES,
            self::FILENAME_PROJECT_CODE_STYLE,
        ]);
    }

    public function getTemplatePerProjectCodeStyleFile(): string
    {
        return PathUtil::pathFromSegments([
            $this->templateDirectory,
            CodeStylePathHelper::DIR_CODE_STYLES,
            'project',
            self::FILENAME_PROJECT_CODE_STYLE,
        ]);
    }

    public function getIdeaDirectory()
    {
        return $this->ideaDirectory;
    }

    public function getTemplateCodeStyleFile(string $filename): string
    {
        return PathUtil::pathFromSegments([
            $this->templateDirectory,
            CodeStylePathHelper::DIR_CODE_STYLES,
            'global',
            $filename,
        ]);
    }

    public function getTemplatePerProjectCodeStyleConfigFile()
    {
        return PathUtil::pathFromSegments([
            $this->templateDirectory,
            CodeStylePathHelper::DIR_CODE_STYLES,
            CodeStyleConfig::FILE
        ]);
    }

    public function getLocalPerProjectCodeStyleConfigFile()
    {
        return PathUtil::pathFromSegments([
            $this->ideaDirectory,
            CodeStylePathHelper::DIR_CODE_STYLES,
            CodeStyleConfig::FILE
        ]);
    }

    public function getTemplateDirectory()
    {
        return $this->templateDirectory;
    }
}
