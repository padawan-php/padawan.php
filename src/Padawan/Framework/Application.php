<?php

namespace Padawan\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Symfony\Component\Console\Application as BaseApplication;

define("PADAWAN_VERSION", "0.3");

/**
 * Class Application
 */
class Application extends BaseApplication
{
    public function __construct($name = "Padawan")
    {
        parent::__construct($name, PADAWAN_VERSION);
        $this->createContainer();
        $this->setAutoExit(false);
    }

    private function createContainer()
    {
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new \Doctrine\Common\Cache\ArrayCache);
        $builder->addDefinitions(__DIR__ . '/DI/config.php');
        $this->container = $builder->build();
    }

    /** @var Container */
    protected $container;
}
