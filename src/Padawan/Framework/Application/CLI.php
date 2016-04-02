<?php

namespace Padawan\Framework\Application;

use Padawan\Framework\Application\BaseApplication;
use Symfony\Component\Console\Application;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Padawan\Framework\Application\CLI;

class CLI extends Application
{
    public function __construct($name="", $version="")
    {
        parent::__construct($name, $version);
        $this->initializeContainer();
        $this->loadCommands();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function loadCommands()
    {
        $this->add(new CLI\GenerateCommand);
    }

    private function initializeContainer()
    {
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new ArrayCache);
        $builder->addDefinitions(dirname(__DIR__) . '/DI/config.php');
        $this->container = $builder->build();
    }

    /** @var Container */
    private $container;
}
