<?php

namespace Command;

use DI\ContainerBuilder;

abstract class AbstractCommand implements CommandInterface{
    protected $container;
    public function __construct(){
        $this->createContainer();
    }
    public function get($serviceName){
        return $this->container->get($serviceName);
    }
    public function getContainer(){
        return $this->container;
    }
    protected function createContainer(){
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new \Doctrine\Common\Cache\ArrayCache);
        $builder->addDefinitions(__DIR__ . '/config.php');
        $this->container = $builder->build();
    }
    protected function isVerbose($arguments){
        $verbose = false;
        if(count($arguments) > 0 && $arguments[0] == '-verbose') {
            array_shift($arguments);
            $verbose = true;
        }
        return $verbose;
    }
}
