<?php

declare(strict_types=1);

namespace PhpStormGen;

use PhpStormGen\ConfigFiles\Project\CodeStyleConfig;
use PhpStormGen\ConfigFiles\Project\CodeStyleFile;
use PhpStormGen\Exception\Exception;
use PhpStormGen\Exception\UnmetExpectationException;
use SimpleXMLElement;
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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ExtractCommand extends Command
{
    const NAME = 'extract';
    const CHOICE_EXPORT_CODE_STYLE_SETTINGS = 'Export code style settings';

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
        if (true || $choice === self::CHOICE_EXPORT_CODE_STYLE_SETTINGS) {
            $this->exportCodeStyle();
        }
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

    private function exportCodeStyle(): void
    {
        $this->ensureProjectExists();

        if (!$this->fs->exists($this->getIdeaDirectory() . CodeStyleConfig::PATH)) {
            // it's default setting from user profile
            throw new Exception('Exporting default settings is not supported yet');
        } else {
            // it's local
            $codeStyleConfigFile = new CodeStyleConfig($this->getIdeaDirectory());
            if ($codeStyleConfigFile->isPerProjectSettings()) {
                $this->output->writeln('Project is using per-project code style');
                $projectCodeStyleFilePath = $this->getIdeaDirectory() . CodeStyleConfig::DIR . '/Project.xml';
                if (!$this->fs->exists($projectCodeStyleFilePath)) {
                    throw new UnmetExpectationException(sprintf(
                        'Project code style not found in "%s"',
                        $projectCodeStyleFilePath
                    ));
                }
                $templateCodeStyleFilePath = $this->getTemplateDirectory() . '/codeStyle/project/Project.xml';
                $this->fs->copy($projectCodeStyleFilePath, $templateCodeStyleFilePath);
                $this->output->writeln(sprintf('Code style stored in "%s"', $templateCodeStyleFilePath));
                $config = $this->readConfig();
                $config['codeStyle']['perProject'] = true;
                $this->writeConfig($config);
            } else {
                $codeStyle = $codeStyleConfigFile->getPrefferedProjectCodeStyle();
                $this->output->writeln(sprintf('Project is using <info>%s</info> code style', $codeStyle));
                $finder = new Finder();
                /** @var SplFileInfo[] $codeStyles */
                $codeStyles = $finder->in($this->getGlobalConfigDirectory() . '/codestyles')->files();
                foreach ($codeStyles as $codeStyle) {
                    $codeStyleFile = new CodeStyleFile($codeStyle->getPathname());
                    if ($codeStyleFile->getCodeStyleName() === $codeStyle) {
                        $foundCodeStyleFinderItem = $codeStyle;
                        break;
                    }
                }
                $templateCodeStyleFilePath = $this->getTemplateDirectory() . '/codeStyle/global/' . $foundCodeStyleFinderItem->getFilename();
                $this->fs->copy(
                    $foundCodeStyleFinderItem->getPathname(),
                    $templateCodeStyleFilePath
                );
                $this->output->writeln(sprintf('Code style stored in "%s"', $templateCodeStyleFilePath));
                $config = $this->readConfig();
                $config['codeStyle']['perProject'] = false;
                $config['codeStyle']['name'] = $codeStyle;
                $this->writeConfig($config);
            }
        }
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
}
