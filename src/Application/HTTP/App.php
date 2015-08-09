<?php

namespace Application\HTTP;

use Entity\Project;
use Entity\Index;
use Command\ErrorCommand;
use DI\Container;
use Application\BaseApplication;

class App extends BaseApplication
{

    public function __construct($noFsIO)
    {
        $this->router = new Router;
        parent::__construct($noFsIO);
    }
    public function handle($request, $response, $data)
    {
        $result = parent::handle($request, $response, $data);
        $this->setResponseHeaders($response);
        return json_encode($result);
    }
    protected function getArguments($request, $response, $data)
    {
        $arguments = $this->parseQuery($request->getQuery(), $data);
        $arguments["project"] = $this->loadProject($arguments);
        return $arguments;
    }
    protected function setResponseHeaders($response)
    {
        try {
            $response->writeHead(200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type',
                'Access-Control-Allow-Origin' => '*',
                'Origin' => 'http://localhost:15155'
            ]);
        } catch (\Exception $e) {
        }
    }
    protected function parseQuery(array $query, $data)
    {
        $query['contents'] = urldecode($data);
        $keys = ["path", "contents", "filepath", "line", "column"];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $query)) {
                $query[$key] = "";
            }
        }
        return $query;
    }
    protected function getCommandName($request)
    {
        $commandName = trim($request->getPath(), '\/');
        return $commandName;
    }

    /**
     * @return Project
     */
    protected function loadProject($arguments)
    {
        $rootDir = $arguments["path"];
        if (empty($rootDir) || $rootDir === '/') {
            $project = $this->createEmptyProject(
                dirname($arguments["filepath"])
            );
        } else {
            if (array_key_exists($rootDir, $this->projectsPool)) {
                $project = $this->projectsPool[$rootDir];
            } else {
                if (!$this->noFsIO) {
                    $project = $this->container->get("IO\Reader")->read($rootDir);
                }
                if (empty($project)) {
                    $project = $this->createEmptyProject($rootDir);
                }
                $this->projectsPool[$rootDir] = $project;
            }
        }
        $this->currentProject = $project;
        return $project;
    }
    protected function createEmptyProject($rootDir)
    {
        $project = new Project(new Index, $rootDir);
        return $project;
    }
}
