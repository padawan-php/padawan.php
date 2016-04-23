<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Padawan\Framework\Application\Socket;
use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Index;
use Fake\Output;
use DI\Container;
use Psr\Log\LoggerInterface;
use Monolog\Handler\NullHandler;
use Padawan\Framework\Generator\IndexGenerator;
use Padawan\Domain\Core\Completion\Scope;
use Padawan\Domain\Core\Completion\Scope\FileScope;

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
        $this->app = new Socket();
        $container = $this->app->getContainer();
        $container->get("Psr\\Log\\LoggerInterface")->popHandler();
        $container->get("Psr\\Log\\LoggerInterface")->pushHandler(new NullHandler());
    }

    public function createProject()
    {
        $this->project = new Project(new Index);
    }

    /**
     * @Given there is a file with:
     */
    public function thereIsAFileWith(PyStringNode $string)
    {
        $file = uniqid() . ".php";
        $container = $this->app->getContainer();
        $generator = $container->get(IndexGenerator::class);
        $walker = $generator->getWalker();
        $parser = $generator->getClassUtils()->getParser();
        $parser->addWalker($walker);
        $parser->setIndex($this->project->getIndex());
        $this->content = $string->getRaw();
        $scope = $parser->parseContent($file, $this->content, null, false);
        $generator->processFileScope($this->project->getIndex(), $scope);
        $this->scope = $scope;
    }

    /**
     * @When I type :code on the :line line
     */
    public function iTypeOnTheLine($code, $linenum)
    {
        $content = explode("\n", $this->content);
        if (!isset($content[$linenum-1])) {
            $content[$linenum-1] = "";
        }
        $content[$linenum-1] .= $code;
        $this->content = implode("\n", $content);
        $this->line = $linenum - 1;
        $this->column = strlen($content[$linenum-1]);
    }

    /**
     * @When I ask for completion
     */
    public function askForCompletion()
    {
        $request = new \stdclass;
        $request->command = "complete";
        $request->params = new \stdclass;
        $request->params->line = $this->line + 1;
        $request->params->column = $this->column + 1;
        $request->params->filepath = $this->filename;
        $request->params->path = $this->path;
        $request->params->data = $this->content;

        $output = new Output;
        $app = $this->app;
        Amp\run(function() use ($request, $output, $app) {
            yield Amp\resolve($app->handle($request, $output));
        });
        $this->response = json_decode($output->output[0], 1);
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
            switch(count($columns)) {
                case 2:
                    $hash["Signature"] = $item["signature"];
                case 1:
                    $hash["Name"] = $item["name"];
                    break;
            }
            return $hash;
        }, $this->response["completion"]);
        expect($table->getColumnsHash())->to->loosely->equal($result);
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
}
