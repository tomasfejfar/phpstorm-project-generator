<?php

declare(strict_types=1);

namespace PhpStormGen;

use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use function getcwd;

class ExtractCommand extends Command
{
    const NAME = 'extract';
    const CHOICE_EXPORT_CODE_STYLE_SETTINGS = 'Export code style settings';

    /** @var string */
    private $currentDir;

    /** @var Filesystem */
    private $fs;

    public function __construct(
        Filesystem $fs
    )
    {
        parent::__construct();
        $this->fs = $fs;
    }

    protected function configure()
    {
        $this->setName(self::NAME);
        $this->currentDir = getcwd();
        $this->phpstormUserProfile = 'c:\Users\tomasfejfar\.PhpStorm2018.2';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('PhpStorm project generator');
        $output->writeln(sprintf('<info>Working in "%s" directory</info>', $this->currentDir));
        $question = new ChoiceQuestion(
            'What would you like to do?',
            [self::CHOICE_EXPORT_CODE_STYLE_SETTINGS],
            '0'
        );
        $choice = $this->getQuestionHelper()->ask($input, $output, $question);
        if ($choice === self::CHOICE_EXPORT_CODE_STYLE_SETTINGS) {
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

    private function exportCodeStyle(): void
    {
        $this->ensureProjectExists();
        $codeStyleConfig = $this->getIdeaDirectory() . '/codeStyles/codeStyleConfig.xml';
        if (!$this->fs->exists($codeStyleConfig)) {
            $codeStyleConfigXml = new SimpleXMLElement("<news></news>");
            $newsXML->addAttribute('newsPagePrefix', 'value goes here');
            $newsIntro = $newsXML->addChild('content');
            $newsIntro->addAttribute('type', 'latest');
            Header('Content-type: text/xml');
            echo $newsXML->asXML();
        }
    }
}
