<?php

declare(strict_types=1);

namespace PhpStormGen;

use PhpStormGen\ConfigFiles\Project\CodeStyleConfig;
use PhpStormGen\Exception\Exception;
use SimpleXMLElement;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function json_decode;

class ImportCommand extends Command
{
    const NAME = 'import';
    const CHOICE_EXPORT_CODE_STYLE_SETTINGS = 'Import code style settings';

    /** @var string */
    private $currentDir;

    /** @var Filesystem */
    private $fs;

    /** @var string */
    private $phpstormUserProfile;

    /** @var OutputInterface */
    private $output;

    /** @var string */
    private $templateDirectory;

    public function __construct(
        Filesystem $fs
    ) {
        parent::__construct();
        $this->fs = $fs;
    }

    protected function configure()
    {
        $this->setName(self::NAME);
        $this->currentDir = getcwd();
        $this->phpstormUserProfile = 'c:\Users\tomasfejfar\.PhpStorm2018.2';
        $this->templateDirectory = '/.ide-tpl';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $output->writeln('PhpStorm project generator');
        $output->writeln(sprintf('<info>Working in "%s" directory</info>', $this->currentDir));
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
        if (!$this->fs->exists($this->getIdeaDirectory())) {
            throw new Exception('Idea directory must exist before export');
        }
    }

    protected function getIdeaDirectory(): string
    {
        return $this->currentDir . '/.idea';
    }

    protected function getGlobalConfigDirectory(): string
    {
        $rootPath = $this->phpstormUserProfile . '/config';
        $settingsRepositoryPath = $rootPath . '/settingsRepository/repository';
        if ($this->fs->exists($settingsRepositoryPath)) {
            $rootPath = $settingsRepositoryPath;
        }

        return $rootPath;
    }

    /**
     * @param $newsXML
     */
    private function generateCodeStyleConfig($newsXML): void
    {
        $codeStyleConfigXml = new SimpleXMLElement("<component />");
        $codeStyleConfigXml->addAttribute('name', 'ProjectCodeStyleConfiguration');
        /**
         *<component name="ProjectCodeStyleConfiguration">
         * <state>
         * <option name="USE_PER_PROJECT_SETTINGS" value="true" />
         * </state>
         * </component>
         */
        $newsXML->addAttribute('newsPagePrefix', 'value goes here');
        $newsIntro = $newsXML->addChild('content');
        $newsIntro->addAttribute('type', 'latest');
        Header('Content-type: text/xml');
        echo $newsXML->asXML();
    }

    private function readConfig()
    {
        if (!$this->fs->exists($this->getConfigPath())) {
            return [];
        }

        return json_decode(file_get_contents($this->getConfigPath()), true);
    }

    private function writeConfig(array $config)
    {
        file_put_contents($this->getConfigPath(), json_encode($config, \JSON_PRETTY_PRINT));
    }

    /**
     * @return string
     */
    private function getConfigPath(): string
    {
        return $this->getTemplateDirectory() . '/config.json';
    }

    /**
     * @return string
     */
    private function getTemplateDirectory(): string
    {
        return $this->currentDir . $this->templateDirectory;
    }

    private function importCodeStyle()
    {
        $config = $this->readConfig();
        if ($config['codeStyle']['perProject'] === true) {
            $codeStyleTemplateFilePath = CodeStyleConfig::DIR . '/project/Project.xml';
            $copyFrom = $this->getTemplateDirectory() . $codeStyleTemplateFilePath;
            $copyTo = $this->getIdeaDirectory() . CodeStyleConfig::PATH;
            $this->fs->copy($copyFrom, $copyTo);
            $this->output->writeln(sprintf('Copied per-project code style to "%s"', $copyTo));
            $codeStyleConfigFile = new CodeStyleConfig($this->getIdeaDirectory());
            $codeStyleConfigFile->setCodeStylePerProject();
        } else {
            dump('NIY');
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
