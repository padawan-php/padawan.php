<?php

use Entity\Project;
use Entity\Index;
use Command\ErrorCommand;

class App {
    private $router;
    private $container;
    private $projectsPool = [];
    private $currentProject = null;
    private $noFsIO         = false;

    public function __construct($noFsIO){
        $this->noFsIO = $noFsIO;
        $this->router = new Router;
    }
    public function handle($request, $response, $data){
        $command = $this->getRouter()
            ->getCommand(
                $this->getCommandName($request)
            );
        if($command instanceof ErrorCommand){
            return $command->run([]);
        }
        $this->container = $command->getContainer();
        $arguments = $this->parseQuery($request->getQuery(), $data);
        $arguments["project"] = $this->loadProject($arguments);
        try{
            $result = $command->run(
                $arguments
            );
        }
        catch(\Exception $e){
            $result = [
                "error" => $e->getMessage()
            ];
            echo $e->getMessage();
        }

        $this->setResponseHeaders($response);
        return json_encode($result);
    }
    public function getRouter(){
        return $this->router;
    }
    public function after(){
        if($this->currentProject){
            $project = $this->currentProject;
            exec(sprintf(
                "cd % && composer dumpautoload -o > /dev/null &",
                $project->getRootDir()
            ));
        }
    }
    protected function loadProject($arguments){
        $rootDir = $arguments["path"];
        if(empty($rootDir) || $rootDir === '/'){
            $project = $this->createEmptyProject(
                dirname($arguments["filepath"])
            );
        }
        else{
            if(array_key_exists($rootDir, $this->projectsPool)){
                $project = $this->projectsPool[$rootDir];
            }
            else{
                if(!$this->noFsIO){
                    $project = $this->container->get("IO\Reader")->read($rootDir);
                }
                if(empty($project)){
                    $project = $this->createEmptyProject($rootDir);
                }
                $this->projectsPool[$rootDir] = $project;
            }
        }
        $this->currentProject = $project;
        return $project;
    }
    protected function createEmptyProject($rootDir){
        $project = new Project(new Index, $rootDir);
        return $project;
    }
    public function setResponseHeaders($response){
        try {
            $response->writeHead(200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type',
                'Access-Control-Allow-Origin' => '*',
                'Origin' => 'http://localhost:10000'
            ]);
        }
        catch(\Exception $e){
        }
    }
    protected function getCommandName($request){
        $commandName = trim($request->getPath(), '\/');
        return $commandName;
    }
    protected function parseQuery(array $query, $data){
        $query['contents'] = urldecode($data);
        $keys = ["path", "contents", "filepath", "line", "column"];
        foreach($keys AS $key){
            if(!array_key_exists($key, $query)){
                $query[$key] = "";
            }
        }
        return $query;
    }
}
