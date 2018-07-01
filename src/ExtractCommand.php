<?php

declare(strict_types=1);

namespace PhpStormGen;

use PhpStormGen\ConfigFiles\CodeStylePathHelper;
use PhpStormGen\ConfigFiles\Project\CodeStyleConfig;
use PhpStormGen\ConfigFiles\Project\CodeStyleFile;
use PhpStormGen\Exception\Exception;
use PhpStormGen\Exception\UnmetExpectationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ExtractCommand extends AbstractCommand
{
    const NAME = 'extract';
    const CHOICE_EXPORT_CODE_STYLE_SETTINGS = 'Export code style settings';

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $output->writeln('PhpStorm project generator');
        $question = new ChoiceQuestion(
            'What would you like to do?',
            [self::CHOICE_EXPORT_CODE_STYLE_SETTINGS],
            '0'
        );
        //$choice = $this->getQuestionHelper()->ask($input, $output, $question);
        //if (true || $choice === self::CHOICE_EXPORT_CODE_STYLE_SETTINGS) {
        $this->exportCodeStyle();
        //}
//        $this->importCodeStyle();
    }

    private function exportCodeStyle(): void
    {
        $this->ensureProjectExists();

        if (!$this->fs->exists($this->codeStylePathHelper->getProjectCodeStyleConfigFilePath())) {
            // it's default setting from user profile
            throw new Exception('Exporting default settings is not supported yet');
        }

        // it's local
        $codeStyleConfigFile = CodeStyleConfig::createLocalFromPathHelper($this->codeStylePathHelper);
        if ($codeStyleConfigFile->isPerProjectSettings()) {
            $this->output->writeln('Project is using per-project code style');
            $localPerProjectCodeStyleFile = $this->codeStylePathHelper->getLocalPerProjectCodeStyleFile();
            if (!$this->fs->exists($localPerProjectCodeStyleFile)) {
                throw new UnmetExpectationException(sprintf(
                    'Project code style not found in "%s"',
                    $localPerProjectCodeStyleFile
                ));
            }
            $templatePerProjectCodeStyleFile = $this->codeStylePathHelper->getTemplatePerProjectCodeStyleFile();
            $this->fs->copy(
                $localPerProjectCodeStyleFile,
                $templatePerProjectCodeStyleFile
            );
            $this->output->writeln(sprintf(
                'Code style stored in "%s"',
                $templatePerProjectCodeStyleFile
            ));
            $config = $this->readConfig();
            $config['codeStyle']['perProject'] = true;
            unset($config['codeStyle']['name']);
            $this->writeConfig($config);
        } else {
            $codeStyleName = $codeStyleConfigFile->getPrefferedProjectCodeStyle();
            $this->output->writeln(sprintf('Project is using <info>%s</info> code style', $codeStyleName));
            $finder = new Finder();
            /** @var SplFileInfo[] $codeStyles */
            $codeStyles = $finder->in($this->codeStylePathHelper->getGlobalCodeStyleDir())->files();
            foreach ($codeStyles as $codeStyle) {
                $codeStyleFile = new CodeStyleFile($codeStyle->getPathname());
                if ($codeStyleFile->getCodeStyleName() === $codeStyleName) {
                    $foundCodeStyleFinderItem = $codeStyle;
                    break;
                }
            }
            $templateCodeStyleFilePath = $this->codeStylePathHelper->getTemplateCodeStyleFile($foundCodeStyleFinderItem->getFilename());
            $this->fs->copy(
                $foundCodeStyleFinderItem->getPathname(),
                $templateCodeStyleFilePath
            );
            $this->output->writeln(sprintf('Code style stored in "%s"', $templateCodeStyleFilePath));
            $config = $this->readConfig();
            $config['codeStyle']['perProject'] = false;
            $config['codeStyle']['name'] = $codeStyleName;
            $config['codeStyle']['filename'] = $foundCodeStyleFinderItem->getFilename();

            $this->writeConfig($config);
        }
    }

    public function getCodeStylePathHelper(): CodeStylePathHelper
    {
        return $this->codeStylePathHelper;
    }
}
