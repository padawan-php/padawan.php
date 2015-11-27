<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Padawan\Framework\Application\HTTP\App;
use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Index;
use Fake\Request;
use Fake\Response;
use DI\Container;
use Psr\Log\LoggerInterface;
use Monolog\Handler\NullHandler;
use Padawan\Framework\Generator\IndexGenerator;

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
        $this->app = new App(true);
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
        $request = new Request("complete", [
            'line' => $this->line + 1,
            'column' => $this->column + 1,
            'file' => $this->filename
        ], $this->content);
        $this->response = json_decode(
            $this->app->handle($request, new Response, $request->body),
            true
        );
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
    private $filename;
    private $line;
    private $column;
    private $content;
    private $response;
}
