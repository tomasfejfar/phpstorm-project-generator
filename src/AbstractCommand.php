<?php

declare(strict_types=1);

namespace PhpStormGen;

use PhpStormGen\ConfigFiles\CodeStylePathHelper;
use PhpStormGen\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractCommand extends Command
{
    /** @var \PhpStormGen\ConfigFiles\CodeStylePathHelper */
    protected $codeStylePathHelper;

    protected $fs;

    public function __construct(
        Filesystem $fs,
        CodeStylePathHelper $codeStylePathHelper
    ) {
        parent::__construct();
        $this->fs = $fs;
        $this->codeStylePathHelper = $codeStylePathHelper;
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }

    /**
     * @param OutputInterface $output
     * @param $fs
     */
    protected function ensureProjectExists(): void
    {
        if (!$this->fs->exists($this->codeStylePathHelper->getIdeaDirectory())) {
            throw new Exception('Idea directory must exist before export');
        }
    }

    protected function readConfig()
    {
        if (!$this->fs->exists($this->getConfigPath())) {
            return [];
        }

        return json_decode(file_get_contents($this->getConfigPath()), true);
    }

    protected function writeConfig(array $config)
    {
        file_put_contents($this->getConfigPath(), json_encode($config, \JSON_PRETTY_PRINT));
    }

    /**
     * @return string
     */
    protected function getConfigPath(): string
    {
        return $this->codeStylePathHelper->getTemplateDirectory() . '/config.json';
    }
}
