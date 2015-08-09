<?php

namespace Application;

use Entity\Project;
use Entity\Index;
use Command\ErrorCommand;
use DI\Container;
use DI\ContainerBuilder;
use Plugin\Package;

abstract class BaseApplication
{
    public function __construct($noFsIO)
    {
        $this->noFsIO = $noFsIO;
        $this->createContainer();
        $this->pluginsPackage = $this->container->get(Package::class);
        $this->loadPlugins();
    }
    public function handle($request, $response, $data)
    {
        $command = $this->getRouter()
            ->getCommand(
                $this->getCommandName($request),
                $this->container
            );
        if ($command instanceof ErrorCommand) {
            return $command->run([]);
        }
        $this->container = $command->getContainer();
        $arguments = $this->getArguments($request, $response, $data);
        try {
            $result = $command->run(
                $arguments
            );
        } catch (\Exception $e) {
            $result = [
                "error" => $e->getMessage()
            ];
            echo $e->getMessage();
        }

        return $result;
    }

    public function after()
    {
        if ($this->currentProject) {
            $project = $this->currentProject;
            exec(sprintf(
                "cd % && composer dumpautoload -o > /dev/null &",
                $project->getRootDir()
            ));
        }
    }

    protected function getArguments()
    {
        return [];
    }
    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->router;
    }
    protected function loadPlugins()
    {
        $plugins = $this->pluginsPackage->getPluginsList();
        foreach ($plugins as $pluginName) {
            $parts = explode("/", $pluginName);
            $className = implode("\\", array_map(function ($part) {
                return ucfirst($part);
            }, $parts));
            $className .= "\\Plugin";
            try {
                /** @var \Plugin\PluginInterface */
                $plugin = $this->container->get($className);
                $plugin->init();
            } catch (\Exception $e) {
                printf("Plugin Error: %s\n", $e->getMessage());
            }
        }
    }
    abstract protected function getCommandName($request);
    protected function createContainer()
    {
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new \Doctrine\Common\Cache\ArrayCache);
        $builder->addDefinitions(dirname(__DIR__) . '/DI/config.php');
        $this->container = $builder->build();
    }

    protected $router;
    /** @var Package */
    protected $pluginsPackage;
    static protected $projectsPool = [];
    static protected $currentProject = null;
    /** @var Container */
    protected $container;
    protected $noFsIO = false;
}
