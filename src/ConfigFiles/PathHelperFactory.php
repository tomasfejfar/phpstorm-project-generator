<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles;

use Symfony\Component\Filesystem\Filesystem;
use function getcwd;

class PathHelperFactory
{
    private $phpstormUserProfile;

    private $currentDir;

    private $templateDirectory;

    /** @var Filesystem */
    private $fs;

    public function __construct(
        Filesystem $fs
    ) {
        $this->phpstormUserProfile = 'c:\Users\tomasfejfar\.PhpStorm2018.2';
        $this->templateDirectory = '/.ide-tpl';
        $this->currentDir = getcwd();
        $this->fs = $fs;
    }

    public function createCodeStylePathHelper(): CodeStylePathHelper
    {
        return new CodeStylePathHelper(
            $this->getTemplateDirectory(),
            $this->getGlobalConfigDirectory(),
            $this->getIdeaDirectory()
        );
    }

    public function getTemplateDirectory(): string
    {
        return $this->currentDir . $this->templateDirectory;
    }

    public function getGlobalConfigDirectory(): string
    {
        $rootPath = $this->phpstormUserProfile . '/config';
        $settingsRepositoryPath = $rootPath . '/settingsRepository/repository';
        if ($this->fs->exists($settingsRepositoryPath)) {
            $rootPath = $settingsRepositoryPath;
        }

        return $rootPath;
    }

    public function getIdeaDirectory(): string
    {
        return $this->currentDir . '/.idea';
    }
}
