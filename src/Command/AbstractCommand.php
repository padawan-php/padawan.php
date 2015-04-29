<?php

namespace Command;

use DI\ContainerBuilder;

abstract class AbstractCommand implements CommandInterface{
    protected static $container;
    public function __construct(){
        $this->createContainer();
    }
    public function get($serviceName){
        return self::$container->get($serviceName);
    }
    public function getContainer(){
        return self::$container;
    }
    protected function createContainer(){
        if(!empty(self::$container)){
            return;
        }
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new \Doctrine\Common\Cache\ArrayCache);
        $builder->addDefinitions(dirname(__DIR__) . '/DI/config.php');
        self::$container = $builder->build();
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
