<?php

use Mkusher\Co;
use Fake\Output;
use DI\Container;
use Padawan\Domain\Scope;
use Padawan\Domain\Project;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use Monolog\Handler\NullHandler;
use Behat\Behat\Context\Context;
use Padawan\Domain\Project\File;
use Padawan\Domain\Project\Index;
use Behat\Gherkin\Node\TableNode;
use Padawan\Domain\Scope\FileScope;
use Behat\Gherkin\Node\PyStringNode;
use Padawan\Framework\Application\Socket;
use Padawan\Framework\Generator\IndexGenerator;
use Behat\Behat\Context\SnippetAcceptingContext;
use Padawan\Framework\Domain\Project\InMemoryIndex;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->createApplication();
        $this->createProject();
    }

    public function createApplication()
    {
        $this->loop = Factory::create();
        $this->app = new Socket($this->loop);
        $container = $this->app->getContainer();
        $container->get(LoggerInterface::class)->popHandler();
        $container->get(LoggerInterface::class)->pushHandler(new NullHandler());
    }

    public function createProject()
    {
        $this->project = new Project(new InMemoryIndex);
    }

    /**
     * @BeforeScenario
     */
    public function cleanTypedContent()
    {
        $this->typedContent = [];
    }

    /**
     * @Given there is a file with:
     */
    public function thereIsAFileWith(PyStringNode $string)
    {
        $filePath = uniqid() . ".php";
        $file = new File($filePath);
        $container = $this->app->getContainer();
        $generator = $container->get(IndexGenerator::class);
        $walker = $generator->getWalker();
        $parser = $generator->getClassUtils()->getParser();
        $parser->addWalker($walker);
        $parser->setIndex($this->project->getIndex());
        $this->content = $string->getRaw();
        $scope = $parser->parseContent($filePath, $this->content, null, false);
        $generator->processFileScope($file, $this->project->getIndex(), $scope, sha1($this->content));
        $this->scope = $scope;
    }

    /**
     * @When I type :code on the :line line
     */
    public function iTypeOnTheLine($code, $linenum)
    {
        $this->typedContent[] = [
            'line' => $linenum - 1,
            'code' => $code,
        ];
    }

    /**
     * @When I ask for completion
     */
    public function askForCompletion()
    {
        $content = explode("\n", $this->content);
        foreach ($this->typedContent as $entry) {
            $linenum = $entry['line'];
            if (!isset($content[$linenum])) {
                $content[$linenum] = "";
            }
            $content[$linenum] .= $entry['code'];
            $column = strlen($content[$linenum]);
        }
        $content = implode("\n", $content);

        $request = new \stdclass;
        $request->command = "complete";
        $request->params = new \stdclass;
        $request->params->line = $linenum + 1;
        $request->params->column = $column + 1;
        $request->params->filepath = $this->filename;
        $request->params->path = $this->path;
        $request->params->data = $content;

        $output = new Output;
        $app = $this->app;
        Co\await(function() use ($request, $output, $app) {
            yield $app->handle($request, $output);
        })->then(function() use ($output) {
            $this->response = json_decode($output->output[0], 1);
        });
        $this->loop->run();
    }

    /**
     * @Then I should get:
     */
    public function iShouldGet(TableNode $table)
    {
        if (isset($this->response["error"])) {
            throw new \Exception(
                sprintf("Application response contains error: %s", $this->response["error"])
            );
        }
        $columns = $table->getRow(0);
        $result = array_map(function ($item) use($columns) {
            $hash = [];
            $map = [
                "Name" => "name",
                "Signature" => "signature",
                "Menu" => "menu"
            ];
            foreach ($columns as $column) {
                $hash[$column] = $item[$map[$column]];
            }
            return $hash;
        }, $this->response["completion"]);
        expect($result)->to->loosely->equal($table->getColumnsHash());
    }

    /** @var App */
    private $app;
    /** @var Project */
    private $project;
    private $path;
    private $filename;
    private $line;
    private $column;
    private $content;
    private $response;
    /** @var Scope */
    private $scope;
    private $typedContent;
}
