<?php

declare(strict_types=1);

namespace PhpStormGen;

use function parent;
use PhpStormGen\ConfigFiles\CodeStylePathHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class SetupCommand extends AbstractCommand
{
    /** @var ProjectConfigHandler */
    private $projectConfigHandler;

    public function __construct(
        Filesystem $fs,
        CodeStylePathHelper $codeStylePathHelper,
        ProjectConfigHandler $projectConfigHandler
    ) {
        parent::__construct($fs, $codeStylePathHelper);
        $this->projectConfigHandler = $projectConfigHandler;
    }

    protected function configure()
    {
        $this->setName('configure');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeader($output);
        $output->writeln('');

        $this->handleMode($input, $output);

        $this->handleSettingsRepository($input, $output);
    }

    private function handleMode(InputInterface $input, OutputInterface $output): void
    {
        $config = $this->projectConfigHandler->load();
        if ($config->getMode()) {
            $confirm = new ConfirmationQuestion(sprintf(
                'Project is already set up to use mode <info>%s</info>. Would you like to change it? [n]',
                $config['mode']
            ), false);
            if (!$this->getQuestionHelper()->ask($input, $output, $confirm)) {
                return;
            }
        }
        $output->writeln([
            'There are two modes of operation for PhpStorm setup. SettingsRepository and local .idea folder. ',
            'Settings repository is more stable and practically native to PhpStorm. ',
            'Using local .idea folder offers better isolation between projects. ',
        ]);
        $output->writeln('');
        $question = new ChoiceQuestion(
            'How would you like to synchronize your settings?',
            [
                Config::MODE_SETTINGS_REPOSITORY,
                Config::MODE_IDEA_FOLDER,
            ]
        );

        $answer = $this->getQuestionHelper()->ask($input, $output, $question);
        $config = $this->readConfig();
        $config['mode'] = $answer;
        if ($config->getMode() === Config::MODE_IDEA_FOLDER) {
            unset($config['repository'])
        }
        $this->writeConfig($config);
    }

    private function handleSettingsRepository(InputInterface $input, OutputInterface $output)
    {
        $config = $this->readConfig();
        if ($config['mode'] !== Config::MODE_SETTINGS_REPOSITORY) {
            return;
        }
        if (isset($config['repository'])) {
            $confirm = new ConfirmationQuestion(sprintf(
                'Settings repository already set to <info>%s</info> would you like to change it? [n]',
                $config['repository']
            ), false);
            if (!$this->getQuestionHelper()->ask($input, $output, $confirm)) {
                return;
            }
        }
        $question = new Question('Please specify git repository for shared setting');
        $repository = $this->getQuestionHelper()->ask($input, $output, $question);
        $config['repository'] = $repository;
        $this->writeConfig($config);
    }
}
