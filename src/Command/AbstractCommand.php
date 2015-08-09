<?php

namespace Command;

use DI\ContainerBuilder;
use DI\Container;

abstract class AbstractCommand implements CommandInterface
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function get($serviceName)
    {
        return $this->getContainer()->get($serviceName);
    }
    /**
     * @return \DI\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
    protected function isVerbose($arguments)
    {
        $verbose = false;
        if (count($arguments) > 0 && $arguments[0] == '-verbose') {
            array_shift($arguments);
            $verbose = true;
        }
        return $verbose;
    }
    /** @var Container */
    protected $container;
}
