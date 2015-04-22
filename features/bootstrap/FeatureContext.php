<?php

use Behat\Behat\Context\Context;

class FeatureContext implements Context {

    public function __construct(){
        $this->rootPath = dirname(dirname(__DIR__)) . "/app/tmp";
        echo $this->rootPath;
    }
    /**
     * @BeforeScenario
     */
    public function prepare(){
        exec(sprintf("cd %s && mkdir project", $this->rootPath));
    }

    /**
     * @AfterScenario
     */
    public function cleanProject(){
        exec(sprintf("cd %s && rm -rf project", $this->rootPath));
    }

    public function getProjectPath(){
        return $this->rootPath . "/project";
    }

    /**
     * @Given I have composer project
     */
    public function createComposerProject(){
        $path = $this->getProjectPath();
        exec(sprintf("cd %s && echo '{}' > composer.json", $path));
    }

    private $rootPath;
}
