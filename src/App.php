<?php

use Entity\Project;
use Entity\Index;

class App {
    private $router;
    private $container;
    private $projectsPool = [];
    private $currentProject = null;

    public function __construct(){
        $this->router = new Router;
    }
    public function handle($request, $response, $data){
        $command = $this->getRouter()
            ->getCommand(
                $this->getCommandName($request)
            );
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
                $project = $this->container->get("IO\Reader")->read($rootDir);
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
    protected function setResponseHeaders($response){
        try {
            $response->writeHead(200, [
                'content-type' => 'application/json'
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
