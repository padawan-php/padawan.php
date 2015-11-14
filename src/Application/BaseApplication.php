<?php

namespace Application;

use Domain\Core\Project;
use Domain\Core\Index;
use Command\ErrorCommand;
use DI\Container;
use DI\ContainerBuilder;
use Plugin\Loader;

abstract class BaseApplication
{
    public function __construct($noFsIO)
    {
        $this->noFsIO = $noFsIO;
        $this->createContainer();
        $this->pluginsLoader = $this->container->get(Loader::class);
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
        $arguments = $this->getArguments($request, $response, $data);
        try {
            $result = $command->run(
                $arguments
            );
        } catch (\Exception $e) {
            $result = [
                "error" => $e->getMessage()
            ];
        }

        return $result;
    }

    public function after()
    {
        return;
    }

    /** @return Container */
    public function getContainer()
    {
        return $this->container;
    }

    abstract protected function getArguments($request, $response, $data);
    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->router;
    }
    protected function loadPlugins()
    {
        return $this->pluginsLoader->load();
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
    /** @var Loader */
    protected $pluginsLoader;
    protected $projectsPool = [];
    protected $currentProject = null;
    /** @var Container */
    protected $container;
    protected $noFsIO = false;
}
