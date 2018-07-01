<?php

declare(strict_types=1);

namespace PhpStormGen;

use PhpStormGen\ConfigFiles\CodeStylePathHelper;
use PhpStormGen\ConfigFiles\Project\CodeStyleConfig;
use PhpStormGen\Exception\Exception;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function sprintf;

class ImportCommand extends AbstractCommand
{
    const NAME = 'import';
    const CHOICE_EXPORT_CODE_STYLE_SETTINGS = 'Import code style settings';

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
//            $this->exportCodeStyle();
        //}
        $this->importCodeStyle();
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
        if (!$this->fs->exists($this->g())) {
            throw new Exception('Idea directory must exist before export');
        }
    }

    private function importCodeStyle()
    {
        $config = $this->readConfig();
        if ($config['codeStyle']['perProject'] === true) {
            $copyFrom = $this->codeStylePathHelper->getTemplatePerProjectCodeStyleFile();
            $copyTo = $this->codeStylePathHelper->getLocalPerProjectCodeStyleFile();
            $this->fs->copy($copyFrom, $copyTo);
            $this->output->writeln(sprintf('Copied per-project code style to "%s"', $copyTo));
            $codeStyleConfigFile = CodeStyleConfig::createLocalFromPathHelper($this->codeStylePathHelper);
            $codeStyleConfigFile->setCodeStylePerProject();
        } else {
            $codeStyleFilename = $config['codeStyle']['filename'];
            $codeStyleName = $config['codeStyle']['name'];
            $copyFrom = $this->codeStylePathHelper->getTemplateCodeStyleFile($codeStyleFilename);
            $copyTo = $this->codeStylePathHelper->getGlobalCodeStyleFile($codeStyleFilename);
            $this->fs->copy($copyFrom, $copyTo);
            $this->output->writeln(sprintf('Copied code style to "%s"', $copyTo));
            $codeStyleConfigFile = CodeStyleConfig::createLocalFromPathHelper($this->codeStylePathHelper);
            $codeStyleConfigFile->setCodeStyle($codeStyleName);
        }
    }

    private function getHomeDir()
    {
        // from https://github.com/drush-ops/drush/blob/5a19e6656c8307b71cb674afc96ce1ef58315c80/includes/environment.inc#L649
        // Cannot use $_SERVER superglobal since that's empty during Drush_UnitTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');
        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? null : $home;
    }
}
